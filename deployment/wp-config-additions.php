<?php
/**
 * Paste these lines into wp-config.php ABOVE:
 *   /* That's all, stop editing! Happy publishing. *
 *
 * Also update DB_NAME, DB_USER, DB_PASSWORD, DB_HOST with FastPanel credentials.
 * Remove or replace the LocalWP WP_CLI DB_HOST block.
 */

// Force correct URLs (fixes redirect loops after migration + SSL).
define( 'WP_HOME', 'https://accepted.phoenixtechs.tech' );
define( 'WP_SITEURL', 'https://accepted.phoenixtechs.tech' );

// Tell WordPress the request is HTTPS when FastPanel/nginx terminates SSL.
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

// Production environment (was 'local' in the exported wp-config).
define( 'WP_ENVIRONMENT_TYPE', 'production' );

// Temporary: show errors while debugging (remove or set false when site works).
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
