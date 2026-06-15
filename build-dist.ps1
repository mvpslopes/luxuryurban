# Gera a pasta dist/ pronta para upload na hospedagem Hostinger
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
$dist = Join-Path $root "dist"

Write-Host "Instalando dependencias PHP..."
Set-Location $root
composer install --no-dev --optimize-autoloader --no-interaction

Write-Host "Limpando dist anterior..."
if (Test-Path $dist) { Remove-Item $dist -Recurse -Force }
New-Item -ItemType Directory -Path $dist | Out-Null

$folders = @("app", "config", "public", "vendor")
foreach ($f in $folders) {
    Copy-Item (Join-Path $root $f) (Join-Path $dist $f) -Recurse -Force
}

Copy-Item (Join-Path $root ".env") (Join-Path $dist ".env") -Force
Copy-Item (Join-Path $root "composer.json") (Join-Path $dist "composer.json") -Force
Copy-Item (Join-Path $root "database") (Join-Path $dist "database") -Recurse -Force
Copy-Item (Join-Path $root "DEPLOY-HOSPEDAGEM.txt") (Join-Path $dist "LEIA-ME.txt") -Force

# Garantir pasta de uploads com permissao de escrita
$uploads = Join-Path $dist "public\uploads\produtos"
New-Item -ItemType Directory -Path $uploads -Force | Out-Null
New-Item -ItemType File -Path (Join-Path $uploads ".gitkeep") -Force | Out-Null

Write-Host ""
Write-Host "dist/ gerada com sucesso em: $dist"
Write-Host "Envie o conteudo de dist/ para a hospedagem e aponte o dominio para dist/public/"
