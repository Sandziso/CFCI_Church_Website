<?php
// includes/auth.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/SecurityManager.php';

// Enhanced Auth class with security features
class Auth {
    private $pdo;
    private $max_login_attempts = 5;
    private $lockout_time = 900; // 15 minutes

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        SecurityManager::initialize($this->pdo);
    }

    /**
     * Authenticates a user.
     */
    public function login($email, $password, $remember_me = false) {
        try {
            // Check if IP is temporarily blocked
            if ($this->isIPBlocked()) {
                return ['error' => 'Too many failed attempts. Please try again later.'];
            }

            // Validate inputs
            if (!SecurityManager::validateEmail($email) || empty($password)) {
                return ['error' => 'Invalid email or password format.'];
            }

            $query = "SELECT u.*, a.role as admin_role
          FROM users u
          LEFT JOIN admins a ON u.id = a.user_id
          WHERE u.email = :email";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password_hash'])) {
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $this->updatePasswordHash($user['id'], $password);
                    }

                    $this->resetFailedAttempts($email);
                    session_regenerate_id(true);

                    $role = $user['admin_role'] ?? $user['role'];

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $role;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time'] = time();

                    $this->updateLastLogin($user['id']);

                    if ($remember_me) {
                        $this->setRememberMeCookie($user['id']);
                    }

                    SecurityManager::logSecurityEvent('LOGIN_SUCCESS', $user['id']);

                    return [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $role
                    ];
                }
            }

            $this->logFailedAttempt($email);
            SecurityManager::logSecurityEvent('LOGIN_FAILED', null, "Email: $email");

            return ['error' => 'Invalid email or password.'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['error' => 'A system error occurred during login.'];
        }
    }

    private function isIPBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts = SecurityManager::getFailedAttempts(null, $ip);
        return $attempts >= $this->max_login_attempts;
    }

    private function logFailedAttempt($email) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $query = "INSERT INTO failed_logins (email, ip_address, user_agent) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$email, $ip, $user_agent]);
        } catch (Exception $e) {
            error_log("Failed login log error: " . $e->getMessage());
        }
    }

    private function resetFailedAttempts($email) {
        try {
            $query = "DELETE FROM failed_logins WHERE email = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$email]);
        } catch (Exception $e) {
            error_log("Reset failed attempts error: " . $e->getMessage());
        }
    }

    private function updatePasswordHash($user_id, $password) {
        try {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$new_hash, $user_id]);
        } catch (Exception $e) {
            error_log("Password hash update error: " . $e->getMessage());
        }
    }

    private function updateLastLogin($user_id) {
        try {
            $query = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Last login update error: " . $e->getMessage());
        }
    }

    private function setRememberMeCookie($user_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days

        try {
            $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id, $token, $expiry]);

            setcookie('remember_token', $token, $expiry, '/', '', true, true);
        } catch (Exception $e) {
            error_log("Remember me token error: " . $e->getMessage());
        }
    }

    public function checkRememberMe() {
        if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
            $token = $_COOKIE['remember_token'];

            try {
                $query = "SELECT u.*, a.role as admin_role
                          FROM remember_tokens rt
                          JOIN users u ON rt.user_id = u.id
                          LEFT JOIN admins a ON u.id = a.user_id
                          WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$token]);

                if ($stmt->rowCount() == 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['admin_role'] ?? $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time'] = time();

                    $this->updateLastLogin($user['id']);
                    $this->setRememberMeCookie($user['id']);

                    SecurityManager::logSecurityEvent('REMEMBER_ME_LOGIN', $user['id']);
                    return true;
                } else {
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            } catch (Exception $e) {
                error_log("Remember me check error: " . $e->getMessage());
            }
        }
        return false;
    }

    public function logout() {
        if (isset($_COOKIE['remember_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
                $stmt->execute([$_COOKIE['remember_token']]);
                setcookie('remember_token', '', time() - 3600, '/');
            } catch (Exception $e) {
                error_log("Logout token cleanup error: " . $e->getMessage());
            }
        }

        if (isset($_SESSION['user_id'])) {
            SecurityManager::logSecurityEvent('LOGOUT', $_SESSION['user_id']);
        }

        session_unset();
        session_destroy();
    }

    public function checkSessionTimeout($timeout_minutes = 60) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > ($timeout_minutes * 60)) {
            $this->logout();
            set_message("Your session has expired. Please log in again.", "warning");
            header("Location: " . SITE_URL . "/auth/login.php");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    public function register($fullName, $email, $password, $phone = null, $address = null, $date_of_birth = null, $role = 'member') {
        try {
            if (empty($fullName) || empty($email) || empty($password)) {
                return ['error' => 'All required fields must be filled.'];
            }

            if (!SecurityManager::validateEmail($email)) {
                return ['error' => 'Invalid email format.'];
            }

            if (!SecurityManager::validatePassword($password)) {
                return ['error' => 'Password must be at least 8 characters with uppercase, lowercase, and number.'];
            }

            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                return ['error' => "Email already registered."];
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)");
            
            if ($stmt->execute([
                'full_name' => SecurityManager::sanitize($fullName),
                'email' => SecurityManager::sanitize($email, 'email'),
                'password_hash' => $hashed_password,
                'role' => $role
            ])) {
                $user_id = $this->pdo->lastInsertId();
                
                if ($phone || $address || $date_of_birth) {
                    $profile_stmt = $this->pdo->prepare("INSERT INTO user_profiles (user_id, phone, address, birth_date) VALUES (:user_id, :phone, :address, :birth_date)");
                    $profile_stmt->execute([
                        'user_id' => $user_id,
                        'phone' => SecurityManager::sanitize($phone),
                        'address' => SecurityManager::sanitize($address),
                        'birth_date' => $date_of_birth
                    ]);
                }
                
                SecurityManager::logSecurityEvent('REGISTRATION_SUCCESS', $user_id);
                return ['success' => true, 'message' => "Registration successful!", 'user_id' => $user_id];
            } else {
                SecurityManager::logSecurityEvent('REGISTRATION_FAILED', null, "Email: $email");
                return ['error' => "Registration failed. Please try again."];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['error' => "A system error occurred during registration."];
        }
    }

    public function initiatePasswordReset($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);

                $stmt = $this->pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);

                SecurityManager::logSecurityEvent('PASSWORD_RESET_REQUESTED', $user['id']);
                return ['success' => true, 'token' => $token];
            } else {
                return ['error' => 'No account found with that email address.'];
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred. Please try again.'];
        }
    }

    public function verifyPasswordResetToken($token) {
        try {
            $stmt = $this->pdo->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
            $stmt->execute([$token]);

            if ($stmt->rowCount() == 1) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return ['success' => true, 'user_id' => $result['user_id']];
            } else {
                return ['error' => 'Invalid or expired reset token.'];
            }
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return ['error' => 'An error occurred during token verification.'];
        }
    }

    public function resetPassword($user_id, $new_password) {
        try {
            if (!SecurityManager::validatePassword($new_password)) {
                return ['error' => 'Password must be at least 8 characters with uppercase, lowercase, and number.'];
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, lock_until = NULL WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $update_stmt = $this->pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?");
                $update_stmt->execute([$user_id]);
                
                SecurityManager::logSecurityEvent('PASSWORD_RESET_SUCCESS', $user_id);
                return ['success' => true, 'message' => 'Password successfully reset.'];
            }
            return ['error' => 'Failed to reset password.'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred while resetting password.'];
        }
    }
}

