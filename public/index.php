<?php

declare(strict_types=1);

use App\Exchanging\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$projectDir = dirname(__DIR__);

if (class_exists(Dotenv::class) && file_exists($projectDir . '/.env')) {
    (new Dotenv())->bootEnv($projectDir . '/.env');
}

/**
 * Standalone runtime fallback.
 */
$_SERVER['APP_ENV'] ??= $_ENV['APP_ENV'] ?? 'dev';
$_ENV['APP_ENV'] ??= $_SERVER['APP_ENV'];

$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? '1';
$_ENV['APP_DEBUG'] ??= $_SERVER['APP_DEBUG'];

$_SERVER['APP_SECRET'] ??= $_ENV['APP_SECRET'] ?? 'change-me-exchanging-runtime-secret';
$_ENV['APP_SECRET'] ??= $_SERVER['APP_SECRET'];

$_SERVER['DATABASE_URL'] ??= $_ENV['DATABASE_URL'] ?? 'sqlite:///%kernel.project_dir%/var/exchanging_runtime.sqlite';
$_ENV['DATABASE_URL'] ??= $_SERVER['DATABASE_URL'];

$env = (string) ($_SERVER['APP_ENV'] ?? 'dev');
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug && class_exists(Debug::class)) {
    Debug::enable();
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
