# Menu-One Plesk vendor upload zip (composer install --no-dev locally)
$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
$zipName = "Menu-One-vendor.zip"
$zipPath = Join-Path $projectRoot $zipName

Push-Location $projectRoot
try {
    Write-Host "composer install --no-dev ..."
    composer install --no-dev --optimize-autoloader --no-interaction
    if (-not (Test-Path "vendor\spatie\laravel-permission")) {
        throw "vendor/spatie/laravel-permission olusmadi"
    }
    if (-not (Test-Path "vendor\laravel\socialite")) {
        throw "vendor/laravel/socialite olusmadi"
    }
} finally {
    Pop-Location
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

Write-Host "Zipleniyor (birkaç dakika surebilir)..."
Compress-Archive -Path (Join-Path $projectRoot "vendor\*") -DestinationPath $zipPath -Force

Write-Host "Hazir: $zipPath"
Write-Host "Plesk: panel.trueddn.com.tr icindeki vendor klasorunu sil -> zip yukle -> vendor icine cikart"
