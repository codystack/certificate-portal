<?php
require_once __DIR__ . "/../guard.php";

$id        = (int) $_SESSION["admin_id"];
$firstName = trim($_POST["firstName"] ?? "");
$lastName  = trim($_POST["lastName"] ?? "");

// Photo-only upload (from profile.php's upload button) skips the name fields.
$photoOnly = !empty($_FILES["photo"]["name"]) && $firstName === "" && $lastName === "";

if (!$photoOnly && ($firstName === "" || $lastName === "")) {
    json_response(["success" => false, "message" => "First and last name are required."]);
}

$stmt = mysqli_prepare($conn, "SELECT firstName, lastName, picture FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$current) {
    json_response(["success" => false, "message" => "Account not found."], 404);
}

$firstName = $photoOnly ? $current["firstName"] : $firstName;
$lastName  = $photoOnly ? $current["lastName"] : $lastName;

$picture = $current["picture"];
$replaced = false;
if (!empty($_FILES["photo"]["name"])) {
    $err = null;
    $stored = store_upload($_FILES["photo"], $err);
    if ($err) {
        json_response(["success" => false, "message" => $err]);
    }
    $picture = $stored;
    $replaced = true;
}

$stmt = mysqli_prepare($conn, "UPDATE admin SET firstName = ?, lastName = ?, picture = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "sssi", $firstName, $lastName, $picture, $id);

if (mysqli_stmt_execute($stmt)) {
    if ($replaced && $current["picture"]) {
        delete_upload($current["picture"]);
    }
    $_SESSION["first_name"] = $firstName;
    $_SESSION["last_name"]  = $lastName;
    $_SESSION["name"]       = trim($firstName . " " . $lastName);
    $_SESSION["picture"]    = $picture;
    log_activity($conn, $id, "updated", "profile", $id, $photoOnly ? "Updated profile photo" : "Updated profile information");
    json_response(["success" => true, "message" => $photoOnly ? "Photo updated." : "Profile updated."]);
}
if ($replaced) {
    delete_upload($picture);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
