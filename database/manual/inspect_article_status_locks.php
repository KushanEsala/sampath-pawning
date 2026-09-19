<?php

// Read-only diagnostics. Never invokes migrations or changes application data.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $name = config('database.default');
    $config = config('database.connections.'.$name);
    echo json_encode(['configured_driver'=>$config['driver'], 'host'=>$config['host'] ?? null, 'port'=>$config['port'] ?? null, 'database'=>$config['database']], JSON_UNESCAPED_SLASHES).PHP_EOL;
    if ($config['driver'] !== 'mysql' || !in_array($config['host'], ['127.0.0.1','localhost','::1'], true) || $config['database'] !== 'smartom_sampath') {
        throw new RuntimeException('Configured database is not the requested local smartom_sampath database. No connection attempted.');
    }
    config(['database.connections.'.$name.'.options.'.PDO::ATTR_TIMEOUT=>5]);
    $db = Illuminate\Support\Facades\DB::connection();
    echo json_encode($db->selectOne('SELECT DATABASE() AS database_name, VERSION() AS version, CONNECTION_ID() AS diagnostic_connection')).PHP_EOL;
    $processes = array_map(fn ($row) => [
        'id'=>$row->Id, 'database'=>$row->db, 'command'=>$row->Command, 'seconds'=>$row->Time, 'state'=>$row->State,
        'operation'=>preg_match('/^\s*([A-Za-z]+)/', $row->Info ?? '', $match) ? strtoupper($match[1]) : null,
        'article_status_sync'=>str_contains(strtolower($row->Info ?? ''), 'update `t_pawn_details`') && str_contains(strtolower($row->Info ?? ''), 'coalesce(receipt.isredeemed'),
    ], $db->select('SHOW FULL PROCESSLIST'));
    echo 'processes '.json_encode($processes).PHP_EOL;
    echo 'transactions '.json_encode($db->select('SELECT trx_id, trx_state, trx_started, trx_mysql_thread_id, trx_rows_locked, trx_rows_modified FROM information_schema.INNODB_TRX')).PHP_EOL;
    try {
        echo 'lock_waits '.json_encode($db->select('SELECT requesting_trx_id, blocking_trx_id FROM information_schema.INNODB_LOCK_WAITS')).PHP_EOL;
    } catch (Throwable $exception) {
        echo 'lock_waits unavailable'.PHP_EOL;
    }
    foreach (['t_pawn_details','t_pawn_sums','t_opening_pawn_details','t_opening_pawn_sums'] as $table) {
        echo $table.' indexes '.json_encode($db->select('SHOW INDEX FROM `'.$table.'`')).PHP_EOL;
    }
} catch (Throwable $exception) {
    echo 'DIAGNOSTIC_FAILED '.$exception->getMessage().PHP_EOL;
    exit(1);
}
