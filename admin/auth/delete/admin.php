<?php
require_once __DIR__ . "/../guard.php";
require_role("Super Admin");

$id = (int) ($_POST["id"] ?? 0);
if ($id <= 0) {
    json_response(["success" => false, "message" => "Invalid admin."]);
}
if ($id === (int) $_SESSION["admin_id"]) {
    json_response(["success" => false, "message" => "You cannot delete your own account."]);
}

$stmt = mysqli_prepare($conn, "SELECT firstName, lastName FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare($conn, "DELETE FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $name = $target ? trim($target["firstName"] . " " . $target["lastName"]) : "admin #{$id}";
    log_activity($conn, (int) $_SESSION["admin_id"], "deleted", "admin", $id, "Deleted admin {$name}");
    json_response(["success" => true, "message" => "Admin removed."]);
}
json_response(["success" => false, "message" => "Could not remove admin."], 500);
