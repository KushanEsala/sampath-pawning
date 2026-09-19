<?php

// Explicit manual repair only: php database/manual/sync_article_status_batches.php --apply
// No migrations, session termination, financial changes or receipt-status changes.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function statusKey(object $row): string { return $row->BC.'|'.$row->Receipt_Number; }
function differs(object $detail, object $receipt): bool {
    return $detail->IsRedeemed === null || $detail->isForfeit === null
        || (int) $detail->IsRedeemed !== (int) $receipt->IsRedeemed
        || (int) $detail->isForfeit !== (int) $receipt->isForfeit;
}
function parentMap(array $rows): array {
    $map = [];
    foreach ($rows as $row) {
        $key = statusKey($row);
        if (isset($map[$key])) throw new RuntimeException('Duplicate branch/receipt parent keys detected. Repair stopped to avoid ambiguous updates.');
        $map[$key] = $row;
    }
    return $map;
}
function inspect(PDO $pdo): array {
    $normal = parentMap($pdo->query('SELECT id, BC, Receipt_Number, IsRedeemed, isForfeit FROM t_pawn_sums')->fetchAll(PDO::FETCH_OBJ));
    $opening = parentMap($pdo->query('SELECT BC, Receipt_Number, IsRedeemed, isForfeit FROM t_opening_pawn_sums')->fetchAll(PDO::FETCH_OBJ));
    $details = $pdo->query('SELECT id, BC, Receipt_Number, IsRedeemed, isForfeit FROM t_pawn_details')->fetchAll(PDO::FETCH_OBJ);
    $openingDetails = $pdo->query('SELECT BC, Receipt_Number, IsRedeemed, isForfeit FROM t_opening_pawn_details')->fetchAll(PDO::FETCH_OBJ);
    $pending = []; $openingPending = []; $unmatched = 0;
    foreach ($details as $detail) {
        $parent = $normal[statusKey($detail)] ?? null;
        if (!$parent) { $unmatched++; continue; }
        if (differs($detail, $parent)) $pending[$detail->id] = $detail;
    }
    foreach ($openingDetails as $detail) {
        $parent = $opening[statusKey($detail)] ?? null;
        if ($parent && differs($detail, $parent)) $openingPending[statusKey($detail)] = $detail;
    }
    return compact('normal','pending','openingPending','unmatched');
}

