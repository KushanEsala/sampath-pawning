<?php

namespace App\Console\Commands;

use App\Services\RepawningMathRepairService;
use Illuminate\Console\Command;

class RepairRepawningMath extends Command
{
    protected $signature = 'repawning:repair-math
        {--branch= : Limit the audit/repair to one branch code}
        {--receipt= : Limit the audit/repair to one receipt number}
        {--apply : Back up changed fields and apply the repair}';

    protected $description = 'Audit or repair duplicated legacy principal math in existing repawning rows';

    public function handle(RepawningMathRepairService $repair): int
    {
        $branch = $this->option('branch');
        $receipt = $this->option('receipt');
        $plan = $repair->audit($branch ?: null, $receipt ?: null);

        $this->table(['Metric', 'Count'], [
            ['Repawning receipts scanned', $plan['receipts_scanned']],
            ['Receipts requiring corrections', $plan['receipts_changed']],
            ['Existing rows requiring updates', count($plan['row_updates'])],
            ['Receipts held for manual review', count($plan['manual_review'])],
        ]);

        $sample = collect($plan['row_updates'])->take(15)->map(function (array $row) {
            return [
                $row['receipt_key'],
                $row['table'],
                json_encode($row['key']),
                json_encode($row['before']),
                json_encode($row['after']),
            ];
        })->all();
        if ($sample) {
            $this->table(['Receipt', 'Table', 'Key', 'Before', 'After'], $sample);
        }

        if ($plan['manual_review']) {
            $this->warn('Ambiguous receipts are excluded from automatic repair:');
            $this->table(['Branch', 'Receipt', 'Reason'], collect($plan['manual_review'])->map(fn ($row) => [
                $row['BC'], $row['Receipt_Number'], $row['reason'],
            ])->all());
        }

        if (!$this->option('apply')) {
            $this->info('DRY RUN ONLY. No database values were changed. Re-run with --apply after reviewing this plan.');
            return self::SUCCESS;
        }

        if (!$plan['row_updates']) {
            $this->info('No unambiguous rows require correction. Nothing was changed.');
            return self::SUCCESS;
        }

        $paths = $repair->writeRecoveryFiles($plan);
        $this->info('Private audit backup: '.$paths['audit']);
        $this->info('Manual rollback SQL: '.$paths['rollback_sql']);
        $result = $repair->apply($plan);
        $this->info('Repair complete: '.$result['receipts_applied'].' receipts / '.$result['rows_applied'].' rows updated.');

        $verification = $repair->audit($branch ?: null, $receipt ?: null);
        if ($verification['row_updates']) {
            $this->error('Post-repair verification still found '.count($verification['row_updates']).' row updates. Review the audit before continuing.');
            return self::FAILURE;
        }

        $this->info('REPAWNING_MATH_REPAIR_VERIFIED');
        return self::SUCCESS;
    }
}

