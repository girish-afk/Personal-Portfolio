<?php
/**
 * Contact Form Processor & Spam Protection Endpoint
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/includes/functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        json_response(['success' => false, 'error' => 'Method not allowed'], 405);
    }
    header('Location: index.php#contact');
    exit;
}

// 1. Anti-spam Honeypot Check
if (!empty($_POST['website_hp'])) {
    // Bot detected; silently respond success or 400
    if ($isAjax) {
        json_response(['success' => true, 'message' => 'Your message has been received.']);
    }
    header('Location: index.php?status=success#contact');
    exit;
}

// 2. CSRF Token Verification
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    if ($isAjax) {
        json_response(['success' => false, 'error' => 'Security token invalid or expired. Please refresh the page.'], 403);
    }
    set_flash('danger', 'Security token expired. Please try again.');
    header('Location: index.php#contact');
    exit;
}

// 3. Rate Limiting by IP (Max 5 submissions per hour)
$clientIp = get_client_ip();
$recentInquiries = Database::where('inquiries', ['ip_address' => $clientIp]);
$oneHourAgo = time() - 3600;
$recentCount = 0;
foreach ($recentInquiries as $inq) {
    if (isset($inq['created_at']) && strtotime($inq['created_at']) > $oneHourAgo) {
        $recentCount++;
    }
}
if ($recentCount >= 5) {
    if ($isAjax) {
        json_response(['success' => false, 'error' => 'You have submitted multiple messages recently. Please wait a while before sending another.'], 429);
    }
    set_flash('warning', 'Rate limit reached. Please wait a while before submitting again.');
    header('Location: index.php#contact');
    exit;
}

// 4. Validate & Sanitize Inputs
$name = clean_input($_POST['name'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$subject = clean_input($_POST['subject'] ?? '');
$message = clean_input($_POST['message'] ?? '');

$errors = [];
if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Please enter your valid full name.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (empty($subject) || strlen($subject) < 3) {
    $errors[] = 'Please enter a message subject.';
}
if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Please enter a message of at least 10 characters.';
}

if (!empty($errors)) {
    $errorMsg = implode(' ', $errors);
    if ($isAjax) {
        json_response(['success' => false, 'error' => $errorMsg], 422);
    }
    set_flash('danger', $errorMsg);
    header('Location: index.php#contact');
    exit;
}

// 5. Store in Database
$inquiryId = Database::insert('inquiries', [
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'ip_address' => $clientIp,
    'is_read' => 0,
    'created_at' => date('Y-m-d H:i:s')
]);

// 6. Optional Email Notification
$notifyAdmin = Database::getSetting('contact_email_notifications', '1');
if ($notifyAdmin === '1') {
    $profile = Database::find('profile', 1);
    $to = $profile['email'] ?? 'girish.pawar.dev@gmail.com';
    $emailSubject = "[Portfolio Inquiry] " . $subject;
    $emailBody = "New message received from portfolio contact form:\n\n"
               . "Name: {$name}\n"
               . "Email: {$email}\n"
               . "IP: {$clientIp}\n"
               . "Date: " . date('Y-m-d H:i:s') . "\n\n"
               . "Message:\n{$message}\n";
    $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
             . "Reply-To: {$email}\r\n"
             . "X-Mailer: PHP/" . phpversion();
    @mail($to, $emailSubject, $emailBody, $headers);
}

// Regenerate CSRF token after successful submission
csrf_token();

if ($isAjax) {
    json_response([
        'success' => true,
        'message' => 'Thank you, ' . $name . '! Your message has been sent successfully. I will get back to you shortly.'
    ]);
}

set_flash('success', 'Thank you, ' . $name . '! Your message has been sent successfully.');
header('Location: index.php#contact');
exit;
