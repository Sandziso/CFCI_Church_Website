<?php
// CFCI_Church_Website/auth_handler.php
// This file is the public endpoint for authentication actions.
// It is outside the protected 'includes' folder, allowing the form to submit to it.
// It then securely includes the actual logic file from the 'includes' folder.

// The auth.php file contains the Security/Auth classes and the POST request handler logic at the end.
// Requiring it here causes the handler logic to execute and process the form data.
require_once 'includes/auth.php';

// Note: If auth.php were ONLY classes, the required path would be different, 
// but since it has request handling logic at the end, this simple include works.
?>