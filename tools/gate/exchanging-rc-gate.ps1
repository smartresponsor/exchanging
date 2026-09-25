param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path
)

$ErrorActionPreference = "Stop"

$failures = New-Object System.Collections.Generic.List[string]

function Add-Failure {
    param([Parameter(Mandatory = $true)][string] $Message)
    $script:failures.Add($Message) | Out-Null
}

function Assert-FileExists {
    param([Parameter(Mandatory = $true)][string] $RelativePath)

    $path = Join-Path $ProjectRoot $RelativePath
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) {
        Add-Failure "Missing required file: $RelativePath"
    }
}

function Assert-DirectoryMissing {
    param([Parameter(Mandatory = $true)][string] $RelativePath)

    $path = Join-Path $ProjectRoot $RelativePath
    if (Test-Path -LiteralPath $path -PathType Container) {
        Add-Failure "Forbidden directory exists: $RelativePath"
    }
}

function Assert-FileContains {
    param(
        [Parameter(Mandatory = $true)]
        [string] $RelativePath,
        [Parameter(Mandatory = $true)]
        [string] $Needle
    )

    $path = Join-Path $ProjectRoot $RelativePath
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) {
        Add-Failure "Cannot inspect missing file: $RelativePath"
        return
    }

    $content = Get-Content -LiteralPath $path -Raw
    if (-not $content.Contains($Needle)) {
        Add-Failure "File $RelativePath does not contain required text: $Needle"
    }
}

function Assert-NoForbiddenNamespace {
    $srcPath = Join-Path $ProjectRoot "src"
    if (-not (Test-Path -LiteralPath $srcPath -PathType Container)) {
        Add-Failure "Missing src directory."
        return
    }

    $phpFiles = Get-ChildItem -LiteralPath $srcPath -Recurse -File -Filter "*.php"
    foreach ($file in $phpFiles) {
        $content = Get-Content -LiteralPath $file.FullName -Raw
        if ($content -match "namespace\s+App\\(?!Exchanging(\\|;))") {
            Add-Failure "Non-canonical namespace in $($file.FullName)"
        }
        if ($content -match "namespace\s+App\\Domain") {
            Add-Failure "Forbidden Domain namespace in $($file.FullName)"
        }
    }
}

function Assert-JiraHeader {
    $csvPath = Join-Path $ProjectRoot "delivery/jira/import.csv"
    if (-not (Test-Path -LiteralPath $csvPath -PathType Leaf)) {
        Add-Failure "Missing Jira import CSV."
        return
    }

    $firstLine = Get-Content -LiteralPath $csvPath -TotalCount 1
    $expected = "Issue Type,Summary,Description,Priority,Labels,Component/s,Epic Name,External ID,Parent External ID,Acceptance Criteria"
    if ($firstLine -ne $expected) {
        Add-Failure "Jira CSV header drift. Expected: $expected"
    }
}

Write-Host "Running Exchanging RC gate for: $ProjectRoot"

$requiredFiles = @(
    "composer.json",
    "src/ExchangingBundle.php",
    "src/DependencyInjection/ExchangingExtension.php",
    "config/services/exchange_services.yaml",
    "config/routes/exchange_routes.yaml",
    "config/packages/exchange_doctrine.yaml",
    "config/packages/exchange_provider.yaml",
    "docs/api/openapi/exchanging.openapi.yaml",
    "docs/manifest/exchanging-component-manifest.json",
    "docs/schema/exchanging-schema-manifest.adoc",
    "docs/status/exchanging-operational-status.adoc",
    "docs/health/exchanging-health-check.adoc",
    "docs/read/exchanging-rate-read-surface.adoc",
    "delivery/jira/manifest.yaml",
    "delivery/jira/import.csv",
    "README.adoc"
)

foreach ($file in $requiredFiles) {
    Assert-FileExists $file
}

Assert-DirectoryMissing "src/Domain"
Assert-DirectoryMissing "Domain"

Assert-FileContains "composer.json" '"name": "exchanging/exchange"'
Assert-FileContains "composer.json" '"App\\Exchanging\\": "src/"'
Assert-FileContains "composer.json" '"App\\Exchanging\\ExchangingBundle"'
Assert-FileContains "src/ExchangingBundle.php" "final class ExchangingBundle extends Bundle"
Assert-FileContains "src/DependencyInjection/ExchangingExtension.php" "final class ExchangingExtension extends Extension"

Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"namespace": "App\\Exchanging"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"tablePrefix": "exchange_"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"class": "App\\Exchanging\\ExchangingBundle"'

Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"GET",'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"/exchanging/rates/latest"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"/exchanging/status"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"/exchanging/health"'

Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"exchanging:rate:latest"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"exchanging:status"'
Assert-FileContains "docs/manifest/exchanging-component-manifest.json" '"exchanging:health"'

Assert-FileContains "docs/api/openapi/exchanging.openapi.yaml" "/exchanging/rates/latest:"
Assert-FileContains "docs/api/openapi/exchanging.openapi.yaml" "/exchanging/status:"
Assert-FileContains "docs/api/openapi/exchanging.openapi.yaml" "/exchanging/health:"

Assert-FileContains "config/services/exchange_services.yaml" "ExchangeOperationalStatusServiceInterface"
Assert-FileContains "config/services/exchange_services.yaml" "ExchangeHealthCheckServiceInterface"
Assert-FileContains "config/services/exchange_services.yaml" "ExchangeRateReadServiceInterface"

Assert-FileContains "delivery/jira/manifest.yaml" "component: Exchanging"
Assert-FileContains "delivery/jira/manifest.yaml" "package: exchanging/exchange"
Assert-FileContains "docs/schema/exchanging-schema-manifest.adoc" "exchange_exchange"
Assert-FileContains "docs/schema/exchanging-schema-manifest.adoc" "exchange_rate"

Assert-NoForbiddenNamespace
Assert-JiraHeader

if ($failures.Count -gt 0) {
    Write-Host ""
    Write-Host "Exchanging RC gate failed:" -ForegroundColor Red
    foreach ($failure in $failures) {
        Write-Host " - $failure" -ForegroundColor Red
    }
    exit 1
}

Write-Host "Exchanging RC gate passed." -ForegroundColor Green
exit 0
