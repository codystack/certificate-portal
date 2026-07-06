<?php
require_once __DIR__ . "/../guard.php";
require_once __DIR__ . "/../../lib/resend.php";
require_role("Super Admin");

$firstName = trim($_POST["firstName"] ?? "");
$lastName  = trim($_POST["lastName"] ?? "");
$email     = trim($_POST["email"] ?? "");
$position  = ($_POST["position"] ?? "Admin") === "Super Admin" ? "Super Admin" : "Admin";
$status    = "Active";

if ($firstName === "" || $lastName === "") {
    json_response(["success" => false, "message" => "First and last name are required."]);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(["success" => false, "message" => "A valid email is required."]);
}

// Auto-generate a login password; the new admin gets it by email and can
// change it from Security afterwards.
$plainPassword = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%&*"), 0, 10);
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO admin (email, password, firstName, lastName, position, status) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ssssss", $email, $hash, $firstName, $lastName, $position, $status);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    log_activity($conn, (int) $_SESSION["admin_id"], "created", "admin", $newId, "Created admin {$firstName} {$lastName} ({$email})");

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
              <h2 style="font-size:20px;margin:0 0 15px;">Admin Account Created</h2>
              <p style="font-size:14px;margin:0 0 15px;">Hi ' . e($firstName) . ',</p>
              <p style="font-size:14px;margin:0 0 20px;">An admin account has been created for you on Glajoe Services. Below are your login credentials:</p>
              <table style="width:100%;background:#f9f9f9;border-radius:6px;padding:15px;">
                <tr><td><b>Email:</b> ' . e($email) . '</td></tr>
                <tr><td><b>Password:</b> ' . e($plainPassword) . '</td></tr>
              </table>
              <p style="font-size:13px;color:#666;margin-top:15px;">
                Please sign in and change your password from Security as soon as possible.
              </p>
              <p style="text-align:center;margin-top:25px;">
                <a href="' . e($siteUrl) . 'admin" style="display:inline-block;padding:10px 25px;background:#ed1c24;color:#fff;text-decoration:none;border-radius:5px;">Go to Admin Dashboard</a>
              </p>
            </td></tr>
            <tr><td style="text-align:center;padding:20px 30px 40px 30px;">
              <p style="font-size:12px;color:#aaa;margin-top:20px;">&copy; ' . date("Y") . ' Glajoe Services. All rights reserved.</p>
            </td></tr>
          </tbody>
        </table>
      </td></tr></tbody>
    </table>';
    send_email($email, "Admin Account Creation", $html);

    json_response(["success" => true, "message" => "Admin created. Login credentials have been sent to {$email}.", "redirect" => "admins.php"]);
}
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "An admin with that email already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
