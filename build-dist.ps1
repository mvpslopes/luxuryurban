# Gera dist/public_html/ pronta para copiar na Hostinger
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
$dist = Join-Path $root "dist"
$publicHtml = Join-Path $dist "public_html"

Write-Host "Instalando dependencias PHP..."
Set-Location $root
composer install --no-dev --optimize-autoloader --no-interaction

Write-Host "Gerando pasta dist..."
if (Test-Path $dist) { Remove-Item $dist -Recurse -Force }
New-Item -ItemType Directory -Path $publicHtml -Force | Out-Null

# Codigo e dependencias
foreach ($f in @("app", "config", "vendor")) {
    Copy-Item (Join-Path $root $f) (Join-Path $publicHtml $f) -Recurse -Force
}

# Entrada do site (index na RAIZ do public_html)
Copy-Item (Join-Path $root "deploy\hostinger\index.php") (Join-Path $publicHtml "index.php") -Force
Copy-Item (Join-Path $root "deploy\hostinger\.htaccess") (Join-Path $publicHtml ".htaccess") -Force

# Assets e uploads publicos
Copy-Item (Join-Path $root "public\assets") (Join-Path $publicHtml "assets") -Recurse -Force
$uploads = Join-Path $publicHtml "uploads\produtos"
New-Item -ItemType Directory -Path $uploads -Force | Out-Null
New-Item -ItemType File -Path (Join-Path $uploads ".gitkeep") -Force | Out-Null

# Proteger pastas sensiveis
$deny = Join-Path $root "deploy\hostinger\htaccess-deny-all.txt"
foreach ($p in @("app", "config", "vendor")) {
    Copy-Item $deny (Join-Path $publicHtml "$p\.htaccess") -Force
}

# Configuracao
Copy-Item (Join-Path $root ".env") (Join-Path $publicHtml ".env") -Force

# SQL e instrucoes (fora do public_html)
Copy-Item (Join-Path $root "database") (Join-Path $dist "database") -Recurse -Force
Copy-Item (Join-Path $root "DEPLOY-HOSPEDAGEM.txt") (Join-Path $dist "LEIA-ME.txt") -Force

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host " PRONTO: $publicHtml" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Copie o CONTEUDO de dist/public_html/ para o public_html da Hostinger."
Write-Host "O index.php fica na raiz do public_html (nao dentro de subpasta)."
