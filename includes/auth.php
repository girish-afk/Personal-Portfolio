<?php
/**
 * Authentication and Session Management Service
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/database.php';
require_once __DIR__ . '/functions.php';

class Auth {
    /**
     * Check if current visitor is an authenticated admin
     */
    public static function check(): bool {
        return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Get authenticated admin data
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return Database::find('admin_users', (int)$_SESSION['admin_id']);
    }

    /**
     * Require authentication; redirect to login if not authenticated
     */
    public static function requireAuth(): void {
        if (!self::check()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                json_response(['success' => false, 'error' => 'Unauthorized access'], 401);
            }
            // Save attempted URL for post-login redirect
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
            set_flash('danger', 'Please log in to access the administration dashboard.');
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Attempt admin login
     */
    public static function attempt(string $username, string $password): array {
        $username = trim($username);
        $user = Database::whereFirst('admin_users', ['username' => $username]);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Check for lockout
        if (!empty($user['lockout_until'])) {
            $lockoutTs = strtotime($user['lockout_until']);
            if ($lockoutTs > time()) {
                $waitMinutes = ceil(($lockoutTs - time()) / 60);
                return [
                    'success' => false,
                    'message' => "Account temporarily locked due to excessive failed attempts. Please retry in {$waitMinutes} minute(s)."
                ];
            }
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Reset failed login counter and lockout
            Database::update('admin_users', [
                'failed_login_attempts' => 0,
                'lockout_until' => null,
                'last_login' => date('Y-m-d H:i:s')
            ], (int)$user['id']);

            // Regenerate session ID to prevent session fixation
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                session_regenerate_id(true);
            }

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (int)$user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];

            return ['success' => true];
        }

        // Failed attempt handling
        $attempts = (int)($user['failed_login_attempts'] ?? 0) + 1;
        $updateData = ['failed_login_attempts' => $attempts];

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_TIME_MINUTES . ' minutes'));
            $updateData['lockout_until'] = $lockoutTime;
            $updateData['failed_login_attempts'] = 0; // Reset counter for after lockout
            Database::update('admin_users', $updateData, (int)$user['id']);
            return [
                'success' => false,
                'message' => "Too many failed attempts. Account locked for " . LOCKOUT_TIME_MINUTES . " minutes."
            ];
        }

        Database::update('admin_users', $updateData, (int)$user['id']);
        $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
        return [
            'success' => false,
            'message' => "Invalid username or password. {$remaining} attempt(s) remaining before temporary lockout."
        ];
    }

    /**
     * Terminate admin session
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Change password for authenticated admin
     */
    public static function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        $user = Database::find('admin_users', $userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Admin account not found.'];
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = Database::update('admin_users', ['password_hash' => $newHash], $userId);

        if ($updated) {
            return ['success' => true, 'message' => 'Password updated successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to update password in database.'];
    }
}
