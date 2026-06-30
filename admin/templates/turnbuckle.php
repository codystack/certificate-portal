<?php
/**
 * Turnbuckle "Certificate of Thorough Examination" → HTML for Dompdf.
 * Single page: identity grid, repeatable item table, specification block,
 * declaration. $c['details'] = ['items' => [...rows], 'spec' => [...]].
 */
function render_turnbuckle_template(array $c, array $def, string $qr): string
{
    $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8");
    $d = $c["details"] ?? [];
    $items = $d["items"] ?? [];
    $spec = $d["spec"] ?? [];

    $fmt = function ($date) {
        if (!$date) return "";
        $t = strtotime($date);
        return $t ? date("d/m/Y", $t) : (string) $date;
    };

    $header = '<table class="hdr"><tr>
        <td class="hdrmain">
            <div class="co">GLAJOE MULTI SERVICES LTD. <span class="rc">RC 1186853</span></div>
            <div class="addr">#3 Doxa Road, Off Peter Odili, Trans-Amadi, Port Harcourt, Rivers State.</div>
            <div class="contact">www.glajoeservices.com.ng &nbsp;|&nbsp; glajoeservices@gmail.com &nbsp;|&nbsp; ' . $h($def["tel"]) . '</div>
        </td>
        <td class="qr"><img src="' . $qr . '" width="84" height="84"><div class="qrcap">Scan to verify</div></td>
    </tr></table>';

    $kv = fn($label, $value) => '<tr><td class="k">' . $h($label) . '</td><td class="v">' . $h($value) . '</td></tr>';

    $identity = '<table class="grid">'
        . $kv("Client", $c["client"])
        . $kv("Certificate Number", $c["certNum"])
        . $kv($def["owner_label"] ?? "Location", $c["equipment_owner"])
        . $kv("Examiner & Qualification", trim(($c["examiner"] ?? "") . ($c["qualification"] ? " — " . $c["qualification"] : "")))
        . $kv("Test Location", $c["test_location"])
        . $kv("Inspection Date", $fmt($c["inspection_date"] ?? null))
        . $kv("Next Inspection Date", $fmt($c["next_inspection_date"] ?? null))
        . '</table>';

    // Item table
    $rows = "";
    $sn = 1;
    foreach ($items as $it) {
        $rows .= '<tr>'
            . '<td class="c">' . $sn++ . '</td>'
            . '<td>' . $h($it["id_no"] ?? "") . '</td>'
            . '<td class="c">' . $h($it["qty"] ?? "") . '</td>'
            . '<td>' . $h($it["description"] ?? "") . '</td>'
            . '<td>' . $h($it["swl"] ?? "") . '</td>'
            . '<td>' . $h($it["size"] ?? "") . '</td>'
            . '<td>' . $h($it["make"] ?? "") . '</td>'
            . '<td>' . $h($it["remarks"] ?? "") . '</td>'
            . '</tr>';
    }
    if ($rows === "") {
        $rows = '<tr><td colspan="8" class="c">—</td></tr>';
    }
    $itemTable = '<table class="items"><thead><tr>'
        . '<th>S/N</th><th>ID No.</th><th>QTY</th><th>Items Description</th><th>SWL</th><th>Size</th><th>Make</th><th>Remarks</th>'
        . '</tr></thead><tbody>' . $rows . '</tbody></table>';

    $signoff = '<table class="sign"><tr>
        <td>Signature of Inspector: ____________________</td>
        <td>Name of Inspector: <b>' . $h($c["inspector_name"]) . '</b></td></tr></table>';

    // Specification block (two columns of key/value)
    $specRows = "";
    foreach ($def["spec_fields"] as $k => $meta) {
        $specRows .= $kv($meta["label"], $spec[$k] ?? "");
    }
    $specBlock = '<div class="sec">SPECIFICATION</div><table class="grid">' . $specRows . '</table>';

    $declaration = '<div class="decl"><b>DECLARATION:</b> ' . $h($def["declaration"]) . '</div>'
        . '<div class="regs">' . $h($def["regs_footer"]) . '</div>';

    $css = '
    @page { margin: 18px 22px; }
    body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:9.5px; }
    .hdr { width:100%; border-bottom:2px solid #c0392b; }
    .hdrmain { text-align:center; }
    .co { font-size:14px; font-weight:bold; color:#1a3c6e; }
    .rc { font-size:8px; color:#555; font-weight:normal; }
    .addr { font-size:8.5px; color:#444; }
    .contact { font-size:7.5px; color:#666; }
    .qr { width:96px; text-align:center; vertical-align:top; }
    .qrcap { font-size:6.5px; color:#555; }
    .rtitle { text-align:center; font-weight:bold; font-size:12px; letter-spacing:1px; margin:8px 0 1px; }
    .rcomp { text-align:center; font-size:7.5px; font-style:italic; color:#666; margin-bottom:5px; }
    .grid { width:100%; border-collapse:collapse; }
    .grid .k { width:33%; background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:3px 5px; }
    .grid .v { border:0.5px solid #ccc; padding:3px 5px; }
    .sec { font-weight:bold; font-size:10px; margin:10px 0 2px; color:#1a3c6e; }
    .items { width:100%; border-collapse:collapse; margin-top:8px; }
    .items th { border:0.5px solid #ccc; background:#1a3c6e; color:#fff; padding:3px 4px; font-size:8.5px; }
    .items td { border:0.5px solid #ccc; padding:3px 5px; font-size:9px; }
    .items td.c { text-align:center; }
    .sign { width:100%; margin-top:14px; }
    .sign td { width:50%; }
    .decl { margin-top:12px; font-size:8.5px; text-align:justify; }
    .regs { margin-top:6px; font-size:7.5px; color:#555; }
    ';

    $body = $header
        . '<div class="rtitle">' . $h($def["report_title"]) . '</div>'
        . '<div class="rcomp">' . $h($def["compliance"]) . '</div>'
        . $identity . $itemTable . $signoff . $specBlock . $declaration;

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>' . $body . '</body></html>';
}
