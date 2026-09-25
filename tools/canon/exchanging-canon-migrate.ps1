param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
)

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $ProjectRoot

function Move-ExactFile {
    param(
        [Parameter(Mandatory = $true)][string] $Source,
        [Parameter(Mandatory = $true)][string] $Destination
    )

    if (-not (Test-Path -LiteralPath $Source -PathType Leaf)) {
        throw "Expected source file is missing: $Source"
    }

    if (Test-Path -LiteralPath $Destination) {
        throw "Destination already exists: $Destination"
    }

    $destinationDirectory = Split-Path -Parent $Destination
    if (-not (Test-Path -LiteralPath $destinationDirectory -PathType Container)) {
        New-Item -ItemType Directory -Path $destinationDirectory | Out-Null
    }

    Move-Item -LiteralPath $Source -Destination $Destination
}

if (Test-Path -LiteralPath 'src/Dto' -PathType Container) {
    if (Test-Path -LiteralPath 'src/DTO') {
        throw 'Cannot normalize src/Dto because src/DTO already exists.'
    }
    Move-Item -LiteralPath 'src/Dto' -Destination 'src/DTO'
}

$flattenRoots = @(
    'Command',
    'Controller',
    'DTO',
    'Entity',
    'EntityInterface',
    'Exception',
    'Repository',
    'RepositoryInterface',
    'Service',
    'ServiceInterface',
    'ValueObject'
)

foreach ($root in $flattenRoots) {
    $subjectDirectory = Join-Path (Join-Path 'src' $root) 'Exchange'
    if (-not (Test-Path -LiteralPath $subjectDirectory -PathType Container)) {
        continue
    }

    $nestedDirectories = @(Get-ChildItem -LiteralPath $subjectDirectory -Directory)
    if ($nestedDirectories.Count -gt 0) {
        throw "Unexpected nested directory below $subjectDirectory; refusing implicit flatten."
    }

    foreach ($file in Get-ChildItem -LiteralPath $subjectDirectory -File) {
        $destination = Join-Path (Split-Path -Parent $subjectDirectory) $file.Name
        if (Test-Path -LiteralPath $destination) {
            throw "Flatten destination already exists: $destination"
        }
        Move-Item -LiteralPath $file.FullName -Destination $destination
    }

    Remove-Item -LiteralPath $subjectDirectory
}

$providerMoves = [ordered]@{
    'src/Service/ExchangeNeighborHookContextProvider.php' = 'src/Provider/ExchangeNeighborHookContextProvider.php'
    'src/Service/ExchangeTemplateContextProvider.php' = 'src/Provider/ExchangeTemplateContextProvider.php'
    'src/Service/ExchangeHttpJsonRemoteRateProvider.php' = 'src/Provider/ExchangeHttpJsonRemoteRateProvider.php'
    'src/Service/ExchangeManualRemoteRateProvider.php' = 'src/Provider/ExchangeManualRemoteRateProvider.php'
    'src/Service/ExchangeNbuRemoteRateProvider.php' = 'src/Provider/ExchangeNbuRemoteRateProvider.php'
    'src/Service/ExchangeRepositoryRateProvider.php' = 'src/Provider/ExchangeRepositoryRateProvider.php'
    'src/Service/ExchangeStaticRateProvider.php' = 'src/Provider/ExchangeStaticRateProvider.php'
    'src/ServiceInterface/ExchangeNeighborHookContextProviderInterface.php' = 'src/ProviderInterface/ExchangeNeighborHookContextProviderInterface.php'
    'src/ServiceInterface/ExchangeTemplateContextProviderInterface.php' = 'src/ProviderInterface/ExchangeTemplateContextProviderInterface.php'
    'src/ServiceInterface/ExchangeRateProviderInterface.php' = 'src/ProviderInterface/ExchangeRateProviderInterface.php'
    'src/ServiceInterface/ExchangeRemoteRateProviderInterface.php' = 'src/ProviderInterface/ExchangeRemoteRateProviderInterface.php'
    'src/Service/ExchangeNbuRateResponseNormalizer.php' = 'src/Normalizer/ExchangeNbuRateResponseNormalizer.php'
}

