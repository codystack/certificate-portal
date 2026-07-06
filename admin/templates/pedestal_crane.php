<?php
/**
 * Pedestal Crane "Certificate of Thorough Examination" → HTML for Dompdf.
 * $c['details'] = ['equipment' => [...], 'checklist' => [key => [result, comment]]].
 */
function render_pedestal_crane_template(array $c, array $def, string $qr): string
{
    $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8");
    $d = $c["details"] ?? [];
    $eq = $d["equipment"] ?? [];
    $cl = $d["checklist"] ?? [];

    $fmt = function ($date) {
        if (!$date) return "";
        $t = strtotime($date);
        return $t ? date("d/m/Y", $t) : (string) $date;
    };

    $fit = ($c["status"] ?? "Active") !== "Expired";

    $headerHtml = function (bool $withQr) use ($h, $def, $qr) {
        $qrCell = $withQr
            ? '<td class="qr"><img src="' . $qr . '" width="84" height="84"><div class="qrcap">Scan to verify</div></td>'
            : '';
        return '<table class="hdr"><tr>
            <td class="hdrmain">
                <img src="' . logo_data_uri() . '" class="logo">
                <div class="co">GLAJOE MULTI SERVICES LTD. <span class="rc">RC 1186853</span></div>
                <div class="addr">#3 Doxa Road, Off Peter Odili, Trans-Amadi, Port Harcourt, Rivers State.</div>
                <div class="contact">www.glajoeservices.com.ng &nbsp;|&nbsp; glajoeservices@gmail.com &nbsp;|&nbsp; ' . $h($def["tel"]) . '</div>
            </td>' . $qrCell . '</tr></table>';
    };

    $kv = fn($label, $value) => '<tr><td class="k">' . $h($label) . '</td><td class="v">' . $h($value) . '</td></tr>';

    // ---- Page 1: identity + equipment + result ----
    $identity = '<table class="grid">'
        . $kv("Certificate Number", $c["certNum"])
        . $kv($def["date_label"], $fmt($c["inspection_date"] ?? null))
        . $kv($def["next_date_label"], $fmt($c["next_inspection_date"] ?? null) ?: "N/A")
        . $kv("Date of Issue", $fmt($eq["date_of_issue"] ?? null))
        . $kv($def["location_label"], $c["test_location"])
        . $kv("Client", $c["client"])
        . '</table>';

    $eqRows = "";
    foreach ($def["equipment_fields"] as $k => $label) {
        if ($k === "date_of_issue") { continue; } // already shown in the identity block above
        $eqRows .= $kv($label, $eq[$k] ?? "");
    }

    $result = $fit
        ? '<span class="fit">Examination satisfactory — FIT FOR USE</span>'
        : '<span class="unfit">Examination unsatisfactory — UNFIT FOR USE</span>';

    $observationLines = array_values(array_filter(array_map("trim", preg_split('/\r\n|\r|\n/', (string) ($c["defects"] ?? "")))));
    $observations = '<div class="sec">OBSERVATIONS</div>';
    if ($observationLines) {
        $observations .= '<div class="obsnote">N/B: To be closed out before next inspection.</div><ol class="obs">';
        foreach ($observationLines as $line) {
            $observations .= '<li>' . $h($line) . '</li>';
        }
        $observations .= '</ol>';
    } else {
        $observations .= '<div class="obsnote">None.</div>';
    }

    $conclusion = '<div class="sec">CONCLUSION</div><div class="concl">'
        . 'Visual/Functional test was conducted and the condition of the crane as at the time of inspection was '
        . '<b>' . ($fit ? "Satisfactory" : "Unsatisfactory") . '</b>, therefore it is certified <b>' . ($fit ? "FIT FOR USE" : "UNFIT FOR USE") . '</b>.<br>'
        . '<i>Note: No mechanical repairs or modifications should be carried out on this equipment without the knowledge of the undersigned, as such nullifies the validity of the certificate.</i>'
        . '</div>';

    $sigCell = !empty($c["signature_img"])
        ? '<img src="' . $c["signature_img"] . '" class="sigimg"><div class="sigline">Signature of Inspector</div>'
        : 'Signature of Inspector: ____________________';
    $stampCell = !empty($c["stamp_img"]) ? '<img src="' . $c["stamp_img"] . '" class="stampimg">' : '';
    $signoff = '<table class="sign"><tr>
        <td>' . $sigCell . '</td>
        <td>Name/Qualification of Inspector: <b>' . $h(trim(($c["inspector_name"] ?? "") . ($c["qualification"] ? " — " . $c["qualification"] : ""))) . '</b></td>
        <td class="stampcell">' . $stampCell . '</td></tr></table>';

    $page1 = $headerHtml(true)
        . '<div class="rtitle">' . $h($def["report_title"]) . '</div>'
        . '<div class="rcomp">' . $h($def["compliance"]) . '</div>'
        . $identity
        . '<div class="sec">EQUIPMENT DETAILS</div><table class="grid">' . $eqRows . '</table>'
        . '<div class="sec">RESULT</div><div class="result">' . $result . '</div>'
        . $observations . $conclusion . $signoff
        . '<div class="regs">' . $h($def["reference_standard"]) . '</div>';

    // ---- Page 2+: checklist ----
    $opts = $def["checklist_options"] ?? ["1", "2", "3"];
    $rows = "";
    foreach ($def["checklist"] as $section => $items) {
        $rows .= '<tr><td class="csec" colspan="' . (count($opts) + 2) . '">' . $h($section) . '</td></tr>';
        foreach ($items as $item) {
            $key = checklist_key($item);
            $res = $cl[$key] ?? ["result" => "", "comment" => ""];
            $mark = fn($v) => ($res["result"] ?? "") === $v ? "&#10003;" : "";
            $rows .= '<tr><td class="cl">' . $h($item) . '</td>';
            foreach ($opts as $opt) {
                $rows .= '<td class="cm">' . $mark($opt) . '</td>';
            }
            $rows .= '<td class="cc">' . $h($res["comment"] ?? "") . '</td></tr>';
        }
    }

    $legendCols = "";
    foreach ($opts as $opt) { $legendCols .= '<th>' . $h($opt) . '</th>'; }

    $page2 = '<div class="pagebreak"></div>' . $headerHtml(false)
        . '<div class="intro">' . $h($def["checklist_legend"] ?? "") . '</div>'
        . '<table class="chk"><thead><tr><th class="hl">COMPONENT</th>' . $legendCols . '<th>REMARKS</th></tr></thead><tbody>' . $rows . '</tbody></table>';

    // ---- Page 3+ (optional): Hoisting Wire Details, Load Test Details,
    // Blocks & Reeving Report, Wire Rope Inspection Report. Only printed at
    // all when the certificate actually has this data — most routine
    // examinations don't include a load test.
    $renderTable = function (array $columns, array $rows) use ($h) {
        $thead = "";
        foreach ($columns as $label) { $thead .= '<th>' . $h($label) . '</th>'; }
        $tbody = "";
        foreach ($rows as $r) {
            $tbody .= '<tr>';
            foreach (array_keys($columns) as $ck) {
                $tbody .= '<td>' . $h($r[$ck] ?? "") . '</td>';
            }
            $tbody .= '</tr>';
        }
        return '<table class="wide"><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table>';
    };

    $hw = $d["hoisting_wire"] ?? [];
    $loadTest = $d["load_test"] ?? [];
    $reeving = $d["reeving"] ?? [];
    $wireRopeReport = $d["wire_rope_report"] ?? [];

    $extraSections = [];
    if (array_filter($hw)) {
        $hwRows = "";
        foreach ($def["hoisting_wire_fields"] as $k => $label) {
            $hwRows .= $kv($label, $hw[$k] ?? "");
        }
        $extraSections[] = '<div class="sec">HOISTING WIRE DETAILS</div><table class="grid">' . $hwRows . '</table>';
    }
    if ($loadTest) {
        $extraSections[] = '<div class="sec">SAFE WORKING LOAD TEST DETAILS</div>' . $renderTable($def["load_test_columns"], $loadTest);
    }
    if ($reeving) {
        $extraSections[] = '<div class="sec">BLOCKS AND REEVING REPORT</div>' . $renderTable($def["reeving_columns"], $reeving);
    }
    if ($wireRopeReport) {
        $extraSections[] = '<div class="sec">WIRE ROPE INSPECTION REPORT (MAIN HOIST LINE)</div>' . $renderTable($def["wire_rope_report_columns"], $wireRopeReport);
    }

    $page3 = "";
    if ($extraSections) {
        $page3 = '<div class="pagebreak"></div>' . $headerHtml(false) . implode('<div class="secgap"></div>', $extraSections);
    }

    $css = '
    @page { margin: 18px 22px; }
    body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:9.5px; }
    .hdr { width:100%; border-bottom:2px solid #c0392b; }
    .hdrmain { text-align:center; }
    .logo { height:34px; margin-bottom:2px; }
    .co { font-size:14px; font-weight:bold; color:#1a3c6e; }
    .rc { font-size:8px; color:#555; font-weight:normal; }
    .addr { font-size:8.5px; color:#444; }
    .contact { font-size:7.5px; color:#666; }
    .qr { width:96px; text-align:center; vertical-align:top; }
    .qrcap { font-size:6.5px; color:#555; }
    .rtitle { text-align:center; font-weight:bold; font-size:12px; letter-spacing:1px; margin:8px 0 1px; }
    .rcomp { text-align:center; font-size:7.5px; font-style:italic; color:#666; margin-bottom:5px; }
    .grid { width:100%; border-collapse:collapse; margin-top:6px; }
    .grid .k { width:33%; background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:3px 5px; }
    .grid .v { border:0.5px solid #ccc; padding:3px 5px; }
    .sec { font-weight:bold; font-size:10px; margin:9px 0 2px; color:#1a3c6e; }
    .result { font-size:10px; font-weight:bold; }
    .fit { color:#1a7f37; }
    .unfit { color:#c0392b; }
    .obsnote { font-size:8px; font-style:italic; color:#555; margin-bottom:2px; }
    .obs { margin:0; padding-left:16px; font-size:8.5px; }
    .obs li { margin-bottom:2px; }
    .concl { font-size:8.5px; text-align:justify; margin-top:2px; }
    .regs { margin-top:10px; font-size:7px; color:#666; }
    .sign { width:100%; margin-top:14px; }
    .sign td { width:33%; vertical-align:bottom; font-size:8.5px; }
    .sigimg { max-height:40px; max-width:140px; }
    .sigline { font-size:8px; border-top:1px solid #444; padding-top:2px; width:140px; }
    .stampcell { text-align:center; }
    .stampimg { max-height:64px; max-width:100px; }
    .pagebreak { page-break-before: always; }
    .intro { font-size:8.5px; margin:8px 0; font-weight:bold; }
    .chk { width:100%; border-collapse:collapse; }
    .chk th { border:0.5px solid #ccc; padding:3px 4px; background:#f3f5f8; font-size:8.5px; }
    .chk th.hl { text-align:left; }
    .csec { background:#1a3c6e; color:#fff; font-weight:bold; padding:3px 5px; font-size:9px; }
    .cl { border:0.5px solid #ccc; padding:3px 5px; font-size:9px; }
    .cm { border:0.5px solid #ccc; text-align:center; width:6%; font-weight:bold; color:#1a7f37; }
    .cc { border:0.5px solid #ccc; padding:3px 5px; width:26%; font-size:8.5px; }
    .secgap { margin-top:14px; }
    .wide { width:100%; border-collapse:collapse; margin-top:4px; }
    .wide th { border:0.5px solid #999; background:#1a3c6e; color:#fff; padding:2px 3px; font-size:6.5px; text-align:center; }
    .wide td { border:0.5px solid #bbb; padding:2px 3px; font-size:6.5px; text-align:center; white-space:nowrap; }
    ';

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>'
        . $page1 . $page2 . $page3 . '</body></html>';
}
