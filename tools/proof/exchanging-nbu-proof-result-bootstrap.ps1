param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $OutputPath = "docs/proof/exchanging-nbu-provider-proof-result.local.adoc",

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

function Ensure-Directory {
    param([Parameter(Mandatory = $true)][string] $DirectoryPath)

    if (-not (Test-Path -LiteralPath $DirectoryPath)) {
        New-Item -ItemType Directory -Force -Path $DirectoryPath | Out-Null
    }
}

$templatePath = Join-Path $ProjectRoot "docs/proof/exchanging-nbu-provider-proof-result-template.adoc"
if (-not (Test-Path -LiteralPath $templatePath -PathType Leaf)) {
    throw "Template not found: $templatePath"
}

$outputFullPath = Join-Path $ProjectRoot $OutputPath
Ensure-Directory -DirectoryPath (Split-Path -Parent $outputFullPath)

if (Test-Path -LiteralPath $outputFullPath) {
    throw "Proof result already exists: $outputFullPath"
}

$content = Get-Content -LiteralPath $templatePath -Raw
$content = $content.Replace("| Pair`r`n| TODO", "| Pair`r`n| `$BaseCurrency/$QuoteCurrency`")
$content = $content.Replace("| Rate date`r`n| TODO", "| Rate date`r`n| `$RateDate`")
$content = $content.Replace("| Host root`r`n| TODO", "| Host root`r`n| `$ProjectRoot`")
$content = $content.Replace("| Proof date`r`n| TODO", "| Proof date`r`n| " + (Get-Date -Format "yyyy-MM-dd"))
$content = $content.Replace("| Operator`r`n| TODO", "| Operator`r`n| local")
$content = $content.Replace("* TODO", "* Capture mode: " + ($(if ($Capture) { "fetch-and-capture" } else { "fetch-only" })))

Set-Content -LiteralPath $outputFullPath -Value $content -Encoding UTF8

Write-Host "Created NBU proof result file: $outputFullPath"
