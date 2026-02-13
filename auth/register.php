<?php
// auth/register.php - User Registration Page with Role Selection
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/main-functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    $role = $_SESSION['user_role'] ?? 'member';
    if ($role === 'admin' || $role === 'super_admin') {
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
    } elseif ($role === 'pastor') {
        header('Location: ' . SITE_URL . '/pastor/dashboard.php');
    } else {
        header('Location: ' . SITE_URL . '/member/dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    try {
        SecurityManager::verifyCSRFToken($_POST['csrf_token'] ?? '');
    } catch (Exception $e) {
        $error = 'Security token validation failed. Please try again.';
    }
    
    if (empty($error)) {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $marital_status = $_POST['marital_status'] ?? '';
        $user_type = $_POST['user_type'] ?? 'member'; // 'member' or 'pastor'
        $church_position = $_POST['church_position'] ?? '';
        $ministry_interest = $_POST['ministry_interest'] ?? '';
        
        // Validate input
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        } elseif ($churchDB->emailExists($email)) {
            $errors[] = 'Email already registered';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($phone) && !preg_match('/^[\+]?[1-9][0-9]{9,14}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number';
        }
        
        if ($user_type === 'pastor' && empty($church_position)) {
            $errors[] = 'Please specify your church position';
        }
        
        if (empty($errors)) {
            try {
                // Register user with selected role
                $role = $user_type === 'pastor' ? 'pastor' : 'member';
                $result = $auth->register($full_name, $email, $password, $phone, $address, $date_of_birth, $role);
                
                if (isset($result['success']) && $result['success']) {
                    $user_id = $result['user_id'];
                    
                    // Update profile with additional data
                    try {
                        $conn = Database::getInstance()->getConnection();
                        
                        // Check if profile exists
                        $stmt = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        
                        if ($stmt->rowCount() > 0) {
                            // Update existing profile
                            $updateSql = "UPDATE user_profiles SET gender = ?, marital_status = ?, church_position = ?, ministry_interest = ? WHERE user_id = ?";
                            $stmt = $conn->prepare($updateSql);
                            $stmt->execute([$gender, $marital_status, $church_position, $ministry_interest, $user_id]);
                        } else {
                            // Insert new profile
                            $insertSql = "INSERT INTO user_profiles (user_id, gender, marital_status, church_position, ministry_interest) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($insertSql);
                            $stmt->execute([$user_id, $gender, $marital_status, $church_position, $ministry_interest]);
                        }
                        
                    } catch (Exception $e) {
                        error_log("Profile update error: " . $e->getMessage());
                    }
                    
                    // If pastor, add to pastors table if it exists
                    if ($user_type === 'pastor' && !empty($church_position)) {
                        try {
                            $stmt = $conn->prepare("INSERT INTO pastors (user_id, position, status) VALUES (?, ?, 'pending')");
                            $stmt->execute([$user_id, $church_position]);
                        } catch (Exception $e) {
                            error_log("Pastor registration error: " . $e->getMessage());
                        }
                    }
                    
                    // Log the registration
                    SecurityManager::logSecurityEvent('REGISTRATION_SUCCESS', $user_id, "Registered as " . $user_type);
                    
                    // Set success message and redirect to login
                    $_SESSION['registration_success'] = true;
                    $_SESSION['registration_email'] = $email;
                    $_SESSION['registration_type'] = $user_type;
                    
                    // Send welcome email (in background)
                    if (function_exists('sendEmailTemplate')) {
                        try {
                            $template = $user_type === 'pastor' ? 'welcome_pastor' : 'welcome_member';
                            sendEmailTemplate($template, $email, [
                                'full_name' => $full_name,
                                'church_name' => SITE_NAME,
                                'user_type' => ucfirst($user_type),
                                'login_url' => SITE_URL . '/auth/login.php'
                            ]);
                        } catch (Exception $e) {
                            error_log("Welcome email error: " . $e->getMessage());
                        }
                    }
                    
                    // Redirect to success page
                    header('Location: register-success.php');
                    exit();
                } else {
                    $error = $result['error'] ?? 'Registration failed. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'An error occurred during registration. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        } else {
            $error = implode('<br>', $errors);
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
    <title>Join Our Family - <?php echo SITE_NAME; ?></title>
    
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
            --accent: #9b59b6;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --gray: #95a5a6;
            --gray-light: #ecf0f1;
            --pastor-color: #8e44ad;
            --member-color: #3498db;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark);
        }
        
        .register-container {
            width: 100%;
            max-width: 1300px;
            margin: 0 auto;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            display: flex;
            min-height: 750px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-sidebar {
            flex: 1;
            background: linear-gradient(145deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        
        .register-sidebar::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.1;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .logo img {
            max-width: 60px;
            max-height: 60px;
        }
        
        .church-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: white;
        }
        
        .church-tagline {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        
        .welcome-text {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .welcome-subtext {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .role-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .role-info h4 {
            margin-bottom: 15px;
            color: var(--secondary-light);
        }
        
        .role-info ul {
            list-style: none;
            padding-left: 0;
        }
        
        .role-info li {
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: flex-start;
        }
        
        .role-info li i {
            margin-right: 10px;
            color: var(--secondary-light);
            margin-top: 3px;
        }
        
        .features-list {
            list-style: none;
            margin-bottom: 30px;
        }
        
        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .features-list li i {
            margin-right: 12px;
            color: var(--secondary-light);
            font-size: 1.2rem;
        }
        
        .testimonial {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: auto;
            border-left: 4px solid var(--secondary);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .register-content {
            flex: 1.5;
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: 750px;
        }
        
        .register-header {
            margin-bottom: 40px;
        }
        
        .register-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        
        .register-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .form-container {
            flex: 1;
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
        
        .form-label .required {
            color: var(--danger);
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
        
        .form-control, .form-select {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: var(--dark);
        }
        
        .form-select {
            padding-left: 50px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.15);
            outline: none;
        }
        
        .form-control.error, .form-select.error {
            border-color: var(--danger);
        }
        
        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 5px;
        }
        
        /* Role Selection Styling */
        .role-selection {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .role-option {
            flex: 1;
            cursor: pointer;
            border: 3px solid var(--gray-light);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            background: white;
        }
        
        .role-option:hover {
            transform: translateY(-5px);
            border-color: var(--primary-light);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.1);
        }
        
        .role-option.selected {
            border-color: var(--primary);
            background: linear-gradient(to bottom right, rgba(26, 82, 118, 0.05), rgba(52, 152, 219, 0.05));
        }
        
        .role-option.selected.member {
            border-color: var(--member-color);
        }
        
        .role-option.selected.pastor {
            border-color: var(--pastor-color);
        }
        
        .role-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: white;
        }
        
        .role-icon.member {
            background: linear-gradient(135deg, var(--member-color) 0%, #2980b9 100%);
        }
        
        .role-icon.pastor {
            background: linear-gradient(135deg, var(--pastor-color) 0%, #9b59b6 100%);
        }
        
        .role-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .role-description {
            font-size: 0.9rem;
            color: var(--gray);
            line-height: 1.5;
        }
        
        .role-input {
            position: absolute;
            opacity: 0;
        }
        
        .role-specific-fields {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        .role-specific-fields.active {
            display: block;
        }
        
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-meter {
            height: 6px;
            background: var(--gray-light);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .strength-text {
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .strength-weak {
            background: var(--danger);
            width: 25%;
        }
        
        .strength-fair {
            background: var(--warning);
            width: 50%;
        }
        
        .strength-good {
            background: var(--secondary);
            width: 75%;
        }
        
        .strength-strong {
            background: var(--success);
            width: 100%;
        }
        
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            margin: 30px 0;
        }
        
        .terms-checkbox input {
            margin-top: 5px;
            margin-right: 12px;
        }
        
        .terms-label {
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .terms-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .terms-label a:hover {
            text-decoration: underline;
        }
        
        .btn-register {
            background: linear-gradient(to right, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 18px 40px;
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
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(26, 82, 118, 0.3);
        }
        
        .btn-register:active {
            transform: translateY(-1px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: none;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
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
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .register-card {
                flex-direction: column;
                max-width: 600px;
            }
            
            .register-sidebar {
                padding: 40px 30px;
            }
            
            .register-content {
                padding: 40px 30px;
            }
            
            .welcome-text {
                font-size: 1.8rem;
            }
            
            .role-selection {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .register-content {
                padding: 30px 20px;
            }
            
            .register-title {
                font-size: 1.8rem;
            }
            
            .form-control, .form-select {
                padding: 14px 15px 14px 45px;
            }
            
            .role-option {
                padding: 20px;
            }
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
        
        /* Floating animation for logo */
        .logo {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* Custom checkbox */
        .custom-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .checkmark {
            position: relative;
            height: 22px;
            width: 22px;
            background-color: white;
            border: 2px solid var(--gray-light);
            border-radius: 6px;
            margin-right: 12px;
            transition: all 0.3s ease;
        }
        
        .custom-checkbox input:checked ~ .checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <!-- Sidebar with Welcome Message -->
            <div class="register-sidebar">
                <div>
                    <div class="logo-container">
                        <div class="logo">
                            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo">
                        </div>
                        <h1 class="church-name"><?php echo SITE_NAME; ?></h1>
                        <p class="church-tagline">Building Families, Strengthening Faith</p>
                    </div>
                    
                    <h2 class="welcome-text">Join Our Family</h2>
                    <p class="welcome-subtext">Whether you're a member or pastor, we welcome you to our community of faith and love.</p>
                    
                    <div class="role-info">
                        <h4><i class="fas fa-users me-2"></i>Choose Your Role</h4>
                        <ul>
                            <li><i class="fas fa-user-check"></i> <strong>Member:</strong> Join our congregation, participate in activities, and grow spiritually</li>
                            <li><i class="fas fa-hands-praying"></i> <strong>Pastor:</strong> Shepherd God's flock, lead ministries, and provide spiritual guidance</li>
                        </ul>
                    </div>
                    
                    <ul class="features-list">
                        <li><i class="fas fa-check-circle"></i> Access to spiritual resources</li>
                        <li><i class="fas fa-check-circle"></i> Event registration and management</li>
                        <li><i class="fas fa-check-circle"></i> Prayer request submission</li>
                        <li><i class="fas fa-check-circle"></i> Ministry involvement opportunities</li>
                        <li><i class="fas fa-check-circle"></i> Community support network</li>
                    </ul>
                </div>
                
                <div class="testimonial">
                    <p class="testimonial-text">"Joining as a pastor here gave me the platform to serve and lead with purpose. The support system is amazing!"</p>
                    <p class="testimonial-author">- Pastor John, Ministry Leader</p>
                </div>
            </div>
            
            <!-- Registration Form -->
            <div class="register-content">
                <div class="register-header">
                    <h2 class="register-title">Create Your Account</h2>
                    <p class="register-subtitle">Select your role and fill in your details</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" action="" id="registerForm">
                        <!-- Role Selection -->
                        <div class="role-selection">
                            <label class="role-option member <?php echo (($_POST['user_type'] ?? 'member') === 'member') ? 'selected' : ''; ?>">
                                <input type="radio" name="user_type" value="member" class="role-input" 
                                       <?php echo (($_POST['user_type'] ?? 'member') === 'member') ? 'checked' : ''; ?>>
                                <div class="role-icon member">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3 class="role-title">Church Member</h3>
                                <p class="role-description">Join as a regular member to participate in church activities, events, and grow in faith with our community.</p>
                            </label>
                            
                            <label class="role-option pastor <?php echo (($_POST['user_type'] ?? '') === 'pastor') ? 'selected' : ''; ?>">
                                <input type="radio" name="user_type" value="pastor" class="role-input"
                                       <?php echo (($_POST['user_type'] ?? '') === 'pastor') ? 'checked' : ''; ?>>
                                <div class="role-icon pastor">
                                    <i class="fas fa-hands-praying"></i>
                                </div>
                                <h3 class="role-title">Pastor/Ministry Leader</h3>
                                <p class="role-description">Register as a pastor or ministry leader to access leadership tools, manage ministries, and shepherd the flock.</p>
                            </label>
                        </div>
                        
                        <!-- Pastor-specific fields -->
                        <div class="role-specific-fields <?php echo (($_POST['user_type'] ?? '') === 'pastor') ? 'active' : ''; ?>" id="pastorFields">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Church Position <span class="required">*</span></label>
                                        <div class="input-with-icon">
                                            <i class="input-icon fas fa-church"></i>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="church_position" 
                                                   name="church_position" 
                                                   value="<?php echo htmlspecialchars($_POST['church_position'] ?? ''); ?>"
                                                   placeholder="e.g., Senior Pastor, Youth Pastor, Worship Leader">
                                        </div>
                                        <div id="positionError" class="error-message"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Information -->
                        <h4 class="mt-4 mb-3" style="color: var(--primary-dark);">Personal Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-user"></i>
                                        <input type="text" 
                                               class="form-control" 
                                               id="full_name" 
                                               name="full_name" 
                                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                               placeholder="Enter your full name"
                                               required>
                                    </div>
                                    <div id="fullNameError" class="error-message"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-envelope"></i>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                               placeholder="Enter your email address"
                                               required>
                                    </div>
                                    <div id="emailError" class="error-message"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Password <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-lock"></i>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Create a strong password"
                                               required>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-meter">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <div class="strength-text" id="strengthText">Password strength</div>
                                    </div>
                                    <div id="passwordError" class="error-message"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-lock"></i>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               placeholder="Confirm your password"
                                               required>
                                    </div>
                                    <div id="confirmPasswordError" class="error-message"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <h4 class="mt-4 mb-3" style="color: var(--primary-dark);">Contact Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-phone"></i>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                               placeholder="+268 7600 0000">
                                    </div>
                                    <div id="phoneError" class="error-message"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date of Birth</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-birthday-cake"></i>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_of_birth" 
                                               name="date_of_birth" 
                                               value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-venus-mars"></i>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo ($_POST['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($_POST['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="other" <?php echo ($_POST['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Marital Status</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-heart"></i>
                                        <select class="form-select" id="marital_status" name="marital_status">
                                            <option value="">Select Status</option>
                                            <option value="single" <?php echo ($_POST['marital_status'] ?? '') == 'single' ? 'selected' : ''; ?>>Single</option>
                                            <option value="married" <?php echo ($_POST['marital_status'] ?? '') == 'married' ? 'selected' : ''; ?>>Married</option>
                                            <option value="divorced" <?php echo ($_POST['marital_status'] ?? '') == 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                            <option value="widowed" <?php echo ($_POST['marital_status'] ?? '') == 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address & Ministry Interest -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-home"></i>
                                        <textarea class="form-control" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="2"
                                                  placeholder="Enter your full address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Ministry Interest</label>
                                    <div class="input-with-icon">
                                        <i class="input-icon fas fa-hands-helping"></i>
                                        <select class="form-select" id="ministry_interest" name="ministry_interest">
                                            <option value="">Select Ministry Interest</option>
                                            <option value="worship" <?php echo ($_POST['ministry_interest'] ?? '') == 'worship' ? 'selected' : ''; ?>>Worship & Music</option>
                                            <option value="youth" <?php echo ($_POST['ministry_interest'] ?? '') == 'youth' ? 'selected' : ''; ?>>Youth Ministry</option>
                                            <option value="children" <?php echo ($_POST['ministry_interest'] ?? '') == 'children' ? 'selected' : ''; ?>>Children's Ministry</option>
                                            <option value="outreach" <?php echo ($_POST['ministry_interest'] ?? '') == 'outreach' ? 'selected' : ''; ?>>Outreach & Evangelism</option>
                                            <option value="prayer" <?php echo ($_POST['ministry_interest'] ?? '') == 'prayer' ? 'selected' : ''; ?>>Prayer Ministry</option>
                                            <option value="media" <?php echo ($_POST['ministry_interest'] ?? '') == 'media' ? 'selected' : ''; ?>>Media & Technology</option>
                                            <option value="hospitality" <?php echo ($_POST['ministry_interest'] ?? '') == 'hospitality' ? 'selected' : ''; ?>>Hospitality</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="terms-checkbox">
                            <label class="custom-checkbox">
                                <input type="checkbox" id="terms" name="terms" required>
                                <span class="checkmark"></span>
                                <span class="terms-label">
                                    I agree to the <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank">Terms of Service</a> 
                                    and <a href="<?php echo SITE_URL; ?>/privacy.php" target="_blank">Privacy Policy</a> 
                                    of <?php echo SITE_NAME; ?>
                                </span>
                            </label>
                        </div>
                        <div id="termsError" class="error-message"></div>
                        
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn-register" id="submitBtn">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Account</span>
                        </button>
                    </form>
                    
                    <div class="login-link">
                        Already have an account? 
                        <a href="login.php">Sign in here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = submitBtn.querySelector('span');
            const submitBtnIcon = submitBtn.querySelector('i');
            
            // Role selection
            const roleOptions = document.querySelectorAll('.role-option');
            const pastorFields = document.getElementById('pastorFields');
            const churchPositionInput = document.getElementById('church_position');
            
            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const input = this.querySelector('.role-input');
                    input.checked = true;
                    
                    // Update UI
                    roleOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Show/hide pastor fields
                    if (input.value === 'pastor') {
                        pastorFields.classList.add('active');
                        churchPositionInput.required = true;
                    } else {
                        pastorFields.classList.remove('active');
                        churchPositionInput.required = false;
                    }
                });
            });
            
            // Password strength checker
            const passwordInput = document.getElementById('password');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                
                // Character type checks
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                // Update strength meter
                let strengthClass = '';
                let text = '';
                
                if (password.length === 0) {
                    text = 'Password strength';
                    strengthFill.style.width = '0%';
                } else if (strength <= 2) {
                    text = 'Weak password';
                    strengthFill.className = 'strength-fill strength-weak';
                    strengthFill.style.width = '25%';
                } else if (strength <= 4) {
                    text = 'Fair password';
                    strengthFill.className = 'strength-fill strength-fair';
                    strengthFill.style.width = '50%';
                } else if (strength <= 5) {
                    text = 'Good password';
                    strengthFill.className = 'strength-fill strength-good';
                    strengthFill.style.width = '75%';
                } else {
                    text = 'Strong password';
                    strengthFill.className = 'strength-fill strength-strong';
                    strengthFill.style.width = '100%';
                }
                
                strengthText.textContent = text;
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    clearError(this);
                });
            });
            
            // Password match validation
            const confirmPasswordInput = document.getElementById('confirm_password');
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    showError(this, 'Passwords do not match');
                } else {
                    clearError(this);
                }
            });
            
            // Email validation
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                const email = this.value;
                if (email && !validateEmail(email)) {
                    showError(this, 'Please enter a valid email address');
                }
            });
            
            // Phone validation
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('blur', function() {
                const phone = this.value;
                if (phone && !validatePhone(phone)) {
                    showError(this, 'Please enter a valid phone number (e.g., +268 7600 0000)');
                }
            });
            
            // Church position validation for pastors
            churchPositionInput.addEventListener('blur', function() {
                const userType = document.querySelector('input[name="user_type"]:checked').value;
                if (userType === 'pastor' && !this.value.trim()) {
                    showError(this, 'Church position is required for pastors');
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtnText.textContent = 'Creating Account...';
                    submitBtnIcon.className = 'fas fa-spinner fa-spin';
                    
                    // Submit the form
                    this.submit();
                }
            });
            
            // Validation functions
            function validateField(field) {
                const value = field.value.trim();
                const fieldId = field.id;
                const fieldName = field.name;
                
                switch(fieldId) {
                    case 'full_name':
                        if (!value) {
                            showError(field, 'Full name is required');
                            return false;
                        }
                        break;
                        
                    case 'email':
                        if (!value) {
                            showError(field, 'Email address is required');
                            return false;
                        }
                        if (!validateEmail(value)) {
                            showError(field, 'Please enter a valid email address');
                            return false;
                        }
                        break;
                        
                    case 'password':
                        if (!value) {
                            showError(field, 'Password is required');
                            return false;
                        }
                        if (value.length < 8) {
                            showError(field, 'Password must be at least 8 characters');
                            return false;
                        }
                        if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/\d/.test(value)) {
                            showError(field, 'Password must include uppercase, lowercase, and number');
                            return false;
                        }
                        break;
                        
                    case 'confirm_password':
                        const password = passwordInput.value;
                        if (!value) {
                            showError(field, 'Please confirm your password');
                            return false;
                        }
                        if (value !== password) {
                            showError(field, 'Passwords do not match');
                            return false;
                        }
                        break;
                        
                    case 'church_position':
                        const userType = document.querySelector('input[name="user_type"]:checked').value;
                        if (userType === 'pastor' && !value) {
                            showError(field, 'Church position is required for pastors');
                            return false;
                        }
                        break;
                        
                    case 'phone':
                        if (value && !validatePhone(value)) {
                            showError(field, 'Please enter a valid phone number');
                            return false;
                        }
                        break;
                        
                    case 'terms':
                        if (!field.checked) {
                            showError(field, 'You must agree to the terms and conditions');
                            return false;
                        }
                        break;
                }
                
                return true;
            }
            
            function validateForm() {
                let isValid = true;
                
                // Check required fields
                const requiredFields = [
                    'full_name',
                    'email',
                    'password',
                    'confirm_password',
                    'terms'
                ];
                
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });
                
                // Check user type
                const userType = document.querySelector('input[name="user_type"]:checked');
                if (!userType) {
                    showError(document.querySelector('.role-selection'), 'Please select a role (Member or Pastor)');
                    isValid = false;
                } else if (userType.value === 'pastor') {
                    const positionField = document.getElementById('church_position');
                    if (!validateField(positionField)) {
                        isValid = false;
                    }
                }
                
                // Check optional fields if they have values
                const phoneField = document.getElementById('phone');
                if (phoneField.value && !validatePhone(phoneField.value)) {
                    isValid = false;
                }
                
                return isValid;
            }
            
            function showError(field, message) {
                field.classList.add('error');
                const errorDiv = document.getElementById(field.id + 'Error') || 
                                document.getElementById(field.name + 'Error');
                if (errorDiv) {
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                }
            }
            
            function clearError(field) {
                field.classList.remove('error');
                const errorDiv = document.getElementById(field.id + 'Error') || 
                                document.getElementById(field.name + 'Error');
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
            }
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            function validatePhone(phone) {
                // Accepts formats: +268 7600 0000, 76000000, 076000000
                const re = /^[\+]?[1-9][\d\s\-\.]{8,14}$/;
                return re.test(phone);
            }
            
            // Auto-hide error messages after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>