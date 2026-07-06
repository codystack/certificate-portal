<?php
require_once __DIR__ . "/../guard.php";
require_once __DIR__ . "/../../lib/inspection_types.php";
require_once __DIR__ . "/../../lib/inspection_form.php";
require_once __DIR__ . "/../../lib/pdf.php";

$id = (int) ($_POST["id"] ?? 0);
if ($id <= 0) {
    json_response(["success" => false, "message" => "Invalid certificate."]);
}

$stmt = mysqli_prepare($conn, "SELECT * FROM certificate WHERE id = ? AND generated = 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$existing) {
    json_response(["success" => false, "message" => "Certificate not found."], 404);
}

$typeKey = $existing["type"];
$def = inspection_type($typeKey);
if (!$def) {
    json_response(["success" => false, "message" => "Unknown inspection type."]);
}

$f = parse_inspection_post($def);

// Only replace signature/stamp if a new file was uploaded; otherwise keep the current one.
$signaturePath = $existing["inspector_signature"];
if (!empty($_FILES["inspector_signature"]["name"])) {
    $err = null;
    $newSig = store_upload($_FILES["inspector_signature"], $err);
    if ($err) {
        json_response(["success" => false, "message" => "Signature: " . $err]);
    }
    $signaturePath = $newSig;
}
$stampPath = $existing["company_stamp"];
if (!empty($_FILES["company_stamp"]["name"])) {
    $err = null;
    $newStamp = store_upload($_FILES["company_stamp"], $err);
    if ($err) {
        if ($signaturePath !== $existing["inspector_signature"]) { delete_upload($signaturePath); }
        json_response(["success" => false, "message" => "Stamp: " . $err]);
    }
    $stampPath = $newStamp;
}

$certForPdf = [
    "type" => $typeKey, "certNum" => $f["certNum"], "client" => $f["client"],
    "equipment_owner" => $f["equipment_owner"], "examiner" => $f["examiner"], "qualification" => $f["qualification"],
    "test_location" => $f["test_location"], "reference_standard" => $f["reference_standard"],
    "inspection_date" => $f["inspection_date"], "next_inspection_date" => $f["next_inspection_date"],
    "inspector_name" => $f["inspector_name"], "defects" => $f["defects"], "status" => $f["status"],
    "inspector_signature" => $signaturePath, "company_stamp" => $stampPath,
    "details" => $f["details"],
];

try {
    $newImagePath = generate_certificate_pdf($certForPdf);
} catch (Throwable $ex) {
    if ($signaturePath !== $existing["inspector_signature"]) { delete_upload($signaturePath); }
    if ($stampPath !== $existing["company_stamp"]) { delete_upload($stampPath); }
    json_response(["success" => false, "message" => "Could not generate PDF: " . $ex->getMessage()], 500);
}

$details = json_encode($f["details"]);
$stmt = mysqli_prepare(
    $conn,
    "UPDATE certificate SET
        title = ?, client = ?, equipment_owner = ?, examiner = ?, qualification = ?, test_location = ?,
        reference_standard = ?, inspection_date = ?, next_inspection_date = ?, inspector_name = ?,
        inspector_signature = ?, company_stamp = ?, defects = ?, details = ?, status = ?, certNum = ?, image = ?
     WHERE id = ?"
);
mysqli_stmt_bind_param(
    $stmt, "sssssssssssssssssi",
    $f["title"], $f["client"], $f["equipment_owner"], $f["examiner"], $f["qualification"], $f["test_location"],
    $f["reference_standard"], $f["inspection_date"], $f["next_inspection_date"], $f["inspector_name"],
    $signaturePath, $stampPath, $f["defects"], $details, $f["status"], $f["certNum"], $newImagePath, $id
);

if (mysqli_stmt_execute($stmt)) {
    delete_upload($existing["image"]);
    if ($signaturePath !== $existing["inspector_signature"]) { delete_upload($existing["inspector_signature"]); }
    if ($stampPath !== $existing["company_stamp"]) { delete_upload($existing["company_stamp"]); }
    log_activity($conn, (int) $_SESSION["admin_id"], "updated", "certificate", $id, "Updated {$def["label"]} certificate {$f["certNum"]}");
    json_response(["success" => true, "message" => "Certificate updated.", "redirect" => "certificates.php"]);
}
delete_upload($newImagePath);
if ($signaturePath !== $existing["inspector_signature"]) { delete_upload($signaturePath); }
if ($stampPath !== $existing["company_stamp"]) { delete_upload($stampPath); }
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "A certificate with that number already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
