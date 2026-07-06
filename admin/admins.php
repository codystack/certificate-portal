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
                    <div class="col-auto">
                        <a href="new-admin.php" class="btn btn-primary lift">Add New Admin <i class="fe fe-plus"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
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
                                        <a href="edit-admin.php?id=<?php echo (int) $a["id"]; ?>" class="btn btn-sm btn-white" title="Edit">
                                            <i class="fe fe-edit-2"></i>
                                        </a>
                                        <?php if ($a["status"] === "Active"): ?>
                                        <button type="button" class="btn btn-sm btn-white toggle-admin-status"
                                            data-id="<?php echo (int) $a["id"]; ?>"
                                            data-name="<?php echo e($a["firstName"] . " " . $a["lastName"]); ?>"
                                            data-action="suspend" title="Suspend"
                                            data-bs-toggle="modal" data-bs-target="#confirmActionModal">
                                            <i class="fe fe-slash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-white toggle-admin-status"
                                            data-id="<?php echo (int) $a["id"]; ?>"
                                            data-name="<?php echo e($a["firstName"] . " " . $a["lastName"]); ?>"
                                            data-action="unsuspend" title="Unsuspend"
                                            data-bs-toggle="modal" data-bs-target="#confirmActionModal">
                                            <i class="fe fe-check-circle"></i>
                                        </button>
                                        <?php endif; ?>
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
        </div>
    </div>
</div>

<?php
include __DIR__ . "/modal/confirm-modal.php";
include __DIR__ . "/components/footer.php";
?>

<script>
// Delete / suspend / unsuspend admin
document.addEventListener('DOMContentLoaded', () => {
    let currentId = null;
    let currentAction = null; // 'delete' | 'suspend' | 'unsuspend'
    const confirmMessage = document.getElementById('confirmActionMessage');
    const confirmButton  = document.getElementById('confirmActionButton');

    document.querySelectorAll('.delete-admin').forEach(btn => {
        btn.addEventListener('click', () => {
            currentId = btn.dataset.id;
            currentAction = 'delete';
            confirmMessage.innerHTML = `You are about to remove<br><b>${btn.dataset.name}</b>.<br>This cannot be undone.`;
            confirmButton.textContent = 'Remove Admin';
            confirmButton.className = 'btn btn-danger btn-lg mb-4';
        });
    });

    document.querySelectorAll('.toggle-admin-status').forEach(btn => {
        btn.addEventListener('click', () => {
            currentId = btn.dataset.id;
            currentAction = btn.dataset.action;
            if (currentAction === 'suspend') {
                confirmMessage.innerHTML = `You are about to suspend<br><b>${btn.dataset.name}</b>.<br>They will not be able to sign in until reinstated.`;
                confirmButton.textContent = 'Suspend Admin';
                confirmButton.className = 'btn btn-danger btn-lg mb-4';
            } else {
                confirmMessage.innerHTML = `You are about to reinstate<br><b>${btn.dataset.name}</b>.<br>They will regain access to their account.`;
                confirmButton.textContent = 'Unsuspend Admin';
                confirmButton.className = 'btn btn-success btn-lg mb-4';
            }
        });
    });

    confirmButton.addEventListener('click', async () => {
        if (!currentId || !currentAction) return;
        const busyLabel = currentAction === 'delete' ? 'Remove Admin'
            : currentAction === 'suspend' ? 'Suspend Admin' : 'Unsuspend Admin';
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        try {
            const url = currentAction === 'delete' ? 'auth/delete/admin.php' : 'auth/update/admin_status.php';
            const body = currentAction === 'delete'
                ? new URLSearchParams({ id: currentId })
                : new URLSearchParams({ id: currentId, action: currentAction });
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF_TOKEN },
                body
            });
            const data = await res.json();
            if (data.success) { notyf.success(data.message); setTimeout(() => window.location.reload(), 900); }
            else { notyf.error(data.message || 'Operation failed.'); }
        } catch (e) { notyf.error('Network or server error.'); }
        confirmButton.disabled = false; confirmButton.textContent = busyLabel;
    });
});
</script>
