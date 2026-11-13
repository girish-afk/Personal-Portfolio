<?php
/**
 * Admin Dashboard Overview
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/includes/admin-header.php';

$projectCount = count(Database::all('projects'));
$skillCount = count(Database::all('skills'));
$internshipCount = count(Database::all('internships'));
$inquiries = Database::all('inquiries', ['created_at' => 'DESC']);
$recentInquiries = array_slice($inquiries, 0, 5);
$projects = Database::all('projects', ['sort_order' => 'ASC']);
$recentProjects = array_slice($projects, 0, 4);

$isFallback = Database::isFallback();
?>

    <div class="page-header">
        <div>
            <h1>Command Center Overview</h1>
            <p>Welcome back, <?= e($currentAdmin['username'] ?? 'Girish') ?>. Here is the operational summary of your portfolio.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="project-edit.php" class="btn btn-cyan">
                <i class="fas fa-plus"></i> Add New Project
            </a>
            <a href="backup.php" class="btn btn-secondary">
                <i class="fas fa-download"></i> Backup Data
            </a>
        </div>
    </div>

    <!-- Stat Metrics Row -->
    <div class="admin-grid grid-cols-4" style="margin-bottom: 2rem;">
        <div class="admin-card stat-card">
            <div class="stat-info">
                <div class="stat-value"><?= $projectCount ?></div>
                <div class="stat-label">Active Projects</div>
            </div>
            <div class="stat-icon"><i class="fas fa-code-branch"></i></div>
        </div>

        <div class="admin-card stat-card">
            <div class="stat-info">
                <div class="stat-value"><?= $skillCount ?></div>
                <div class="stat-label">Configured Skills</div>
            </div>
            <div class="stat-icon"><i class="fas fa-tools"></i></div>
        </div>

        <div class="admin-card stat-card">
            <div class="stat-info">
                <div class="stat-value"><?= $internshipCount ?></div>
                <div class="stat-label">Internship Positions</div>
            </div>
            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
        </div>

        <div class="admin-card stat-card">
            <div class="stat-info">
                <div class="stat-value" style="<?= $unreadInquiries > 0 ? 'color: var(--rose);' : '' ?>">
                    <?= $unreadInquiries ?>
                </div>
                <div class="stat-label">Unread Messages</div>
            </div>
            <div class="stat-icon" style="<?= $unreadInquiries > 0 ? 'background: rgba(244, 63, 94, 0.15); color: var(--rose);' : '' ?>">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
    </div>

    <!-- Storage Status Alert Banner -->
    <div class="admin-card" style="margin-bottom: 2rem; border-left: 4px solid <?= $isFallback ? 'var(--amber)' : 'var(--emerald)' ?>;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h4 style="font-size: 1rem; color: var(--text-main); margin-bottom: 0.25rem;">
                    Database Engine: <?= $isFallback ? 'Resilient Server-Side JSON Storage' : 'Active MySQL 8.0 / MariaDB PDO' ?>
                </h4>
                <p style="font-size: 0.85rem; color: var(--text-sub); margin: 0;">
                    <?= $isFallback 
                        ? 'All CMS content and inquiries are persisting safely in data/portfolio_store.json. Once XAMPP MySQL is active, click sync below to transfer records into MySQL tables.'
                        : 'Connected directly to MySQL database `girish_portfolio`. Transactions are executing through PDO.' ?>
                </p>
            </div>
            <?php if ($isFallback): ?>
                <a href="settings.php#database" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sync-alt"></i> Check / Sync MySQL
                </a>
            <?php else: ?>
                <span class="badge badge-success"><i class="fas fa-check"></i> MySQL Online</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2 Column Overview Grids -->
    <div class="admin-grid grid-cols-2">
        <!-- Recent Inquiries -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-inbox" style="color: var(--cyan);"></i> Recent Inquiries</h3>
                <a href="inquiries.php" class="btn btn-secondary btn-sm">View All (<?= count($inquiries) ?>)</a>
            </div>

            <?php if (empty($recentInquiries)): ?>
                <p style="color: var(--text-dim); font-size: 0.9rem; text-align: center; padding: 2rem 0;">No messages received yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Sender</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInquiries as $inq): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?= e($inq['name']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-dim);"><?= e($inq['email']) ?></div>
                                    </td>
                                    <td><?= e(mb_strimwidth($inq['subject'], 0, 25, '...')) ?></td>
                                    <td style="font-family: var(--font-mono); font-size: 0.75rem;"><?= format_display_date($inq['created_at']) ?></td>
                                    <td>
                                        <?php if (empty($inq['is_read'])): ?>
                                            <span class="badge badge-danger">New</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="inquiries.php#msg-<?= $inq['id'] ?>" class="btn btn-secondary btn-sm" title="View Message">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Featured Projects List -->
        <div class="admin-card">
            <div class="card-header-flex">
                <h3><i class="fas fa-project-diagram" style="color: var(--cyan);"></i> Portfolio Projects</h3>
                <a href="projects.php" class="btn btn-secondary btn-sm">Manage (<?= count($projects) ?>)</a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $p): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="../<?= e($p['cover_image'] ?? 'assets/img/projects/agrishield.svg') ?>" alt="" style="width: 36px; height: 36px; border-radius: var(--radius-sm); object-fit: cover;">
                                        <span style="font-weight: 600;"><?= e($p['title']) ?></span>
                                    </div>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-sub);"><?= e(mb_strimwidth($p['category'], 0, 20, '...')) ?></td>
                                <td>
                                    <span class="badge badge-cyan"><?= e($p['status'] ?? 'Active') ?></span>
                                </td>
                                <td>
                                    <a href="project-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Case Study">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
