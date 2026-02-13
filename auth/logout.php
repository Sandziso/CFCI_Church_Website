<?php
// auth/logout.php - User Logout Page
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/main-functions.php';

// Initialize variables
$success = '';
$error = '';

// Debug mode for troubleshooting
$debug_mode = false; // Set to true for debugging

// Check if user is logged in - use existing function from auth.php
$isLoggedIn = is_logged_in();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Handle logout action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    if ($debug_mode) {
        error_log("=== LOGOUT DEBUG ===");
        error_log("POST Data: " . print_r($_POST, true));
        error_log("Session ID: " . session_id());
        error_log("User ID: " . $userId);
        error_log("CSRF Token in POST: " . ($_POST['csrf_token'] ?? 'MISSING'));
        error_log("CSRF Token in Session: " . ($_SESSION['csrf_token'] ?? 'MISSING'));
    }
    
    try {
        // Check if user is actually logged in
        if (!$isLoggedIn) {
            throw new Exception('No active session found.');
        }
        
        // Verify CSRF token - use SecurityManager function
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            throw new Exception('Security token missing.');
        }
        
        // Use SecurityManager function for CSRF verification
        require_once '../includes/SecurityManager.php';
        SecurityManager::verifyCSRFToken($_POST['csrf_token']);
        
        // Log the logout activity before destroying session
        if ($userId) {
            // Log security event using SecurityManager
            SecurityManager::logSecurityEvent('LOGOUT', $userId, 'User logged out from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
        
        // Get user info for display before clearing session
        $userEmail = $_SESSION['user_email'] ?? '';
        $userRole = $_SESSION['user_role'] ?? '';
        
        // Use the global auth object to logout
        global $auth;
        $auth->logout();
        
        // Set success message
        $success = 'You have been successfully logged out.';
        
        if ($debug_mode) {
            error_log("Logout successful for user: " . $userEmail);
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Logout Error: " . $e->getMessage());
        
        // Generate a new CSRF token on error to prevent reuse attacks
        require_once '../includes/SecurityManager.php';
        SecurityManager::generateCSRFToken();
    }
}

// Generate CSRF token for the form if not already set
if (empty($_SESSION['csrf_token'])) {
    require_once '../includes/SecurityManager.php';
    SecurityManager::generateCSRFToken();
}

// If logout was successful and we're in debug mode, show immediate redirect
if ($success && $debug_mode) {
    header('Location: login.php?msg=logged_out');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - <?php echo defined('SITE_NAME') ? SITE_NAME : 'Church Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --secondary: #e67e22;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --text-light: #666;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .logout-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            border: none;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .logout-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logout-icon {
            font-size: 4rem;
            color: white;
            margin-bottom: 20px;
        }
        
        .logout-body {
            padding: 40px 30px;
            text-align: center;
        }
        
        .user-info {
            background: var(--light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid var(--secondary);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }
        
        .progress-bar {
            height: 5px;
            background: var(--primary);
            border-radius: 3px;
            margin-top: 10px;
            animation: progress 3s linear forwards;
        }
        
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        @media (max-width: 768px) {
            .logout-card {
                margin: 20px;
            }
            
            .logout-header {
                padding: 30px 20px;
            }
            
            .logout-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="logout-card">
                    <!-- Header -->
                    <div class="logout-header">
                        <i class="fas fa-sign-out-alt logout-icon"></i>
                        <h2>Logout Confirmation</h2>
                        <p>You are about to sign out of your account</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="logout-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                                <div class="progress-bar"></div>
                            </div>
                            
                            <div class="countdown">
                                <i class="fas fa-clock me-2"></i>
                                Redirecting to login page in <span id="countdown-timer">3</span> seconds...
                            </div>
                            
                            <!-- Auto-redirect for non-JS users -->
                            <noscript>
                                <meta http-equiv="refresh" content="3;url=login.php">
                            </noscript>
                            
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                        <?php endif; ?>
                        
                        <?php if (!$success && $isLoggedIn): ?>
                            <!-- User Info -->
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php 
                                    // Use existing function from auth.php
                                    if (function_exists('getUserInitials')) {
                                        echo getUserInitials($userName);
                                    } else {
                                        // Fallback if function doesn't exist
                                        echo !empty($userName) ? strtoupper(substr($userName, 0, 2)) : 'U';
                                    }
                                    ?>
                                </div>
                                <h4><?php echo htmlspecialchars($userName ?: 'User'); ?></h4>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($userEmail ?: 'No email'); ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-user-tag me-1"></i>
                                    <?php echo htmlspecialchars(ucfirst($userRole ?: 'member')); ?>
                                </p>
                            </div>
                            
                            <!-- Security Note -->
                            <div class="alert alert-warning">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Security Notice:</strong> Logging out will end your session and require you to login again.
                            </div>
                            
                            <!-- Logout Form -->
                            <form method="POST" action="" id="logoutForm" autocomplete="off">
                                <input type="hidden" name="confirm_logout" value="1">
                                
                                <?php if (isset($_SESSION['csrf_token'])): ?>
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                    <button type="submit" class="btn btn-logout me-md-2">
                                        <i class="fas fa-sign-out-alt me-2"></i>Yes, Logout
                                    </button>
                                    <a href="../dashboard.php" class="btn btn-cancel">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                            
                            <?php if ($debug_mode): ?>
                            <div class="mt-3 p-2 bg-light rounded small">
                                <strong>Debug Info:</strong><br>
                                Session ID: <?php echo session_id(); ?><br>
                                User ID: <?php echo $userId; ?><br>
                                CSRF Token: <?php echo substr($_SESSION['csrf_token'] ?? 'Not set', 0, 20); ?>...
                            </div>
                            <?php endif; ?>
                            
                        <?php elseif (!$isLoggedIn && !$success): ?>
                            <!-- Not logged in message -->
                            <div class="alert alert-warning">
                                <i class="fas fa-user-slash me-2"></i>
                                <strong>No Active Session Found</strong>
                                <p class="mb-0 mt-2">You are not currently logged in.</p>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                <a href="login.php" class="btn btn-primary me-md-2">
                                    <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                                </a>
                                <a href="../index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle countdown for successful logout
        <?php if ($success): ?>
            let countdown = 3;
            const countdownElement = document.getElementById('countdown-timer');
            const countdownInterval = setInterval(() => {
                countdown--;
                if (countdownElement) {
                    countdownElement.textContent = countdown;
                }
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'login.php';
                }
            }, 1000);
        <?php endif; ?>
        
        // Handle logout form submission
        document.getElementById('logoutForm')?.addEventListener('submit', function(e) {
            const logoutButton = this.querySelector('button[type="submit"]');
            if (logoutButton) {
                logoutButton.disabled = true;
                logoutButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging out...';
            }
        });
        
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>