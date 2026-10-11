<?php

/**
 * One-time production correction for open receipts after receipt-type edits.
 * Dry-run by default. This is not a Laravel migration and changes no schema.
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apply = in_array('--apply', $argv, true);
$service = app(App\Services\ReceiptTypeForwardTerms::class);
$types = App\Models\Recei_Add::active()->orderByDesc('effective_from')->orderByDesc('id')
    ->get()->unique(fn ($type) => strtoupper(trim((string) $type->receiptname)))->values();

$audit = [
    'generated_at' => now()->toIso8601String(),
    'mode' => $apply ? 'apply' : 'dry-run',
    'types' => [],
    'rows' => [],
];
foreach ($types as $type) {
    $values = $service->values($type);
    $columns = array_merge(['id', 'BC', 'Receipt_Number', 'receiptname', 'Receipt_Type'], array_keys($values));
    $rows = $service->mismatches($type->receiptname, $values)->orderBy('id')->get($columns);
    $audit['types'][] = [
        'type' => $type->receiptname,
        'version_id' => $type->id,
        'effective_from' => optional($type->effective_from)->toDateString(),
        'version_created_at' => optional($type->created_at)->toIso8601String(),
        'count' => $rows->count(),
        'new_terms' => $values,
    ];
    foreach ($rows as $row) {
        $audit['rows'][] = [
            'id' => $row->id,
            'BC' => $row->BC,
            'Receipt_Number' => $row->Receipt_Number,
            'type' => $type->receiptname,
            'old_terms' => collect($values)->keys()->mapWithKeys(fn ($field) => [$field => $row->{$field}])->all(),
        ];
    }
}

foreach ($audit['types'] as $type) {
    echo $type['type'].' version '.$type['version_id'].' ('.$type['effective_from'].', created '.$type['version_created_at'].'): '
        .$type['count']." mismatched open receipts\n";
}
echo 'Total: '.count($audit['rows'])."\n";
if (!$apply) {
    echo "Dry-run only; use --apply after taking a full database backup.\n";
    exit(0);
}

$path = storage_path('app/receipt_type_forward_terms_before_'.now()->format('Ymd_His').'.json');
if (file_put_contents($path, json_encode($audit, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false) {
    throw new RuntimeException('Cannot save pre-change audit; database was not changed.');
}
echo 'Pre-change row audit: '.$path."\n";

foreach ($types as $type) {
    // Small batches avoid holding a table-wide lock while staff use the app.
    $values = $service->values($type);
    $updated = 0;
    $service->mismatches($type->receiptname, $values)->orderBy('id')->chunkById(250, function ($rows) use ($values, &$updated) {
        $updated += Illuminate\Support\Facades\DB::table('t_pawn_sums')
            ->whereIn('id', $rows->pluck('id'))
            ->whereRaw('COALESCE(IsRedeemed, 0) = 0')
            ->whereRaw('COALESCE(isForfeit, 0) = 0')
            ->update($values);
    }, 'id');
    echo $type->receiptname.': updated '.$updated."\n";
}
