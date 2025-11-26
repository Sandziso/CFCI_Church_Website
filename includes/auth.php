<?php
// includes/auth.php

// Ensure session is started if not already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Security class for logging and additional security features
class Security {
    private static $pdo;

    public static function initialize($database_connection) {
        self::$pdo = $database_connection;
    }

    public static function logSecurityEvent($event_type, $user_id = null, $details = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt = self::$pdo->prepare("INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$event_type, $user_id, $ip_address, $user_agent, $details]);
            return true;
        } catch (Exception $e) {
            error_log("Security log error: " . $e->getMessage());
            return false;
        }
    }

    public static function getFailedAttempts($email = null, $ip = null) {
        try {
            $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

            if ($email) {
                $stmt = self::$pdo->prepare("SELECT COUNT(*) as attempts FROM failed_logins WHERE (email = ? OR ip_address = ?) AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
                $stmt->execute([$email, $ip]);
            } else {
                $stmt = self::$pdo->prepare("SELECT COUNT(*) as attempts FROM failed_logins WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
                $stmt->execute([$ip]);
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['attempts'] ?? 0;
        } catch (Exception $e) {
            error_log("Failed attempts check error: " . $e->getMessage());
            return 0;
        }
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword($password) {
        return strlen($password) >= 6;
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("CSRF token validation failed");
        }
        return true;
    }
}

// Enhanced Auth class with security features
class Auth {
    private $pdo;
    private $max_login_attempts = 5;
    private $lockout_time = 900; // 15 minutes

    public function __construct($database_connection) {
        $this->pdo = $database_connection;
    }

    /**
     * Authenticates a user.
     * @return array Returns an array with user data on success, or an array with an 'error' key on failure.
     */
    public function login($email, $password, $remember_me = false) {
        try {
            // Check if IP is temporarily blocked
            if ($this->isIPBlocked()) {
                return ['error' => 'Too many failed attempts. Please try again later.'];
            }

            // Validate inputs
            if (!Security::validateEmail($email) || empty($password)) {
                return ['error' => 'Invalid email or password format.'];
            }

            $query = "SELECT u.*, a.role as admin_role
                      FROM users u
                      LEFT JOIN admins a ON u.id = a.user_id
                      WHERE u.email = :email AND u.is_active = 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password_hash'])) {
                    // Check if password needs rehashing
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $this->updatePasswordHash($user['id'], $password);
                    }

                    // Reset failed attempts
                    $this->resetFailedAttempts($email);

                    // Regenerate session ID
                    session_regenerate_id(true);

                    // Determine final role
                    $role = $user['admin_role'] ?? $user['role'];

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $role;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time'] = time();

                    // Update last login
                    $this->updateLastLogin($user['id']);

                    // Set remember me cookie
                    if ($remember_me) {
                        $this->setRememberMeCookie($user['id']);
                    }

                    // Log successful login
                    Security::logSecurityEvent('LOGIN_SUCCESS', $user['id']);

                    // Return necessary user data for the handler to redirect
                    return [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $role
                    ];
                }
            }

            // Log failed attempt
            $this->logFailedAttempt($email);
            Security::logSecurityEvent('LOGIN_FAILED', null, "Email: $email");

            // Return error on failure
            return ['error' => 'Invalid email or password.'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['error' => 'A system error occurred during login.'];
        }
    }

    private function isIPBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts = Security::getFailedAttempts(null, $ip);
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
            // Store token in database
            $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id, $token, $expiry]);

            // Set cookie
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

                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['admin_role'] ?? $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time'] = time();

                    // Update last login
                    $this->updateLastLogin($user['id']);

                    // Refresh token
                    $this->setRememberMeCookie($user['id']);

                    Security::logSecurityEvent('REMEMBER_ME_LOGIN', $user['id']);
                    return true;
                } else {
                    // Invalid token, clear cookie
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            } catch (Exception $e) {
                error_log("Remember me check error: " . $e->getMessage());
            }
        }
        return false;
    }

    public function logout() {
        // Clear remember me token if exists
        if (isset($_COOKIE['remember_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
                $stmt->execute([$_COOKIE['remember_token']]);
                setcookie('remember_token', '', time() - 3600, '/');
            } catch (Exception $e) {
                error_log("Logout token cleanup error: " . $e->getMessage());
            }
        }

        // Log security event
        if (isset($_SESSION['user_id'])) {
            Security::logSecurityEvent('LOGOUT', $_SESSION['user_id']);
        }

        // Clear session
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

    /**
     * Registers a new user.
     * @return array Returns an array with a 'message' key on success, or an array with an 'error' key on failure.
     */
public function register($fullName, $email, $password, $phone = null, $address = null, $date_of_birth = null, $role = 'member') {
    try {
        // Validate inputs
        if (empty($fullName) || empty($email) || empty($password)) {
            return ['error' => 'All required fields must be filled.'];
        }

        if (!Security::validateEmail($email)) {
            return ['error' => 'Invalid email format.'];
        }

        if (!Security::validatePassword($password)) {
            return ['error' => 'Password must be at least 6 characters long.'];
        }

        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            return ['error' => "Email already registered. Please use a different email."];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into users table (only columns that exist)
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)");
        
        if ($stmt->execute([
            'full_name' => Security::sanitizeInput($fullName),
            'email' => Security::sanitizeInput($email),
            'password_hash' => $hashed_password,
            'role' => $role
        ])) {
            $user_id = $this->pdo->lastInsertId();
            
            // Now insert into user_profiles if we have additional data
            if ($phone || $address || $date_of_birth) {
                $profile_stmt = $this->pdo->prepare("INSERT INTO user_profiles (user_id, phone, address, birth_date) VALUES (:user_id, :phone, :address, :birth_date)");
                $profile_stmt->execute([
                    'user_id' => $user_id,
                    'phone' => Security::sanitizeInput($phone),
                    'address' => Security::sanitizeInput($address),
                    'birth_date' => $date_of_birth
                ]);
            }
            
            Security::logSecurityEvent('REGISTRATION_SUCCESS', $user_id);
            return ['success' => true, 'message' => "Registration successful!", 'user_id' => $user_id];
        } else {
            Security::logSecurityEvent('REGISTRATION_FAILED', null, "Email: $email");
            return ['error' => "Registration failed. Please try again."];
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        error_log("Registration error details: " . $e->getTraceAsString());
        return ['error' => "A system error occurred during registration. Error: " . (DEV_MODE ? $e->getMessage() : "Please try again later.")];
    }
}

    /**
     * Initiates password reset process
     */
    public function initiatePasswordReset($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                // Store reset token
                $stmt = $this->pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);

                Security::logSecurityEvent('PASSWORD_RESET_REQUESTED', $user['id']);

                return ['success' => true, 'token' => $token];
            } else {
                return ['error' => 'No account found with that email address.'];
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred. Please try again.'];
        }
    }

    /**
     * Verifies password reset token
     */
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

    /**
     * Resets user password
     */
    public function resetPassword($user_id, $new_password) {
        try {
            if (!Security::validatePassword($new_password)) {
                return ['error' => 'Password must be at least 6 characters long.'];
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, lock_until = NULL WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                // Mark token as used
                $update_stmt = $this->pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?");
                $update_stmt->execute([$user_id]);
                
                Security::logSecurityEvent('PASSWORD_RESET_SUCCESS', $user_id);
                return ['success' => true, 'message' => 'Password successfully reset.'];
            }
            return ['error' => 'Failed to reset password.'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred while resetting password.'];
        }
    }
}

