<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

Config::loadEnv($base);
date_default_timezone_set('Asia/Jakarta');

// error handling: dev tampilkan, prod sembunyikan + log
$isDebug = filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
if ($isDebug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
}
set_exception_handler(function (Throwable $e) use ($isDebug): void {
    Logger::error('Unhandled', ['msg' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
    if (!$isDebug) {
        http_response_code(500);
        require dirname(__DIR__) . '/app/Views/errors/500.phtml';
        exit;
    }
    throw $e;
});

Session::start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$router = new Router();
require $base . '/config/routes.php';
$router->dispatch(Request::capture());