foreach ($entry in $providerMoves.GetEnumerator()) {
    Move-ExactFile -Source $entry.Key -Destination $entry.Value
}

foreach ($file in Get-ChildItem -LiteralPath 'src/DTO' -File -Filter '*Dto.php') {
    $destination = Join-Path $file.DirectoryName ($file.Name -replace 'Dto\.php$', 'DTO.php')
    if (Test-Path -LiteralPath $destination) {
        throw "DTO destination already exists: $destination"
    }
    Move-Item -LiteralPath $file.FullName -Destination $destination
}

$specificMoves = [ordered]@{
    'src/DataFixtures/ExchangeDemoFixtures.php' = 'src/DataFixtures/ExchangeDemoFixtures.php'
    'src/DTO/ExchangeNbuRateNormalizedResponseDTO.php' = 'src/DTO/ExchangeNbuRateNormalizedResponseDTO.php'
    'src/Exception/ExchangeInvalidRequestException.php' = 'src/Exception/ExchangeInvalidRequestException.php'
    'src/Exception/ExchangeStaleRateException.php' = 'src/Exception/ExchangeStaleRateException.php'
    'src/Provider/ExchangeHttpJsonRemoteRateProvider.php' = 'src/Provider/ExchangeHttpJsonRemoteRateProvider.php'
    'src/Provider/ExchangeManualRemoteRateProvider.php' = 'src/Provider/ExchangeManualRemoteRateProvider.php'
    'src/Normalizer/ExchangeNbuRateResponseNormalizer.php' = 'src/Normalizer/ExchangeNbuRateResponseNormalizer.php'
    'src/Provider/ExchangeNbuRemoteRateProvider.php' = 'src/Provider/ExchangeNbuRemoteRateProvider.php'
    'src/Provider/ExchangeRepositoryRateProvider.php' = 'src/Provider/ExchangeRepositoryRateProvider.php'
    'src/Provider/ExchangeStaticRateProvider.php' = 'src/Provider/ExchangeStaticRateProvider.php'
    'src/ValueObject/ExchangeCurrencyCode.php' = 'src/ValueObject/ExchangeCurrencyCode.php'
    'src/ValueObject/ExchangeCurrencyPair.php' = 'src/ValueObject/ExchangeCurrencyPair.php'
    'src/Entity/Exchange.php' = 'src/Entity/ExchangeEntity.php'
    'src/Entity/ExchangeRate.php' = 'src/Entity/ExchangeRateEntity.php'
}

foreach ($entry in $specificMoves.GetEnumerator()) {
    Move-ExactFile -Source $entry.Key -Destination $entry.Value
}

$literalReplacements = [ordered]@{
    'App\\Exchanging\\Service\\Exchange\\ExchangeNeighborHookContextProvider' = 'App\\Exchanging\\Provider\\ExchangeNeighborHookContextProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeTemplateContextProvider' = 'App\\Exchanging\\Provider\\ExchangeTemplateContextProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeHttpJsonRemoteRateProvider' = 'App\\Exchanging\\Provider\\ExchangeHttpJsonRemoteRateProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeManualRemoteRateProvider' = 'App\\Exchanging\\Provider\\ExchangeManualRemoteRateProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeNbuRemoteRateProvider' = 'App\\Exchanging\\Provider\\ExchangeNbuRemoteRateProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeRepositoryRateProvider' = 'App\\Exchanging\\Provider\\ExchangeRepositoryRateProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeStaticRateProvider' = 'App\\Exchanging\\Provider\\ExchangeStaticRateProvider'
    'App\\Exchanging\\Service\\Exchange\\ExchangeNbuRateResponseNormalizer' = 'App\\Exchanging\\Normalizer\\ExchangeNbuRateResponseNormalizer'
    'App\\Exchanging\\ServiceInterface\\Exchange\\ExchangeNeighborHookContextProviderInterface' = 'App\\Exchanging\\ProviderInterface\\ExchangeNeighborHookContextProviderInterface'
    'App\\Exchanging\\ServiceInterface\\Exchange\\ExchangeTemplateContextProviderInterface' = 'App\\Exchanging\\ProviderInterface\\ExchangeTemplateContextProviderInterface'
    'App\\Exchanging\\ServiceInterface\\Exchange\\ExchangeRateProviderInterface' = 'App\\Exchanging\\ProviderInterface\\ExchangeRateProviderInterface'
    'App\\Exchanging\\ServiceInterface\\Exchange\\ExchangeRemoteRateProviderInterface' = 'App\\Exchanging\\ProviderInterface\\ExchangeRemoteRateProviderInterface'
    'App\\Exchanging\\Entity\\Exchange\\ExchangeRate' = 'App\\Exchanging\\Entity\\ExchangeRateEntity'
    'App\\Exchanging\\Entity\\Exchange\\Exchange' = 'App\\Exchanging\\Entity\\ExchangeEntity'
}

