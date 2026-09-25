param(
    [Parameter(Mandatory = $true)]
    [string] $HostRoot,

    [Parameter(Mandatory = $false)]
    [string] $BaseCurrency = "USD",

    [Parameter(Mandatory = $false)]
    [string] $QuoteCurrency = "UAH",

    [Parameter(Mandatory = $false)]
    [string] $Amount = "100"
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
    Invoke-ProofStep "Composer validate" {
        composer validate
    }

    Invoke-ProofStep "Composer dump-autoload" {
        composer dump-autoload
    }

    Invoke-ProofStep "Symfony cache clear" {
        php bin/console cache:clear
    }

    Invoke-ProofStep "Debug Exchanging routes" {
        php bin/console debug:router | findstr exchanging
    }

    Invoke-ProofStep "Debug Exchanging service interface" {
        php bin/console debug:container App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface
    }

    Invoke-ProofStep "Doctrine mapping info" {
        php bin/console doctrine:mapping:info | findstr Exchanging
    }

    Invoke-ProofStep "Exchanging status" {
        php bin/console exchanging:status
    }

    Invoke-ProofStep "Exchanging health" {
        php bin/console exchanging:health
    }

    Invoke-ProofStep "Seed Exchanging demo rates" {
        php bin/console exchanging:demo:seed-rates
    }

    Invoke-ProofStep "List latest Exchanging rates" {
        php bin/console exchanging:rate:latest --base=$BaseCurrency --quote=$QuoteCurrency --provider=demo --limit=10
    }

    Invoke-ProofStep "Quote Exchanging demo rate" {
        php bin/console exchanging:quote $BaseCurrency $QuoteCurrency $Amount
    }

    Write-Host ""
    Write-Host "Exchanging host runtime proof completed." -ForegroundColor Green
}
finally {
    Pop-Location
}
