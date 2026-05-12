<?php
/**
 * SecurityManager.php – Consolidated Security Helpers
 */

class SecurityManager
{
    private static $pdo;

    /**
     * Initialize with PDO connection (call once from Auth or main)
     */
    public static function initialize($pdo)
    {
        self::$pdo = $pdo;
    }

    /**
     * CSRF token generation (stored in session)
     */
    public static function generateCSRFToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token from POST
     */
    public static function verifyCSRFToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) return false;
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        // Regenerate after use for one‑time protection
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $valid;
    }

    /**
     * Sanitize input by type
     */
    public static function sanitize($value, $type = 'string')
    {
        $value = trim($value);
        switch ($type) {
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'int':
                return (int) $value;
            case 'string':
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            default:
                return strip_tags($value);
        }
    }

    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password complexity
     */
    public static function validatePassword(string $password): bool
    {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 digit
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    /**
     * Count failed attempts for IP or email
     */
    public static function getFailedAttempts($email = null, $ip = null): int
    {
        if (!self::$pdo) return 0;
        try {
            $query = "SELECT COUNT(*) FROM failed_logins WHERE ";
            $params = [];
            if ($email) {
                $query .= "email = :email ";
                $params[':email'] = $email;
            }
            if ($ip) {
                $query .= ($email ? "AND " : "") . "ip_address = :ip AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
                $params[':ip'] = $ip;
            }
            $stmt = self::$pdo->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("getFailedAttempts error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Log security event into `security_logs`
     */
    public static function logSecurityEvent($eventType, $userId = null, $details = null)
    {
        if (!self::$pdo) return;
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $stmt = self::$pdo->prepare(
                "INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details)
                 VALUES (:event, :uid, :ip, :ua, :details)"
            );
            $stmt->execute([
                ':event'   => $eventType,
                ':uid'     => $userId,
                ':ip'      => $ip,
                ':ua'      => $ua,
                ':details' => $details
            ]);
        } catch (Exception $e) {
            error_log("Security log error: " . $e->getMessage());
        }
    }
}