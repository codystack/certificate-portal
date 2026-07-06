<?php
require_once __DIR__ . "/../guard.php";

$title   = trim($_POST["title"] ?? "");
$client  = trim($_POST["client"] ?? "");
$certNum = trim($_POST["certNum"] ?? "");
$status  = ($_POST["status"] ?? "Active") === "Expired" ? "Expired" : "Active";

if ($title === "" || $client === "" || $certNum === "") {
    json_response(["success" => false, "message" => "Title, client and certificate number are required."]);
}

$imagePath = null;
if (!empty($_FILES["image"]["name"])) {
    $err = null;
    $imagePath = store_upload($_FILES["image"], $err);
    if ($err) {
        json_response(["success" => false, "message" => $err]);
    }
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO certificate (title, client, certNum, image, status) VALUES (?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sssss", $title, $client, $certNum, $imagePath, $status);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    log_activity($conn, (int) $_SESSION["admin_id"], "created", "certificate", $newId, "Created certificate {$certNum}");
    json_response(["success" => true, "message" => "Certificate added successfully.", "redirect" => "certificates.php"]);
}
delete_upload($imagePath);
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "A certificate with that number already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
