<?php
// ===================================================
// CONFIGURATION FILE - Christian Family Centre International
// ===================================================

// Define root path if not defined yet
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// SITE CONFIGURATION
// ====================================================================

// Site Information
define('SITE_NAME', 'Christian Family Centre International');
define('SITE_URL', 'http://localhost/cfci'); // Update for production
define('ADMIN_EMAIL', 'admin@cfci.org.sz');
define('SUPPORT_EMAIL', 'support@cfci.org.sz');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cfci_church_db');
define('DB_USER', 'root'); // Change to your database user
define('DB_PASS', ''); // Change to your database password
define('DB_PORT', 3306); // Add port definition

// Security Configuration
define('SESSION_NAME', 'CFCI_SESS');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('CSRF_TOKEN_EXPIRY', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour

// File Upload Configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

// Development Settings
define('DEV_MODE', true); // Set to false in production
define('LOG_PATH', ROOT_PATH . '/logs/');
define('CACHE_PATH', ROOT_PATH . '/cache/');

// Timezone
date_default_timezone_set('Africa/Mbabane');

// ====================================================================
// APPLICATION CONSTANTS
// ====================================================================

// User Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_PASTOR', 'pastor');
define('ROLE_MEMBER', 'member');
define('ROLE_GUEST', 'guest');

// Status Constants
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');
define('STATUS_COMPLETED', 'completed');

// Prayer Request Categories
define('PRAYER_HEALTH', 'health');
define('PRAYER_FINANCIAL', 'financial');
define('PRAYER_FAMILY', 'family');
define('PRAYER_SPIRITUAL', 'spiritual');
define('PRAYER_WORK', 'work');
define('PRAYER_OTHER', 'other');

// Event Status
define('EVENT_UPCOMING', 'upcoming');
define('EVENT_ONGOING', 'ongoing');
define('EVENT_COMPLETED', 'completed');
define('EVENT_CANCELLED', 'cancelled');

// Donation Status
define('DONATION_PENDING', 'pending');
define('DONATION_COMPLETED', 'completed');
define('DONATION_FAILED', 'failed');
define('DONATION_REFUNDED', 'refunded');

// ====================================================================
// ERROR REPORTING
// ====================================================================

if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    if (defined('LOG_PATH')) {
        ini_set('error_log', LOG_PATH . 'php_errors.log');
    }
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    if (defined('LOG_PATH')) {
        ini_set('error_log', LOG_PATH . 'php_errors.log');
    }
}

// ====================================================================
// AUTO-LOAD CLASSES (Optional)
// ====================================================================

spl_autoload_register(function ($class_name) {
    $class_file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});
?>