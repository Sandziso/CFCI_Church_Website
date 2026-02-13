<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pastor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = new ChurchDB($conn);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-stats':
        $stats = $db->getDashboardStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
        
    case 'update-settings':
        $settings = $_POST;
        $result = $db->updateSettings($settings);
        echo json_encode(['success' => $result, 'message' => $result ? 'Settings updated' : 'Update failed']);
        break;
        
    case 'export-data':
        $table = $_GET['table'] ?? '';
        $format = $_GET['format'] ?? 'csv';
        $data = $db->exportData($table, $format);
        
        if ($data) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $table . '_export.csv"');
            echo $data;
        } else {
            echo json_encode(['success' => false, 'message' => 'Export failed']);
        }
        break;
        
    case 'backup-db':
        $file = $db->backupDatabase();
        if ($file) {
            echo json_encode(['success' => true, 'file' => $file]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Backup failed']);
        }
        break;
        
    case 'delete-user':
        $id = $_GET['id'] ?? 0;
        if ($id && $id != $_SESSION['user_id']) {
            $result = $db->deleteUser($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'User deleted' : 'Delete failed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid user']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>