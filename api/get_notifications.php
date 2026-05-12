<?php
/**
 * API endpoint for real‑time notifications & quick stats
 * Called by AJAX from the topbar and sidebar
 */
require_once '../includes/config.php';
require_once '../includes/main-functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    require_once '../includes/database.php';
    $db = Database::getInstance()->getConnection();

    if ($action === 'count_and_preview') {
        // Unread count + recent 5 notifications
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $unread = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT id, title, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$user_id]);
        $previews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format time ago
        foreach ($previews as &$notif) {
            $notif['time_ago'] = timeAgo($notif['created_at']);
            $notif['icon'] = match($notif['type']) {
                'event' => 'fa-calendar-alt',
                'prayer' => 'fa-pray',
                'sermon' => 'fa-podcast',
                'donation' => 'fa-donate',
                'ministry' => 'fa-hands-helping',
                default => 'fa-bell'
            };
        }

        echo json_encode([
            'status' => 'success',
            'unread_count' => $unread,
            'previews' => $previews
        ]);

    } elseif ($action === 'mark_all_read') {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['status' => 'success']);

    } elseif ($action === 'stats') {
        // Quick stats for sidebar
        $stmt = $db->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND is_active = 1 AND is_published = 1");
        $upcoming_events = $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM sermons WHERE is_published = 1");
        $sermons = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM prayer_requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $prayers = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(DISTINCT mm.ministry_id) FROM ministry_members mm WHERE mm.user_id = ? AND mm.is_active = 1");
        $stmt->execute([$user_id]);
        $ministries = $stmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'upcoming_events' => $upcoming_events,
            'sermons_available' => $sermons,
            'prayer_requests' => $prayers,
            'ministries_involved' => $ministries
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("API error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}

// Helper: time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60).'m ago';
    if ($diff < 86400) return floor($diff/3600).'h ago';
    return date('M d', $time);
}