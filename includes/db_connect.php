<?php
/**
 * db_connect.php – Database Connection Wrapper
 */

defined('ROOT_PATH') or die('Direct access not allowed');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class DBConnect
{
    private static $instance = null;
    private $connection;
    private $database;

    private function __construct()
    {
        $this->initialize();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getConnection(): PDO
    {
        return self::getInstance()->connection;
    }

    public static function getDatabase(): Database
    {
        return self::getInstance()->database;
    }

    private function initialize(): void
    {
        try {
            $this->database = Database::getInstance();
            $this->connection = $this->database->getConnection();

            // Run initial migration if migrations table is empty
            $this->maybeRunMigrations();

            if (defined('DEV_MODE') && DEV_MODE) {
                error_log("DB connected: " . DB_NAME);
            }
        } catch (Exception $e) {
            $this->handleConnectionError($e);
        }
    }

    private function maybeRunMigrations(): void
    {
        try {
            $stmt = $this->connection->query("SELECT COUNT(*) FROM migrations");
            $count = (int)$stmt->fetchColumn();
            if ($count === 0) {
                // Run the upgrade script automatically (idempotent)
                $upgradeFile = ROOT_PATH . 'sql/upgrade_v1.1.sql';
                if (file_exists($upgradeFile)) {
                    $this->database->runMigrationFile($upgradeFile);
                }
            }
        } catch (Exception $e) {
            // Migrations table might not exist yet – ignore
        }
    }

    private function handleConnectionError(Exception $e): void
    {
        $msg = "Database Connection Error: " . $e->getMessage();
        error_log($msg);
        if (defined('DEV_MODE') && DEV_MODE) {
            die("<div class='alert alert-danger'>$msg</div>");
        } else {
            die("<div class='alert alert-danger'>Service temporarily unavailable.</div>");
        }
    }

    // Static helpers for quick queries (same as before)
    public static function executeQuery(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::executeQuery($sql, $params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        self::executeQuery($sql, array_values($data));
        return (int) self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = ?";
            $params[] = $val;
        }
        $params = array_merge($params, $whereParams);
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
        $stmt = self::executeQuery($sql, $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $stmt = self::executeQuery("DELETE FROM $table WHERE $where", $params);
        return $stmt->rowCount();
    }

    public static function count(string $table, string $where = '1', array $params = []): int
    {
        $stmt = self::executeQuery("SELECT COUNT(*) FROM $table WHERE $where", $params);
        return (int) $stmt->fetchColumn();
    }
}

// Global helper functions (backward compat)
function db_query(string $sql, array $params = []): PDOStatement {
    return DBConnect::executeQuery($sql, $params);
}
function db_fetch(string $sql, array $params = []): ?array {
    return DBConnect::fetchOne($sql, $params);
}
function db_fetch_all(string $sql, array $params = []): array {
    return DBConnect::fetchAll($sql, $params);
}
function db_insert(string $table, array $data): int {
    return DBConnect::insert($table, $data);
}
function db_update(string $table, array $data, string $where, array $whereParams = []): int {
    return DBConnect::update($table, $data, $where, $whereParams);
}
function db_delete(string $table, string $where, array $params = []): int {
    return DBConnect::delete($table, $where, $params);
}
function db_count(string $table, string $where = '1', array $params = []): int {
    return DBConnect::count($table, $where, $params);
}

// Initialize connection globals
try {
    DBConnect::getInstance();
    $conn = DBConnect::getConnection();
    $db   = DBConnect::getDatabase();
} catch (Exception $e) {
    // Error handled inside class
    die("Database initialization failed.");
}