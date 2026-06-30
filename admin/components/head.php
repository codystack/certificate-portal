<?php
/**
 * Opening markup + auth gate for every admin page (Dashly theme).
 * Mirrors the brit-backoffice component pattern.
 *
 * A page includes this first:
 *   $page = 'Certificates';
 *   include __DIR__ . '/components/head.php';
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/db.php";  // $conn (mysqli)
require_once __DIR__ . "/../includes/helpers.php";

// Gate: pages require a logged-in admin.
if (!isset($_SESSION["admin_id"])) {
    redirect("index.php");
}

// Sanitised session data for the chrome.
$fullName    = e($_SESSION["name"] ?? "");
$firstName   = e($_SESSION["first_name"] ?? "");
$userEmail   = e($_SESSION["email"] ?? "");
$designation = e($_SESSION["designation"] ?? "");
$avatarRaw   = $_SESSION["picture"] ?? "";
$avatar      = $avatarRaw
    ? "upload/" . rawurlencode(basename($avatarRaw))
    : "../assets/images/avatar.png";

$pageTitle = $pageTitle ?? ($page ?? "Dashboard");
?>
<!doctype html>
<html lang="en" data-bs-theme="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo csrf_meta(); ?>
    <link rel="shortcut icon" href="../assets/images/glajoe-favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/libs.bundle.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/butler/stylesheet.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <title><?php echo e($pageTitle); ?> :: Glajoe Admin</title>
</head>
<body>
