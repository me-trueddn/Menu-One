# Menu-One Plesk upload zip (vendor/node_modules/.env hariç, public/build DAHIL)
$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
$zipName = "Menu-One-plesk.zip"
$zipPath = Join-Path $projectRoot $zipName

if (-not (Test-Path (Join-Path $projectRoot "public\build\manifest.json"))) {
    Write-Host "public/build yok. Once: npm ci && npm run build"
    exit 1
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

$excludeDirs = @(
    "vendor",
    "node_modules",
    ".git",
    "public\hot",
    "storage\logs",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views"
)

$excludeFiles = @(
    ".env",
    ".env.backup",
    ".env.production",
    "Menu-One-plesk.zip"
)

$temp = Join-Path $env:TEMP ("menu-one-plesk-" + [guid]::NewGuid().ToString())
New-Item -ItemType Directory -Path $temp | Out-Null

try {
    Get-ChildItem -Path $projectRoot -Force | ForEach-Object {
        $name = $_.Name
        if ($excludeFiles -contains $name) { return }
        if ($excludeDirs -contains $name) { return }

        $dest = Join-Path $temp $name
        if ($_.PSIsContainer) {
            robocopy $_.FullName $dest /E /XD vendor node_modules .git public\hot /XF .env .env.backup .env.production /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
            if (-not (Test-Path $dest)) {
                Copy-Item -Path $_.FullName -Destination $dest -Recurse -Force
            }
        } else {
            Copy-Item -Path $_.FullName -Destination $dest -Force
        }
    }

    Compress-Archive -Path (Join-Path $temp '*') -DestinationPath $zipPath -Force
    Write-Host "Hazir: $zipPath"
    Write-Host "Plesk Dosya Yoneticisi -> panel.trueddn.com.tr -> yukle -> cikart"
} finally {
    Remove-Item -Path $temp -Recurse -Force -ErrorAction SilentlyContinue
}
