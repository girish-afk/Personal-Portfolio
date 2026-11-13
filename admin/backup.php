<?php
/**
 * Admin Database Backup & Restore Utility
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/includes/admin-auth-check.php';

// Handle Direct Download of Backup JSON
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $backupData = [
        'app' => APP_NAME,
        'version' => APP_VERSION,
        'timestamp' => date('Y-m-d H:i:s'),
        'profile' => Database::all('profile'),
        'skill_categories' => Database::all('skill_categories'),
        'skills' => Database::all('skills'),
        'projects' => Database::all('projects'),
        'experience_claims' => Database::all('experience_claims'),
        'internships' => Database::all('internships'),
        'education' => Database::all('education'),
        'certifications' => Database::all('certifications'),
        'social_links' => Database::all('social_links'),
        'settings' => Database::all('settings')
    ];

    $filename = 'girish_portfolio_backup_' . date('Ymd_His') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Handle Restore from Uploaded JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: backup.php');
        exit;
    }

    if (!empty($_FILES['backup_file']['tmp_name'])) {
        $content = file_get_contents($_FILES['backup_file']['tmp_name']);
        $json = json_decode($content, true);

        if (!$json || !isset($json['profile'])) {
            set_flash('danger', 'Invalid backup file format. Expected a valid portfolio JSON export.');
        } else {
            // Restore into persistence layer
            $tables = ['profile', 'skill_categories', 'skills', 'projects', 'experience_claims', 'internships', 'education', 'certifications', 'social_links'];
            
            $pdo = Database::getConnection();
            if ($pdo) {
                // If MySQL is active, truncate and reinsert
                foreach ($tables as $t) {
                    if (isset($json[$t]) && is_array($json[$t])) {
                        $pdo->exec("TRUNCATE TABLE `{$t}`");
                        foreach ($json[$t] as $row) {
                            Database::insert($t, $row);
                        }
                    }
                }
            } else {
                // If in fallback JSON mode, replace records directly
                $store = Database::getJsonStore();
                foreach ($tables as $t) {
                    if (isset($json[$t]) && is_array($json[$t])) {
                        $store[$t] = $json[$t];
                    }
                }
                Database::saveJsonStore($store);
            }

            set_flash('success', 'Database successfully restored from backup snapshot!');
        }
    } else {
        set_flash('warning', 'Please select a backup JSON file to upload.');
    }

    header('Location: backup.php');
    exit;
}

$pageTitle = 'Backup & Restore';
require_once __DIR__ . '/includes/admin-header.php';
?>

    <div class="page-header">
        <div>
            <h1>Portfolio Data Backup &amp; Disaster Recovery</h1>
            <p>Download full structured snapshots of your portfolio data and restore anytime without terminal access.</p>
        </div>
    </div>

    <div class="admin-grid grid-cols-2">
        <!-- Download Backup -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-file-export" style="color: var(--cyan);"></i> Create &amp; Download Snapshot</h3>
            </div>

            <p style="font-size: 0.9rem; color: var(--text-sub); line-height: 1.6; margin-bottom: 1.5rem;">
                Exports all portfolio entities (Profile, Projects, Case Studies, Skills, Experience, Education, Certifications, and Settings) into a single JSON archive. Store this on your computer or cloud storage as a verified backup.
            </p>

            <a href="backup.php?action=download" class="btn btn-cyan btn-lg" style="width: 100%; justify-content: center;">
                <i class="fas fa-download"></i> Download Full Backup (.json)
            </a>
        </div>

        <!-- Restore Backup -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-file-import" style="color: var(--amber);"></i> Restore from Archive</h3>
            </div>

            <form action="backup.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="restore">

                <div class="form-group">
                    <label for="backup_file" class="form-label">Upload Backup JSON File</label>
                    <input type="file" name="backup_file" id="backup_file" class="form-input" accept=".json,application/json" required>
                </div>

                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; font-size: 0.82rem; color: #fde68a;">
                    <i class="fas fa-exclamation-triangle"></i> Warning: Restoring will overwrite existing portfolio records with the data from the uploaded backup file.
                </div>

                <button type="submit" class="btn btn-danger btn-confirm-delete" data-confirm="Are you absolutely sure you want to restore? This will replace your current website data." style="width: 100%; justify-content: center;">
                    <i class="fas fa-history"></i> Restore Database
                </button>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
