<?php
require_once __DIR__ . "/../guard.php";
require_role("Super Admin");

$firstName = trim($_POST["firstName"] ?? "");
$lastName  = trim($_POST["lastName"] ?? "");
$email     = trim($_POST["email"] ?? "");
$password  = $_POST["password"] ?? "";
$position  = ($_POST["position"] ?? "Admin") === "Super Admin" ? "Super Admin" : "Admin";
$status    = ($_POST["status"] ?? "Active") === "Inactive" ? "Inactive" : "Active";

if ($firstName === "" || $lastName === "") {
    json_response(["success" => false, "message" => "First and last name are required."]);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(["success" => false, "message" => "A valid email is required."]);
}
if (strlen($password) < 6) {
    json_response(["success" => false, "message" => "Password must be at least 6 characters."]);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO admin (email, password, firstName, lastName, position, status) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ssssss", $email, $hash, $firstName, $lastName, $position, $status);

if (mysqli_stmt_execute($stmt)) {
    json_response(["success" => true, "message" => "Admin created.", "redirect" => "admins.php"]);
}
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "An admin with that email already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
