<?php
require_once __DIR__ . "/../guard.php";
require_role("Super Admin");

$id     = (int) ($_POST["id"] ?? 0);
$action = $_POST["action"] ?? "";

if ($id <= 0 || !in_array($action, ["suspend", "unsuspend"], true)) {
    json_response(["success" => false, "message" => "Invalid request."]);
}
if ($id === (int) $_SESSION["admin_id"]) {
    json_response(["success" => false, "message" => "You cannot change your own status."]);
}

$newStatus = $action === "suspend" ? "Inactive" : "Active";

$stmt = mysqli_prepare($conn, "SELECT firstName, lastName FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$target) {
    json_response(["success" => false, "message" => "Admin not found."], 404);
}

$stmt = mysqli_prepare($conn, "UPDATE admin SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);

if (mysqli_stmt_execute($stmt)) {
    $name = trim($target["firstName"] . " " . $target["lastName"]);
    log_activity($conn, (int) $_SESSION["admin_id"], "status_changed", "admin", $id, ucfirst($action) . "ed admin {$name}");
    json_response([
        "success" => true,
        "message" => $action === "suspend" ? "Admin suspended." : "Admin reinstated.",
    ]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
