param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $OutputPath = "docs/proof/exchanging-host-runtime-proof-result.local.adoc"
)

$ErrorActionPreference = "Stop"

function Ensure-Directory {
    param([Parameter(Mandatory = $true)][string] $DirectoryPath)

    if (-not (Test-Path -LiteralPath $DirectoryPath)) {
        New-Item -ItemType Directory -Force -Path $DirectoryPath | Out-Null
    }
}

$templatePath = Join-Path $ProjectRoot "docs/proof/exchanging-host-runtime-proof-result-template.adoc"
if (-not (Test-Path -LiteralPath $templatePath -PathType Leaf)) {
    throw "Template not found: $templatePath"
}

$outputFullPath = Join-Path $ProjectRoot $OutputPath
Ensure-Directory -DirectoryPath (Split-Path -Parent $outputFullPath)

if (Test-Path -LiteralPath $outputFullPath) {
    throw "Proof result already exists: $outputFullPath"
}

Copy-Item -LiteralPath $templatePath -Destination $outputFullPath

Write-Host "Created proof result file: $outputFullPath"
