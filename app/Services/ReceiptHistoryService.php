<?php

namespace App\Services;

use App\Models\ArrearsLetterEvent;
use App\Models\Customer;
use App\Models\ForfeitReminderPromise;
use App\Models\MPawnfeedback;
use App\Models\ReceiptLifecycleEvent;
use App\Models\TForfeitSum;
use App\Models\TPawnDetails;
use App\Models\TPawnPayment;
use App\Models\TPawnSum;
use App\Models\TRedeemSum;
use App\Models\TRepawningSum;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReceiptHistoryService
{
    private array $operatorProfiles = [];
    public function __construct(private ReceiptFinancialCalculator $calculator) {}

    public function build(TPawnSum $receipt): array
    {
        $customer = Customer::where('NIC', $receipt->Customer_NIC)
            ->where(function ($query) use ($receipt) {
                $query->where('BC', $receipt->BC)->orWhereNull('BC');
            })->first();

        $details = TPawnDetails::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $transactions = DB::table('t_pawn_trans')
            ->where('code', $receipt->Receipt_Number)->where('BC', $receipt->BC)
            ->orderByDesc('dDate')->orderByDesc('id')->get();
        $payments = TPawnPayment::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $redeems = TRedeemSum::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $repawns = TRepawningSum::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $feedbacks = MPawnfeedback::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $forfeits = TForfeitSum::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();

        $letters = Schema::hasTable('arrears_letter_events')
            ? ArrearsLetterEvent::where('pawn_sum_id', $receipt->id)->orderByDesc('issued_at')->get()
            : collect();
        $promises = Schema::hasTable('forfeit_reminder_promises')
            ? ForfeitReminderPromise::where('pawn_sum_id', $receipt->id)->orderByDesc('created_at')->get()
            : collect();
        $lifecycle = Schema::hasTable('receipt_lifecycle_events')
            ? ReceiptLifecycleEvent::where('pawn_sum_id', $receipt->id)->orderByDesc('event_date')->get()
            : collect();

        return [
            'receipt' => $receipt,
            'customer' => $customer,
            'details' => $details,
            'financial' => $this->calculator->calculate($receipt),
            'timeline' => $this->timeline($receipt, $transactions, $payments, $redeems, $repawns, $feedbacks, $forfeits, $letters, $promises, $lifecycle),
        ];
    }

    public function appendChargeRows(Collection $rows, TPawnSum $receipt): Collection
    {
        $chargeRows = collect();
        $base = [
            'code' => $receipt->Receipt_Number, 'payable_total' => 0,
            'Paided_Interest' => 0, 'interest_Balance' => 0,
            'Paided_Captional' => 0, 'Cr_amount' => 0, 'Dr_amount' => 0,
            'Extend_Date' => null, 'letter_1_date' => null,
            'letter_2_date' => null, 'letter_3_date' => null,
        ];

        foreach (TPawnPayment::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $receipt->BC)->get() as $payment) {
            if ((float) $payment->Document_Charges > 0) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $payment->Redeem_Date, 'trans_type' => 'SERVICE CHARGE',
                    'trans_amount' => (float) $payment->Document_Charges, 'Postage_charge' => 0,
                ]));
            }
        }
        foreach (TRedeemSum::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $receipt->BC)->get() as $payment) {
            if ((float) $payment->Document_Charges > 0) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $payment->Redeem_Date, 'trans_type' => 'SERVICE CHARGE',
                    'trans_amount' => (float) $payment->Document_Charges, 'Postage_charge' => 0,
                ]));
            }
        }
        foreach (TRepawningSum::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $receipt->BC)->get() as $payment) {
            if ((float) $payment->Document_Charges > 0) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $payment->Redeem_Date, 'trans_type' => 'SERVICE CHARGE',
                    'trans_amount' => (float) $payment->Document_Charges, 'Postage_charge' => 0,
                ]));
            }
        }

        if (Schema::hasTable('arrears_letter_events')) {
            foreach (ArrearsLetterEvent::where('pawn_sum_id', $receipt->id)->get() as $letter) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $letter->issued_at, 'trans_type' => 'LETTER '.$letter->letter_no.' CHARGE',
                    'trans_amount' => (float) $letter->postage_charge,
                    'Postage_charge' => (float) $letter->postage_charge,
                ]));
            }
        } else {
            foreach ([1, 2, 3] as $letterNo) {
                $date = $receipt->{"letter_{$letterNo}_date"};
                $amount = (float) $receipt->{['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$letterNo - 1]};
                if ($date) {
                    $chargeRows->push((object) array_merge($base, [
                        'dDate' => $date, 'trans_type' => 'LETTER '.$letterNo.' CHARGE',
                        'trans_amount' => $amount, 'Postage_charge' => $amount,
                    ]));
                }
            }
        }

        return $rows->concat($chargeRows)->sortByDesc(function ($row) {
            return sprintf('%s-%010d', (string) ($row->dDate ?? ''), (int) ($row->id ?? 0));
        })->values();
    }

    private function timeline(
        TPawnSum $receipt,
        Collection $transactions,
        Collection $payments,
        Collection $redeems,
        Collection $repawns,
        Collection $feedbacks,
        Collection $forfeits,
        Collection $letters,
        Collection $promises,
        Collection $lifecycle
    ): Collection {
        $events = collect();
        $names = $transactions->concat($payments)->concat($redeems)->concat($repawns)->concat($feedbacks)->concat($forfeits)
            ->map(fn ($row) => $row->OC ?? null)
            ->concat($letters->pluck('issued_by'))->concat($promises->pluck('created_by'))
            ->concat($promises->pluck('updated_by'))->concat($lifecycle->pluck('created_by'))
            ->filter()->unique()->values();
        // Resolve only recorded operators; never attribute old events to the viewer.
        $this->operatorProfiles = $names->isEmpty() ? [] : User::whereIn('username', $names)
            ->get(['username', 'name', 'role', 'Branch', 'BC'])
            ->mapWithKeys(fn ($user) => [$user->username.'|'.$user->BC => $user])->all();

        foreach ($transactions as $row) {
            $events->push($this->event($row->dDate ?? $row->created_at, $row->trans_type ?: 'TRANSACTION', (float) ($row->payable_total ?? $row->trans_amount ?? 0), [
                'Paid interest' => $row->Paided_Interest ?? 0,
                'Paid capital' => $row->Paided_Captional ?? 0,
                'Balance interest' => $row->interest_Balance ?? 0,
                'Extended date' => $row->Extend_Date ?? null,
            ], $row->id ?? 0, $row->OC ?? null, $receipt->BC));
        }

        foreach ($payments as $row) {
            if ((float) $row->Document_Charges > 0) {
                $events->push($this->event($row->Redeem_Date, 'SERVICE CHARGE', (float) $row->Document_Charges, ['Payment no' => $row->Redeem_Number], 0, $row->OC, $receipt->BC));
            }
        }
        foreach ($redeems as $row) {
            if ((float) $row->Document_Charges > 0) {
                $events->push($this->event($row->Redeem_Date, 'SERVICE CHARGE', (float) $row->Document_Charges, ['Redeem no' => $row->Redeem_Number], 0, $row->OC, $receipt->BC));
            }
        }
        foreach ($repawns as $row) {
            if ((float) $row->Document_Charges > 0) {
                $events->push($this->event($row->Redeem_Date, 'SERVICE CHARGE', (float) $row->Document_Charges, ['Repawn no' => $row->Redeem_Number], 0, $row->OC, $receipt->BC));
            }
        }

        if ($letters->isNotEmpty()) {
            foreach ($letters as $letter) {
                $events->push($this->event($letter->issued_at, "LETTER {$letter->letter_no} CHARGE", (float) $letter->postage_charge, [
                    'Due date' => optional($letter->due_date)->toDateString(),
                    'Issued by' => $letter->issued_by,
                ], $letter->id, $letter->issued_by, $receipt->BC));
            }
        } else {
            foreach ([1, 2, 3] as $letterNo) {
                $date = $receipt->{"letter_{$letterNo}_date"};
                $amount = (float) $receipt->{['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$letterNo - 1]};
                if ($date) {
                    $events->push($this->event($date, "LETTER {$letterNo} CHARGE", $amount));
                }
            }
        }

        foreach ($promises as $promise) {
            $events->push($this->event($promise->created_at, 'PAYMENT PROMISE', null, [
                'Promise date' => optional($promise->promise_date)->toDateString(),
                'Remark' => $promise->remark,
                'Status' => $promise->status,
                'Last updated by' => $promise->updated_by ? $this->operatorLabel($promise->updated_by, $receipt->BC) : null,
            ], $promise->id, $promise->created_by, $receipt->BC));
        }
        foreach ($feedbacks as $feedback) {
            $events->push($this->event($feedback->Current_date ?? $feedback->created_at, 'CUSTOMER REMARK', null, ['Remark' => $feedback->feedback], $feedback->id, $feedback->OC, $receipt->BC));
        }
        foreach ($forfeits as $forfeit) {
            $events->push($this->event($forfeit->Forfeit_Date ?? $forfeit->created_at, 'FORFEITED', (float) $forfeit->Payable_Total, ['Forfeit no' => $forfeit->Forfeit_Number], 0, $forfeit->OC, $receipt->BC));
        }
        foreach ($lifecycle as $row) {
            $event = $this->event($row->event_date, str_replace('_', ' ', $row->event_type), $row->amount !== null ? (float) $row->amount : null, ['Description' => $row->description], $row->id, $row->created_by, $receipt->BC);
            if ($row->event_type === 'ARTICLES_TRANSFERRED_TO_STOCK') {
                $event['print_url'] = route('forfeit.articles.transfer.print', ['events'=>[$row->id]]);
            }
            $events->push($event);
        }

        return $events->sortByDesc(fn ($event) => sprintf('%s-%010d', $event['sort_date'], $event['sequence']))->values();
    }

    private function operatorLabel(?string $username, ?string $branch): string
    {
        if (!$username) return 'Not recorded';
        $profile = $this->operatorProfiles[$username.'|'.$branch] ?? null;
        return implode(' · ', array_filter([$username, $profile?->name, $profile?->role, $profile?->Branch, $branch ? 'Branch '.$branch : null]));
    }

    private function event(mixed $date, string $type, ?float $amount, array $details = [], int $sequence = 0, ?string $operator = null, ?string $branch = null): array
    {
        $dateString = $date ? (string) $date : '';
        $details['Operator'] = $this->operatorLabel($operator, $branch);

        return [
            'date' => $dateString,
            'sort_date' => $dateString,
            'sequence' => $sequence,
            'type' => $type,
            'amount' => $amount,
            'details' => array_filter($details, fn ($value) => $value !== null && $value !== ''),
        ];
    }

    public function enrichWithRemainingAmounts(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $today = now()->startOfDay();

        // Sort chronologically (oldest to newest) to determine sequential remaining balances and next transaction dates
        $sorted = $rows->sortBy(function ($r) {
            $date = $r->dDate ?? $r->Pawn_Date ?? '1970-01-01';
            $id = $r->id ?? 0;
            return sprintf('%s-%010d', $date, $id);
        })->values();

        $count = $sorted->count();
        $enriched = [];
        $runningCapital = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $row = $sorted[$i];
            $type = strtoupper((string) ($row->trans_type ?? ''));

            if ($type === 'REDEEM') {
                $row->remaining_capital = 0.00;
                $row->days_count = 0;
                $row->interest_amount = 0.00;
                $row->remaining_total = 0.00;
                $row->remaining_display = '<strong>Rs. 0.00</strong><br><small class="text-success">Redeemed</small>';
                $enriched[$i] = $row;
                continue;
            }

            if (str_contains($type, 'CHARGE')) {
                $row->remaining_capital = 0.00;
                $row->days_count = 0;
                $row->interest_amount = 0.00;
                $row->remaining_total = 0.00;
                $row->remaining_display = '<span class="text-muted">-</span>';
                $enriched[$i] = $row;
                continue;
            }

            // Determine remaining capital after this transaction
            if ($type === 'PAWN') {
                $runningCapital = (float) ($row->trans_pawn_amount ?? $row->Pawn_Amount ?? $row->Cr_amount ?? $row->Amount ?? 0);
            } elseif ($type === 'PART_PAYMENT') {
                $paidCap = (float) ($row->Paided_Captional ?? 0);
                if ($runningCapital > 0) {
                    $runningCapital = max(0, $runningCapital - $paidCap);
                } else {
                    $basePawn = (float) ($row->trans_pawn_amount ?? $row->Pawn_Amount ?? 0);
                    $runningCapital = max(0, $basePawn - $paidCap);
                }
            } elseif ($type === 'REPAWNING') {
                $runningCapital = (float) ($row->RePawning_amount ?? $row->sum_pawn_amount ?? ((float) ($row->trans_pawn_amount ?? $row->Pawn_Amount ?? 0) + (float) ($row->Cr_amount ?? 0)));
            } else {
                $runningCapital = (float) ($row->trans_pawn_amount ?? $row->Pawn_Amount ?? 0);
            }

            $remCapital = $runningCapital;

            // Find next loan transaction date (chronologically after this one)
            $nextDate = null;
            for ($j = $i + 1; $j < $count; $j++) {
                $nextType = strtoupper((string) ($sorted[$j]->trans_type ?? ''));
                if (!str_contains($nextType, 'CHARGE') && !empty($sorted[$j]->dDate)) {
                    $nextDate = \Carbon\Carbon::parse($sorted[$j]->dDate)->startOfDay();
                    break;
                }
            }

            $startDate = !empty($row->dDate) ? \Carbon\Carbon::parse($row->dDate)->startOfDay() : $today;
            $endDate = $nextDate ?? $today;
            $days = max(0, $startDate->diffInDays($endDate, false) + 1);

            // Interest calculation using receipt rate configuration
            $receiptName = strtoupper((string) ($row->receiptname ?? $row->Receipt_Type ?? ''));
            $rate1 = (float) ($row->rate1 ?? 1.68);
            $rate2 = (float) ($row->rate2 ?? 2.00);
            $rate3 = (float) ($row->rate3 ?? 2.50);
            $period1 = (int) ($row->period1 ?? 7);
            $period2 = (int) ($row->period2 ?? 14);
            $validPeriod = (int) ($row->validPeriod ?? $row->Valid_Period ?? 0);

            $interest = 0.0;
            if ($remCapital > 0 && $days > 0) {
                if ($receiptName === 'SILVER') {
                    $months = (int) ceil($days / 30);
                    $interest = ($remCapital / 100) * $rate1 * $months;
                } elseif ($receiptName === 'D' && $validPeriod > 0 && $days > $validPeriod) {
                    $months = (int) ceil($days / 30);
                    $penaltyMonths = (int) ceil(($days - $validPeriod) / 30);
                    $interest = (($remCapital / 100) * $rate2 * $months) + (($remCapital / 100) * 0.5 * $penaltyMonths);
                } elseif ($days <= $period1) {
                    $interest = ($remCapital / 100) * $rate1;
                } elseif ($days <= $period2) {
                    $interest = ($remCapital / 100) * $rate2;
                } elseif ($days <= 30) {
                    $interest = ($remCapital / 100) * $rate3;
                } else {
                    $fullMonths = intdiv($days, 30);
                    $remainingDays = $days % 30;
                    $totalRate = ($rate3 * $fullMonths) + (($rate3 / 30) * $remainingDays);
                    $interest = ($remCapital / 100) * $totalRate;
                }
            }

            $total = $remCapital + $interest;

            $row->remaining_capital = round($remCapital, 2);
            $row->days_count = $days;
            $row->interest_amount = round($interest, 2);
            $row->remaining_total = round($total, 2);
            $row->remaining_display = '<strong>Rs. ' . number_format($total, 2) . '</strong><br><small class="text-muted">Capital: ' . number_format($remCapital, 2) . ' + Interest: ' . number_format($interest, 2) . ' (' . $days . ' days)</small>';

            $enriched[$i] = $row;
        }

        // Return rows in descending order
        return collect($enriched)->sortByDesc(function ($r) {
            $date = $r->dDate ?? $r->Pawn_Date ?? '1970-01-01';
            $id = $r->id ?? 0;
            return sprintf('%s-%010d', $date, $id);
        })->values();
    }
}
