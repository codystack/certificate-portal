<?php
/**
 * Shared $_POST → certificate-row parser for inspection-generated
 * certificates. Used by both auth/create/inspection.php and
 * auth/update/inspection.php so the two stay in lockstep as new
 * inspection types/fields are added.
 *
 * Ends the request (via json_response) on validation failure, exactly
 * like the callers used to do inline.
 */
require_once __DIR__ . "/inspection_types.php";

function parse_inspection_post(array $def): array
{
    $client  = trim($_POST["client"] ?? "");
    $certNum = trim($_POST["certNum"] ?? "");
    if ($client === "" || $certNum === "") {
        json_response(["success" => false, "message" => "Client and certificate number are required."]);
    }

    $norm = fn($k) => trim($_POST[$k] ?? "");
    $dt   = fn($k) => (($v = trim($_POST[$k] ?? "")) !== "" ? $v : null);

    $out = [
        "client"                => $client,
        "certNum"               => $certNum,
        "equipment_owner"       => $norm("equipment_owner"),
        "examiner"              => $norm("examiner"),
        "qualification"         => $norm("qualification"),
        "test_location"         => $norm("test_location"),
        "reference_standard"    => $norm("reference_standard") ?: ($def["reference_standard"] ?? ""),
        "inspector_name"        => $norm("inspector_name"),
        "inspection_date"       => $dt("inspection_date"),
        "next_inspection_date"  => $dt("next_inspection_date"),
        "defects"               => $norm("defects"),
        "status"                => ($_POST["status"] ?? "Active") === "Expired" ? "Expired" : "Active",
    ];

    $layout = $def["layout"] ?? "checklist";
    $detailsArr = [];
    $title = $def["label"];

    if ($layout === "checklist") {
        $equipment = [];
        foreach (array_keys($def["equipment_fields"]) as $k) {
            $equipment[$k] = trim($_POST["eq"][$k] ?? "");
        }
        $wireRope = [];
        foreach (array_keys($def["subblock_fields"] ?? []) as $k) {
            $wireRope[$k] = trim($_POST["wr"][$k] ?? "");
        }
        $validResults = $def["checklist_options"] ?? ["SAT", "UNSAT", "N/A"];
        $checklist = [];
        foreach (checklist_components($def) as $comp) {
            $key = checklist_key($comp["item"]);
            $r = $_POST["cl"][$key] ?? [];
            $result = in_array($r["result"] ?? "", $validResults, true) ? $r["result"] : "";
            $checklist[$key] = ["result" => $result, "comment" => trim($r["comment"] ?? "")];
        }
        // Optional extra pages (currently only Pedestal Crane defines these
        // column sets; every other checklist-layout type just gets empty
        // arrays here, so this is a no-op for them).
        $hoistingWire = [];
        foreach (array_keys($def["hoisting_wire_fields"] ?? []) as $k) {
            $hoistingWire[$k] = trim($_POST["hw"][$k] ?? "");
        }
        $repeatRows = function (string $postKey, array $columns): array {
            $out = [];
            foreach (($_POST[$postKey] ?? []) as $row) {
                $clean = [];
                $any = false;
                foreach (array_keys($columns) as $ck) {
                    $clean[$ck] = trim($row[$ck] ?? "");
                    if ($clean[$ck] !== "") { $any = true; }
                }
                if ($any) { $out[] = $clean; }
            }
            return $out;
        };
        $loadTest = $repeatRows("loadtest", $def["load_test_columns"] ?? []);
        $reeving = $repeatRows("reeving", $def["reeving_columns"] ?? []);
        $wireRopeReport = $repeatRows("wrreport", $def["wire_rope_report_columns"] ?? []);

        $detailsArr = [
            "equipment" => $equipment, "wire_rope" => $wireRope, "checklist" => $checklist,
            "hoisting_wire" => $hoistingWire, "load_test" => $loadTest,
            "reeving" => $reeving, "wire_rope_report" => $wireRopeReport,
        ];
        $titleKey = $def["title_field"] ?? "description";
        if (($equipment[$titleKey] ?? "") !== "") {
            $title = $equipment[$titleKey];
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
        $multi = !empty($def["multi_equipment"]);
        if ($multi) {
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
        } else {
            $equipment = [];
            foreach (array_keys($def["equipment_fields"]) as $k) {
                $equipment[$k] = trim($_POST["eq"][$k] ?? "");
            }
        }
        $questions = [];
        foreach (array_keys($def["questions"]) as $qk) {
            $questions[$qk] = (($_POST["q"][$qk] ?? "") === "NO") ? "NO" : "YES";
        }
        $results = [];
        foreach (array_keys($def["result_fields"]) as $k) {
            $results[$k] = trim($_POST["res"][$k] ?? "");
        }
        if ($multi) {
            $detailsArr = ["items" => $items, "questions" => $questions, "results" => $results];
            if (($items[0]["description"] ?? "") !== "") {
                $title = $items[0]["description"];
            }
        } else {
            $detailsArr = ["equipment" => $equipment, "questions" => $questions, "results" => $results];
            if (($equipment["description"] ?? "") !== "") {
                $title = $equipment["description"];
            }
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

    $out["title"] = $title;
    $out["details"] = $detailsArr;

    return $out;
}
