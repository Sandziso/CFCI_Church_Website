<?php
// api/search.php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

// Check if user is logged in
$session->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_term = $_GET['q'] ?? '';
    
    if (strlen($search_term) < 2) {
        echo json_encode(['success' => false, 'message' => 'Search term too short']);
        exit;
    }
    
    try {
        $db = new ChurchDB($conn);
        $user_id = $session->getUserId();
        $results = $db->searchDashboardContent($user_id, $search_term);
        
        echo json_encode([
            'success' => true,
            'events' => $results['events'] ?? [],
            'sermons' => $results['sermons'] ?? [],
            'ministries' => $results['ministries'] ?? []
        ]);
        
    } catch (Exception $e) {
        error_log("Search API error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Search failed']);
    }
}