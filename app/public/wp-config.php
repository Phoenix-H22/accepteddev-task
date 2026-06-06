<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	define( 'DB_HOST', '127.0.0.1:10004' );
} else {
	define( 'DB_HOST', 'localhost' );
}

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '[oCm<5qnL*cG6~Su81|xKgr~bSw} ]9@zG$g!~B{[)J*lyR~N*^IusW6A$C&-$IN' );
define( 'SECURE_AUTH_KEY',   'yB;e1<_$UinQm;=[UN@>(cl{#_w1PqSK(FHPfsKgQd~D5 LEa|Gejs4S_bX?@]L#' );
define( 'LOGGED_IN_KEY',     'Lb1CM_6gj/HagT1RY<WGpRLq+N9d]1q53^3a2DB0[ge`c1*XO~Y|0HZV]*CbA()n' );
define( 'NONCE_KEY',         'C7+<o?v&l#+)&&K8B;):LzeV)5/!JEWB,{3Yd5* vATPRl4p9+[3D]$mq-{ZO1E#' );
define( 'AUTH_SALT',         'q.&ta0;hQDUMO%L=GB3zBGSXZ+;0J>SuP_n!j/+L!*I;d-[D@7MIeas1hL*9n!rB' );
define( 'SECURE_AUTH_SALT',  ';xa&.S,O:@+U.s.d F5t7@NV|v.@uZ[OS7V4pJ/x](5`AFCQrQh(+xV.jL~l_WGe' );
define( 'LOGGED_IN_SALT',    'tC<-f-IOmp$``[o__]MJ_*[C}vH&Qiwu!(yKewy=tA1U4YgG$qErkkD`Qw{H.1p&' );
define( 'NONCE_SALT',        '=5?QK1]+5kNQl<[b=Gt6+|p_`KE)GNW/@_`a`p-[PazGx4^y~h#5?*]T5E4iOB$u' );
define( 'WP_CACHE_KEY_SALT', 'R8(MiRc1>-=T.JdD%Qs_>+.ji=n9d4GP.t3_pL::[uf.,]HMi%pj0Qv{~{xGQQOL' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
