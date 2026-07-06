<?php
$page = "NewInspection";
include __DIR__ . "/components/head.php";
require_once __DIR__ . "/lib/inspection_types.php";

$prefill = [];
$existing = null;
$editId = (int) ($_GET["id"] ?? 0);
$mode = "create";

if ($editId > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM certificate WHERE id = ? AND generated = 1");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$existing) {
        flash("Certificate not found.", "error");
        redirect("certificates.php");
    }
    $mode = "edit";
    $typeKey = $existing["type"];
    $def = inspection_type($typeKey);
    if (!$def) {
        flash("Unknown inspection type.", "error");
        redirect("certificates.php");
    }
    $layout = $def["layout"] ?? "checklist";
    $details = json_decode($existing["details"] ?? "", true) ?: [];

    $prefill = [
        "client" => $existing["client"],
        "certNum" => $existing["certNum"],
        "equipment_owner" => $existing["equipment_owner"],
        "examiner" => $existing["examiner"],
        "qualification" => $existing["qualification"],
        "test_location" => $existing["test_location"],
        "reference_standard" => $existing["reference_standard"],
        "inspector_name" => $existing["inspector_name"],
        "inspection_date" => $existing["inspection_date"],
        "next_inspection_date" => $existing["next_inspection_date"],
        "defects" => $existing["defects"],
        "status" => $existing["status"],
    ];
    if ($layout === "checklist") {
        $prefill["eq"] = $details["equipment"] ?? [];
        $prefill["wr"] = $details["wire_rope"] ?? [];
        $prefill["cl"] = $details["checklist"] ?? [];
        $prefill["hw"] = $details["hoisting_wire"] ?? [];
        $prefill["loadtest"] = $details["load_test"] ?? [];
        $prefill["reeving"] = $details["reeving"] ?? [];
        $prefill["wrreport"] = $details["wire_rope_report"] ?? [];
    } elseif ($layout === "items") {
        $prefill["items"] = $details["items"] ?? [];
        $prefill["spec"] = $details["spec"] ?? [];
    } elseif ($layout === "loler") {
        if (!empty($def["multi_equipment"])) {
            $prefill["items"] = $details["items"] ?? [];
        } else {
            $prefill["eq"] = $details["equipment"] ?? [];
        }
        $prefill["q"] = $details["questions"] ?? [];
        $prefill["res"] = $details["results"] ?? [];
    } elseif ($layout === "tally") {
        $prefill["spec"] = $details["spec"] ?? [];
        $prefill["items"] = $details["rows"] ?? [];
    }
} else {
    $typeKey = preg_replace("/[^a-z0-9_]/", "", strtolower($_GET["type"] ?? "winch"));
    $def = inspection_type($typeKey);
    if (!$def) {
        flash("Unknown inspection type.", "error");
        redirect("certificates.php");
    }
    $layout = $def["layout"] ?? "checklist";
}

include __DIR__ . "/components/sidebar.php";

$pfval = function (string $name) use ($prefill) {
    if (preg_match('/^(\w+)\[(\w+)\]$/', $name, $m)) {
        return $prefill[$m[1]][$m[2]] ?? "";
    }
    return $prefill[$name] ?? "";
};
$field = function (string $name, string $label, string $type = "text", string $fallback = "") use ($pfval) {
    $val = $pfval($name);
    if ($val === "" || $val === null) { $val = $fallback; }
    return '<div class="col-md-6 mb-3"><label class="form-label">' . e($label) . '</label>'
        . '<input type="' . e($type) . '" name="' . e($name) . '" class="form-control" value="' . e((string) $val) . '"></div>';
};
/** A repeatable-rows table section (Load Test / Reeving / Wire Rope Report),
 * driven entirely by a def column list. `$postKey` doubles as the POST field
 * prefix and the DOM id prefix the shared add/remove JS hooks into. */
