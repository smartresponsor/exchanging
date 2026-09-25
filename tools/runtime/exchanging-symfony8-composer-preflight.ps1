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

function Test-ConstraintAllowsSymfony8 {
    param([Parameter(Mandatory = $true)][string] $Constraint)

    return $Constraint -match "\^8\.1" -or $Constraint -match "\|\|\s*\^8\.1"
}

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

$composerPath = Join-Path $ProjectRoot "composer.json"
Assert-PathExists -PathValue $composerPath -Label "composer.json"

$composer = Get-Content -LiteralPath $composerPath -Raw | ConvertFrom-Json
$failed = $false

Write-Host "Exchanging Symfony 8 composer preflight: $ProjectRoot" -ForegroundColor Cyan

$sections = @("require", "require-dev")
foreach ($section in $sections) {
    if (-not $composer.PSObject.Properties.Name.Contains($section)) {
        continue
    }

    $requirements = $composer.$section
    foreach ($property in $requirements.PSObject.Properties) {
        $name = $property.Name
        $constraint = [string] $property.Value

        if ($name -like "symfony/*" -and $name -ne "symfony/maker-bundle") {
            if (-not (Test-ConstraintAllowsSymfony8 -Constraint $constraint)) {
                Write-Host "[FAIL] $section $name uses '$constraint', expected Symfony ^8.1-only direction." -ForegroundColor Red
                $failed = $true
            } else {
                Write-Host "[PASS] $section $name => $constraint" -ForegroundColor Green
            }
        }

        if ($name -eq "doctrine/doctrine-bundle") {
            if ($constraint -notmatch "\^3\.") {
                Write-Host "[FAIL] $section doctrine/doctrine-bundle uses '$constraint', expected ^3.x for Symfony 8 compatibility." -ForegroundColor Red
                $failed = $true
            } else {
                Write-Host "[PASS] $section doctrine/doctrine-bundle => $constraint" -ForegroundColor Green
            }
        }

        if ($name -eq "doctrine/dbal") {
            if ($constraint -notmatch "\^4\.") {
                Write-Host "[FAIL] $section doctrine/dbal uses '$constraint', expected ^4.x for DoctrineBundle 3.x alignment." -ForegroundColor Red
                $failed = $true
            } else {
                Write-Host "[PASS] $section doctrine/dbal => $constraint" -ForegroundColor Green
            }
        }
    }
}

if ($failed) {
    Write-Host ""
    Write-Host "Run tools/runtime/exchanging-upgrade-symfony8-only.ps1 to migrate runtime Symfony/Doctrine constraints." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "[PASS] Composer constraints are aligned for Symfony ^8.1 direction." -ForegroundColor Green
exit 0
