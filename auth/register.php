<?php
// Start session and include config at the very top
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php'; // Include auth.php to access Security class

// Ensure the security class is initialized (assuming $conn is available from database.php)
Security::initialize($conn); 
$csrf_token = Security::generateCSRFToken(); // Generate the CSRF token

$page_title = "Join CFCI Church";
require_once '../includes/header.php';
?>

<!-- Custom CSS for enhanced registration -->
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
    
    .role-selection {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .role-option {
        flex: 1;
        text-align: center;
        padding: 20px 15px;
        border: 2px solid #e3e6f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .role-option:hover {
        border-color: #4e73df;
        transform: translateY(-3px);
    }
    
    .role-option.selected {
        border-color: #4e73df;
        background: rgba(78, 115, 223, 0.05);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.1);
    }
    
    .role-option.quick-register {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.05);
    }
    
    .role-option.quick-register:hover {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.1);
    }
    
    .role-option.quick-register.selected {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.1);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    }
    
    .quick-register-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
    }
    
    .role-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: #4e73df;
    }
    
    .quick-register .role-icon {
        color: #28a745;
    }
    
    .role-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: #4a4a4a;
    }
    
    .role-description {
        font-size: 0.85rem;
        color: #6c757d;
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
    
    .progress {
        height: 8px;
        border-radius: 10px;
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
    
    .password-strength-meter {
        margin-top: 10px;
    }
    
    .strength-label {
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .strength-very-weak { color: #dc3545; }
    .strength-weak { color: #fd7e14; }
    .strength-fair { color: #ffc107; }
    .strength-good { color: #20c997; }
    .strength-strong { color: #198754; }
    
    .password-requirements {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }
    
    .requirement {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 0.85rem;
    }
    
    .requirement i {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }
    
    .requirement.valid {
        color: #198754;
    }
    
    .requirement.invalid {
        color: #6c757d;
    }
    
    .auth-footer {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fc;
        border-top: 1px solid #e3e6f0;
    }
    
    @media (max-width: 768px) {
        .role-selection {
            flex-direction: column;
        }
        
        .auth-body {
            padding: 1.5rem;
        }
        
        .auth-header {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8 col-xl-7">
            <div class="auth-card animate__animated animate__fadeInUp">
                <div class="auth-header">
                    <h1 class="display-5 fw-bold"><i class="fas fa-church me-2"></i>Join CFCI Church</h1>
                    <p class="mb-0">Create your account and become part of our spiritual family</p>
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
                    
                    <!-- Quick Pastor Registration Form -->
                    <form id="quickPastorForm" action="register-process.php" method="POST" style="display: none;">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="role" value="pastor">
                        <input type="hidden" name="quick_register" value="1">
                    </form>
                    
                    <form id="registerForm" action="register-process.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="role" id="selected_role" value="member">
                        
                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">I want to register as:</label>
                            <div class="role-selection">
                                <div class="role-option selected" data-role="member">
                                    <div class="role-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="role-title">Church Member</div>
                                    <div class="role-description">Join our congregation and participate in church activities</div>
                                </div>
                                <div class="role-option quick-register" data-role="pastor" id="pastorQuickOption">
                                    <div class="quick-register-badge">Quick Register</div>
                                    <div class="role-icon">
                                        <i class="fas fa-pray"></i>
                                    </div>
                                    <div class="role-title">Pastor/Minister</div>
                                    <div class="role-description">Click to register instantly as a pastor</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required 
                                               placeholder="Enter your full name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               placeholder="Enter your email">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="Enter your phone number">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <textarea class="form-control" id="address" name="address" rows="2" 
                                          placeholder="Enter your address"></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required 
                                               placeholder="Create a password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="password-strength-meter">
                                        <div class="progress mb-2">
                                            <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <div class="strength-label">
                                            Password strength: <span id="passwordStrength" class="strength-very-weak">Very Weak</span>
                                        </div>
                                    </div>
                                    
                                    <div class="password-requirements">
                                        <div class="requirement invalid" id="req-length">
                                            <i class="fas fa-times-circle"></i>
                                            At least 8 characters
                                        </div>
                                        <div class="requirement invalid" id="req-uppercase">
                                            <i class="fas fa-times-circle"></i>
                                            Contains uppercase letter
                                        </div>
                                        <div class="requirement invalid" id="req-lowercase">
                                            <i class="fas fa-times-circle"></i>
                                            Contains lowercase letter
                                        </div>
                                        <div class="requirement invalid" id="req-number">
                                            <i class="fas fa-times-circle"></i>
                                            Contains number
                                        </div>
                                        <div class="requirement invalid" id="req-special">
                                            <i class="fas fa-times-circle"></i>
                                            Contains special character
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                                               placeholder="Confirm your password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" checked>
                            <label class="form-check-label" for="newsletter">
                                Subscribe to our newsletter for updates and events
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account as Member
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="login.php" class="text-primary fw-semibold">Sign in here</a>
                        </p>
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
        // Role selection
        $('.role-option').click(function() {
            $('.role-option').removeClass('selected');
            $(this).addClass('selected');
            const selectedRole = $(this).data('role');
            $('#selected_role').val(selectedRole);
            
            // Update button text
            if (selectedRole === 'pastor') {
                $('button[type="submit"]').html('<i class="fas fa-user-plus me-2"></i>Create Account as Pastor');
            } else {
                $('button[type="submit"]').html('<i class="fas fa-user-plus me-2"></i>Create Account as Member');
            }
        });
        
        // Quick pastor registration
        $('#pastorQuickOption').click(function() {
            if (confirm('Would you like to register instantly as a pastor? You can fill in additional details later in your profile.')) {
                // Show loading state
                $(this).html('<div class="role-icon"><i class="fas fa-spinner fa-spin"></i></div><div class="role-title">Registering...</div>');
                
                // Submit the quick pastor form
                $('#quickPastorForm').submit();
            }
        });
        
        // Password strength calculation function
        function checkPasswordStrength(password) {
            let strength = 0;
            let requirements = {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            };
            
            // Length requirement
            if (password.length >= 8) {
                strength++;
                requirements.length = true;
            }
            
            // Uppercase requirement
            if (password.match(/[A-Z]/)) {
                strength++;
                requirements.uppercase = true;
            }
            
            // Lowercase requirement
            if (password.match(/[a-z]/)) {
                strength++;
                requirements.lowercase = true;
            }
            
            // Number requirement
            if (password.match(/\d/)) {
                strength++;
                requirements.number = true;
            }
            
            // Special character requirement
            if (password.match(/[^a-zA-Z\d]/)) {
                strength++;
                requirements.special = true;
            }
            
            return { strength, requirements };
        }
        
        // Update password strength indicator
        function updatePasswordStrength(password) {
            const { strength, requirements } = checkPasswordStrength(password);
            const strengthBar = $('#passwordStrengthBar');
            const strengthText = $('#passwordStrength');
            
            let width = 0;
            let color = '';
            let text = '';
            let textClass = '';
            
            switch(strength) {
                case 0:
                case 1:
                    width = 20;
                    color = 'bg-danger';
                    text = 'Very Weak';
                    textClass = 'strength-very-weak';
                    break;
                case 2:
                    width = 40;
                    color = 'bg-warning';
                    text = 'Weak';
                    textClass = 'strength-weak';
                    break;
                case 3:
                    width = 60;
                    color = 'bg-info';
                    text = 'Fair';
                    textClass = 'strength-fair';
                    break;
                case 4:
                    width = 80;
                    color = 'bg-primary';
                    text = 'Good';
                    textClass = 'strength-good';
                    break;
                case 5:
                    width = 100;
                    color = 'bg-success';
                    text = 'Strong';
                    textClass = 'strength-strong';
                    break;
            }
            
            // Remove all color classes and add the current one
            strengthBar.removeClass('bg-danger bg-warning bg-info bg-primary bg-success').addClass(color);
            strengthBar.css('width', width + '%');
            strengthText.text(text).removeClass('strength-very-weak strength-weak strength-fair strength-good strength-strong').addClass(textClass);
            
            // Update requirement indicators
            Object.keys(requirements).forEach(key => {
                const requirementElement = $(`#req-${key}`);
                if (requirements[key]) {
                    requirementElement.removeClass('invalid').addClass('valid');
                    requirementElement.find('i').removeClass('fa-times-circle').addClass('fa-check-circle');
                } else {
                    requirementElement.removeClass('valid').addClass('invalid');
                    requirementElement.find('i').removeClass('fa-check-circle').addClass('fa-times-circle');
                }
            });
        }
        
        // Email validation function
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Password validation function
        function validatePassword(password) {
            return password.length >= 6;
        }
        
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
        
        // Show loading function
        function showLoading(show) {
            if (show) {
                $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
            } else {
                const role = $('#selected_role').val();
                const buttonText = role === 'pastor' ? 'Create Account as Pastor' : 'Create Account as Member';
                $('button[type="submit"]').prop('disabled', false).html(`<i class="fas fa-user-plus me-2"></i>${buttonText}`);
            }
        }
        
        // Toggle password visibility
        $('#togglePassword, #toggleConfirmPassword').click(function() {
            const targetId = $(this).attr('id') === 'togglePassword' ? '#password' : '#confirm_password';
            const passwordField = $(targetId);
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
        // Password strength indicator
        $('#password').on('input', function() {
            updatePasswordStrength($(this).val());
            checkPasswordMatch();
        });
        
        // Confirm password match
        $('#confirm_password').on('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const matchElement = $('#passwordMatch');
            
            if (confirmPassword === '') {
                matchElement.html('');
                return;
            }
            
            if (password === confirmPassword) {
                matchElement.html('<small class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</small>');
            } else {
                matchElement.html('<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords do not match</small>');
            }
        }
        
        // Form validation
        $('#registerForm').on('submit', function(e) {
            const fullName = $('#full_name').val().trim();
            const email = $('#email').val();
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const terms = $('#terms').is(':checked');
            const role = $('#selected_role').val();
            
            if (fullName === '') {
                showAlert('Please enter your full name.', 'warning');
                e.preventDefault();
                return;
            }
            
            if (!validateEmail(email)) {
                showAlert('Please enter a valid email address.', 'warning');
                e.preventDefault();
                return;
            }
            
            if (!validatePassword(password)) {
                showAlert('Password must be at least 6 characters long.', 'warning');
                e.preventDefault();
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match.', 'warning');
                e.preventDefault();
                return;
            }
            
            if (!terms) {
                showAlert('You must agree to the Terms of Service and Privacy Policy.', 'warning');
                e.preventDefault();
                return;
            }
            
            const { strength } = checkPasswordStrength(password);
            if (strength < 2) {
                if (!confirm('Your password is weak. Are you sure you want to continue?')) {
                    e.preventDefault();
                    return;
                }
            }
            
            if (role === 'pastor') {
                if (!confirm('Pastor accounts require verification. You will be contacted by church administration. Continue?')) {
                    e.preventDefault();
                    return;
                }
            }
            
            showLoading(true);
        });
        
        // Add animation to form elements
        $('.form-control').on('focus', function() {
            $(this).parent().addClass('animate__pulse');
        });
        
        $('.form-control').on('blur', function() {
            $(this).parent().removeClass('animate__pulse');
        });
        
        // Set maximum date for date of birth (18 years ago)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        $('#date_of_birth').attr('max', maxDate.toISOString().split('T')[0]);
    });
</script>

<?php require_once '../includes/footer.php'; ?>
