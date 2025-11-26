<?php
// includes/db_connect.php

require_once '../includes/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+02:00'");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    if (DEBUG_MODE) {
        die("Database connection error: " . $e->getMessage());
    } else {
        die("System temporarily unavailable. Please try again later.");
    }
}
?>