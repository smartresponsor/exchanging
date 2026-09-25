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

$composerPath = Join-Path $ProjectRoot "composer.json"
Assert-PathExists -PathValue $composerPath -Label "composer.json"

$bytes = [System.IO.File]::ReadAllBytes($composerPath)

if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
    $newBytes = New-Object byte[] ($bytes.Length - 3)
    [Array]::Copy($bytes, 3, $newBytes, 0, $newBytes.Length)
    [System.IO.File]::WriteAllBytes($composerPath, $newBytes)
    Write-Host "Removed UTF-8 BOM from composer.json" -ForegroundColor Green
} else {
    Write-Host "composer.json has no UTF-8 BOM." -ForegroundColor Green
}

Push-Location $ProjectRoot
try {
    composer validate
}
finally {
    Pop-Location
}
