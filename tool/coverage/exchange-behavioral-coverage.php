<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$manifestPath = $root . '/docs/manifest/exchanging-component-manifest.json';
$runtimeTestPath = $root . '/tests/ExchangeRuntimeIntegrationTest.php';
$browserTestPath = $root . '/tests/browser/exchanging-health.spec.ts';
$outputPath = $root . '/var/coverage/behavioral-ui.json';

$manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
$runtimeTest = (string) file_get_contents($runtimeTestPath);
$browserTest = (string) file_get_contents($browserTestPath);

$routes = $manifest['http']['routes'] ?? [];
if (!is_array($routes)) {
    throw new RuntimeException('Exchanging component manifest does not expose an HTTP route inventory.');
}

$functionalEligible = [];
$functionalCovered = [];

foreach ($routes as $route) {
    if (!is_array($route) || !isset($route['path'], $route['methods']) || !is_array($route['methods'])) {
        throw new RuntimeException('Exchanging component manifest contains an invalid HTTP route entry.');
    }

    $path = (string) $route['path'];
    foreach ($route['methods'] as $method) {
        $identifier = sprintf('route:%s %s', strtoupper((string) $method), $path);
        $functionalEligible[] = $identifier;

        if (str_contains($runtimeTest, $path)) {
            $functionalCovered[] = $identifier;
        }
    }
}

$behavioralEligible = [
    'workflow:cli-capture-read-quote',
    'workflow:http-capture-read-quote-status-health',
];
$behavioralCovered = [];

if (str_contains($runtimeTest, 'testStandaloneCaptureReadAndQuoteFlow')) {
    $behavioralCovered[] = 'workflow:cli-capture-read-quote';
}

if (str_contains($runtimeTest, 'testHttpCaptureLatestQuoteStatusAndHealthFlow')) {
    $behavioralCovered[] = 'workflow:http-capture-read-quote-status-health';
}

$uiEligible = [
    'surface:http-health-browser',
];
$uiCovered = [];

if (
    str_contains($browserTest, "page.goto('/exchanging/health')")
    && str_contains($browserTest, 'expect(response?.status()).toBe(200)')
) {
    $uiCovered[] = 'surface:http-health-browser';
}

$criticalEligible = [
    'workflow:http-capture-read-quote-status-health',
];
$criticalCovered = array_values(array_intersect($criticalEligible, $behavioralCovered));

$evidence = [
    'schema' => 'behavioral-ui-coverage-v2',
    'generatedAt' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
    'producer' => [
        'kind' => 'repository_script',
        'script' => 'test:behavioral-coverage',
    ],
    'dimensions' => [
        'functional' => [
            'eligible' => $functionalEligible,
            'covered' => $functionalCovered,
        ],
        'behavioral' => [
            'eligible' => $behavioralEligible,
            'covered' => $behavioralCovered,
        ],
        'ui' => [
            'eligible' => $uiEligible,
            'covered' => $uiCovered,
        ],
        'critical' => [
            'eligible' => $criticalEligible,
            'covered' => $criticalCovered,
        ],
    ],
];

$directory = dirname($outputPath);
if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
    throw new RuntimeException(sprintf('Unable to create behavioral coverage directory: %s', $directory));
}

$json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (false === file_put_contents($outputPath, $json . PHP_EOL)) {
    throw new RuntimeException(sprintf('Unable to write behavioral coverage evidence: %s', $outputPath));
}

fwrite(STDOUT, sprintf("Behavioral/UI coverage evidence written to %s%s", $outputPath, PHP_EOL));
