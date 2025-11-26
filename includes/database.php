<?php
// includes/database.php

require_once 'config.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    public $conn;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = 'utf8mb4';
    }
    
    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->conn->exec("SET time_zone = '+03:00'");
            
        } catch(PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            if (DEV_MODE) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } else {
                throw new Exception("Unable to connect to database. Please try again later.");
            }
        }
        return $this->conn;
    }
    
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollBack() {
        return $this->conn->rollBack();
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function testConnection() {
        try {
            $this->getConnection();
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'database' => $this->db_name,
                'host' => $this->host
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function initializeDatabase() {
        try {
            $this->getConnection();
            
            $this->createUsersTable();
            $this->createAnnouncementsTable();
            $this->createEventsTable();
            $this->createEventAttendanceTable();
            $this->createDonationsTable();
            $this->createMinistriesTable();
            $this->createMinistryMembersTable();
            $this->createPrayerRequestsTable();
            $this->createSermonsTable();
            $this->createQuotesTable();
            $this->createAdminsTable();
            $this->createSessionsTable();
            $this->createPasswordResetTokensTable();
            
            return [
                'success' => true,
                'message' => 'Database initialized successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Database initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('member', 'pastor', 'admin') DEFAULT 'member',
            phone VARCHAR(20),
            address TEXT,
            date_of_birth DATE,
            marital_status ENUM('single', 'married', 'divorced', 'widowed'),
            join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME,
            profile_picture VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(100),
            reset_token VARCHAR(100),
            reset_expires DATETIME,
            failed_login_attempts INT DEFAULT 0,
            lock_until DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createAnnouncementsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            author_id INT NOT NULL,
            is_published BOOLEAN DEFAULT TRUE,
            expires_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createEventsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME,
            location VARCHAR(255),
            venue_details TEXT,
            event_type ENUM('service', 'meeting', 'conference', 'outreach', 'other') DEFAULT 'service',
            max_attendees INT,
            image_path VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createEventAttendanceTable() {
        $sql = "CREATE TABLE IF NOT EXISTS event_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            attended_at DATETIME,
            notes TEXT,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_event_user (event_id, user_id)
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createDonationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS donations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            amount DECIMAL(10,2) NOT NULL,
            purpose VARCHAR(255),
            payment_method ENUM('cash', 'mpesa', 'card', 'bank_transfer') DEFAULT 'mpesa',
            transaction_id VARCHAR(255),
            status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
            donation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createMinistriesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ministries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            leader_id INT,
            meeting_schedule TEXT,
            contact_email VARCHAR(255),
            contact_phone VARCHAR(20),
            is_active BOOLEAN DEFAULT TRUE,
            image_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createMinistryMembersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ministry_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ministry_id INT NOT NULL,
            user_id INT NOT NULL,
            role ENUM('leader', 'assistant_leader', 'member', 'volunteer') DEFAULT 'member',
            joined_date DATE,
            is_active BOOLEAN DEFAULT TRUE,
            notes TEXT,
            FOREIGN KEY (ministry_id) REFERENCES ministries(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_ministry_user (ministry_id, user_id)
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createPrayerRequestsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS prayer_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            request_text TEXT NOT NULL,
            status ENUM('pending', 'addressed', 'closed') DEFAULT 'pending',
            is_anonymous BOOLEAN DEFAULT FALSE,
            addressed_by_pastor_id INT,
            addressed_at DATETIME,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (addressed_by_pastor_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createSermonsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS sermons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            preacher_id INT,
            sermon_date DATE NOT NULL,
            bible_passage VARCHAR(100),
            audio_url VARCHAR(255),
            video_url VARCHAR(255),
            notes_text TEXT,
            sermon_series VARCHAR(255),
            is_published BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (preacher_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createQuotesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS quotes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            author_id INT NOT NULL,
            quote_text TEXT NOT NULL,
            visibility ENUM('public', 'private', 'members_only') DEFAULT 'public',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createAdminsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role ENUM('super_admin', 'content_admin', 'finance_admin') DEFAULT 'content_admin',
            permissions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_admin_user (user_id)
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createSessionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INT,
            data TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->conn->exec($sql);
    }
    
    private function createPasswordResetTokensTable() {
        $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_token (token)
        )";
        
        $this->conn->exec($sql);
    }
    
    public function backupDatabase($backup_path = null) {
        try {
            if ($backup_path === null) {
                $backup_path = dirname(__DIR__) . '/backups/';
            }
            
            if (!is_dir($backup_path)) {
                mkdir($backup_path, 0755, true);
            }
            
            $backup_file = $backup_path . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $command = "mysqldump --user=" . $this->username . " --password=" . $this->password . 
                      " --host=" . $this->host . " " . $this->db_name . " > " . $backup_file;
            
            system($command, $output);
            
            if ($output === 0) {
                return [
                    'success' => true,
                    'file' => $backup_file,
                    'message' => 'Backup created successfully'
                ];
            } else {
                throw new Exception("Backup failed with output: " . $output);
            }
        } catch (Exception $e) {
            error_log("Backup error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getDatabaseInfo() {
        try {
            $stmt = $this->conn->query("SELECT version() as version");
            $version = $stmt->fetchColumn();
            
            $stmt = $this->conn->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . $this->db_name . "'");
            $table_count = $stmt->fetchColumn();
            
            return [
                'version' => $version,
                'database' => $this->db_name,
                'tables' => $table_count,
                'host' => $this->host
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}

// Initialize database connection
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (defined('INIT_DB') && INIT_DB === true) {
        $init_result = $database->initializeDatabase();
        if ($init_result['success']) {
            error_log("Database initialized: " . $init_result['message']);
        } else {
            error_log("Database initialization failed: " . $init_result['message']);
        }
    }
    
} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    
    if (defined('DEV_MODE') && DEV_MODE === true) {
        die("Database error: " . $e->getMessage());
    } else {
        die("System temporarily unavailable. Please try again later.");
    }
}