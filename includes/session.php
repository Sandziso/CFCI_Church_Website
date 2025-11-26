<?php
// includes/session.php

class SessionManager {
    private $db;
    
    public function __construct($database_connection = null) {
        // Set database connection if provided and if we want to use database sessions
        if ($database_connection && defined('USE_DATABASE_SESSIONS') && USE_DATABASE_SESSIONS) {
            $this->db = $database_connection;
            $this->setupDatabaseSessions();
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize session if needed
        $this->initializeSession();
        
        // Validate session integrity and update activity
        $this->validateSessionIntegrity();
        $this->updateUserActivity();
    }
    
    private function setupDatabaseSessions() {
        // Only set up database sessions if session not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_set_save_handler(
                array($this, 'sessionOpen'),
                array($this, 'sessionClose'),
                array($this, 'sessionRead'),
                array($this, 'sessionWrite'),
                array($this, 'sessionDestroy'),
                array($this, 'sessionGC')
            );
        }
    }
    
    private function initializeSession() {
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = true;
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['created_at'] = time();
        }
    }
    
    // Session save handler methods (for database sessions)
    public function sessionOpen($savePath, $sessionName) {
        return true;
    }
    
    public function sessionClose() {
        return true;
    }
    
    public function sessionRead($sessionId) {
        try {
            $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stmt->execute([$sessionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['data'];
            }
        } catch (Exception $e) {
            error_log("Session read error: " . $e->getMessage());
        }
        
        return '';
    }
    
    public function sessionWrite($sessionId, $data) {
        try {
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $stmt = $this->db->prepare("
                INSERT INTO sessions (id, user_id, data, last_activity) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                user_id = VALUES(user_id), 
                data = VALUES(data), 
                last_activity = NOW()
            ");
            
            return $stmt->execute([$sessionId, $user_id, $data]);
            
        } catch (Exception $e) {
            error_log("Session write error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sessionDestroy($sessionId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
            return $stmt->execute([$sessionId]);
        } catch (Exception $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sessionGC($maxlifetime) {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)");
            return $stmt->execute([$maxlifetime]);
        } catch (Exception $e) {
            error_log("Session GC error: " . $e->getMessage());
            return false;
        }
    }
    
    // Session management methods
    public function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    public function destroy() {
        // Remove session from database if using database sessions
        if ($this->db && $this->has('user_id')) {
            try {
                $stmt = $this->db->prepare("DELETE FROM sessions WHERE user_id = ?");
                $stmt->execute([$this->get('user_id')]);
            } catch (Exception $e) {
                error_log("Session cleanup error: " . $e->getMessage());
            }
        }
        
        // Clear session data
        $_SESSION = array();
        
        // Delete session cookie
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
        
        // Destroy session
        session_destroy();
    }
    
    // User session methods
    public function loginUser($user_data) {
        $this->regenerateSession();
        
        $_SESSION['user_id'] = $user_data['id'] ?? null;
        $_SESSION['user_email'] = $user_data['email'] ?? '';
        $_SESSION['user_name'] = $user_data['full_name'] ?? '';
        $_SESSION['user_role'] = $user_data['role'] ?? 'member';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Update last login in database
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user_data['id']]);
            } catch (Exception $e) {
                error_log("Update last login error: " . $e->getMessage());
            }
        }
    }
    
    public function logoutUser() {
        $this->destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getUserRole() {
        return $this->get('user_role', 'guest');
    }
    
    public function getUserId() {
        return $this->get('user_id');
    }
    
    public function getUserName() {
        return $this->get('user_name', 'Member');
    }
    
    public function getUserEmail() {
        return $this->get('user_email', '');
    }
    
    // Authorization methods
    public function requireLogin($redirect_url = '/auth/login.php') {
        if (!$this->isLoggedIn()) {
            $this->set('redirect_after_login', $_SERVER['REQUEST_URI']);
            header('Location: ' . $redirect_url);
            exit;
        }
    }
    
    public function requireRole($required_role, $redirect_url = '/auth/login.php') {
        $this->requireLogin($redirect_url);
        
        $user_role = $this->getUserRole();
        
        // Role hierarchy: admin > pastor > member
        $role_hierarchy = [
            'member' => 1,
            'pastor' => 2,
            'admin' => 3
        ];
        
        $user_level = isset($role_hierarchy[$user_role]) ? $role_hierarchy[$user_role] : 0;
        $required_level = isset($role_hierarchy[$required_role]) ? $role_hierarchy[$required_role] : 0;
        
        if ($user_level < $required_level) {
            http_response_code(403);
            $this->setFlash('error', 'Access denied. Insufficient permissions.');
            header('Location: /member/dashboard.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireRole('admin');
    }
    
    public function requirePastor() {
        $this->requireRole('pastor');
    }
    
    // Flash message methods
    public function setFlash($type, $message) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
    }
    
    public function getFlashMessages() {
        $messages = $this->get('flash_messages', []);
        $this->remove('flash_messages');
        return $messages;
    }
    
    public function hasFlashMessages() {
        return !empty($_SESSION['flash_messages']);
    }
    
    // CSRF protection methods
    public function generateCSRFToken($name = 'csrf_token') {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$name] = $token;
        $_SESSION[$name . '_time'] = time();
        return $token;
    }
    
    public function validateCSRFToken($token, $name = 'csrf_token', $expire_time = 3600) {
        if (!isset($_SESSION[$name]) || !isset($_SESSION[$name . '_time'])) {
            return false;
        }
        
        $stored_token = $_SESSION[$name];
        $token_time = $_SESSION[$name . '_time'];
        
        // Remove token after validation (one-time use)
        unset($_SESSION[$name], $_SESSION[$name . '_time']);
        
        // Check if token matches and hasn't expired
        if (hash_equals($stored_token, $token) && (time() - $token_time) < $expire_time) {
            return true;
        }
        
        return false;
    }
    
    // Security methods
    public function validateSessionIntegrity() {
        // Check user agent consistency
        $current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stored_user_agent = $this->get('user_agent');
        
        if (!$stored_user_agent) {
            $this->set('user_agent', $current_user_agent);
        } elseif ($stored_user_agent !== $current_user_agent) {
            // User agent changed - possible session hijacking
            $this->destroy();
            $this->setFlash('error', 'Session security violation detected. Please log in again.');
            header('Location: /auth/login.php');
            exit;
        }
    }
    
    // Activity tracking
    public function updateUserActivity() {
        $_SESSION['last_activity'] = time();
        
        // Auto-logout after period of inactivity (optional)
        $inactive_timeout = 86400; // 24 hours in seconds
        $last_activity = $this->get('last_activity', 0);
        
        if ((time() - $last_activity) > $inactive_timeout) {
            $this->logoutUser();
            $this->setFlash('info', 'You have been automatically logged out due to inactivity.');
            header('Location: /auth/login.php');
            exit;
        }
    }
    
    // Check if session needs regeneration
    public function checkSessionRegeneration() {
        $regeneration_interval = 1800; // 30 minutes
        $last_regeneration = $this->get('last_regeneration', 0);
        
        if ((time() - $last_regeneration) > $regeneration_interval) {
            $this->regenerateSession();
        }
    }
}

// Initialize session manager
try {
    // Get database connection if available
    $db_connection = null;
    if (class_exists('Database')) {
        $database = new Database();
        $db_connection = $database->getConnection();
    }
    
    // Create session manager instance
    $session = new SessionManager($db_connection);
    
} catch (Exception $e) {
    error_log("Session initialization error: " . $e->getMessage());
    // Continue with basic session management if initialization fails
    $session = new SessionManager();
}