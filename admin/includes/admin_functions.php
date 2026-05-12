<?php
// admin/includes/admin_functions.php
// Centralised helper functions for the admin panel

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/main-functions.php';
requireAdminAccess();

// Use the global connection if available, otherwise fall back
global $conn;
if (!$conn) {
    require_once __DIR__ . '/../../includes/db_connect.php';
    $conn = DBConnect::getConnection();
}

// Simple static cache to avoid repeated queries in the same request
static $cache = [];

function cached_query(string $key, callable $queryFn) {
    global $cache;
    if (!isset($cache[$key])) {
        $cache[$key] = $queryFn();
    }
    return $cache[$key];
}

function getTotalUsersCount() {
    return cached_query('total_users', function() {
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getTotalUsersCount: " . $e->getMessage());
            return 0;
        }
    });
}

function getUpcomingEventsCount() {
    return cached_query('upcoming_events', function() {
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getUpcomingEventsCount: " . $e->getMessage());
            return 0;
        }
    });
}

function getMonthlyDonationsTotal() {
    return cached_query('monthly_donations', function() {
        global $conn;
        try {
            $stmt = $conn->prepare(
                "SELECT SUM(amount) FROM donations 
                 WHERE MONTH(donation_date) = MONTH(CURDATE()) 
                   AND YEAR(donation_date) = YEAR(CURDATE()) 
                   AND status = 'completed'"
            );
            $stmt->execute();
            $total = $stmt->fetchColumn();
            return number_format($total ?: 0, 0);
        } catch (PDOException $e) {
            error_log("getMonthlyDonationsTotal: " . $e->getMessage());
            return '0';
        }
    });
}

function getPendingPrayersCount() {
    return cached_query('pending_prayers', function() {
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM prayer_requests WHERE status = 'pending'");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getPendingPrayersCount: " . $e->getMessage());
            return 0;
        }
    });
}

function getUnreadMessagesCount() {
    return cached_query('unread_messages', function() {
        global $conn;
        try {
            // Check for both possible column names
            $columns = $conn->query("SHOW COLUMNS FROM contact_messages")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('is_read', $columns)) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
            } elseif (in_array('status', $columns)) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
            } else {
                return 0;
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getUnreadMessagesCount: " . $e->getMessage());
            return 0;
        }
    });
}

// Additional dashboard‑specific functions
function getMonthlyNewMembers() {
    return cached_query('new_members_month', function() {
        global $conn;
        try {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) FROM users 
                 WHERE is_active = 1 
                   AND MONTH(created_at) = MONTH(CURDATE()) 
                   AND YEAR(created_at) = YEAR(CURDATE())"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getMonthlyNewMembers: " . $e->getMessage());
            return 0;
        }
    });
}

function getDonationChartData() {
    // Returns JSON‑ready array of last 6 months' donations
    global $conn;
    try {
        $stmt = $conn->prepare(
            "SELECT DATE_FORMAT(donation_date, '%Y-%m') AS month, SUM(amount) AS total 
             FROM donations 
             WHERE status = 'completed' 
               AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month 
             ORDER BY month ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
}

function getEventAttendanceStats() {
    // Sum of registrations per event for upcoming events
    global $conn;
    try {
        $stmt = $conn->prepare(
            "SELECT e.title, COUNT(er.id) AS attendees 
             FROM events e
             LEFT JOIN event_registrations er ON e.id = er.event_id
             WHERE e.event_date >= CURDATE()
             GROUP BY e.id
             ORDER BY e.event_date ASC
             LIMIT 10"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
}