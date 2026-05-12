<?php
/**
 * database.php – Database Singleton with auto‑creation and migration support
 */

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $port;
    public $conn;
    private static $instance = null;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = 'utf8mb4';
        $this->port = defined('DB_PORT') ? DB_PORT : 3306;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null || !$this->ping()) {
            try {
                // Ensure database exists
                if (!$this->databaseExists()) {
                    $this->createDatabase();
                }

                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset};port={$this->port}";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_TIMEOUT            => 30,
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("SET time_zone = '+02:00'");
            } catch (PDOException $e) {
                $this->logError("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    private function ping(): bool
    {
        if ($this->conn === null) return false;
        try {
            $this->conn->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            if (in_array($e->errorInfo[1], [2006, 2013])) { // MySQL server gone / lost connection
                $this->conn = null;
                return false;
            }
            throw $e;
        }
    }

    private function databaseExists(): bool
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};charset={$this->charset}";
            $temp = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $temp->query("SHOW DATABASES LIKE '{$this->db_name}'");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function createDatabase(): void
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};charset={$this->charset}";
            $temp = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $temp->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            $this->logError("Database creation failed: " . $e->getMessage());
            throw new Exception("Unable to create database");
        }
    }

    public function testConnection(): array
    {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT DATABASE() as db, USER() as user");
            $result = $stmt->fetch();
            return [
                'success'  => true,
                'database' => $result['db'],
                'user'     => $result['user'],
                'host'     => $this->host,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run a migration SQL file (idempotent)
     */
    public function runMigrationFile($filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'Migration file not found'];
            }
            $sql = file_get_contents($filePath);
            $conn = $this->getConnection();
            $conn->exec($sql);
            return ['success' => true, 'message' => 'Migration executed'];
        } catch (Exception $e) {
            $this->logError("Migration error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function logError(string $message): void
    {
        if (defined('LOG_PATH')) {
            $logFile = LOG_PATH . 'database_errors.log';
            $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
}