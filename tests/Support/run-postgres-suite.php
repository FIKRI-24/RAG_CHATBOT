<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
$config = $connection->getConfig();
if (! $app->environment('local') || $connection->getDriverName() !== 'pgsql'
    || ! in_array($config['host'], ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('This helper requires a local PostgreSQL connection.');
}
$name = 'rag_testing_'.bin2hex(random_bytes(8));
$created = false;
try {
    $connection->getPdo()->exec('CREATE DATABASE "'.$name.'"');
    $created = true;
    $process = new Process(['php', 'artisan', 'test', '--compact'], base_path(), [
        'APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => $name, 'DB_HOST' => $config['host'],
        'DB_PORT' => (string) $config['port'], 'DB_USERNAME' => $config['username'], 'DB_PASSWORD' => (string) ($config['password'] ?? ''),
        'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'MAIL_MAILER' => 'array',
    ]);
    $process->setTimeout(180);
    $exitCode = $process->run(fn ($type, $output) => print ($output));
} finally {
    if ($created) {
        $connection->getPdo()->exec('DROP DATABASE "'.$name.'"');
    }
}
exit($exitCode ?? 1);
