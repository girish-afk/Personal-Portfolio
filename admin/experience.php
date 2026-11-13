<?php
/**
 * Admin Experience & Internships Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Experience';
require_once __DIR__ . '/includes/admin-header.php';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: experience.php');
        exit;
    }

    $action = $_POST['action'];

    // 1. Save Experience Claim
    if ($action === 'save_claim') {
        $id = (int)($_POST['claim_id'] ?? 0);
        $area = clean_input($_POST['area'] ?? '');
        $duration = clean_input($_POST['duration'] ?? '');
        $desc = clean_input($_POST['description'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 1);

        if (!empty($area) && !empty($duration)) {
            $data = [
                'area' => $area,
                'duration' => $duration,
                'description' => $desc,
                'sort_order' => $order
            ];

            if ($id > 0) {
                Database::update('experience_claims', $data, $id);
                set_flash('success', "Experience claim '{$area}' updated.");
            } else {
                Database::insert('experience_claims', $data);
                set_flash('success', "Experience claim '{$area}' added.");
            }
        }
    }

    // 2. Delete Experience Claim
    if ($action === 'delete_claim') {
        $id = (int)($_POST['claim_id'] ?? 0);
        if ($id > 0) {
            Database::delete('experience_claims', $id);
            set_flash('success', 'Experience claim deleted.');
        }
    }

    // 3. Save Internship
    if ($action === 'save_internship') {
        $id = (int)($_POST['internship_id'] ?? 0);
        $company = clean_input($_POST['company'] ?? '');
        $location = clean_input($_POST['location'] ?? '');
        $domain = clean_input($_POST['domain'] ?? '');
        $tech = clean_input($_POST['technologies'] ?? '');
        $role = clean_input($_POST['role_description'] ?? '');
        $startDate = clean_input($_POST['start_date'] ?? '');
        $endDate = clean_input($_POST['end_date'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 1);

        if (!empty($company) && !empty($domain)) {
            $data = [
                'company' => $company,
                'location' => $location,
                'domain' => $domain,
                'technologies' => $tech,
                'role_description' => $role,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sort_order' => $order
            ];

            if ($id > 0) {
                Database::update('internships', $data, $id);
                set_flash('success', "Internship at '{$company}' updated.");
            } else {
                Database::insert('internships', $data);
                set_flash('success', "Internship at '{$company}' added.");
            }
        }
    }

    // 4. Delete Internship
    if ($action === 'delete_internship') {
        $id = (int)($_POST['internship_id'] ?? 0);
        if ($id > 0) {
            Database::delete('internships', $id);
            set_flash('success', 'Internship record deleted.');
        }
    }

    header('Location: experience.php');
    exit;
}

$claims = Database::all('experience_claims', ['sort_order' => 'ASC']);
$internships = Database::all('internships', ['sort_order' => 'ASC']);
?>

    <div class="page-header">
        <div>
            <h1>Experience &amp; Internships Management</h1>
            <p>Update development duration claims and industry internships with roles and technologies.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('claim-modal').style.display='flex'">
                <i class="fas fa-plus"></i> Add Experience Claim
            </button>
            <button type="button" class="btn btn-cyan" onclick="document.getElementById('internship-modal').style.display='flex'">
                <i class="fas fa-briefcase"></i> Add Internship
            </button>
        </div>
    </div>

    <!-- Experience Claims Table -->
    <div class="admin-card" style="margin-bottom: 2rem;">
        <div class="card-header-flex">
            <h3><i class="fas fa-chart-line" style="color: var(--cyan);"></i> Experience Claims (Duration Overview)</h3>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Domain / Area</th>
                        <th>Duration Claim</th>
                        <th>Description</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $c): ?>
                        <tr>
                            <td style="font-family: var(--font-mono); color: var(--cyan);">#<?= e($c['sort_order']) ?></td>
                            <td style="font-weight: 700; color: var(--text-main);"><?= e($c['area']) ?></td>
                            <td><span class="badge badge-cyan"><?= e($c['duration']) ?></span></td>
                            <td style="font-size: 0.85rem; color: var(--text-sub); max-width: 380px;"><?= e($c['description'] ?? '') ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick='editClaim(<?= json_encode($c) ?>)' title="Edit Claim">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="experience.php" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_claim">
                                        <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete claim '<?= e($c['area']) ?>'?" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Internships Table -->
    <div class="admin-card">
        <div class="card-header-flex">
            <h3><i class="fas fa-briefcase" style="color: var(--cyan);"></i> Industry Internships</h3>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Domain</th>
                        <th>Technologies</th>
                        <th>Dates</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($internships as $i): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--text-main);"><?= e($i['company']) ?></td>
                            <td><?= e($i['location']) ?></td>
                            <td><span class="badge badge-success"><?= e($i['domain']) ?></span></td>
                            <td style="font-size: 0.85rem; color: var(--text-sub);"><?= e($i['technologies']) ?></td>
                            <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?= e($i['start_date']) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick='editInternship(<?= json_encode($i) ?>)' title="Edit Internship">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="experience.php" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_internship">
                                        <input type="hidden" name="internship_id" value="<?= $i['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete internship at '<?= e($i['company']) ?>'?" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Claim Modal -->
    <div id="claim-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="claim-modal-title">Experience Claim</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('claim-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="experience.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_claim">
                <input type="hidden" name="claim_id" id="modal-claim-id" value="0">

                <div class="form-group">
                    <label class="form-label">Area / Specialization</label>
                    <input type="text" name="area" id="modal-claim-area" class="form-input" placeholder="e.g. UI/UX Design" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" id="modal-claim-duration" class="form-input" placeholder="e.g. 2 Years" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="modal-claim-desc" class="form-textarea" rows="3" placeholder="Summary of responsibilities..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="modal-claim-order" class="form-input" value="1" min="1">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('claim-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Claim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Internship Modal -->
    <div id="internship-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="internship-modal-title">Internship Position</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('internship-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="experience.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_internship">
                <input type="hidden" name="internship_id" id="modal-intern-id" value="0">

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company" id="modal-intern-company" class="form-input" placeholder="TechKing Solutions India" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="modal-intern-location" class="form-input" placeholder="Shirdi" required>
                    </div>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">Domain</label>
                        <input type="text" name="domain" id="modal-intern-domain" class="form-input" placeholder="Web Development / Cybersecurity" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dates / Year</label>
                        <input type="text" name="start_date" id="modal-intern-date" class="form-input" placeholder="2024">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technologies (Comma-separated)</label>
                    <input type="text" name="technologies" id="modal-intern-tech" class="form-input" placeholder="HTML, CSS, JavaScript, Bootstrap" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role &amp; Responsibilities</label>
                    <textarea name="role_description" id="modal-intern-role" class="form-textarea" rows="3" placeholder="Responsive UI development..." required></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('internship-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Internship</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editClaim(c) {
            document.getElementById('claim-modal-title').textContent = 'Edit Experience Claim: ' + c.area;
            document.getElementById('modal-claim-id').value = c.id;
            document.getElementById('modal-claim-area').value = c.area;
            document.getElementById('modal-claim-duration').value = c.duration;
            document.getElementById('modal-claim-desc').value = c.description || '';
            document.getElementById('modal-claim-order').value = c.sort_order || 1;
            document.getElementById('claim-modal').style.display = 'flex';
        }

        function editInternship(i) {
            document.getElementById('internship-modal-title').textContent = 'Edit Internship: ' + i.company;
            document.getElementById('modal-intern-id').value = i.id;
            document.getElementById('modal-intern-company').value = i.company;
            document.getElementById('modal-intern-location').value = i.location;
            document.getElementById('modal-intern-domain').value = i.domain;
            document.getElementById('modal-intern-date').value = i.start_date || '';
            document.getElementById('modal-intern-tech').value = i.technologies || '';
            document.getElementById('modal-intern-role').value = i.role_description || '';
            document.getElementById('internship-modal').style.display = 'flex';
        }
    </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
