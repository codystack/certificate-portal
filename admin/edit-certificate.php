<?php
$page = "Certificates";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";

$id = (int) ($_GET["id"] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id, title, client, certNum, image, status FROM certificate WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$cert = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$cert) {
    flash("Certificate not found.", "error");
    redirect("certificates.php");
}
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Edit record</h6>
                        <h1 class="header-title">Edit Certificate</h1>
                    </div>
                    <div class="col-auto">
                        <a href="certificates.php" class="btn btn-white">Back</a>
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
                        <form id="certForm" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo (int) $cert["id"]; ?>">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?php echo e($cert["title"]); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Client's Name</label>
                                <input type="text" name="client" class="form-control" value="<?php echo e($cert["client"]); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Certificate No.</label>
                                <input type="text" name="certNum" class="form-control" value="<?php echo e($cert["certNum"]); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active"  <?php echo $cert["status"] === "Active" ? "selected" : ""; ?>>Active</option>
                                    <option value="Expired" <?php echo $cert["status"] === "Expired" ? "selected" : ""; ?>>Expired</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Certificate File <span class="text-body-secondary small">(leave empty to keep current)</span></label>
                                <?php if (!empty($cert["image"])): ?>
                                    <p class="mb-2"><a href="<?php echo e($cert["image"]); ?>" target="_blank" rel="noopener"><i class="fe fe-file-text"></i> <?php echo e(basename($cert["image"])); ?></a></p>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <button type="submit" class="btn btn-primary" id="certSubmit">Update Certificate</button>
                            <a href="certificates.php" class="btn btn-link text-body-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
document.getElementById('certForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('certSubmit');
    btn.disabled = true; btn.textContent = 'Updating...';
    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);
    try {
        const res = await fetch('auth/update/certificate.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => window.location.href = data.redirect, 1000);
        } else {
            notyf.error(data.message);
            btn.disabled = false; btn.textContent = 'Update Certificate';
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false; btn.textContent = 'Update Certificate';
    }
});
</script>
