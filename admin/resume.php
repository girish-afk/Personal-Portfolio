<?php
/**
 * Admin Resume Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Resume';
require_once __DIR__ . '/includes/admin-header.php';
require_once dirname(__DIR__) . '/includes/upload.php';

$profile = Database::find('profile', 1) ?? [];
$currentResume = $profile['resume_url'] ?? 'uploads/resume/Girish_Pawar_Resume.pdf';
$fullResumePath = ROOT_PATH . '/' . ltrim($currentResume, '/');
$fileExists = file_exists($fullResumePath);

// Handle Resume Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: resume.php');
        exit;
    }

    if (!empty($_FILES['resume_file']['name'])) {
        $upload = Upload::document($_FILES['resume_file'], 'resume');
        if ($upload['success']) {
            Database::update('profile', ['resume_url' => $upload['file_path']], 1);
            set_flash('success', 'Resume document uploaded and published successfully. Public download links updated automatically.');
        } else {
            set_flash('danger', 'Resume upload failed: ' . $upload['error']);
        }
    } else {
        set_flash('warning', 'Please select a PDF resume file to upload.');
    }

    header('Location: resume.php');
    exit;
}
?>

    <div class="page-header">
        <div>
            <h1>Curriculum Vitae / Resume Management</h1>
            <p>Upload, update, and preview your professional resume PDF without altering site source code.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="../download-resume.php" class="btn btn-cyan" target="_blank">
                <i class="fas fa-download"></i> Test Public Download
            </a>
        </div>
    </div>

    <div class="admin-grid grid-cols-2" style="margin-bottom: 2rem;">
        <!-- Upload Card -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-file-upload" style="color: var(--cyan);"></i> Upload Updated Resume</h3>
            </div>

            <form action="resume.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="resume_file" class="form-label">Select PDF Document</label>
                    <input type="file" name="resume_file" id="resume_file" class="form-input" accept=".pdf,application/pdf" required>
                    <div class="form-help">Only official PDF format allowed. Maximum file size: <?= MAX_UPLOAD_SIZE_MB ?>MB.</div>
                </div>

                <div style="background: rgba(0, 0, 0, 0.2); padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--admin-border); margin: 1.5rem 0;">
                    <h4 style="font-size: 0.9rem; color: var(--text-main); margin-bottom: 0.4rem;">
                        <i class="fas fa-shield-alt" style="color: var(--emerald);"></i> Automated Publication
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--text-sub); margin: 0; line-height: 1.5;">
                        When you upload a new document, the public "Download Resume" buttons on the Hero, About, and Footer immediately link to this new version without any server restarts.
                    </p>
                </div>

                <button type="submit" class="btn btn-cyan btn-lg" style="width: 100%;">
                    <i class="fas fa-upload"></i> Upload &amp; Activate Resume
                </button>
            </form>
        </div>

        <!-- Current File Status Card -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-info-circle" style="color: var(--cyan);"></i> Active File Status</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--text-dim); text-transform: uppercase;">Active Path</div>
                    <div style="font-family: var(--font-mono); font-size: 0.88rem; color: var(--cyan); word-break: break-all;">
                        <?= e($currentResume) ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 0.78rem; color: var(--text-dim); text-transform: uppercase;">File Presence</div>
                    <div>
                        <?php if ($fileExists): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Verified On Server (<?= round(filesize($fullResumePath) / 1024, 1) ?> KB)</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> File Missing</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($fileExists): ?>
                    <div>
                        <div style="font-size: 0.78rem; color: var(--text-dim); text-transform: uppercase;">Last Modified</div>
                        <div style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--text-main);">
                            <?= date('M j, Y — g:i A', filemtime($fullResumePath)) ?>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display: flex; gap: 0.75rem;">
                        <a href="../<?= e($currentResume) ?>" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                        <a href="../download-resume.php" class="btn btn-cyan btn-sm">
                            <i class="fas fa-download"></i> Direct Download
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
