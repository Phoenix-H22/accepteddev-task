<?php
/**
 * Verify Zoho OAuth2 refresh token and CRM API v8 access.
 *
 * Usage:
 *   copy integration/zoho.local.json.example to integration/zoho.local.json
 *   php scripts/test-zoho-oauth.php
 */

$configPath = dirname( __DIR__ ) . '/integration/zoho.local.json';

if ( ! is_readable( $configPath ) ) {
	fwrite( STDERR, "Missing {$configPath}\nCopy from integration/zoho.local.json.example and fill OAuth values.\n" );
	exit( 1 );
}

$config = json_decode( file_get_contents( $configPath ), true );
if ( ! is_array( $config ) ) {
	fwrite( STDERR, "Invalid JSON in zoho.local.json\n" );
	exit( 1 );
}

$required = [ 'client_id', 'client_secret', 'refresh_token', 'accounts_url', 'api_domain' ];
foreach ( $required as $key ) {
	if ( empty( $config[ $key ] ) || str_contains( (string) $config[ $key ], 'YOUR_' ) ) {
		fwrite( STDERR, "Set {$key} in zoho.local.json\n" );
		exit( 1 );
	}
}

$tokenUrl = rtrim( $config['accounts_url'], '/' ) . '/oauth/v2/token';
$body     = http_build_query(
	[
		'refresh_token' => $config['refresh_token'],
		'client_id'     => $config['client_id'],
		'client_secret' => $config['client_secret'],
		'grant_type'    => 'refresh_token',
	]
);

$ch = curl_init( $tokenUrl );
curl_setopt_array(
	$ch,
	[
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => $body,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 30,
	]
);
$tokenRaw   = curl_exec( $ch );
$tokenCode  = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

$tokenData = json_decode( (string) $tokenRaw, true );
if ( $tokenCode !== 200 || empty( $tokenData['access_token'] ) ) {
	fwrite( STDERR, "OAuth token refresh failed (HTTP {$tokenCode}):\n{$tokenRaw}\n" );
	exit( 1 );
}

echo "OAuth OK — access token received (expires in {$tokenData['expires_in']}s)\n";

$modulesUrl = rtrim( $config['api_domain'], '/' ) . '/crm/v8/settings/modules';
$ch         = curl_init( $modulesUrl );
curl_setopt_array(
	$ch,
	[
		CURLOPT_HTTPHEADER     => [ 'Authorization: Zoho-oauthtoken ' . $tokenData['access_token'] ],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 30,
	]
);
$modulesRaw  = curl_exec( $ch );
$modulesCode = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

if ( $modulesCode !== 200 ) {
	fwrite( STDERR, "CRM API v8 call failed (HTTP {$modulesCode}):\n{$modulesRaw}\n" );
	exit( 1 );
}

$modules = json_decode( (string) $modulesRaw, true );
$names   = [];
if ( ! empty( $modules['modules'] ) ) {
	foreach ( $modules['modules'] as $module ) {
		if ( in_array( $module['api_name'] ?? '', [ 'Contacts', 'Deals' ], true ) ) {
			$names[] = $module['api_name'];
		}
	}
}

echo 'CRM API v8 OK — modules reachable: ' . implode( ', ', $names ) . "\n";
echo "Ready to paste OAuth credentials into the Deluge script.\n";