// Global utility functions

// Function to set a session message
function set_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Function to display and clear session message
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
        
        // Clear the message
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
}

// Function to check user role
function is_pastor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'pastor';
}

function is_member() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member';
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect if not logged in
function redirect_if_not_logged_in($redirect_to = 'auth/login.php') {
    if (!is_logged_in()) {
        set_message("You must be logged in to access that page.", "danger");
        header("Location: " . SITE_URL . "/" . $redirect_to);
        exit();
    }
}

// Function to redirect if not specific role
function redirect_if_not_role($role, $redirect_to = 'index.php') {
    if (!is_logged_in() || $_SESSION['user_role'] !== $role) {
        set_message("You do not have permission to access that page.", "danger");
        header("Location: " . SITE_URL . "/" . $redirect_to);
        exit();
    }
}

// Function to require specific role
function require_role($role) {
    if (!is_logged_in() || $_SESSION['user_role'] !== $role) {
        http_response_code(403);
        die("Access denied. Insufficient permissions.");
    }
}

// Enhanced logout function
function logout() {
    global $auth;
    $auth->logout();

    set_message("You have been logged out.", "info");
    header("Location: " . SITE_URL . "/auth/login.php");
    exit();
}

// -----------------------------------------------------------------------------
// Initialization and Handler Logic
// -----------------------------------------------------------------------------

// Initialize database and auth
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Initialize Security and Auth
    Security::initialize($pdo);
    $auth = new Auth($pdo);

    // Check remember me cookie on every page load
    $auth->checkRememberMe();

    // Check session timeout (only if logged in)
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

