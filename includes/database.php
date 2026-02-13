<?php
// includes/database.php - Enhanced Database Class with Auto-Creation

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $port;
    public $conn;
    private static $instance = null;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = 'utf8mb4';
        $this->port = defined('DB_PORT') ? DB_PORT : '3306';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function ping() {
        if ($this->conn === null) return false;
        try {
            $this->conn->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                $this->conn = null;
                return false;
            }
            throw $e;
        }
    }
    
    public function createDatabaseIfNotExists() {
        try {
            // Connect without database name
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $temp_conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Create database if it doesn't exist
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` 
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $temp_conn->exec($sql);
            
            return true;
        } catch (PDOException $e) {
            $this->logError("Database creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConnection() {
        if ($this->conn === null || !$this->ping()) {
            try {
                // First, ensure database exists
                if (!$this->checkDatabaseExists()) {
                    if (!$this->createDatabaseIfNotExists()) {
                        throw new Exception("Failed to create database: " . $this->db_name);
                    }
                }
                
                $dsn = "mysql:host=" . $this->host . 
                       ";dbname=" . $this->db_name . 
                       ";charset=" . $this->charset . 
                       ";port=" . $this->port;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_TIMEOUT => 30
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("SET time_zone = '+02:00'");
                
            } catch(PDOException $e) {
                $this->logError("Database connection error: " . $e->getMessage());
                
                // If database doesn't exist, try to create it and reconnect
                if ($e->errorInfo[1] == 1049) { // Database doesn't exist
                    if ($this->createDatabaseIfNotExists()) {
                        return $this->getConnection(); // Retry connection
                    }
                }
                
                if (defined('DEV_MODE') && DEV_MODE) {
                    throw new Exception("Database connection failed: " . $e->getMessage());
                } else {
                    throw new Exception("Service temporarily unavailable.");
                }
            }
        }
        return $this->conn;
    }
    
    private function checkDatabaseExists() {
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $temp_conn = new PDO($dsn, $this->username, $this->password, $options);
            $stmt = $temp_conn->query("SHOW DATABASES LIKE '" . $this->db_name . "'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function testConnection() {
        try {
            $this->getConnection();
            $stmt = $this->conn->query("SELECT DATABASE() as db, USER() as user");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'success' => true,
                'database' => $result['db'],
                'user' => $result['user'],
                'host' => $this->host
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function initializeDatabase($force = false) {
        try {
            $this->getConnection();
            
            if (!$force) {
                $stmt = $this->conn->query("SHOW TABLES LIKE 'users'");
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Database already initialized',
                        'tables_exist' => true
                    ];
                }
            }
            
            // Create tables if Migration.php doesn't exist
            $this->createTables();
            
            return [
                'success' => true,
                'message' => 'Database initialized successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->logError("Database initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    private function createTables() {
        // Use heredoc to avoid variable interpolation issues
        $sql = <<<SQL
        -- Users table
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'pastor', 'member', 'staff', 'elder') DEFAULT 'member',
            `is_active` BOOLEAN DEFAULT TRUE,
            `failed_login_attempts` INT DEFAULT 0,
            `lock_until` DATETIME NULL,
            `last_login` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- User profiles
        CREATE TABLE IF NOT EXISTS `user_profiles` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `phone` VARCHAR(20),
            `address` TEXT,
            `birth_date` DATE NULL,
            `profile_image` VARCHAR(255),
            `bio` TEXT,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Admins table (for admin-specific roles)
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `role` ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
            `permissions` TEXT,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Failed logins table
        CREATE TABLE IF NOT EXISTS `failed_logins` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(100) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` TEXT,
            `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Remember tokens table
        CREATE TABLE IF NOT EXISTS `remember_tokens` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `token` VARCHAR(64) NOT NULL UNIQUE,
            `expires_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Password reset tokens
        CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `token` VARCHAR(64) NOT NULL UNIQUE,
            `expires_at` DATETIME NOT NULL,
            `used` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        
        // Split SQL by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->conn->exec($statement);
            }
        }
        
        // Now create the admin user with a properly escaped password hash
        $this->createAdminUser();
    }
    
    private function createAdminUser() {
        try {
            // Check if admin user already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => 'admin@cfci.org.sz']);
            
            if ($stmt->rowCount() == 0) {
                // Create a valid bcrypt hash for 'admin123'
                // This hash is for password: admin123
                $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                
                $stmt = $this->conn->prepare("
                    INSERT INTO users (full_name, email, password_hash, role, is_active) 
                    VALUES (:full_name, :email, :password_hash, 'admin', 1)
                ");
                
                $stmt->execute([
                    ':full_name' => 'Administrator',
                    ':email' => 'admin@cfci.org.sz',
                    ':password_hash' => $password_hash
                ]);
                
                $user_id = $this->conn->lastInsertId();
                
                // Add to admins table
                $stmt = $this->conn->prepare("
                    INSERT INTO admins (user_id, role) 
                    VALUES (:user_id, 'super_admin')
                ");
                $stmt->execute([':user_id' => $user_id]);
                
                error_log("Admin user created successfully with ID: $user_id");
            }
        } catch (Exception $e) {
            error_log("Error creating admin user: " . $e->getMessage());
        }
    }
    
    private function logError($message) {
        if (defined('LOG_PATH')) {
            $log_file = LOG_PATH . 'database_errors.log';
            $log_entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
}
?>