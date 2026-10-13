$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

function Stop-WithMessage {
    param([Parameter(Mandatory = $true)][string] $Message)

    Write-Host "ERROR: $Message" -ForegroundColor Red
    exit 1
}

Write-Host 'Starting TodoListWeb development services...'

foreach ($commandName in @('composer', 'node', 'npm', 'npx', 'php')) {
    if (-not (Get-Command -Name $commandName -ErrorAction SilentlyContinue)) {
        if ($commandName -eq 'php') {
            continue
        }

        Stop-WithMessage "$commandName was not found in PATH. Install it, reopen the terminal, and retry."
    }
}

if (-not (Test-Path -LiteralPath '.env')) {
    Stop-WithMessage '.env is missing. Create it from .env.example and configure the application first.'
}

if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) {
    Stop-WithMessage 'Composer dependencies are missing. Run: composer install'
}

if (-not (Test-Path -LiteralPath 'node_modules')) {
    Stop-WithMessage 'JavaScript dependencies are missing. Run: npm install'
}

$phpCommand = Get-Command -Name 'php' -ErrorAction SilentlyContinue
$phpPath = if ($phpCommand) { $phpCommand.Source } else { $null }
$phpHasPostgresDriver = $false

if ($phpPath) {
    $phpModules = & $phpPath -m 2>$null
    $phpHasPostgresDriver = $phpModules -contains 'pdo_pgsql'
}

if (-not $phpHasPostgresDriver) {
    $wingetPackageRoot = Join-Path $env:LOCALAPPDATA 'Microsoft/WinGet/Packages'
    $phpCandidates = Get-ChildItem -LiteralPath $wingetPackageRoot -Directory -Filter 'PHP.PHP.*' -ErrorAction SilentlyContinue |
        ForEach-Object { Join-Path $_.FullName 'php.exe' } |
        Where-Object { Test-Path -LiteralPath $_ }

    $phpPath = $phpCandidates |
        Where-Object {
            $extensionPath = Join-Path (Split-Path -Parent $_) 'ext/php_pdo_pgsql.dll'
            Test-Path -LiteralPath $extensionPath
        } |
        Select-Object -First 1

    if (-not $phpPath) {
        Stop-WithMessage 'PHP pdo_pgsql is not enabled, and no WinGet PHP installation with that extension was found.'
    }

    $phpDirectory = Split-Path -Parent $phpPath
    $extensionDirectory = Join-Path $phpDirectory 'ext'
    $extraIniDirectory = Join-Path $env:TEMP 'TodoListWeb-php-ini'
    New-Item -ItemType Directory -Path $extraIniDirectory -Force | Out-Null

    @(
        "extension_dir=`"$extensionDirectory`""
        'extension=php_pdo_pgsql.dll'
    ) | Set-Content -LiteralPath (Join-Path $extraIniDirectory 'pdo_pgsql.ini') -Encoding Ascii

    $env:PHP_INI_SCAN_DIR = $extraIniDirectory
    $env:Path = "$phpDirectory;$env:Path"
    $phpModules = & $phpPath -m 2>&1

    if ($phpModules -notcontains 'pdo_pgsql') {
        $phpModules | Where-Object { $_ -match 'Warning|Unable|Error' } | Write-Host
        Stop-WithMessage 'Could not load PHP pdo_pgsql. Check the PHP extension files and try again.'
    }
}

Write-Host 'PHP PostgreSQL driver is ready.' -ForegroundColor Green
Write-Host 'Laravel, queue, and Vite will start together. Press Ctrl+C to stop.'
Write-Host ''

& composer run dev
exit $LASTEXITCODE
