<?php
/**
 * Admin Settings, SEO & Security
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Settings & Security';
require_once __DIR__ . '/includes/admin-header.php';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: settings.php');
        exit;
    }

    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if ($newPass !== $confirmPass) {
        set_flash('danger', 'New password and confirmation do not match.');
    } else {
        $result = Auth::changePassword((int)$_SESSION['admin_id'], $currentPass, $newPass);
        if ($result['success']) {
            set_flash('success', $result['message']);
        } else {
            set_flash('danger', $result['message']);
        }
    }

    header('Location: settings.php');
    exit;
}

// Handle SEO & Site Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: settings.php');
        exit;
    }

    $siteTitle = clean_input($_POST['site_title'] ?? '');
    $metaDesc = clean_input($_POST['meta_description'] ?? '');
    $metaKeys = clean_input($_POST['meta_keywords'] ?? '');
    $emailNotify = isset($_POST['contact_email_notifications']) ? '1' : '0';

    Database::updateSetting('site_title', $siteTitle);
    Database::updateSetting('meta_description', $metaDesc);
    Database::updateSetting('meta_keywords', $metaKeys);
    Database::updateSetting('contact_email_notifications', $emailNotify);

    set_flash('success', 'Site settings and SEO metadata saved.');
    header('Location: settings.php');
    exit;
}

// Handle MySQL Sync
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync_mysql') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $synced = Database::syncJsonToMysql();
        if ($synced) {
            set_flash('success', 'Database synchronization complete! All tables seeded directly into MySQL.');
        } else {
            set_flash('danger', 'MySQL connection failed: ' . Database::getErrorMessage() . '. Ensure MySQL is started in XAMPP.');
        }
    }
    header('Location: settings.php#database');
    exit;
}

$siteTitle = Database::getSetting('site_title', 'Girish Pawar — Personal Developer Portfolio');
$metaDesc = Database::getSetting('meta_description', '');
$metaKeys = Database::getSetting('meta_keywords', '');
$emailNotify = Database::getSetting('contact_email_notifications', '1');
$isFallback = Database::isFallback();
?>

    <div class="page-header">
        <div>
            <h1>Settings, Security &amp; Database</h1>
            <p>Configure administrative credentials, global search engine metadata, and database engines.</p>
        </div>
    </div>

    <div class="admin-grid grid-cols-2" style="margin-bottom: 2rem;">
        <!-- Change Password Card -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-lock" style="color: var(--cyan);"></i> Change Owner Password</h3>
            </div>

            <form action="settings.php" method="POST" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">New Password (Min 8 characters)</label>
                    <input type="password" name="new_password" id="new_password" class="form-input" minlength="8" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-input" minlength="8" required>
                </div>

                <button type="submit" class="btn btn-cyan" style="width: 100%;">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>

        <!-- Database Engine & Sync -->
        <div class="admin-card" id="database">
            <div class="card-header-flex">
                <h3><i class="fas fa-database" style="color: var(--cyan);"></i> Database Engine Status</h3>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <div style="font-size: 0.85rem; color: var(--text-sub); margin-bottom: 0.5rem;">
                    Status: <strong style="color: <?= $isFallback ? 'var(--amber)' : 'var(--emerald)' ?>;"><?= $isFallback ? 'Server-Side JSON Persistence' : 'MySQL PDO Connected' ?></strong>
                </div>
                <div style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-dim); line-height: 1.6;">
                    Host: <?= DB_HOST ?>:<?= DB_PORT ?><br>
                    Database: <?= DB_NAME ?><br>
                    User: <?= DB_USER ?>
                </div>
            </div>

            <form action="settings.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="sync_mysql">
                <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-sync-alt"></i> <?= $isFallback ? 'Connect & Sync to MySQL' : 'Re-verify MySQL Connection' ?>
                </button>
            </form>
            <div class="form-help" style="margin-top: 0.75rem;">
                Attempts connection to MySQL and migrates any newly created data directly into your database schema.
            </div>
        </div>
    </div>

    <!-- SEO & Metadata -->
    <div class="admin-card">
        <div class="card-header-flex">
            <h3><i class="fas fa-globe" style="color: var(--cyan);"></i> SEO &amp; Open Graph Metadata</h3>
        </div>

        <form action="settings.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_settings">

            <div class="form-group">
                <label for="site_title" class="form-label">Browser &amp; Search Engine Title</label>
                <input type="text" name="site_title" id="site_title" class="form-input" value="<?= e($siteTitle) ?>" required>
            </div>

            <div class="form-group">
                <label for="meta_description" class="form-label">Meta Description (150-160 characters recommended)</label>
                <textarea name="meta_description" id="meta_description" class="form-textarea" rows="2"><?= e($metaDesc) ?></textarea>
            </div>

            <div class="form-group">
                <label for="meta_keywords" class="form-label">Meta Keywords (Comma-separated)</label>
                <input type="text" name="meta_keywords" id="meta_keywords" class="form-input" value="<?= e($metaKeys) ?>">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.65rem; cursor: pointer;">
                    <input type="checkbox" name="contact_email_notifications" value="1" <?= $emailNotify === '1' ? 'checked' : '' ?> style="width: 16px; height: 16px; accent-color: var(--cyan);">
                    <span style="font-weight: 600; color: var(--text-main);">Send Email Notification on New Contact Form Submission</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                <button type="submit" class="btn btn-cyan btn-lg">
                    <i class="fas fa-save"></i> Save SEO Settings
                </button>
            </div>
        </form>
    </div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
