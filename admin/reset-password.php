<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/includes/helpers.php";

if (isset($_SESSION["admin_id"])) {
    redirect("dashboard.php");
}

$token = $_GET["token"] ?? "";
$valid = false;

if ($token !== "") {
    $tokenHash = hash("sha256", $token);
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW() LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $tokenHash);
    mysqli_stmt_execute($stmt);
    $valid = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
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
    <title>Reset Password :: Glajoe Services&trade;</title>
    <style>
      .login-card { max-width: 440px; }
      .form-floating { margin-bottom: 1.25rem; }
      .form-floating .form-control { height: 3.5rem; padding: 1rem 0.85rem 0.25rem; }
      .form-floating label { color: #6c757d; }
      .toggle-pw {
        position: absolute;
        right: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        z-index: 5;
      }
      .forgot-link { font-size: 0.8125rem; }
    </style>
  </head>
  <body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">
    <div class="container-fluid">
      <div class="row justify-content-center">

        <!-- Reset password panel -->
        <div class="col-12 col-md-6 col-lg-5 col-xl-4 px-4 px-lg-5 my-5 align-self-center">
          <div class="login-card mx-auto">

            <div class="mb-5 text-center">
              <a href="./">
                <img src="./assets/img/logo-dark.svg" width="180" alt="Glajoe Services">
              </a>
            </div>

            <?php if (!$valid): ?>
              <h1 class="display-4 text-center mb-2">Link Expired</h1>
              <p class="text-body-secondary text-center mb-4">
                This password reset link is invalid or has expired. Request a new one below.
              </p>
              <div class="text-center">
                <a href="forgot-password.php" class="btn btn-lg w-100 btn-primary">Request a new link</a>
              </div>
            <?php else: ?>
              <h1 class="display-4 text-center mb-2">Reset Password</h1>
              <p class="text-body-secondary text-center mb-4">Choose a new password for your account.</p>

              <div id="formAlert" class="alert d-none mb-3" role="alert"></div>

              <form id="resetForm" novalidate>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">

                <div class="form-floating position-relative">
                  <input type="password" name="password" class="form-control pe-5"
                         id="floatingPassword" placeholder="New password"
                         autocomplete="new-password" minlength="6" required>
                  <label for="floatingPassword">New password</label>
                  <span class="toggle-pw" onclick="togglePassword('floatingPassword', 'toggleIcon1')" title="Show/hide password">
                    <i id="toggleIcon1" class="fe fe-eye"></i>
                  </span>
                </div>

                <div class="form-floating position-relative">
                  <input type="password" name="confirm_password" class="form-control pe-5"
                         id="floatingConfirm" placeholder="Confirm password"
                         autocomplete="new-password" minlength="6" required>
                  <label for="floatingConfirm">Confirm password</label>
                  <span class="toggle-pw" onclick="togglePassword('floatingConfirm', 'toggleIcon2')" title="Show/hide password">
                    <i id="toggleIcon2" class="fe fe-eye"></i>
                  </span>
                </div>

                <button class="btn btn-lg w-100 btn-primary mt-2" id="formSubmit" type="submit">
                  Reset password
                </button>
              </form>
            <?php endif; ?>

          </div>
        </div>

        <!-- Cover image -->
        <div class="col-12 col-md-6 col-lg-7 col-xl-8 d-none d-md-block">
          <div class="bg-cover h-100 min-vh-100 mt-n1 me-n3"
               style="background-image: url(assets/img/pass-bg.jpg);"></div>
        </div>

      </div>
    </div>

    <script src="assets/js/vendor.bundle.js"></script>
    <script src="assets/js/theme.bundle.js"></script>
    <?php if ($valid): ?>
    <script>
      function togglePassword(inputId, iconId) {
        const pw   = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        const show = pw.type === "password";
        pw.type        = show ? "text" : "password";
        icon.className = show ? "fe fe-eye-off" : "fe fe-eye";
      }

      document.getElementById("resetForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const btn   = document.getElementById("formSubmit");
        const alert = document.getElementById("formAlert");
        const csrf  = document.querySelector('meta[name="csrf-token"]').content;

        const password = this.password.value;
        const confirm  = this.confirm_password.value;
        if (password !== confirm) {
          alert.className   = "alert alert-danger";
          alert.textContent = "Passwords do not match.";
          return;
        }

        btn.disabled    = true;
        btn.textContent = "Resetting…";
        alert.className = "alert d-none";

        try {
          const res  = await fetch("auth/reset_password.php", {
            method:  "POST",
            headers: { "X-CSRF-Token": csrf },
            body:    new FormData(this),
          });
          const data = await res.json();

          if (data.success) {
            alert.className   = "alert alert-success";
            alert.textContent = data.message;
            btn.textContent   = "Redirecting…";
            setTimeout(() => { window.location.href = data.redirect; }, 1500);
          } else {
            alert.className   = "alert alert-danger";
            alert.textContent = data.message;
            btn.disabled      = false;
            btn.textContent   = "Reset password";
          }
        } catch {
          alert.className   = "alert alert-danger";
          alert.textContent = "Network error. Please try again.";
          btn.disabled      = false;
          btn.textContent   = "Reset password";
        }
      });
    </script>
    <?php endif; ?>
  </body>
</html>