$repeatSection = function (string $title, string $postKey, array $columns, array $rowsData): string {
    if (!$rowsData) { $rowsData = [[]]; }
    $head = '<th style="width:32px;">#</th>';
    foreach ($columns as $label) { $head .= '<th>' . e($label) . '</th>'; }
    $head .= '<th style="width:32px;"></th>';

    $body = "";
    foreach ($rowsData as $i => $rowVal) {
        $body .= '<tr class="item-row"><td class="sn">' . ($i + 1) . '</td>';
        foreach (array_keys($columns) as $ck) {
            $body .= '<td><input type="text" name="' . e($postKey) . '[' . $i . '][' . e($ck) . ']" class="form-control form-control-sm" value="' . e($rowVal[$ck] ?? "") . '"></td>';
        }
        $body .= '<td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td></tr>';
    }

    $tplRow = '<tr class="item-row"><td class="sn"></td>';
    foreach (array_keys($columns) as $ck) {
        $tplRow .= '<td><input type="text" name="' . e($postKey) . '[__i__][' . e($ck) . ']" class="form-control form-control-sm"></td>';
    }
    $tplRow .= '<td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td></tr>';

    return '<div class="card">'
        . '<div class="card-header d-flex justify-content-between align-items-center">'
        . '<h4 class="card-header-title">' . e($title) . '</h4>'
        . '<button type="button" class="btn btn-sm btn-white add-row-btn" data-target="' . e($postKey) . '"><i class="fe fe-plus"></i> Add Row</button>'
        . '</div>'
        . '<div class="card-body table-responsive">'
        . '<table class="table table-sm align-middle"><thead><tr>' . $head . '</tr></thead>'
        . '<tbody id="' . e($postKey) . 'Body" data-repeat-group="' . e($postKey) . '">' . $body . '</tbody></table>'
        . '</div></div>'
        . '<template id="' . e($postKey) . 'RowTpl">' . $tplRow . '</template>';
};
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle"><?php echo $mode === "edit" ? "Edit certificate" : "Generate certificate"; ?></h6>
                        <h1 class="header-title"><?php echo $mode === "edit" ? "Edit " : "New "; echo e($def["label"]); ?></h1>
                    </div>
                    <div class="col-auto"><a href="certificates.php" class="btn btn-white">Back</a></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form id="inspForm" enctype="multipart/form-data">
            <input type="hidden" name="type" value="<?php echo e($typeKey); ?>">
            <?php if ($mode === "edit"): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editId; ?>">
            <?php endif; ?>

            <!-- Identity -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Certificate Details</h4></div>
                <div class="card-body"><div class="row">
                    <?php
                    echo $field("client", "Client");
                    echo $field("certNum", "Certificate Number");
                    echo $field("equipment_owner", $def["owner_label"] ?? "Equipment Owner / Address");
                    echo $field("examiner", "Examiner");
                    echo $field("qualification", "Qualification");
                    echo $field("test_location", $def["location_label"] ?? "Test Location");
                    echo $field("reference_standard", "Reference Standard", "text", $def["reference_standard"] ?? "");
                    echo $field("inspector_name", "Inspector Name");
                    echo $field("inspection_date", $def["date_label"] ?? "Inspection Date", "date");
                    echo $field("next_inspection_date", $def["next_date_label"] ?? "Next Inspection Date", "date");
                    $curStatus = $prefill["status"] ?? "Active";
                    ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?php echo $curStatus === "Active" ? "selected" : ""; ?>>Active</option>
                            <option value="Expired" <?php echo $curStatus === "Expired" ? "selected" : ""; ?>>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Inspector Signature <span class="text-body-secondary small">(image<?php echo $mode === "edit" ? ", leave empty to keep current" : ", optional"; ?>)</span></label>
                        <?php if ($mode === "edit" && !empty($existing["inspector_signature"])): ?>
                        <div class="mb-2"><img src="<?php echo e($existing["inspector_signature"]); ?>" style="max-height:50px;"></div>
                        <?php endif; ?>
                        <input type="file" name="inspector_signature" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Stamp <span class="text-body-secondary small">(image<?php echo $mode === "edit" ? ", leave empty to keep current" : ", optional"; ?>)</span></label>
                        <?php if ($mode === "edit" && !empty($existing["company_stamp"])): ?>
                        <div class="mb-2"><img src="<?php echo e($existing["company_stamp"]); ?>" style="max-height:50px;"></div>
                        <?php endif; ?>
                        <input type="file" name="company_stamp" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                </div></div>
            </div>

            <?php if ($layout === "checklist"):
                $checklistOptions = $def["checklist_options"] ?? ["SAT", "UNSAT", "N/A"];
                $defaultResult = $checklistOptions[0] ?? "SAT";
            ?>
            <!-- Equipment description -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title"><?php echo e($def["equipment_section_label"] ?? "Equipment Description"); ?></h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["equipment_fields"] as $k => $label) echo $field("eq[$k]", $label); ?>
                </div></div>
            </div>
            <?php if (!empty($def["subblock_fields"])): ?>
            <!-- Sub-block -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title"><?php echo e($def["subblock_label"]); ?></h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["subblock_fields"] as $k => $label):
                        if (in_array($k, $def["subblock_textarea_fields"] ?? [], true)): ?>
                    <div class="col-12 mb-3">
                        <label class="form-label"><?php echo e($label); ?></label>
                        <textarea name="wr[<?php echo e($k); ?>]" class="form-control" rows="3"><?php echo e($pfval("wr[$k]")); ?></textarea>
                    </div>
                    <?php else: echo $field("wr[$k]", $label); endif; endforeach; ?>
                </div></div>
            </div>
            <?php endif; ?>
            <!-- Defects -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title"><?php echo e($def["defects_heading"] ?? "Defects"); ?></h4></div>
                <div class="card-body">
                    <label class="form-label"><?php echo e($def["defects_prompt"] ?? "Identification of any defect (if none, leave blank — defaults to NONE)"); ?></label>
                    <textarea name="defects" class="form-control" rows="2"><?php echo e($prefill["defects"] ?? ""); ?></textarea>
                </div>
            </div>
            <!-- Checklist -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-header-title">Inspection Checklist</h4>
                    <?php if (!empty($def["checklist_legend"])): ?><span class="text-body-secondary small"><?php echo e($def["checklist_legend"]); ?></span><?php endif; ?>
                </div>
                <div class="card-body">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Component</th><?php foreach ($checklistOptions as $opt): ?><th class="text-center"><?php echo e($opt); ?></th><?php endforeach; ?><th>Comment</th></tr></thead>
                        <tbody>
                        <?php foreach ($def["checklist"] as $section => $items): ?>
                            <tr class="table-active"><td colspan="<?php echo count($checklistOptions) + 2; ?>" class="fw-bold"><?php echo e($section); ?></td></tr>
                            <?php foreach ($items as $item):
                                $key = checklist_key($item);
                                $curResult = $prefill["cl"][$key]["result"] ?? $defaultResult;
                                $curComment = $prefill["cl"][$key]["comment"] ?? "";
                            ?>
                            <tr>
                                <td><?php echo e($item); ?></td>
                                <?php foreach ($checklistOptions as $opt): ?>
                                <td class="text-center"><input type="radio" name="cl[<?php echo $key; ?>][result]" value="<?php echo e($opt); ?>" <?php echo $opt === $curResult ? "checked" : ""; ?>></td>
                                <?php endforeach; ?>
                                <td><input type="text" name="cl[<?php echo $key; ?>][comment]" class="form-control form-control-sm" value="<?php echo e($curComment); ?>"></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($def["hoisting_wire_fields"])): ?>
            <!-- Hoisting Wire Details -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Hoisting Wire Details</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["hoisting_wire_fields"] as $k => $label) echo $field("hw[$k]", $label); ?>
                </div></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($def["load_test_columns"])): ?>
            <?php echo $repeatSection("Load Test Details", "loadtest", $def["load_test_columns"], $prefill["loadtest"] ?? []); ?>
            <?php endif; ?>
            <?php if (!empty($def["reeving_columns"])): ?>
            <?php echo $repeatSection("Blocks & Reeving Report", "reeving", $def["reeving_columns"], $prefill["reeving"] ?? []); ?>
            <?php endif; ?>
            <?php if (!empty($def["wire_rope_report_columns"])): ?>
            <?php echo $repeatSection("Wire Rope Inspection Report", "wrreport", $def["wire_rope_report_columns"], $prefill["wrreport"] ?? []); ?>
            <?php endif; ?>

            <?php elseif ($layout === "items"):
                $rowsData = $prefill["items"] ?? [];
                if (!$rowsData) { $rowsData = [[]]; }
            ?>
            <!-- Item table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Items</h4>
                    <button type="button" class="btn btn-sm btn-white" id="addItem"><i class="fe fe-plus"></i> Add Item</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle" id="itemsTable">
                        <thead><tr>
                            <th style="width:32px;">#</th>
                            <?php foreach ($def["item_columns"] as $label): ?><th><?php echo e($label); ?></th><?php endforeach; ?>
                            <th style="width:32px;"></th>
                        </tr></thead>
                        <tbody id="itemsBody">
                            <?php foreach ($rowsData as $i => $rowVal): ?>
                            <tr class="item-row">
                                <td class="sn"><?php echo $i + 1; ?></td>
                                <?php foreach ($def["item_columns"] as $ck => $label): ?>
                                <td><input type="text" name="items[<?php echo $i; ?>][<?php echo $ck; ?>]" class="form-control form-control-sm" value="<?php echo e($rowVal[$ck] ?? ""); ?>"></td>
                                <?php endforeach; ?>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Specification -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Specification</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["spec_fields"] as $k => $meta) echo $field("spec[$k]", $meta["label"], "text", $meta["default"] ?? ""); ?>
                </div></div>
            </div>

            <template id="itemRowTpl">
                <tr class="item-row">
                    <td class="sn"></td>
                    <?php foreach ($def["item_columns"] as $ck => $label): ?>
                    <td><input type="text" name="items[__i__][<?php echo $ck; ?>]" class="form-control form-control-sm"></td>
                    <?php endforeach; ?>
                    <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                </tr>
            </template>

            <?php elseif ($layout === "loler"): ?>
            <?php if (!empty($def["multi_equipment"])):
                $rowsData = $prefill["items"] ?? [];
                if (!$rowsData) { $rowsData = [[]]; }
            ?>
            <!-- Repeatable equipment items -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Description &amp; Identification of Equipment</h4>
                    <button type="button" class="btn btn-sm btn-white" id="addItem"><i class="fe fe-plus"></i> Add Item</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle" id="itemsTable">
                        <thead><tr>
                            <th style="width:32px;">#</th>
                            <?php foreach ($def["item_columns"] as $label): ?><th><?php echo e($label); ?></th><?php endforeach; ?>
                            <th style="width:32px;"></th>
                        </tr></thead>
                        <tbody id="itemsBody">
                            <?php foreach ($rowsData as $i => $rowVal): ?>
                            <tr class="item-row">
                                <td class="sn"><?php echo $i + 1; ?></td>
                                <?php foreach ($def["item_columns"] as $ck => $label): ?>
                                <td><input type="text" name="items[<?php echo $i; ?>][<?php echo $ck; ?>]" class="form-control form-control-sm" value="<?php echo e($rowVal[$ck] ?? ""); ?>"></td>
                                <?php endforeach; ?>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <template id="itemRowTpl">
                <tr class="item-row">
                    <td class="sn"></td>
                    <?php foreach ($def["item_columns"] as $ck => $label): ?>
                    <td><input type="text" name="items[__i__][<?php echo $ck; ?>]" class="form-control form-control-sm"></td>
                    <?php endforeach; ?>
                    <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                </tr>
            </template>
            <?php else: ?>
            <!-- Equipment -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Description &amp; Identification of Equipment</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["equipment_fields"] as $k => $label) echo $field("eq[$k]", $label); ?>
                </div></div>
            </div>
            <?php endif; ?>
            <!-- Report details -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Report Details</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["result_fields"] as $k => $meta) echo $field("res[$k]", $meta["label"], $meta["type"] ?? "text", $meta["default"] ?? ""); ?>
                </div></div>
            </div>
            <!-- Questions -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Examination</h4></div>
                <div class="card-body">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Question</th><th class="text-center" style="width:90px;">YES</th><th class="text-center" style="width:90px;">NO</th></tr></thead>
                        <tbody>
                        <?php foreach ($def["questions"] as $qk => $question): $qVal = $prefill["q"][$qk] ?? "YES"; ?>
                            <tr>
                                <td><?php echo e($question); ?></td>
                                <td class="text-center"><input type="radio" name="q[<?php echo $qk; ?>]" value="YES" <?php echo $qVal === "YES" ? "checked" : ""; ?>></td>
                                <td class="text-center"><input type="radio" name="q[<?php echo $qk; ?>]" value="NO" <?php echo $qVal === "NO" ? "checked" : ""; ?>></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($layout === "tally"):
                $cols = tally_columns($def);
                $rowsData = $prefill["items"] ?? [];
                if (!$rowsData) { $rowsData = [[]]; }
            ?>
            <!-- String / spec details -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">String Details</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["spec_fields"] as $k => $meta) echo $field("spec[$k]", $meta["label"], "text", $meta["default"] ?? ""); ?>
                </div></div>
            </div>
            <!-- Wide tally grid -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Pipe Tally <span class="text-body-secondary small">(scroll sideways &rarr;)</span></h4>
                    <button type="button" class="btn btn-sm btn-white" id="addItem"><i class="fe fe-plus"></i> Add Pipe</button>
                </div>
                <div class="card-body table-responsive" style="max-height:480px;">
                    <table class="table table-sm table-bordered align-middle" style="white-space:nowrap;">
                        <thead>
                            <tr>
                                <?php foreach ($def["column_groups"] as $g):
                                    $count = count($g["cols"]);
                                    if ($g["group"] === ""):
                                        foreach ($g["cols"] as $label): ?>
                                            <th rowspan="2" class="small align-middle"><?php echo e($label); ?></th>
                                        <?php endforeach;
                                    else: ?>
                                        <th colspan="<?php echo $count; ?>" class="text-center small"><?php echo e($g["group"]); ?></th>
                                    <?php endif;
                                endforeach; ?>
                                <th rowspan="2"></th>
                            </tr>
                            <tr>
                                <?php foreach ($def["column_groups"] as $g):
                                    if ($g["group"] === "") continue;
                                    foreach ($g["cols"] as $label): ?>
                                        <th class="small" style="font-size:10px;"><?php echo e($label); ?></th>
                                    <?php endforeach;
                                endforeach; ?>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php foreach ($rowsData as $i => $rowVal): ?>
                            <tr class="item-row">
                                <?php foreach (array_keys($cols) as $ck): ?>
                                <td><input type="text" name="items[<?php echo $i; ?>][<?php echo $ck; ?>]" class="form-control form-control-sm" style="min-width:64px;" value="<?php echo e($rowVal[$ck] ?? ""); ?>"></td>
                                <?php endforeach; ?>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <template id="itemRowTpl">
                <tr class="item-row">
                    <?php foreach (array_keys($cols) as $ck): ?>
                    <td><input type="text" name="items[__i__][<?php echo $ck; ?>]" class="form-control form-control-sm" style="min-width:64px;"></td>
                    <?php endforeach; ?>
                    <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                </tr>
            </template>
            <?php endif; ?>

            <div class="pt-4 pb-5">
                <button type="submit" class="btn btn-primary btn-lg" id="inspSubmit"><?php echo $mode === "edit" ? "Save Changes" : "Generate Certificate"; ?></button>
                <a href="certificates.php" class="btn btn-link text-body-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
