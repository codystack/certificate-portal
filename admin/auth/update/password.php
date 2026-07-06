<?php
require_once __DIR__ . "/../guard.php";

$id              = (int) $_SESSION["admin_id"];
$currentPassword = $_POST["current_password"] ?? "";
$newPassword     = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
    json_response(["success" => false, "message" => "All fields are required."]);
}
if (strlen($newPassword) < 6) {
    json_response(["success" => false, "message" => "Password must be at least 6 characters."]);
}
if ($newPassword !== $confirmPassword) {
    json_response(["success" => false, "message" => "Passwords do not match."]);
}

$stmt = mysqli_prepare($conn, "SELECT password FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$admin) {
    json_response(["success" => false, "message" => "Account not found."], 404);
}

// Verify current password: bcrypt (new) or legacy SHA-1 (existing accounts).
$stored = $admin["password"];
$ok = password_verify($currentPassword, $stored)
    || (strlen($stored) === 40 && ctype_xdigit($stored) && hash_equals($stored, sha1($currentPassword)));
if (!$ok) {
    json_response(["success" => false, "message" => "Current password is incorrect."]);
}
if (password_verify($newPassword, $stored)) {
    json_response(["success" => false, "message" => "New password cannot be the same as the current one."]);
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $hash, $id);

if (mysqli_stmt_execute($stmt)) {
    log_activity($conn, $id, "updated", "security", $id, "Changed password");
    session_unset();
    session_destroy();
    json_response(["success" => true, "message" => "Password updated. Please sign in again.", "redirect" => "index.php"]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
