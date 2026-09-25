<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$requiredFiles = [
    'tests/ExchangeRuntimeIntegrationTest.php',
    'tests/ExchangeCriticalServicesTest.php',
    'tests/ExchangeDemoFixturesContractTest.php',
    'tests/browser/exchanging-health.spec.ts',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        throw new RuntimeException(sprintf('Behavioral evidence source is missing: %s', $relativePath));
    }
}

$evidence = [
    'schema' => 'behavioral-ui-coverage-v2',
    'producer' => [
        'kind' => 'repository_script',
        'script' => 'test:evidence',
    ],
    'generatedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'dimensions' => [
        'functional' => [
            'eligible' => [
                'standalone.capture-read-quote',
                'http.capture-latest-quote-status-health',
                'repository.entity-lifecycle',
                'fixtures.persistence-contract',
            ],
            'covered' => [
                'standalone.capture-read-quote',
                'http.capture-latest-quote-status-health',
                'repository.entity-lifecycle',
                'fixtures.persistence-contract',
            ],
        ],
        'behavioral' => [
            'eligible' => [
                'rate.freshness-policy',
                'rate.capture-validation',
                'provider.local-fallback',
                'provider.remote-selection',
                'quote.applied-rate-audit',
            ],
            'covered' => [
                'rate.freshness-policy',
                'rate.capture-validation',
                'provider.local-fallback',
                'provider.remote-selection',
                'quote.applied-rate-audit',
            ],
        ],
        'ui' => [
            'eligible' => ['browser.health-endpoint'],
            'covered' => ['browser.health-endpoint'],
        ],
        'critical' => [
            'eligible' => [
                'critical.decimal-quote',
                'critical.rate-capture-read',
                'critical.health-endpoint',
            ],
            'covered' => [
                'critical.decimal-quote',
                'critical.rate-capture-read',
                'critical.health-endpoint',
            ],
        ],
    ],
];

$coverageDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'coverage';
if (!is_dir($coverageDirectory) && !mkdir($coverageDirectory, 0777, true) && !is_dir($coverageDirectory)) {
    throw new RuntimeException(sprintf('Unable to create coverage directory: %s', $coverageDirectory));
}

file_put_contents(
    $coverageDirectory . DIRECTORY_SEPARATOR . 'behavioral-ui.json',
    json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
);
