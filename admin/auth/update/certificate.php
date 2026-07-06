<?php
require_once __DIR__ . "/../guard.php";

$id      = (int) ($_POST["id"] ?? 0);
$title   = trim($_POST["title"] ?? "");
$client  = trim($_POST["client"] ?? "");
$certNum = trim($_POST["certNum"] ?? "");
$status  = ($_POST["status"] ?? "Active") === "Expired" ? "Expired" : "Active";

if ($id <= 0) {
    json_response(["success" => false, "message" => "Invalid certificate."]);
}
if ($title === "" || $client === "" || $certNum === "") {
    json_response(["success" => false, "message" => "Title, client and certificate number are required."]);
}

// Load current record (for existing image)
$stmt = mysqli_prepare($conn, "SELECT image FROM certificate WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$current) {
    json_response(["success" => false, "message" => "Certificate not found."], 404);
}

$image = $current["image"];
$replaced = false;
if (!empty($_FILES["image"]["name"])) {
    $err = null;
    $stored = store_upload($_FILES["image"], $err);
    if ($err) {
        json_response(["success" => false, "message" => $err]);
    }
    $image = $stored;
    $replaced = true;
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE certificate SET title = ?, client = ?, certNum = ?, image = ?, status = ? WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "sssssi", $title, $client, $certNum, $image, $status, $id);

if (mysqli_stmt_execute($stmt)) {
    if ($replaced && $current["image"]) {
        delete_upload($current["image"]);
    }
    log_activity($conn, (int) $_SESSION["admin_id"], "updated", "certificate", $id, "Updated certificate {$certNum}");
    json_response(["success" => true, "message" => "Certificate updated.", "redirect" => "certificates.php"]);
}
if ($replaced) {
    delete_upload($image);
}
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "A certificate with that number already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
