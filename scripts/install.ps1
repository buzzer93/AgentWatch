#Requires -Version 7.0
<#
.SYNOPSIS
    Installation initiale d'AgentWatch en local (Windows).

.DESCRIPTION
    Demarre les services Docker (PostgreSQL, n8n, Ollama, Mailpit),
    attend que PostgreSQL soit pret, puis cree la base et applique
    les migrations Doctrine.

    Symfony lui-meme est lance separement avec "symfony serve".

.EXAMPLE
    .\scripts\install.ps1
#>

[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# Se placer a la racine du projet, quel que soit l'endroit d'ou on appelle le script.
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

Write-Step "Verification de .env.local"
if (-not (Test-Path '.env.local')) {
    Write-Warning ".env.local introuvable. Copie .env.example vers .env.local et adapte les secrets avant de continuer si besoin."
}

Write-Step "Demarrage des services Docker"
docker @Compose up -d
if ($LASTEXITCODE -ne 0) { throw "Echec du demarrage des conteneurs." }

Write-Step "Attente que PostgreSQL soit pret"
if (-not (Wait-Postgres -TimeoutSeconds 60)) {
    throw "PostgreSQL n'est pas devenu disponible dans le temps imparti."
}
Write-Host "PostgreSQL pret." -ForegroundColor Green

Write-Step "Creation de la base si necessaire"
php bin/console doctrine:database:create --if-not-exists --no-interaction
if ($LASTEXITCODE -ne 0) { throw "Echec doctrine:database:create." }

Write-Step "Application des migrations Doctrine"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if ($LASTEXITCODE -ne 0) { throw "Echec doctrine:migrations:migrate." }

Write-Step "Etat des conteneurs"
docker @Compose ps

Write-Host ""
Write-Host "Installation terminee." -ForegroundColor Green
Write-Host ""
Write-Host "Prochaines etapes :" -ForegroundColor Yellow
Write-Host "  1. Lance Symfony : symfony serve"
Write-Host "  2. Ouvre le dashboard : http://127.0.0.1:8000/dashboard"
Write-Host "  3. n8n : http://127.0.0.1:5678"
Write-Host "  4. Mailpit : http://127.0.0.1:8025"
