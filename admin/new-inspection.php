<?php
$page = "NewInspection";
include __DIR__ . "/components/head.php";
require_once __DIR__ . "/lib/inspection_types.php";

$typeKey = preg_replace("/[^a-z0-9_]/", "", strtolower($_GET["type"] ?? "winch"));
$def = inspection_type($typeKey);
if (!$def) {
    flash("Unknown inspection type.", "error");
    redirect("certificates.php");
}
$layout = $def["layout"] ?? "checklist";
include __DIR__ . "/components/sidebar.php";

$field = function (string $name, string $label, string $type = "text", string $val = "") {
    return '<div class="col-md-6 mb-3"><label class="form-label">' . e($label) . '</label>'
        . '<input type="' . e($type) . '" name="' . e($name) . '" class="form-control" value="' . e($val) . '"></div>';
};
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Generate certificate</h6>
                        <h1 class="header-title">New <?php echo e($def["label"]); ?></h1>
                    </div>
                    <div class="col-auto"><a href="certificates.php" class="btn btn-white">Back</a></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form id="inspForm">
            <input type="hidden" name="type" value="<?php echo e($typeKey); ?>">

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
                    ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select"><option value="Active">Active</option><option value="Expired">Expired</option></select>
                    </div>
                </div></div>
            </div>

            <?php if ($layout === "checklist"): ?>
            <!-- Equipment description -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Equipment Description</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["equipment_fields"] as $k => $label) echo $field("eq[$k]", $label); ?>
                </div></div>
            </div>
            <!-- Sub-block -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title"><?php echo e($def["subblock_label"]); ?></h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["subblock_fields"] as $k => $label) echo $field("wr[$k]", $label); ?>
                </div></div>
            </div>
            <!-- Defects -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Defects</h4></div>
                <div class="card-body">
                    <label class="form-label">Identification of any defect (if none, leave blank — defaults to NONE)</label>
                    <textarea name="defects" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <!-- Checklist -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Inspection Checklist</h4></div>
                <div class="card-body">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Component</th><th class="text-center">SAT</th><th class="text-center">UNSAT</th><th class="text-center">N/A</th><th>Comment</th></tr></thead>
                        <tbody>
                        <?php foreach ($def["checklist"] as $section => $items): ?>
                            <tr class="table-active"><td colspan="5" class="fw-bold"><?php echo e($section); ?></td></tr>
                            <?php foreach ($items as $item): $key = checklist_key($item); ?>
                            <tr>
                                <td><?php echo e($item); ?></td>
                                <?php foreach (["SAT", "UNSAT", "N/A"] as $opt): ?>
                                <td class="text-center"><input type="radio" name="cl[<?php echo $key; ?>][result]" value="<?php echo $opt; ?>" <?php echo $opt === "SAT" ? "checked" : ""; ?>></td>
                                <?php endforeach; ?>
                                <td><input type="text" name="cl[<?php echo $key; ?>][comment]" class="form-control form-control-sm"></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($layout === "items"): ?>
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
                            <tr class="item-row">
                                <td class="sn">1</td>
                                <?php foreach ($def["item_columns"] as $ck => $label): ?>
                                <td><input type="text" name="items[0][<?php echo $ck; ?>]" class="form-control form-control-sm"></td>
                                <?php endforeach; ?>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                            </tr>
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
            <!-- Equipment -->
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Description &amp; Identification of Equipment</h4></div>
                <div class="card-body"><div class="row">
                    <?php foreach ($def["equipment_fields"] as $k => $label) echo $field("eq[$k]", $label); ?>
                </div></div>
            </div>
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
                        <?php foreach ($def["questions"] as $qk => $question): ?>
                            <tr>
                                <td><?php echo e($question); ?></td>
                                <td class="text-center"><input type="radio" name="q[<?php echo $qk; ?>]" value="YES" checked></td>
                                <td class="text-center"><input type="radio" name="q[<?php echo $qk; ?>]" value="NO"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($layout === "tally"):
                $cols = tally_columns($def); ?>
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
                            <tr class="item-row">
                                <?php foreach (array_keys($cols) as $ck): ?>
                                <td><input type="text" name="items[0][<?php echo $ck; ?>]" class="form-control form-control-sm" style="min-width:64px;"></td>
                                <?php endforeach; ?>
                                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item"><i class="fe fe-x"></i></button></td>
                            </tr>
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

            <div class="mb-5">
                <button type="submit" class="btn btn-primary btn-lg" id="inspSubmit">Generate Certificate</button>
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

// Submit (shared)
document.getElementById('inspForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('inspSubmit');
    btn.disabled = true; btn.textContent = 'Generating PDF...';
    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);
    try {
        const res = await fetch('auth/create/inspection.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => window.location.href = data.redirect, 1100);
        } else {
            notyf.error(data.message);
            btn.disabled = false; btn.textContent = 'Generate Certificate';
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false; btn.textContent = 'Generate Certificate';
    }
});
</script>
