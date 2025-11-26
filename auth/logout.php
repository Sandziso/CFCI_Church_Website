<?php
// auth/logout.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

// Initialize security class
Security::initialize($conn);

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        // Log potential CSRF attack
        error_log("Potential CSRF attack detected during logout from IP: " . $_SERVER['REMOTE_ADDR']);
        
        // Redirect to login with error
        header('Location: login.php?error=Security token invalid. Please try again.');
        exit();
    }
}

try {
    // Store user information for logging before destroying session
    $user_id = $_SESSION['user_id'] ?? null;
    $user_email = $_SESSION['email'] ?? null;
    
    // Log the logout activity
    if ($user_id) {
        $log_stmt = $conn->prepare("
            INSERT INTO user_activity_log (user_id, activity_type, ip_address, user_agent) 
            VALUES (?, 'logout', ?, ?)
        ");
        $log_stmt->execute([
            $user_id, 
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    // Clear all session variables
    $_SESSION = [];

    // If session cookie exists, delete it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finally, destroy the session
    session_destroy();

    // Clear any existing output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Redirect to login page with success message
    header('Location: login.php?message=You have been successfully logged out.');
    exit();

} catch (Exception $e) {
    // Log any errors during logout process
    error_log("Logout error for user {$user_id}: " . $e->getMessage());
    
    // Force session destruction even if logging fails
    session_destroy();
    
    // Redirect to login page
    header('Location: login.php?message=You have been logged out.');
    exit();
}
?>