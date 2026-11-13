<?php
/**
 * Admin Profile & Bio Management
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

$pageTitle = 'Edit Profile & Bio';
require_once __DIR__ . '/includes/admin-header.php';
require_once dirname(__DIR__) . '/includes/upload.php';

$profile = Database::find('profile', 1) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: profile.php');
        exit;
    }

    $fullName = clean_input($_POST['full_name'] ?? '');
    $primaryTitle = clean_input($_POST['primary_title'] ?? '');
    $rawHeadlines = trim($_POST['headline_titles'] ?? '');
    $bioShort = clean_input($_POST['bio_short'] ?? '');
    $bioFull = trim($_POST['bio_full'] ?? '');
    $location = clean_input($_POST['location'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $availableForHire = isset($_POST['available_for_hire']) ? 1 : 0;
    $approachText = trim($_POST['approach_text'] ?? '');
    $learningText = trim($_POST['learning_text'] ?? '');
    $careerGoals = trim($_POST['career_goals'] ?? '');

    // Convert headlines from newline-separated list to array
    $headlinesArray = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $rawHeadlines))));
    if (empty($headlinesArray)) {
        $headlinesArray = ['Web Developer', 'Software Developer', 'Full-Stack Developer'];
    }

    $avatarUrl = $profile['avatar_url'] ?? 'assets/img/girish-avatar.svg';

    // Handle image upload if provided
    if (!empty($_FILES['avatar_file']['name'])) {
        $uploadRes = Upload::image($_FILES['avatar_file'], 'profile');
        if ($uploadRes['success']) {
            $avatarUrl = $uploadRes['file_path'];
        } else {
            set_flash('danger', 'Photo upload failed: ' . $uploadRes['error']);
            header('Location: profile.php');
            exit;
        }
    }

    $updateData = [
        'full_name' => $fullName,
        'primary_title' => $primaryTitle,
        'headline_titles' => $headlinesArray,
        'bio_short' => $bioShort,
        'bio_full' => $bioFull,
        'avatar_url' => $avatarUrl,
        'location' => $location,
        'email' => $email,
        'phone' => $phone,
        'available_for_hire' => $availableForHire,
        'approach_text' => $approachText,
        'learning_text' => $learningText,
        'career_goals' => $careerGoals
    ];

    $saved = Database::update('profile', $updateData, 1);

    if ($saved) {
        set_flash('success', 'Profile and bio details updated successfully.');
    } else {
        set_flash('warning', 'No changes detected or save error.');
    }

    header('Location: profile.php');
    exit;
}

$headlineLines = is_array($profile['headline_titles']) ? implode("\n", $profile['headline_titles']) : '';
?>

    <div class="page-header">
        <div>
            <h1>Personal Profile &amp; Biography</h1>
            <p>Modify public-facing personal data, headline transitions, bio statements, and profile image.</p>
        </div>
        <a href="../index.php#about" target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> View on Live Site
        </a>
    </div>

    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-grid grid-cols-2" style="margin-bottom: 2rem;">
            <!-- Column 1: Core Identity -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h3><i class="fas fa-id-card" style="color: var(--cyan);"></i> Identity &amp; Titles</h3>
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-input" value="<?= e($profile['full_name'] ?? 'Girish Pawar') ?>" required>
                </div>

                <div class="form-group">
                    <label for="primary_title" class="form-label">Primary Professional Title</label>
                    <input type="text" name="primary_title" id="primary_title" class="form-input" value="<?= e($profile['primary_title'] ?? 'Web Developer') ?>" required>
                    <div class="form-help">Displayed under monogram in the header and metadata.</div>
                </div>

                <div class="form-group">
                    <label for="headline_titles" class="form-label">Animated Typing Titles (One per line)</label>
                    <textarea name="headline_titles" id="headline_titles" class="form-textarea" rows="6" required><?= e($headlineLines) ?></textarea>
                    <div class="form-help">These rotate dynamically in the hero typing animation.</div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <input type="checkbox" name="available_for_hire" value="1" <?= !empty($profile['available_for_hire']) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--emerald);">
                        <span style="font-weight: 600; color: var(--text-main);">Mark Available for Opportunities (Pulsing Emerald Badge)</span>
                    </label>
                </div>
            </div>

            <!-- Column 2: Profile Image & Contact Coordinates -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h3><i class="fas fa-image" style="color: var(--cyan);"></i> Portrait &amp; Contact Coordinates</h3>
                </div>

                <div class="form-group" style="display: flex; gap: 1.5rem; align-items: center;">
                    <div style="width: 90px; height: 90px; border-radius: var(--radius-md); overflow: hidden; background: #000; border: 1px solid var(--admin-border); flex-shrink: 0;">
                        <img id="avatar-preview-img" src="../<?= e($profile['avatar_url'] ?? 'assets/img/girish-avatar.svg') ?>" alt="Current Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="flex-grow: 1;">
                        <label for="avatar_file" class="form-label">Upload New Profile Picture</label>
                        <input type="file" name="avatar_file" id="avatar_file" class="form-input" accept="image/*" data-preview="avatar-preview-img">
                        <div class="form-help">Supports JPG, PNG, WEBP, SVG. Max 8MB.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Professional Email</label>
                    <input type="email" name="email" id="email" class="form-input" value="<?= e($profile['email'] ?? 'girish.pawar.dev@gmail.com') ?>" required>
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">Location / Base</label>
                    <input type="text" name="location" id="location" class="form-input" value="<?= e($profile['location'] ?? 'Maharashtra, India') ?>">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                    <input type="text" name="phone" id="phone" class="form-input" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+91 ...">
                </div>
            </div>
        </div>

        <!-- Full Biography & Philosophy -->
        <div class="admin-card" style="margin-bottom: 2rem;">
            <div class="card-header-flex">
                <h3><i class="fas fa-align-left" style="color: var(--cyan);"></i> Biography &amp; Engineering Statements</h3>
            </div>

            <div class="form-group">
                <label for="bio_short" class="form-label">Short Introduction (Hero Section)</label>
                <textarea name="bio_short" id="bio_short" class="form-textarea" rows="3" required><?= e($profile['bio_short'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="bio_full" class="form-label">Comprehensive Biography (About Section)</label>
                <textarea name="bio_full" id="bio_full" class="form-textarea" rows="6" required><?= e($profile['bio_full'] ?? '') ?></textarea>
            </div>

            <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="form-group">
                    <label for="approach_text" class="form-label">Development Approach</label>
                    <textarea name="approach_text" id="approach_text" class="form-textarea" rows="3"><?= e($profile['approach_text'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="learning_text" class="form-label">Currently Learning</label>
                    <textarea name="learning_text" id="learning_text" class="form-textarea" rows="3"><?= e($profile['learning_text'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="career_goals" class="form-label">Career Goals</label>
                    <textarea name="career_goals" id="career_goals" class="form-textarea" rows="3"><?= e($profile['career_goals'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="reset" class="btn btn-secondary">Discard Changes</button>
            <button type="submit" class="btn btn-cyan btn-lg">
                <i class="fas fa-save"></i> Save Profile Changes
            </button>
        </div>
    </form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
