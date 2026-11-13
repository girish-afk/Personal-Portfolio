<?php
/**
 * Admin Projects Management List
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Projects';
require_once __DIR__ . '/includes/admin-header.php';

// Handle Project Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF validation failed.');
        header('Location: projects.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $deleted = Database::delete('projects', $id);
        if ($deleted) {
            set_flash('success', 'Project removed successfully.');
        } else {
            set_flash('danger', 'Failed to remove project.');
        }
    }
    header('Location: projects.php');
    exit;
}

// Handle Visibility Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_visibility') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $id = (int)($_POST['id'] ?? 0);
        $project = Database::find('projects', $id);
        if ($project) {
            $newVal = empty($project['is_visible']) ? 1 : 0;
            Database::update('projects', ['is_visible' => $newVal], $id);
            set_flash('success', 'Visibility updated.');
        }
    }
    header('Location: projects.php');
    exit;
}

$projects = Database::all('projects', ['sort_order' => 'ASC']);
?>

    <div class="page-header">
        <div>
            <h1>Projects &amp; Case Studies</h1>
            <p>Manage, publish, reorder, and document your technical applications and engineering prototypes.</p>
        </div>
        <a href="project-edit.php" class="btn btn-cyan">
            <i class="fas fa-plus"></i> Add New Project
        </a>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Project</th>
                        <th>Category</th>
                        <th>Tech Stack</th>
                        <th>Status</th>
                        <th>Visibility</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                                No projects recorded. Click "Add New Project" above to create your first case study.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $p): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--cyan);">
                                    #<?= e($p['sort_order'] ?? 1) ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                                        <img src="../<?= e($p['cover_image'] ?? 'assets/img/projects/agrishield.svg') ?>" alt="" style="width: 44px; height: 44px; border-radius: var(--radius-sm); object-fit: cover;">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main);"><?= e($p['title']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-dim); font-family: var(--font-mono);"><?= e($p['slug']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: var(--text-sub);"><?= e($p['category']) ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.3rem; max-width: 250px;">
                                        <?php 
                                            $stack = is_array($p['tech_stack']) ? $p['tech_stack'] : explode(',', $p['tech_stack']);
                                            foreach (array_slice($stack, 0, 3) as $tech): 
                                        ?>
                                            <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-sub); border: 1px solid var(--admin-border); font-size: 0.65rem;">
                                                <?= e(trim($tech)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($stack) > 3): ?>
                                            <span style="font-size: 0.7rem; color: var(--text-dim);">+<?= count($stack) - 3 ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-cyan"><?= e($p['status'] ?? 'Active') ?></span>
                                </td>
                                <td>
                                    <form action="projects.php" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_visibility">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="badge <?= !empty($p['is_visible']) ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                            <?= !empty($p['is_visible']) ? 'Visible' : 'Hidden' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.5rem;">
                                        <a href="project-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Case Study">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="projects.php" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete project '<?= e($p['title']) ?>'? This will permanently remove its case study data." title="Delete Project">
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

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
