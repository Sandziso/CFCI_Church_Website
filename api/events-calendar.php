<?php
// api/events-calendar.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

try {
    $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $stmt = $conn->prepare("
        SELECT e.id, e.title, e.event_date, e.start_time, e.location, 
               ec.color as category_color, ec.name as category_name
        FROM events e
        LEFT JOIN event_categories ec ON e.category_id = ec.id
        WHERE e.event_date BETWEEN ? AND ?
          AND e.is_active = 1 
          AND e.is_published = 1
        ORDER BY e.event_date, e.start_time
    ");
    
    $stmt->execute([$start_date, $end_date]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($events);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>