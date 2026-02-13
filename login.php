<?php
// login.php - Fixed version with modern design

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once 'config/app_config.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'pastor') {
        header("Location: " . BASE_URL . "admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "member/dashboard.php");
    }
    exit();
}

// Include database and functions
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

$email = '';
$password = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Initialize ChurchDB
        $db = new ChurchDB($conn);
        
        $result = $db->loginUser($email, $password);
        
        if (isset($result['success']) && $result['success']) {
            // Login successful
            if (isset($result['redirect'])) {
                header("Location: " . BASE_URL . $result['redirect']);
                exit();
            } else {
                // Default redirect
                if ($_SESSION['role'] === 'pastor') {
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "member/dashboard.php");
                }
                exit();
            }
        } else {
            $error = isset($result['error']) ? $result['error'] : "Login failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --primary-light: #2c82c9;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --gradient-start: #1a5276;
            --gradient-end: #3498db;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #1a5276, #3498db, #2c3e50, #1a5276);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            opacity: 0.3;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            animation-delay: -5s;
            background: rgba(230, 126, 34, 0.1);
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: -10s;
        }
        
        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 70%;
            animation-delay: -15s;
            background: rgba(52, 152, 219, 0.1);
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: moveBackground 20s linear infinite;
            z-index: 0;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 30px); }
        }
        
        .login-header-content {
            position: relative;
            z-index: 1;
        }
        
        .church-logo {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: white;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .login-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0 0 10px 0;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 300;
        }
        
        .login-body {
            padding: 40px 35px 30px;
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
            transition: color 0.3s ease;
        }
        
        .input-group {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.15);
            transform: translateY(-2px);
        }
        
        .input-group-prepend {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            width: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .form-control {
            border: none;
            padding: 16px 20px;
            font-size: 1rem;
            background-color: #fff;
            transition: all 0.3s ease;
            height: auto;
        }
        
        .form-control:focus {
            box-shadow: none;
            background-color: #f8f9fa;
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            font-weight: 300;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s ease;
            color: white;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(26, 82, 118, 0.3);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(26, 82, 118, 0.4);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .btn-login i {
            margin-right: 10px;
        }
        
        .login-footer {
            text-align: center;
            padding: 25px 35px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            color: #6c757d;
            font-size: 0.9rem;
            background-color: #f8f9fa;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 2px 0;
        }
        
        .login-footer a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary);
            transition: width 0.3s ease;
        }
        
        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: none;
        }
        
        .login-footer a:hover::after {
            width: 100%;
        }
        
        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            animation: slideInRight 0.5s ease-out;
            margin-bottom: 25px;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        
        .features-list {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .feature-item {
            text-align: center;
            color: var(--primary);
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: var(--primary);
            transition: transform 0.3s ease;
        }
        
        .feature-item:hover .feature-icon {
            transform: scale(1.2);
        }
        
        .feature-text {
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Responsive Design */
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 30px 20px 25px;
            }
            
            .login-body {
                padding: 30px 25px 25px;
            }
            
            .login-footer {
                padding: 20px 25px;
            }
            
            .church-logo {
                font-size: 2.8rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .features-list {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        /* Loading animation */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }
        
        @keyframes button-loading-spinner {
            from { transform: rotate(0turn); }
            to { transform: rotate(1turn); }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-content">
                    <div class="church-logo">
                        <i class="fas fa-church"></i>
                    </div>
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>Welcome Back! Please login to continue</p>
                </div>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   placeholder="Enter your email"
                                   required
                                   autocomplete="email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="loginButton">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-pray"></i>
                        </div>
                        <div class="feature-text">Prayer Requests</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="feature-text">Events</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div class="feature-text">Ministries</div>
                    </div>
                </div>
            </div>
            
            <div class="login-footer">
                <p class="mb-3">
                    <a href="forgot-password.php">
                        <i class="fas fa-key me-1"></i> Forgot Password?
                    </a>
                </p>
                <p class="mb-0">
                    Don't have an account? 
                    <a href="register.php">
                        <i class="fas fa-user-plus me-1"></i> Register here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }
            
            // Form submission with loading state
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            
            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', function() {
                    // Show loading state
                    loginButton.classList.add('btn-loading');
                    loginButton.disabled = true;
                    
                    // Re-enable button after 5 seconds (in case of error)
                    setTimeout(function() {
                        loginButton.classList.remove('btn-loading');
                        loginButton.disabled = false;
                    }, 5000);
                });
            }
            
            // Input focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
            
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.feature-item').forEach(item => {
                observer.observe(item);
            });
        });
    </script>
</body>
</html>