<?php
require_once __DIR__ . "/../guard.php";
require_once __DIR__ . "/../../lib/inspection_types.php";
require_once __DIR__ . "/../../lib/inspection_form.php";
require_once __DIR__ . "/../../lib/pdf.php";

$typeKey = preg_replace("/[^a-z0-9_]/", "", strtolower($_POST["type"] ?? ""));
$def = inspection_type($typeKey);
if (!$def) {
    json_response(["success" => false, "message" => "Unknown inspection type."]);
}

$f = parse_inspection_post($def);

$signaturePath = null;
if (!empty($_FILES["inspector_signature"]["name"])) {
    $err = null;
    $signaturePath = store_upload($_FILES["inspector_signature"], $err);
    if ($err) {
        json_response(["success" => false, "message" => "Signature: " . $err]);
    }
}
$stampPath = null;
if (!empty($_FILES["company_stamp"]["name"])) {
    $err = null;
    $stampPath = store_upload($_FILES["company_stamp"], $err);
    if ($err) {
        delete_upload($signaturePath);
        json_response(["success" => false, "message" => "Stamp: " . $err]);
    }
}

// Build the array the template/PDF generator expects (details decoded).
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
    $imagePath = generate_certificate_pdf($certForPdf);
} catch (Throwable $ex) {
    delete_upload($signaturePath);
    delete_upload($stampPath);
    json_response(["success" => false, "message" => "Could not generate PDF: " . $ex->getMessage()], 500);
}

$details = json_encode($f["details"]);
$generated = 1;
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO certificate
        (title, type, client, equipment_owner, examiner, qualification, test_location,
         reference_standard, inspection_date, next_inspection_date, inspector_name,
         inspector_signature, company_stamp, defects, details, status, certNum, image, generated)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);
mysqli_stmt_bind_param(
    $stmt, "ssssssssssssssssssi",
    $f["title"], $typeKey, $f["client"], $f["equipment_owner"], $f["examiner"], $f["qualification"], $f["test_location"],
    $f["reference_standard"], $f["inspection_date"], $f["next_inspection_date"], $f["inspector_name"],
    $signaturePath, $stampPath, $f["defects"], $details, $f["status"], $f["certNum"], $imagePath, $generated
);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    log_activity($conn, (int) $_SESSION["admin_id"], "created", "certificate", $newId, "Generated {$def["label"]} certificate {$f["certNum"]}");
    json_response(["success" => true, "message" => "Certificate generated successfully.", "redirect" => "certificates.php"]);
}
delete_upload($imagePath);
delete_upload($signaturePath);
delete_upload($stampPath);
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "A certificate with that number already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
