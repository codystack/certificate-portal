<?php
require_once __DIR__ . "/../guard.php";

$id = (int) ($_POST["id"] ?? 0);
if ($id <= 0) {
    json_response(["success" => false, "message" => "Invalid certificate."]);
}

$stmt = mysqli_prepare($conn, "SELECT image, certNum FROM certificate WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare($conn, "DELETE FROM certificate WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    delete_upload($row["image"] ?? null);
    $certNum = $row["certNum"] ?? "#{$id}";
    log_activity($conn, (int) $_SESSION["admin_id"], "deleted", "certificate", $id, "Deleted certificate {$certNum}");
    json_response(["success" => true, "message" => "Certificate deleted."]);
}
json_response(["success" => false, "message" => "Could not delete certificate."], 500);
