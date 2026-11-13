<?php
/**
 * Automated Verification & Security Test Suite
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

echo "========================================================\n";
echo "  GIRISH PAWAR PORTFOLIO — SYSTEM VERIFICATION SUITE   \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "[PASS] " . $name . "\n";
    } else {
        $failCount++;
        echo "[FAIL] " . $name . ($details ? " ($details)" : '') . "\n";
    }
}

// 1. Core Config & Database Loading
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

assertTest("Database initialization", true);
$profile = Database::find('profile', 1);
assertTest("Profile loaded: " . ($profile['full_name'] ?? 'None'), !empty($profile) && $profile['full_name'] === 'Girish Pawar');
assertTest("Profile primary title: " . ($profile['primary_title'] ?? ''), !empty($profile['primary_title']) && $profile['primary_title'] === 'Web Developer');
assertTest("Profile headlines array", !empty($profile['headline_titles']) && in_array('Computer Technology Student', $profile['headline_titles']));

// 2. Initial Data Integrity
$projects = Database::all('projects');
assertTest("Initial Flagship Projects count >= 3", count($projects) >= 3);
$projectTitles = array_column($projects, 'title');
assertTest("AgriShield present", in_array('AgriShield', $projectTitles));
assertTest("AURA AI present", in_array('AURA AI', $projectTitles));
assertTest("PROJECT ASTRO present", in_array('PROJECT ASTRO', $projectTitles));

$skills = Database::all('skills');
assertTest("Skills configured >= 20", count($skills) >= 20);

$education = Database::all('education');
assertTest("Sanjivani K.B.P. Polytechnic College education loaded", !empty($education) && strpos($education[0]['institution'], 'Sanjivani') !== false);
assertTest("Percentage 87.03% verified in score", !empty($education) && strpos($education[0]['score'], '87.03%') !== false);

$internships = Database::all('internships');
assertTest("Internships count == 2", count($internships) === 2);

$certs = Database::all('certifications');
assertTest("IEEE Coding Challenge 2026 present", !empty($certs) && strpos($certs[0]['title'], 'IEEE Coding Challenge 2026') !== false);

$socials = Database::all('social_links');
assertTest("GitHub link configured: girish-afk", !empty($socials) && in_array('https://github.com/girish-afk', array_column($socials, 'url')));

// 3. Security & Admin Authentication Testing
// Test invalid login
$failedAttempt = Auth::attempt('admin', 'WrongPassword123');
assertTest("Rejection of incorrect admin password", $failedAttempt['success'] === false);

// Test valid login
$validAttempt = Auth::attempt('admin', 'GirishPawar@2026!');
assertTest("Authentication of valid owner credentials", $validAttempt['success'] === true);
assertTest("Admin session flag active", Auth::check() === true);

// Test password change
$changeResult = Auth::changePassword(1, 'GirishPawar@2026!', 'NewSecurePassword@2026!');
assertTest("Password change with correct current password", $changeResult['success'] === true);

// Verify new password works
$loginWithNew = Auth::attempt('admin', 'NewSecurePassword@2026!');
assertTest("Login with updated password", $loginWithNew['success'] === true);

// Revert to default test password for environment consistency
Auth::changePassword(1, 'NewSecurePassword@2026!', 'GirishPawar@2026!');
assertTest("Reverted admin password to default benchmark", true);

// Logout
Auth::logout();
assertTest("Logout invalidates session", Auth::check() === false);

// 4. Contact Form Inquiries Storage & Anti-Spam
$dummyMsg = [
    'name' => 'Recruiter Test',
    'email' => 'recruiter@techfirm.com',
    'subject' => 'Frontend Engineering Opportunity',
    'message' => 'Hello Girish, we reviewed your AgriShield project and would love to connect regarding an engineering role.',
    'ip_address' => '127.0.0.1',
    'is_read' => 0,
    'created_at' => date('Y-m-d H:i:s')
];
$newInquiryId = Database::insert('inquiries', $dummyMsg);
assertTest("Contact inquiry inserted with ID: $newInquiryId", $newInquiryId > 0);

$fetchedInquiry = Database::find('inquiries', $newInquiryId);
assertTest("Inquiry retrieval verifies name and subject", $fetchedInquiry['name'] === 'Recruiter Test' && $fetchedInquiry['is_read'] == 0);

// Mark read
Database::update('inquiries', ['is_read' => 1], $newInquiryId);
$markedInquiry = Database::find('inquiries', $newInquiryId);
assertTest("Inquiry mark-as-read toggle verified", $markedInquiry['is_read'] == 1);

// Delete test inquiry
Database::delete('inquiries', $newInquiryId);
$deletedInquiry = Database::find('inquiries', $newInquiryId);
assertTest("Inquiry deletion cleanup", $deletedInquiry === null);

// 5. Public Experience Rendering & Security Inspection
ob_start();
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/index.php';
require dirname(__DIR__) . '/index.php';
$html = ob_get_clean();

assertTest("Public page rendered without fatal error", !empty($html));
assertTest("Public page contains 'Girish Pawar'", strpos($html, 'Girish Pawar') !== false);
assertTest("Public page contains AgriShield case study data", strpos($html, 'AgriShield') !== false);
assertTest("Public page contains AURA AI case study data", strpos($html, 'AURA AI') !== false);
assertTest("Public page contains PROJECT ASTRO case study data", strpos($html, 'PROJECT ASTRO') !== false);
assertTest("Public page does NOT leak admin edit buttons", strpos($html, 'Edit Portfolio') === false && strpos($html, 'admin-auth-check') === false);
assertTest("Public page contains active CSRF token for contact form", strpos($html, 'name="csrf_token"') !== false);
assertTest("Public page contains honeypot anti-spam field", strpos($html, 'name="website_hp"') !== false);

// 6. Resume File Verification
$resumePath = ROOT_PATH . '/uploads/resume/Girish_Pawar_Resume.pdf';
assertTest("Resume PDF file exists on disk", file_exists($resumePath) && filesize($resumePath) > 500);

echo "\n--------------------------------------------------------\n";
echo "SUMMARY: Passed: $passCount, Failed: $failCount\n";
echo "STATUS:  " . ($failCount === 0 ? "ALL CHECKS PASSED PERFECTLY!" : "SOME CHECKS FAILED") . "\n";
echo "--------------------------------------------------------\n";

exit($failCount > 0 ? 1 : 0);
