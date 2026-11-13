<?php
/**
 * Admin Project Create & Case Study Editor
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/includes/admin-auth-check.php';
require_once dirname(__DIR__) . '/includes/upload.php';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEditing = ($projectId > 0);
$project = $isEditing ? Database::find('projects', $projectId) : null;

if ($isEditing && !$project) {
    set_flash('danger', 'Project not found.');
    header('Location: projects.php');
    exit;
}

$pageTitle = $isEditing ? 'Edit Project: ' . e($project['title']) : 'Create New Project';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'CSRF verification failed.');
        header('Location: projects.php');
        exit;
    }

    $title = clean_input($_POST['title'] ?? '');
    $slug = slugify($_POST['slug'] ?? $title);
    $category = clean_input($_POST['category'] ?? '');
    $shortDesc = clean_input($_POST['short_description'] ?? '');
    $techStackRaw = clean_input($_POST['tech_stack'] ?? '');
    $githubUrl = clean_input($_POST['github_url'] ?? '');
    $demoUrl = clean_input($_POST['demo_url'] ?? '');
    $status = clean_input($_POST['status'] ?? 'Active');
    $devDate = clean_input($_POST['development_date'] ?? date('Y'));
    $keyFeatures = trim($_POST['key_features'] ?? '');
    $challenges = trim($_POST['challenges'] ?? '');
    $solutions = trim($_POST['solutions'] ?? '');
    $results = trim($_POST['results'] ?? '');
    $caseStudy = trim($_POST['case_study_content'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 1);
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    $techStackArray = array_values(array_filter(array_map('trim', explode(',', $techStackRaw))));

    $coverImage = $project['cover_image'] ?? 'assets/img/projects/agrishield.svg';

    if (!empty($_FILES['cover_file']['name'])) {
        $upload = Upload::image($_FILES['cover_file'], 'projects');
        if ($upload['success']) {
            $coverImage = $upload['file_path'];
        } else {
            set_flash('danger', 'Cover image upload error: ' . $upload['error']);
        }
    }

    $data = [
        'title' => $title,
        'slug' => $slug,
        'category' => $category,
        'short_description' => $shortDesc,
        'cover_image' => $coverImage,
        'logo_url' => '',
        'tech_stack' => $techStackArray,
        'github_url' => $githubUrl,
        'demo_url' => $demoUrl,
        'status' => $status,
        'development_date' => $devDate,
        'key_features' => $keyFeatures,
        'challenges' => $challenges,
        'solutions' => $solutions,
        'results' => $results,
        'case_study_content' => $caseStudy,
        'screenshots' => $project['screenshots'] ?? [],
        'sort_order' => $sortOrder,
        'is_visible' => $isVisible,
        'is_featured' => $isFeatured
    ];

    if ($isEditing) {
        Database::update('projects', $data, $projectId);
        set_flash('success', 'Project updated successfully.');
    } else {
        $newId = Database::insert('projects', $data);
        set_flash('success', 'Project published successfully.');
    }

    header('Location: projects.php');
    exit;
}

$stackString = '';
if ($project && isset($project['tech_stack'])) {
    $stackString = is_array($project['tech_stack']) ? implode(', ', $project['tech_stack']) : $project['tech_stack'];
}

require_once __DIR__ . '/includes/admin-header.php';
?>

    <div class="page-header">
        <div>
            <h1><?= $isEditing ? 'Edit Project Case Study' : 'Author New Case Study' ?></h1>
            <p><?= $isEditing ? 'Refine architecture metrics, challenge/solution records, and URLs.' : 'Add a new real-world engineering project or hackathon build.' ?></p>
        </div>
        <a href="projects.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    <form action="project-edit.php<?= $isEditing ? '?id=' . $projectId : '' ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-grid grid-cols-2" style="margin-bottom: 2rem;">
            <!-- Main Details -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h3><i class="fas fa-info-circle" style="color: var(--cyan);"></i> Project Metadata</h3>
                </div>

                <div class="form-group">
                    <label for="title" class="form-label">Project Title <span style="color: var(--cyan);">*</span></label>
                    <input type="text" name="title" id="title" class="form-input" value="<?= e($project['title'] ?? '') ?>" placeholder="e.g. AgriShield" required>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category <span style="color: var(--cyan);">*</span></label>
                    <input type="text" name="category" id="category" class="form-input" value="<?= e($project['category'] ?? 'AI / Technology') ?>" placeholder="e.g. AI / Technology / Agriculture" required>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">URL Slug</label>
                    <input type="text" name="slug" id="slug" class="form-input" value="<?= e($project['slug'] ?? '') ?>" placeholder="agrishield">
                    <div class="form-help">Leave empty to generate automatically from title.</div>
                </div>

                <div class="form-group">
                    <label for="tech_stack" class="form-label">Tech Stack (Comma-separated) <span style="color: var(--cyan);">*</span></label>
                    <input type="text" name="tech_stack" id="tech_stack" class="form-input" value="<?= e($stackString) ?>" placeholder="Python, Machine Learning, PHP, MySQL" required>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" id="status" class="form-input" value="<?= e($project['status'] ?? 'Active') ?>" placeholder="Completed / Active / Hackathon">
                    </div>

                    <div class="form-group">
                        <label for="development_date" class="form-label">Development Date / Year</label>
                        <input type="text" name="development_date" id="development_date" class="form-input" value="<?= e($project['development_date'] ?? '2024') ?>" placeholder="2024">
                    </div>
                </div>

                <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="sort_order" class="form-label">Display Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-input" value="<?= e($project['sort_order'] ?? 1) ?>" min="1">
                    </div>

                    <div class="form-group" style="padding-top: 1.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_visible" value="1" <?= (!isset($project['is_visible']) || $project['is_visible']) ? 'checked' : '' ?> style="width: 16px; height: 16px; accent-color: var(--emerald);">
                            <span style="font-size: 0.9rem; font-weight: 600;">Visible to Public</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Cover Image & External Links -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h3><i class="fas fa-image" style="color: var(--cyan);"></i> Visuals &amp; External URLs</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Graphic Preview</label>
                    <div style="width: 100%; height: 160px; border-radius: var(--radius-md); overflow: hidden; background: #000; border: 1px solid var(--admin-border); margin-bottom: 0.75rem;">
                        <img id="project-cover-preview" src="../<?= e($project['cover_image'] ?? 'assets/img/projects/agrishield.svg') ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <label for="cover_file" class="form-label">Upload New Cover Image</label>
                    <input type="file" name="cover_file" id="cover_file" class="form-input" accept="image/*" data-preview="project-cover-preview">
                    <div class="form-help">Recommended ratio 16:10. Supports JPG, PNG, WEBP, SVG.</div>
                </div>

                <div class="form-group">
                    <label for="github_url" class="form-label"><i class="fab fa-github"></i> GitHub Repository URL</label>
                    <input type="url" name="github_url" id="github_url" class="form-input" value="<?= e($project['github_url'] ?? '') ?>" placeholder="https://github.com/girish-afk/...">
                    <div class="form-help">Leave empty until real repository link is available.</div>
                </div>

                <div class="form-group">
                    <label for="demo_url" class="form-label"><i class="fas fa-external-link-alt"></i> Live Demo URL</label>
                    <input type="url" name="demo_url" id="demo_url" class="form-input" value="<?= e($project['demo_url'] ?? '') ?>" placeholder="https://...">
                    <div class="form-help">Leave empty until production deployment is live.</div>
                </div>
            </div>
        </div>

        <!-- Case Study Detailed Content -->
        <div class="admin-card" style="margin-bottom: 2rem;">
            <div class="card-header-flex">
                <h3><i class="fas fa-file-alt" style="color: var(--cyan);"></i> Engineering Case Study Breakdown</h3>
            </div>

            <div class="form-group">
                <label for="short_description" class="form-label">Short Summary (Shown on Project Card) <span style="color: var(--cyan);">*</span></label>
                <textarea name="short_description" id="short_description" class="form-textarea" rows="2" required><?= e($project['short_description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="key_features" class="form-label">Key Engineered Features</label>
                <textarea name="key_features" id="key_features" class="form-textarea" rows="4" placeholder="• Feature 1&#10;• Feature 2"><?= e($project['key_features'] ?? '') ?></textarea>
            </div>

            <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="form-group">
                    <label for="challenges" class="form-label">Key Challenge</label>
                    <textarea name="challenges" id="challenges" class="form-textarea" rows="3"><?= e($project['challenges'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="solutions" class="form-label">Engineered Solution</label>
                    <textarea name="solutions" id="solutions" class="form-textarea" rows="3"><?= e($project['solutions'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="results" class="form-label">Results &amp; Impact</label>
                    <textarea name="results" id="results" class="form-textarea" rows="3"><?= e($project['results'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="case_study_content" class="form-label">Full Case Study Narrative</label>
                <textarea name="case_study_content" id="case_study_content" class="form-textarea" rows="5"><?= e($project['case_study_content'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="projects.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-cyan btn-lg">
                <i class="fas fa-save"></i> <?= $isEditing ? 'Save Changes' : 'Publish Project' ?>
            </button>
        </div>
    </form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
