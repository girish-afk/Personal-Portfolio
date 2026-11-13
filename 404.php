<?php
/**
 * Custom 404 Error Page
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/includes/functions.php';

$profile = Database::find('profile', 1) ?? [];
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | Girish Pawar</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem;">

    <div style="max-width: 500px;">
        <img src="assets/img/logo.svg" alt="Monogram" width="64" height="64" style="margin-bottom: 1.5rem;">
        <div style="font-family: var(--font-mono); font-size: 4rem; font-weight: 800; color: var(--cyan-primary); line-height: 1; margin-bottom: 0.5rem;">404</div>
        <h1 style="font-family: var(--font-display); font-size: 1.75rem; margin-bottom: 0.75rem; color: var(--text-primary);">Page Not Found</h1>
        <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 1rem; line-height: 1.6;">
            The requested destination does not exist or has been relocated within the portfolio index.
        </p>
        <a href="index.php" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Back to Portfolio
        </a>
    </div>

</body>
</html>
