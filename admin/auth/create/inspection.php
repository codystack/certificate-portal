<?php
require_once __DIR__ . "/../guard.php";
require_once __DIR__ . "/../../lib/inspection_types.php";
require_once __DIR__ . "/../../lib/pdf.php";

$typeKey = preg_replace("/[^a-z0-9_]/", "", strtolower($_POST["type"] ?? ""));
$def = inspection_type($typeKey);
if (!$def) {
    json_response(["success" => false, "message" => "Unknown inspection type."]);
}

$client  = trim($_POST["client"] ?? "");
$certNum = trim($_POST["certNum"] ?? "");
if ($client === "" || $certNum === "") {
    json_response(["success" => false, "message" => "Client and certificate number are required."]);
}

// Common header fields
$norm = fn($k) => trim($_POST[$k] ?? "");
$dt   = fn($k) => (($v = trim($_POST[$k] ?? "")) !== "" ? $v : null);

$equipment_owner      = $norm("equipment_owner");
$examiner             = $norm("examiner");
$qualification        = $norm("qualification");
$test_location        = $norm("test_location");
$reference_standard   = $norm("reference_standard") ?: ($def["reference_standard"] ?? "");
$inspector_name       = $norm("inspector_name");
$inspection_date      = $dt("inspection_date");
$next_inspection_date = $dt("next_inspection_date");
$defects              = $norm("defects");
$status               = ($_POST["status"] ?? "Active") === "Expired" ? "Expired" : "Active";

// Type-specific blocks, by layout
$layout = $def["layout"] ?? "checklist";
$detailsArr = [];
$title = $def["label"];

if ($layout === "checklist") {
    $equipment = [];
    foreach (array_keys($def["equipment_fields"]) as $k) {
        $equipment[$k] = trim($_POST["eq"][$k] ?? "");
    }
    $wireRope = [];
    foreach (array_keys($def["subblock_fields"]) as $k) {
        $wireRope[$k] = trim($_POST["wr"][$k] ?? "");
    }
    $checklist = [];
    foreach (checklist_components($def) as $comp) {
        $key = checklist_key($comp["item"]);
        $r = $_POST["cl"][$key] ?? [];
        $result = in_array($r["result"] ?? "", ["SAT", "UNSAT", "N/A"], true) ? $r["result"] : "";
        $checklist[$key] = ["result" => $result, "comment" => trim($r["comment"] ?? "")];
    }
    $detailsArr = ["equipment" => $equipment, "wire_rope" => $wireRope, "checklist" => $checklist];
    if (($equipment["description"] ?? "") !== "") {
        $title = $equipment["description"];
    }
} elseif ($layout === "items") {
    $items = [];
    foreach (($_POST["items"] ?? []) as $row) {
        $clean = [];
        $any = false;
        foreach (array_keys($def["item_columns"]) as $ck) {
            $clean[$ck] = trim($row[$ck] ?? "");
            if ($clean[$ck] !== "") { $any = true; }
        }
        if ($any) { $items[] = $clean; }
    }
    if (!$items) {
        json_response(["success" => false, "message" => "Add at least one item."]);
    }
    $spec = [];
    foreach ($def["spec_fields"] as $k => $meta) {
        $spec[$k] = trim($_POST["spec"][$k] ?? "");
    }
    $detailsArr = ["items" => $items, "spec" => $spec];
    if (($items[0]["description"] ?? "") !== "") {
        $title = $items[0]["description"];
    }
} elseif ($layout === "loler") {
    $equipment = [];
    foreach (array_keys($def["equipment_fields"]) as $k) {
        $equipment[$k] = trim($_POST["eq"][$k] ?? "");
    }
    $questions = [];
    foreach (array_keys($def["questions"]) as $qk) {
        $questions[$qk] = (($_POST["q"][$qk] ?? "") === "NO") ? "NO" : "YES";
    }
    $results = [];
    foreach (array_keys($def["result_fields"]) as $k) {
        $results[$k] = trim($_POST["res"][$k] ?? "");
    }
    $detailsArr = ["equipment" => $equipment, "questions" => $questions, "results" => $results];
    if (($equipment["description"] ?? "") !== "") {
        $title = $equipment["description"];
    }
} elseif ($layout === "tally") {
    $spec = [];
    foreach ($def["spec_fields"] as $k => $meta) {
        $spec[$k] = trim($_POST["spec"][$k] ?? "");
    }
    $cols = array_keys(tally_columns($def));
    $rows = [];
    foreach (($_POST["items"] ?? []) as $row) {
        $clean = [];
        $any = false;
        foreach ($cols as $ck) {
            $clean[$ck] = trim($row[$ck] ?? "");
            if ($clean[$ck] !== "") { $any = true; }
        }
        if ($any) { $rows[] = $clean; }
    }
    if (!$rows) {
        json_response(["success" => false, "message" => "Add at least one pipe row."]);
    }
    $detailsArr = ["spec" => $spec, "rows" => $rows];
    $bits = array_filter([$spec["size"] ?? "", $spec["grade"] ?? ""]);
    $title = $def["label"] . ($bits ? " (" . implode(" ", $bits) . ")" : "");
}

$details = json_encode($detailsArr);

// Build the array the template/PDF generator expects (details decoded).
$certForPdf = [
    "type" => $typeKey, "certNum" => $certNum, "client" => $client,
    "equipment_owner" => $equipment_owner, "examiner" => $examiner, "qualification" => $qualification,
    "test_location" => $test_location, "reference_standard" => $reference_standard,
    "inspection_date" => $inspection_date, "next_inspection_date" => $next_inspection_date,
    "inspector_name" => $inspector_name, "defects" => $defects,
    "details" => $detailsArr,
];

try {
    $imagePath = generate_certificate_pdf($certForPdf);
} catch (Throwable $ex) {
    json_response(["success" => false, "message" => "Could not generate PDF: " . $ex->getMessage()], 500);
}

$generated = 1;
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO certificate
        (title, type, client, equipment_owner, examiner, qualification, test_location,
         reference_standard, inspection_date, next_inspection_date, inspector_name,
         defects, details, status, certNum, image, generated)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
);
mysqli_stmt_bind_param(
    $stmt, "ssssssssssssssssi",
    $title, $typeKey, $client, $equipment_owner, $examiner, $qualification, $test_location,
    $reference_standard, $inspection_date, $next_inspection_date, $inspector_name,
    $defects, $details, $status, $certNum, $imagePath, $generated
);

if (mysqli_stmt_execute($stmt)) {
    json_response(["success" => true, "message" => "Certificate generated successfully.", "redirect" => "certificates.php"]);
}
delete_upload($imagePath);
if (mysqli_errno($conn) === 1062) {
    json_response(["success" => false, "message" => "A certificate with that number already exists."]);
}
json_response(["success" => false, "message" => "Database error. Please try again."], 500);
