<?php
/**
 * config.php – Application Configuration
 * Christian Family Centre International
 */

// -------------------------------------------------------------
// 1. Base path (real path of project root)
// -------------------------------------------------------------
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
}

// -------------------------------------------------------------
// 2. Site URL (dynamic)
// -------------------------------------------------------------
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Extract subfolder after document root
    $projectRoot = str_replace('\\', '/', ROOT_PATH);
    $docRoot     = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $subDir = '/' . ltrim(substr($projectRoot, strlen($docRoot)), '/');
    if (substr($subDir, -1) !== '/') {
        $subDir .= '/';
    }

    define('SITE_URL', $protocol . $host . $subDir);
}

// -------------------------------------------------------------
// 3. Application info
// -------------------------------------------------------------
define('SITE_NAME', 'Christian Family Centre International');
define('ADMIN_EMAIL', 'admin@cfci.org.sz');
define('SUPPORT_EMAIL', 'support@cfci.org.sz');

// -------------------------------------------------------------
// 4. Database
// -------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cfci_church_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// -------------------------------------------------------------
// 5. Security
// -------------------------------------------------------------
define('SESSION_NAME', 'CFCI_SESS');
define('SESSION_TIMEOUT', 3600);    // 1 hour
define('CSRF_TOKEN_EXPIRY', 1800);  // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);   // 15 minutes
define('PASSWORD_RESET_EXPIRY', 3600);
define('REMEMBER_ME_EXPIRY', 30 * 86400); // 30 days

// -------------------------------------------------------------
// 6. File uploads
// -------------------------------------------------------------
define('MAX_FILE_SIZE', 5242880);    // 5 MB
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');

// -------------------------------------------------------------
// 7. Development & logging
// -------------------------------------------------------------
define('DEV_MODE', true);           // false in production
define('LOG_PATH', ROOT_PATH . 'logs/');
define('CACHE_PATH', ROOT_PATH . 'cache/');

// -------------------------------------------------------------
// 8. Timezone
// -------------------------------------------------------------
date_default_timezone_set('Africa/Mbabane');

// -------------------------------------------------------------
// 9. Roles / statuses (constants)
// -------------------------------------------------------------
define('ROLE_SUPER_ADMIN',  'super_admin');
define('ROLE_ADMIN',       'admin');
define('ROLE_PASTOR',      'pastor');
define('ROLE_MEMBER',      'member');
define('ROLE_GUEST',       'guest');
define('ROLE_SUPER',       'super');   // ← ADD THIS LINE

define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_COMPLETED', 'completed');
define('STATUS_REJECTED', 'rejected');

define('DONATION_COMPLETED', 'completed');
define('DONATION_FAILED', 'failed');
define('DONATION_PENDING', 'pending');
define('DONATION_REFUNDED', 'refunded');

// -------------------------------------------------------------
// 10. Create directories
// -------------------------------------------------------------
foreach ([UPLOAD_PATH, LOG_PATH, CACHE_PATH] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

// -------------------------------------------------------------
// 11. Error reporting
// -------------------------------------------------------------
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . 'php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . 'php_errors.log');
}

// -------------------------------------------------------------
// 12. Autoloader (optional)
// -------------------------------------------------------------
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});