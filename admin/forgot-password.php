<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/includes/helpers.php";

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
    <title>Forgot Password :: Glajoe Services&trade;</title>
    <style>
      .login-card { max-width: 440px; }
      .form-floating { margin-bottom: 1.25rem; }
      .form-floating .form-control { height: 3.5rem; padding: 1rem 0.85rem 0.25rem; }
      .form-floating label { color: #6c757d; }
      .forgot-link { font-size: 0.8125rem; }
    </style>
  </head>
  <body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">
    <div class="container-fluid">
      <div class="row justify-content-center">

        <!-- Forgot password panel -->
        <div class="col-12 col-md-6 col-lg-5 col-xl-4 px-4 px-lg-5 my-5 align-self-center">
          <div class="login-card mx-auto">

            <div class="mb-5 text-center">
              <a href="./">
                <img src="./assets/img/logo-dark.svg" width="180" alt="Glajoe Services">
              </a>
            </div>

            <h1 class="display-4 text-center mb-2">Forgot Password</h1>
            <p class="text-body-secondary text-center mb-4">
              Enter your email and we'll send you a link to reset your password.
            </p>

            <div id="formAlert" class="alert d-none mb-3" role="alert"></div>

            <form id="forgotForm" novalidate>
              <?php echo csrf_field(); ?>

              <div class="form-floating">
                <input type="email" name="email" class="form-control" id="floatingEmail"
                       placeholder="name@example.com" autocomplete="email" required>
                <label for="floatingEmail">Email address</label>
              </div>

              <button class="btn btn-lg w-100 btn-primary mt-2" id="formSubmit" type="submit">
                Send reset link
              </button>

              <div class="text-center mt-4">
                <a href="index.php" class="forgot-link text-body-secondary">Back to sign in</a>
              </div>
            </form>

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
    <script>
      document.getElementById("forgotForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const btn   = document.getElementById("formSubmit");
        const alert = document.getElementById("formAlert");
        const csrf  = document.querySelector('meta[name="csrf-token"]').content;

        btn.disabled    = true;
        btn.textContent = "Sending…";
        alert.className = "alert d-none";

        try {
          const res  = await fetch("auth/forgot_password.php", {
            method:  "POST",
            headers: { "X-CSRF-Token": csrf },
            body:    new FormData(this),
          });
          const data = await res.json();

          alert.className   = data.success ? "alert alert-success" : "alert alert-danger";
          alert.textContent = data.message;
          if (data.success) {
            this.reset();
          }
        } catch {
          alert.className   = "alert alert-danger";
          alert.textContent = "Network error. Please try again.";
        } finally {
          btn.disabled    = false;
          btn.textContent = "Send reset link";
        }
      });
    </script>
  </body>
</html>
