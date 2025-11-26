<?php
// api/dashboard-updates.php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

// Check if user is logged in
$session->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $db = new ChurchDB($conn);
        $user_id = $session->getUserId();
        
        // Get unread notification count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'unread_notifications' => $result['unread_count'] ?? 0,
            'timestamp' => time()
        ]);
        
    } catch (Exception $e) {
        error_log("Dashboard updates API error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}