// Global utility functions with existence checks
if (!function_exists('set_message')) {
    function set_message($message, $type = 'info') {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
}

if (!function_exists('display_message')) {
    function display_message() {
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $type = $_SESSION['message_type'] ?? 'info';
            
            $alert_class = '';
            switch ($type) {
                case 'success': $alert_class = 'alert-success'; break;
                case 'warning': $alert_class = 'alert-warning'; break;
                case 'danger': $alert_class = 'alert-danger'; break;
                default: $alert_class = 'alert-info';
            }
            
            echo "<div class='alert $alert_class alert-dismissible fade show' role='alert'>";
            echo htmlspecialchars($message);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
            
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
    }
}

if (!function_exists('is_pastor')) {
    function is_pastor() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'pastor';
    }
}

if (!function_exists('is_member')) {
    function is_member() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member';
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

if (!function_exists('redirect_if_not_logged_in')) {
    function redirect_if_not_logged_in($redirect_to = 'auth/login.php') {
        if (!is_logged_in()) {
            set_message("You must be logged in to access that page.", "danger");
            header("Location: " . SITE_URL . "/" . $redirect_to);
            exit();
        }
    }
}

if (!function_exists('logout')) {
    function logout() {
        global $auth;
        $auth->logout();
        set_message("You have been logged out.", "info");
        header("Location: " . SITE_URL . "/auth/login.php");
        exit();
    }
}

// ====================================================================
// Dashboard Helper Functions (for backward compatibility)
// ====================================================================

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

if (!function_exists('checkIsAdmin')) {
    function checkIsAdmin() {
        return is_admin();
    }
}

if (!function_exists('checkIsPastor')) {
    function checkIsPastor() {
        return is_pastor();
    }
}

if (!function_exists('getUserInitials')) {
    function getUserInitials($name) {
        if (empty($name)) return 'U';
        $initials = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }
}

// -----------------------------------------------------------------------------
// Initialization
// -----------------------------------------------------------------------------

try {
    $auth = new Auth();
    $auth->checkRememberMe();

    if (isset($_SESSION['user_id'])) {
        $auth->checkSessionTimeout();
    }

} catch (Exception $e) {
    error_log("Auth initialization error: " . $e->getMessage());
    if (defined('DEV_MODE') && DEV_MODE) {
        die("Authentication system error: " . $e->getMessage());
    } else {
        die("System temporarily unavailable. Please try again later.");
    }
}

// Handler functions
if (!function_exists('handleLogin')) {
    function handleLogin() {
        global $auth;

        $email = SecurityManager::sanitize($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        try {
            SecurityManager::verifyCSRFToken($_POST['csrf_token'] ?? '');
        } catch (Exception $e) {
            header('Location: ../auth/login.php?error=' . urlencode('Security token validation failed'));
            exit;
        }

        if (empty($email) || empty($password)) {
            header('Location: ../auth/login.php?error=' . urlencode('Please fill in all fields'));
            exit;
        }

        $result = $auth->login($email, $password, $remember);

        if (isset($result['error'])) {
            header('Location: ../auth/login.php?error=' . urlencode($result['error']));
            exit;
        } else {
            $role = $result['role'];
            if ($role === 'admin') {
                header('Location: ../admin/dashboard.php');
            } elseif ($role === 'pastor') {
                header('Location: ../pastor/dashboard.php');
            } else {
                header('Location: ../member/dashboard.php');
            }
            exit;
        }
    }
}

if (!function_exists('handleRegister')) {
    function handleRegister() {
        global $auth;

        $full_name = SecurityManager::sanitize($_POST['full_name'] ?? '');
        $email = SecurityManager::sanitize($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = SecurityManager::sanitize($_POST['phone'] ?? '');
        $address = SecurityManager::sanitize($_POST['address'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $csrf_token = $_POST['csrf_token'] ?? '';
        $terms = $_POST['terms'] ?? '';

        $redirect_url = SITE_URL . '/auth/register.php';

        try {
            SecurityManager::verifyCSRFToken($csrf_token);

            if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($terms)) {
                header('Location: ' . $redirect_url . '?error=' . urlencode('All required fields must be filled.'));
                exit;
            }

            if (!SecurityManager::validateEmail($email)) {
                header('Location: ' . $redirect_url . '?error=' . urlencode('Invalid email format.'));
                exit;
            }

            if (!SecurityManager::validatePassword($password)) {
                header('Location: ' . $redirect_url . '?error=' . urlencode('Password must be at least 8 characters with uppercase, lowercase, and number.'));
                exit;
            }

            if ($password !== $confirm_password) {
                header('Location: ' . $redirect_url . '?error=' . urlencode('Passwords do not match.'));
                exit;
            }

            $register_result = $auth->register($full_name, $email, $password, $phone, $address, $date_of_birth);
            
            if (isset($register_result['error'])) {
                header('Location: ' . $redirect_url . '?error=' . urlencode($register_result['error']));
                exit;
            }

            header('Location: ' . SITE_URL . '/auth/login.php?message=' . urlencode('Registration successful!'));
            exit;

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            header('Location: ' . $redirect_url . '?error=' . urlencode('A security error occurred.'));
            exit;
        }
    }
}

if (!function_exists('handlePasswordReset')) {
    function handlePasswordReset() {
        global $auth;
        
        $email = SecurityManager::sanitize($_POST['email'] ?? '', 'email');
        
        if (empty($email)) {
            header('Location: ../auth/forgot-password.php?error=' . urlencode('Please enter your email address'));
            exit;
        }

        $result = $auth->initiatePasswordReset($email);
        
        if (isset($result['error'])) {
            header('Location: ../auth/forgot-password.php?error=' . urlencode($result['error']));
            exit;
        } else {
            header('Location: ../auth/reset-password.php?token=' . $result['token'] . '&message=' . urlencode('Password reset initiated.'));
            exit;
        }
    }
}
?>