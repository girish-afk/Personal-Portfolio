<?php
/**
 * Admin Certifications & Achievements Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Certifications';
require_once __DIR__ . '/includes/admin-header.php';
require_once dirname(__DIR__) . '/includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: certifications.php');
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'save_certification') {
        $id = (int)($_POST['cert_id'] ?? 0);
        $title = clean_input($_POST['title'] ?? '');
        $type = clean_input($_POST['certificate_type'] ?? 'Certificate of Participation');
        $org = clean_input($_POST['issuing_organization'] ?? '');
        $date = clean_input($_POST['issue_date'] ?? '');
        $rank = clean_input($_POST['rank_achievement'] ?? '');
        $credId = clean_input($_POST['credential_id'] ?? '');
        $verifyUrl = clean_input($_POST['verification_url'] ?? '');
        $desc = clean_input($_POST['description'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 1);

        $imageUrl = 'assets/img/ieee-certificate.svg';
        if ($id > 0) {
            $existing = Database::find('certifications', $id);
            if ($existing && !empty($existing['image_url'])) {
                $imageUrl = $existing['image_url'];
            }
        }

        if (!empty($_FILES['cert_file']['name'])) {
            $upload = Upload::image($_FILES['cert_file'], 'certificates');
            if ($upload['success']) {
                $imageUrl = $upload['file_path'];
            } else {
                set_flash('danger', 'Certificate upload failed: ' . $upload['error']);
            }
        }

        if (!empty($title) && !empty($org)) {
            $data = [
                'title' => $title,
                'certificate_type' => $type,
                'issuing_organization' => $org,
                'issue_date' => $date,
                'rank_achievement' => $rank,
                'credential_id' => $credId,
                'verification_url' => $verifyUrl,
                'image_url' => $imageUrl,
                'description' => $desc,
                'sort_order' => $order
            ];

            if ($id > 0) {
                Database::update('certifications', $data, $id);
                set_flash('success', "Certification '{$title}' updated.");
            } else {
                Database::insert('certifications', $data);
                set_flash('success', "Certification '{$title}' added.");
            }
        }
    }

    if ($action === 'delete_certification') {
        $id = (int)($_POST['cert_id'] ?? 0);
        if ($id > 0) {
            Database::delete('certifications', $id);
            set_flash('success', 'Certification removed.');
        }
    }

    header('Location: certifications.php');
    exit;
}

$certifications = Database::all('certifications', ['sort_order' => 'ASC']);
?>

    <div class="page-header">
        <div>
            <h1>Certifications &amp; Achievements</h1>
            <p>Manage verified credentials, coding challenges, honors, and certificate previews.</p>
        </div>
        <button type="button" class="btn btn-cyan" onclick="document.getElementById('cert-modal').style.display='flex'">
            <i class="fas fa-plus"></i> Add Certification
        </button>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Preview</th>
                        <th>Title &amp; Type</th>
                        <th>Organization</th>
                        <th>Achievement / Rank</th>
                        <th>Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certifications)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-dim);">
                                No certifications configured.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($certifications as $c): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); color: var(--cyan);">#<?= e($c['sort_order']) ?></td>
                                <td>
                                    <img src="../<?= e($c['image_url'] ?? 'assets/img/ieee-certificate.svg') ?>" alt="" style="width: 50px; height: 35px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--admin-border);">
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);"><?= e($c['title']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-dim);"><?= e($c['certificate_type']) ?></div>
                                </td>
                                <td><?= e($c['issuing_organization']) ?></td>
                                <td>
                                    <?php if (!empty($c['rank_achievement'])): ?>
                                        <span class="badge badge-warning"><?= e($c['rank_achievement']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-dim); font-size: 0.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?= e($c['issue_date']) ?></td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='editCert(<?= json_encode($c) ?>)' title="Edit Certificate">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="certifications.php" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_certification">
                                            <input type="hidden" name="cert_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete certificate '<?= e($c['title']) ?>'?" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Certificate Modal -->
    <div id="cert-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="cert-modal-title">Certification Details</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('cert-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="certifications.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_certification">
                <input type="hidden" name="cert_id" id="modal-cert-id" value="0">

                <div class="form-group">
                    <label class="form-label">Certification Title</label>
                    <input type="text" name="title" id="modal-cert-title" class="form-input" placeholder="e.g. IEEE Coding Challenge 2026" required>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">Certificate Type</label>
                        <input type="text" name="certificate_type" id="modal-cert-type" class="form-input" placeholder="Certificate of Participation" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rank / Achievement</label>
                        <input type="text" name="rank_achievement" id="modal-cert-rank" class="form-input" placeholder="Reported Rank: 6th">
                    </div>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1.2fr 0.8fr;">
                    <div class="form-group">
                        <label class="form-label">Issuing Organization</label>
                        <input type="text" name="issuing_organization" id="modal-cert-org" class="form-input" placeholder="IEEE Student Branch – Sanjivani University" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Issue Date</label>
                        <input type="text" name="issue_date" id="modal-cert-date" class="form-input" placeholder="23 September 2026" required>
                    </div>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">Credential ID</label>
                        <input type="text" name="credential_id" id="modal-cert-cred" class="form-input" placeholder="IEEE-SB-2026-CC-06">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Verification URL</label>
                        <input type="url" name="verification_url" id="modal-cert-verify" class="form-input" placeholder="https://...">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Certificate Image</label>
                    <input type="file" name="cert_file" id="modal-cert-file" class="form-input" accept="image/*">
                    <div class="form-help">Supports JPG, PNG, WEBP, SVG.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Summary</label>
                    <textarea name="description" id="modal-cert-desc" class="form-textarea" rows="3" placeholder="Context, algorithmic challenges solved..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="modal-cert-order" class="form-input" value="1" min="1">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('cert-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Certification</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCert(c) {
            document.getElementById('cert-modal-title').textContent = 'Edit Certificate: ' + c.title;
            document.getElementById('modal-cert-id').value = c.id;
            document.getElementById('modal-cert-title').value = c.title;
            document.getElementById('modal-cert-type').value = c.certificate_type;
            document.getElementById('modal-cert-rank').value = c.rank_achievement || '';
            document.getElementById('modal-cert-org').value = c.issuing_organization;
            document.getElementById('modal-cert-date').value = c.issue_date;
            document.getElementById('modal-cert-cred').value = c.credential_id || '';
            document.getElementById('modal-cert-verify').value = c.verification_url || '';
            document.getElementById('modal-cert-desc').value = c.description || '';
            document.getElementById('modal-cert-order').value = c.sort_order || 1;
            document.getElementById('cert-modal').style.display = 'flex';
        }
    </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