// Request handler functions
function handleLogin() {
    global $auth;

    // Sanitize inputs
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate CSRF token
    try {
        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
    } catch (Exception $e) {
        header('Location: ../auth/login.php?error=' . urlencode('Security token validation failed'));
        exit;
    }

    if (empty($email) || empty($password)) {
        header('Location: ../auth/login.php?error=' . urlencode('Please fill in all fields'));
        exit;
    }

    // Use the Auth class login method
    $result = $auth->login($email, $password, $remember);

    if (isset($result['error'])) {
        // Login failed
        header('Location: ../auth/login.php?error=' . urlencode($result['error']));
        exit;
    } else {
        // Login successful - redirect based on role
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

function handleRegister() {
    global $auth;

    // Sanitize inputs
    $full_name = Security::sanitizeInput($_POST['full_name'] ?? '');
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = Security::sanitizeInput($_POST['phone'] ?? '');
    $address = Security::sanitizeInput($_POST['address'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $csrf_token = $_POST['csrf_token'] ?? '';
    $terms = $_POST['terms'] ?? '';

    $redirect_url = SITE_URL . '/auth/register.php';

    try {
        // 1. CSRF Protection
        Security::verifyCSRFToken($csrf_token);

        // 2. Server-side Validation
        if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($terms)) {
            header('Location: ' . $redirect_url . '?error=' . urlencode('All required fields must be filled, and you must agree to the terms.'));
            exit;
        }

        if (!Security::validateEmail($email)) {
            header('Location: ' . $redirect_url . '?error=' . urlencode('Invalid email format.'));
            exit;
        }

        if (!Security::validatePassword($password)) {
            header('Location: ' . $redirect_url . '?error=' . urlencode('Password must be at least 6 characters long.'));
            exit;
        }

        if ($password !== $confirm_password) {
            header('Location: ' . $redirect_url . '?error=' . urlencode('Passwords do not match.'));
            exit;
        }
        
        // 3. Sanitization (Auth::register will handle individual sanitation, but it's good practice to sanitize for database input if the Auth method didn't)
        // Since the Auth::register method already calls Security::sanitizeInput, we can proceed.

        // 4. Registration
        $register_result = $auth->register($full_name, $email, $password, $phone, $address, $date_of_birth);
        
        if (isset($register_result['error'])) {
            header('Location: ' . $redirect_url . '?error=' . urlencode($register_result['error']));
            exit;
        }

        // 5. Success
        // Redirect to login page with a success message
        header('Location: ' . SITE_URL . '/auth/login.php?message=' . urlencode('Registration successful! Please log in to your new account.'));
        exit;

    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Registration error: " . $e->getMessage());
        header('Location: ' . $redirect_url . '?error=' . urlencode('A security or system error occurred during registration.'));
        exit;
    }
}

function handleLogout() {
    logout();
}

function handlePasswordReset() {
    global $auth;
    
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email)) {
        header('Location: ../auth/forgot-password.php?error=' . urlencode('Please enter your email address'));
        exit;
    }

    $result = $auth->initiatePasswordReset($email);
    
    if (isset($result['error'])) {
        header('Location: ../auth/forgot-password.php?error=' . urlencode($result['error']));
        exit;
    } else {
        // In production, you would send the token via email
        // For now, we'll redirect to a page showing the token (for testing)
        header('Location: ../auth/reset-password.php?token=' . $result['token'] . '&message=' . urlencode('Password reset initiated. Check your email.'));
        exit;
    }
}

function handlePasswordResetConfirm() {
    global $auth;
    
    $token = Security::sanitizeInput($_POST['token'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        header('Location: ../auth/reset-password.php?token=' . $token . '&error=' . urlencode('Please fill in all fields'));
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        header('Location: ../auth/reset-password.php?token=' . $token . '&error=' . urlencode('Passwords do not match'));
        exit;
    }
    
    // Verify token
    $token_result = $auth->verifyPasswordResetToken($token);
    if (isset($token_result['error'])) {
        header('Location: ../auth/forgot-password.php?error=' . urlencode($token_result['error']));
        exit;
    }
    
    // Reset password
    $reset_result = $auth->resetPassword($token_result['user_id'], $new_password);
    if (isset($reset_result['error'])) {
        header('Location: ../auth/reset-password.php?token=' . $token . '&error=' . urlencode($reset_result['error']));
        exit;
    } else {
        header('Location: ../auth/login.php?message=' . urlencode('Password successfully reset. Please login with your new password.'));
        exit;
    }
}
// -----------------------------------------------------------------------------
// Initialization Only - No Request Handler
// -----------------------------------------------------------------------------

// Initialize database and auth
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Initialize Security and Auth
    Security::initialize($pdo);
    $auth = new Auth($pdo);

    // Check remember me cookie on every page load
    $auth->checkRememberMe();

    // Check session timeout (only if logged in)
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

// Note: Individual process files (login-process.php, register-process.php) 
// will handle specific form submissions
?>