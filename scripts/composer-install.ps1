$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (-not (Test-Path ".\\composer.phar")) {
  throw "No se encontró composer.phar en $projectRoot"
}

New-Item -ItemType Directory -Force ".\\.composer-home" | Out-Null
New-Item -ItemType Directory -Force ".\\.composer-cache" | Out-Null

$env:COMPOSER_HOME = (Resolve-Path ".\\.composer-home").Path
$env:COMPOSER_CACHE_DIR = (Resolve-Path ".\\.composer-cache").Path

php .\\composer.phar install
