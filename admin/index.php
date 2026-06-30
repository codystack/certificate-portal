<?php
session_start();
require_once __DIR__ . "/includes/helpers.php";

// Already signed in → straight to the dashboard.
if (isset($_SESSION["admin_id"])) {
    redirect("dashboard.php");
}
?>
<!doctype html>
<html lang="en" data-bs-theme="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo csrf_meta(); ?>
    <link rel="shortcut icon" href="../assets/images/glajoe-favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/libs.bundle.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <title>Sign In :: Glajoe Admin</title>
</head>
<body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-5 col-lg-6 col-xl-4 px-lg-6 my-5 align-self-center">
                <div class="px-lg-4 px-0">
                    <div class="mb-4 text-center">
                        <a href="../"><img src="../assets/images/glajoe-favicon.png" width="72" alt="Glajoe"></a>
                    </div>
                    <h1 class="display-4 text-center mb-3">Glajoe Admin</h1>
                    <p class="text-body-secondary text-center mb-5">Sign in to manage certificates.</p>

                    <form id="loginForm">
                        <div class="form-floating form-group mb-3">
                            <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                            <label for="floatingInput">Email address</label>
                        </div>
                        <div class="form-floating form-group position-relative mb-4">
                            <input type="password" name="password" class="form-control pe-5" id="floatingPassword" placeholder="Password" required>
                            <label for="floatingPassword">Password</label>
                            <span onclick="togglePassword()" class="position-absolute end-0 translate-middle-y me-4" style="cursor:pointer;top:35%!important;">
                                <i id="toggleIcon" class="fe fe-eye"></i>
                            </span>
                        </div>
                        <button class="btn btn-lg w-100 btn-primary mb-3" id="loginSubmit" type="submit">Sign in</button>
                        <div class="text-center">
                            <a href="../" class="form-text small text-body-secondary">&larr; Back to verification portal</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-12 col-md-7 col-lg-6 d-none d-lg-block">
                <div class="bg-cover h-100 min-vh-100 mt-n1 me-n3" style="background-image:url(assets/img/banner-bg.jpeg);background-position:center;"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="assets/js/vendor.bundle.js"></script>
    <script src="assets/js/theme.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        function togglePassword() {
            const input = document.getElementById('floatingPassword');
            const icon = document.getElementById('toggleIcon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fe-eye', !show);
            icon.classList.toggle('fe-eye-off', show);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('#loginForm');
            const submitButton = document.querySelector('#loginSubmit');
            const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                submitButton.disabled = true;
                submitButton.textContent = 'Signing in...';

                const formData = new FormData(form);
                formData.append('csrf', CSRF_TOKEN);

                try {
                    const res = await fetch('auth/login_auth.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.href = data.redirect, 1200);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (err) {
                    notyf.error('Network or server error. Please try again.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Sign in';
                }
            });
        });
    </script>
</body>
</html>
