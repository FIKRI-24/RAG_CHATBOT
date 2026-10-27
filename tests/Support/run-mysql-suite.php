<?php

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Process\Process;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$config = config('database.connections.mysql');
$host = $config['host'] ?? '127.0.0.1';
$port = (string) ($config['port'] ?? 3306);
$user = $config['username'] ?? 'root';
$pass = (string) ($config['password'] ?? '');

$pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$name = 'rag_testing_'.bin2hex(random_bytes(6));
$created = false;
try {
    $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $created = true;
    $process = new Process(['php', 'artisan', 'test', '--compact'], base_path(), [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => $name,
        'DB_HOST' => $host,
        'DB_PORT' => $port,
        'DB_USERNAME' => $user,
        'DB_PASSWORD' => $pass,
        'DB_URL' => '',
        'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array',
        'MAIL_MAILER' => 'array',
    ]);
    $process->setTimeout(300);
    $exitCode = $process->run(fn ($type, $output) => print ($output));
} finally {
    if ($created) {
        $pdo->exec("DROP DATABASE IF EXISTS `{$name}`");
    }
}
exit($exitCode ?? 1);
