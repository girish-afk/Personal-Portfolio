<?php
/**
 * Admin Secure Authentication Login
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Redirect if already authenticated
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$errorMessage = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $errorMessage = 'Security token invalid or expired. Please reload the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errorMessage = 'Please provide both username and password.';
        } else {
            $result = Auth::attempt($username, $password);
            if ($result['success']) {
                $target = $_SESSION['intended_url'] ?? 'index.php';
                unset($_SESSION['intended_url']);
                header('Location: ' . $target);
                exit;
            } else {
                $errorMessage = $result['message'] ?? 'Authentication failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login — Girish Pawar Portfolio CMS</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-brand">
            <img src="../assets/img/logo.svg" alt="Girish Pawar Monogram">
            <h1>Owner Authentication</h1>
            <p>Private content management &amp; administration</p>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><?= e($errorMessage) ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-input" placeholder="admin" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-input" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn btn-cyan btn-lg" style="width: 100%; margin-top: 1rem;">
                <i class="fas fa-lock"></i> Secure Login
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--admin-border); padding-top: 1.25rem;">
            <a href="../index.php" style="color: var(--text-sub); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-arrow-left"></i> Return to Public Portfolio
            </a>
        </div>
    </div>

</body>
</html>
