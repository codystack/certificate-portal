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
    <link rel="shortcut icon" href="assets/img/glajoe-favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/libs.bundle.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Sign In :: Glajoe Services&trade;</title>
    <style>
      .login-card { max-width: 440px; }
      .form-floating { margin-bottom: 1.25rem; }
      .form-floating .form-control { height: 3.5rem; padding: 1rem 0.85rem 0.25rem; }
      .form-floating label { padding: 0.85rem; color: #6c757d; }
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

        <!-- Login panel -->
        <div class="col-12 col-md-6 col-lg-5 col-xl-4 px-4 px-lg-5 my-5 align-self-center">
          <div class="login-card mx-auto">

            <div class="mb-5 text-center">
              <a href="./">
                <img src="./assets/img/logo-dark.svg" width="180" alt="Glajoe Services">
              </a>
            </div>

            <h1 class="display-4 text-center mb-2">Welcome Back</h1>
            <p class="text-body-secondary text-center mb-4">Sign in to continue to your account.</p>

            <div id="loginAlert" class="alert d-none mb-3" role="alert"></div>

            <form id="loginForm" novalidate>
              <?php echo csrf_field(); ?>

              <div class="form-floating">
                <input type="email" name="email" class="form-control" id="floatingEmail"
                       placeholder="name@example.com" autocomplete="email" required>
                <label for="floatingEmail">Email address</label>
              </div>

              <div class="form-floating position-relative">
                <input type="password" name="password" class="form-control pe-5"
                       id="floatingPassword" placeholder="Password"
                       autocomplete="current-password" required>
                <label for="floatingPassword">Password</label>
                <span class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
                  <i id="toggleIcon" class="fe fe-eye"></i>
                </span>
              </div>

              <div class="text-end mb-4">
                <a href="forgot-password" class="forgot-link text-body-secondary">Forgot password?</a>
              </div>

              <button class="btn btn-lg w-100 btn-primary" id="loginSubmit" type="submit">
                Sign in
              </button>
            </form>

          </div>
        </div>

        <!-- Cover image -->
        <div class="col-12 col-md-6 col-lg-7 col-xl-8 d-none d-md-block">
          <div class="bg-cover h-100 min-vh-100 mt-n1 me-n3"
               style="background-image: url(assets/img/bg.jpg);"></div>
        </div>

      </div>
    </div>

    <script src="assets/js/vendor.bundle.js"></script>
    <script src="assets/js/theme.bundle.js"></script>
    <script>
      function togglePassword() {
        const pw   = document.getElementById("floatingPassword");
        const icon = document.getElementById("toggleIcon");
        const show = pw.type === "password";
        pw.type        = show ? "text" : "password";
        icon.className = show ? "fe fe-eye-off" : "fe fe-eye";
      }

      document.getElementById("loginForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const btn   = document.getElementById("loginSubmit");
        const alert = document.getElementById("loginAlert");
        const csrf  = document.querySelector('meta[name="csrf-token"]').content;

        btn.disabled    = true;
        btn.textContent = "Signing in…";
        alert.className = "alert d-none";

        try {
          const res  = await fetch("auth/login_auth.php", {
            method:  "POST",
            headers: { "X-CSRF-Token": csrf },
            body:    new FormData(this),
          });
          const data = await res.json();

          if (data.success) {
            btn.textContent = "Redirecting…";
            window.location.href = data.redirect;
          } else {
            alert.className   = "alert alert-danger";
            alert.textContent = data.message;
            btn.disabled      = false;
            btn.textContent   = "Sign in";
          }
        } catch {
          alert.className   = "alert alert-danger";
          alert.textContent = "Network error. Please try again.";
          btn.disabled      = false;
          btn.textContent   = "Sign in";
        }
      });
    </script>
  </body>
</html>
