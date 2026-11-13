<?php
/**
 * Safe Resume Download Endpoint
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/database.php';

$profile = Database::find('profile', 1);
$resumeFile = $profile['resume_url'] ?? 'uploads/resume/Girish_Pawar_Resume.pdf';
$fullPath = ROOT_PATH . '/' . ltrim($resumeFile, '/');

if (!file_exists($fullPath) || !is_readable($fullPath)) {
    http_response_code(404);
    die('Resume file not found. The website administrator will upload an updated version shortly.');
}

// Clean output buffer before sending binary
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Girish_Pawar_Resume.pdf"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($fullPath);
exit;
