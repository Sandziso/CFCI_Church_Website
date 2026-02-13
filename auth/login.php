<?php
// auth/login.php - User Login Page

// 1. Load Configuration and Core Systems
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/main-functions.php';

// 2. Initialize Auth System
try {
    $auth = new Auth();
} catch (Exception $e) {
    die("<div class='alert alert-danger'>System Error: " . $e->getMessage() . "</div>"); 
}

// 3. Helper: Redirect based on role
function redirectToDashboard($role) {
    $role = strtolower($role ?? 'member');
    switch ($role) {
        case 'super_admin':
        case 'admin':
            $url = SITE_URL . '/admin/dashboard.php';
            break;
        case 'pastor':
            $url = SITE_URL . '/pastor/dashboard.php';
            break;
        case 'staff':
            $url = SITE_URL . '/staff/dashboard.php';
            break;
        case 'elder':
            $url = SITE_URL . '/elder/dashboard.php';
            break;
        default:
            $url = SITE_URL . '/member/dashboard.php';
    }
    header("Location: " . $url);
    exit();
}

// 4. Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    redirectToDashboard($_SESSION['user_role']);
}

// Initialize variables
$error = '';
$success = '';
$email = '';
$password = '';
$result = null;

// 5. Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    try {
        SecurityManager::verifyCSRFToken($_POST['csrf_token'] ?? '');
    } catch (Exception $e) {
        $error = 'Security token validation failed. Please try again.';
    }
    
    if (empty($error)) {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password';
        } else {
            // USE THE AUTH CLASS! 
            $result = $auth->login($email, $password, $remember);

            if (isset($result['error'])) {
                $error = $result['error'];
            } else {
                // Login successful - Auth class has already set $_SESSION variables
                SecurityManager::logSecurityEvent('LOGIN_SUCCESS', $result['id'], "User logged in via email: $email");
                redirectToDashboard($result['role']);
            }
        }
    }
}

