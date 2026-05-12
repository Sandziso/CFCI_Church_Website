<?php
/**
 * logout.php – CFCI Church Logout Handler
 * Destroys session, clears remember‑me tokens, logs the event, and redirects.
 */

// 1. Bootstrap the application – this will define ROOT_PATH, start session, etc.
require_once __DIR__ . '/includes/main-functions.php';

// 2. Log the logout event (if user was logged in)
$userId = SessionManager::getUserId();
if ($userId) {
    SecurityManager::logSecurityEvent('LOGOUT', $userId, 'User logged out from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

// 3. Remove remember‑me token from database and cookie
if (isset($_COOKIE['remember_token'])) {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = :token");
    $stmt->execute([':token' => $_COOKIE['remember_token']]);
    // Clear the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// 4. Completely destroy the session
SessionManager::destroy();   // This handles session_unset(), destroy(), and cookie removal

// 5. Redirect to homepage with a confirmation
header('Location: ' . SITE_URL . 'index.php?logged_out=1');
exit;