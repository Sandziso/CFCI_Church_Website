<?php
/**
 * session.php – Session Manager (DB‑driven)
 * Uses the `sessions` table
 */

if (session_status() === PHP_SESSION_NONE) {
    // Set session name before starting
    if (defined('SESSION_NAME')) session_name(SESSION_NAME);

    // Register DB‑based session handler
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/config.php';

    class DatabaseSessionHandler implements SessionHandlerInterface
    {
        private $pdo;

        public function __construct()
        {
            $this->pdo = Database::getInstance()->getConnection();
        }

        public function open($savePath, $sessionName): bool
        {
            return true;
        }

        public function close(): bool
        {
            return true;
        }

        public function read($id): string|false
        {
            try {
                $stmt = $this->pdo->prepare("SELECT payload FROM sessions WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $session = $stmt->fetch();
                return $session ? $session['payload'] : '';
            } catch (Exception $e) {
                error_log("Session read error: " . $e->getMessage());
                return '';
            }
        }

        public function write($id, $data): bool
        {
            try {
                $userId = $_SESSION['user_id'] ?? null;
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $time = time();

                $stmt = $this->pdo->prepare("
                    INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                    VALUES (:id, :uid, :ip, :ua, :payload, :time)
                    ON DUPLICATE KEY UPDATE
                        user_id = VALUES(user_id),
                        ip_address = VALUES(ip_address),
                        user_agent = VALUES(user_agent),
                        payload = VALUES(payload),
                        last_activity = VALUES(last_activity)
                ");
                return $stmt->execute([
                    ':id'      => $id,
                    ':uid'     => $userId,
                    ':ip'      => $ip,
                    ':ua'      => $ua,
                    ':payload' => $data,
                    ':time'    => $time,
                ]);
            } catch (Exception $e) {
                error_log("Session write error: " . $e->getMessage());
                return false;
            }
        }

        public function destroy($id): bool
        {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } catch (Exception $e) {
                error_log("Session destroy error: " . $e->getMessage());
                return false;
            }
        }

        public function gc($max_lifetime): int|false
        {
            try {
                $old = time() - $max_lifetime;
                $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_activity < :old");
                $stmt->execute([':old' => $old]);
                return $stmt->rowCount();
            } catch (Exception $e) {
                error_log("Session GC error: " . $e->getMessage());
                return false;
            }
        }
    }

    // Register the handler
    $handler = new DatabaseSessionHandler();
    session_set_save_handler($handler, true);

    // Start the session
    session_start();
}

/**
 * SessionManager – static helpers for flash messages and session data
 */
class SessionManager
{
    public static function setFlash($type, $message)
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash()
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
    }

    public static function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function hasRole($role): bool
    {
        $userRole = self::getUserRole();
        if ($role === 'admin' && $userRole === 'super_admin') return true;
        return $userRole === $role;
    }

    public static function destroy()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}