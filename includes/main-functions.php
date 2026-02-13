<?php
// ===================================================
// MAIN FUNCTIONS FILE - Christian Family Centre International
// ===================================================

defined('ROOT_PATH') or die('Direct access not allowed');

// Load configuration FIRST
require_once __DIR__ . '/config.php';

// Start session management
require_once __DIR__ . '/session.php';

// Load database (Singleton)
require_once __DIR__ . '/database.php';

// Load consolidated security
require_once __DIR__ . '/SecurityManager.php';

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Load auth system
require_once __DIR__ . '/auth.php';

// ====================================================================
// GLOBAL HELPER FUNCTIONS (with existence checks)
// ====================================================================

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

if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        $flash = SessionManager::getFlash();
        if ($flash) {
            $alert_class = '';
            switch ($flash['type']) {
                case 'success': $alert_class = 'alert-success'; break;
                case 'warning': $alert_class = 'alert-warning'; break;
                case 'danger': $alert_class = 'alert-danger'; break;
                default: $alert_class = 'alert-info';
            }
            
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

if (!function_exists('logError')) {
    function logError($message, $context = []) {
        $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if (!empty($context)) {
            $log_entry .= ' Context: ' . json_encode($context);
        }
        $log_entry .= PHP_EOL;
        
        $log_file = defined('LOG_PATH') ? LOG_PATH . 'app_errors_' . date('Y-m-d') . '.log' : __DIR__ . '/../logs/app_errors.log';
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}

if (!function_exists('requireUserLogin')) {
    function requireUserLogin($redirect = '') {
        if (!SessionManager::isAuthenticated()) {
            $redirectParam = $redirect ? '?redirect=' . urlencode($redirect) : '';
            header('Location: ' . SITE_URL . '/auth/login.php' . $redirectParam);
            exit();
        }
    }
}

if (!function_exists('requireAdminAccess')) {
    function requireAdminAccess() {
        requireUserLogin('admin');
        if (!SessionManager::hasRole('admin') && !SessionManager::hasRole('super_admin')) {
            header('Location: ' . SITE_URL . '/index.php?error=unauthorized');
            exit();
        }
    }
}

if (!function_exists('requirePastorAccess')) {
    function requirePastorAccess() {
        requireUserLogin('pastor');
        if (!SessionManager::hasRole('pastor') && !SessionManager::hasRole('admin') && !SessionManager::hasRole('super_admin')) {
            header('Location: ' . SITE_URL . '/index.php?error=unauthorized');
            exit();
        }
    }
}

if (!function_exists('redirectToRoleDashboard')) {
    function redirectToRoleDashboard() {
        if (SessionManager::isAuthenticated()) {
            $role = SessionManager::getUserRole();
            switch ($role) {
                case 'super_admin':
                case 'admin':
                    header('Location: ' . SITE_URL . '/admin/dashboard.php');
                    break;
                case 'pastor':
                    header('Location: ' . SITE_URL . '/pastor/dashboard.php');
                    break;
                default:
                    header('Location: ' . SITE_URL . '/member/dashboard.php');
            }
            exit();
        }
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser() {
        $user_id = SessionManager::getUserId();
        
        if ($user_id) {
            // Log the logout event
            require_once __DIR__ . '/SecurityManager.php';
            SecurityManager::logSecurityEvent('LOGOUT', $user_id, 'User logged out');
        }
        
        SessionManager::destroy();
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit();
    }
}

// Initialize global objects
global $auth;
$auth = new Auth();
?>