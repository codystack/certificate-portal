<?php
$page = "AddCertificate";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">New record</h6>
                        <h1 class="header-title">Add Certificate</h1>
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
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Client's Name</label>
                                <input type="text" name="client" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Certificate No.</label>
                                <input type="text" name="certNum" class="form-control" placeholder="e.g. GMSL/SSL-XXU/092031/108" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Certificate File <span class="text-body-secondary small">(PDF, JPG or PNG, max 10 MB)</span></label>
                                <input type="file" name="image" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <button type="submit" class="btn btn-primary" id="certSubmit">Save Certificate</button>
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
    btn.disabled = true; btn.textContent = 'Saving...';
    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);
    try {
        const res = await fetch('auth/create/certificate.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => window.location.href = data.redirect, 1000);
        } else {
            notyf.error(data.message);
            btn.disabled = false; btn.textContent = 'Save Certificate';
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false; btn.textContent = 'Save Certificate';
    }
});
</script>
