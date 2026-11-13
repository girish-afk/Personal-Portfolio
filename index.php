<?php
/**
 * Main Public Portfolio Page
 * Girish Pawar — Personal Developer Portfolio
 */

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/includes/functions.php';

// Load Data from Database / Storage
$profile = Database::find('profile', 1) ?? [];
$skillCategories = Database::all('skill_categories', ['sort_order' => 'ASC']);
$skills = Database::all('skills', ['sort_order' => 'ASC']);
$projects = Database::where('projects', ['is_visible' => 1], ['sort_order' => 'ASC']);
$experienceClaims = Database::all('experience_claims', ['sort_order' => 'ASC']);
$internships = Database::all('internships', ['sort_order' => 'ASC']);
$educationList = Database::all('education', ['sort_order' => 'ASC']);
$certifications = Database::all('certifications', ['sort_order' => 'ASC']);
$socialLinks = Database::where('social_links', ['is_visible' => 1], ['sort_order' => 'ASC']);

// Animated titles json
$headlineTitles = $profile['headline_titles'] ?? [
    'Web Developer',
    'Software Developer',
    'Full-Stack Developer',
    'AI Enthusiast',
    'Cybersecurity Enthusiast',
    'Computer Technology Student'
];

require_once __DIR__ . '/includes/header.php';
?>

    <main id="main-content">
        <!-- ========================================
             A. HOME / HERO SECTION
             ======================================== -->
        <section class="section hero-section" id="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <?php if (!empty($profile['available_for_hire'])): ?>
                            <div class="hero-status-pill">
                                <span class="status-dot"></span>
                                <span>Available for opportunities</span>
                            </div>
                        <?php endif; ?>

                        <h1 class="hero-headline">
                            Hi, I'm <span class="text-gradient"><?= e($profile['full_name'] ?? 'Girish Pawar') ?></span>
                        </h1>

                        <div class="hero-animated-title">
                            <span>I build as a</span>
                            <span id="hero-typed-text" data-titles="<?= e(json_encode($headlineTitles)) ?>"></span>
                            <span class="typed-cursor">|</span>
                        </div>

                        <p class="hero-intro">
                            <?= e($profile['bio_short'] ?? 'I am a Computer Technology professional with a strong interest in software development, web technologies, artificial intelligence, and cybersecurity.') ?>
                        </p>

                        <div class="hero-actions">
                            <a href="#projects" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-down"></i> View My Work
                            </a>
                            <a href="download-resume.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-download"></i> Download Resume
                            </a>
                            <a href="#contact" class="btn btn-outline btn-lg">
                                <i class="fas fa-envelope"></i> Contact Me
                            </a>
                        </div>

                        <div class="hero-socials">
                            <?php foreach ($socialLinks as $soc): ?>
                                <a href="<?= e($soc['url']) ?>" target="_blank" rel="noopener noreferrer" class="hero-social-link" title="<?= e($soc['platform']) ?>" aria-label="<?= e($soc['platform']) ?>">
                                    <i class="<?= e($soc['icon_class']) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="hero-visual">
                        <div class="hero-portrait-card">
                            <div class="hero-avatar-wrapper">
                                <img src="<?= e($profile['avatar_url'] ?? 'assets/img/girish-avatar.svg') ?>" alt="<?= e($profile['full_name'] ?? 'Girish Pawar') ?> Profile">
                            </div>
                            <div class="hero-metric-pill">
                                <span class="metric-val">87.03%</span>
                                <span class="metric-lbl">Academic Standing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================
             B. ABOUT ME SECTION
             ======================================== -->
        <section class="section section-alt" id="about">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">01 // Biography & Philosophy</span>
                    <h2 class="section-title">About Me</h2>
                    <p class="section-description">A look into my engineering background, technical interests, and methodology.</p>
                </div>

                <div class="about-grid">
                    <div class="about-bio">
                        <p>
                            <?= nl2br(e($profile['bio_full'] ?? $profile['bio_short'])) ?>
                        </p>

                        <div class="about-highlights-grid">
                            <div class="highlight-box">
                                <div class="box-icon"><i class="fas fa-code-branch"></i></div>
                                <h4>Engineering Approach</h4>
                                <p><?= e($profile['approach_text'] ?? 'Focusing on clean modular architecture, security-first mindset, and responsive user-centric design.') ?></p>
                            </div>

                            <div class="highlight-box">
                                <div class="box-icon"><i class="fas fa-compass"></i></div>
                                <h4>Current Exploration</h4>
                                <p><?= e($profile['learning_text'] ?? 'Exploring advanced AI agent integration, scalable micro-architectures, and defensive security auditing.') ?></p>
                            </div>

                            <div class="highlight-box">
                                <div class="box-icon"><i class="fas fa-graduation-cap"></i></div>
                                <h4>Academic Foundation</h4>
                                <p>Diploma in Computer Technology at Sanjivani K.B.P. Polytechnic College (87.03%).</p>
                            </div>

                            <div class="highlight-box">
                                <div class="box-icon"><i class="fas fa-bullseye"></i></div>
                                <h4>Career Vision</h4>
                                <p><?= e($profile['career_goals'] ?? 'To engineer resilient, high-impact digital products and contribute to innovative teams.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="about-sidebar">
                        <div class="card-editorial">
                            <h3><i class="fas fa-layer-group"></i> Technical Interests</h3>
                            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.65rem; margin-top: 0.75rem;">
                                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <i class="fas fa-check" style="color: var(--cyan-primary); font-size: 0.75rem;"></i> Full-Stack Web Applications
                                </li>
                                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <i class="fas fa-check" style="color: var(--cyan-primary); font-size: 0.75rem;"></i> Artificial Intelligence & Machine Learning
                                </li>
                                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <i class="fas fa-check" style="color: var(--cyan-primary); font-size: 0.75rem;"></i> Cybersecurity & Network Testing
                                </li>
                                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <i class="fas fa-check" style="color: var(--cyan-primary); font-size: 0.75rem;"></i> Relational Database Engineering (MySQL)
                                </li>
                                <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <i class="fas fa-check" style="color: var(--cyan-primary); font-size: 0.75rem;"></i> Algorithmic Problem Solving
                                </li>
                            </ul>
                        </div>

                        <div class="card-editorial" style="border-left: 3px solid var(--cyan-primary);">
                            <h3><i class="fas fa-file-contract"></i> Resume & Credentials</h3>
                            <p style="margin-bottom: 1.25rem;">Download my updated resume containing verified coursework, technical proficiencies, and project details.</p>
                            <a href="download-resume.php" class="btn btn-secondary btn-sm" style="width: 100%;">
                                <i class="fas fa-download"></i> Download Resume (PDF)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================
             C. SKILLS & TECHNOLOGIES
             ======================================== -->
        <section class="section" id="skills">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">02 // Technical Proficiencies</span>
                    <h2 class="section-title">Skills & Technologies</h2>
                    <p class="section-description">A structured breakdown of programming languages, frontend/backend technologies, databases, cybersecurity tools, and workflows.</p>
                </div>

                <!-- Category Filters -->
                <div class="skills-filter-nav">
                    <button type="button" class="filter-btn active" data-filter="all">All Skills (<?= count($skills) ?>)</button>
                    <?php foreach ($skillCategories as $cat): ?>
                        <button type="button" class="filter-btn" data-filter="<?= e($cat['id']) ?>">
                            <?= e($cat['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Skills Grid -->
                <div class="skills-grid">
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-card" data-category="<?= e($skill['category_id']) ?>">
                            <div class="skill-icon">
                                <i class="<?= e($skill['icon_class'] ?? 'fas fa-code') ?>"></i>
                            </div>
                            <div class="skill-info">
                                <div class="skill-name"><?= e($skill['name']) ?></div>
                                <div class="skill-meta">
                                    <span><?= e($skill['proficiency_level'] ?? 'Proficient') ?></span>
                                    <span><?= e($skill['proficiency_percent'] ?? 85) ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========================================
             D. PROJECTS PORTFOLIO
             ======================================== -->
        <section class="section section-alt" id="projects">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">03 // Featured Case Studies</span>
                    <h2 class="section-title">Projects Portfolio</h2>
                    <p class="section-description">Selected real-world digital solutions, architectural prototypes, and hackathon projects.</p>
                </div>

                <!-- Projects Toolbar: Search & Categories -->
                <div class="projects-toolbar">
                    <div class="skills-filter-nav" style="border: none; margin: 0; padding: 0;">
                        <button type="button" class="filter-btn project-filter-btn active" data-category="all">All Work</button>
                        <button type="button" class="filter-btn project-filter-btn" data-category="AI / Technology / Agriculture">Agriculture</button>
                        <button type="button" class="filter-btn project-filter-btn" data-category="Artificial Intelligence / Education">Education</button>
                        <button type="button" class="filter-btn project-filter-btn" data-category="Space Technology / NASA Space Apps Challenge 2026">Space Tech</button>
                    </div>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="project-search-input" class="search-input" placeholder="Search projects, technologies...">
                    </div>
                </div>

                <!-- Projects Grid -->
                <div class="projects-grid">
                    <?php foreach ($projects as $proj): ?>
                        <?php 
                            $tagsStr = is_array($proj['tech_stack']) ? implode(', ', $proj['tech_stack']) : $proj['tech_stack'];
                            $projJson = json_encode($proj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>
                        <article class="project-card" data-category="<?= e($proj['category']) ?>" data-tags="<?= e($tagsStr) ?>">
                            <div class="project-cover">
                                <img src="<?= e($proj['cover_image'] ?? 'assets/img/projects/agrishield.svg') ?>" alt="<?= e($proj['title']) ?> Cover">
                                <span class="project-category-badge"><?= e($proj['category']) ?></span>
                            </div>

                            <div class="project-body">
                                <h3 class="project-title"><?= e($proj['title']) ?></h3>
                                <p class="project-desc"><?= e($proj['short_description']) ?></p>

                                <div class="project-tags">
                                    <?php if (is_array($proj['tech_stack'])): ?>
                                        <?php foreach ($proj['tech_stack'] as $t): ?>
                                            <span class="tech-tag"><?= e($t) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="project-footer">
                                    <button type="button" class="btn btn-secondary btn-sm btn-open-case-study" data-project='<?= $projJson ?>'>
                                        <i class="fas fa-file-alt"></i> Case Study
                                    </button>

                                    <div class="project-links">
                                        <?php if (!empty($proj['github_url'])): ?>
                                            <a href="<?= e($proj['github_url']) ?>" target="_blank" rel="noopener noreferrer" class="project-link-icon" title="View Source on GitHub">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($proj['demo_url'])): ?>
                                            <a href="<?= e($proj['demo_url']) ?>" target="_blank" rel="noopener noreferrer" class="project-link-icon" title="View Live Demo">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="no-projects-notice" style="display: none; text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; color: var(--text-muted);"></i>
                    <p>No projects match your current filter criteria.</p>
                </div>
            </div>
        </section>

        <!-- ========================================
             E. EXPERIENCE & CAREER JOURNEY
             ======================================== -->
        <section class="section" id="experience">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">04 // Career Journey</span>
                    <h2 class="section-title">Experience & Internships</h2>
                    <p class="section-description">Hands-on technical development experience and verified industry internships.</p>
                </div>

                <div class="experience-layout">
                    <!-- Experience Breakdown Claims -->
                    <div class="experience-column">
                        <h3><i class="fas fa-chart-line"></i> Development Experience</h3>
                        <div class="claims-grid">
                            <?php foreach ($experienceClaims as $claim): ?>
                                <div class="claim-card">
                                    <div class="claim-duration"><?= e($claim['duration']) ?></div>
                                    <div class="claim-area"><?= e($claim['area']) ?></div>
                                    <p class="claim-desc"><?= e($claim['description'] ?? '') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Verified Internships Timeline -->
                    <div class="experience-column">
                        <h3><i class="fas fa-briefcase"></i> Internships</h3>
                        <div class="timeline">
                            <?php foreach ($internships as $intern): ?>
                                <div class="timeline-item">
                                    <span class="timeline-dot"></span>
                                    <div class="timeline-card">
                                        <div class="timeline-header">
                                            <div class="timeline-title"><?= e($intern['domain']) ?> Intern</div>
                                            <div class="timeline-meta"><?= e($intern['start_date'] ?? '2024') ?></div>
                                        </div>
                                        <div class="timeline-company"><?= e($intern['company']) ?> &bull; <?= e($intern['location']) ?></div>
                                        <p class="timeline-desc"><?= e($intern['role_description']) ?></p>
                                        <div class="project-tags">
                                            <?php foreach (explode(',', $intern['technologies']) as $tech): ?>
                                                <span class="tech-tag"><?= e(trim($tech)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================
             F. EDUCATION & G. CERTIFICATIONS
             ======================================== -->
        <section class="section section-alt" id="education">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">05 // Academic & Credentials</span>
                    <h2 class="section-title">Education & Achievements</h2>
                    <p class="section-description">Formal computer technology education and certified competitive accomplishments.</p>
                </div>

                <div class="edu-cert-grid">
                    <!-- Education Card -->
                    <div>
                        <h3 style="font-family: var(--font-display); font-size: 1.35rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; color: var(--text-primary);">
                            <i class="fas fa-graduation-cap" style="color: var(--cyan-primary);"></i> Formal Education
                        </h3>
                        <?php foreach ($educationList as $edu): ?>
                            <div class="edu-card">
                                <span class="edu-badge"><?= e($edu['duration']) ?></span>
                                <h4 class="edu-degree"><?= e($edu['degree']) ?></h4>
                                <div class="edu-inst"><?= e($edu['institution']) ?></div>
                                <div class="edu-score-pill">
                                    <i class="fas fa-award"></i> <?= e($edu['score']) ?>
                                </div>
                                <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;">
                                    <?= e($edu['description'] ?? 'Curriculum focused on software engineering, algorithms, databases, networking, and modern digital development.') ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Certifications & Awards -->
                    <div>
                        <h3 style="font-family: var(--font-display); font-size: 1.35rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; color: var(--text-primary);">
                            <i class="fas fa-certificate" style="color: var(--cyan-primary);"></i> Certifications & Awards
                        </h3>
                        <?php foreach ($certifications as $cert): ?>
                            <div class="cert-card">
                                <?php if (!empty($cert['image_url'])): ?>
                                    <div class="cert-preview-img" data-title="<?= e($cert['title']) ?>" title="Click to inspect certificate">
                                        <img src="<?= e($cert['image_url']) ?>" alt="<?= e($cert['title']) ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="cert-body">
                                    <?php if (!empty($cert['rank_achievement'])): ?>
                                        <div class="cert-rank"><?= e($cert['rank_achievement']) ?></div>
                                    <?php endif; ?>
                                    <h4 class="cert-title"><?= e($cert['title']) ?></h4>
                                    <div class="cert-org"><?= e($cert['issuing_organization']) ?> &bull; <?= e($cert['issue_date']) ?></div>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                                        <?= e($cert['description'] ?? '') ?>
                                    </p>
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
                                        <span><?= e($cert['certificate_type']) ?></span>
                                        <?php if (!empty($cert['verification_url'])): ?>
                                            <a href="<?= e($cert['verification_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--cyan-primary);">Verify <i class="fas fa-external-link-alt"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================
             I. CONTACT SECTION
             ======================================== -->
        <section class="section" id="contact">
            <div class="container">
                <div class="section-header">
                    <span class="section-label">06 // Connect & Collaborate</span>
                    <h2 class="section-title">Get In Touch</h2>
                    <p class="section-description">Have an opportunity, project collaboration, or technical inquiry? Send a message directly.</p>
                </div>

                <div class="contact-grid">
                    <!-- Contact Info & Quick Reach -->
                    <div>
                        <div class="contact-card">
                            <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--text-primary); margin-bottom: 0.75rem;">
                                Let's Build Something Impactful
                            </h3>
                            <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6;">
                                I am actively interested in software development, web engineering, AI solutions, and collaborative technical challenges. Feel free to reach out via email or through the contact form.
                            </p>

                            <ul class="contact-info-list">
                                <li class="contact-info-item">
                                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                                    <div class="contact-info-text">
                                        <div class="label">Professional Email</div>
                                        <div class="val">
                                            <?= e($profile['email'] ?? 'girish.pawar.dev@gmail.com') ?>
                                            <button type="button" class="btn-copy-email" data-email="<?= e($profile['email'] ?? 'girish.pawar.dev@gmail.com') ?>" title="Copy Email to Clipboard">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>

                                <li class="contact-info-item">
                                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="contact-info-text">
                                        <div class="label">Location</div>
                                        <div class="val"><?= e($profile['location'] ?? 'Maharashtra, India') ?></div>
                                    </div>
                                </li>

                                <li class="contact-info-item">
                                    <div class="contact-info-icon"><i class="fas fa-signal"></i></div>
                                    <div class="contact-info-text">
                                        <div class="label">Availability Status</div>
                                        <div class="val" style="color: var(--emerald-primary);">
                                            <?= !empty($profile['available_for_hire']) ? 'Open to Freelance & Full-Time Roles' : 'Occupied with current projects' ?>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Functional Contact Form -->
                    <div>
                        <div class="contact-card">
                            <form id="portfolio-contact-form" action="contact.php" method="POST" novalidate>
                                <?= csrf_field() ?>

                                <!-- Anti-Spam Honeypot Field (Invisible to real users) -->
                                <div style="display: none;" aria-hidden="true">
                                    <label for="website_hp">Leave this empty</label>
                                    <input type="text" name="website_hp" id="website_hp" tabindex="-1" autocomplete="off">
                                </div>

                                <div class="form-group">
                                    <label for="contact-name" class="form-label">Your Name <span style="color: var(--cyan-primary);">*</span></label>
                                    <input type="text" name="name" id="contact-name" class="form-control" placeholder="e.g. Jane Doe" required>
                                </div>

                                <div class="form-group">
                                    <label for="contact-email" class="form-label">Email Address <span style="color: var(--cyan-primary);">*</span></label>
                                    <input type="email" name="email" id="contact-email" class="form-control" placeholder="name@domain.com" required>
                                </div>

                                <div class="form-group">
                                    <label for="contact-subject" class="form-label">Subject <span style="color: var(--cyan-primary);">*</span></label>
                                    <input type="text" name="subject" id="contact-subject" class="form-control" placeholder="Project Inquiry / Job Opportunity" required>
                                </div>

                                <div class="form-group">
                                    <label for="contact-message" class="form-label">Message <span style="color: var(--cyan-primary);">*</span></label>
                                    <textarea name="message" id="contact-message" class="form-control" rows="5" placeholder="Share project details, timelines, or your message..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
