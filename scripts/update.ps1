#Requires -Version 7.0
<#
.SYNOPSIS
    Met a jour AgentWatch en local (Windows).

.DESCRIPTION
    Recupere les dernieres images Docker, recree les conteneurs,
    attend que PostgreSQL soit pret, puis applique les nouvelles
    migrations Doctrine s'il y en a.

.EXAMPLE
    .\scripts\update.ps1
#>

[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

$Compose = @('compose')
if (Test-Path '.env') {
    $Compose += @('--env-file', '.env')
}
if (Test-Path '.env.local') {
    $Compose += @('--env-file', '.env.local')
}
$Compose += @('-f', 'compose.yaml', '-f', 'compose.override.yaml')

function Write-Step($message) {
    Write-Host ""
    Write-Host "==> $message" -ForegroundColor Cyan
}

function Test-DockerRunning {
    try {
        docker info --format '{{.ServerVersion}}' *> $null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Wait-Postgres {
    param([int]$TimeoutSeconds = 60)

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    while ((Get-Date) -lt $deadline) {
        docker @Compose exec -T database pg_isready -U app -d app *> $null
        if ($LASTEXITCODE -eq 0) {
            return $true
        }
        Start-Sleep -Seconds 2
    }
    return $false
}

Write-Step "Verification de Docker"
if (-not (Test-DockerRunning)) {
    throw "Docker ne repond pas. Lance Docker Desktop puis relance ce script."
}
Write-Host "Docker OK." -ForegroundColor Green

Write-Step "Recuperation des dernieres images"
docker @Compose pull
if ($LASTEXITCODE -ne 0) { throw "Echec du pull des images." }

Write-Step "Recreation des conteneurs"
docker @Compose up -d --remove-orphans
if ($LASTEXITCODE -ne 0) { throw "Echec du up -d." }

Write-Step "Attente que PostgreSQL soit pret"
if (-not (Wait-Postgres -TimeoutSeconds 60)) {
    throw "PostgreSQL n'est pas devenu disponible dans le temps imparti."
}
Write-Host "PostgreSQL pret." -ForegroundColor Green

Write-Step "Application des migrations Doctrine en attente"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if ($LASTEXITCODE -ne 0) { throw "Echec doctrine:migrations:migrate." }

Write-Step "Etat des conteneurs"
docker @Compose ps

Write-Host ""
Write-Host "Mise a jour terminee." -ForegroundColor Green
Write-Host "Pense a redemarrer 'symfony serve' si tu l'avais lance." -ForegroundColor Yellow
