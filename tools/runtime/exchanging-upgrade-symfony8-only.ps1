param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path
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

function Invoke-Step {
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

function New-Constraint {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Package,
        [Parameter(Mandatory = $true)]
        [string] $Version
    )

    # Avoid PowerShell/cmd caret stripping in nested shells by constructing caret from char code.
    return $Package + ':' + [char]94 + $Version
}

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

Push-Location $ProjectRoot

try {
    $doctrineRequirements = @(
        (New-Constraint 'doctrine/doctrine-bundle' '3.2'),
        (New-Constraint 'doctrine/dbal' '4.0'),
        (New-Constraint 'doctrine/persistence' '4.0')
    )

    $symfonyRequirements = @(
        (New-Constraint 'symfony/framework-bundle' '8.1'),
        (New-Constraint 'symfony/console' '8.1'),
        (New-Constraint 'symfony/runtime' '8.1'),
        (New-Constraint 'symfony/dotenv' '8.1'),
        (New-Constraint 'symfony/yaml' '8.1'),
        (New-Constraint 'symfony/http-client' '8.1'),
        (New-Constraint 'symfony/var-exporter' '8.1')
    )

    Invoke-Step "Require Doctrine Symfony 8 compatible persistence stack" {
        & composer require @doctrineRequirements --with-all-dependencies
    }

    Invoke-Step "Require Symfony 8 runtime packages" {
        & composer require @symfonyRequirements --with-all-dependencies
    }

    Invoke-Step "Regenerate autoload" {
        & composer dump-autoload
    }

    Invoke-Step "Validate composer" {
        & composer validate
    }

    Invoke-Step "Symfony cache clear" {
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
    Write-Host "Exchanging composer runtime upgraded to Symfony ^8.1-only direction with DoctrineBundle ^3.2 alignment." -ForegroundColor Green
}
finally {
    Pop-Location
}