// Generate CSRF token
$csrf_token = SecurityManager::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --primary-light: #3498db;
            --secondary: #e67e22;
            --secondary-light: #f39c12;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --gray-light: #ecf0f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><path fill="white" d="M50,5 L60,35 L90,35 L65,55 L75,85 L50,65 L25,85 L35,55 L10,35 L40,35 Z"/></svg>');
            background-size: 80px;
            animation: floatBackground 60s linear infinite;
        }
        
        @keyframes floatBackground {
            0% { background-position: 0 0; }
            100% { background-position: 80px 80px; }
        }
        
        .login-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            min-height: 650px;
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .login-hero {
            flex: 1;
            background: linear-gradient(145deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.1;
            animation: pulse 20s linear infinite;
        }
        
        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .church-logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-circle {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .logo-circle img {
            max-width: 70px;
            max-height: 70px;
        }
        
        .church-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: white;
        }
        
        .church-tagline {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .welcome-message {
            margin: 40px 0;
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .features-list {
            list-style: none;
            margin-top: 40px;
        }
        
        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1rem;
        }
        
        .features-list li i {
            margin-right: 15px;
            color: var(--secondary-light);
            font-size: 1.3rem;
        }
        
        .login-form-section {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            margin-bottom: 40px;
        }
        
        .form-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        
        .form-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.1rem;
            z-index: 2;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: var(--dark);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.15);
            outline: none;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0 30px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .remember-checkbox {
            display: none;
        }
        
        .custom-checkbox {
            width: 22px;
            height: 22px;
            border: 2px solid var(--gray-light);
            border-radius: 6px;
            margin-right: 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .remember-checkbox:checked + .custom-checkbox {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .remember-checkbox:checked + .custom-checkbox::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        .remember-label {
            font-size: 0.95rem;
            color: var(--dark);
        }
        
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px rgba(26, 82, 118, 0.2);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(26, 82, 118, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--gray);
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .divider-text {
            padding: 0 15px;
            font-size: 0.9rem;
        }
        
        .register-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .register-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .role-hint {
            background: rgba(52, 152, 219, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--primary-dark);
            border-left: 3px solid var(--primary);
        }
        
        .role-hint i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        /* Loading animation */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .login-card {
                flex-direction: column;
                max-width: 600px;
            }
            
            .login-hero {
                padding: 40px 30px;
            }
            
            .login-form-section {
                padding: 40px 30px;
            }
            
            .welcome-title {
                font-size: 2rem;
            }
            
            .form-title {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-form-section {
                padding: 30px 20px;
            }
            
            .form-title {
                font-size: 1.8rem;
            }
            
            .form-control {
                padding: 14px 15px 14px 45px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
        
        /* Error animation */
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Hero Section -->
            <div class="login-hero">
                <div class="hero-content">
                    <div class="church-logo">
                        <div class="logo-circle">
                            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo">
                        </div>
                        <h1 class="church-name"><?php echo SITE_NAME; ?></h1>
                        <p class="church-tagline">Where Faith Meets Family</p>
                    </div>
                    
                    <div class="welcome-message">
                        <h2 class="welcome-title">Welcome Back</h2>
                        <p class="welcome-text">Sign in to access your account and continue your spiritual journey with our community. We're glad to have you back!</p>
                    </div>
                    
                    <ul class="features-list">
                        <li><i class="fas fa-check-circle"></i> Access your personal dashboard</li>
                        <li><i class="fas fa-check-circle"></i> Manage your profile and preferences</li>
                        <li><i class="fas fa-check-circle"></i> Register for upcoming events</li>
                        <li><i class="fas fa-check-circle"></i> Submit and track prayer requests</li>
                        <li><i class="fas fa-check-circle"></i> Connect with ministry leaders</li>
                    </ul>
                </div>
            </div>
            
            <!-- Login Form Section -->
            <div class="login-form-section">
                <div class="form-header">
                    <h2 class="form-title">Sign In</h2>
                    <p class="form-subtitle">Enter your credentials to access your account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php 
                // Check for any URL messages
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars(urldecode($_GET['error'])) . '
                    </div>';
                }
                if (isset($_GET['message'])) {
                    echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars(urldecode($_GET['message'])) . '
                    </div>';
                }
                if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
                    echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Registration successful! Please sign in with your credentials.
                    </div>';
                }
                ?>
                
                <form method="POST" action="" id="loginForm">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   placeholder="Enter your email address"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Enter your password"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" 
                                   name="remember" 
                                   id="remember" 
                                   class="remember-checkbox">
                            <span class="custom-checkbox"></span>
                            <span class="remember-label">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">
                            <i class="fas fa-key me-1"></i>Forgot password?
                        </a>
                    </div>
                    
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-login" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span class="divider-text">New to our church?</span>
                </div>
                
                <div class="register-link">
                    Don't have an account? 
                    <a href="register.php">Create an account</a>
                </div>
                
                <div class="role-hint">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> If you're a pastor or ministry leader, make sure to select the appropriate role during registration.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = submitBtn.querySelector('span');
            const submitBtnIcon = submitBtn.querySelector('i');
            const emailInput = form.querySelector('input[name="email"]');
            const passwordInput = form.querySelector('input[name="password"]');
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                if (!emailInput.value.trim() || !passwordInput.value.trim()) {
                    showError('Please fill in all fields');
                    return;
                }
                
                // Email validation
                const email = emailInput.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showError('Please enter a valid email address');
                    emailInput.classList.add('shake');
                    setTimeout(() => emailInput.classList.remove('shake'), 500);
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtnText.textContent = 'Signing In...';
                submitBtnIcon.className = 'fas fa-spinner fa-spin';
                
                // Submit the form
                this.submit();
            });
            
            function showError(message) {
                // Remove any existing error alerts
                const existingAlerts = document.querySelectorAll('.alert-danger');
                existingAlerts.forEach(alert => alert.remove());
                
                // Create new error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
                
                // Insert after form header
                const formHeader = document.querySelector('.form-header');
                formHeader.parentNode.insertBefore(alertDiv, formHeader.nextSibling);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.classList.add('fade');
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.parentNode.removeChild(alertDiv);
                            }
                        }, 300);
                    }
                }, 5000);
            }
            
            // Auto-hide success messages after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert-success').forEach(alert => {
                    alert.classList.add('fade');
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                });
            }, 5000);
            
            // Auto-hide error messages after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert-danger').forEach(alert => {
                    alert.classList.add('fade');
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                });
            }, 5000);
            
            // Add animation to inputs on focus
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
            
            // Enter key to submit
            form.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !submitBtn.disabled) {
                    submitBtn.click();
                }
            });
            
            // Demo credentials hint (remove in production)
            console.log('%c✨ ' + SITE_NAME + ' Login ✨', 'color: #1a5276; font-size: 14px; font-weight: bold;');
            console.log('%cDemo Credentials:', 'color: #e67e22; font-weight: bold;');
            console.log('%cAdmin: admin@cfci.org.sz / admin123', 'color: #3498db;');
            console.log('%cMember: member@example.com / password123', 'color: #2ecc71;');
        });
    </script>
</body>
</html>