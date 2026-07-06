<?php
$page = "Security";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">

                <?php include __DIR__ . "/components/profile-header.php"; ?>

                <div class="row justify-content-between align-items-center mb-5">
                    <div class="col-12 col-md-9 col-xl-7">
                        <h2 class="mb-2">Change your password</h2>
                        <p class="text-body-secondary mb-xl-0">
                            You will need to sign in again after changing your password.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 order-md-2">
                        <div class="card bg-light border ms-md-4">
                            <div class="card-body">
                                <p class="mb-2">Password requirements</p>
                                <p class="small text-body-secondary mb-2">
                                    To create a new password, you have to meet all of the following requirements:
                                </p>
                                <ul class="small text-body-secondary ps-4 mb-0">
                                    <li>Minimum 6 characters</li>
                                    <li>Can't be the same as your current password</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <form id="changePasswordForm">
                            <div class="form-floating form-group position-relative">
                                <input type="password" name="current_password" class="form-control pe-5" placeholder="Password" required>
                                <label>Current Password</label>
                                <span onclick="togglePassword(this)"
                                    class="position-absolute end-0 translate-middle-y me-3"
                                    style="cursor: pointer; top: 55%;">
                                    <i class="fe fe-eye"></i>
                                </span>
                            </div>

                            <div class="form-floating form-group position-relative">
                                <input type="password" name="password" class="form-control pe-5" placeholder="Password" minlength="6" required>
                                <label>New Password</label>
                                <span onclick="togglePassword(this)"
                                    class="position-absolute end-0 translate-middle-y me-3"
                                    style="cursor: pointer; top: 55%;">
                                    <i class="fe fe-eye"></i>
                                </span>
                            </div>

                            <div class="form-floating form-group position-relative">
                                <input type="password" name="confirm_password" class="form-control pe-5" placeholder="Password" minlength="6" required>
                                <label>Confirm Password</label>
                                <span onclick="togglePassword(this)"
                                    class="position-absolute end-0 translate-middle-y me-3"
                                    style="cursor: pointer; top: 55%;">
                                    <i class="fe fe-eye"></i>
                                </span>
                            </div>

                            <button class="btn w-100 btn-primary lift" type="submit">
                                Update password
                            </button>
                        </form>
                    </div>
                </div>

                <br>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>

<script>
function togglePassword(el) {
    const container = el.closest(".form-floating");
    const input = container.querySelector("input");
    const icon = el.querySelector("i");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fe-eye", "fe-eye-off");
    } else {
        input.type = "password";
        icon.classList.replace("fe-eye-off", "fe-eye");
    }
}

document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    formData.append('csrf', CSRF_TOKEN);

    if (formData.get('password') !== formData.get('confirm_password')) {
        notyf.error('Passwords do not match.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

    try {
        const res = await fetch('auth/update/password.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            notyf.success(data.message);
            setTimeout(() => { window.location.href = data.redirect || 'index.php'; }, 1200);
        } else {
            notyf.error(data.message);
        }
    } catch (err) {
        notyf.error('Network or server error.');
    }

    btn.disabled = false;
    btn.innerHTML = 'Update password';
});
</script>
