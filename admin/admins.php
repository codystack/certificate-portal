<?php
$page = "Admins";
include __DIR__ . "/components/head.php";

// Super Admin only.
if (($_SESSION["designation"] ?? "") !== "Super Admin") {
    flash("Super Admin access required.", "error");
    redirect("dashboard.php");
}
include __DIR__ . "/components/sidebar.php";

$rows = mysqli_query($conn, "SELECT id, email, firstName, lastName, position, status, date FROM admin ORDER BY date DESC");
$admins = $rows ? mysqli_fetch_all($rows, MYSQLI_ASSOC) : [];
$myId = (int) $_SESSION["admin_id"];
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Access</h6>
                        <h1 class="header-title">Administrators</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header"><h4 class="card-header-title">All Admins</h4></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap card-table">
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                            <tbody class="list fs-base">
                                <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td><?php echo e($a["firstName"] . " " . $a["lastName"]); ?></td>
                                    <td><?php echo e($a["email"]); ?></td>
                                    <td><?php echo e($a["position"]); ?></td>
                                    <td><span class="badge bg-<?php echo $a["status"] === "Active" ? "success" : "secondary"; ?>-subtle text-<?php echo $a["status"] === "Active" ? "success" : "secondary"; ?>"><?php echo e($a["status"]); ?></span></td>
                                    <td class="text-end">
                                        <?php if ((int) $a["id"] !== $myId): ?>
                                        <button type="button" class="btn btn-sm btn-danger delete-admin"
                                            data-id="<?php echo (int) $a["id"]; ?>"
                                            data-name="<?php echo e($a["firstName"] . " " . $a["lastName"]); ?>"
                                            data-bs-toggle="modal" data-bs-target="#confirmActionModal">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                        <?php else: ?>
                                            <span class="text-body-secondary small">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header"><h4 class="card-header-title">Add Admin</h4></div>
                    <div class="card-body">
                        <form id="adminForm">
                            <div class="mb-3"><label class="form-label">First Name</label><input type="text" name="firstName" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Last Name</label><input type="text" name="lastName" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="6" required></div>
                            <div class="mb-3"><label class="form-label">Role</label>
                                <select name="position" class="form-select"><option>Admin</option><option>Super Admin</option></select>
                            </div>
                            <div class="mb-4"><label class="form-label">Status</label>
                                <select name="status" class="form-select"><option>Active</option><option>Inactive</option></select>
                            </div>
                            <button type="submit" class="btn btn-primary" id="adminSubmit">Create Admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/modal/confirm-modal.php";
include __DIR__ . "/components/footer.php";
?>

<script>
// Create admin
document.getElementById('adminForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('adminSubmit');
    btn.disabled = true; btn.textContent = 'Creating...';
    const fd = new FormData(this); fd.append('csrf', CSRF_TOKEN);
    try {
        const res = await fetch('auth/create/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) { notyf.success(data.message); setTimeout(() => window.location.reload(), 900); }
        else { notyf.error(data.message); btn.disabled = false; btn.textContent = 'Create Admin'; }
    } catch (err) { notyf.error('Network or server error.'); btn.disabled = false; btn.textContent = 'Create Admin'; }
});

// Delete admin
document.addEventListener('DOMContentLoaded', () => {
    let currentId = null;
    const confirmMessage = document.getElementById('confirmActionMessage');
    const confirmButton  = document.getElementById('confirmActionButton');
    document.querySelectorAll('.delete-admin').forEach(btn => {
        btn.addEventListener('click', () => {
            currentId = btn.dataset.id;
            confirmMessage.innerHTML = `You are about to remove<br><b>${btn.dataset.name}</b>.<br>This cannot be undone.`;
            confirmButton.textContent = 'Remove Admin';
            confirmButton.className = 'btn btn-danger btn-lg mb-4';
        });
    });
    confirmButton.addEventListener('click', async () => {
        if (!currentId) return;
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        try {
            const res = await fetch('auth/delete/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF_TOKEN },
                body: new URLSearchParams({ id: currentId })
            });
            const data = await res.json();
            if (data.success) { notyf.success(data.message); setTimeout(() => window.location.reload(), 900); }
            else { notyf.error(data.message || 'Operation failed.'); }
        } catch (e) { notyf.error('Network or server error.'); }
        confirmButton.disabled = false; confirmButton.textContent = 'Remove Admin';
    });
});
</script>
