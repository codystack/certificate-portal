<?php
mysqli_report(MYSQLI_REPORT_OFF);

// Load .env from project root
$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$appEnv = getenv('APP_ENV') ?: 'production';
$suffix  = ($appEnv === 'development') ? '_DEV' : '_PROD';

$host     = getenv('DB_HOST')           ?: 'localhost';
$user     = getenv('DB_USER' . $suffix) ?: '';
$password = getenv('DB_PASS' . $suffix) ?: '';
$database = getenv('DB_NAME' . $suffix) ?: '';

$appUrl = getenv('APP_URL' . $suffix);
if ($appUrl) {
    define('APP_BASE_URL', rtrim($appUrl, '/') . '/');
}

if ($appEnv === 'development') {
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

$conn = mysqli_connect($host, $user, $password) or die("Could not connect to database");
mysqli_select_db($conn, $database);
mysqli_set_charset($conn, "utf8mb4");
