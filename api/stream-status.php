<?php
// api/stream-status.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get stream ID from query parameter
$stream_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($stream_id <= 0) {
    echo json_encode(['error' => 'Invalid stream ID']);
    exit;
}

try {
    // Get stream status
    $stmt = $conn->prepare("
        SELECT 
            ls.id,
            ls.title,
            ls.is_live,
            ls.total_viewers,
            ls.max_viewers,
            COUNT(DISTINCT sv.id) as current_viewers
        FROM live_streams ls
        LEFT JOIN stream_viewers sv ON ls.id = sv.stream_id AND sv.leave_time IS NULL
        WHERE ls.id = ?
        GROUP BY ls.id, ls.title, ls.is_live, ls.total_viewers, ls.max_viewers
    ");
    $stmt->execute([$stream_id]);
    $stream = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stream) {
        echo json_encode([
            'success' => true,
            'stream' => $stream
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Stream not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>