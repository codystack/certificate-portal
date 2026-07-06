<?php
require_once __DIR__ . "/../guard.php";
require_role("Super Admin");

$id        = (int) ($_POST["id"] ?? 0);
$firstName = trim($_POST["firstName"] ?? "");
$lastName  = trim($_POST["lastName"] ?? "");
$email     = trim($_POST["email"] ?? "");
$position  = ($_POST["position"] ?? "Admin") === "Super Admin" ? "Super Admin" : "Admin";

if ($id <= 0) {
    json_response(["success" => false, "message" => "Invalid admin."]);
}
if ($id === (int) $_SESSION["admin_id"]) {
    json_response(["success" => false, "message" => "Use the Profile page to edit your own account."]);
}
if ($firstName === "" || $lastName === "") {
    json_response(["success" => false, "message" => "First and last name are required."]);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(["success" => false, "message" => "A valid email is required."]);
}

$stmt = mysqli_prepare($conn, "UPDATE admin SET firstName = ?, lastName = ?, email = ?, position = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "ssssi", $firstName, $lastName, $email, $position, $id);

if (mysqli_stmt_execute($stmt)) {
    log_activity($conn, (int) $_SESSION["admin_id"], "updated", "admin", $id, "Updated admin {$firstName} {$lastName} ({$email})");
    json_response(["success" => true, "message" => "Admin updated.", "redirect" => "admins.php"]);
}
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "An admin with that email already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
