<?php

use Illuminate\Http\Request;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$asset = realpath(__DIR__.'/../../public'.urldecode($path));
$public = realpath(__DIR__.'/../../public');
if ($path !== '/' && $asset && str_starts_with($asset, $public.DIRECTORY_SEPARATOR) && is_file($asset)) {
    return false;
}
$app = require __DIR__.'/e2e-bootstrap.php';
$app->handleRequest(Request::capture());
