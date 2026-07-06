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
require_once __DIR__ . "/../lib/resend.php";

verify_csrf(true);

/** Best-effort client IP, skipping private/reserved ranges from proxy headers. */
function get_client_ip(): string
{
    foreach (["HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR"] as $key) {
        $val = $_SERVER[$key] ?? "";
        $ip = trim(explode(",", $val)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }
    return $_SERVER["REMOTE_ADDR"] ?? "Unknown";
}

/** Best-effort city/country for an IP via a free GeoIP lookup; never throws. */
function resolve_ip_location(string $ip): string
{
    if ($ip === "Unknown") {
        return "Unknown";
    }
    $ctx = stream_context_create(["http" => ["timeout" => 3]]);
    $json = @file_get_contents("https://ipapi.co/{$ip}/json/", false, $ctx);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data["city"]) && !empty($data["country_name"])) {
            return $data["city"] . ", " . $data["country_name"];
        }
    }
    return "Unavailable";
}

function send_login_notification_email(array $admin, string $ip): void
{
    date_default_timezone_set("Africa/Lagos");
    $time = date("l, jS F Y \\a\\t g:i A");
    $location = resolve_ip_location($ip);
    $device = $_SERVER["HTTP_USER_AGENT"] ?? "Unknown device";
    $siteUrl = public_base_url();
    $logoUrl = $siteUrl . "assets/images/glajoe-favicon.png";

    $html = '
    <table style="width:100%;background:#f5f6fa;font-family:Arial,sans-serif;padding:20px;">
      <tbody><tr><td>
        <table style="margin:0 auto;max-width:600px;background:#fff;border-radius:8px;overflow:hidden;">
          <tbody>
            <tr><td style="text-align:center;padding-top:30px;">
              <a href="' . e($siteUrl) . '"><img src="' . e($logoUrl) . '" alt="Glajoe Services" width="60"></a>
            </td></tr>
            <tr><td style="padding:30px 30px 10px 30px;text-align:left;color:#444;">
              <h2 style="font-size:20px;margin:0 0 15px;">Admin Login Alert</h2>
              <p style="font-size:14px;margin:0 0 15px;">Hi ' . e($admin["firstName"]) . ',</p>
              <p style="font-size:14px;margin:0 0 20px;">A login was just detected on your Glajoe Services admin account.</p>
              <table style="width:100%;background:#f9f9f9;border-radius:6px;padding:15px;">
                <tr><td><b>Time:</b> ' . e($time) . '</td></tr>
                <tr><td><b>IP:</b> ' . e($ip) . '</td></tr>
                <tr><td><b>Location:</b> ' . e($location) . '</td></tr>
                <tr><td><b>Device:</b> ' . e($device) . '</td></tr>
              </table>
              <p style="font-size:13px;color:#666;margin-top:15px;">
                If this was you, no action is required.<br>
                If not, please reset your password immediately.
              </p>
            </td></tr>
            <tr><td style="text-align:center;padding:20px 30px 40px 30px;">
              <p style="font-size:12px;color:#aaa;margin-top:20px;">&copy; ' . date("Y") . ' Glajoe Services. All rights reserved.</p>
            </td></tr>
          </tbody>
        </table>
      </td></tr></tbody>
    </table>';

    send_email($admin["email"], "Login Notification", $html);
}

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

log_activity($conn, (int) $admin["id"], "logged_in", "auth");

// Respond immediately; the login-notification email (GeoIP lookup + send)
// runs after the response is flushed so the user isn't kept waiting on it.
$payload = json_encode([
    "success"  => true,
    "message"  => "Welcome back, " . $admin["firstName"] . "!",
    "redirect" => "dashboard.php",
]);
session_write_close();
ignore_user_abort(true);
header("Connection: close");
ob_start();
echo $payload;
header("Content-Length: " . ob_get_length());
ob_end_flush();
if (function_exists("fastcgi_finish_request")) {
    fastcgi_finish_request();
} else {
    flush();
}

send_login_notification_email($admin, get_client_ip());
exit;
