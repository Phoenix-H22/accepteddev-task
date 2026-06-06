# Exchange Zoho grant code for refresh token.
# Run: powershell -ExecutionPolicy Bypass -File scripts\exchange-zoho-token.ps1

Write-Host ""
Write-Host "=== Zoho OAuth: grant code -> refresh token ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "BEFORE running this script:" -ForegroundColor Yellow
Write-Host "  1. API Console -> Self Client -> Generate Code (scope: ZohoCRM.modules.ALL)"
Write-Host "  2. Copy the code immediately (expires in ~3 minutes, single use)"
Write-Host ""

$clientId = Read-Host "Client ID"
$clientSecret = Read-Host "Client Secret"
$code = Read-Host "Grant code (paste now)"

$useRedirect = Read-Host "Server-based app? Add redirect_uri https://www.zoho.com ? (y/N)"
$accountsUrl = Read-Host "Accounts URL [https://accounts.zoho.com]"

if ([string]::IsNullOrWhiteSpace($accountsUrl)) {
	$accountsUrl = "https://accounts.zoho.com"
}

$body = @{
	code          = $code.Trim()
	client_id     = $clientId.Trim()
	client_secret = $clientSecret.Trim()
	grant_type    = "authorization_code"
}

if ($useRedirect -eq "y" -or $useRedirect -eq "Y") {
	$body.redirect_uri = "https://www.zoho.com"
}

Write-Host ""
Write-Host "Exchanging code..." -ForegroundColor Cyan

try {
	$response = Invoke-RestMethod -Method Post -Uri "$accountsUrl/oauth/v2/token" -Body $body
}
catch {
	Write-Host "Request failed:" -ForegroundColor Red
	if ($_.ErrorDetails.Message) {
		Write-Host $_.ErrorDetails.Message
	}
	else {
		Write-Host $_.Exception.Message
	}
	exit 1
}

if ($response.error) {
	Write-Host "Error: $($response.error)" -ForegroundColor Red
	Write-Host ""
	Write-Host "Fix:" -ForegroundColor Yellow
	Write-Host "  - Generate a NEW grant code (old one expired or already used)"
	Write-Host "  - Use Self Client ID/Secret from the SAME client that generated the code"
	Write-Host "  - For Server-based app, answer 'y' to add redirect_uri"
	exit 1
}

Write-Host ""
Write-Host "SUCCESS" -ForegroundColor Green
Write-Host ""
Write-Host "refresh_token (paste into Deluge as ZOHO_REFRESH_TOKEN):" -ForegroundColor Green
Write-Host $response.refresh_token
Write-Host ""
Write-Host "access_token (temporary, do not save):" -ForegroundColor DarkGray
Write-Host $response.access_token
Write-Host ""

# Optionally save to zoho.local.json if example exists
$configPath = Join-Path $PSScriptRoot "..\integration\zoho.local.json"
$examplePath = Join-Path $PSScriptRoot "..\integration\zoho.local.json.example"

if (-not (Test-Path $configPath) -and (Test-Path $examplePath)) {
	$save = Read-Host "Save to integration/zoho.local.json ? (y/N)"
	if ($save -eq "y" -or $save -eq "Y") {
		$config = Get-Content $examplePath -Raw | ConvertFrom-Json
		$config.client_id = $clientId.Trim()
		$config.client_secret = $clientSecret.Trim()
		$config.refresh_token = $response.refresh_token
		$config.accounts_url = $accountsUrl
		$config | ConvertTo-Json | Set-Content $configPath -Encoding UTF8
		Write-Host "Saved to $configPath" -ForegroundColor Green
	}
}
