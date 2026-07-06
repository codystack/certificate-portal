<?php
/**
 * Drill Collar Inspection Report → HTML for Dompdf (A3 landscape tally sheet).
 * $c['details'] = ['spec' => [...], 'rows' => [ [colkey => val], ... ]].
 */
function render_drill_collar_template(array $c, array $def, string $qr): string
{
    $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8");
    $d = $c["details"] ?? [];
    $spec = $d["spec"] ?? [];
    $rows = $d["rows"] ?? [];
    $groups = $def["column_groups"] ?? [];

    $fmt = function ($date) {
        if (!$date) return "";
        $t = strtotime($date);
        return $t ? date("d/m/Y", $t) : (string) $date;
    };

    $header = '<table class="hdr"><tr>
        <td class="hdrmain">
            <img src="' . logo_data_uri() . '" class="logo">
            <div class="co">GLAJOE MULTI SERVICES LTD. <span class="rc">RC 1186853</span></div>
            <div class="addr">#3 Doxa Road, Off Peter Odili, Trans-Amadi, Port Harcourt, Rivers State.</div>
            <div class="contact">www.glajoeservices.com.ng &nbsp;|&nbsp; glajoeservices@gmail.com &nbsp;|&nbsp; ' . $h($def["tel"]) . '</div>
        </td>
        <td class="qr"><img src="' . $qr . '" width="74" height="74"><div class="qrcap">Scan to verify</div></td>
    </tr></table>';

    // Top identity strip
    $cell = fn($l, $v) => '<td class="k">' . $h($l) . '</td><td class="v">' . $h($v) . '</td>';
    $idStrip = '<table class="strip"><tr>'
        . $cell("Customer", $c["client"])
        . $cell($def["location_label"] ?? "Location", $c["test_location"])
        . $cell($def["owner_label"] ?? "Rig", $c["equipment_owner"])
        . '</tr><tr>'
        . $cell("Dept", $spec["dept"] ?? "")
        . $cell("Report No", $c["certNum"])
        . $cell($def["date_label"] ?? "Date", $fmt($c["inspection_date"] ?? null))
        . '</tr></table>';

    // String spec strip (skip dept and the repair/classification counts, shown separately)
    $classKeys = ["repair_pin", "repair_box", "repair_pin_box", "repair_pin_refaced", "repair_bent", "premium_qty", "class2_qty", "scrap_qty", "total_qty"];
    $specCells = "";
    foreach ($def["spec_fields"] as $k => $meta) {
        if ($k === "dept" || in_array($k, $classKeys, true)) continue;
        $specCells .= '<td class="k">' . $h($meta["label"]) . '</td><td class="v">' . $h($spec[$k] ?? "") . '</td>';
    }
    $specStrip = '<table class="strip strip2"><tr>' . $specCells . '</tr></table>';

    // Grouped table header (two rows)
    $top = "";
    $bottom = "";
    foreach ($groups as $g) {
        $count = count($g["cols"]);
        if ($g["group"] === "") {
            foreach ($g["cols"] as $label) {
                $top .= '<th rowspan="2">' . $h($label) . '</th>';
            }
        } else {
            $top .= '<th colspan="' . $count . '">' . $h($g["group"]) . '</th>';
            foreach ($g["cols"] as $label) {
                $bottom .= '<th>' . $h($label) . '</th>';
            }
        }
    }
    $thead = '<thead><tr>' . $top . '</tr><tr>' . $bottom . '</tr></thead>';

    // Data rows
    $cols = tally_columns($def);
    $body = "";
    foreach ($rows as $r) {
        $body .= '<tr>';
        foreach (array_keys($cols) as $ck) {
            $body .= '<td>' . $h($r[$ck] ?? "") . '</td>';
        }
        $body .= '</tr>';
    }
    if ($body === "") {
        $body = '<tr><td colspan="' . count($cols) . '">No drill collars recorded.</td></tr>';
    }
    $table = '<table class="tally">' . $thead . '<tbody>' . $body . '</tbody></table>';

    // Repair-remark counts + classification summary (counts), one combined
    // list — a wrapper table with two side-by-side inner tables is unreliable
    // in dompdf (the second inner table can overlap/hide the first one's
    // value column), so this is kept as a single vertical list instead.
    $classCells = "";
    foreach (["repair_pin" => "Join with Pin for Repair", "repair_box" => "Joint with Box for Repair", "repair_pin_box" => "Joint with Pin & Box for Repair", "repair_pin_refaced" => "Pin Refaced / Rebeveled", "repair_bent" => "Bent Pipes",
              "premium_qty" => "Premium", "class2_qty" => "Class 2", "scrap_qty" => "Scrap", "total_qty" => "Total"] as $k => $label) {
        $classCells .= '<tr><td class="k">' . $h($label) . '</td><td class="v">' . $h($spec[$k] ?? "") . '</td></tr>';
    }
    $classification = '<table class="grid classtbl">' . $classCells . '</table>';

    $legend = '<div class="legend">
        <b>Classification Codes:</b> SD=Seal Damage, TD=Thread Damage, BB=Belled Box, ST=Stretched Thread, PT=Pitted Thread, PS=Pitted Seal, GT=Galled Thread, WO=Wash Out, CRK=Crack, STJ=Short Tool Joint, HND=Hard Band Damage, RF=Refaced, ACC=Acceptable, NA=Not Applicable, W=Worn Hardband
    </div>';

    $sigCell = !empty($c["signature_img"])
        ? '<img src="' . $c["signature_img"] . '" class="sigimg"><div class="sigline">Signature of Inspector</div>'
        : 'Signature of Inspector: ____________________';
    $stampCell = !empty($c["stamp_img"]) ? '<img src="' . $c["stamp_img"] . '" class="stampimg">' : '';
    $signoff = '<table class="sign"><tr>
        <td>' . $sigCell . '</td>
        <td>Name of Inspector: <b>' . $h($c["inspector_name"] ?? "") . '</b></td>
        <td class="stampcell">' . $stampCell . '</td></tr></table>';

    $css = '
    @page { margin: 12px 14px; }
    body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:8px; }
    .hdr { width:100%; border-bottom:2px solid #c0392b; }
    .hdrmain { text-align:center; }
    .logo { height:34px; margin-bottom:2px; }
    .co { font-size:13px; font-weight:bold; color:#1a3c6e; }
    .rc { font-size:7px; color:#555; font-weight:normal; }
    .addr { font-size:8px; color:#444; }
    .contact { font-size:7px; color:#666; }
    .qr { width:84px; text-align:center; vertical-align:top; }
    .qrcap { font-size:6px; color:#555; }
    .rtitle { text-align:center; font-weight:bold; font-size:13px; letter-spacing:1px; margin:6px 0 4px; }
    .strip { width:100%; border-collapse:collapse; margin-bottom:3px; }
    .strip .k { background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:2px 4px; white-space:nowrap; font-size:8px; }
    .strip .v { border:0.5px solid #ccc; padding:2px 4px; font-size:8px; }
    .strip2 .k, .strip2 .v { font-size:7px; }
    .tally { width:100%; border-collapse:collapse; margin-top:4px; }
    .tally th { border:0.5px solid #999; background:#1a3c6e; color:#fff; padding:2px 1px; font-size:6px; text-align:center; }
    .tally td { border:0.5px solid #bbb; padding:1px 2px; font-size:6.5px; text-align:center; white-space:nowrap; }
    .grid { border-collapse:collapse; }
    .grid .k { background:#f3f5f8; font-weight:bold; border:0.5px solid #ccc; padding:2px 5px; }
    .grid .v { border:0.5px solid #ccc; padding:2px 5px; }
    .classtbl { width:340px; margin-top:8px; }
    .classtbl .k { width:78%; }
    .classtbl .v { width:22%; }
    .legend { margin-top:8px; font-size:6.5px; color:#444; }
    .sign { width:100%; margin-top:10px; }
    .sign td { width:33%; vertical-align:bottom; font-size:8px; }
    .sigimg { max-height:32px; max-width:120px; }
    .sigline { font-size:7px; border-top:1px solid #444; padding-top:2px; width:120px; }
    .stampcell { text-align:center; }
    .stampimg { max-height:56px; max-width:84px; }
    ';

    $body_html = $header
        . '<div class="rtitle">' . $h($def["report_title"]) . '</div>'
        . $idStrip . $specStrip . $table . $classification . $legend . $signoff;

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>' . $body_html . '</body></html>';
}
