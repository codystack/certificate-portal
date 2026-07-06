<?php
/**
 * Electric Chain Hoist Non-Conformance Report → HTML for Dompdf.
 * $c['details'] = ['equipment' => [...], 'wire_rope' => ['remark'=>.., 'recommendations'=>..], 'checklist' => [...]].
 * The "wire_rope" slot (named for parity with the shared checklist-layout
 * parser) actually carries the page-2 Remark/Recommendations fields here,
 * since this equipment has no second identity sub-block like a winch's rope.
 */
function render_chain_hoist_defect_template(array $c, array $def, string $qr): string
{
    $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8");
    $d = $c["details"] ?? [];
    $eq = $d["equipment"] ?? [];
    $notes = $d["wire_rope"] ?? [];
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
                <div class="comp">' . $h($def["compliance"]) . '</div>
                <div class="contact">www.glajoeservices.com.ng &nbsp;|&nbsp; glajoeservices@gmail.com &nbsp;|&nbsp; ' . $h($def["tel"]) . '</div>
            </td>' . $qrCell . '</tr></table>';
    };

    $kv = fn($label, $value) => '<tr><td class="k">' . $h($label) . '</td><td class="v">' . $h($value) . '</td></tr>';

    // ---- Page 1: identity + equipment ----
    $identity = '<table class="grid">'
        . $kv("Client", $c["client"])
        . $kv("Certificate Number", $c["certNum"])
        . $kv($def["owner_label"] ?? "Equipment Owner / Address", $c["equipment_owner"])
        . $kv("Examiner & Qualification", trim(($c["examiner"] ?? "") . ($c["qualification"] ? " — " . $c["qualification"] : "")))
        . $kv("Test Location", $c["test_location"])
        . $kv("Reference Standard", $c["reference_standard"])
        . $kv("Inspection Date", $fmt($c["inspection_date"] ?? null))
        . $kv("Next Inspection Date", $fmt($c["next_inspection_date"] ?? null))
        . '</table>';

    $eqRows = "";
    foreach ($def["equipment_fields"] as $k => $label) {
        $eqRows .= $kv($label, $eq[$k] ?? "");
    }

    $defects = '<div class="defects"><div class="defq">' . $h($def["defects_prompt"]) . '</div>'
        . '<div class="defv">' . ($h($c["defects"]) ?: "NONE") . '</div></div>';

    $sigCell = !empty($c["signature_img"])
        ? '<img src="' . $c["signature_img"] . '" class="sigimg"><div class="sigline">Signature of Inspector</div>'
        : 'Signature of Inspector: ____________________';
    $stampCell = !empty($c["stamp_img"]) ? '<img src="' . $c["stamp_img"] . '" class="stampimg">' : '';
    $signoff = '<table class="sign"><tr>
        <td>' . $sigCell . '</td>
        <td>Name of Inspector: <b>' . $h($c["inspector_name"]) . '</b></td>
        <td class="stampcell">' . $stampCell . '</td></tr></table>';

    $page1 = $headerHtml(true)
        . '<div class="rtitle">' . $h($def["report_title"]) . '</div>'
        . $identity
        . '<div class="sec">EQUIPMENT DESCRIPTION</div><table class="grid">' . $eqRows . '</table>'
        . $defects . $signoff;

    // ---- Page 2: checklist + remark/recommendations ----
    $rows = "";
    foreach ($def["checklist"] as $section => $items) {
        $rows .= '<tr><td class="csec" colspan="5">' . $h($section) . '</td></tr>';
        foreach ($items as $item) {
            $key = checklist_key($item);
            $res = $cl[$key] ?? ["result" => "", "comment" => ""];
            $mark = fn($v) => ($res["result"] ?? "") === $v ? "&#10003;" : "";
            $rows .= '<tr>'
                . '<td class="cl">' . $h($item) . '</td>'
                . '<td class="cm">' . $mark("SAT") . '</td>'
                . '<td class="cm">' . $mark("UNSAT") . '</td>'
                . '<td class="cm">' . $mark("N/A") . '</td>'
                . '<td class="cc">' . $h($res["comment"] ?? "") . '</td></tr>';
        }
    }

    $recLines = array_values(array_filter(array_map("trim", preg_split('/\r\n|\r|\n/', (string) ($notes["recommendations"] ?? "")))));
    $recommendations = '<div class="sec">Recommendations</div>';
    if ($recLines) {
        $recommendations .= '<ol class="rec">';
        foreach ($recLines as $line) { $recommendations .= '<li>' . $h($line) . '</li>'; }
        $recommendations .= '</ol>';
    } else {
        $recommendations .= '<div class="remarktext">None.</div>';
    }

    $page2 = '<div class="pagebreak"></div>' . $headerHtml(false)
        . '<div class="intro">Visual and functional test were carried out on the equipment identified above as per requirements. And was found <b>' . ($fit ? "Fit for use" : "Unfit for use") . '</b>.</div>'
        . '<table class="chk"><thead><tr>'
        . '<th class="hl">INSPECTION COMPONENT</th><th>SAT.</th><th>UNSAT.</th><th>N/A</th><th>COMMENT</th>'
        . '</tr></thead><tbody>' . $rows . '</tbody></table>'
        . '<div class="sec">Remark</div><div class="remarktext">' . ($h($notes["remark"] ?? "") ?: "None.") . '</div>'
        . $recommendations;

    $css = '
    @page { margin: 18px 22px; }
    body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:9.5px; }
    .hdr { width:100%; border-bottom:2px solid #c0392b; }
    .hdrmain { text-align:center; }
    .logo { height:34px; margin-bottom:2px; }
    .co { font-size:14px; font-weight:bold; color:#1a3c6e; }
    .rc { font-size:8px; color:#555; font-weight:normal; }
    .addr { font-size:8.5px; color:#444; }
    .comp { font-size:7.5px; color:#666; font-style:italic; }
    .contact { font-size:7.5px; color:#666; }
    .qr { width:96px; text-align:center; vertical-align:top; }
    .qrcap { font-size:6.5px; color:#555; }
    .rtitle { text-align:center; font-weight:bold; font-size:12px; letter-spacing:1px; margin:8px 0 4px; color:#c0392b; }
    .grid { width:100%; border-collapse:collapse; }
    .grid .k { width:33%; background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:3px 5px; }
    .grid .v { border:0.5px solid #ccc; padding:3px 5px; }
    .sec { font-weight:bold; font-size:10px; margin:9px 0 2px; color:#1a3c6e; }
    .defects { margin-top:9px; }
    .defq { font-style:italic; font-size:8.5px; }
    .defv { font-weight:bold; margin-top:2px; }
    .remarktext { font-size:8.5px; }
    .rec { margin:2px 0 0; padding-left:16px; font-size:8.5px; }
    .rec li { margin-bottom:2px; }
    .sign { width:100%; margin-top:18px; }
    .sign td { width:33%; vertical-align:bottom; }
    .sigimg { max-height:40px; max-width:140px; }
    .sigline { font-size:8px; border-top:1px solid #444; padding-top:2px; width:140px; }
    .stampcell { text-align:center; }
    .stampimg { max-height:64px; max-width:100px; }
    .pagebreak { page-break-before: always; }
    .intro { font-size:9px; margin:8px 0; }
    .chk { width:100%; border-collapse:collapse; }
    .chk th { border:0.5px solid #ccc; padding:3px 4px; background:#f3f5f8; font-size:8.5px; }
    .chk th.hl { text-align:left; }
    .csec { background:#1a3c6e; color:#fff; font-weight:bold; padding:3px 5px; font-size:9px; }
    .cl { border:0.5px solid #ccc; padding:3px 5px; font-size:9px; }
    .cm { border:0.5px solid #ccc; text-align:center; width:9%; font-weight:bold; color:#1a7f37; }
    .cc { border:0.5px solid #ccc; padding:3px 5px; width:28%; font-size:8.5px; }
    ';

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>'
        . $page1 . $page2 . '</body></html>';
}
