<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Rebuild legacy pawn principal by replaying the recorded financial events.
 *
 * This service never inserts adjustment transactions and never deletes a
 * transaction. It corrects the existing summary/history fields in place. Any
 * receipt with ambiguous duplicate repawning keys is excluded for manual
 * review instead of guessing which cash disbursement was genuine.
 */
class RepawningMathRepairService
{
    public function audit(?string $branch = null, ?string $receiptNumber = null): array
    {
        $repawns = $this->scoped('t_repawning_sums', 'Receipt_Number', $branch, $receiptNumber)
            ->orderBy('BC')->orderBy('Receipt_Number')->orderBy('Redeem_Date')->orderBy('id')->get();

        $scopeKeys = $repawns->map(fn ($row) => $this->receiptKey($row->BC, $row->Receipt_Number))
            ->unique()->flip();

        if ($scopeKeys->isEmpty()) {
            return ['receipts_scanned' => 0, 'receipts_changed' => 0, 'row_updates' => [], 'manual_review' => []];
        }

        $transactions = $this->scoped('t_pawn_trans', 'code', $branch, $receiptNumber)
            ->whereIn(DB::raw('UPPER(trans_type)'), ['PAWN', 'PART_PAYMENT', 'REPAWNING', 'REDEEM'])
            ->orderBy('dDate')->orderBy('id')->get()
            ->filter(fn ($row) => $scopeKeys->has($this->receiptKey($row->BC, $row->code)));

        $payments = $this->scoped('t_pawn_payments', 'Receipt_Number', $branch, $receiptNumber)->get()
            ->filter(fn ($row) => $scopeKeys->has($this->receiptKey($row->BC, $row->Receipt_Number)));
        $receipts = $this->scoped('t_pawn_sums', 'Receipt_Number', $branch, $receiptNumber)->get()
            ->filter(fn ($row) => $scopeKeys->has($this->receiptKey($row->BC, $row->Receipt_Number)));

        $repawnsByReceipt = $repawns->groupBy(fn ($row) => $this->receiptKey($row->BC, $row->Receipt_Number));
        $transactionsByReceipt = $transactions->groupBy(fn ($row) => $this->receiptKey($row->BC, $row->code));
        $paymentsByReceipt = $payments->groupBy(fn ($row) => $this->receiptKey($row->BC, $row->Receipt_Number));
        $receiptsByKey = $receipts->groupBy(fn ($row) => $this->receiptKey($row->BC, $row->Receipt_Number));

        $updates = [];
        $manualReview = [];
        $changedReceipts = [];

        foreach ($repawnsByReceipt as $key => $receiptRepawns) {
            [$bc, $number] = explode('|', $key, 2);
            $parents = $receiptsByKey->get($key, collect());
            if ($parents->count() !== 1) {
                $manualReview[] = $this->review($bc, $number, 'Expected exactly one t_pawn_sums parent; found '.$parents->count());
                continue;
            }

            $receiptTransactions = $transactionsByReceipt->get($key, collect())->values();
            $duplicates = $this->duplicateEventKeys($receiptTransactions, $receiptRepawns);
            if ($duplicates) {
                $manualReview[] = $this->review($bc, $number, 'Duplicate/reused repawning key: '.implode(', ', $duplicates));
                continue;
            }

            try {
                $receiptUpdates = $this->replayReceipt(
                    $parents->first(),
                    $receiptTransactions,
                    $receiptRepawns,
                    $paymentsByReceipt->get($key, collect())
                );
            } catch (RuntimeException $exception) {
                $manualReview[] = $this->review($bc, $number, $exception->getMessage());
                continue;
            }

            if ($receiptUpdates) {
                $changedReceipts[$key] = true;
                array_push($updates, ...$receiptUpdates);
            }
        }

        return [
            'receipts_scanned' => $repawnsByReceipt->count(),
            'receipts_changed' => count($changedReceipts),
            'row_updates' => $updates,
            'manual_review' => $manualReview,
        ];
    }

