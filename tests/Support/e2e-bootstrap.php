<?php

use App\Services\GeminiService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Tests\Support\FakeGeminiService;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = realpath(config('database.connections.sqlite.database'));
$allowedRoot = realpath(__DIR__.'/../../.tools/e2e');
if (! $app->environment('e2e') || config('database.default') !== 'sqlite' || ! $database || ! $allowedRoot
    || ! str_starts_with($database, $allowedRoot.DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('E2E requires its own SQLite file under .tools/e2e.');
}
config(['filesystems.disks.local.root' => dirname($database).'/private', 'filesystems.disks.public.root' => dirname($database).'/public']);
$app->useStoragePath(dirname($database).'/storage');
File::ensureDirectoryExists(storage_path('framework'));
require_once __DIR__.'/FakeGeminiService.php';
$app->instance(GeminiService::class, new FakeGeminiService);

return $app;
