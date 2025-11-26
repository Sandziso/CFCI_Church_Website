<?php
// login.php

// Include global app configuration (for BASE_URL)
require_once 'config/app_config.php';
// Include database connection
require_once 'config/db_connect.php';
// Include authentication functions
require_once 'includes/auth.php';

// If user is already logged in, redirect to their dashboard
if (is_logged_in()) {
    if (is_pastor()) {
        header("Location: " . BASE_URL . "/pastor/dashboard.php");
    } else { // Assuming member
        header("Location: " . BASE_URL . "/member/dashboard.php");
    }
    exit();
}

$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_message("Please enter both email and password.", "danger");
    } else {
        if (attempt_login($email, $password, $pdo)) {
            // Login successful, redirect handled by attempt_login based on role
            // No need for further action here as header() and exit() are called
        } else {
            // Login failed, message already set by attempt_login
        }
    }
}

// Include header for consistent layout and message display
require_once 'includes/header.php';
?>
<style>
    /* Specific styles for the login page */
    body {
        font-family: 'Poppins', sans-serif;
        color: var(--cfci-dark);
        background: linear-gradient(135deg, rgba(26, 107, 158, 0.1) 0%, rgba(76, 175, 80, 0.1) 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center; /* Center content vertically */
        padding: 20px;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .login-container {
        background: var(--cfci-white);
        border-radius: 20px;
        box-shadow: var(--shadow);
        padding: 40px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .login-container h2 {
        font-family: 'Playfair Display', serif;
        color: var(--cfci-primary);
        font-size: 2.2rem;
        margin-bottom: 20px;
    }

    .login-container p {
        color: var(--cfci-gray);
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 25px;
        text-align: left;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--cfci-gray);
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cfci-primary);
    }

    .form-control {
        width: 100%;
        padding: 14px 20px 14px 45px;
        border: 1px solid var(--cfci-light-gray);
        border-radius: 50px;
        font-family: 'Poppins', sans-serif;
        font-size: 16px;
        transition: var(--transition);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--cfci-primary);
        box-shadow: 0 0 0 3px rgba(26, 107, 158, 0.2);
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--cfci-gray);
    }

    .btn-login {
        display: block;
        width: 100%;
        padding: 14px;
        background: var(--cfci-primary);
        color: var(--cfci-white);
        border: none;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(26, 107, 158, 0.3);
        margin-top: 20px;
    }

    .btn-login:hover {
        background: #135a87;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(26, 107, 158, 0.4);
    }

    .forgot-password, .register-link {
        margin-top: 15px;
        font-size: 0.95rem;
    }

    .forgot-password a, .register-link a {
        color: var(--cfci-primary);
        text-decoration: none;
        font-weight: 500;
    }

    .forgot-password a:hover, .register-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="login-container">
    <h2>Welcome Back!</h2>
    <p>Sign in to access your dashboard</p>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-with-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required autocomplete="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-with-icon">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="password" name="password" placeholder="Your password" required autocomplete="current-password">
                <span class="password-toggle" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="forgot-password">
        <a href="#">Forgot Password?</a>
    </div>

    <div class="register-link">
        Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register here</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Handle loading state on form submission
        const loginForm = document.querySelector('.login-container form');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                submitBtn.disabled = true;
            });
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>
