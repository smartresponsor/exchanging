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

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

Write-Host "Exchanging runtime config preflight: $ProjectRoot" -ForegroundColor Cyan

$yamlFiles = Get-ChildItem -LiteralPath $ProjectRoot -Recurse -File -Include "*.yaml","*.yml" |
    Where-Object {
        $_.FullName -like "*\config\*" -and
        $_.FullName -notlike "*\vendor\*" -and
        $_.FullName -notlike "*\var\*"
    }

$foundLazyGhost = $false

foreach ($file in $yamlFiles) {
    $content = Get-Content -LiteralPath $file.FullName -Raw
    if ($content -match "enable_lazy_ghost_objects\s*:\s*true") {
        $foundLazyGhost = $true
        Write-Host "[FAIL] LazyGhost enabled in: $($file.FullName)" -ForegroundColor Red
    }
}

if ($foundLazyGhost) {
    Write-Host ""
    Write-Host "Standalone runtime cannot boot with enable_lazy_ghost_objects: true unless symfony/var-exporter/native lazy objects are available." -ForegroundColor Red
    Write-Host "For direct Exchanging runtime, remove that option from runtime-loaded Doctrine config." -ForegroundColor Red
    exit 1
}

Write-Host "[PASS] No config YAML enables Doctrine LazyGhost explicitly." -ForegroundColor Green
exit 0
