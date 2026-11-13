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

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('CORE_PATH', ROOT_PATH . '/core');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODELS_PATH', ROOT_PATH . '/models');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Load Centralized Environment Architecture
require_once CORE_PATH . '/env.php';
Env::load();

// Environment & Debugging Controls
if (!defined('APP_ENV')) {
    define('APP_ENV', (string)Env::get('APP_ENV', 'local'));
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', Env::isDebug());
}

// Error Reporting & Display Protection
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Ensure data and upload directories exist
if (!is_dir(DATA_PATH)) {
    @mkdir(DATA_PATH, 0755, true);
}
if (!is_dir(UPLOADS_PATH)) {
    @mkdir(UPLOADS_PATH, 0755, true);
}
foreach (['profile', 'projects', 'certificates', 'resume'] as $sub) {
    $dir = UPLOADS_PATH . '/' . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Database Configuration Constants (Sourced via Env)
if (!defined('DB_HOST')) define('DB_HOST', (string)Env::get('DB_HOST', '127.0.0.1'));
if (!defined('DB_PORT')) define('DB_PORT', (string)Env::get('DB_PORT', '3306'));
if (!defined('DB_NAME')) define('DB_NAME', (string)Env::get('DB_NAME', 'girish_portfolio'));
if (!defined('DB_USER')) define('DB_USER', (string)Env::get('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', (string)Env::get('DB_PASS', ''));
if (!defined('DB_CHARSET')) define('DB_CHARSET', (string)Env::get('DB_CHARSET', 'utf8mb4'));

// Security & Sessions
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if ($isHttps) {
        ini_set('session.cookie_secure', 1);
    }
    
    @session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Security limits
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME_MINUTES', 15);
define('MAX_UPLOAD_SIZE_MB', 8);

// Default Seed Admin
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_EMAIL', 'girish.pawar.dev@gmail.com');
define('DEFAULT_ADMIN_PASS', 'GirishPawar@2026!');
