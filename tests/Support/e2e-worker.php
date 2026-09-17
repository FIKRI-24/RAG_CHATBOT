<?php

use Illuminate\Contracts\Console\Kernel;

$app = require __DIR__.'/e2e-bootstrap.php';
$kernel = $app->make(Kernel::class);
exit($kernel->call('queue:work', ['connection' => 'rag', '--queue' => 'rag', '--once' => true, '--tries' => 1, '--timeout' => 300]));
