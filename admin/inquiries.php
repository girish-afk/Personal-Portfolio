<?php
/**
 * Admin Contact Inquiries Inbox
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Contact Inquiries';
require_once __DIR__ . '/includes/admin-header.php';

// Handle Action (Toggle Read / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: inquiries.php');
        exit;
    }

    $id = (int)($_POST['inquiry_id'] ?? 0);

    if ($_POST['action'] === 'toggle_read' && $id > 0) {
        $inq = Database::find('inquiries', $id);
        if ($inq) {
            $newVal = empty($inq['is_read']) ? 1 : 0;
            Database::update('inquiries', ['is_read' => $newVal], $id);
            set_flash('success', 'Status updated.');
        }
    }

    if ($_POST['action'] === 'delete' && $id > 0) {
        Database::delete('inquiries', $id);
        set_flash('success', 'Message deleted.');
    }

    header('Location: inquiries.php');
    exit;
}

$inquiries = Database::all('inquiries', ['created_at' => 'DESC']);
?>

    <div class="page-header">
        <div>
            <h1>Inbound Opportunities &amp; Messages</h1>
            <p>Direct inquiries submitted by recruiters, collaborators, and clients through your public portfolio.</p>
        </div>
        <div style="font-family: var(--font-mono); font-size: 0.9rem; color: var(--text-sub);">
            Total: <?= count($inquiries) ?> &bull; Unread: <?= $unreadInquiries ?>
        </div>
    </div>

    <div class="admin-grid" style="gap: 1.5rem;">
        <?php if (empty($inquiries)): ?>
            <div class="admin-card" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-dim);">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-dim);"></i>
                <h3 style="color: var(--text-main); margin-bottom: 0.35rem;">Inbox Zero</h3>
                <p>No messages have been submitted through your contact form yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($inquiries as $inq): ?>
                <div class="admin-card" id="msg-<?= $inq['id'] ?>" style="border-left: 4px solid <?= empty($inq['is_read']) ? 'var(--rose)' : 'var(--emerald)' ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span class="badge <?= empty($inq['is_read']) ? 'badge-danger' : 'badge-success' ?>" style="margin-bottom: 0.4rem;">
                                <?= empty($inq['is_read']) ? 'Unread Message' : 'Read' ?>
                            </span>
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);"><?= e($inq['subject']) ?></h3>
                            <div style="font-size: 0.85rem; color: var(--text-sub);">
                                From: <strong style="color: var(--text-main);"><?= e($inq['name']) ?></strong> &bull;
                                <a href="mailto:<?= e($inq['email']) ?>" style="color: var(--cyan);"><?= e($inq['email']) ?></a>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--text-dim); margin-bottom: 0.5rem;">
                                <?= format_display_date($inq['created_at']) ?>
                                <?php if (!empty($inq['ip_address'])): ?>
                                    &bull; IP: <?= e($inq['ip_address']) ?>
                                <?php endif; ?>
                            </div>

                            <div style="display: inline-flex; gap: 0.5rem;">
                                <a href="mailto:<?= e($inq['email']) ?>?subject=Re: <?= urlencode($inq['subject']) ?>" class="btn btn-cyan btn-sm">
                                    <i class="fas fa-reply"></i> Reply
                                </a>

                                <form action="inquiries.php" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_read">
                                    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Toggle Read Status">
                                        <i class="fas <?= empty($inq['is_read']) ? 'fa-check' : 'fa-envelope' ?>"></i>
                                    </button>
                                </form>

                                <form action="inquiries.php" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete" data-confirm="Delete message from <?= e($inq['name']) ?>?" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--admin-border); border-radius: var(--radius-sm); padding: 1.25rem; font-size: 0.92rem; line-height: 1.7; color: var(--text-main); white-space: pre-wrap;">
                        <?= e($inq['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
