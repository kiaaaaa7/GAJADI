<?php
// Konfigurasi Aplikasi
session_start();
ob_start();

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Load environment variables
if (file_exists(dirname(__DIR__) . '/.env')) {
    $lines = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Konstanta
define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost/house-cafe');
define('SITE_NAME', $_ENV['APP_NAME'] ?? 'House Cafe');
define('DEBUG', $_ENV['DEBUG_MODE'] ?? false);
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads/');

// Error reporting
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Auto require classes
spl_autoload_register(function ($class_name) {
    $paths = [
        dirname(__DIR__) . '/app/models/',
        dirname(__DIR__) . '/app/controllers/',
        dirname(__DIR__) . '/includes/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>