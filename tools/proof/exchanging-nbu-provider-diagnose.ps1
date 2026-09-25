param(
    [Parameter(Mandatory = $false)]
    [string] $HostRoot = (Get-Location).Path
)

$ErrorActionPreference = "Continue"

function Write-Check {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name,
        [Parameter(Mandatory = $true)]
        [bool] $Passed,
        [Parameter(Mandatory = $false)]
        [string] $Message = ""
    )

    if ($Passed) {
        Write-Host "[PASS] $Name $Message" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Name $Message" -ForegroundColor Red
    }
}

Push-Location $HostRoot

try {
    Write-Host "Exchanging NBU provider diagnostics for: $HostRoot" -ForegroundColor Cyan

    Write-Check "Provider class file" (Test-Path -LiteralPath "src/Service/Exchange/ExchangeNbuRemoteRateProvider.php" -PathType Leaf)
    Write-Check "Provider config file" (Test-Path -LiteralPath "config/packages/exchange_nbu_provider.yaml" -PathType Leaf)

    $hostImportCandidates = @(
        "config/packages/exchange_nbu_import.yaml",
        "config/packages/exchange_nbu_provider.yaml"
    )

    $hasImport = $false
    foreach ($candidate in $hostImportCandidates) {
        if (Test-Path -LiteralPath $candidate -PathType Leaf) {
            $content = Get-Content -LiteralPath $candidate -Raw
            if ($content.Contains("exchange_nbu_provider.yaml")) {
                $hasImport = $true
            }
        }
    }

    Write-Check "Host NBU import visible" $hasImport

    Write-Host ""
    Write-Host "Suggested next commands:" -ForegroundColor Yellow
    Write-Host "composer dump-autoload"
    Write-Host "php bin/console cache:clear -vvv"
    Write-Host "php bin/console debug:container App\Exchanging\Provider\ExchangeNbuRemoteRateProvider"
    Write-Host "php bin/console exchanging:rate:fetch USD UAH nbu --rate-date=2026-05-03"
}
finally {
    Pop-Location
}
