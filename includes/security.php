<?php
// includes/security.php

class SecurityManager {
    private $db;
    private $session;
    
    public function __construct($database_connection = null, $session_manager = null) {
        $this->db = $database_connection;
        $this->session = $session_manager;
        
        // Set security headers
        $this->setSecurityHeaders();
    }
    
    // INPUT VALIDATION AND SANITIZATION
    public function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $input);
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        switch ($type) {
            case 'email':
                $input = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                return $this->validateEmail($input) ? $input : '';
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
                
            case 'html':
                // Allow safe HTML for content areas
                return $this->sanitizeHTML($input);
                
            case 'sql':
                // For use in raw SQL (should be avoided when possible)
                return $this->db ? $this->db->quote($input) : addslashes($input);
                
            case 'filename':
                return $this->sanitizeFilename($input);
                
            case 'string':
            default:
                return filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        }
    }
    
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function validatePhone($phone) {
        // Basic phone number validation
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }
    
    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
        }
        
        if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (PASSWORD_REQUIRE_SYMBOLS && !preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }
    
    // XSS PREVENTION
    private function sanitizeHTML($html) {
        if (empty($html)) return '';
        
        // Allow only safe HTML tags and attributes
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 
            'p,br,strong,em,u,ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,code,pre,table,thead,tbody,tr,th,td,a[href|title],img[src|alt|width|height]'
        );
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
    
    public function escapeOutput($data, $context = 'html') {
        if (is_array($data)) {
            return array_map(function($item) use ($context) {
                return $this->escapeOutput($item, $context);
            }, $data);
        }
        
        switch ($context) {
            case 'html':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'attr':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'js':
                return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                
            case 'url':
                return urlencode($data);
                
            default:
                return $data;
        }
    }
    
    // FILE UPLOAD SECURITY
    public function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) { // 5MB default
        $errors = [];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $this->getUploadError($file['error']);
            return [false, $errors];
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            $errors[] = "File size exceeds maximum allowed size of " . ($max_size / 1024 / 1024) . "MB";
        }
        
        // Check file type
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
            $errors[] = "File type not allowed. Allowed types: " . implode(', ', array_keys($allowed_types));
        }
        
        // Check for double extensions
        if (preg_match('/\.(php|phtml|php3|php4|php5|php7|phar|js|exe|bat|cmd|sh)$/i', $file['name'])) {
            $errors[] = "Potentially dangerous file type detected";
        }
        
        // Validate image files specifically
        if (strpos($mime_type, 'image/') === 0) {
            $image_info = getimagesize($file['tmp_name']);
            if (!$image_info) {
                $errors[] = "Invalid image file";
            }
        }
        
        return [empty($errors), $errors];
    }
    
    private function getUploadError($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload";
            default:
                return "Unknown upload error";
        }
    }
    
    public function sanitizeFilename($filename) {
        // Remove path traversal characters
        $filename = basename($filename);
        
        // Replace spaces and special characters
        $filename = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $filename);
        
        // Remove multiple consecutive underscores
        $filename = preg_replace('/_{2,}/', '_', $filename);
        
        // Limit filename length
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }
        
        return $filename;
    }
    
    // BRUTE FORCE PROTECTION
    public function checkBruteForce($identifier, $type = 'login') {
        if (!$this->db) return false;
        
        try {
            $now = time();
            $valid_attempts = $now - (60 * 15); // 15 minutes ago
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts 
                FROM security_logs 
                WHERE identifier = ? AND type = ? AND attempt_time > ?
            ");
            
            $stmt->execute([$identifier, $type, date('Y-m-d H:i:s', $valid_attempts)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
            
        } catch (Exception $e) {
            error_log("Brute force check error: " . $e->getMessage());
            return false;
        }
    }
    
    public function recordFailedAttempt($identifier, $type = 'login', $ip_address = null) {
        if (!$this->db) return false;
        
        try {
            $ip_address = $ip_address ?: $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->db->prepare("
                INSERT INTO security_logs (identifier, type, ip_address, user_agent, attempt_time) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$identifier, $type, $ip_address, $user_agent]);
            
        } catch (Exception $e) {
            error_log("Failed attempt recording error: " . $e->getMessage());
            return false;
        }
    }
    
    public function clearFailedAttempts($identifier, $type = 'login') {
        if (!$this->db) return false;
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM security_logs 
                WHERE identifier = ? AND type = ?
            ");
            
            return $stmt->execute([$identifier, $type]);
            
        } catch (Exception $e) {
            error_log("Clear failed attempts error: " . $e->getMessage());
            return false;
        }
    }
    
    // PASSWORD SECURITY
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function passwordNeedsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    // SECURITY HEADERS
    private function setSecurityHeaders() {
        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://trusted.cdn.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'"
        ];
        
        header("Content-Security-Policy: " . implode("; ", $csp));
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        
        // Remove sensitive headers
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    
    // RATE LIMITING
    public function checkRateLimit($key, $max_requests = 100, $time_window = 3600) {
        if (!$this->db) return true;
        
        try {
            $window_start = time() - $time_window;
            
            // Clean old entries
            $this->cleanRateLimitRecords($time_window);
            
            // Count recent requests
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as requests 
                FROM rate_limits 
                WHERE request_key = ? AND request_time > ?
            ");
            
            $stmt->execute([$key, date('Y-m-d H:i:s', $window_start)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['requests'] >= $max_requests) {
                return false;
            }
            
            // Record this request
            $stmt = $this->db->prepare("
                INSERT INTO rate_limits (request_key, request_time, ip_address) 
                VALUES (?, NOW(), ?)
            ");
            
            $stmt->execute([$key, $this->getClientIP()]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return true; // Fail open
        }
    }
    
    private function cleanRateLimitRecords($time_window) {
        try {
            $cutoff_time = date('Y-m-d H:i:s', time() - $time_window);
            $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE request_time < ?");
            $stmt->execute([$cutoff_time]);
        } catch (Exception $e) {
            error_log("Rate limit cleanup error: " . $e->getMessage());
        }
    }
    
    // SQL INJECTION PREVENTION (additional layer)
    public function validateSQLInput($input, $expected_type = 'string') {
        // Common SQL injection patterns
        $sql_patterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)\b/i',
            '/\b(OR|AND)\s+[\d\']/i',
            '/--|\/\*|\*\//',
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i'
        ];
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logSecurityEvent('sql_injection_attempt', $input);
                return false;
            }
        }
        
        // Type-specific validation
        switch ($expected_type) {
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT) !== false;
            case 'email':
                return $this->validateEmail($input);
            case 'date':
                return $this->validateDate($input);
            default:
                return true;
        }
    }
    
    // SECURITY LOGGING
    public function logSecurityEvent($event_type, $details = '', $user_id = null) {
        if (!$this->db) return false;
        
        try {
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            
            $stmt = $this->db->prepare("
                INSERT INTO security_events (event_type, user_id, ip_address, user_agent, request_uri, details, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$event_type, $user_id, $ip_address, $user_agent, $request_uri, $details]);
            
        } catch (Exception $e) {
            error_log("Security event logging error: " . $e->getMessage());
            return false;
        }
    }
    
    // UTILITY METHODS
    public function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if ($this->validateIP($ip)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
    
    public function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // REQUEST VALIDATION
    public function validateRequestMethod($allowed_methods = ['GET', 'POST']) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (!in_array($method, $allowed_methods)) {
            $this->logSecurityEvent('invalid_request_method', "Method: $method");
            http_response_code(405);
            die('Method Not Allowed');
        }
        
        return $method;
    }
    
    public function validateReferer($allowed_domains = []) {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (empty($referer)) {
            return false;
        }
        
        $referer_domain = parse_url($referer, PHP_URL_HOST);
        $current_domain = $_SERVER['HTTP_HOST'];
        
        // Always allow same-domain requests
        if ($referer_domain === $current_domain) {
            return true;
        }
        
        // Check against allowed domains
        return in_array($referer_domain, $allowed_domains);
    }
    
    // DATA ENCRYPTION (for sensitive data)
    public function encryptData($data, $key = null) {
        $key = $key ?: ENCRYPTION_KEY;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public function decryptData($data, $key = null) {
        $key = $key ?: ENCRYPTION_KEY;
        
        if (empty($data)) return '';
        
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }
}

