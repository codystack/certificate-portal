<?php
/**
 * Reset-password endpoint (AJAX → JSON). Consumes a token minted by
 * forgot_password.php and sets a new bcrypt password on the admin account.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";      // $conn (mysqli)
require_once __DIR__ . "/../includes/helpers.php";

verify_csrf(true);

$token    = $_POST["token"] ?? "";
$password = $_POST["password"] ?? "";
$confirm  = $_POST["confirm_password"] ?? "";

if ($token === "") {
    json_response(["success" => false, "message" => "Invalid or expired reset link."]);
}
if (strlen($password) < 6) {
    json_response(["success" => false, "message" => "Password must be at least 6 characters."]);
}
if ($password !== $confirm) {
    json_response(["success" => false, "message" => "Passwords do not match."]);
}

$tokenHash = hash("sha256", $token);
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, admin_id FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW() LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $tokenHash);
mysqli_stmt_execute($stmt);
$reset = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$reset) {
    json_response(["success" => false, "message" => "Invalid or expired reset link."]);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$upd = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
mysqli_stmt_bind_param($upd, "si", $hash, $reset["admin_id"]);
mysqli_stmt_execute($upd);

// Mark this token used and drop any other outstanding tokens for the account.
$del = mysqli_prepare($conn, "DELETE FROM password_resets WHERE admin_id = ?");
mysqli_stmt_bind_param($del, "i", $reset["admin_id"]);
mysqli_stmt_execute($del);

json_response([
    "success"  => true,
    "message"  => "Your password has been reset. Redirecting to sign in…",
    "redirect" => "index.php",
]);
