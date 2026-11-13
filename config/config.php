<?php
/**
 * Application Configuration & Security Defaults
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

// Prevent multiple inclusions
if (defined('APP_INIT')) {
    return;
}
define('APP_INIT', true);

// Application Metadata
define('APP_NAME', 'Girish Pawar — Developer Portfolio');
define('APP_TAGLINE', 'Building Digital Experiences. Solving Real Problems.');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Girish Pawar');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODELS_PATH', ROOT_PATH . '/models');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Ensure data and upload directories exist
if (!is_dir(DATA_PATH)) {
    @mkdir(DATA_PATH, 0755, true);
}
if (!is_dir(UPLOADS_PATH)) {
    @mkdir(UPLOADS_PATH, 0755, true);
    @mkdir(UPLOADS_PATH . '/profile', 0755, true);
    @mkdir(UPLOADS_PATH . '/projects', 0755, true);
    @mkdir(UPLOADS_PATH . '/certificates', 0755, true);
    @mkdir(UPLOADS_PATH . '/resume', 0755, true);
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'girish_portfolio');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Security & Sessions
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // Check if HTTPS is active
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if ($isHttps) {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Security constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME_MINUTES', 15);
define('MAX_UPLOAD_SIZE_MB', 8);

// Default Admin Credentials for First-Time Setup
// (Will be seeded into DB on initial run; user can change password inside dashboard)
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_EMAIL', 'girish.pawar.dev@gmail.com');
define('DEFAULT_ADMIN_PASS', 'GirishPawar@2026!');
