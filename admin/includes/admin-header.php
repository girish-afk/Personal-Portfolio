<?php
/**
 * Admin Dashboard Header Component
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/admin-auth-check.php';

$currentAdmin = Auth::user();
$unreadInquiries = count(Database::where('inquiries', ['is_read' => 0]));
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?> — Girish Pawar Portfolio CMS</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= APP_VERSION ?>">
</head>
<body>

    <!-- Top Admin Bar -->
    <header class="admin-navbar">
        <div class="admin-nav-inner">
            <div class="admin-brand">
                <img src="../assets/img/logo.svg" alt="Monogram" width="32" height="32">
                <span style="font-family: var(--font-display); font-weight: 700; font-size: 1.05rem;">GIRISH PAWAR</span>
                <span class="badge-admin">CMS CONTROL ROOM</span>
            </div>

            <div class="admin-nav-user">
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm" title="Open live public website">
                    <i class="fas fa-external-link-alt"></i> View Public Site
                </a>
                <div class="user-chip">
                    <i class="fas fa-user-shield"></i>
                    <span><?= e($currentAdmin['username'] ?? 'admin') ?></span>
                </div>
                <a href="logout.php" class="btn btn-danger btn-sm" title="End admin session">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Secondary Admin Navigation Tabs -->
    <nav class="admin-tabs-nav" aria-label="Admin Navigation Tabs">
        <div class="admin-tabs-inner">
            <a href="index.php" class="admin-tab-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Overview
            </a>
            <a href="profile.php" class="admin-tab-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user-edit"></i> Profile & Bio
            </a>
            <a href="projects.php" class="admin-tab-link <?= in_array($currentPage, ['projects.php', 'project-edit.php']) ? 'active' : '' ?>">
                <i class="fas fa-code-branch"></i> Projects
            </a>
            <a href="skills.php" class="admin-tab-link <?= $currentPage === 'skills.php' ? 'active' : '' ?>">
                <i class="fas fa-tools"></i> Skills
            </a>
            <a href="experience.php" class="admin-tab-link <?= $currentPage === 'experience.php' ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i> Experience
            </a>
            <a href="education.php" class="admin-tab-link <?= $currentPage === 'education.php' ? 'active' : '' ?>">
                <i class="fas fa-graduation-cap"></i> Education
            </a>
            <a href="certifications.php" class="admin-tab-link <?= $currentPage === 'certifications.php' ? 'active' : '' ?>">
                <i class="fas fa-certificate"></i> Certifications
            </a>
            <a href="social.php" class="admin-tab-link <?= $currentPage === 'social.php' ? 'active' : '' ?>">
                <i class="fas fa-share-alt"></i> Social Links
            </a>
            <a href="inquiries.php" class="admin-tab-link <?= $currentPage === 'inquiries.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Inquiries
                <?php if ($unreadInquiries > 0): ?>
                    <span class="tab-badge"><?= $unreadInquiries ?></span>
                <?php endif; ?>
            </a>
            <a href="resume.php" class="admin-tab-link <?= $currentPage === 'resume.php' ? 'active' : '' ?>">
                <i class="fas fa-file-pdf"></i> Resume
            </a>
            <a href="settings.php" class="admin-tab-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings & SEO
            </a>
            <a href="backup.php" class="admin-tab-link <?= $currentPage === 'backup.php' ? 'active' : '' ?>">
                <i class="fas fa-database"></i> Backup & Restore
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="admin-container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <div><?= e($flash['message']) ?></div>
            </div>
        <?php endif; ?>
