<?php

/**
 * Simple login handler for Dynamic Graph Creator
 * This is a minimal login page for development/testing.
 * In live environment, users will login through the main Rapidkart system.
 */

// Handle login form submission
if (isset($_POST['submit']) && $_POST['submit'] === 'login') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $admin_user = new AdminUser();
        $admin_user->setEmail($_POST['email']);
        $admin_user->setPassword($_POST['password']);

        if ($admin_user->authenticate()) {
            // Set company and licence IDs
            BaseConfig::$company_id = $admin_user->getCompanyId();

            // Get licence_id from company
            $licence_company = new LicenceCompanies($admin_user->getCompanyId());
            BaseConfig::$licence_id = $licence_company->getLicid();

            // Load full user data
            $admin_user->load();

            // Login user using Session class (same as live project)
            Session::loginUser($admin_user);

            // Redirect to home page
            header('Location: .?urlq=graph');
            exit;
        } else {
            $error_message = "Invalid email or password";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dynamic Graph Creator</title>
    <link href="<?= SiteConfig::themeLibrariessUrl() ?>bootstrap5/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Dynamic Graph Creator</h2>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="submit" value="login">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-login">Login</button>
        </form>
    </div>
</body>
</html>
