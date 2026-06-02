#Requires -Version 7.0
<#
.SYNOPSIS
    Health check rapide de la stack AgentWatch (Windows).

.DESCRIPTION
    Verifie :
    - Docker / Compose
    - PostgreSQL (pg_isready)
    - n8n (HTTP 200)
    - Ollama (GET /api/tags)
    - Mailpit (HTTP 200)
    - Token interne Symfony depuis n8n (header X-Internal-Token)

.EXAMPLE
    .\scripts\health-check.ps1
#>

[CmdletBinding()]
param(
    [int]$TimeoutSeconds = 5
)

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

function Write-Ok($message) {
    Write-Host "OK  - $message" -ForegroundColor Green
}

function Write-Warn($message) {
    Write-Host "WARN- $message" -ForegroundColor Yellow
}

function Write-Fail($message) {
    Write-Host "FAIL- $message" -ForegroundColor Red
}

function Test-DockerRunning {
    try {
        docker info --format '{{.ServerVersion}}' *> $null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Read-DotEnvFile {
    param([string]$Path)

    $map = @{}

    foreach ($line in (Get-Content -Path $Path -ErrorAction Stop)) {
        $trimmed = $line.Trim()
        if ($trimmed -eq '' -or $trimmed.StartsWith('#')) {
            continue
        }
        if ($trimmed.StartsWith('###')) {
            continue
        }

        if ($trimmed -notmatch '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$') {
            continue
        }

        $key = $Matches[1]
        $value = $Matches[2].Trim()

        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }

        $map[$key] = $value
    }

    return $map
}

function Load-Env {
    $envMap = @{}
    if (Test-Path '.env') {
        $envMap = Read-DotEnvFile '.env'
    }
    if (Test-Path '.env.local') {
        $localMap = Read-DotEnvFile '.env.local'
        foreach ($k in $localMap.Keys) {
            $envMap[$k] = $localMap[$k]
        }
    }
    return $envMap
}

function Get-EnvValue {
    param(
        [hashtable]$EnvMap,
        [string]$Key,
        [string]$DefaultValue
    )

    if ($EnvMap.ContainsKey($Key) -and ($EnvMap[$Key] -as [string]) -ne '') {
        return [string]$EnvMap[$Key]
    }
    return $DefaultValue
}

function Test-Http {
    param(
        [string]$Name,
        [string]$Url,
        [int]$ExpectedStatusCode = 200
    )

    try {
        $resp = Invoke-WebRequest -Uri $Url -Method Get -TimeoutSec $TimeoutSeconds -SkipHttpErrorCheck
        if ($resp.StatusCode -eq $ExpectedStatusCode) {
            Write-Ok "$Name ($ExpectedStatusCode) - $Url"
            return $true
        }

        Write-Fail "$Name (got $($resp.StatusCode), expected $ExpectedStatusCode) - $Url"
        return $false
    } catch {
        Write-Fail "$Name (request failed) - $Url"
        return $false
    }
}

$envMap = Load-Env
$postgresUser = Get-EnvValue -EnvMap $envMap -Key 'POSTGRES_USER' -DefaultValue 'app'
$postgresDb = Get-EnvValue -EnvMap $envMap -Key 'POSTGRES_DB' -DefaultValue 'app'
$internalToken = Get-EnvValue -EnvMap $envMap -Key 'APP_INTERNAL_TOKEN' -DefaultValue ''

Write-Step "Verification de Docker"
if (-not (Test-DockerRunning)) {
    throw "Docker ne repond pas. Lance Docker Desktop puis relance ce script."
}
Write-Ok "Docker repond."

Write-Step "Etat des conteneurs"
docker @Compose ps

Write-Step "PostgreSQL (pg_isready)"
docker @Compose exec -T database pg_isready -U $postgresUser -d $postgresDb *> $null
if ($LASTEXITCODE -eq 0) {
    Write-Ok "PostgreSQL pret (db=$postgresDb user=$postgresUser)."
} else {
    Write-Fail "PostgreSQL ne repond pas (db=$postgresDb user=$postgresUser)."
}

Write-Step "HTTP services"
Test-Http -Name 'n8n' -Url 'http://127.0.0.1:5678/' | Out-Null
Test-Http -Name 'Mailpit UI' -Url 'http://127.0.0.1:8025/' | Out-Null
Test-Http -Name 'Ollama' -Url 'http://127.0.0.1:11434/api/tags' | Out-Null

Write-Step "Token interne Symfony depuis n8n"
if ($internalToken -eq '') {
    Write-Warn "APP_INTERNAL_TOKEN introuvable dans .env/.env.local : skip test /internal/*."
    exit 0
}

$n8nToken = ''
try {
    $n8nToken = (docker @Compose exec -T n8n sh -lc 'printenv APP_INTERNAL_TOKEN' 2>$null).Trim()
} catch {
    $n8nToken = ''
}

if ($n8nToken -eq '') {
    Write-Warn "APP_INTERNAL_TOKEN absent du conteneur n8n (expression `$env.APP_INTERNAL_TOKEN` renverra vide)."
} elseif ($n8nToken -ne $internalToken) {
    Write-Warn "Mismatch token : n8n='$n8nToken' != env='$internalToken' (cause probable du 403)."
} else {
    Write-Ok "APP_INTERNAL_TOKEN present dans n8n et aligne."
}

docker @Compose exec -T n8n wget -S --spider --header "X-Internal-Token: $internalToken" http://host.docker.internal:8000/internal/rss-sources *> $null
if ($LASTEXITCODE -eq 0) {
    Write-Ok "GET /internal/rss-sources OK depuis n8n."
} else {
    Write-Fail "GET /internal/rss-sources KO depuis n8n (verifie symfony serve et le token)."
}

