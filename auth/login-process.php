<?php
// auth/login-process.php

// Start session and include required files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$email = Security::sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate required fields
if (empty($email) || empty($password)) {
    header('Location: login.php?error=' . urlencode('Please fill in all fields'));
    exit;
}

// Validate email format
if (!Security::validateEmail($email)) {
    header('Location: login.php?error=' . urlencode('Please enter a valid email address'));
    exit;
}

// Attempt login
$result = $auth->login($email, $password, $remember);

if (isset($result['error'])) {
    // Login failed
    header('Location: login.php?error=' . urlencode($result['error']));
    exit;
} else {
    // Login successful - redirect based on role
    $role = $result['role'];
    $user_id = $result['id'];
    
    // Log successful login
    Security::logSecurityEvent('LOGIN_SUCCESS', $user_id);
    
    // Check if user is admin (from admins table)
    $churchDB = new ChurchDB($conn);
    $admin_info = $churchDB->isUserAdmin($user_id);
    
    if ($admin_info) {
        // User is an admin - redirect to admin dashboard
        header('Location: ../admin/dashboard.php');
        exit;
    }
    
    // Redirect based on user role from users table
    switch ($role) {
        case 'pastor':
            header('Location: ../pastor/dashboard.php');
            break;
        case 'member':
        case 'guest':
        default:
            header('Location: ../member/dashboard.php');
            break;
    }
    exit;
}
?>