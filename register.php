<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Christian Family Centre International</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* Enhanced CFCI Church Website Styles */
        :root {
            --cfci-primary: #1a6b9e;   /* Deep Blue */
            --cfci-secondary: #4caf50; /* Vibrant Green */
            --cfci-tertiary: #f0ad4e;  /* Accent Orange */
            --cfci-light: #f8f9fa;
            --cfci-dark: #212529; 
            --cfci-gray: #6c757d;
            --cfci-light-gray: #e9ecef;
            --cfci-white: #ffffff;
            --cfci-gradient: linear-gradient(135deg, var(--cfci-primary) 0%, var(--cfci-secondary) 100%);
            --transition-speed: 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 24px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 12px 30px rgba(0, 0, 0, 0.2);
            --border-radius: 12px;
        }

        /* Modern Base Styles */
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--cfci-dark);
            line-height: 1.7;
            background-color: var(--cfci-white);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .register-container {
            display: flex;
            flex-wrap: wrap;
            min-height: 100vh;
            width: 100%;
        }

        .register-image {
            flex: 1;
            min-width: 350px;
            background: var(--cfci-gradient), 
                        linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2000&q=80') center/cover no-repeat;
            color: white;
            padding: 4rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .register-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><polygon points="0,100 100,0 100,100" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .church-logo {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .church-logo img {
            height: 70px;
            border-radius: 50%;
            margin-right: 15px;
            background: white;
            padding: 5px;
        }

        .church-logo-text {
            font-family: 'Merriweather', serif;
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }

        .church-logo-text span {
            color: var(--cfci-secondary);
        }

        .image-content {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        .image-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .image-content p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .features i {
            margin-right: 15px;
            color: var(--cfci-secondary);
            font-size: 1.4rem;
            width: 25px;
            text-align: center;
        }

        .register-form-section {
            flex: 1;
            min-width: 350px;
            padding: 4rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: var(--cfci-white);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 2.2rem;
            color: var(--cfci-primary);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--cfci-gray);
            font-size: 1.1rem;
        }

        .register-form {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 600;
            color: var(--cfci-dark);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--cfci-gray);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 20px 14px 50px;
            border: 1px solid var(--cfci-light-gray);
            border-radius: var(--border-radius);
            font-size: 1.05rem;
            transition: all var(--transition-speed);
            background-color: var(--cfci-light);
        }

        .form-control:focus {
            border-color: var(--cfci-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 107, 158, 0.2);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--cfci-gray);
            font-size: 1.1rem;
        }

        .role-selection {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .role-option {
            flex: 1;
            border: 2px solid var(--cfci-light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
        }

        .role-option i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--cfci-gray);
            transition: all var(--transition-speed);
        }

        .role-option .role-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--cfci-dark);
        }

        .role-option .role-desc {
            font-size: 0.9rem;
            color: var(--cfci-gray);
        }

        .role-option:hover {
            border-color: var(--cfci-primary);
        }

        .role-option:hover i {
            color: var(--cfci-primary);
        }

        .role-option.selected {
            border-color: var(--cfci-primary);
            background-color: rgba(26, 107, 158, 0.05);
        }

        .role-option.selected i {
            color: var(--cfci-primary);
        }

        .role-option.pastor.selected {
            background-color: rgba(76, 175, 80, 0.05);
            border-color: var(--cfci-secondary);
        }

        .role-option.pastor.selected i {
            color: var(--cfci-secondary);
        }

        .selected-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid var(--cfci-light-gray);
            transition: all var(--transition-speed);
        }

        .role-option.selected .selected-indicator {
            background-color: var(--cfci-primary);
            border-color: var(--cfci-primary);
        }

        .role-option.pastor.selected .selected-indicator {
            background-color: var(--cfci-secondary);
            border-color: var(--cfci-secondary);
        }

        .password-strength {
            margin-top: 10px;
        }

        .password-strength-meter {
            height: 8px;
            border-radius: 4px;
            background-color: var(--cfci-light-gray);
            overflow: hidden;
            margin-bottom: 5px;
        }

        .password-strength-meter div {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .password-strength-text {
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .requirements {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
            font-size: 0.9rem;
        }

        .requirements li {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            color: var(--cfci-gray);
        }

        .requirements i {
            margin-right: 8px;
            font-size: 0.8rem;
        }

        .requirements .valid {
            color: var(--cfci-secondary);
        }

        .requirements .invalid {
            color: var(--cfci-gray);
        }

        .terms {
            display: flex;
            align-items: flex-start;
            margin: 2rem 0;
        }

        .terms input {
            margin-top: 5px;
            margin-right: 10px;
        }

        .terms label {
            font-size: 0.95rem;
            color: var(--cfci-gray);
            line-height: 1.6;
        }

        .terms a {
            color: var(--cfci-primary);
            text-decoration: none;
        }

        .terms a:hover {
            text-decoration: underline;
        }

        .btn-register {
            background-color: var(--cfci-primary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: var(--shadow-light);
        }

        .btn-register:hover {
            background-color: #135a87;
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--cfci-light-gray);
        }

        .divider span {
            padding: 0 15px;
            color: var(--cfci-gray);
            font-size: 0.9rem;
        }

        .social-register {
            display: flex;
            gap: 15px;
            margin-bottom: 2rem;
        }

        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all var(--transition-speed);
            border: 1px solid var(--cfci-light-gray);
            background-color: var(--cfci-white);
        }

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-light);
        }

        .social-btn.google:hover {
            border-color: #DB4437;
            background-color: rgba(219, 68, 55, 0.05);
        }

        .social-btn.facebook:hover {
            border-color: #4267B2;
            background-color: rgba(66, 103, 178, 0.05);
        }

        .login-link {
            text-align: center;
            color: var(--cfci-gray);
            font-size: 1rem;
        }

        .login-link a {
            color: var(--cfci-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            display: none;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            display: none;
        }

        /* Dashboard preview styling */
        .dashboard-preview {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .dashboard-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        
        .dashboard-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .pastor-dashboard .dashboard-icon {
            color: var(--cfci-secondary);
        }
        
        .member-dashboard .dashboard-icon {
            color: var(--cfci-primary);
        }
        
        .dashboard-content h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .dashboard-content p {
            margin-bottom: 2rem;
            color: var(--cfci-gray);
        }
        
        .dashboard-btn {
            background: var(--cfci-primary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .dashboard-btn:hover {
            background: #135a87;
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .register-image {
                padding: 3rem 1.5rem;
            }
            
            .register-form-section {
                padding: 3rem 1.5rem;
            }
            
            .image-content h2 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }
            
            .register-image {
                min-height: 500px;
            }
            
            .role-selection {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .image-content h2 {
                font-size: 2rem;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
            
            .social-register {
                flex-direction: column;
            }
            
            .church-logo {
                justify-content: center;
            }
            
            .church-logo-text {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-image">
            <div class="church-logo">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNTAgMjUwIj48cGF0aCBmaWxsPSIjMmM1ZTkyIiBkPSJNMTI1IDMwTDMwIDEyNWw5NSA1TDEyNSAzMHoiLz48cGF0aCBmaWxsPSIjZTliOTQ5IiBkPSJNMTI1IDMwTDIyMCAxMjVxLTEwMCAxMDAgLTk1IDVsOTUgLTkweiIvPjxwYXRoIGZpbGw9IiMxYTNjNWUiIGQ9Ik0xMjUgMjIwbC05NSA5NSA5NSAtOTV6Ii8+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTE1MCAxMjVhMjUgMjUgMCAxIDEtNTAgMCAyNSAyNSAwIDEgMSA1MCAweiIvPjx0ZXh0IHg9IjEyNSIgeT0iMTUwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMxYTNjNWUiPkNGQ0k8L3RleHQ+PC9zdmc+" alt="CFCI Logo">
                <div class="church-logo-text">CF<span>CI</span></div>
            </div>
            
            <div class="image-content">
                <h2>Join Our Spiritual Family</h2>
                <p>Become a part of Christian Family Centre International and begin your journey of faith, growth, and fellowship.</p>

                <ul class="features">
                    <li><i class="fas fa-check"></i> Access exclusive member resources</li>
                    <li><i class="fas fa-check"></i> Join ministry groups and events</li>
                    <li><i class="fas fa-check"></i> Receive pastoral guidance</li>
                    <li><i class="fas fa-check"></i> Connect with fellow believers</li>
                    <li><i class="fas fa-check"></i> Grow in your spiritual journey</li>
                </ul>
            </div>
        </div>

        <div class="register-form-section">
            <div class="form-header">
                <h2>Create Your Account</h2>
                <p>Join our church community in just a few steps</p>
            </div>

            <div class="error-message" id="errorMessage">
                <!-- Error messages will appear here -->
            </div>
            
            <div class="success-message" id="successMessage">
                <!-- Success messages will appear here -->
            </div>

            <form class="register-form" id="registrationForm">
                <div class="form-group">
                    <label for="username">Full Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Select Your Role</label>
                    <div class="role-selection">
                        <div class="role-option member" id="memberOption">
                            <input type="radio" name="role" value="member" required>
                            <div class="selected-indicator"></div>
                            <i class="fas fa-users"></i>
                            <div class="role-title">Church Member</div>
                            <div class="role-desc">Congregation member and participant</div>
                        </div>

                        <div class="role-option pastor" id="pastorOption">
                            <input type="radio" name="role" value="pastor">
                            <div class="selected-indicator"></div>
                            <i class="fas fa-user-tie"></i>
                            <div class="role-title">Pastor/Minister</div>
                            <div class="role-desc">Church staff & leadership roles</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                        <span class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="password-strength">
                        <div class="password-strength-meter">
                            <div id="password-strength-meter" style="width: 0%; background-color: #e74c3c;"></div>
                        </div>
                        <div class="password-strength-text" id="password-strength-text">Password strength: <span>Weak</span></div>
                    </div>

                    <ul class="requirements">
                        <li id="length"><i class="fas fa-circle invalid"></i> At least 8 characters</li>
                        <li id="uppercase"><i class="fas fa-circle invalid"></i> Contains uppercase letter</li>
                        <li id="number"><i class="fas fa-circle invalid"></i> Contains number</li>
                        <li id="special"><i class="fas fa-circle invalid"></i> Contains special character</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <span class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="password-match" class="password-strength-text" style="color: #e74c3c;"></div>
                </div>

                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I understand that my information will be used in accordance with the church's data protection policy.</label>
                </div>

                <button type="submit" class="btn-register">Create Account</button>

                <div class="divider">
                    <span>Or sign up with</span>
                </div>

                <div class="social-register">
                    <div class="social-btn google">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </div>
                    <div class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        <span>Facebook</span>
                    </div>
                </div>

                <div class="login-link">
                    Already have an account? <a href="#">Login here</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Dashboard Preview -->
    <div class="dashboard-preview" id="dashboardPreview">
        <div class="dashboard-content" id="dashboardContent">
            <div class="dashboard-icon">
                <i class="fas fa-user"></i>
            </div>
            <h3 id="dashboardTitle">Welcome to Your Dashboard</h3>
            <p id="dashboardDescription">You're now part of the Christian Family Centre International community</p>
            <button class="dashboard-btn" id="continueBtn">Continue to Dashboard</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const confirmPassword = document.querySelector('#confirm_password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPassword.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            // Password strength checker
            password.addEventListener('input', function() {
                const value = password.value;
                const strengthMeter = document.getElementById('password-strength-meter');
                const strengthText = document.getElementById('password-strength-text').querySelector('span');
                const requirements = {
                    length: value.length >= 8,
                    uppercase: /[A-Z]/.test(value),
                    number: /[0-9]/.test(value),
                    special: /[^A-Za-z0-9]/.test(value)
                };

                // Update requirement indicators
                Object.keys(requirements).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        const icon = element.querySelector('i');
                        if (requirements[key]) {
                            icon.className = 'fas fa-check-circle valid';
                        } else {
                            icon.className = 'fas fa-circle invalid';
                        }
                    }
                });

                // Calculate strength score
                const strength = Object.values(requirements).reduce((sum, valid) => sum + valid, 0);
                const percentage = (strength / 4) * 100;

                // Update strength meter
                strengthMeter.style.width = `${percentage}%`;

                // Update strength text
                if (value.length === 0) {
                    strengthText.textContent = 'Weak';
                    strengthText.parentElement.style.color = '#e74c3c';
                    strengthMeter.style.backgroundColor = '#e74c3c';
                } else if (strength <= 1) {
                    strengthText.textContent = 'Weak';
                    strengthText.parentElement.style.color = '#e74c3c';
                    strengthMeter.style.backgroundColor = '#e74c3c';
                } else if (strength <= 2) {
                    strengthText.textContent = 'Medium';
                    strengthText.parentElement.style.color = '#f39c12';
                    strengthMeter.style.backgroundColor = '#f39c12';
                } else if (strength <= 3) {
                    strengthText.textContent = 'Strong';
                    strengthText.parentElement.style.color = '#27ae60';
                    strengthMeter.style.backgroundColor = '#27ae60';
                } else {
                    strengthText.textContent = 'Very Strong';
                    strengthText.parentElement.style.color = '#27ae60';
                    strengthMeter.style.backgroundColor = '#27ae60';
                }
            });

            // Password match checker
            confirmPassword.addEventListener('input', function() {
                const matchElement = document.getElementById('password-match');
                if (confirmPassword.value === password.value) {
                    matchElement.innerHTML = '<i class="fas fa-check-circle valid"></i> Passwords match';
                    matchElement.style.color = '#27ae60';
                } else {
                    matchElement.innerHTML = '<i class="fas fa-times-circle invalid"></i> Passwords do not match';
                    matchElement.style.color = '#e74c3c';
                }
            });

            // Role selection functionality
            const memberOption = document.getElementById('memberOption');
            const pastorOption = document.getElementById('pastorOption');

            memberOption.addEventListener('click', function() {
                if (!this.classList.contains('selected')) {
                    pastorOption.classList.remove('selected');
                    this.classList.add('selected');
                    document.querySelector('input[name="role"][value="member"]').checked = true;
                }
            });

            pastorOption.addEventListener('click', function() {
                if (!this.classList.contains('selected')) {
                    memberOption.classList.remove('selected');
                    this.classList.add('selected');
                    document.querySelector('input[name="role"][value="pastor"]').checked = true;
                }
            });

            // Form validation and submission
            const registerForm = document.getElementById('registrationForm');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const dashboardPreview = document.getElementById('dashboardPreview');
            const dashboardContent = document.getElementById('dashboardContent');
            const dashboardTitle = document.getElementById('dashboardTitle');
            const dashboardDescription = document.getElementById('dashboardDescription');
            const continueBtn = document.getElementById('continueBtn');
            
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Reset messages
                errorMessage.style.display = 'none';
                successMessage.style.display = 'none';
                errorMessage.innerHTML = '';
                
                let errors = [];
                
                // Check if role is selected
                if (!document.querySelector('input[name="role"]:checked')) {
                    errors.push('Please select your role.');
                }
                
                // Check if terms are accepted
                if (!document.getElementById('terms').checked) {
                    errors.push('You must agree to the terms and conditions.');
                }
                
                // Check password match
                if (password.value !== confirmPassword.value) {
                    errors.push('Passwords do not match.');
                }
                
                // Check password strength
                const strengthText = document.getElementById('password-strength-text').querySelector('span');
                if (strengthText.textContent === 'Weak' && password.value.length > 0) {
                    errors.push('Password is too weak. Please create a stronger password.');
                }
                
                // Display errors if any
                if (errors.length > 0) {
                    errorMessage.innerHTML = errors.join('<br>');
                    errorMessage.style.display = 'block';
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                submitBtn.disabled = true;
                
                // Simulate registration process (in a real app, this would be an AJAX request)
                setTimeout(() => {
                    // Determine user role
                    const role = document.querySelector('input[name="role"]:checked').value;
                    
                    // Show success message
                    successMessage.innerHTML = 'Account created successfully! Redirecting to your dashboard...';
                    successMessage.style.display = 'block';
                    
                    // Reset button after delay
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        
                        // Show dashboard preview based on role
                        showDashboardPreview(role);
                        
                    }, 2000);
                }, 2000);
            });
            
            // Show dashboard preview based on role
            function showDashboardPreview(role) {
                if (role === 'pastor') {
                    dashboardContent.className = 'dashboard-content pastor-dashboard';
                    dashboardTitle.innerHTML = 'Pastor Dashboard';
                    dashboardDescription.innerHTML = 'Welcome Pastor! You now have access to ministry resources, member management, and sermon planning tools.';
                    dashboardContent.querySelector('.dashboard-icon').innerHTML = '<i class="fas fa-user-tie"></i>';
                } else {
                    dashboardContent.className = 'dashboard-content member-dashboard';
                    dashboardTitle.innerHTML = 'Member Dashboard';
                    dashboardDescription.innerHTML = 'Welcome to our church community! Explore events, connect with groups, and access spiritual resources.';
                    dashboardContent.querySelector('.dashboard-icon').innerHTML = '<i class="fas fa-users"></i>';
                }
                
                dashboardPreview.style.display = 'flex';
            }
            
            // Continue to dashboard button
            continueBtn.addEventListener('click', function() {
                // In a real application, this would redirect to the actual dashboard
                const role = document.querySelector('input[name="role"]:checked').value;
                
                if (role === 'pastor') {
                    // Redirect to pastor dashboard
                    window.location.href = "pastor/dashboard.php";
                } else {
                    // Redirect to member dashboard
                    window.location.href = "member/dashboard.php";
                }
            });
        });
    </script>
</body>
</html>