<?php
/**
 * Admin Education Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Education';
require_once __DIR__ . '/includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: education.php');
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'save_education') {
        $id = (int)($_POST['education_id'] ?? 0);
        $degree = clean_input($_POST['degree'] ?? '');
        $inst = clean_input($_POST['institution'] ?? '');
        $score = clean_input($_POST['score'] ?? '');
        $duration = clean_input($_POST['duration'] ?? '');
        $desc = clean_input($_POST['description'] ?? '');
        $order = (int)($_POST['sort_order'] ?? 1);

        if (!empty($degree) && !empty($inst)) {
            $data = [
                'degree' => $degree,
                'institution' => $inst,
                'score' => $score,
                'duration' => $duration,
                'description' => $desc,
                'sort_order' => $order
            ];

            if ($id > 0) {
                Database::update('education', $data, $id);
                set_flash('success', "Education entry '{$degree}' updated.");
            } else {
                Database::insert('education', $data);
                set_flash('success', "Education entry '{$degree}' added.");
            }
        }
    }

    if ($action === 'delete_education') {
        $id = (int)($_POST['education_id'] ?? 0);
        if ($id > 0) {
            Database::delete('education', $id);
            set_flash('success', 'Education record removed.');
        }
    }

    header('Location: education.php');
    exit;
}

$education = Database::all('education', ['sort_order' => 'ASC']);
?>

    <div class="page-header">
        <div>
            <h1>Academic Education Management</h1>
            <p>Update degrees, institutions, percentages, academic durations, and coursework.</p>
        </div>
        <button type="button" class="btn btn-cyan" onclick="document.getElementById('edu-modal').style.display='flex'">
            <i class="fas fa-plus"></i> Add Education Record
        </button>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Degree / Diploma</th>
                        <th>Institution</th>
                        <th>Academic Score</th>
                        <th>Duration</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($education)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-dim);">
                                No education records configured.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($education as $e): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); color: var(--cyan);">#<?= e($e['sort_order']) ?></td>
                                <td style="font-weight: 700; color: var(--text-main);"><?= e($e['degree']) ?></td>
                                <td><?= e($e['institution']) ?></td>
                                <td><span class="badge badge-success"><?= e($e['score']) ?></span></td>
                                <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?= e($e['duration']) ?></td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='editEdu(<?= json_encode($e) ?>)' title="Edit Record">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="education.php" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_education">
                                            <input type="hidden" name="education_id" value="<?= $e['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete '<?= e($e['degree']) ?>'?" title="Delete">
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

    <!-- Education Modal -->
    <div id="edu-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="edu-modal-title">Education Record</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('edu-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="education.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_education">
                <input type="hidden" name="education_id" id="modal-edu-id" value="0">

                <div class="form-group">
                    <label class="form-label">Degree / Qualification</label>
                    <input type="text" name="degree" id="modal-edu-degree" class="form-input" placeholder="Diploma in Computer Technology" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Institution / College</label>
                    <input type="text" name="institution" id="modal-edu-inst" class="form-input" placeholder="Sanjivani K.B.P. Polytechnic College" required>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label class="form-label">Score / Percentage</label>
                        <input type="text" name="score" id="modal-edu-score" class="form-input" placeholder="Percentage: 87.03%" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" id="modal-edu-duration" class="form-input" placeholder="August 2022 – June 2025" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Curriculum / Details</label>
                    <textarea name="description" id="modal-edu-desc" class="form-textarea" rows="3" placeholder="Key focus areas, coursework..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="modal-edu-order" class="form-input" value="1" min="1">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('edu-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Education</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editEdu(e) {
            document.getElementById('edu-modal-title').textContent = 'Edit Education: ' + e.degree;
            document.getElementById('modal-edu-id').value = e.id;
            document.getElementById('modal-edu-degree').value = e.degree;
            document.getElementById('modal-edu-inst').value = e.institution;
            document.getElementById('modal-edu-score').value = e.score;
            document.getElementById('modal-edu-duration').value = e.duration;
            document.getElementById('modal-edu-desc').value = e.description || '';
            document.getElementById('modal-edu-order').value = e.sort_order || 1;
            document.getElementById('edu-modal').style.display = 'flex';
        }
    </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
