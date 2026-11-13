<?php
/**
 * Admin Skills & Categories Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Skills';
require_once __DIR__ . '/includes/admin-header.php';

// Handle Add / Edit Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: skills.php');
        exit;
    }

    $action = $_POST['action'];

    // 1. Add / Edit Skill
    if ($action === 'save_skill') {
        $skillId = (int)($_POST['skill_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $name = clean_input($_POST['name'] ?? '');
        $level = clean_input($_POST['proficiency_level'] ?? 'Proficient');
        $percent = max(0, min(100, (int)($_POST['proficiency_percent'] ?? 85)));
        $icon = clean_input($_POST['icon_class'] ?? 'fas fa-code');
        $order = (int)($_POST['sort_order'] ?? 1);

        if (!empty($name)) {
            $data = [
                'category_id' => $categoryId,
                'name' => $name,
                'proficiency_level' => $level,
                'proficiency_percent' => $percent,
                'icon_class' => $icon,
                'sort_order' => $order,
                'is_featured' => 1
            ];

            if ($skillId > 0) {
                Database::update('skills', $data, $skillId);
                set_flash('success', "Skill '{$name}' updated.");
            } else {
                Database::insert('skills', $data);
                set_flash('success', "Skill '{$name}' added.");
            }
        }
    }

    // 2. Delete Skill
    if ($action === 'delete_skill') {
        $skillId = (int)($_POST['skill_id'] ?? 0);
        if ($skillId > 0) {
            Database::delete('skills', $skillId);
            set_flash('success', 'Skill deleted.');
        }
    }

    // 3. Add Category
    if ($action === 'add_category') {
        $catName = clean_input($_POST['category_name'] ?? '');
        if (!empty($catName)) {
            $slug = slugify($catName);
            Database::insert('skill_categories', [
                'name' => $catName,
                'slug' => $slug,
                'sort_order' => count(Database::all('skill_categories')) + 1
            ]);
            set_flash('success', "Category '{$catName}' added.");
        }
    }

    // 4. Delete Category
    if ($action === 'delete_category') {
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($catId > 0) {
            Database::delete('skill_categories', $catId);
            set_flash('success', 'Category deleted.');
        }
    }

    header('Location: skills.php');
    exit;
}

$categories = Database::all('skill_categories', ['sort_order' => 'ASC']);
$skills = Database::all('skills', ['sort_order' => 'ASC']);

// Index categories for quick lookup
$catMap = [];
foreach ($categories as $c) {
    $catMap[$c['id']] = $c['name'];
}
?>

    <div class="page-header">
        <div>
            <h1>Skills &amp; Technologies Management</h1>
            <p>Configure technical proficiencies, categories, icons, and proficiency levels without code changes.</p>
        </div>
        <button type="button" class="btn btn-cyan" onclick="document.getElementById('skill-modal').style.display='flex'">
            <i class="fas fa-plus"></i> Add New Skill
        </button>
    </div>

    <!-- Quick Add Category Bar -->
    <div class="admin-card" style="margin-bottom: 2rem;">
        <div class="card-header-flex">
            <h3><i class="fas fa-folder-plus" style="color: var(--cyan);"></i> Skill Categories</h3>
            <span style="font-size: 0.85rem; color: var(--text-sub);"><?= count($categories) ?> Active Categories</span>
        </div>

        <form action="skills.php" method="POST" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_category">
            <input type="text" name="category_name" class="form-input" placeholder="New Category Name (e.g. Cloud & DevOps)" style="max-width: 300px;" required>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </form>

        <div style="display: flex; flex-wrap: wrap; gap: 0.65rem;">
            <?php foreach ($categories as $cat): ?>
                <span class="badge badge-cyan" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.85rem;">
                    <span><?= e($cat['name']) ?></span>
                    <form action="skills.php" method="POST" style="display: inline;" onsubmit="return confirm('Delete category <?= e($cat['name']) ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                        <button type="submit" style="background: none; border: none; color: var(--rose); cursor: pointer; padding: 0; line-height: 1;">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Skills List Table -->
    <div class="admin-card">
        <div class="card-header-flex">
            <h3><i class="fas fa-tools" style="color: var(--cyan);"></i> Active Skills (<?= count($skills) ?>)</h3>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Icon</th>
                        <th>Skill Name</th>
                        <th>Category</th>
                        <th>Proficiency Level</th>
                        <th>Percent</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($skills)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-dim);">
                                No skills added yet. Use the "Add New Skill" button above.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($skills as $s): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); color: var(--cyan);">
                                    #<?= e($s['sort_order'] ?? 1) ?>
                                </td>
                                <td>
                                    <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background: rgba(0,0,0,0.3); border: 1px solid var(--admin-border); display: flex; align-items: center; justify-content: center; color: var(--cyan);">
                                        <i class="<?= e($s['icon_class'] ?? 'fas fa-code') ?>"></i>
                                    </div>
                                </td>
                                <td style="font-weight: 600; color: var(--text-main);">
                                    <?= e($s['name']) ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text-sub);">
                                        <?= e($catMap[$s['category_id']] ?? 'General') ?>
                                    </span>
                                </td>
                                <td>
                                    <?= e($s['proficiency_level'] ?? 'Proficient') ?>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem;">
                                    <?= e($s['proficiency_percent'] ?? 85) ?>%
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='editSkill(<?= json_encode($s) ?>)' title="Edit Skill">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="skills.php" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_skill">
                                            <input type="hidden" name="skill_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete skill '<?= e($s['name']) ?>'?" title="Delete Skill">
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

    <!-- Skill Add / Edit Modal -->
    <div id="skill-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="skill-modal-title">Add New Skill</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('skill-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="skills.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_skill">
                <input type="hidden" name="skill_id" id="modal-skill-id" value="0">

                <div class="form-group">
                    <label for="modal-skill-name" class="form-label">Skill Name</label>
                    <input type="text" name="name" id="modal-skill-name" class="form-input" placeholder="e.g. Python" required>
                </div>

                <div class="form-group">
                    <label for="modal-category-id" class="form-label">Category</label>
                    <select name="category_id" id="modal-category-id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="modal-proficiency-level" class="form-label">Proficiency Level</label>
                        <select name="proficiency_level" id="modal-proficiency-level" class="form-select">
                            <option value="Advanced">Advanced</option>
                            <option value="Proficient" selected>Proficient</option>
                            <option value="Working Knowledge">Working Knowledge</option>
                            <option value="Familiar">Familiar</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-proficiency-percent" class="form-label">Proficiency (0-100%)</label>
                        <input type="number" name="proficiency_percent" id="modal-proficiency-percent" class="form-input" min="10" max="100" value="85">
                    </div>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1.3fr 0.7fr;">
                    <div class="form-group">
                        <label for="modal-icon-class" class="form-label">Font Awesome Icon</label>
                        <input type="text" name="icon_class" id="modal-icon-class" class="form-input" value="fab fa-python" placeholder="fab fa-python">
                        <div class="form-help">e.g. fab fa-java, fab fa-js, fas fa-database</div>
                    </div>

                    <div class="form-group">
                        <label for="modal-sort-order" class="form-label">Display Order</label>
                        <input type="number" name="sort_order" id="modal-sort-order" class="form-input" value="1" min="1">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('skill-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Skill</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editSkill(skill) {
            document.getElementById('skill-modal-title').textContent = 'Edit Skill: ' + skill.name;
            document.getElementById('modal-skill-id').value = skill.id;
            document.getElementById('modal-skill-name').value = skill.name;
            document.getElementById('modal-category-id').value = skill.category_id;
            document.getElementById('modal-proficiency-level').value = skill.proficiency_level || 'Proficient';
            document.getElementById('modal-proficiency-percent').value = skill.proficiency_percent || 85;
            document.getElementById('modal-icon-class').value = skill.icon_class || 'fas fa-code';
            document.getElementById('modal-sort-order').value = skill.sort_order || 1;
            document.getElementById('skill-modal').style.display = 'flex';
        }
    </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