// Security configuration
class SecurityConfig {
    const ALLOWED_FILE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
    ];
    
    const MAX_FILE_SIZE = 10485760; // 10MB
    
    const RATE_LIMITS = [
        'login' => ['max_attempts' => 5, 'window' => 900], // 5 attempts per 15 minutes
        'api' => ['max_requests' => 100, 'window' => 3600], // 100 requests per hour
        'contact_form' => ['max_requests' => 3, 'window' => 3600] // 3 submissions per hour
    ];
}

// Initialize security manager
try {
    $security = new SecurityManager($conn, $session);
} catch (Exception $e) {
    error_log("Security manager initialization failed: " . $e->getMessage());
    // Create without database connection
    $security = new SecurityManager();
}

// Security helper functions
function sanitize($input, $type = 'string') {
    global $security;
    return $security->sanitizeInput($input, $type);
}

function escape($data, $context = 'html') {
    global $security;
    return $security->escapeOutput($data, $context);
}

function validate_csrf_token($token, $name = 'csrf_token') {
    global $session;
    return $session->validateCSRFToken($token, $name);
}

// Input validation shortcuts
function validate_email($email) {
    global $security;
    return $security->validateEmail($email);
}

function validate_phone($phone) {
    global $security;
    return $security->validatePhone($phone);
}

function validate_date($date, $format = 'Y-m-d') {
    global $security;
    return $security->validateDate($date, $format);
}

// Security logging shortcut
function log_security_event($event_type, $details = '', $user_id = null) {
    global $security;
    return $security->logSecurityEvent($event_type, $details, $user_id);
}