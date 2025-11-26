<?php
// auth/login.php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

Security::initialize($conn);
$csrf_token = Security::generateCSRFToken();

$page_title = "Login to CFCI Church";
require_once '../includes/header.php';
?>

<!-- Use the same CSS styles from the enhanced registration page -->
<style>
    .auth-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .auth-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .auth-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,192C672,181,768,139,864,138.7C960,139,1056,181,1152,192C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
        background-size: cover;
        background-position: bottom;
        opacity: 0.2;
    }
    
    .auth-body {
        padding: 2.5rem;
    }
    
    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e3e6f0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
    
    .input-group-text {
        border-radius: 10px 0 0 10px;
        background: #f8f9fc;
        border: 2px solid #e3e6f0;
        border-right: none;
    }
    
    .input-group .form-control {
        border-left: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(78, 115, 223, 0.3);
    }
    
    .auth-footer {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fc;
        border-top: 1px solid #e3e6f0;
    }
    
    .login-features {
        display: flex;
        justify-content: space-around;
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e3e6f0;
    }
    
    .feature-item {
        flex: 1;
        padding: 0 15px;
    }
    
    .feature-icon {
        font-size: 2rem;
        color: #4e73df;
        margin-bottom: 10px;
    }
    
    @media (max-width: 768px) {
        .auth-body {
            padding: 1.5rem;
        }
        
        .auth-header {
            padding: 2rem 1.5rem;
        }
        
        .login-features {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="auth-card animate__animated animate__fadeInUp">
                <div class="auth-header">
                    <h1 class="display-5 fw-bold"><i class="fas fa-church me-2"></i>Welcome Back</h1>
                    <p class="mb-0">Sign in to your CFCI Church account</p>
                </div>
                
                <div class="auth-body">
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form id="loginForm" action="login-process.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required 
                                       placeholder="Enter your password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-primary">Forgot password?</a>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php" class="text-primary fw-semibold">Create one here</a>
                        </p>
                    </div>
                    
                    <div class="login-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6>Community</h6>
                            <small class="text-muted">Join our spiritual family</small>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-pray"></i>
                            </div>
                            <h6>Prayer</h6>
                            <small class="text-muted">Share prayer requests</small>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h6>Events</h6>
                            <small class="text-muted">Stay connected</small>
                        </div>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> CFCI Church. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
        // Form validation
        $('#loginForm').on('submit', function(e) {
            const email = $('#email').val().trim();
            const password = $('#password').val();
            
            if (email === '') {
                e.preventDefault();
                showAlert('Please enter your email address.', 'warning');
                return;
            }
            
            if (password === '') {
                e.preventDefault();
                showAlert('Please enter your password.', 'warning');
                return;
            }
            
            // Show loading state
            $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Signing In...');
        });
        
        // Show alert function
        function showAlert(message, type = 'info') {
            // Remove any existing alerts
            $('.alert-dismissible').remove();
            
            // Create new alert
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.auth-body').prepend(alertHtml);
        }
        
        // Add animation to form elements
        $('.form-control').on('focus', function() {
            $(this).parent().addClass('animate__pulse');
        });
        
        $('.form-control').on('blur', function() {
            $(this).parent().removeClass('animate__pulse');
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>