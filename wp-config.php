<?php

// 1) Detect environment (based on absolute path)
$root = realpath(__DIR__) ?: __DIR__;

$environment = 'local';
if (str_contains($root, 'staging')) {
	$environment = 'staging';
} elseif (str_contains($root, 'prod')) {
	$environment = 'production';
}

// 2) Load env array from file (file must "return [ ... ];")
$env = [];

$envFile = __DIR__ . '/.wp-env.' . $environment . '.php';

if (is_readable($envFile)) {
	$loaded = require $envFile;
	if (is_array($loaded)) {
		$env = $loaded;
	}
}

// 3) Load passwords

$secretsFile = __DIR__ . '/.wp-secrets.php';
if (is_readable($secretsFile)) {
	$secrets = require $secretsFile;
	if (is_array($secrets)) {
		$env = array_replace($env, $secrets); // secrets override env
	}
}

// 4) Helper: env file values win, then real env vars, then default
function envv(string $key, $default = null) {
	global $env;

	if (is_array($env) && array_key_exists($key, $env)) {
		return $env[$key];
	}

	$v = getenv($key);
	return ($v !== false) ? $v : $default;
}

// 5) Define constants
define('DB_NAME',     envv('DB_NAME', ''));
define('DB_USER',     envv('DB_USER', ''));
define('DB_PASSWORD', envv('DB_PASSWORD', ''));
define('DB_HOST',     envv('DB_HOST', 'localhost'));

define('WP_HOME',    envv('WP_HOME', ''));
define('WP_SITEURL', envv('WP_SITEURL', ''));

define('WP_ENVIRONMENT_TYPE', envv('WP_ENVIRONMENT_TYPE', 'production')); // local|development|staging|production

define('WP_DEBUG', WP_ENVIRONMENT_TYPE !== 'production');

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// ...



/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY',         'xtpFhGiQjh2PtkmlUPORESKoniYtgtm4AhMRYMAgTYK3kcj4oY2G5DtVAT6k8L9H');

define('SECURE_AUTH_KEY',  '1gF3lB2EwtupMbyvulKBEcCkvDlyigOeXmeatYIqjGbLCvDjeh4tIoB2TbDvppu3');

define('LOGGED_IN_KEY',    'epPILkFOnVq5jwZv9IYWTCHPx01uEEsxeZQW8cnGUwb1FM9dxhDsgaCWXhXPgaV2');

define('NONCE_KEY',        'xozpPEYbAfntJXPTK6DWWEPIbF2vx7UrTM8PusCL4cZtmOgsVsHeZCuDRvvg32jR');

define('AUTH_SALT',        'oYmRb8VqSymGPR48e0G2VaTAdT6jQrBE24Wl26IStLYtBbtY15cWLuh3YKVPbBRb');

define('SECURE_AUTH_SALT', 'pMWJpmo8nfx3csw6puCHlXwb3NNivszNtrcqb2okIkpXRz0lwI9JJchu2yFTkCUV');

define('LOGGED_IN_SALT',   'saIV17RuDWVYthOjEpEewkN0CBg23CiQMOwve46blJ7Jc4lsiKY7VaSYdvaBmkuf');

define('NONCE_SALT',       '04yGxKkcDnx4QMIs62ZOXzZkli5A7qtAe62ZRV1923M1XfkR0wJn1cAGlmblKNKi');


/**

 * Other customizations.

 */

define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

define( 'UPLOADS', 'wp-content/'.'uploads' );

define('ALLOW_UNFILTERED_UPLOADS', true);

/**#@-*/


/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'viva_';

/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');


/** Sets up WordPress vars and included files. */

require_once(ABSPATH . 'wp-settings.php');