<?php

// Align a receipt's printed type label with its saved interest-rate snapshot.
// Dry-run by default. Apply only with a verified private full SQL backup.
// Never run a Laravel migration in this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptTypeResolver;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$options = [];
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
        [$key, $value] = explode('=', substr($argument, 2), 2);
        $options[$key] = $value;
    }
}
if (DB::getDatabaseName() !== 'kreethya_sampath_prod_20261004') {
    throw new RuntimeException('Unexpected database target; no changes made.');
}

$resolver = app(ReceiptTypeResolver::class);
$fields = ['rate1', 'rate2', 'rate3', 'period1', 'period2', 'period3', 'validPeriod'];
$snapshotMatches = static function (TPawnSum $receipt) use ($resolver, $fields): bool {
    $type = strtoupper(trim((string) $receipt->receiptname));
    if (!in_array($type, ['A', 'B', 'C', 'SILVER'], true)) return false;
    $date = ($receipt->Pawn_Date ?: $receipt->Receipt_Date)?->toDateString();
    $version = $resolver->resolveByDate($type, $date);
    if (!$version) return false;
    foreach ($fields as $field) {
        if (abs((float) $receipt->{$field} - (float) $version->{$field}) >= 0.011) {
            // Imported Silver valid-period snapshots can be zero while the
            // three six-percent rates still identify the Silver contract.
            if ($type === 'SILVER' && $field === 'validPeriod'
                && abs((float) $receipt->rate1 - 6) < 0.011
                && abs((float) $receipt->rate2 - 6) < 0.011
                && abs((float) $receipt->rate3 - 6) < 0.011) {
                continue;
            }
            return false;
        }
    }
    return true;
};

$plans = [];
$skipped = [];
foreach (TPawnSum::where('IsRedeemed', 0)->where('isForfeit', 0)->orderBy('id')->cursor() as $receipt) {
    $old = strtoupper(trim((string) $receipt->Receipt_Type));
    $new = strtoupper(trim((string) $receipt->receiptname));
    if ($old === $new) continue;
    if (!$snapshotMatches($receipt)) {
        $skipped[] = ['id' => $receipt->id, 'BC' => $receipt->BC, 'receipt' => $receipt->Receipt_Number];
        continue;
    }
    $plans[] = [
        'id' => $receipt->id,
        'BC' => $receipt->BC,
        'receipt_number' => $receipt->Receipt_Number,
        'old_type' => $old,
        'new_type' => $new,
        'updated_at' => (string) $receipt->getRawOriginal('updated_at'),
        'before' => $receipt->getAttributes(),
    ];
}

$pairs = [];
foreach ($plans as $plan) {
    $pair = $plan['old_type'].' -> '.$plan['new_type'];
    $pairs[$pair] = ($pairs[$pair] ?? 0) + 1;
}
ksort($pairs);
$summary = [
    'mode' => $apply ? 'apply' : 'dry-run',
    'planned' => count($plans),
    'by_pair' => $pairs,
    'without_rate_evidence' => $skipped,
];
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
if (!$apply || !$plans) exit(0);

$fullBackup = $options['backup'] ?? '';
$backupHash = strtolower($options['sha256'] ?? '');
if (!str_starts_with($fullBackup, '/home/kreethya/db-backups/')
    || !is_file($fullBackup)
    || !preg_match('/^[a-f0-9]{64}$/', $backupHash)
    || hash_file('sha256', $fullBackup) !== $backupHash) {
    throw new RuntimeException('Verified private full backup is required; no changes made.');
}

$beforePath = storage_path('app').'/receipt_type_labels_before_'
    .gmdate('Ymd_His').'_'.bin2hex(random_bytes(3)).'.json';
$payload = [
    'full_backup' => $fullBackup,
    'full_backup_sha256' => $backupHash,
    'created_at_utc' => gmdate('c'),
    'summary' => $summary,
    'plans' => $plans,
];
if (file_put_contents($beforePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
    throw new RuntimeException('Could not save row-level before-state; no changes made.');
}
chmod($beforePath, 0600);
echo json_encode(['before_state' => $beforePath, 'sha256' => hash_file('sha256', $beforePath)]).PHP_EOL;

$lifecycle = app(ReceiptLifecycleService::class);
$applied = 0;
$changed = [];
foreach ($plans as $plan) {
    $ok = DB::transaction(function () use ($plan, $lifecycle, $snapshotMatches): bool {
        $receipt = TPawnSum::whereKey($plan['id'])->lockForUpdate()->first();
        if (!$receipt || $receipt->BC !== $plan['BC']
            || (string) $receipt->Receipt_Number !== (string) $plan['receipt_number']
            || $receipt->IsRedeemed || $receipt->isForfeit
            || strtoupper(trim((string) $receipt->Receipt_Type)) !== $plan['old_type']
            || strtoupper(trim((string) $receipt->receiptname)) !== $plan['new_type']
            || (string) $receipt->getRawOriginal('updated_at') !== $plan['updated_at']
            || !$snapshotMatches($receipt)) {
            return false;
        }
        $receipt->Receipt_Type = $plan['new_type'];
        $receipt->save();
        $lifecycle->record(
            $receipt->fresh(), 'RECEIPT_TYPE_LABEL_CORRECTED', null,
            'Printed receipt type aligned with the saved interest-rate snapshot.',
            [
                'old_type' => $plan['old_type'],
                'new_type' => $plan['new_type'],
                'interest_rates_and_amount_unchanged' => true,
                'repair_source' => '028_reconcile_receipt_type_labels',
            ]
        );
        return true;
    }, 3);
    if ($ok) $applied++;
    else $changed[] = $plan['id'];
}
echo json_encode([
    'applied' => $applied,
    'skipped_due_to_concurrent_change' => $changed,
    'before_state' => $beforePath,
    'before_state_sha256' => hash_file('sha256', $beforePath),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