    public function apply(array $plan): array
    {
        $updates = collect($plan['row_updates'] ?? [])->groupBy('receipt_key');
        $appliedRows = 0;

        foreach ($updates as $receiptKey => $receiptUpdates) {
            DB::transaction(function () use ($receiptUpdates, &$appliedRows) {
                foreach ($receiptUpdates as $update) {
                    $query = DB::table($update['table']);
                    foreach ($update['key'] as $column => $value) {
                        $query->where($column, $value);
                    }

                    $current = $query->lockForUpdate()->first();
                    if (!$current) {
                        throw new RuntimeException($update['table'].' row disappeared before repair.');
                    }

                    foreach ($update['before'] as $field => $expected) {
                        if (!$this->same($current->{$field} ?? null, $expected)) {
                            throw new RuntimeException($update['table'].'.'.$field.' changed after the dry run; repair stopped.');
                        }
                    }

                    $query->update($update['after']);
                    $appliedRows++;
                }
            }, 3);
        }

        return ['receipts_applied' => $updates->count(), 'rows_applied' => $appliedRows];
    }

    public function writeRecoveryFiles(array $plan): array
    {
        $directory = storage_path('app/private/repawning-repair');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the private repawning repair directory.');
        }

        $stamp = now()->format('Ymd_His').'_'.bin2hex(random_bytes(3));
        $jsonPath = $directory.'/repawning_math_'.$stamp.'.json';
        $sqlPath = $directory.'/repawning_math_'.$stamp.'_rollback.sql';
        file_put_contents($jsonPath, json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $sql = [
            '-- Generated before the repawning math repair was applied.',
            '-- Review before manual use. This file restores only fields changed by that repair.',
            'START TRANSACTION;',
        ];
        foreach ($plan['row_updates'] ?? [] as $update) {
            $sets = collect($update['before'])->map(fn ($value, $field) => '`'.$field.'` = '.$this->quote($value))->implode(', ');
            $where = collect($update['key'])->map(fn ($value, $field) => '`'.$field.'` <=> '.$this->quote($value))->implode(' AND ');
            $sql[] = 'UPDATE `'.$update['table'].'` SET '.$sets.' WHERE '.$where.';';
        }
        $sql[] = 'COMMIT;';
        file_put_contents($sqlPath, implode(PHP_EOL, $sql).PHP_EOL);

        return ['audit' => $jsonPath, 'rollback_sql' => $sqlPath];
    }

    private function replayReceipt(object $receipt, Collection $transactions, Collection $repawns, Collection $payments): array
    {
        $repawnByEvent = $repawns->keyBy(fn ($row) => $this->eventKey($row->Redeem_Number, $row->Redeem_Date));
        $paymentByEvent = $payments->keyBy(fn ($row) => $this->eventKey($row->Redeem_Number, $row->Redeem_Date));
        $updates = [];
        $principal = 0.0;
        $lastRepawnPayout = null;
        $lastInterestBalance = 0.0;
        $seenPawn = false;

        foreach ($transactions->sortBy(fn ($row) => sprintf('%s-%010d', $row->dDate ?? '', $row->id ?? 0)) as $transaction) {
            $type = strtoupper(trim((string) $transaction->trans_type));
            if ($type === 'PAWN') {
                if (!$seenPawn) {
                    $principal = max(0, (float) ($transaction->Cr_amount ?: $transaction->Pawn_Amount ?: $transaction->trans_amount ?: $receipt->Amount));
                    $seenPawn = true;
                }
                continue;
            }

            if (!$seenPawn) {
                $principal = max(0, (float) $receipt->Amount);
                $seenPawn = true;
            }

            $opening = round($principal, 2);
            $eventKey = $this->eventKey($transaction->trans_no, $transaction->dDate);

            if ($type === 'PART_PAYMENT') {
                $payment = $paymentByEvent->get($eventKey);
                $paidCapital = max(0, (float) ($transaction->Paided_Captional ?: ($payment->Advance_Payment ?? 0)));
                $principal = round(max(0, $opening - $paidCapital), 2);
                $lastInterestBalance = max(0, (float) ($transaction->interest_Balance ?? 0));

                $this->addUpdate($updates, $receipt, 't_pawn_trans', ['id' => $transaction->id], $transaction, [
                    'Pawn_Amount' => $opening,
                ]);

                if ($payment) {
                    $this->addUpdate($updates, $receipt, 't_pawn_payments', [
                        'BC' => $payment->BC,
                        'Redeem_Number' => $payment->Redeem_Number,
                    ], $payment, [
                        'Original_Pawn_Amount' => $opening,
                        'current_pawn_amount' => $opening,
                        'Payable_Pawn_Amount' => $principal,
                    ]);
                }
                continue;
            }

            if ($type === 'REPAWNING') {
                $repawn = $repawnByEvent->get($eventKey);
                if (!$repawn) {
                    throw new RuntimeException('No t_repawning_sums row matches transaction '.$transaction->id.' ('.$eventKey.').');
                }

                $interest = max(0, (float) $repawn->Paid_Interest);
                $service = max(0, (float) $repawn->Document_Charges);
                $stamp = max(0, (float) $repawn->Stamp_Fee);
                $postage = max(0, (float) ($transaction->Postage_charge ?? 0));
                $discount = max(0, (float) $repawn->Discount);
                $extraCash = max(0, (float) ($transaction->Cr_amount ?: $repawn->Payable_Total));
                $redeemTotal = round(max(0, $opening + $interest + $service + $stamp + $postage - $discount), 2);
                $principal = round($redeemTotal + $extraCash, 2);
                $lastRepawnPayout = $extraCash;
                $lastInterestBalance = 0.0;

                $this->addUpdate($updates, $receipt, 't_pawn_trans', ['id' => $transaction->id], $transaction, [
                    'Pawn_Amount' => $opening,
                    'trans_amount' => $redeemTotal,
                ]);
                $this->addUpdate($updates, $receipt, 't_repawning_sums', ['id' => $repawn->id], $repawn, [
                    'Payable_Pawn_Amount' => $opening,
                    'Redeem_total' => $redeemTotal,
                ]);
                continue;
            }

            if ($type === 'REDEEM') {
                $this->addUpdate($updates, $receipt, 't_pawn_trans', ['id' => $transaction->id], $transaction, [
                    'Pawn_Amount' => $opening,
                ]);
                $principal = 0.0;
                $lastInterestBalance = 0.0;
            }
        }

        if ((int) $receipt->IsRedeemed === 0 && (int) $receipt->isForfeit === 0) {
            $desired = [
                'Pawn_Amount' => round($principal, 2),
                'RePawning_amount' => round($principal, 2),
                'interest_Paid' => 0.0,
                'BalanceInterest' => round($lastInterestBalance, 2),
            ];
            if ($lastRepawnPayout !== null) {
                $desired['RePawn_get_amount'] = round($lastRepawnPayout, 2);
            }
            $this->addUpdate($updates, $receipt, 't_pawn_sums', ['id' => $receipt->id], $receipt, $desired);
        }

        return $updates;
    }

