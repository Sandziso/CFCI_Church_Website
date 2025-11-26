<?php
// auth/register-process.php

// Start session and include required files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Get form data
$full_name = Security::sanitizeInput($_POST['full_name'] ?? '');
$email = Security::sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$phone = Security::sanitizeInput($_POST['phone'] ?? '');
$address = Security::sanitizeInput($_POST['address'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$role = Security::sanitizeInput($_POST['role'] ?? 'member');
$csrf_token = $_POST['csrf_token'] ?? '';
$terms = $_POST['terms'] ?? '';

// CSRF protection
try {
    Security::verifyCSRFToken($csrf_token);
} catch (Exception $e) {
    header('Location: register.php?error=' . urlencode('Security token validation failed. Please try again.'));
    exit;
}

// Server-side validation
if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($terms)) {
    header('Location: register.php?error=' . urlencode('All required fields must be filled, and you must agree to the terms.'));
    exit;
}

if (!Security::validateEmail($email)) {
    header('Location: register.php?error=' . urlencode('Invalid email format.'));
    exit;
}

if (!Security::validatePassword($password)) {
    header('Location: register.php?error=' . urlencode('Password must be at least 6 characters long.'));
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=' . urlencode('Passwords do not match.'));
    exit;
}

// Validate role (only allow member or pastor)
if (!in_array($role, ['member', 'pastor'])) {
    $role = 'member'; // Default to member if invalid role provided
}

// For pastor registrations, set is_active to 0 initially for verification
$is_active = ($role === 'pastor') ? 0 : 1;

// Attempt registration with role parameter
$register_result = $auth->register($full_name, $email, $password, $phone, $address, $date_of_birth, $role, $is_active);

if (isset($register_result['error'])) {
    // Log the detailed error for debugging
    error_log("Registration failed: " . $register_result['error']);
    
    // Redirect with error message
    header('Location: register.php?error=' . urlencode($register_result['error']));
    exit;
}

// Registration successful
Security::logSecurityEvent('REGISTRATION_COMPLETE', $register_result['user_id']);

// Different success messages based on role
if ($role === 'pastor') {
    $success_message = 'Registration submitted! Pastor accounts require verification. You will be contacted by church administration.';
} else {
    $success_message = 'Registration successful! Please log in to your new account.';
}

header('Location: login.php?message=' . urlencode($success_message));
exit;
?>