<?php
/**
 * Plugin Name: AcceptedDev Local REST Auth
 * Description: Allows WooCommerce REST API key auth over HTTP for local development only.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authenticate WooCommerce REST requests on plain HTTP using API keys in the query string.
 * WooCommerce only enables simple key auth automatically on HTTPS.
 */
add_filter(
	'determine_current_user',
	static function ( $user_id ) {
		if ( $user_id || is_ssl() ) {
			return $user_id;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return $user_id;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );

		if ( false === strpos( $request_uri, $rest_prefix . 'wc/' ) ) {
			return $user_id;
		}

		$consumer_key    = isset( $_GET['consumer_key'] ) ? wp_unslash( $_GET['consumer_key'] ) : '';
		$consumer_secret = isset( $_GET['consumer_secret'] ) ? wp_unslash( $_GET['consumer_secret'] ) : '';

		if ( ! $consumer_key || ! $consumer_secret || ! function_exists( 'wc_api_hash' ) ) {
			return $user_id;
		}

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT user_id, consumer_secret FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_key = %s",
				wc_api_hash( sanitize_text_field( $consumer_key ) )
			)
		);

		if ( ! $row || ! hash_equals( $row->consumer_secret, $consumer_secret ) ) {
			return $user_id;
		}

		return (int) $row->user_id;
	},
	20
);
