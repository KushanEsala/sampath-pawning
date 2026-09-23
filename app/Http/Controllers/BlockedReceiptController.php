<?php

namespace App\Http\Controllers;

use App\Models\TOpeningPawnSum;
use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BlockedReceiptController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'receipt_type' => 'nullable|in:Pawn,Opening_Pawn',
            'receipt_number' => 'nullable|string|max:80',
            'status' => 'nullable|in:all,blocked,active',
        ]);
        $type = $validated['receipt_type'] ?? 'Pawn';
        $ready = $this->schemaReady($type);
        $receipts = null;

        if ($ready) {
            $model = $type === 'Pawn' ? TPawnSum::class : TOpeningPawnSum::class;
            $query = $model::where('BC', auth()->user()->BC)
                ->where('IsRedeemed', 0)->where('isForfeit', 0);
            if (!empty($validated['receipt_number'])) {
                $number = $validated['receipt_number'];
                $query->where(function ($q) use ($number) {
                    $q->where('Receipt_Number', $number)
                        ->orWhere('Ticket_Number', $number)
                        ->orWhere('Invoice_Number', $number);
                });
            }
            if (($validated['status'] ?? 'all') === 'blocked') $query->where('is_blocked', 1);
            if (($validated['status'] ?? 'all') === 'active') $query->where('is_blocked', 0);
            $receipts = $query->orderByDesc('Receipt_Number')->paginate(25)->withQueryString();
        }

        return view('blockedReceipts', compact('receipts', 'type', 'ready'));
    }

    public function update(Request $request, ReceiptLifecycleService $lifecycle)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'receipt_type' => 'required|in:Pawn,Opening_Pawn',
            'receipt_number' => 'required|string|max:80',
            'action' => 'required|in:block,unblock',
            'reason' => 'nullable|string|max:1000',
        ]);
        if (!$this->schemaReady($validated['receipt_type'])) {
            throw ValidationException::withMessages(['receipt_number' => 'Apply manual database script 009 before using receipt blocking.']);
        }

        $type = $validated['receipt_type'];
        $model = $type === 'Pawn' ? TPawnSum::class : TOpeningPawnSum::class;
        DB::transaction(function () use ($validated, $type, $model, $lifecycle) {
            $receipt = $model::where('BC', auth()->user()->BC)
                ->where('Receipt_Number', $validated['receipt_number'])
                ->lockForUpdate()->firstOrFail();
            if ($receipt->IsRedeemed || $receipt->isForfeit) {
                throw ValidationException::withMessages(['receipt_number' => 'A redeemed or forfeited receipt cannot be blocked or unblocked.']);
            }

            $blocked = $validated['action'] === 'block';
            if ((bool) $receipt->is_blocked === $blocked) {
                throw ValidationException::withMessages(['receipt_number' => $blocked ? 'This receipt is already blocked.' : 'This receipt is not blocked.']);
            }
            $reason = trim((string) ($validated['reason'] ?? ''));
            if ($blocked && $reason === '') {
                throw ValidationException::withMessages(['reason' => 'Please enter a reason for blocking this receipt.']);
            }

            $previousReason = $receipt->block_reason;
            $updates = [
                'is_blocked' => $blocked ? 1 : 0,
                'blocked_at' => $blocked ? now() : null,
                'blocked_by' => $blocked ? auth()->user()->username : null,
                'block_reason' => $blocked ? $reason : null,
                'updated_at' => now(),
            ];
            // Opening-pawn summaries have no id primary key; always use the branch and receipt key.
            DB::table($type === 'Pawn' ? 't_pawn_sums' : 't_opening_pawn_sums')
                ->where('BC', $receipt->BC)
                ->where('Receipt_Number', $receipt->Receipt_Number)
                ->update($updates);
            $receipt->forceFill($updates);

            if ($type === 'Pawn') {
                $lifecycle->record($receipt, $blocked ? 'BLOCKED' : 'UNBLOCKED', null,
                    $reason ?: 'Administrator unblocked receipt.', ['previous_reason' => $blocked ? null : $previousReason]);
            }
        });

        return back()->with('done', 'Receipt '.($validated['action'] === 'block' ? 'blocked' : 'unblocked').' successfully.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    private function schemaReady(string $type): bool
    {
        $table = $type === 'Pawn' ? 't_pawn_sums' : 't_opening_pawn_sums';
        return Schema::hasColumn($table, 'is_blocked') && Schema::hasColumn($table, 'blocked_at')
            && Schema::hasColumn($table, 'blocked_by') && Schema::hasColumn($table, 'block_reason');
    }
}
