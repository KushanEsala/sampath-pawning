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
    public function __construct(
        private ReceiptFinancialCalculator $calculator,
        private ?ReceiptTypeResolver $receiptTypeResolver = null
    ) {}

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

        $calculationReceipt = ($this->receiptTypeResolver ??= app(ReceiptTypeResolver::class))
            ->receiptForCalculation($receipt);

        $timeline = $this->timeline($receipt, $transactions, $payments, $redeems, $repawns, $feedbacks, $forfeits, $letters, $promises, $lifecycle);
        $ledger = $this->mergeNonFinancialActivity($this->paymentLedger($receipt), $timeline);

        return [
            'receipt' => $receipt,
            'customer' => $customer,
            'details' => $details,
            'financial' => $this->calculator->calculate($calculationReceipt),
            'ledger' => $ledger,
            'timeline' => $timeline,
        ];
    }

    /**
     * Return one consistent ledger for Receipt Search and every payment page.
     * Rows are newest first, but balances are calculated oldest first so the
     * displayed DR/CR movement always reconciles to the corrected balance.
     */
    public function paymentLedger(TPawnSum $receipt): Collection
    {
        $rows = DB::table('t_pawn_trans as trans')
            ->join('t_pawn_sums as sum', 'trans.code', '=', 'sum.Receipt_Number')
            ->where('trans.code', $receipt->Receipt_Number)
            ->where('trans.BC', $receipt->BC)
            ->where('sum.BC', $receipt->BC)
            ->select(
                'sum.*', 'trans.*',
                'sum.Pawn_Amount as sum_pawn_amount',
                'trans.Pawn_Amount as trans_pawn_amount',
                'sum.Amount as sum_original_amount'
            )
            ->orderByDesc('trans.dDate')
            ->orderByDesc('trans.id')
            ->get();

        $redeems = TRedeemSum::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)->get();
        $rows = $this->attachRedemptionInterest($rows, $redeems, $receipt);
        $rows = $this->attachPaymentAndRepawnInterest($rows, $receipt);
        $rows = $this->appendChargeRows($rows, $receipt);
        $rows = $this->enrichWithRemainingAmounts($rows, $receipt);

        return $this->formatLedgerRows($rows, $receipt);
    }

    /** Use the redemption summary's posted breakdown or pawn sum / calculator fallback for transactions. */
    public function attachRedemptionInterest(Collection $rows, Collection $redeems, ?TPawnSum $receipt = null): Collection
    {
        $byEvent = $redeems->groupBy(fn ($redeem) => $this->redemptionKey(
            $redeem->Redeem_Number ?? null, $redeem->Redeem_Date ?? null
        ));

        foreach ($rows as $row) {
            if (strtoupper(trim((string) ($row->trans_type ?? ''))) !== 'REDEEM') {
                continue;
            }

            // If the transaction row already has non-zero Paided_Interest
            if ($row->Paided_Interest !== null && (float) $row->Paided_Interest > 0) {
                $row->redeem_interest_conflict = false;
                // If a matching summary exists, attach discount if any
                $matches = $byEvent->get($this->redemptionKey($row->trans_no ?? null, $row->dDate ?? null), collect());
                if ($matches->count() === 1) {
                    $summary = $matches->first();
                    if (!empty($summary->Discount)) {
                        $row->Discount = (float) $summary->Discount;
                    }
                    if ($summary->Paid_Interest !== null && abs((float) $row->Paided_Interest - (float) $summary->Paid_Interest) > 0.01) {
                        $row->redeem_interest_conflict = true;
                    }
                }
                continue;
            }

            // Tier 1: Match summary by Redeem_Number and date
            $matches = $byEvent->get($this->redemptionKey($row->trans_no ?? null, $row->dDate ?? null), collect());
            if ($matches->count() === 1) {
                $summary = $matches->first();
                if ($summary->Paid_Interest !== null) {
                    $transactionInterest = $row->Paided_Interest ?? null;
                    $row->redeem_interest_conflict = $transactionInterest !== null
                        && abs((float) $transactionInterest - (float) $summary->Paid_Interest) > 0.01;
                    $row->Paided_Interest = (float) $summary->Paid_Interest;
                    if (!empty($summary->Discount)) {
                        $row->Discount = (float) $summary->Discount;
                    }
                    continue;
                }
            }

            // Tier 2: Check Redeem_interest from TPawnSum
            if ($receipt && (float) ($receipt->Redeem_interest ?? 0) > 0) {
                $row->Paided_Interest = (float) $receipt->Redeem_interest;
                continue;
            }

            // Tier 3: If single summary exists for this receipt
            if ($redeems->count() === 1 && $redeems->first()->Paid_Interest !== null) {
                $single = $redeems->first();
                $row->Paided_Interest = (float) $single->Paid_Interest;
                if (!empty($single->Discount)) {
                    $row->Discount = (float) $single->Discount;
                }
                continue;
            }

            // Tier 4: Calculate dynamically using receipt rates as of redemption date
            if ($receipt) {
                $calc = $this->calculator->calculate($receipt, $row->dDate ?? null);
                if (($calc['interest'] ?? 0) > 0) {
                    $row->Paided_Interest = (float) $calc['interest'];
                }
            }
        }

        return $rows;
    }

    /** Attach interest for historical part payments and repawnings if missing on trans row. */
    public function attachPaymentAndRepawnInterest(Collection $rows, TPawnSum $receipt): Collection
    {
        $payments = null;
        $repawns = null;

        foreach ($rows as $row) {
            $type = strtoupper(trim((string) ($row->trans_type ?? '')));
            if ($type === 'PART_PAYMENT' && ($row->Paided_Interest === null || (float) $row->Paided_Interest <= 0)) {
                $payments ??= TPawnPayment::where('Receipt_Number', $receipt->Receipt_Number)
                    ->where('BC', $receipt->BC)->get();
                $matched = $payments->first(fn ($p) => (string) ($p->Redeem_Number ?? '') === (string) ($row->trans_no ?? '')
                    || substr((string) ($p->Redeem_Date ?? ''), 0, 10) === substr((string) ($row->dDate ?? ''), 0, 10));
                if ($matched && $matched->Paid_Interest !== null) {
                    $row->Paided_Interest = (float) $matched->Paid_Interest;
                }
            } elseif ($type === 'REPAWNING' && ($row->Paided_Interest === null || (float) $row->Paided_Interest <= 0)) {
                $repawns ??= TRepawningSum::where('Receipt_Number', $receipt->Receipt_Number)
                    ->where('BC', $receipt->BC)->get();
                $matched = $repawns->first(fn ($r) => (string) ($r->Repawn_Number ?? '') === (string) ($row->trans_no ?? '')
                    || substr((string) ($r->Redeem_Date ?? ''), 0, 10) === substr((string) ($row->dDate ?? ''), 0, 10));
                if ($matched && $matched->Paid_Interest !== null) {
                    $row->Paided_Interest = (float) $matched->Paid_Interest;
                }
            }
        }

        return $rows;
    }

    private function redemptionKey(mixed $number, mixed $date): string
    {
        $number = trim((string) $number);
        if (ctype_digit($number)) {
            $number = ltrim($number, '0') ?: '0';
        }

        return $number.'|'.substr((string) $date, 0, 10);
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
                    'OC' => $payment->OC,
                ]));
            }
        }
        foreach (TRedeemSum::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $receipt->BC)->get() as $payment) {
            if ((float) $payment->Document_Charges > 0) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $payment->Redeem_Date, 'trans_type' => 'SERVICE CHARGE',
                    'trans_amount' => (float) $payment->Document_Charges, 'Postage_charge' => 0,
                    'OC' => $payment->OC,
                ]));
            }
        }
        foreach (TRepawningSum::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $receipt->BC)->get() as $payment) {
            if ((float) $payment->Document_Charges > 0) {
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $payment->Redeem_Date, 'trans_type' => 'SERVICE CHARGE',
                    'trans_amount' => (float) $payment->Document_Charges, 'Postage_charge' => 0,
                    'OC' => $payment->OC,
                ]));
            }
        }

        $letterEvents = collect();
        if (Schema::hasTable('arrears_letter_events')) {
            $letterEvents = ArrearsLetterEvent::where('pawn_sum_id', $receipt->id)->get();
        }

        if ($letterEvents->isNotEmpty()) {
            foreach ($letterEvents as $letter) {
                $charge = (float) $letter->postage_charge;
                $dateStr = $letter->issued_at instanceof \Carbon\Carbon ? $letter->issued_at->toDateString() : substr((string) $letter->issued_at, 0, 10);
                $chargeRows->push((object) array_merge($base, [
                    'dDate' => $dateStr,
                    'trans_type' => 'LETTER '.$letter->letter_no.' CHARGE',
                    'trans_amount' => $charge,
                    'Postage_charge' => $charge,
                    'letter_sent' => $this->ordinal($letter->letter_no).' Letter',
                    'letter_charge' => $charge,
                    'OC' => $letter->issued_by,
                ]));
            }
        } else {
            foreach ([1, 2, 3] as $letterNo) {
                $date = $receipt->{"letter_{$letterNo}_date"};
                if ($date) {
                    $charge = $this->calculator->resolveLetterCharge($receipt, $letterNo);
                    $dateStr = $date instanceof \Carbon\Carbon ? $date->toDateString() : substr((string) $date, 0, 10);
                    $chargeRows->push((object) array_merge($base, [
                        'dDate' => $dateStr,
                        'trans_type' => 'LETTER '.$letterNo.' CHARGE',
                        'trans_amount' => $charge,
                        'Postage_charge' => $charge,
                        'letter_sent' => $this->ordinal($letterNo).' Letter',
                        'letter_charge' => $charge,
                        'OC' => $receipt->OC,
                    ]));
                }
            }
        }

        return $rows->concat($chargeRows)->sortByDesc(function ($row) {
            return sprintf('%s-%010d', (string) ($row->dDate ?? ''), (int) ($row->id ?? 0));
        })->values();
    }

    private function formatLedgerRows(Collection $rows, TPawnSum $receipt): Collection
    {
        $chronological = $rows->sortBy(function ($row) {
            return sprintf('%s-%010d', (string) ($row->dDate ?? ''), (int) ($row->id ?? 0));
        })->values();

        $names = $chronological->pluck('OC')->filter()->unique()->values();
        if ($names->isNotEmpty()) {
            $profiles = User::whereIn('username', $names)
                ->get(['username', 'name', 'role', 'Branch', 'BC'])
                ->mapWithKeys(fn ($user) => [$user->username.'|'.$user->BC => $user])
                ->all();
            $this->operatorProfiles = array_merge($this->operatorProfiles, $profiles);
        }

        $balance = 0.0;
        $ledger = collect();
        $receiptReference = 'Receipt #'.$receipt->Receipt_Number.' / Stock #'.($receipt->Invoice_Number ?: '—');
        $latestLoanRow = $chronological->last(function ($row) {
            $type = strtoupper(trim((string) ($row->trans_type ?? '')));
            return !str_contains($type, 'CHARGE');
        });
        $latestLoanIdentity = is_object($latestLoanRow) ? spl_object_id($latestLoanRow) : null;

        $append = function (
            string $date,
            string $description,
            array $details,
            float $debit,
            float $credit,
            string $type,
            string $operator
        ) use (&$balance, $ledger): void {
            $debit = round(max(0, $debit), 2);
            $credit = round(max(0, $credit), 2);
            // Keep the signed ledger balance. A negative value is meaningful:
            // it exposes an overpayment/credit balance instead of silently
            // forcing the account back to zero.
            $balance = round($balance + $debit - $credit, 2);
            $ledger->push([
                'date' => $date,
                'description' => $description,
                'summary' => $this->ledgerSummary($type, $details, $debit, $credit),
                'details' => $details,
                'dr' => $debit,
                'cr' => $credit,
                'balance' => $balance,
                'type' => $type,
                'operator' => $operator,
            ]);
        };

        foreach ($chronological as $row) {
            $type = strtoupper(trim((string) ($row->trans_type ?? 'TRANSACTION')));
            $isCharge = str_contains($type, 'CHARGE');
            $date = substr((string) ($row->dDate ?? ''), 0, 10);
            $operator = $this->operatorLabel($row->OC ?? null, $receipt->BC);
            [$description, $details] = $this->ledgerDescription($row, $type, $receipt);

            if ($isCharge) {
                $append($date, $description, $details,
                    (float) ($row->trans_amount ?? $row->Postage_charge ?? 0), 0, $type, $operator);
                continue;
            }

            // Historical interest must use the amount actually recorded at
            // that payment/repawn event. Recalculating old periods with today's
            // master rates creates artificial differences even after the
            // underlying principal snapshots have been repaired.
            $recordedInterest = in_array($type, ['PART_PAYMENT', 'REPAWNING', 'REDEEM'], true)
                ? max(0, (float) ($row->Paided_Interest ?? 0))
                : 0.0;
            if ($recordedInterest >= 0.01) {
                $append(
                    $date,
                    'Interest charged',
                    array_filter([
                        'Recorded with '.$description,
                        $row->rate_configuration ?? null,
                        $receiptReference,
                    ]),
                    $recordedInterest,
                    0,
                    'INTEREST_CHARGE',
                    $operator
                );
            }

            $debit = 0.0;
            $credit = 0.0;
            if ($type === 'PAWN') {
                $debit = max(0, (float) ($row->Cr_amount ?: $row->trans_amount ?: $row->trans_pawn_amount ?: 0));
            } elseif ($type === 'REPAWNING') {
                $debit = max(0, (float) ($row->Cr_amount ?: 0));
            } elseif (in_array($type, ['PART_PAYMENT', 'REDEEM'], true)) {
                $credit = max(0, (float) ($row->payable_total ?: $row->Dr_amount ?: 0));
            } else {
                $debit = max(0, (float) ($row->Cr_amount ?? 0));
                $credit = max(0, (float) ($row->Dr_amount ?? 0));
            }

            if ($type === 'REDEEM') {
                $discount = max(0, (float) ($row->Discount ?? 0));
                if ($discount >= 0.01) {
                    $append(
                        $date,
                        'Discount allowed',
                        array_filter([
                            'Discount allowed on redemption: Rs. '.number_format($discount, 2),
                            $receiptReference,
                        ]),
                        0,
                        $discount,
                        'DISCOUNT',
                        $operator
                    );
                }

                // If customer cash payment is rounded up to the nearest rupee:
                $cashRounding = round($credit - $balance, 2);
                if ($cashRounding > 0.001 && $cashRounding < 5.00) {
                    $append(
                        $date,
                        'Cash rounding',
                        array_filter([
                            'Rounded to whole rupee for cash collection (+Rs. '.number_format($cashRounding, 2).')',
                            $receiptReference,
                        ]),
                        $cashRounding,
                        0,
                        'ROUNDING_ADJUSTMENT',
                        $operator
                    );
                }
            }

            $append($date, $description, $details, $debit, $credit, $type, $operator);

            if ($type === 'REDEEM' && abs($balance) >= 0.01) {
                // Ensure a redeemed receipt balance always settles cleanly to 0.00
                $settlementDiff = round($balance, 2);
                if ($settlementDiff > 0) {
                    $append(
                        $date,
                        'Redemption settlement',
                        array_filter([
                            'Settlement of loan account on redemption',
                            $receiptReference,
                        ]),
                        0,
                        $settlementDiff,
                        'SETTLEMENT_ADJUSTMENT',
                        $operator
                    );
                } elseif ($settlementDiff < 0) {
                    $append(
                        $date,
                        'Redemption settlement',
                        array_filter([
                            'Settlement of loan account on redemption',
                            $receiptReference,
                        ]),
                        abs($settlementDiff),
                        0,
                        'SETTLEMENT_ADJUSTMENT',
                        $operator
                    );
                }
            }

            // Only the open period after the latest transaction is calculated
            // dynamically. All closed historical periods use recorded values.
            $isLatestLoanRow = $latestLoanIdentity !== null && spl_object_id($row) === $latestLoanIdentity;
            $currentInterest = $type === 'REDEEM' || !$isLatestLoanRow
                ? 0.0
                : max(0, (float) ($row->interest_amount ?? 0));
            if ($currentInterest >= 0.01) {
                $days = (int) ($row->days_count ?? 0);
                $append(
                    substr((string) ($row->interest_to_date ?? $date), 0, 10),
                    'Interest accrued',
                    [
                        $days.' day'.($days === 1 ? '' : 's').' in the current open period',
                        'Capital used: Rs. '.number_format((float) ($row->remaining_capital ?? 0), 2),
                        ($row->rate_type_name ?? 'Type unknown').' · '.($row->rate_configuration ?? 'Rate source unverified'),
                        $receiptReference,
                    ],
                    $currentInterest,
                    0,
                    'INTEREST_CHARGE',
                    'System calculation'
                );
            }
        }

        return $ledger->reverse()->values();
    }

    private function ledgerSummary(string $type, array $details, float $debit, float $credit): string
    {
        if ($type === 'PART_PAYMENT') {
            return collect($details)
                ->filter(fn ($detail) => str_starts_with($detail, 'Paid capital:') || str_starts_with($detail, 'Paid interest:'))
                ->implode(' • ');
        }

        return match (true) {
            $type === 'INTEREST_CHARGE' => 'Interest: Rs. '.number_format($debit, 2),
            $type === 'REPAWNING' => 'Repawn amount: Rs. '.number_format($debit, 2),
            $type === 'REDEEM' => 'Customer paid: Rs. '.number_format($credit, 2),
            $type === 'PAWN' => 'Capital: Rs. '.number_format($debit, 2),
            $type === 'ROUNDING_ADJUSTMENT' => 'Cash rounding: +Rs. '.number_format($debit, 2),
            $type === 'DISCOUNT' => 'Discount: Rs. '.number_format($credit, 2),
            $type === 'SETTLEMENT_ADJUSTMENT' => ($debit > 0 ? 'Adjustment: +Rs. ' : 'Adjustment: -Rs. ').number_format(max($debit, $credit), 2),
            str_contains($type, 'LETTER') => 'Postal charge: Rs. '.number_format($debit, 2),
            $type === 'SERVICE CHARGE' => 'Service charge: Rs. '.number_format($debit, 2),
            $debit > 0 => 'Debit: Rs. '.number_format($debit, 2),
            $credit > 0 => 'Credit: Rs. '.number_format($credit, 2),
            default => 'No financial movement',
        };
    }

    private function ledgerDescription(object $row, string $type, TPawnSum $receipt): array
    {
        $details = [];
        $description = match (true) {
            $type === 'PAWN' => 'Pawn receipt issued',
            $type === 'PART_PAYMENT' => 'Part payment received',
            $type === 'REPAWNING' => 'Receipt repawned',
            $type === 'REDEEM' => 'Receipt redeemed',
            $type === 'ROUNDING_ADJUSTMENT' => 'Cash rounding',
            $type === 'DISCOUNT' => 'Discount allowed',
            $type === 'SETTLEMENT_ADJUSTMENT' => 'Redemption settlement',
            str_contains($type, 'LETTER') => ($row->letter_sent ?? ucwords(strtolower($type))).' – postal charge',
            $type === 'SERVICE CHARGE' => 'Service charge',
            default => ucwords(strtolower(str_replace('_', ' ', $type))),
        };

        if ($type === 'PAWN') {
            $details[] = 'Principal: Rs. '.number_format((float) ($row->remaining_capital ?? $row->trans_pawn_amount ?? 0), 2);
        } elseif ($type === 'PART_PAYMENT') {
            $details[] = 'Paid capital: Rs. '.number_format((float) ($row->Paided_Captional ?? 0), 2);
            $details[] = 'Paid interest: Rs. '.number_format((float) ($row->Paided_Interest ?? 0), 2);
        } elseif ($type === 'REPAWNING') {
            $details[] = 'Opening principal: Rs. '.number_format((float) ($row->trans_pawn_amount ?? $row->Pawn_Amount ?? 0), 2);
            $details[] = 'Paid interest: Rs. '.number_format((float) ($row->Paided_Interest ?? 0), 2);
            $details[] = 'Additional amount issued: Rs. '.number_format((float) ($row->Cr_amount ?? 0), 2);
            $details[] = 'New principal: Rs. '.number_format((float) ($row->remaining_capital ?? 0), 2);
        } elseif ($type === 'REDEEM') {
            $details[] = 'Customer payment: Rs. '.number_format((float) ($row->payable_total ?: $row->Dr_amount ?: 0), 2);
            $details[] = 'Paid interest: Rs. '.number_format((float) ($row->Paided_Interest ?? 0), 2);
            if (!empty($row->redeem_interest_conflict)) {
                $details[] = 'Interest differs between transaction and redemption summary; summary used';
            }
        }

        $isCharge = str_contains($type, 'CHARGE');
        if (!$isCharge) {
            if (!empty($row->Extend_Date)) {
                $details[] = 'Expiry extended to '.substr((string) $row->Extend_Date, 0, 10);
            }
            if (!empty($row->rate_configuration)) {
                $rateText = $row->rate_1_used === null
                    ? ''
                    : ' ('.$row->rate_1_used.'% / '.$row->rate_2_used.'% / '.$row->rate_3_used.'%)';
                $details[] = 'Rate basis: '.($row->rate_type_name ?? 'Unknown').' · '.$row->rate_configuration.$rateText;
            }
        }

        $details[] = 'Receipt #'.$receipt->Receipt_Number.' / Stock #'.($receipt->Invoice_Number ?: '—');

        return [$description, $details];
    }

    private function mergeNonFinancialActivity(Collection $ledger, Collection $timeline): Collection
    {
        $financialTypes = ['PAWN', 'PART_PAYMENT', 'REPAWNING', 'REDEEM', 'SERVICE CHARGE'];

        $activities = $timeline->filter(function (array $event) use ($financialTypes) {
            $type = strtoupper((string) ($event['type'] ?? ''));
            return !in_array($type, $financialTypes, true) && !str_contains($type, 'LETTER');
        })->map(function (array $event) use ($ledger) {
            $date = substr((string) ($event['date'] ?? ''), 0, 10);
            $nearest = $ledger->first(fn (array $row) => ($row['date'] ?? '') <= $date) ?? $ledger->last();
            $details = collect($event['details'] ?? []);
            $operator = (string) ($details->pull('Operator') ?? 'Not recorded');

            return [
                'date' => $date,
                'description' => ucwords(strtolower(str_replace('_', ' ', (string) ($event['type'] ?? 'Activity')))),
                'details' => $details->map(fn ($value, $label) => $label.': '.$value)->values()->all(),
                'dr' => 0.0,
                'cr' => 0.0,
                'balance' => (float) ($nearest['balance'] ?? 0),
                'type' => strtoupper((string) ($event['type'] ?? 'ACTIVITY')),
                'operator' => $operator,
            ];
        });

        return $ledger->concat($activities)->sortByDesc(function (array $row) {
            return ($row['date'] ?? '').'|'.(($row['dr'] || $row['cr']) ? '1' : '0');
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

        $extraNames = collect();
        if ($letters->isEmpty()) {
            foreach ([1, 2, 3] as $letterNo) {
                $date = $receipt->{"letter_{$letterNo}_date"};
                if ($date) {
                    $op = $this->resolveLetterOperator($receipt, $letterNo, $date, $feedbacks);
                    if ($op) {
                        $extraNames->push($op);
                    }
                }
            }
        }

        $names = $transactions->concat($payments)->concat($redeems)->concat($repawns)->concat($feedbacks)->concat($forfeits)
            ->map(fn ($row) => $row->OC ?? null)
            ->concat($letters->pluck('issued_by'))->concat($promises->pluck('created_by'))
            ->concat($promises->pluck('updated_by'))->concat($lifecycle->pluck('created_by'))
            ->concat($extraNames)
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
                    'Letter' => $this->ordinal($letter->letter_no).' Letter',
                    'Due date' => optional($letter->due_date)->toDateString(),
                    'Issued by' => $letter->issued_by,
                ], $letter->id, $letter->issued_by, $receipt->BC));
            }
        } else {
            foreach ([1, 2, 3] as $letterNo) {
                $date = $receipt->{"letter_{$letterNo}_date"};
                if ($date) {
                    $amount = $this->calculator->resolveLetterCharge($receipt, $letterNo);
                    $dateStr = $date instanceof \Carbon\Carbon ? $date->toDateString() : substr((string) $date, 0, 10);
                    $op = $this->resolveLetterOperator($receipt, $letterNo, $dateStr, $feedbacks);
                    $events->push($this->event(
                        $dateStr,
                        "LETTER {$letterNo} CHARGE",
                        $amount,
                        [
                            'Letter' => $this->ordinal($letterNo).' Letter',
                        ],
                        0,
                        $op,
                        $receipt->BC
                    ));
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

    private function resolveLetterOperator(TPawnSum $receipt, int $letterNo, mixed $date, Collection $feedbacks): ?string
    {
        $dateStr = $date instanceof \Carbon\Carbon ? $date->toDateString() : substr((string) $date, 0, 10);
        $dateTime = strtotime($dateStr ?: '1970-01-01');

        // First try exact or near-date match (within 7 days) where feedback text mentions letter or post
        $match = $feedbacks->first(function ($f) use ($dateStr, $dateTime) {
            $fDate = substr((string) ($f->Current_date ?? $f->created_at ?? ''), 0, 10);
            $fText = strtoupper((string) ($f->feedback ?? ''));
            $isLetterFeedback = str_contains($fText, 'LETTER') || str_contains($fText, 'POST');
            if (!$isLetterFeedback) {
                return false;
            }
            if ($fDate === $dateStr) {
                return true;
            }
            $fTime = strtotime($fDate ?: '1970-01-01');
            return abs($fTime - $dateTime) <= 86400 * 7;
        });

        if ($match && !empty($match->OC)) {
            return $match->OC;
        }

        // Fallback: any letter/post feedback in chronological order for letter 1, 2, 3
        $letterFeedbacks = $feedbacks->filter(function ($f) {
            $fText = strtoupper((string) ($f->feedback ?? ''));
            return str_contains($fText, 'LETTER') || str_contains($fText, 'POST');
        })->sortBy('Current_date')->values();

        if ($letterFeedbacks->has($letterNo - 1) && !empty($letterFeedbacks[$letterNo - 1]->OC)) {
            return $letterFeedbacks[$letterNo - 1]->OC;
        }

        return $receipt->OC ?: null;
    }

    private function ordinal(int $num): string
    {
        return match ($num) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $num.'th',
        };
    }

    private function operatorLabel(?string $username, ?string $branch): string
    {
        if (!$username) return 'Not recorded';
        $profile = $this->operatorProfiles[$username.'|'.$branch] ?? null;
        return implode(' · ', array_filter([$username, $profile?->name, $profile?->role, $profile?->Branch, $branch ? 'Branch '.$branch : null]));
    }

    private function event(mixed $date, string $type, ?float $amount, array $details = [], int $sequence = 0, ?string $operator = null, ?string $branch = null): array
    {
        $dateString = '';
        if ($date instanceof \Carbon\Carbon) {
            $dateString = $date->format('Y-m-d');
        } elseif (!empty($date)) {
            try {
                $dateString = \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable) {
                $dateString = (string) $date;
            }
        }
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

    public function enrichWithRemainingAmounts(Collection $rows, ?TPawnSum $receipt = null): Collection
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

        if ($receipt && $receipt->Receipt_Number && $receipt->BC) {
            $this->attachHistoricalCapitalSnapshots($sorted, $receipt);
        }

        $count = $sorted->count();
        $enriched = [];
        $runningCapital = 0.0;
        $latestRepawnIndex = null;
        foreach ($sorted as $index => $item) {
            if (strtoupper(trim((string) ($item->trans_type ?? ''))) === 'REPAWNING') {
                $latestRepawnIndex = $index;
            }
        }
        // Repawning overwrites the parent type/rate snapshot. Article details
        // retain the original type, while the parent retains the latest cycle.
        $originalTypeFromDetails = $sorted->first(fn ($item) => strtoupper((string) ($item->trans_type ?? '')) === 'PAWN')
            ->original_receipt_type ?? null;
        if (!$originalTypeFromDetails && $receipt && $receipt->Receipt_Number && $receipt->BC) {
            $originalTypeFromDetails = TPawnDetails::where('Receipt_Number', $receipt->Receipt_Number)
                ->where('BC', $receipt->BC)->value('Receipt_Type');
        }
        $originalType = strtoupper(trim((string) ($originalTypeFromDetails ?: ($receipt?->Receipt_Type ?? ''))));
        $cycleConfig = null;
        $cycleSource = 'Transaction snapshot';

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
            if (isset($row->historical_capital) && is_numeric($row->historical_capital)) {
                $runningCapital = max(0, (float) $row->historical_capital);
            } elseif ($type === 'PAWN') {
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
            $nextTransaction = null;
            for ($j = $i + 1; $j < $count; $j++) {
                $nextType = strtoupper((string) ($sorted[$j]->trans_type ?? ''));
                if (!str_contains($nextType, 'CHARGE') && !empty($sorted[$j]->dDate)) {
                    $nextTransaction = $sorted[$j];
                    $nextDate = \Carbon\Carbon::parse($sorted[$j]->dDate)->startOfDay();
                    break;
                }
            }

            $startDate = !empty($row->dDate) ? \Carbon\Carbon::parse($row->dDate)->startOfDay() : $today;
            // A part payment closes the old period on its payment date. The
            // next interest cycle begins on the following day (as stored in
            // Pawn_Date), so history must use the same start as live payments.
            if ($type === 'PART_PAYMENT') {
                $startDate->addDay();
            }
            $endDate = $nextDate ?? $today;
            $days = max(0, $startDate->diffInDays($endDate, false) + 1);

            // Rates change at pawn/repawn boundaries, not at a part payment.
            // The current cycle uses its stored snapshot; older cycles use the
            // type version effective when that cycle began.
            if ($receipt && in_array($type, ['PAWN', 'REPAWNING'], true)) {
                $resolver = $this->receiptTypeResolver ??= app(ReceiptTypeResolver::class);
                $isCurrentCycle = $type === 'REPAWNING'
                    ? $i === $latestRepawnIndex
                    : $latestRepawnIndex === null;
                if ($isCurrentCycle && $receipt->rate1 !== null) {
                    $cycleConfig = $resolver->resolveForReceipt($receipt);
                    $cycleSource = 'Stored receipt rate snapshot';
                } elseif ($type === 'PAWN') {
                    $cycleConfig = $resolver->resolveByDate($originalType, $startDate);
                    $cycleSource = $latestRepawnIndex !== null && !$originalTypeFromDetails
                        ? 'Original rate unverified: original type was not retained in article details'
                        : ($cycleConfig ? 'Receipt type version #'.$cycleConfig->id : 'Historical type unavailable');
                } else {
                    $specialType = in_array($originalType, ['SILVER', 'D'], true) ? $originalType : null;
                    $cycleConfig = $specialType
                        ? $resolver->resolveByDate($specialType, $startDate)
                        : $resolver->resolveForRepawnAmount($remCapital, $startDate);
                    $cycleSource = $cycleConfig ? 'Receipt type version #'.$cycleConfig->id : 'Historical type unavailable';
                }
            }
            $rateConfig = $cycleConfig;
            $unverifiedHistoricalRate = str_starts_with($cycleSource, 'Original rate unverified')
                || ($rateConfig && $cycleSource !== 'Stored receipt rate snapshot'
                    && (($rateConfig->updated_at && $rateConfig->updated_at->greaterThan($startDate->copy()->endOfDay()))
                        || ($rateConfig->created_at && $rateConfig->created_at->greaterThan($startDate->copy()->endOfDay()))
                        || ($rateConfig->effective_from && $rateConfig->effective_from->greaterThan($startDate))));
            if ($unverifiedHistoricalRate) {
                $cycleSource = 'Original rate unverified: dated master record does not prove the rate at this event';
            }
            $receiptName = strtoupper((string) ($rateConfig?->receiptname ?? $row->receiptname ?? $row->Receipt_Type ?? $originalType));
            $rate1 = (float) ($rateConfig?->rate1 ?? $row->rate1 ?? 1.68);
            $rate2 = (float) ($rateConfig?->rate2 ?? $row->rate2 ?? 2.00);
            $rate3 = (float) ($rateConfig?->rate3 ?? $row->rate3 ?? 2.50);
            $period1 = (int) ($rateConfig?->period1 ?? $row->period1 ?? 7);
            $period2 = (int) ($rateConfig?->period2 ?? $row->period2 ?? 14);
            $period3 = (int) ($rateConfig?->period3 ?? $row->period3 ?? 30);
            $validPeriod = (int) ($rateConfig?->validPeriod ?? $row->validPeriod ?? $row->Valid_Period ?? 0);

            // A closed period is a posted financial fact, not a projection.
            // Never silently reprice it using a master row that may have been
            // edited after the transaction. The next event records what was
            // actually charged/paid; only the open period is calculated.
            $interest = $nextTransaction
                ? max(0, (float) ($nextTransaction->Paided_Interest ?? 0))
                : 0.0;
            if (!$nextTransaction && $remCapital > 0 && $days > 0) {
                if ($receiptName === 'SILVER') {
                    $interest = SilverInterest::amount($remCapital, $rate1, $days, $period3);
                } elseif ($receiptName === 'D') {
                    $months = (int) ceil($days / 30);
                    $baseInterest = ($remCapital / 100) * $rate2 * $months;
                    if ($validPeriod > 0 && $days > $validPeriod) {
                        $penaltyMonths = (int) ceil(($days - $validPeriod) / 30);
                        $interest = $baseInterest + (($remCapital / 100) * 0.5 * $penaltyMonths);
                    } else {
                        $interest = $baseInterest;
                    }
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
            $row->rate_configuration = $cycleSource;
            $row->rate_type_name = $receiptName;
            $row->rate_1_used = $unverifiedHistoricalRate ? null : $rate1;
            $row->rate_2_used = $unverifiedHistoricalRate ? null : $rate2;
            $row->rate_3_used = $unverifiedHistoricalRate ? null : $rate3;
            $row->interest_source = $nextTransaction ? 'Recorded at next transaction' : 'Calculated current period';
            $row->days_count = $days;
            $row->interest_amount = round($interest, 2);
            $row->interest_to_date = $endDate->toDateString();
            $row->remaining_total = round($total, 2);
            $row->remaining_display = '<strong>Rs. ' . number_format($total, 2) . '</strong><br><small class="text-muted">Capital: ' . number_format($remCapital, 2) . ' + '.($nextTransaction ? 'Interest paid at next transaction' : 'Accrued interest').': ' . number_format($interest, 2) . ' (' . $days . ' days)</small>';

            $enriched[$i] = $row;
        }

        // Return rows in descending order
        return collect($enriched)->sortByDesc(function ($r) {
            $date = $r->dDate ?? $r->Pawn_Date ?? '1970-01-01';
            $id = $r->id ?? 0;
            return sprintf('%s-%010d', $date, $id);
        })->values();
    }

    /**
     * Attach the principal that was actually recorded after each historical
     * event. Payment summaries contain the post-payment principal. For a
     * repawning event, the next transaction's opening principal is the most
     * reliable historical snapshot; the current receipt is used only for the
     * latest repawning event.
     */
    private function attachHistoricalCapitalSnapshots(Collection $sorted, TPawnSum $receipt): void
    {
        $key = fn ($date, $number) => substr((string) $date, 0, 10).'|'.(string) $number;

        $payments = TPawnPayment::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)
            ->get()
            ->keyBy(fn ($payment) => $key($payment->Redeem_Date, $payment->Redeem_Number));

        $count = $sorted->count();
        for ($i = 0; $i < $count; $i++) {
            $row = $sorted[$i];
            $type = strtoupper((string) ($row->trans_type ?? ''));

            if ($type === 'PART_PAYMENT') {
                $payment = $payments->get($key($row->dDate ?? null, $row->trans_no ?? null));
                if ($payment && is_numeric($payment->Payable_Pawn_Amount)) {
                    $row->historical_capital = max(0, (float) $payment->Payable_Pawn_Amount);
                }
                continue;
            }

            if ($type !== 'REPAWNING') {
                continue;
            }

            $nextOpeningCapital = null;
            for ($j = $i + 1; $j < $count; $j++) {
                $nextType = strtoupper((string) ($sorted[$j]->trans_type ?? ''));
                if (str_contains($nextType, 'CHARGE')) {
                    continue;
                }

                $candidate = $sorted[$j]->trans_pawn_amount ?? null;
                if (is_numeric($candidate) && (float) $candidate > 0) {
                    $nextOpeningCapital = (float) $candidate;
                }
                break;
            }

            $row->historical_capital = $nextOpeningCapital
                ?? max(0, (float) ($receipt->Pawn_Amount ?? 0));
        }
    }

}
