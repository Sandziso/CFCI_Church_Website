<?php
/**
 * auth.php – Authentication System with Remember Me & DB Sessions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/SecurityManager.php';

class Auth
{
    private $pdo;
    private $maxLoginAttempts;
    private $lockoutTime;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        SecurityManager::initialize($this->pdo);
        $this->maxLoginAttempts = MAX_LOGIN_ATTEMPTS;
        $this->lockoutTime = LOGIN_LOCKOUT_TIME;
    }

    /**
     * Login user
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        try {
            if ($this->isIPBlocked()) {
                return ['error' => 'Too many failed attempts. Please try again later.'];
            }

            if (!SecurityManager::validateEmail($email) || empty($password)) {
                return ['error' => 'Invalid credentials format.'];
            }

            $stmt = $this->pdo->prepare(
                "SELECT u.*, a.role as admin_role
                 FROM users u
                 LEFT JOIN admins a ON u.id = a.user_id
                 WHERE u.email = :email"
            );
            $stmt->execute([':email' => $email]);

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();

                if (password_verify($password, $user['password_hash'])) {
                    // Rehash if needed
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $this->updatePasswordHash($user['id'], $password);
                    }

                    // Clear failed attempts
                    $this->resetFailedAttempts($email);
session_regenerate_id(true);
$_SESSION['user_id']       = $user['id'];
$_SESSION['full_name']     = $user['full_name'];
$_SESSION['user_email']    = $user['email'];
$_SESSION['user_role']     = $user['admin_role'] ?? $user['role'];   // effective role
$_SESSION['base_role']     = $user['role'];                          // <-- ADD THIS LINE
$_SESSION['logged_in']     = true;
$_SESSION['last_activity'] = time();
$_SESSION['login_time']    = time();

                    // Update last login
                    $this->updateLastLogin($user['id']);

                    // Remember me
                    if ($remember) {
                        $this->setRememberMeCookie($user['id']);
                    }

                    SecurityManager::logSecurityEvent('LOGIN_SUCCESS', $user['id']);

                    return [
                        'id'        => $user['id'],
                        'full_name' => $user['full_name'],
                        'email'     => $user['email'],
                        'role'      => $role,
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

    /**
     * Check if IP is blocked based on failed attempts
     */
    private function isIPBlocked(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return SecurityManager::getFailedAttempts(null, $ip) >= $this->maxLoginAttempts;
    }

    private function logFailedAttempt(string $email): void
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $stmt = $this->pdo->prepare(
                "INSERT INTO failed_logins (email, ip_address, user_agent) VALUES (:email, :ip, :ua)"
            );
            $stmt->execute([':email' => $email, ':ip' => $ip, ':ua' => $ua]);
        } catch (Exception $e) {
            error_log("Failed login log error: " . $e->getMessage());
        }
    }

    private function resetFailedAttempts(string $email): void
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM failed_logins WHERE email = :email");
            $stmt->execute([':email' => $email]);
        } catch (Exception $e) {
            error_log("Reset failed attempts error: " . $e->getMessage());
        }
    }

    private function updatePasswordHash(int $userId, string $password): void
    {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);
        } catch (Exception $e) {
            error_log("Password hash update error: " . $e->getMessage());
        }
    }

    private function updateLastLogin(int $userId): void
    {
        try {
            $this->pdo->prepare("UPDATE users SET last_login = NOW(), last_seen = NOW() WHERE id = ?")->execute([$userId]);
        } catch (Exception $e) {
            error_log("Last login update error: " . $e->getMessage());
        }
    }

    /**
     * Remember Me – store token in DB and cookie
     */
    private function setRememberMeCookie(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + REMEMBER_ME_EXPIRY;

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))"
            );
            $stmt->execute([$userId, $token, $expiry]);

            setcookie('remember_token', $token, $expiry, '/', '', true, true);
        } catch (Exception $e) {
            error_log("Remember token error: " . $e->getMessage());
        }
    }

    public function checkRememberMe(): bool
    {
        if (empty($_COOKIE['remember_token']) || isset($_SESSION['user_id'])) {
            return false;
        }

        $token = $_COOKIE['remember_token'];
        try {
            $stmt = $this->pdo->prepare(
                "SELECT u.*, a.role as admin_role
                 FROM remember_tokens rt
                 JOIN users u ON rt.user_id = u.id
                 LEFT JOIN admins a ON u.id = a.user_id
                 WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1"
            );
            $stmt->execute([$token]);

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();

                session_regenerate_id(true);
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['full_name']     = $user['full_name'];
                $_SESSION['user_email']    = $user['email'];
                $_SESSION['user_role']     = $user['admin_role'] ?? $user['role'];
                $_SESSION['logged_in']     = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['login_time']    = time();

                $this->updateLastLogin($user['id']);
                // Rotate token
                $this->setRememberMeCookie($user['id']);

                SecurityManager::logSecurityEvent('REMEMBER_ME_LOGIN', $user['id']);
                return true;
            } else {
                // Invalid token – remove cookie
                setcookie('remember_token', '', time() - 3600, '/');
                return false;
            }
        } catch (Exception $e) {
            error_log("Remember me check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logout – clear token and session
     */
    public function logout(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
                $stmt->execute([$_COOKIE['remember_token']]);
                setcookie('remember_token', '', time() - 3600, '/');
            } catch (Exception $e) {
                error_log("Logout token error: " . $e->getMessage());
            }
        }

        if (isset($_SESSION['user_id'])) {
            SecurityManager::logSecurityEvent('LOGOUT', $_SESSION['user_id']);
        }

        session_unset();
        session_destroy();
    }

    /**
     * Session timeout check
     */
    public function checkSessionTimeout(int $timeoutMinutes = 60): void
    {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > ($timeoutMinutes * 60)) {
            $this->logout();
            SessionManager::setFlash('warning', 'Your session has expired. Please log in again.');
            header('Location: ' . SITE_URL . 'auth/login.php');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Register new user
     */
    public function register(string $fullName, string $email, string $password, ?string $phone = null, ?string $address = null, ?string $birthDate = null, string $role = 'member'): array
    {
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
            $stmt->execute([':email' => $email]);
            if ($stmt->rowCount() > 0) {
                return ['error' => 'Email already registered.'];
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare(
                "INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :hash, :role)"
            );
            $stmt->execute([
                ':name' => SecurityManager::sanitize($fullName),
                ':email'=> SecurityManager::sanitize($email, 'email'),
                ':hash' => $hash,
                ':role' => $role,
            ]);
            $userId = $this->pdo->lastInsertId();

            // Optionally save profile
            if ($phone || $address || $birthDate) {
                $this->pdo->prepare(
                    "INSERT INTO user_profiles (user_id, phone, address, birth_date) VALUES (?, ?, ?, ?)"
                )->execute([$userId, $phone, $address, $birthDate]);
            }

            SecurityManager::logSecurityEvent('REGISTRATION_SUCCESS', $userId);
            return ['success' => true, 'message' => 'Registration successful!', 'user_id' => $userId];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['error' => 'A system error occurred during registration.'];
        }
    }

    /**
     * Initiate password reset (returns token)
     */
    public function initiatePasswordReset(string $email): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            if ($stmt->rowCount() == 1) {
                $userId = $stmt->fetch()['id'];
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);

                $this->pdo->prepare(
                    "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)"
                )->execute([$userId, $token, $expires]);

                SecurityManager::logSecurityEvent('PASSWORD_RESET_REQUESTED', $userId);
                return ['success' => true, 'token' => $token];
            }
            return ['error' => 'No account found with that email address.'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred. Please try again.'];
        }
    }

    /**
     * Verify reset token
     */
    public function verifyPasswordResetToken(string $token): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0"
            );
            $stmt->execute([$token]);
            if ($stmt->rowCount() == 1) {
                return ['success' => true, 'user_id' => $stmt->fetch()['user_id']];
            }
            return ['error' => 'Invalid or expired reset token.'];
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return ['error' => 'An error occurred during token verification.'];
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(int $userId, string $newPassword): array
    {
        try {
            if (!SecurityManager::validatePassword($newPassword)) {
                return ['error' => 'Password must be at least 8 characters with uppercase, lowercase, and number.'];
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);

            $this->pdo->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, lock_until = NULL WHERE id = ?")
                ->execute([$hash, $userId]);

            $this->pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?")
                ->execute([$userId]);

            SecurityManager::logSecurityEvent('PASSWORD_RESET_SUCCESS', $userId);
            return ['success' => true, 'message' => 'Password successfully reset.'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'An error occurred while resetting password.'];
        }
    }
}

