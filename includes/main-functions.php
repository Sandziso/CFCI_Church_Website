<?php
/**
 * main-functions.php
 * Central loader for Christian Family Centre International
 * Updated for enhanced database schema v1.1
 */

// Safety
if (!defined('ROOT_PATH') && file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
defined('ROOT_PATH') or die('Direct access not allowed');

// -------------------------------------------------------
// 1. Core Includes
// -------------------------------------------------------
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/db_connect.php';      // DBConnect, helper functions
require_once __DIR__ . '/SecurityManager.php';  // Security utilities

// Initialize session handler (custom DB‑driven or default)
require_once __DIR__ . '/session.php';

// Load auth system (uses global $auth object)
require_once __DIR__ . '/auth.php';

// -------------------------------------------------------
// 2. Global Objects & Initialization
// -------------------------------------------------------
global $auth;
try {
    $auth = new Auth();
    
    // Auto‑login via remember me
    $auth->checkRememberMe();
    
    // Session timeout check if logged in
    if (SessionManager::isAuthenticated()) {
        $auth->checkSessionTimeout(SESSION_TIMEOUT / 60);
    }
} catch (Exception $e) {
    error_log("Auth init error: " . $e->getMessage());
    if (defined('DEV_MODE') && DEV_MODE) {
        die("Authentication system error: " . $e->getMessage());
    } else {
        die("Service temporarily unavailable.");
    }
}

// -------------------------------------------------------
// 3. Utility Functions (if not already defined)
// -------------------------------------------------------

// URL helpers
if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $message = null, $type = 'info') {
        if ($message) {
            SessionManager::setFlash($type, $message);
        }
        header('Location: ' . $url);
        exit();
    }
}

// Flash messages
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        $flash = SessionManager::getFlash();
        if ($flash) {
            $alert_class = [
                'success' => 'alert-success',
                'warning' => 'alert-warning',
                'danger'  => 'alert-danger',
                'info'    => 'alert-info'
            ][$flash['type']] ?? 'alert-info';

            return '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">' .
                   htmlspecialchars($flash['message']) .
                   '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
                   '</div>';
        }
        return '';
    }
}

if (!function_exists('setFlashMessage')) {
    function setFlashMessage($message, $type = 'info') {
        SessionManager::setFlash($type, $message);
    }
}

