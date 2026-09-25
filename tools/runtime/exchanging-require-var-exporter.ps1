param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $Constraint = "^7.0"
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

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

Push-Location $ProjectRoot

try {
    Write-Host "Adding symfony/var-exporter runtime dependency in: $ProjectRoot" -ForegroundColor Cyan
    composer require "symfony/var-exporter:$Constraint"

    Write-Host ""
    Write-Host "Regenerating autoload..." -ForegroundColor Cyan
    composer dump-autoload

    Write-Host ""
    Write-Host "Checking Symfony runtime..." -ForegroundColor Cyan
    php bin/console cache:clear
    php bin/console list exchanging

    Write-Host ""
    Write-Host "symfony/var-exporter dependency is installed and Exchanging runtime check completed." -ForegroundColor Green
}
finally {
    Pop-Location
}
