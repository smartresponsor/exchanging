param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path
)

$ErrorActionPreference = "Stop"

function Assert-PathExists {
    param([Parameter(Mandatory = $true)][string] $PathValue, [Parameter(Mandatory = $true)][string] $Label)

    if (-not (Test-Path -LiteralPath $PathValue)) {
        throw "$Label does not exist: $PathValue"
    }
}

function Invoke-Step {
    param([Parameter(Mandatory = $true)][string] $Title, [Parameter(Mandatory = $true)][scriptblock] $Command)

    Write-Host ""
    Write-Host "== $Title ==" -ForegroundColor Cyan
    & $Command

    if ($LASTEXITCODE -ne $null -and $LASTEXITCODE -ne 0) {
        throw "Step failed: $Title"
    }
}

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

Push-Location $ProjectRoot

try {
    Invoke-Step "Remove composer.json BOM if present" {
        & powershell -ExecutionPolicy Bypass -File "tools/runtime/exchanging-remove-composer-bom.ps1" -ProjectRoot $ProjectRoot
    }

    Invoke-Step "Fix exact Composer constraints" {
        & powershell -ExecutionPolicy Bypass -File "tools/runtime/exchanging-fix-exact-composer-constraints.ps1" -ProjectRoot $ProjectRoot
    }

    Invoke-Step "Targeted Symfony 8 / Doctrine update" {
        & composer update `
            doctrine/doctrine-bundle `
            doctrine/dbal `
            doctrine/persistence `
            symfony/framework-bundle `
            symfony/console `
            symfony/runtime `
            symfony/dotenv `
            symfony/yaml `
            symfony/http-client `
            symfony/var-exporter `
            --with-all-dependencies
    }

    Invoke-Step "Dump autoload" {
        & composer dump-autoload
    }

    Invoke-Step "Validate composer" {
        & composer validate
    }

    Invoke-Step "Cache clear" {
        & php bin/console cache:clear
    }

    Invoke-Step "List Exchanging commands" {
        & php bin/console list exchanging
    }

    Invoke-Step "Exchanging status" {
        & php bin/console exchanging:status
    }

    Invoke-Step "Exchanging health" {
        & php bin/console exchanging:health
    }

    Write-Host ""
    Write-Host "Targeted Symfony 8 / Doctrine runtime update completed." -ForegroundColor Green
}
finally {
    Pop-Location
}
