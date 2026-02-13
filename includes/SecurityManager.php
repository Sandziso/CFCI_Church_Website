<?php
// includes/SecurityManager.php

class SecurityManager {
    private static $pdo = null;
    
    public static function initialize($pdo) {
        self::$pdo = $pdo;
    }

    // From auth.php Security class
    public static function logSecurityEvent($event_type, $user_id = null, $details = null) {
        if (!self::$pdo) return false;
        
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
        if (!self::$pdo) return 0;
        
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

    // From session.php Security class
    public static function sanitize($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : '';
                
            case 'url':
                $input = filter_var($input, FILTER_SANITIZE_URL);
                return filter_var($input, FILTER_VALIDATE_URL) ? $input : '';
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'string':
            default:
                $input = stripslashes($input);
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    // Alias for backward compatibility (from auth.php)
    public static function sanitizeInput($input) {
        return self::sanitize($input, 'string');
    }

    // Validation methods
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone($phone) {
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($clean_phone) >= 10 && strlen($clean_phone) <= 15;
    }

    public static function validatePassword($password) {
        // Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number (stronger than auth.php's version)
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }

    public static function checkPasswordStrength($password) {
        $strength = 0;
        
        if (strlen($password) >= 8) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/\d/', $password)) $strength++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $strength++;
        
        return $strength;
    }

    // CSRF Token Management
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

    // File validation
    public static function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = 5242880) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $errors[] = $upload_errors[$file['error']] ?? 'Unknown upload error';
            return ['success' => false, 'errors' => $errors];
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = 'File too large. Maximum size: ' . ($max_size / 1024 / 1024) . 'MB';
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_types);
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        return ['success' => true, 'extension' => $file_ext];
    }

    public static function secureUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = 5242880) {
        $validation = self::validateFileUpload($file, $allowed_types, $max_size);
        
        if (!$validation['success']) {
            return $validation;
        }
        
        $new_filename = bin2hex(random_bytes(16)) . '_' . time() . '.' . $validation['extension'];
        
        $upload_path = defined('UPLOAD_PATH') ? UPLOAD_PATH . $new_filename : __DIR__ . '/../uploads/' . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            chmod($upload_path, 0644);
            
            return [
                'success' => true,
                'filename' => $new_filename,
                'path' => '/uploads/' . $new_filename,
                'size' => $file['size']
            ];
        }
        
        return ['success' => false, 'errors' => ['Failed to upload file']];
    }

    // Rate limiting
    public static function checkRateLimit($key, $limit = 5, $timeout = 300) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cache_key = "rate_limit_{$key}_{$ip}";
        
        $attempts = $_SESSION[$cache_key] ?? 0;
        $first_attempt = $_SESSION[$cache_key . '_time'] ?? time();
        
        if (time() - $first_attempt > $timeout) {
            $_SESSION[$cache_key] = 1;
            $_SESSION[$cache_key . '_time'] = time();
            return true;
        }
        
        if ($attempts >= $limit) {
            return false;
        }
        
        $_SESSION[$cache_key] = $attempts + 1;
        return true;
    }

    // Utility methods
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateUniqueFilename($extension) {
        return time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }
}

// Global helper functions with existence checks
if (!function_exists('sanitizeUserInput')) {
    function sanitizeUserInput($data) {
        return SecurityManager::sanitize($data);
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return SecurityManager::validateEmail($email);
    }
}

if (!function_exists('isValidPassword')) {
    function isValidPassword($password) {
        return SecurityManager::validatePassword($password);
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return SecurityManager::generateCSRFToken();
    }
}
?>