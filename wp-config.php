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
define( 'DB_NAME', 'newhookup2' );

/** MySQL database username */
define( 'DB_USER', 'nhuuser2' );

/** MySQL database password */
define( 'DB_PASSWORD', 'UvRRejZHxhFp4wmy' );

/** MySQL hostname */
define( 'DB_HOST', 'hookupdb1.webair.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'nxqqYIZ#Z1{GV0HGY$Q84RT;D7;st]cLkE1|1^;fo y=]uH4SxeIcz+&_gM![Eqw' );
define( 'SECURE_AUTH_KEY',  'tvNNjvf6W>*Y?BXV|kF)A?vit_C*a {`Me[(V&cII79[:T`V`# p.BlS@LGr7dS5' );
define( 'LOGGED_IN_KEY',    'k%Zf}!1v@Y*FV|udN9FV?1jbLV9uU=:iD/-q&$zJp#jz1I)2L@uO<<rG*,|kMs+]' );
define( 'NONCE_KEY',        'Wu.0c&6Uh0@J?8AB%-I{%7Q rrP853f~&siA>te,tKHq/a]ccS~l>N@bf8AVARtc' );
define( 'AUTH_SALT',        'Xnrs)Py?]3x4GqQiB[Gh~k0muEg2GO31Ay0D^IQ04Z@jj_-k4%LK?:[o0aV^)M>7' );
define( 'SECURE_AUTH_SALT', '[0n7?pYmUCG3l/oUtBe(UnC=~}[@1(,jJlp/JM1lg3A&Yh|=3-Z2|>WJ{}BB>68c' );
define( 'LOGGED_IN_SALT',   'k>htDY36}cM2`%`L*z0->K}h%}~,rki>N#KF=lxL(*/gH1NX*c+PT:i<v&T6Y9Nr' );
define( 'NONCE_SALT',       '6;9)o5vzcE|H%@~g<HXc%g]G`(.7zD{:J-2I$O2EeMzpH:ci^fsQGB%Cl<`zff6i' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp2_';

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
define( 'WP_DEBUG', false );
#define('WP_MEMORY_LIMIT', '256M');
define( 'WP_MEMORY_LIMIT', ini_get( 'memory_limit' ) );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
