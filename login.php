<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// login_logic
require_once './admin/data_access/login_auth.php';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="authentication page" />
  <title>Login</title>

  <link rel="stylesheet" href="./assets/css/bootstrap.min.css" />
  <link
    rel="stylesheet"
    href="./assets/vendors/bootstrap-icons/bootstrap-icons.css" />
  <link rel="stylesheet" href="./assets/css/style.css" />
</head>

<body class="auth-body">
  <button
    class="icon-button theme-toggle auth-theme-toggle"
    type="button"
    data-theme-toggle
    aria-label="Switch color theme"
    title="Switch color theme">
    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
  </button>
  <main class="auth-page">
    <section class="auth-card">
      <div class="mb-4 fs-2">
        <i class="bi bi-shield-lock-fill"></i>
        <span><strong>File Management</strong></span>
      </div>
      <form method="post">
        <!-- Error Message -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($_SESSION['successMessage'])): ?>
          <div class="alert alert-success">
            <?php
            echo $_SESSION['successMessage'];
            unset($_SESSION['successMessage']);
            ?>
          </div>
        <?php endif; ?>

        <!-- <div class="mb-4">
            <p class="text-muted mb-0">Sign in to your admin workspace.</p>
          </div> -->

        <div class="mb-3">
          <label class="form-label" for="username">Username</label>
          <input class="form-control" name="username" id="username" type="text" required />
          <!-- <div class="invalid-feedback">Enter a valid username.</div> -->
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <label class="form-label" for="loginPassword">Password</label>
          </div>
          <input
            class="form-control"
            id="loginPassword"
            type="password"
            name="password"
            minlength="6"
            required />
          <!-- <div class="invalid-feedback">
            Password must be at least 6 characters.
          </div> -->
        </div>

        <button class="btn btn-primary w-100" type="submit">Sign In</button>
      </form>

      <!-- <div class="auth-footer">
          New here? <a href="#">Create an account</a>
        </div> -->
    </section>
  </main>

  <script src="./assets/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/main.js"></script>
</body>

</html>