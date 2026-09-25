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

function Write-Utf8NoBom {
    param(
        [Parameter(Mandatory = $true)]
        [string] $PathValue,
        [Parameter(Mandatory = $true)]
        [string] $Content
    )

    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($PathValue, $Content, $utf8NoBom)
}

function Remove-Utf8Bom {
    param([Parameter(Mandatory = $true)][string] $PathValue)

    $bytes = [System.IO.File]::ReadAllBytes($PathValue)
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        $newBytes = New-Object byte[] ($bytes.Length - 3)
        [Array]::Copy($bytes, 3, $newBytes, 0, $newBytes.Length)
        [System.IO.File]::WriteAllBytes($PathValue, $newBytes)
    }
}

Assert-PathExists -PathValue $ProjectRoot -Label "ProjectRoot"

$composerPath = Join-Path $ProjectRoot "composer.json"
Assert-PathExists -PathValue $composerPath -Label "composer.json"

Remove-Utf8Bom -PathValue $composerPath

$composer = Get-Content -LiteralPath $composerPath -Raw | ConvertFrom-Json
$changed = $false

$replacements = @{
    "doctrine/doctrine-bundle" = "^3.2"
    "doctrine/dbal" = "^4.0"
    "doctrine/persistence" = "^4.0"
    "symfony/framework-bundle" = "^8.1"
    "symfony/console" = "^8.1"
    "symfony/runtime" = "^8.1"
    "symfony/dotenv" = "^8.1"
    "symfony/yaml" = "^8.1"
    "symfony/http-client" = "^8.1"
    "symfony/var-exporter" = "^8.1"
}

foreach ($section in @("require", "require-dev")) {
    if (-not $composer.PSObject.Properties.Name.Contains($section)) {
        continue
    }

    foreach ($name in $replacements.Keys) {
        if ($composer.$section.PSObject.Properties.Name.Contains($name)) {
            $current = [string] $composer.$section.$name
            $target = $replacements[$name]

            if ($current -eq $target) {
                continue
            }

            if ($current -in @("3.2", "4.0", "8.0") -or $current -match "^\d+\.\d+$") {
                Write-Host "Fixing ${section} ${name}: $current -> $target" -ForegroundColor Cyan
                $composer.$section.$name = $target
                $changed = $true
            }
        }
    }
}

if (-not $changed) {
    Write-Host "No exact Composer constraints needed correction." -ForegroundColor Green
    exit 0
}

$json = $composer | ConvertTo-Json -Depth 100
Write-Utf8NoBom -PathValue $composerPath -Content ($json + [Environment]::NewLine)

Write-Host "composer.json constraints corrected and saved as UTF-8 without BOM." -ForegroundColor Green
Write-Host "Next recommended command:" -ForegroundColor Yellow
Write-Host "composer update doctrine/doctrine-bundle doctrine/dbal doctrine/persistence symfony/framework-bundle symfony/console symfony/runtime symfony/dotenv symfony/yaml symfony/http-client symfony/var-exporter --with-all-dependencies"
