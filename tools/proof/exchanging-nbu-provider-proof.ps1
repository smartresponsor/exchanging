param(
    [Parameter(Mandatory = $false)]
    [string] $HostRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $BaseCurrency = "USD",

    [Parameter(Mandatory = $false)]
    [string] $QuoteCurrency = "UAH",

    [Parameter(Mandatory = $false)]
    [string] $RateDate = "2026-05-03",

    [Parameter(Mandatory = $false)]
    [switch] $Capture
)

$ErrorActionPreference = "Stop"

function Assert-PathExists {
    param(
        [Parameter(Mandatory = $true)]
        [string] $PathValue,
        [Parameter(Mandatory = $true)]
        [string] $Label
    )

    if (-not (Test-Path -LiteralPath $PathValue)) {
        throw "$Label does not exist: $PathValue"
    }
}

function Invoke-ProofStep {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Title,
        [Parameter(Mandatory = $true)]
        [scriptblock] $Command
    )

    Write-Host ""
    Write-Host "== $Title ==" -ForegroundColor Cyan
    & $Command

    if ($LASTEXITCODE -ne $null -and $LASTEXITCODE -ne 0) {
        throw "Step failed: $Title"
    }
}

Assert-PathExists -PathValue $HostRoot -Label "HostRoot"

Push-Location $HostRoot

try {
    Invoke-ProofStep "Verify NBU optional config file exists" {
        if (-not (Test-Path -LiteralPath "config/packages/exchange_nbu_provider.yaml" -PathType Leaf)) {
            throw "Missing optional provider config: config/packages/exchange_nbu_provider.yaml"
        }
        Write-Host "NBU optional provider config exists."
    }

    Invoke-ProofStep "Verify NBU provider class exists" {
        if (-not (Test-Path -LiteralPath "src/Service/Exchange/ExchangeNbuRemoteRateProvider.php" -PathType Leaf)) {
            throw "Missing NBU provider class."
        }
        Write-Host "NBU provider class exists."
    }

    Invoke-ProofStep "Composer dump-autoload" {
        composer dump-autoload
    }

    Invoke-ProofStep "Symfony cache clear" {
        php bin/console cache:clear
    }

    Invoke-ProofStep "Debug NBU provider service" {
        php bin/console debug:container App\Exchanging\Provider\ExchangeNbuRemoteRateProvider
    }

    Invoke-ProofStep "Fetch NBU rate" {
        php bin/console exchanging:rate:fetch $BaseCurrency $QuoteCurrency nbu --rate-date=$RateDate
    }

    if ($Capture) {
        Invoke-ProofStep "Fetch and capture NBU rate" {
            php bin/console exchanging:rate:fetch-capture $BaseCurrency $QuoteCurrency nbu --rate-date=$RateDate
        }

        Invoke-ProofStep "List captured NBU latest rates" {
            php bin/console exchanging:rate:latest --base=$BaseCurrency --quote=$QuoteCurrency --provider=nbu --limit=10
        }
    }

    Write-Host ""
    Write-Host "Exchanging NBU provider proof completed." -ForegroundColor Green
}
finally {
    Pop-Location
}
