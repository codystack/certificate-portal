<?php
$page = "Admins";
include __DIR__ . "/components/head.php";

// Super Admin only.
if (($_SESSION["designation"] ?? "") !== "Super Admin") {
    flash("Super Admin access required.", "error");
    redirect("dashboard.php");
}

$id = (int) ($_GET["id"] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id, email, firstName, lastName, position FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$admin) {
    flash("Admin not found.", "error");
    redirect("admins.php");
}
if ((int) $admin["id"] === (int) $_SESSION["admin_id"]) {
    flash("Use the Profile page to edit your own account.", "error");
    redirect("admins.php");
}

include __DIR__ . "/components/sidebar.php";
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Edit record</h6>
                        <h1 class="header-title">Edit Admin</h1>
                    </div>
                    <div class="col-auto">
                        <a href="admins.php" class="btn btn-white">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form id="adminEditForm">
                            <input type="hidden" name="id" value="<?php echo (int) $admin["id"]; ?>">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" class="form-control" value="<?php echo e($admin["firstName"]); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" class="form-control" value="<?php echo e($admin["lastName"]); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($admin["email"]); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Role</label>
                                <select name="position" class="form-select">
                                    <option value="Admin" <?php echo $admin["position"] === "Admin" ? "selected" : ""; ?>>Admin</option>
                                    <option value="Super Admin" <?php echo $admin["position"] === "Super Admin" ? "selected" : ""; ?>>Super Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" id="adminEditSubmit">Save Changes</button>
                            <a href="admins.php" class="btn btn-link text-body-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
document.getElementById('adminEditForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('adminEditSubmit');
    btn.disabled = true; btn.textContent = 'Saving...';
    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);
    try {
        const res = await fetch('auth/update/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => window.location.href = data.redirect, 1000);
        } else {
            notyf.error(data.message);
            btn.disabled = false; btn.textContent = 'Save Changes';
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false; btn.textContent = 'Save Changes';
    }
});
</script>
