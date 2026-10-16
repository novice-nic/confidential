<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="authentication page" />
    <title>Login</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link
      rel="stylesheet"
      href="../assets/vendors/bootstrap-icons/bootstrap-icons.css"
    />
    <link rel="stylesheet" href="../assets/css/style.css" />
  </head>

  <body class="auth-body">
    <button
      class="icon-button theme-toggle auth-theme-toggle"
      type="button"
      data-theme-toggle
      aria-label="Switch color theme"
      title="Switch color theme"
    >
      <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>
    <main class="auth-page">
      <section class="auth-card">
        <span><strong>Confidential Section | Admin Login</strong></span>
        <form class="needs-validation" novalidate>
          <div class="mb-4">
            <p class="text-muted mb-0">Sign in to your admin workspace.</p>
          </div>

          <div class="mb-3">
            <label class="form-label" for="loginEmail">Username</label>
            <input class="form-control" id="loginEmail" type="email" required />
            <div class="invalid-feedback">Enter a valid username.</div>
          </div>
          <div class="mb-3">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="loginPassword">Password</label>
            </div>
            <input
              class="form-control"
              id="loginPassword"
              type="password"
              minlength="6"
              required
            />
            <div class="invalid-feedback">
              Password must be at least 6 characters.
            </div>
          </div>

          <button class="btn btn-primary w-100" type="submit">Sign In</button>
        </form>

        <div class="auth-footer">
          New here? <a href="#">Create an account</a>
        </div>
      </section>
    </main>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
  </body>
</html>
