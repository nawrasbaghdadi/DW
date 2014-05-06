<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'jmag');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ',sk-eUy6GmzgEULKG^lv[J$uqDDZcto&sdMPsoh|NDBQZ<QA#7K4QGkbp_8k-[]$');
define('SECURE_AUTH_KEY',  '_h@7j_o|jlH^_DJ?#1OC#Y-]Ge-UM#Co0I23~Yt6WD&pP-A}@p+XnNwdL=:*%X++');
define('LOGGED_IN_KEY',    'RBUfcc+Pt8;LLNr$f9j=6T?^I)+TSAc^7(u:oosGqC0FBaZ.O`UJxz-9_Q(%nGvc');
define('NONCE_KEY',        '<ew=lxhp.F{ wNGE]qs-MD5KUuya~l==`C*1S`>-g+Su$ulapsc6/g[kjG0fPRi<');
define('AUTH_SALT',        '7; .7|>X=nHZjRgDi|6>RU^Ulk&y;|Sp*6?$2SncEi~G9DUF`Cfqiy.Jd;LkD4LV');
define('SECURE_AUTH_SALT', 'eSwB<8moQg#A=sp%1pO~,YS$ztW`,x$RoT(z|G<lw54,^BjXK>/ortS|x+QPxzo!');
define('LOGGED_IN_SALT',   '`E6#+(@s#OE7Qy,iTH$y@xRLMLprW9OxJ@=1&+=#3@|o_>M|3}@NKQT!}t@[S*1K');
define('NONCE_SALT',       'q bN9B_oFewDuKTrL~P+bbNq]#B*x*I3?#YfP>!3C}$(2N7U9DPIu^W9c6%(&eE.');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
