$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$runtimeDirectory = Join-Path $projectRoot '.tools/node'
$runtimePath = Join-Path $runtimeDirectory 'node.exe'
$expectedHash = '0d0f5e39f9f3d9587bc19f73eab3c2c9c4903fd02d6dbf9c853dd81b3d95fad4'
if ((Test-Path -LiteralPath $runtimePath) -and ((Get-FileHash -LiteralPath $runtimePath -Algorithm SHA256).Hash -eq $expectedHash)) {
    & $runtimePath --version
    exit $LASTEXITCODE
}
New-Item -ItemType Directory -Path $runtimeDirectory -Force | Out-Null
$downloadPath = Join-Path $runtimeDirectory 'node.exe.download'
Invoke-WebRequest -UseBasicParsing -Uri 'https://nodejs.org/dist/v22.23.2/win-x64/node.exe' -OutFile $downloadPath
if ((Get-FileHash -LiteralPath $downloadPath -Algorithm SHA256).Hash -ne $expectedHash) {
    throw 'Checksum Node.js tidak cocok. Runtime tidak dipasang.'
}
Move-Item -LiteralPath $downloadPath -Destination $runtimePath -Force
& $runtimePath --version
exit $LASTEXITCODE
