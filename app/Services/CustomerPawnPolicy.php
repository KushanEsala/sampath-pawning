<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\TPawnSum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CustomerPawnPolicy
{
    public function customers(string $nic, bool $lock = false): Collection
    {
        $query = Customer::where('NIC', trim($nic))->orderBy('id');
        return ($lock ? $query->lockForUpdate() : $query)->get();
    }

    public function branchCustomer(string $nic, string $branch): ?Customer
    {
        return Customer::where('BC', $branch)
            ->where('NIC', trim($nic))
            ->orderByDesc('id')->first();
    }

    public function customerForOperator(string $nic, string $branch, bool $isAdmin): ?Customer
    {
        $local = $this->branchCustomer($nic, $branch);
        if ($local || !$isAdmin) return $local;
        return Customer::whereNotNull('BC')
            ->where('NIC', trim($nic))
            ->orderByDesc('id')->first();
    }

    public function policy(Collection $customers): array
    {
        $amountLimits = $customers->pluck('Limit_Amount')->filter(fn ($value) => (float) $value > 0);
        $countLimits = $customers->pluck('Limit_Pawn_Count')->filter(fn ($value) => (int) $value > 0);

        return [
            'active' => !$customers->contains(fn ($row) => (string) $row->Status === '0'),
            'amount_limit' => $amountLimits->isEmpty() ? 0.0 : (float) $amountLimits->min(),
            'count_limit' => $countLimits->isEmpty() ? 0 : (int) $countLimits->min(),
        ];
    }

    public function exposure(string $nic): array
    {
        $main = DB::table('t_pawn_sums')
            ->where('Customer_NIC', trim($nic))
            ->where('IsRedeemed', 0)->where('isForfeit', 0)
            ->selectRaw('COUNT(*) AS pawn_count, COALESCE(SUM(COALESCE(NULLIF(Pawn_Amount, 0), Amount, 0)), 0) AS pawn_amount')
            ->first();
        $count = (int) ($main->pawn_count ?? 0);
        $amount = (float) ($main->pawn_amount ?? 0);

        // Opening receipts are separate loans only when no matching main receipt exists.
        if (Schema::hasTable('t_opening_pawn_sums')) {
            $opening = DB::table('t_opening_pawn_sums AS opening')
                ->where('opening.Customer_NIC', trim($nic))
                ->where('opening.IsRedeemed', 0)->where('opening.isForfeit', 0)
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')->from('t_pawn_sums AS main')
                        ->whereColumn('main.BC', 'opening.BC')
                        ->whereColumn('main.Receipt_Number', 'opening.Receipt_Number');
                })
                ->selectRaw('COUNT(*) AS pawn_count, COALESCE(SUM(Amount), 0) AS pawn_amount')
                ->first();
            $count += (int) ($opening->pawn_count ?? 0);
            $amount += (float) ($opening->pawn_amount ?? 0);
        }

        return ['pawn_count' => $count, 'pawn_amount' => $amount];
    }

    public function assertNewPawn(string $nic, float $amount): void
    {
        $this->assertProjected($nic, $amount, 1);
    }

    public function assertRepawn(TPawnSum $receipt, float $newAmount): void
    {
        $current = (float) ($receipt->Pawn_Amount ?: $receipt->Amount ?: 0);
        $this->assertProjected((string) $receipt->Customer_NIC, $newAmount - $current, 0);
    }

    private function assertProjected(string $nic, float $amountDelta, int $countDelta): void
    {
        // Called inside the saving transaction. Every pawn/repawn for this NIC locks
        // the same customer rows, including legacy unassigned rows, before counting.
        $customers = $this->customers($nic, true);
        if ($customers->isEmpty()) {
            throw ValidationException::withMessages(['customer_nic' => 'Register this customer before pawning.']);
        }
        $policy = $this->policy($customers);
        if (!$policy['active']) {
            throw ValidationException::withMessages(['customer_nic' => 'This customer is inactive. Activate the customer before pawning or repawning.']);
        }
        $exposure = $this->exposure($nic);
        $violation = self::violation($policy, $exposure, $amountDelta, $countDelta);
        if ($violation !== null) {
            throw ValidationException::withMessages($violation);
        }
    }

    public static function violation(array $policy, array $exposure, float $amountDelta, int $countDelta): ?array
    {
        if ($policy['count_limit'] > 0 && $exposure['pawn_count'] + $countDelta > $policy['count_limit']) {
            return ['customer_nic' => 'Customer pawn count limit exceeded.'];
        }
        if ($policy['amount_limit'] > 0 && $exposure['pawn_amount'] + $amountDelta > $policy['amount_limit'] + 0.009) {
            return ['amount' => 'Customer pawn amount limit exceeded.'];
        }
        return null;
    }

    public function updateIdentityPolicy(string $nic, int $status, ?float $amountLimit, ?int $countLimit): void
    {
        // Explicit editing replaces conflicting legacy/branch policy snapshots.
        Customer::where('NIC', trim($nic))
            ->update([
                'Status' => $status,
                'Limit_Amount' => $amountLimit,
                'Limit_Pawn_Count' => $countLimit,
            ]);
    }

    public static function key(string $nic): string
    {
        return mb_strtoupper(trim($nic));
    }
}
