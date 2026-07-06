<?php
$page = "NewAdmin";
include __DIR__ . "/components/head.php";

// Super Admin only.
if (($_SESSION["designation"] ?? "") !== "Super Admin") {
    flash("Super Admin access required.", "error");
    redirect("dashboard.php");
}
include __DIR__ . "/components/sidebar.php";
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                <div class="header mt-md-5">
                    <div class="header-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="header-title">Add New Admin</h1>
                            </div>
                            <div class="col-auto">
                                <button type="button" onclick="history.back()" class="btn btn-white btn-sm">
                                    <i class="fe fe-arrow-left"></i> Go back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-body-secondary mb-4">
                    A login password is generated automatically and emailed to the new admin along with their sign-in details.
                </p>

                <form id="newAdminForm" class="mb-4">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-floating form-group">
                                <input type="text" name="firstName" class="form-control" placeholder="John" required>
                                <label>First Name</label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-floating form-group">
                                <input type="text" name="lastName" class="form-control" placeholder="Doe" required>
                                <label>Last Name</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-group">
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                                <label>Email</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-group">
                                <select class="form-select" name="position" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Super Admin">Super Admin</option>
                                </select>
                                <label>Role</label>
                            </div>
                        </div>
                    </div>

                    <button id="newAdminSubmit" class="btn w-100 btn-primary mt-3" type="submit">
                        Add Admin
                    </button>

                    <a href="admins.php" class="btn w-100 btn-link text-body-secondary mt-2">
                        Cancel
                    </a>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
document.getElementById('newAdminForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('newAdminSubmit');
    btn.disabled = true;
    btn.textContent = 'Creating admin...';

    const fd = new FormData(this);
    fd.append('csrf', CSRF_TOKEN);

    try {
        const res = await fetch('auth/create/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => { window.location.href = data.redirect || 'admins.php'; }, 900);
        } else {
            notyf.error(data.message);
            btn.disabled = false;
            btn.textContent = 'Add Admin';
        }
    } catch (err) {
        notyf.error('Network or server error.');
        btn.disabled = false;
        btn.textContent = 'Add Admin';
    }
});
</script>
