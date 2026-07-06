<?php
/**
 * Wire Rope Sling LOLER "Certificate of Thorough Examination" → HTML for Dompdf.
 * $c['details'] = ['items' => [[id_no,qty,description,swl,date_of_manufacture,manufacturer], ...],
 *                  'questions' => [key=>YES|NO], 'results' => [...]].
 */
function render_wire_rope_sling_template(array $c, array $def, string $qr): string
{
    $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8");
    $d = $c["details"] ?? [];
    $items = $d["items"] ?? [];
    $q  = $d["questions"] ?? [];
    $res = $d["results"] ?? [];

    $fmt = function ($date) {
        if (!$date) return "";
        $t = strtotime($date);
        return $t ? date("d/m/Y", $t) : (string) $date;
    };
    $yn = fn($key) => '<span class="yn">YES ' . (($q[$key] ?? "") === "YES" ? "&#10003;" : "&#9744;")
        . '</span> <span class="yn">NO ' . (($q[$key] ?? "") === "NO" ? "&#10003;" : "&#9744;") . '</span>';

    $header = '<table class="hdr"><tr>
        <td class="hdrmain">
            <img src="' . logo_data_uri() . '" class="logo">
            <div class="co">GLAJOE MULTI SERVICES LTD. <span class="rc">RC 1186853</span></div>
            <div class="addr">#3 Doxa Road, Off Peter Odili, Trans-Amadi, Port Harcourt, Rivers State.</div>
            <div class="contact">www.glajoeservices.com.ng &nbsp;|&nbsp; glajoeservices@gmail.com &nbsp;|&nbsp; ' . $h($def["tel"]) . '</div>
        </td>
        <td class="qr"><img src="' . $qr . '" width="84" height="84"><div class="qrcap">Scan to verify</div></td>
    </tr></table>';

    $kv = fn($label, $value) => '<tr><td class="k">' . $h($label) . '</td><td class="v">' . $h($value) . '</td></tr>';

    $top = '<table class="grid">'
        . '<tr><td class="k">' . $h($def["date_label"]) . '</td><td class="v">' . $h($fmt($c["inspection_date"] ?? null))
        . '</td><td class="k">Date of Report</td><td class="v">' . $h($fmt($res["date_of_report"] ?? null))
        . '</td><td class="k">Report Number</td><td class="v">' . $h($c["certNum"]) . '</td></tr></table>';

    $idblock = '<table class="grid">'
        . $kv("Name and Address of Client", $c["client"])
        . $kv($def["owner_label"], $c["equipment_owner"])
        . '</table>';

    // Item table (horizontal, unlike the stacked-block shackle layout)
    $cols = $def["item_columns"];
    $rows = "";
    $sn = 1;
    foreach ($items as $it) {
        $rows .= '<tr><td class="c">' . $sn++ . '</td>';
        foreach (array_keys($cols) as $ck) {
            $rows .= '<td' . ($ck === "qty" ? ' class="c"' : '') . '>' . $h($it[$ck] ?? "") . '</td>';
        }
        $rows .= '</tr>';
    }
    if ($rows === "") {
        $rows = '<tr><td colspan="' . (count($cols) + 1) . '" class="c">—</td></tr>';
    }
    $headCells = '<th>S/N</th>';
    foreach ($cols as $label) { $headCells .= '<th>' . $h($label) . '</th>'; }
    $itemTable = '<table class="items"><thead><tr>' . $headCells . '</tr></thead><tbody>' . $rows . '</tbody></table>';

    // Test information (third-party sling test certificate)
    $testInfo = '<table class="grid">'
        . $kv("Test Item", $res["test_item"] ?? "")
        . $kv("Test Manufacturer", $res["test_manufacturer"] ?? "")
        . $kv("Test Certificate No", $res["test_cert_no"] ?? "")
        . $kv("Test Date", $fmt($res["test_date"] ?? null))
        . '</table>';

    // Questions
    $qRows = "";
    foreach ($def["questions"] as $k => $question) {
        $qRows .= '<tr><td class="q">' . $h($question) . '</td><td class="a">' . $yn($k) . '</td></tr>';
    }
    $questions = '<table class="grid qtbl">' . $qRows . '</table>';

    // Results
    $results = '<table class="grid">'
        . $kv("Reference Standard", $c["reference_standard"])
        . $kv($def["result_fields"]["type_of_inspection"]["label"], $res["type_of_inspection"] ?? "")
        . $kv($def["result_fields"]["inspection_result"]["label"], $res["inspection_result"] ?? "")
        . '</table>';

    $declaration = '<div class="decl"><b>DECLARATION:</b> ' . $h($def["declaration"]) . '</div>'
        . '<div class="regs">' . $h($def["regs_footer"]) . '</div>';

    $sigValue = !empty($c["signature_img"])
        ? '<img src="' . $c["signature_img"] . '" class="sigimg">'
        : '____________________';
    $footerBlock = '<table class="grid">'
        . '<tr><td class="k">Name &amp; Qualifications of Inspector</td><td class="v">' . $h(trim(($c["inspector_name"] ?? "") . ($c["qualification"] ? " — " . $c["qualification"] : ""))) . '</td></tr>'
        . '<tr><td class="k">Signature</td><td class="v">' . $sigValue . '</td></tr>'
        . $kv($def["next_date_label"], $fmt($c["next_inspection_date"] ?? null))
        . $kv("Name & Position authenticating this report", $res["authenticator"] ?? "")
        . (!empty($c["stamp_img"]) ? '<tr><td class="k">Company Stamp</td><td class="v"><img src="' . $c["stamp_img"] . '" class="stampimg"></td></tr>' : '')
        . '</table>';

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
    .grid .k { background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:3px 5px; }
    .grid .v { border:0.5px solid #ccc; padding:3px 5px; }
    .sec { font-weight:bold; font-size:10px; margin:9px 0 0; color:#1a3c6e; }
    .items { width:100%; border-collapse:collapse; margin-top:6px; }
    .items th { border:0.5px solid #ccc; background:#1a3c6e; color:#fff; padding:3px 4px; font-size:8px; }
    .items td { border:0.5px solid #ccc; padding:3px 5px; font-size:8.5px; }
    .items td.c { text-align:center; }
    .qtbl .q { border:0.5px solid #ccc; padding:3px 5px; width:70%; }
    .qtbl .a { border:0.5px solid #ccc; padding:3px 5px; white-space:nowrap; }
    .yn { margin-right:10px; font-weight:bold; }
    .decl { margin-top:12px; font-size:8.5px; text-align:justify; }
    .regs { margin-top:6px; font-size:7.5px; color:#555; }
    .sigimg { max-height:36px; max-width:130px; }
    .stampimg { max-height:60px; max-width:90px; }
    ';

    $body = $header
        . '<div class="rtitle">' . $h($def["report_title"]) . '</div>'
        . '<div class="rcomp">' . $h($def["compliance"]) . '</div>'
        . $top . $idblock
        . '<div class="sec">Item(s)</div>' . $itemTable
        . '<div class="sec">Test Information</div>' . $testInfo
        . '<div class="sec">Examination</div>' . $questions
        . $results . $declaration . $footerBlock;

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>' . $body . '</body></html>';
}
