<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

if (file_exists('/hp/bq/ac/ma/www/prod/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", '/hp/bq/ac/ma/www/prod/wp-content/wflogs/');
	include_once '/hp/bq/ac/ma/www/prod/wp-content/plugins/wordfence/waf/bootstrap.php';
}
?>