    private function addUpdate(array &$updates, object $receipt, string $table, array $key, object $row, array $desired): void
    {
        $before = [];
        $after = [];
        foreach ($desired as $field => $value) {
            $existing = $row->{$field} ?? null;
            if (!$this->same($existing, $value)) {
                $before[$field] = $existing;
                $after[$field] = $value;
            }
        }
        if (!$after) {
            return;
        }

        $updates[] = [
            'receipt_key' => $this->receiptKey($receipt->BC, $receipt->Receipt_Number),
            'table' => $table,
            'key' => $key,
            'before' => $before,
            'after' => $after,
        ];
    }

    private function duplicateEventKeys(Collection $transactions, Collection $repawns): array
    {
        $transactionDuplicates = $transactions->where('trans_type', 'REPAWNING')
            ->groupBy(fn ($row) => $this->eventKey($row->trans_no, $row->dDate))
            ->filter(fn ($rows) => $rows->count() > 1)->keys();
        $summaryDuplicates = $repawns->groupBy(fn ($row) => $this->eventKey($row->Redeem_Number, $row->Redeem_Date))
            ->filter(fn ($rows) => $rows->count() > 1)->keys();

        return $transactionDuplicates->concat($summaryDuplicates)->unique()->values()->all();
    }

    private function scoped(string $table, string $receiptColumn, ?string $branch, ?string $receiptNumber)
    {
        return DB::table($table)
            ->when($branch !== null && $branch !== '', fn ($query) => $query->where('BC', $branch))
            ->when($receiptNumber !== null && $receiptNumber !== '', fn ($query) => $query->where($receiptColumn, $receiptNumber));
    }

    private function eventKey(mixed $number, mixed $date): string
    {
        return (string) $number.'|'.substr((string) $date, 0, 10);
    }

    private function receiptKey(mixed $branch, mixed $number): string
    {
        return (string) $branch.'|'.(string) $number;
    }

    private function review(string $branch, string $receipt, string $reason): array
    {
        return ['BC' => $branch, 'Receipt_Number' => $receipt, 'reason' => $reason];
    }

    private function same(mixed $left, mixed $right): bool
    {
        if (is_numeric($left) && is_numeric($right)) {
            return abs((float) $left - (float) $right) < 0.005;
        }

        return $left === $right;
    }

    private function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }
}

