<?php
	ini_set('display_errors',1);
	error_reporting(-1);
	define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    define('DB_DATABASE', 'fragrancehub');

// Base path configuration
$base_path = '/ecommerce/';

// Function to get asset URL
function asset_url($path) {
    global $base_path;
    return $base_path . $path;
}
?>