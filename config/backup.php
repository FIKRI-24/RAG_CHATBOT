<?php

return [
    'pg_bin' => env('PG_BIN', PHP_OS_FAMILY === 'Windows' ? 'C:/Program Files/PostgreSQL/18/bin' : ''),
    'process_timeout' => 180,
];
