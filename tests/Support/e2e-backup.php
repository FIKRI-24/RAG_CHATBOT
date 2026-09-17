<?php

use App\Services\BackupService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/e2e-bootstrap.php';
$kernel = $app->make(Kernel::class);
$usersBefore = DB::table('users')->count();
$kernel->call('down');
try {
    $service = $app->make(BackupService::class);
    $path = $service->create();
    $result = $service->verify($path);
    if (! $result['restored'] || $result['counts']['users'] !== $usersBefore) {
        throw new RuntimeException('Restore mismatch');
    }
    $corrupt = dirname($path).'/corrupt.zip';
    copy($path, $corrupt);
    $zip = new ZipArchive;
    $zip->open($corrupt);
    $zip->addFromString('database/database.sqlite', 'tampered database');
    $zip->close();
    $rejected = false;
    try {
        $service->verify($corrupt);
    } catch (RuntimeException $e) {
        $rejected = true;
    }
    if (! $rejected || DB::table('users')->count() !== $usersBefore) {
        throw new RuntimeException('Tampering must be rejected without changing the live fixture.');
    }
    echo "SQLite restore and tamper rejection passed.\n";
} finally {
    $kernel->call('up');
}