// Error logging
if (!function_exists('logError')) {
    function logError($message, $context = []) {
        $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if (!empty($context)) {
            $log_entry .= ' Context: ' . json_encode($context);
        }
        $log_entry .= PHP_EOL;

        $log_dir = defined('LOG_PATH') ? LOG_PATH : ROOT_PATH . 'logs/';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
        file_put_contents($log_dir . 'app_errors_' . date('Y-m-d') . '.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// -------------------------------------------------------
// 4. Auth Shortcuts (compatible with existing code)
// -------------------------------------------------------
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}
if (!function_exists('checkUserLoggedIn')) {
    function checkUserLoggedIn() {
        return is_logged_in();
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

if (!function_exists('getUserName')) {
    function getUserName() {
        return $_SESSION['full_name'] ?? null;
    }
}

// *** FIXED: is_admin() now recognises 'super' ***
if (!function_exists('is_admin')) {
    function is_admin() {
        $role = getUserRole();
        return in_array($role, ['admin', 'super_admin', 'super']);
    }
}

if (!function_exists('is_pastor')) {
    function is_pastor() {
        return getUserRole() === 'pastor';
    }
}

if (!function_exists('is_member')) {
    function is_member() {
        return getUserRole() === 'member';
    }
}

if (!function_exists('checkIsAdmin')) {
    function checkIsAdmin() { return is_admin(); }
}

if (!function_exists('checkIsPastor')) {
    function checkIsPastor() { return is_pastor(); }
}

// -------------------------------------------------------
// 5. Access Control Helpers
// -------------------------------------------------------
if (!function_exists('requireUserLogin')) {
    function requireUserLogin($redirect = '') {
        if (!SessionManager::isAuthenticated()) {
            $param = $redirect ? '?redirect=' . urlencode($redirect) : '';
            header('Location: ' . SITE_URL . 'auth/login.php' . $param);
            exit();
        }
    }
}

if (!function_exists('requireAdminAccess')) {
    function requireAdminAccess() {
        requireUserLogin('admin');
        if (!is_admin()) {
            header('Location: ' . SITE_URL . 'index.php?error=unauthorized');
            exit();
        }
    }
}

if (!function_exists('requirePastorAccess')) {
    function requirePastorAccess() {
        requireUserLogin('pastor');
        if (!is_pastor() && !is_admin()) {
            header('Location: ' . SITE_URL . 'index.php?error=unauthorized');
            exit();
        }
    }
}

if (!function_exists('redirect_if_not_logged_in')) {
    function redirect_if_not_logged_in($redirect_to = 'auth/login.php') {
        if (!is_logged_in()) {
            setFlashMessage('You must be logged in to access that page.', 'danger');
            header('Location: ' . SITE_URL . $redirect_to);
            exit();
        }
    }
}

// *** FIXED: redirectToRoleDashboard() respects base_role and handles 'super' ***
if (!function_exists('redirectToRoleDashboard')) {
    function redirectToRoleDashboard() {
        if (SessionManager::isAuthenticated()) {
            $baseRole = $_SESSION['base_role'] ?? $_SESSION['user_role'] ?? 'member';
            // Pastors always go to pastor dashboard, regardless of admin privileges
            if ($baseRole === 'pastor') {
                header('Location: ' . SITE_URL . 'pastor/dashboard.php');
                exit;
            }
            // Otherwise use the effective role (admin/super)
            $effectiveRole = $_SESSION['user_role'] ?? $baseRole;
            switch ($effectiveRole) {
                case 'super':           // <-- ADDED
                case 'super_admin':
                case 'admin':
                    header('Location: ' . SITE_URL . 'admin/dashboard.php');
                    break;
                default:
                    header('Location: ' . SITE_URL . 'member/dashboard.php');
            }
            exit();
        }
    }
}

// -------------------------------------------------------
// 6. User Data Helpers (leveraging new schema)
// -------------------------------------------------------
if (!function_exists('getUserProfile')) {
    function getUserProfile($user_id = null) {
        $id = $user_id ?? getUserId();
        if (!$id) return null;

        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT u.*, up.*,
                       a.role as admin_role
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            logError("getUserProfile error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getUserInitials')) {
    function getUserInitials($name = null) {
        if (empty($name)) $name = getUserName();
        if (empty($name)) return 'U';
        $initials = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            if (!empty($word)) $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }
}

if (!function_exists('userHasEmailVerified')) {
    function userHasEmailVerified($user_id = null) {
        $profile = getUserProfile($user_id);
        return $profile && !empty($profile['email_verified_at']);
    }
}

// -------------------------------------------------------
// 7. Session Manager (DB‑driven) – included via session.php
// -------------------------------------------------------
// (session.php should implement DB session storage using the `sessions` table)

// -------------------------------------------------------
// 8. Logout Function
// -------------------------------------------------------
if (!function_exists('logoutUser')) {
    function logoutUser() {
        global $auth;
        $auth->logout(); // clears remember token and logs event
        SessionManager::destroy();
        header('Location: ' . SITE_URL . 'auth/login.php');
        exit();
    }
}

// -------------------------------------------------------
// 9. CSRF Helper (using SecurityManager)
// -------------------------------------------------------
if (!function_exists('csrfField')) {
    function csrfField() {
        $token = SecurityManager::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('validateCsrf')) {
    function validateCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (!SecurityManager::verifyCSRFToken($token)) {
            logError("CSRF validation failed", ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            die("Security validation failed. Please go back and refresh the page.");
        }
    }
}

// -------------------------------------------------------
// 10. Settings Helper (from settings table)
// -------------------------------------------------------
if (!function_exists('siteSetting')) {
    function siteSetting($key, $default = null) {
        static $cache = null;
        if ($cache === null) {
            try {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                $cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (Exception $e) {
                logError("siteSetting load error: " . $e->getMessage());
                $cache = [];
            }
        }
        return $cache[$key] ?? $default;
    }
}

// -------------------------------------------------------
// 11. Audit Trail Helper
// -------------------------------------------------------
if (!function_exists('auditLog')) {
    function auditLog($action_type, $table, $record_id = null, $old = null, $new = null, $user_id = null) {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO audit_log (user_id, action_type, action_table, record_id, old_value, new_value, ip_address)
                VALUES (:uid, :type, :table, :rid, :old, :new, :ip)
            ");
            $stmt->execute([
                ':uid'  => $user_id ?? getUserId(),
                ':type' => $action_type,
                ':table'=> $table,
                ':rid'  => $record_id,
                ':old'  => $old ? json_encode($old) : null,
                ':new'  => $new ? json_encode($new) : null,
                ':ip'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            logError("Audit log error: " . $e->getMessage());
        }
    }
}

// -------------------------------------------------------
// 12. Quick DB helper (PDO shortcut)
// -------------------------------------------------------
if (!function_exists('db')) {
    function db() {
        return Database::getInstance()->getConnection();
    }
}

// -------------------------------------------------------
// 13. Force HTTPS (optional)
// -------------------------------------------------------
if (!function_exists('forceHttps')) {
    function forceHttps() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect);
            exit();
        }
    }
}

// ========================= END OF FILE ============================