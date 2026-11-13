<?php
/**
 * Public Header Component
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/database.php';
require_once __DIR__ . '/functions.php';

$profile = Database::find('profile', 1) ?? [];
$siteTitle = Database::getSetting('site_title', 'Girish Pawar — Personal Developer Portfolio');
$metaDesc = Database::getSetting('meta_description', 'Official portfolio of Girish Pawar: Web Developer, Full-Stack Developer, and Computer Technology Student.');
$metaKeys = Database::getSetting('meta_keywords', 'Girish Pawar, Web Developer, Full-Stack Developer, Computer Technology, AgriShield, AURA AI, PROJECT ASTRO');

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
$currentUrl = "{$protocol}://{$host}{$uri}";
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?= e($siteTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="keywords" content="<?= e($metaKeys) ?>">
    <meta name="author" content="<?= e($profile['full_name'] ?? 'Girish Pawar') ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($currentUrl) ?>">
    <meta property="og:title" content="<?= e($siteTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:image" content="assets/img/girish-avatar.svg">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($siteTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDesc) ?>">
    <meta name="twitter:image" content="assets/img/girish-avatar.svg">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Core Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=mobile20261112">
</head>
<body>

    <!-- Site Header & Navigation -->
    <header class="site-header">
        <div class="container">
            <nav class="nav-inner" aria-label="Main Navigation">
                <a href="#hero" class="brand-monogram">
                    <img src="assets/img/logo.svg" alt="Girish Pawar Monogram Logo" width="36" height="36">
                    <div class="brand-name">
                        <span class="name"><?= e($profile['full_name'] ?? 'Girish Pawar') ?></span>
                        <span class="role-tag"><?= e($profile['primary_title'] ?? 'Web Developer') ?></span>
                    </div>
                </a>

                <ul class="nav-links" id="primary-nav-links">
                    <li class="nav-item"><a href="#hero" class="active">Home</a></li>
                    <li class="nav-item"><a href="#about">About</a></li>
                    <li class="nav-item"><a href="#skills">Skills</a></li>
                    <li class="nav-item"><a href="#projects">Projects</a></li>
                    <li class="nav-item"><a href="#experience">Experience</a></li>
                    <li class="nav-item"><a href="#education">Education</a></li>
                    <li class="nav-item"><a href="#contact">Contact</a></li>
                </ul>

                <div class="nav-actions">
                    <button type="button" class="btn-theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme Mode">
                        <i class="fas fa-sun"></i>
                    </button>
                    <a href="#contact" class="btn btn-primary btn-sm">Get In Touch</a>
                    <button type="button" class="btn-mobile-toggle" id="mobile-nav-toggle" aria-label="Toggle Navigation Menu" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </nav>
        </div>
    </header>
