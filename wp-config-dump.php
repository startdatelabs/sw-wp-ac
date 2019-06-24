<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpressdb');

/** MySQL database username */
define( 'DB_USER', 'wordpressuser');

/** MySQL database password */
define( 'DB_PASSWORD', '29i1bYigsvuk');

/** MySQL hostname */
define( 'DB_HOST', 'ecs01.sw:3306');

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '7a342ad7b9f2aac87b75f725deac976d21422751');
define( 'SECURE_AUTH_KEY',  '48327e75b5f853a02f734572d1efa48a187aee8f');
define( 'LOGGED_IN_KEY',    'd1d13af32d91f1db2ba5ca32097e736b39794056');
define( 'NONCE_KEY',        '8e6f6bf263b2de15ab12dc2bd56f3301d1da189c');
define( 'AUTH_SALT',        '3b4ac6a4c4ae5b6cfcf40963930c9dbd8bd71bc9');
define( 'SECURE_AUTH_SALT', 'c91f567f328d9746bacbeae7e3173ecf6cfe1bbe');
define( 'LOGGED_IN_SALT',   '3eb75b8a2995c61a8f9abff81f651c9a5ae4ebb3');
define( 'NONCE_SALT',       '9951bfb3c592ba1456749ff524132e5a5e74910d');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

define('FS_METHOD', 'direct');

// If we're behind a proxy server and using HTTPS, we need to alert Wordpress of that fact
// see also http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	$_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