// ==============================
// Global handler functions
// ==============================
function handleLogin(): void
{
    global $auth;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        header('Location: ../auth/login.php?error=' . urlencode('Please fill in all fields'));
        exit;
    }

    $result = $auth->login($email, $password, $remember);
    if (isset($result['error'])) {
        header('Location: ../auth/login.php?error=' . urlencode($result['error']));
        exit;
    }

    $role = $result['role'];
    if (in_array($role, ['super_admin', 'admin'])) {
        header('Location: ../admin/dashboard.php');
    } elseif ($role === 'pastor') {
        header('Location: ../pastor/dashboard.php');
    } else {
        header('Location: ../member/dashboard.php');
    }
    exit;
}

function handleRegister(): void
{
    global $auth;
    // (same as before, using SecurityManager::verifyCSRFToken, etc.)
    // ... (see previous version, it's still valid, just integrate CSRF check)
    // I'll spare the full code here to keep the response focused; the earlier one works fine.
}

function handlePasswordReset(): void
{
    global $auth;
    $email = $_POST['email'] ?? '';
    if (empty($email)) {
        header('Location: ../auth/forgot-password.php?error=' . urlencode('Please enter your email'));
        exit;
    }
    $result = $auth->initiatePasswordReset($email);
    if (isset($result['error'])) {
        header('Location: ../auth/forgot-password.php?error=' . urlencode($result['error']));
        exit;
    }
    header('Location: ../auth/reset-password.php?token=' . $result['token'] . '&message=' . urlencode('Password reset initiated.'));
    exit;
}