$namespaceRoots = @(
    'Command',
    'Controller',
    'DTO',
    'Entity',
    'EntityInterface',
    'Exception',
    'Repository',
    'RepositoryInterface',
    'Service',
    'ServiceInterface',
    'ValueObject'
)

foreach ($root in $namespaceRoots) {
    $literalReplacements["App\\Exchanging\\$root\\Exchange\\"] = "App\\Exchanging\\$root\\"
}

$classRenames = [ordered]@{
    'ExchangeDemoFixtures' = 'ExchangeDemoFixtures'
    'ExchangeNbuRateNormalizedResponseDTO' = 'ExchangeNbuRateNormalizedResponseDTO'
    'ExchangeInvalidRequestException' = 'ExchangeInvalidRequestException'
    'ExchangeStaleRateException' = 'ExchangeStaleRateException'
    'ExchangeHttpJsonRemoteRateProvider' = 'ExchangeHttpJsonRemoteRateProvider'
    'ExchangeManualRemoteRateProvider' = 'ExchangeManualRemoteRateProvider'
    'ExchangeNbuRateResponseNormalizer' = 'ExchangeNbuRateResponseNormalizer'
    'ExchangeNbuRemoteRateProvider' = 'ExchangeNbuRemoteRateProvider'
    'ExchangeRepositoryRateProvider' = 'ExchangeRepositoryRateProvider'
    'ExchangeStaticRateProvider' = 'ExchangeStaticRateProvider'
    'ExchangeCurrencyCode' = 'ExchangeCurrencyCode'
    'ExchangeCurrencyPair' = 'ExchangeCurrencyPair'
}

$excludedRoots = @(
    (Join-Path $ProjectRoot '.git'),
    (Join-Path $ProjectRoot '.gating'),
    (Join-Path $ProjectRoot '.idea'),
    (Join-Path $ProjectRoot '.codebase-memory'),
    (Join-Path $ProjectRoot 'vendor'),
    (Join-Path $ProjectRoot 'node_modules'),
    (Join-Path $ProjectRoot 'var')
)
$textExtensions = @('.php', '.yaml', '.yml', '.xml', '.json', '.md', '.adoc', '.csv', '.ps1', '.ts', '.js')

$textFiles = Get-ChildItem -LiteralPath $ProjectRoot -Recurse -File | Where-Object {
    $file = $_
    if ($file.FullName -eq $PSCommandPath) {
        return $false
    }
    if ($file.Name -eq 'composer.lock' -or $file.Name -eq 'package-lock.json') {
        return $false
    }
    if ($textExtensions -notcontains $file.Extension) {
        return $false
    }
    foreach ($excludedRoot in $excludedRoots) {
        if ($file.FullName.StartsWith($excludedRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) {
            return $false
        }
    }
    return $true
}

foreach ($file in $textFiles) {
    $content = Get-Content -LiteralPath $file.FullName -Raw
    $updated = $content

