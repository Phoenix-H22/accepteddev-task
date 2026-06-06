<?php
/**
 * Create WooCommerce REST API key for Zoho integration.
 */

global $wpdb;

$user_id = 1;
$description = 'Zoho CRM Integration';
$permissions = 'read_write';

$table = $wpdb->prefix . 'woocommerce_api_keys';

// Remove previous key with same description.
$existing = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT key_id, consumer_key FROM {$table} WHERE user_id = %d AND description = %s",
        $user_id,
        $description
    )
);

if ( $existing ) {
    $wpdb->delete( $table, [ 'key_id' => $existing->key_id ], [ '%d' ] );
    WP_CLI::log( 'Removed previous API key with same description.' );
}

$consumer_key    = 'ck_' . wc_rand_hash();
$consumer_secret = 'cs_' . wc_rand_hash();

$wpdb->insert(
    $table,
    [
        'user_id'         => $user_id,
        'description'     => $description,
        'permissions'     => $permissions,
        'consumer_key'    => wc_api_hash( $consumer_key ),
        'consumer_secret' => $consumer_secret,
        'truncated_key'   => substr( $consumer_key, -7 ),
    ],
    [ '%d', '%s', '%s', '%s', '%s', '%s' ]
);

if ( ! $wpdb->insert_id ) {
    WP_CLI::error( 'Failed to create WooCommerce REST API key.' );
}

$config_dir = dirname( __DIR__ ) . '/integration';
if ( ! is_dir( $config_dir ) ) {
    wp_mkdir_p( $config_dir );
}

$config = [
    'site_url'        => home_url(),
    'api_base'        => home_url( '/wp-json/wc/v3' ),
    'consumer_key'    => $consumer_key,
    'consumer_secret' => $consumer_secret,
    'permissions'     => $permissions,
    'created_at'      => gmdate( 'c' ),
];

file_put_contents(
    $config_dir . '/woocommerce-api.local.json',
    wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
);

WP_CLI::success( 'WooCommerce REST API key created.' );
WP_CLI::log( 'Saved to: integration/woocommerce-api.local.json' );
WP_CLI::log( 'Consumer Key: ' . $consumer_key );
WP_CLI::log( 'Consumer Secret: ' . $consumer_secret );
WP_CLI::log( 'API Base: ' . $config['api_base'] );
