<?php
/**
 * Forgot-password endpoint (AJAX → JSON).
 * Always returns a generic success message so the response can't be used
 * to enumerate which emails have an admin account.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";      // $conn (mysqli)
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../lib/resend.php";

verify_csrf(true);

$email = filter_var($_POST["email"] ?? "", FILTER_SANITIZE_EMAIL);

$generic = [
    "success" => true,
    "message" => "If an account exists for that email, we've sent a password reset link.",
];

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response($generic);
}

$stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE email = ? AND status = 'Active' LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($admin) {
    // Invalidate any previous outstanding tokens for this admin.
    $del = mysqli_prepare($conn, "DELETE FROM password_resets WHERE admin_id = ?");
    mysqli_stmt_bind_param($del, "i", $admin["id"]);
    mysqli_stmt_execute($del);

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expiresAt = date("Y-m-d H:i:s", time() + 1800); // 30 minutes

    $ins = mysqli_prepare(
        $conn,
        "INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($ins, "iss", $admin["id"], $tokenHash, $expiresAt);
    mysqli_stmt_execute($ins);

    $resetUrl = public_base_url() . "admin/reset-password.php?token=" . urlencode($token);
    $html = "<p>Hi " . e($admin["firstName"]) . ",</p>"
        . "<p>We received a request to reset your Glajoe Services admin password. "
        . "This link expires in 30 minutes:</p>"
        . '<p><a href="' . e($resetUrl) . '">' . e($resetUrl) . "</a></p>"
        . "<p>If you didn't request this, you can safely ignore this email.</p>";

    send_email($email, "Reset your Glajoe Services password", $html);
}

json_response($generic);
