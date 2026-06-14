<?php
// set a default environment
$WEBSITE_ENVIRONMENT = "Development";
// detect the URL to determine if it's development or production
if (stristr($_SERVER['HTTP_HOST'], 'localhost') === FALSE) $WEBSITE_ENVIRONMENT = "Production";
// value of variables will change depending on if Development vs Production
if ($WEBSITE_ENVIRONMENT == "Development") {
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "glajoe";
    error_reporting(E_ALL ^ E_NOTICE); // turn ON showing errors
} else {
    // Production credentials are loaded from an untracked file outside source control.
    // See config/secrets.sample.php — copy it to config/secrets.php and fill in values.
    $secrets = __DIR__ . "/secrets.php";
    if (!is_readable($secrets)) {
        die("Configuration error.");
    }
    $cfg = require $secrets;
    $host = $cfg['host'];
    $user = $cfg['user'];
    $password = $cfg['password'];
    $database = $cfg['database'];
    define("APP_ENVIRONMENT", "Production");
    define("APP_BASE_URL", "https://app.glajoeservices.com.ng/");
    error_reporting(0);          // do not expose errors in production
    ini_set('display_errors', '0');
}
// connect to the database server
$conn = mysqli_connect($host, $user, $password) or die("Could not connect to database");
// select the right database
mysqli_select_db($conn, $database);
mysqli_set_charset($conn, "utf8mb4");
// END Database connection and Configuration