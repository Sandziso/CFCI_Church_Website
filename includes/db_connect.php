<?php
// includes/db_connect.php - Database Connection Handler

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
// Database connection handler
class DBConnect {
    private static $instance = null;
    private $connection;
    private $database;
    
    private function __construct() {
        $this->initialize();
    }
    
    // Singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Get database connection
    public static function getConnection() {
        $instance = self::getInstance();
        return $instance->connection;
    }
    
    // Get database instance
    public static function getDatabase() {
        $instance = self::getInstance();
        return $instance->database;
    }
    
    private function initialize() {
        try {
            // Initialize Database
            $this->database = Database::getInstance();
            $this->connection = $this->database->getConnection();
            
            // Test connection
            $test_result = $this->database->testConnection();
            
            if (!$test_result['success']) {
                throw new Exception("Database connection test failed: " . $test_result['message']);
            }
            
            // Log successful connection in development mode
            if (defined('DEV_MODE') && DEV_MODE) {
                error_log("Database connected: " . $test_result['database'] . " @ " . $test_result['host']);
            }
            
        } catch (Exception $e) {
            $this->handleConnectionError($e);
        }
    }
    
    private function handleConnectionError(Exception $e) {
        $error_message = "Database Connection Error: " . $e->getMessage();
        
        // Log error
        error_log($error_message);
        
        if (defined('LOG_PATH')) {
            $log_file = LOG_PATH . 'connection_errors.log';
            $log_entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $error_message);
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
        
        // Show appropriate error message
        if (defined('DEV_MODE') && DEV_MODE) {
            die("<div class='alert alert-danger'>
                <h3>Database Connection Error</h3>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>File:</strong> " . $e->getFile() . "</p>
                <p><strong>Line:</strong> " . $e->getLine() . "</p>
                <p>Please check your database configuration in config.php</p>
            </div>");
        } else {
            die("<div class='alert alert-danger text-center'>
                <h3>Service Temporarily Unavailable</h3>
                <p>We're experiencing technical difficulties. Please try again later.</p>
                <p>If the problem persists, please contact the church administrator.</p>
            </div>");
        }
    }
    
    // Execute query with error handling
    public static function executeQuery($sql, $params = []) {
        try {
            $db = self::getDatabase();
            return $db->query($sql, $params);
        } catch (Exception $e) {
            self::handleQueryError($e, $sql);
            return false;
        }
    }
    
    // Fetch single row
    public static function fetchOne($sql, $params = []) {
        try {
            $stmt = self::executeQuery($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Fetch all rows
    public static function fetchAll($sql, $params = []) {
        try {
            $stmt = self::executeQuery($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Insert data
    public static function insert($table, $data) {
        try {
            $db = self::getDatabase();
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($data));
            
            return $db->lastInsertId();
        } catch (Exception $e) {
            self::handleQueryError($e, "INSERT INTO $table");
            return false;
        }
    }
    
    // Update data
    public static function update($table, $data, $where, $where_params = []) {
        try {
            $db = self::getDatabase();
            $set = [];
            $params = [];
            
            foreach ($data as $column => $value) {
                $set[] = "$column = ?";
                $params[] = $value;
            }
            
            $params = array_merge($params, $where_params);
            $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            self::handleQueryError($e, "UPDATE $table");
            return false;
        }
    }
    
    // Delete data
    public static function delete($table, $where, $params = []) {
        try {
            $db = self::getDatabase();
            $sql = "DELETE FROM $table WHERE $where";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            self::handleQueryError($e, "DELETE FROM $table");
            return false;
        }
    }
    
    // Check if record exists
    public static function exists($table, $where, $params = []) {
        try {
            $sql = "SELECT COUNT(*) FROM $table WHERE $where";
            $stmt = self::executeQuery($sql, $params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Get count
    public static function count($table, $where = '1', $params = []) {
        try {
            $sql = "SELECT COUNT(*) FROM $table WHERE $where";
            $stmt = self::executeQuery($sql, $params);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Begin transaction
    public static function beginTransaction() {
        try {
            $db = self::getDatabase();
            return $db->beginTransaction();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Commit transaction
    public static function commit() {
        try {
            $db = self::getDatabase();
            return $db->commit();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Rollback transaction
    public static function rollBack() {
        try {
            $db = self::getDatabase();
            return $db->rollBack();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Escape string
    public static function escape($string) {
        try {
            $conn = self::getConnection();
            return $conn->quote($string);
        } catch (Exception $e) {
            return "'" . addslashes($string) . "'";
        }
    }
    
    // Handle query errors
    private static function handleQueryError(Exception $e, $sql = '') {
        $error_message = "Query Error: " . $e->getMessage() . " | Query: " . $sql;
        
        // Log error
        error_log($error_message);
        
        if (defined('LOG_PATH')) {
            $log_file = LOG_PATH . 'query_errors.log';
            $log_entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $error_message);
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
        
        // Show error in development mode
        if (defined('DEV_MODE') && DEV_MODE) {
            echo "<div class='alert alert-danger'>
                <h4>Database Query Error</h4>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>Query:</strong> " . htmlspecialchars($sql) . "</p>
                <p><strong>File:</strong> " . $e->getFile() . "</p>
                <p><strong>Line:</strong> " . $e->getLine() . "</p>
            </div>";
        }
    }
    
    // Get last insert ID
    public static function lastInsertId() {
        try {
            $db = self::getDatabase();
            return $db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Close all connections
    public static function closeAll() {
        if (self::$instance !== null) {
            self::$instance->database->closeConnection();
            self::$instance = null;
        }
    }
}

// Auto-initialize database connection
try {
    DBConnect::getInstance();
} catch (Exception $e) {
    // Connection error already handled in initialize()
}

// Global database helper functions
function db_query($sql, $params = []) {
    return DBConnect::executeQuery($sql, $params);
}

function db_fetch($sql, $params = []) {
    return DBConnect::fetchOne($sql, $params);
}

function db_fetch_all($sql, $params = []) {
    return DBConnect::fetchAll($sql, $params);
}

function db_insert($table, $data) {
    return DBConnect::insert($table, $data);
}

function db_update($table, $data, $where, $params = []) {
    return DBConnect::update($table, $data, $where, $params);
}

function db_delete($table, $where, $params = []) {
    return DBConnect::delete($table, $where, $params);
}

function db_count($table, $where = '1', $params = []) {
    return DBConnect::count($table, $where, $params);
}

function db_exists($table, $where, $params = []) {
    return DBConnect::exists($table, $where, $params);
}

// Global database connection variable
$conn = DBConnect::getConnection();
$db = DBConnect::getDatabase();

// Initialize ChurchDB if needed
if (!isset($churchDB) && class_exists('ChurchDB')) {
    $churchDB = new ChurchDB($conn);
}
?>