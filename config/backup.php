<?php

return [
    'pg_bin' => env('PG_BIN', PHP_OS_FAMILY === 'Windows' ? 'C:/Program Files/PostgreSQL/18/bin' : ''),
    'mysql_bin' => env('MYSQL_BIN', PHP_OS_FAMILY === 'Windows' ? (is_dir('C:/xampp_new/mysql/bin') ? 'C:/xampp_new/mysql/bin' : 'C:/xampp/mysql/bin') : ''),
    'process_timeout' => 180,
];
