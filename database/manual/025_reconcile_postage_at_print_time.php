<?php

// Guarded, dry-run-by-default repair of current-cycle issued-letter postage.
// php database/manual/025_reconcile_postage_at_print_time.php [--apply]
// Never run a Laravel migration in this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ArrearsLetterEvent;
use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$lifecycle = app(ReceiptLifecycleService::class);

// `effective_to` is mutable when a later version is inserted. For a real
// timestamped print, use the latest version that had actually been created
// by then and whose effective date had arrived. A same-day earlier print
// must not inherit a version created later that afternoon.
$versionAtPrint = static function (string $type, string $issuedAt): ?Recei_Add {
    return Recei_Add::where('receiptname', $type)
        ->whereDate('effective_from', '<=', substr($issuedAt, 0, 10))
        ->where('created_at', '<=', $issuedAt)
        ->orderByDesc('effective_from')->orderByDesc('created_at')->orderByDesc('id')
        ->first();
};

$candidate = static function (ArrearsLetterEvent $event) use ($lifecycle, $versionAtPrint): array {
    $receipt = TPawnSum::find($event->pawn_sum_id);
    $field = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$event->letter_no - 1] ?? null;
    $issuedAt = $event->issued_at?->toDateTimeString();
    $date = substr((string) $issuedAt, 0, 10);
    $type = strtoupper(trim((string) ($receipt?->receiptname ?: $receipt?->Receipt_Type)));
    $version = $type && $issuedAt ? $versionAtPrint($type, $issuedAt) : null;
    $old = round((float) $event->postage_charge, 2);
    $new = round((float) ($version?->Postage_charge ?? 0), 2);
    $reason = null;

    if (!$receipt || !$field || !$issuedAt || $event->BC !== $receipt->BC) {
        $reason = 'Missing/mismatched receipt, letter number or print time';
    } elseif ($old <= 0) {
        $reason = 'Zero-charge historical event requires individual review';
    } elseif (!$version || $new <= 0) {
        $reason = 'No timestamp-proven positive receipt-type version';
    } elseif (abs($old - $new) < 0.01) {
        $reason = 'Already matches version at print time';
    } elseif ($receipt->IsRedeemed || $receipt->isForfeit) {
        $reason = 'Closed receipt; posted settlement needs individual review';
    } elseif ($receipt->Pawn_Date && $receipt->Pawn_Date->toDateString() > $date) {
        $reason = 'A later pawn/payment cycle exists';
    } elseif ((int) $event->cycle_no !== $lifecycle->cycleNumber($receipt)) {
        $reason = 'Event belongs to a previous letter cycle';
    } elseif (!(bool) $receipt->{'is_letter_'.$event->letter_no}
        || $receipt->{'letter_'.$event->letter_no.'_date'}?->toDateString() !== $date) {
        $reason = 'Current letter flag or saved print date differs';
    } elseif (abs((float) ($receipt->{$field} ?? 0) - $old) >= 0.01) {
        $reason = 'Current per-letter saved amount differs';
    } elseif (DB::table('t_pawn_trans')->where('BC', $receipt->BC)
        ->where('code', $receipt->Receipt_Number)
        ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
        ->whereDate('dDate', '>=', $date)->exists()) {
        $reason = 'A payment or repawn on/after print date needs allocation review';
    }

    return [
        'event_id' => $event->id,
        'pawn_sum_id' => $receipt?->id,
        'BC' => $event->BC,
        'receipt_number' => $event->receipt_number,
        'letter_no' => $event->letter_no,
        'issued_at' => $issuedAt,
        'old_postage' => $old,
        'new_postage' => $new,
        'type' => $type,
        'type_version_id' => $version?->id,
        'type_version_created_at' => $version?->created_at?->toDateTimeString(),
        'reason' => $reason,
    ];
};

$plans = [];
$skips = [];
foreach (ArrearsLetterEvent::orderBy('id')->cursor() as $event) {
    $result = $candidate($event);
    if ($result['reason']) {
        $skips[$result['reason']] = ($skips[$result['reason']] ?? 0) + 1;
    } else {
        $plans[] = $result;
    }
}

$byType = [];
foreach ($plans as $plan) {
    $key = $plan['type'].' '.$plan['old_postage'].' -> '.$plan['new_postage'];
    $byType[$key] = ($byType[$key] ?? 0) + 1;
}
ksort($byType);
ksort($skips);
echo json_encode([
    'mode' => $apply ? 'apply' : 'dry-run',
    'planned' => count($plans),
    'by_type' => $byType,
    'skipped' => $skips,
    'receipt_10771' => array_values(array_filter($plans,
        fn (array $row) => $row['BC'] === '001' && (int) $row['receipt_number'] === 10771)),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (!$apply || !$plans) exit(0);

$backup = storage_path('app/letter_postage_print_time_before_'.date('Ymd_His').'.json');
if (file_put_contents($backup, json_encode(['plans' => $plans, 'skipped' => $skips], JSON_PRETTY_PRINT)) === false) {
    throw new RuntimeException('Could not save pre-change audit; no database changes made.');
}
@chmod($backup, 0600);
echo 'Private pre-change audit: '.$backup.PHP_EOL;

$updated = DB::transaction(function () use ($plans, $candidate, $lifecycle): int {
    $count = 0;
    foreach ($plans as $plan) {
        $receipt = TPawnSum::whereKey($plan['pawn_sum_id'])->lockForUpdate()->firstOrFail();
        $event = ArrearsLetterEvent::whereKey($plan['event_id'])->lockForUpdate()->firstOrFail();
        $fresh = $candidate($event);
        if ($fresh['reason'] || $fresh['pawn_sum_id'] !== $receipt->id
            || $fresh['type_version_id'] !== $plan['type_version_id']
            || abs($fresh['old_postage'] - $plan['old_postage']) >= 0.01
            || abs($fresh['new_postage'] - $plan['new_postage']) >= 0.01) {
            throw new RuntimeException('Event changed during repair; all writes rolled back (event '.$event->id.').');
        }

        $field = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$event->letter_no - 1];
        $event->postage_charge = $plan['new_postage'];
        $event->save();
        $receipt->{$field} = $plan['new_postage'];
        $receipt->save();

        $lifecycle->record($receipt, 'POSTAGE_RATE_CORRECTION',
            $plan['new_postage'] - $plan['old_postage'],
            'Existing printed-letter postage corrected to the type version in force at its actual print time.',
            [
                'letter_event_id' => $event->id,
                'letter_no' => $event->letter_no,
                'issued_at' => $plan['issued_at'],
                'old_postage' => $plan['old_postage'],
                'new_postage' => $plan['new_postage'],
                'type_version_id' => $plan['type_version_id'],
            ]);
        $count++;
    }
    return $count;
});

echo 'Corrected open current-cycle letter events: '.$updated.PHP_EOL;
