<?php
/**
 * Login endpoint (AJAX → JSON), mirroring the brit pattern.
 * Supports the legacy SHA-1 hashes already in the `admin` table and
 * transparently upgrades them to bcrypt on a successful login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";    // $conn (mysqli)
require_once __DIR__ . "/../includes/helpers.php";

verify_csrf(true);

$email = filter_var($_POST["email"] ?? "", FILTER_SANITIZE_EMAIL);
$password = $_POST["password"] ?? "";

if (!$email || $password === "") {
    json_response(["success" => false, "message" => "Email and password are required."]);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(["success" => false, "message" => "Invalid email format."]);
}

$stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Uniform message prevents account enumeration.
$invalid = ["success" => false, "message" => "Invalid email or password."];
if (!$admin) {
    json_response($invalid);
}

// Verify password: bcrypt (new) or legacy SHA-1 (existing accounts).
$stored = $admin["password"];
$ok = false;
$needsUpgrade = false;
if (password_verify($password, $stored)) {
    $ok = true;
} elseif (strlen($stored) === 40 && ctype_xdigit($stored) && hash_equals($stored, sha1($password))) {
    $ok = true;
    $needsUpgrade = true;
}
if (!$ok) {
    json_response($invalid);
}

if ($admin["status"] !== "Active") {
    json_response(["success" => false, "message" => "Your account is inactive. Contact a Super Admin."]);
}

// Upgrade legacy hash to bcrypt now that we have the plaintext.
if ($needsUpgrade) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $up = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($up, "si", $newHash, $admin["id"]);
    mysqli_stmt_execute($up);
}

session_regenerate_id(true);
$_SESSION = [
    "admin_id"   => $admin["id"],
    "email"      => $admin["email"],
    "name"       => trim($admin["firstName"] . " " . $admin["lastName"]),
    "first_name" => $admin["firstName"],
    "last_name"  => $admin["lastName"],
    "picture"    => $admin["picture"],
    "designation"=> $admin["position"],
];

json_response([
    "success"  => true,
    "message"  => "Welcome back, " . $admin["firstName"] . "!",
    "redirect" => "dashboard.php",
]);
