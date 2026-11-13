<?php
/**
 * Public Footer Component
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$profile = $profile ?? (Database::find('profile', 1) ?? []);
$socialLinks = Database::where('social_links', ['is_visible' => 1], ['sort_order' => 'ASC']);
?>
    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <div class="brand-monogram" style="margin-bottom: 0.5rem;">
                        <img src="assets/img/logo.svg" alt="Girish Pawar Logo" width="28" height="28">
                        <span style="font-family: var(--font-display); font-weight: 700; color: var(--text-primary);"><?= e($profile['full_name'] ?? 'Girish Pawar') ?></span>
                    </div>
                    <p class="footer-credits">
                        <?= e(APP_TAGLINE) ?> &bull; &copy; <?= date('Y') ?> All rights reserved.
                    </p>
                </div>

                <ul class="footer-links">
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#projects">Work</a></li>
                    <li><a href="download-resume.php"><i class="fas fa-file-pdf"></i> Resume</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>

                <div class="hero-socials">
                    <?php foreach ($socialLinks as $soc): ?>
                        <a href="<?= e($soc['url']) ?>" target="_blank" rel="noopener noreferrer" class="hero-social-link" title="<?= e($soc['platform']) ?>" aria-label="<?= e($soc['platform']) ?>">
                            <i class="<?= e($soc['icon_class']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Universal Dynamic Modal for Case Studies & Certificate Previews -->
    <div class="modal-overlay" id="main-modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-container">
            <button type="button" class="modal-close-btn" id="modal-close-btn" aria-label="Close dialog">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-content" id="modal-dynamic-content">
                <!-- Injected via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button type="button" class="btn-back-to-top" id="btn-back-to-top" aria-label="Scroll back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Global Toast Container -->
    <div class="toast-container" aria-live="polite"></div>

    <!-- Scripts -->
    <script src="assets/js/main.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
