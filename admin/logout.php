<?php
session_start();

if (isset($_SESSION["admin_id"])) {
    require_once __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/includes/helpers.php";
    log_activity($conn, (int) $_SESSION["admin_id"], "logged_out", "auth");
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();
header("Location: index.php");
exit;