// Repeatable items table (turnbuckle / "items" layout)
(function () {
    const body = document.getElementById('itemsBody');
    const tpl  = document.getElementById('itemRowTpl');
    const addBtn = document.getElementById('addItem');
    if (!body || !tpl || !addBtn) return;

    function renumber() {
        body.querySelectorAll('.item-row').forEach((tr, i) => {
            const sn = tr.querySelector('.sn');
            if (sn) sn.textContent = i + 1;
            tr.querySelectorAll('input').forEach(inp => {
                inp.name = inp.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });
        });
    }
    addBtn.addEventListener('click', () => {
        const html = tpl.innerHTML.replace(/__i__/g, body.querySelectorAll('.item-row').length);
        body.insertAdjacentHTML('beforeend', html);
        renumber();
    });
    body.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const rows = body.querySelectorAll('.item-row');
            if (rows.length > 1) { e.target.closest('.item-row').remove(); renumber(); }
        }
    });
})();

// Repeatable-row tables identified by [data-repeat-group] (Pedestal Crane's
// Load Test / Reeving / Wire Rope Report — any number can coexist on one page).
document.querySelectorAll('[data-repeat-group]').forEach((body) => {
    const group = body.dataset.repeatGroup;
    const tpl = document.getElementById(group + 'RowTpl');
    const addBtn = document.querySelector('.add-row-btn[data-target="' + group + '"]');
    if (!tpl || !addBtn) return;

    function renumber() {
        body.querySelectorAll('.item-row').forEach((tr, i) => {
            const sn = tr.querySelector('.sn');
            if (sn) sn.textContent = i + 1;
            tr.querySelectorAll('input').forEach(inp => {
                inp.name = inp.name.replace(new RegExp(group + '\\[\\d+\\]'), group + '[' + i + ']');
            });
        });
    }
    addBtn.addEventListener('click', () => {
        const html = tpl.innerHTML.replace(/__i__/g, body.querySelectorAll('.item-row').length);
        body.insertAdjacentHTML('beforeend', html);
        renumber();
    });
    body.addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const rows = body.querySelectorAll('.item-row');
            if (rows.length > 1) { e.target.closest('.item-row').remove(); renumber(); }
        }
    });
});

// Submit (shared)
const INSP_MODE = <?php echo json_encode($mode); ?>;
document.getElementById('inspForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('inspSubmit');
    const busyLabel = INSP_MODE === 'edit' ? 'Saving...' : 'Generating PDF...';
    const idleLabel = INSP_MODE === 'edit' ? 'Save Changes' : 'Generate Certificate';
    btn.disabled = true; btn.textContent = busyLabel;
    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);
    const url = INSP_MODE === 'edit' ? 'auth/update/inspection.php' : 'auth/create/inspection.php';
    try {
        const res = await fetch(url, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => window.location.href = data.redirect, 1100);
        } else {
            notyf.error(data.message);
            btn.disabled = false; btn.textContent = idleLabel;
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false; btn.textContent = idleLabel;
    }
});
</script>
