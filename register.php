<?php
/**
 * register.php – CFCI Create Account
 * Bootstrap only → no output before redirect.
 */
require_once 'includes/main-functions.php';   // DB, Auth, Session, CSRF – NO HTML OUTPUT

// If already logged in, redirect
if (is_logged_in()) {
    redirectToRoleDashboard();
    exit;
}

$error   = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $fullName        = trim($_POST['full_name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role            = $_POST['role'] ?? 'member';
    $termsAccepted   = isset($_POST['terms']);

    $errors = [];

    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if (!$termsAccepted) $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';

    // Password strength (matches Auth::register validation)
    if (strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, a number, and a special character.';
    }

    if (empty($errors)) {
        $result = $auth->register($fullName, $email, $password, null, null, null, $role);

        if ($result['success']) {
            // Auto‑login after registration and redirect
            $loginResult = $auth->login($email, $password, false);
            if (!isset($loginResult['error'])) {
                SessionManager::setFlash('success', 'Registration successful! Welcome to CFCI.');
                redirectToRoleDashboard();
                exit;
            }
        } else {
            $error = $result['message'] ?? 'Registration failed. Please try again.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// ========== Now render the page ==========
$current_page = 'register';
require_once 'includes/header.php';   // HTML output starts here
?>

<section class="page-header-sm bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-0">Create Your Account</h1>
                <p class="text-white-50 mb-0">Join our spiritual family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Register</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registrationForm">
                            <?= csrfField() ?>

                            <div class="mb-4">
                                <label for="full_name" class="form-label fw-bold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           placeholder="John Doe" required maxlength="100"
                                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="you@example.com" required
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">I am a</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-check-inline bg-light p-3 rounded-3 flex-fill border role-option-member">
                                        <input class="form-check-input" type="radio" name="role" id="roleMember" value="member"
                                               <?= (($_POST['role'] ?? 'member') === 'member') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="roleMember">
                                            <strong>Church Member</strong><br><small class="text-muted">Congregation participant</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline bg-light p-3 rounded-3 flex-fill border role-option-pastor">
                                        <input class="form-check-input" type="radio" name="role" id="rolePastor" value="pastor"
                                               <?= (($_POST['role'] ?? '') === 'pastor') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="rolePastor">
                                            <strong>Pastor/Minister</strong><br><small class="text-muted">Leadership role</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Create a strong password" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar" id="passwordStrengthMeter" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div id="passwordStrengthText" class="small mt-1 text-muted">Strength: Too weak</div>
                                <ul class="list-unstyled small mt-2">
                                    <li id="reqLength"><i class="fas fa-circle me-1 text-muted"></i>At least 8 characters</li>
                                    <li id="reqUppercase"><i class="fas fa-circle me-1 text-muted"></i>Contains uppercase letter</li>
                                    <li id="reqNumber"><i class="fas fa-circle me-1 text-muted"></i>Contains number</li>
                                    <li id="reqSpecial"><i class="fas fa-circle me-1 text-muted"></i>Contains special character</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           placeholder="Confirm your password" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatchMsg" class="small mt-1"></div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow" id="registerButton">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            Already have an account? <a href="<?= SITE_URL ?>login.php" class="fw-bold text-primary">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('passwordStrengthMeter');
    const strengthText = document.getElementById('passwordStrengthText');
    const reqs = {
        length: document.getElementById('reqLength'),
        uppercase: document.getElementById('reqUppercase'),
        number: document.getElementById('reqNumber'),
        special: document.getElementById('reqSpecial')
    };

    password.addEventListener('input', function() {
        const val = password.value;
        const checks = {
            length: val.length >= 8,
            uppercase: /[A-Z]/.test(val),
            number: /[0-9]/.test(val),
            special: /[^A-Za-z0-9]/.test(val)
        };
        let score = Object.values(checks).filter(Boolean).length;
        strengthBar.style.width = (score/4)*100 + '%';
        if (score <= 1) { strengthBar.className = 'progress-bar bg-danger'; strengthText.textContent = 'Strength: Weak'; }
        else if (score === 2) { strengthBar.className = 'progress-bar bg-warning'; strengthText.textContent = 'Strength: Fair'; }
        else if (score === 3) { strengthBar.className = 'progress-bar bg-info'; strengthText.textContent = 'Strength: Good'; }
        else { strengthBar.className = 'progress-bar bg-success'; strengthText.textContent = 'Strength: Strong'; }

        reqs.length.querySelector('i').className = checks.length ? 'fas fa-check-circle text-success' : 'fas fa-circle text-muted';
        reqs.uppercase.querySelector('i').className = checks.uppercase ? 'fas fa-check-circle text-success' : 'fas fa-circle text-muted';
        reqs.number.querySelector('i').className = checks.number ? 'fas fa-check-circle text-success' : 'fas fa-circle text-muted';
        reqs.special.querySelector('i').className = checks.special ? 'fas fa-check-circle text-success' : 'fas fa-circle text-muted';
    });

    confirm.addEventListener('input', function() {
        const msg = document.getElementById('passwordMatchMsg');
        if (confirm.value === password.value) {
            msg.innerHTML = '<i class="fas fa-check-circle text-success"></i> Passwords match';
        } else {
            msg.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Passwords do not match';
        }
    });

    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = password.type === 'password' ? 'text' : 'password';
        password.type = type;
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const type = confirm.type === 'password' ? 'text' : 'password';
        confirm.type = type;
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });

    // Loading state on submit
    document.getElementById('registrationForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerButton');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating Account...';
        btn.disabled = true;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>