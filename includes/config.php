<?php
// includes/config.php

// ------------------------------------
// SESSION CONFIGURATION
// MUST BE SET BEFORE ANY OUTPUT
// ------------------------------------

// Only configure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session configuration BEFORE starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', 1);
    
    // Session garbage collection
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Custom session name
    session_name('CFCI_CHURCH_SESSION');
}

// ------------------------------------
// TIMEZONE
// ------------------------------------
date_default_timezone_set('Africa/Mbabane');

// ------------------------------------
// DATABASE CONFIGURATION
// ------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cfci_church_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ------------------------------------
// APPLICATION SETTINGS
// ------------------------------------
define('INIT_DB', false); // Set to true only for initial setup
define('DEV_MODE', true); // Set to false in production
define('USE_DATABASE_SESSIONS', false); // Set to true if you want database sessions

// ------------------------------------
// SITE CONFIGURATION
// ------------------------------------
define('SITE_URL', 'http://localhost/CFCI_Church_Website');
define('SITE_NAME', 'CFCI Church');
define('CHURCH_LOCATION', 'Ntunja Township behind William Pitcher College');
define('SERVICE_TIME', 'Sunday 9:00 AM - 12:00 PM');

// ------------------------------------
// FILE UPLOAD PATHS
// ------------------------------------
define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('PROFILE_PICTURE_PATH', UPLOAD_PATH . 'profile-pictures/');
define('SERMON_UPLOAD_PATH', UPLOAD_PATH . 'sermons/');
define('EVENT_IMAGE_PATH', UPLOAD_PATH . 'event-images/');

// ------------------------------------
// SECURITY
// ------------------------------------
define('ENCRYPTION_KEY', 'your-secure-encryption-key-here');
define('CSRF_SECRET', 'your-csrf-secret-key');
define('SESSION_ENCRYPTION_KEY', 'your-session-encryption-key-here');

// ------------------------------------
// ERROR REPORTING (Development vs. Production)
// ------------------------------------
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}