<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$varDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'var';
$databasePath = $varDirectory . DIRECTORY_SEPARATOR . 'schema-parity.sqlite';

if (!is_dir($varDirectory) && !mkdir($varDirectory, 0777, true) && !is_dir($varDirectory)) {
    throw new RuntimeException(sprintf('Unable to create %s.', $varDirectory));
}

if (is_file($databasePath) && !unlink($databasePath)) {
    throw new RuntimeException(sprintf('Unable to remove stale parity database %s.', $databasePath));
}

$databaseUrl = 'sqlite:///' . str_replace('\\', '/', $databasePath);
putenv('APP_ENV=test');
putenv('APP_DEBUG=1');
putenv('DATABASE_URL=' . $databaseUrl);
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';
$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $databaseUrl;

$commands = [
    ['doctrine:migrations:migrate', '--no-interaction', '--env=test'],
    ['doctrine:migrations:up-to-date', '--env=test'],
    ['doctrine:schema:validate', '--env=test'],
];

try {
    foreach ($commands as $arguments) {
        $command = array_merge([PHP_BINARY, $projectRoot . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console'], $arguments);
        $escaped = implode(' ', array_map(static fn (string $argument): string => escapeshellarg($argument), $command));
        passthru($escaped, $exitCode);
        if (0 !== $exitCode) {
            throw new RuntimeException(sprintf('Schema parity command failed (%d): %s', $exitCode, implode(' ', $arguments)));
        }
    }
} finally {
    if (is_file($databasePath)) {
        unlink($databasePath);
    }
}

