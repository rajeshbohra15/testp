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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'lQWQjOsd8BvJLmvnH1TdkSYsDhV+Ych0ElX76BXAVBAmLIxzMchTqphtCDGAHSJAOWq+vkGpvf2l9dcu6Gifcw==');
define('SECURE_AUTH_KEY',  'oyqZnGXxHBLyw3gQ59l5rs4Dna3cnIfvgVyV30DV+HLox5dUFb4CnsfOBX3VD/TFwts5TqO1OIC5SdCOKU1IpA==');
define('LOGGED_IN_KEY',    'T6pcJjymUuBj5MTcDM1mVRGoq+tBOORnCtGt5s+fJddIQqZaCiqy3CBd+wmIj/SNolEmHODQGxSLTJY1z42cqg==');
define('NONCE_KEY',        'HJ5UVC3L/VLRmkxyCTF9o5UK1sEZo1s1LK2C7xrdkH4bnWzMBB/o1ZWrarIeA4gHvZPmEx0XR3RD4+q6+jw87Q==');
define('AUTH_SALT',        'gqNJ8H0xb9HDbFOgcxYqMH25D2Hw22ZRibWUZ+E3Vl00Oe7cdbq98DNs4oj397Un+1sgqyxn5d0iFcNbLsUUdg==');
define('SECURE_AUTH_SALT', 'FcpoQxVAXdzfVDMj88MxARF+j9bgDZSxDDO7n5ADqvV9Goy1itRA9Q93ompEV/Qvrbdc/D2vwJ+xSPrkstfzYg==');
define('LOGGED_IN_SALT',   'IVV00OZ2THHlphT5ITyHHShqF/hcev2yaovd+gd37ZQZSTPq5UDZXZWXca7WM7x6297R+LwkWXp3JBKFq0qwAw==');
define('NONCE_SALT',       'fwd7Y0Vj+s8FoHIqkTHBDuhJY3QwtNH4YJNvpsOFYOqab7Oo/VGCf4i4FG0PrWMQ63gm6Pk1Tju9AJs4kHR2Jw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