try {
    $name = config('database.default');
    $config = config('database.connections.'.$name);
    if ($config['driver'] !== 'mysql' || !in_array($config['host'], ['127.0.0.1','localhost','::1'], true) || $config['database'] !== 'smartom_sampath') {
        throw new RuntimeException('Refusing to access anything except the requested local smartom_sampath database.');
    }
    config(['database.connections.'.$name.'.options.'.PDO::ATTR_TIMEOUT=>5]);
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'smartom_sampath') throw new RuntimeException('Connected database does not match.');
    $pdo->exec('SET SESSION innodb_lock_wait_timeout = 5');
    $plan = inspect($pdo);
    echo json_encode(['normal_rows_to_sync'=>count($plan['pending']), 'opening_receipts_to_sync'=>count($plan['openingPending']), 'unmatched_normal_articles_unchanged'=>$plan['unmatched']]).PHP_EOL;
    if (!in_array('--apply', $argv, true)) { echo 'DRY_RUN_ONLY'.PHP_EOL; exit(0); }
    foreach ($pdo->query('SHOW FULL PROCESSLIST')->fetchAll(PDO::FETCH_OBJ) as $process) {
        if ($process->db === 'smartom_sampath' && preg_match('/^\s*UPDATE\s+`?t_(?:opening_)?pawn_details`?/i', $process->Info ?? '')) {
            throw new RuntimeException('Another article-status UPDATE is still running. No repair started.');
        }
    }

    // Full consistent backup stays outside public/ and is never printed to the terminal.
    $backupDirectory = storage_path('app/private/database-backups');
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0700, true)) throw new RuntimeException('Unable to create private backup directory.');
    $backup = $backupDirectory.'/smartom_sampath_before_article_status_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.sql';
    $dump = new Symfony\Component\Process\Process([
        'F:/Programing/Xampp/mysql/bin/mysqldump.exe', '--host='.$config['host'], '--port='.$config['port'],
        '--user='.$config['username'], '--single-transaction', '--quick', '--skip-lock-tables',
        '--routines', '--triggers', '--events', '--result-file='.$backup, 'smartom_sampath',
    ], base_path(), ['MYSQL_PWD'=>$config['password']]);
    $dump->setTimeout(120);
    echo 'Creating full private database backup...'.PHP_EOL;
    $dump->run();
    if (!$dump->isSuccessful() || !is_file($backup) || filesize($backup) === 0) throw new RuntimeException('Database backup failed; no status repair performed.');
    echo 'BACKUP '.$backup.' ('.filesize($backup).' bytes)'.PHP_EOL;
    $changed = 0;
    $update = $pdo->prepare('UPDATE t_pawn_details SET IsRedeemed = ?, isForfeit = ? WHERE id = ?');
    foreach (array_chunk(array_keys($plan['pending']), 100) as $batch => $ids) {
        $pdo->beginTransaction();
        try {
            $parentIds = [];
            foreach ($ids as $id) $parentIds[] = $plan['normal'][statusKey($plan['pending'][$id])]->id;
            $parentIds = array_values(array_unique($parentIds)); sort($parentIds);
            $parents = $pdo->prepare('SELECT id, BC, Receipt_Number, IsRedeemed, isForfeit FROM t_pawn_sums WHERE id IN ('.implode(',', array_fill(0, count($parentIds), '?')).') ORDER BY id FOR UPDATE');
            $parents->execute($parentIds);
            $currentParents = parentMap($parents->fetchAll(PDO::FETCH_OBJ));
            $details = $pdo->prepare('SELECT id, BC, Receipt_Number, IsRedeemed, isForfeit FROM t_pawn_details WHERE id IN ('.implode(',', array_fill(0, count($ids), '?')).') ORDER BY id FOR UPDATE');
            $details->execute($ids);
            foreach ($details->fetchAll(PDO::FETCH_OBJ) as $detail) {
                $parent = $currentParents[statusKey($detail)] ?? null;
                if (!$parent) throw new RuntimeException('A receipt identity changed during repair; current batch rolled back.');
                if (differs($detail, $parent)) {
                    $update->execute([(int) $parent->IsRedeemed, (int) $parent->isForfeit, $detail->id]);
                    $changed += $update->rowCount();
                }
            }
            $pdo->commit();
            if (($batch + 1) % 10 === 0) echo 'Committed '.($batch + 1).' small batches; rows changed '.$changed.PHP_EOL;
        } catch (Throwable $exception) { $pdo->rollBack(); throw $exception; }
    }
    $openingChanged = 0;
    foreach ($plan['openingPending'] as $detail) {
        $pdo->beginTransaction();
        try {
            $parent = $pdo->prepare('SELECT BC, Receipt_Number, IsRedeemed, isForfeit FROM t_opening_pawn_sums WHERE BC = ? AND Receipt_Number = ? FOR UPDATE');
            $parent->execute([$detail->BC, $detail->Receipt_Number]);
            $parent = $parent->fetch(PDO::FETCH_OBJ);
            if (!$parent) throw new RuntimeException('Opening receipt disappeared during repair.');
            $updateOpening = $pdo->prepare('UPDATE t_opening_pawn_details SET IsRedeemed = ?, isForfeit = ? WHERE BC = ? AND Receipt_Number = ? AND (NOT (IsRedeemed <=> ?) OR NOT (isForfeit <=> ?))');
            $flags = [(int) $parent->IsRedeemed, (int) $parent->isForfeit];
            $updateOpening->execute([...$flags, $detail->BC, $detail->Receipt_Number, ...$flags]);
            $openingChanged += $updateOpening->rowCount();
            $pdo->commit();
        } catch (Throwable $exception) { $pdo->rollBack(); throw $exception; }
    }
    $remaining = inspect($pdo);
    echo json_encode(['normal_rows_changed'=>$changed, 'opening_rows_changed'=>$openingChanged,
        'normal_mismatches_remaining'=>count($remaining['pending']), 'opening_receipts_mismatched_remaining'=>count($remaining['openingPending']),
        'unmatched_normal_articles_unchanged'=>$remaining['unmatched']]).PHP_EOL;
    if ($remaining['pending'] || $remaining['openingPending']) throw new RuntimeException('Some statuses still differ; repair is not fully complete.');
    echo 'STATUS_SYNC_VERIFIED'.PHP_EOL;
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo 'REPAIR_STOPPED '.$exception->getMessage().PHP_EOL;
    exit(1);
}
