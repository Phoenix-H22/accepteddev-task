# Export local WordPress DB for production upload.
# Requires LocalWP site to be STARTED (green/running).

$ErrorActionPreference = "Stop"
$php = "C:\Users\abdal\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe"
$ini = "D:\GitHub\accepteddev-task-wp\conf\php\cli-php.ini"
$wp = "D:\GitHub\accepteddev-task-wp\app\public\wp-cli.phar"
$path = "D:\GitHub\accepteddev-task-wp\app\public"
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$localExport = "D:\GitHub\accepteddev-task-wp\app\sql\accepteddevtask-local-$stamp.sql"
$prodExport = "D:\GitHub\accepteddev-task-wp\app\sql\accepted.phoenixtechs.tech-export.sql"
$prodDomain = "https://accepted.phoenixtechs.tech"

Write-Host "Exporting database (start LocalWP first if this fails)..."
& $php -c $ini -d memory_limit=512M $wp --path=$path db export $localExport --add-drop-table 2>&1
if ($LASTEXITCODE -ne 0) { throw "WP-CLI export failed. Is LocalWP running?" }

$content = [System.IO.File]::ReadAllText($localExport)
$content = $content -replace 'https?://accepteddevtask\.local', $prodDomain
[System.IO.File]::WriteAllText($prodExport, $content)

Write-Host ""
Write-Host "Local export:  $localExport"
Write-Host "Production:    $prodExport"
Write-Host "Upload this file to FastPanel phpMyAdmin: $prodExport"
