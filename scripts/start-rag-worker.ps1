param([switch]$Once)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $projectRoot
try {
    # queue:listen isolates each job in a subprocess on Windows, where pcntl timeout is unavailable.
    if ($Once) { & php artisan queue:work rag --queue=rag --once --tries=3 --timeout=300 }
    else { & php artisan queue:listen rag --queue=rag --sleep=3 --tries=3 --timeout=300 }
    exit $LASTEXITCODE
} finally { Pop-Location }
