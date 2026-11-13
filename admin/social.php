<?php
/**
 * Admin Social Media & Professional Links Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Manage Social Links';
require_once __DIR__ . '/includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: social.php');
        exit;
    }

    $action = $_POST['action'];

    // Save Social Link
    if ($action === 'save_link') {
        $id = (int)($_POST['link_id'] ?? 0);
        $platform = clean_input($_POST['platform'] ?? '');
        $label = clean_input($_POST['display_label'] ?? '');
        $url = clean_input($_POST['url'] ?? '');
        $icon = clean_input($_POST['icon_class'] ?? 'fab fa-github');
        $visible = isset($_POST['is_visible']) ? 1 : 0;
        $order = (int)($_POST['sort_order'] ?? 1);

        if (!empty($platform) && !empty($url)) {
            $data = [
                'platform' => $platform,
                'display_label' => $label ?: $platform,
                'url' => $url,
                'icon_class' => $icon,
                'is_visible' => $visible,
                'sort_order' => $order
            ];

            if ($id > 0) {
                Database::update('social_links', $data, $id);
                set_flash('success', "Social link '{$platform}' updated.");
            } else {
                Database::insert('social_links', $data);
                set_flash('success', "Social link '{$platform}' added.");
            }
        }
    }

    // Toggle Visibility
    if ($action === 'toggle_visibility') {
        $id = (int)($_POST['link_id'] ?? 0);
        $link = Database::find('social_links', $id);
        if ($link) {
            $newVal = empty($link['is_visible']) ? 1 : 0;
            Database::update('social_links', ['is_visible' => $newVal], $id);
            set_flash('success', 'Visibility updated.');
        }
    }

    // Delete Link
    if ($action === 'delete_link') {
        $id = (int)($_POST['link_id'] ?? 0);
        if ($id > 0) {
            Database::delete('social_links', $id);
            set_flash('success', 'Social profile removed.');
        }
    }

    header('Location: social.php');
    exit;
}

$socialLinks = Database::all('social_links', ['sort_order' => 'ASC']);
?>

    <div class="page-header">
        <div>
            <h1>Social Media &amp; Professional Profiles</h1>
            <p>Manage external professional handles, portfolio links, icons, and visibility.</p>
        </div>
        <button type="button" class="btn btn-cyan" onclick="document.getElementById('social-modal').style.display='flex'">
            <i class="fas fa-plus"></i> Add Profile Link
        </button>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Icon</th>
                        <th>Platform</th>
                        <th>Display Handle / Label</th>
                        <th>URL</th>
                        <th>Visibility</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($socialLinks)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-dim);">
                                No links configured.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($socialLinks as $s): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); color: var(--cyan);">#<?= e($s['sort_order']) ?></td>
                                <td>
                                    <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background: rgba(0,0,0,0.3); border: 1px solid var(--admin-border); display: flex; align-items: center; justify-content: center; color: var(--cyan);">
                                        <i class="<?= e($s['icon_class']) ?>"></i>
                                    </div>
                                </td>
                                <td style="font-weight: 700; color: var(--text-main);"><?= e($s['platform']) ?></td>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem;"><?= e($s['display_label']) ?></td>
                                <td style="font-size: 0.82rem; color: var(--text-sub); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <a href="<?= e($s['url']) ?>" target="_blank" style="color: var(--cyan);"><?= e($s['url']) ?></a>
                                </td>
                                <td>
                                    <form action="social.php" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_visibility">
                                        <input type="hidden" name="link_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="badge <?= !empty($s['is_visible']) ? 'badge-success' : 'badge-danger' ?>" style="border: none; cursor: pointer;">
                                            <?= !empty($s['is_visible']) ? 'Visible' : 'Hidden' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.4rem;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='editSocial(<?= json_encode($s) ?>)' title="Edit Profile">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="social.php" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_link">
                                            <input type="hidden" name="link_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Remove link to '<?= e($s['platform']) ?>'?" title="Delete">
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

    <!-- Social Modal -->
    <div id="social-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="admin-card" style="width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <div class="card-header-flex">
                <h3 id="social-modal-title">Social Profile Link</h3>
                <button type="button" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.25rem;" onclick="document.getElementById('social-modal').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="social.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_link">
                <input type="hidden" name="link_id" id="modal-link-id" value="0">

                <div class="form-group">
                    <label class="form-label">Platform Name</label>
                    <input type="text" name="platform" id="modal-link-platform" class="form-input" placeholder="e.g. GitHub, LinkedIn, YouTube" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Display Handle / Text</label>
                    <input type="text" name="display_label" id="modal-link-label" class="form-input" placeholder="e.g. girish-afk" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Destination URL</label>
                    <input type="url" name="url" id="modal-link-url" class="form-input" placeholder="https://..." required>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1.2fr 0.8fr;">
                    <div class="form-group">
                        <label class="form-label">Font Awesome Icon</label>
                        <input type="text" name="icon_class" id="modal-link-icon" class="form-input" placeholder="fab fa-github" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="modal-link-order" class="form-input" value="1" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_visible" id="modal-link-visible" value="1" checked style="width: 16px; height: 16px; accent-color: var(--emerald);">
                        <span style="font-weight: 600;">Visible on Public Portfolio</span>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('social-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-cyan">Save Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editSocial(s) {
            document.getElementById('social-modal-title').textContent = 'Edit Profile: ' + s.platform;
            document.getElementById('modal-link-id').value = s.id;
            document.getElementById('modal-link-platform').value = s.platform;
            document.getElementById('modal-link-label').value = s.display_label;
            document.getElementById('modal-link-url').value = s.url;
            document.getElementById('modal-link-icon').value = s.icon_class;
            document.getElementById('modal-link-order').value = s.sort_order || 1;
            document.getElementById('modal-link-visible').checked = Boolean(Number(s.is_visible));
            document.getElementById('social-modal').style.display = 'flex';
        }
    </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
