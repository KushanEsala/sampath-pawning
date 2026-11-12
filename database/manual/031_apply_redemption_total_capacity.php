<?php

// Manual, guarded production change. Never run through Laravel migrations.

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = DB::getDatabaseName();
if ($database !== 'kreethya_sampath_prod_20261004') {
    throw new RuntimeException('Refusing to change an unexpected database.');
}

$column = DB::selectOne(
    'SELECT COLUMN_TYPE AS type, IS_NULLABLE AS nullable, COLUMN_DEFAULT AS default_value
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
    [$database, 't_redeem_sums', 'Payable_Total']
);
if (!$column || strtolower($column->type) !== 'decimal(8,2)'
    || $column->nullable !== 'YES'
    || !in_array($column->default_value, [null, 'NULL'], true)) {
    throw new RuntimeException('The redemption total column does not match the reviewed starting state.');
}

$receipt = DB::table('t_pawn_sums')
    ->where('BC', '001')->where('Receipt_Number', 6763)
    ->first(['id', 'IsRedeemed', 'isForfeit', 'is_blocked']);
if (!$receipt || (int) $receipt->IsRedeemed !== 0
    || (int) $receipt->isForfeit !== 0 || (int) $receipt->is_blocked !== 0) {
    throw new RuntimeException('Receipt 001/6763 is no longer active and unblocked.');
}

$countBefore = DB::table('t_redeem_sums')->count();
echo "Database: {$database}; redemption rows: {$countBefore}; Payable_Total: {$column->type}\n";
if (!in_array('--apply', $argv, true)) {
    echo "Dry run only. Pass --apply after explicit approval.\n";
    exit(0);
}

$backupDir = '/home/kreethya/db-backups';
if (!is_dir($backupDir) || !is_writable($backupDir)) {
    throw new RuntimeException('Private backup directory is unavailable.');
}
$backup = $backupDir.'/t_redeem_sums_before_widen_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.sql.gz';
$config = DB::connection()->getConfig();
$dump = '/usr/local/apps/mariadb114/bin/mariadb-dump';
if (!is_executable($dump)) {
    throw new RuntimeException('MariaDB dump program is unavailable.');
}

$command = [$dump, '--no-defaults', '--single-transaction', '--skip-lock-tables',
    '--quick', '--hex-blob', '--user='.(string) $config['username']];
if (!empty($config['unix_socket'])) {
    $command[] = '--socket='.$config['unix_socket'];
} else {
    $command[] = '--host='.(string) ($config['host'] ?? '127.0.0.1');
    $command[] = '--port='.(string) ($config['port'] ?? 3306);
}
$command[] = $database;
$command[] = 't_redeem_sums';

umask(0077);
$stream = gzopen($backup, 'wb9');
if (!$stream) {
    throw new RuntimeException('Could not open the private backup file.');
}
$stderr = '';
try {
    $process = new Process($command, null, ['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
    $process->setTimeout(120);
    $process->run(function (string $type, string $buffer) use ($stream, &$stderr): void {
        if ($type === Process::OUT) {
            if (gzwrite($stream, $buffer) !== strlen($buffer)) {
                throw new RuntimeException('Backup write was incomplete.');
            }
        } else {
            $stderr .= $buffer;
        }
    });
    if (!$process->isSuccessful()) {
        throw new RuntimeException('MariaDB backup failed: '.trim($stderr));
    }
} catch (Throwable $e) {
    gzclose($stream);
    unlink($backup);
    throw $e;
}
gzclose($stream);

$verify = gzopen($backup, 'rb');
if (!$verify) {
    throw new RuntimeException('Could not read the backup for verification.');
}
$bytes = 0;
while (!gzeof($verify)) {
    $chunk = gzread($verify, 65536);
    if ($chunk === false) {
        gzclose($verify);
        throw new RuntimeException('Backup gzip verification failed.');
    }
    $bytes += strlen($chunk);
}
gzclose($verify);
if ($bytes < 1000) {
    throw new RuntimeException('Backup is unexpectedly small.');
}
echo 'Backup: '.$backup.'; uncompressed bytes: '.$bytes.'; SHA-256: '.hash_file('sha256', $backup)."\n";

DB::statement('ALTER TABLE `t_redeem_sums` MODIFY COLUMN `Payable_Total` DECIMAL(15,2) NULL DEFAULT NULL');

$typeAfter = DB::table('information_schema.COLUMNS')
    ->where('TABLE_SCHEMA', $database)
    ->where('TABLE_NAME', 't_redeem_sums')
    ->where('COLUMN_NAME', 'Payable_Total')
    ->value('COLUMN_TYPE');
$countAfter = DB::table('t_redeem_sums')->count();
if (strtolower((string) $typeAfter) !== 'decimal(15,2)' || $countAfter !== $countBefore) {
    throw new RuntimeException('Post-change verification failed; inspect the database and backup immediately.');
}
echo "Verified Payable_Total: {$typeAfter}; redemption rows: {$countAfter}\n";
