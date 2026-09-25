$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot

& composer run-script test:coverage
if ($LASTEXITCODE -ne 0) {
    throw "Composer coverage failed with exit code $LASTEXITCODE."
}

& npm test
if ($LASTEXITCODE -ne 0) {
    throw "Playwright test failed with exit code $LASTEXITCODE."
}

Write-Host 'RC evidence tests completed successfully.'
