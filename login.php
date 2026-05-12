<?php
/**
 * login.php – CFCI Member Login
 * Bootstrap only → avoid headers‑already‑sent.
 */
require_once 'includes/main-functions.php';   // DB, Auth, Session, CSRF – NO HTML OUTPUT

$email   = '';
$error   = '';
$success = '';

// If already logged in, we **do not** redirect immediately.
// Instead we will show a "you are logged in" message later,
// so the user can choose to log out and switch accounts.
$logged_in = is_logged_in();

// Process POST only if NOT already logged in (to avoid confusion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$logged_in) {
    validateCsrf();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email and password.';
    } else {
        $result = $auth->login($email, $password, $remember);

        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            // Successful login – set flash and redirect
            SessionManager::setFlash('success', 'Welcome back, ' . $result['full_name'] . '!');
            redirectToRoleDashboard();
            exit;
        }
    }
}

// ========== Only now start outputting HTML ==========
$current_page = 'login';
require_once 'includes/header.php';   // HTML output starts here
?>

<section class="page-header-sm bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-0">Welcome Back</h1>
                <p class="text-white-50 mb-0">Login to your CFCI account</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Login</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <!-- If already logged in, show identity + logout button -->
                <?php if ($logged_in): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-user-check fa-3x mb-3 d-block"></i>
                        <h5>You are already logged in as</h5>
                        <h4 class="fw-bold"><?= htmlspecialchars(getUserName()) ?></h4>
                        <p class="mb-3">To switch accounts, please log out first.</p>
                        <a href="<?= SITE_URL ?>auth/login.php?logout=1" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Log Out
                        </a>
                        <a href="javascript:location.reload()" class="btn btn-outline-secondary ms-2" onclick="return confirm('This will log you out. Continue?') ? (location.href='<?= SITE_URL ?>auth/login.php?logout=1') : false;">
                            Switch User
                        </a>
                        <hr>
                        <a href="<?= SITE_URL ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Go to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Normal login form -->
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-md-5">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="loginForm">
                                <?= csrfField() ?>

                                <div class="mb-4">
                                    <label for="email" class="form-label fw-bold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?= htmlspecialchars($email) ?>"
                                               placeholder="Enter your email" required autocomplete="email">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between">
                                        <label for="password" class="form-label fw-bold">Password</label>
                                        <a href="<?= SITE_URL ?>auth/forgot-password.php" class="small text-primary">Forgot password?</a>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                                        <input type="password" class="form-control" id="password" name="password"
                                               placeholder="Enter your password" required autocomplete="current-password">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow" id="loginButton">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <p class="mb-0">Don't have an account?
                                    <a href="<?= SITE_URL ?>register.php" class="fw-bold text-primary">Register here</a>
                                </p>
                            </div>

                            <hr class="my-4">

                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="fas fa-pray fa-lg text-primary mb-2"></i>
                                    <div class="small">Prayer</div>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-calendar-alt fa-lg text-primary mb-2"></i>
                                    <div class="small">Events</div>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-hands-helping fa-lg text-primary mb-2"></i>
                                    <div class="small">Ministries</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const pw = document.getElementById('password');
            const type = pw.type === 'password' ? 'text' : 'password';
            pw.type = type;
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }

    // Loading state (only if form exists)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const btn = document.getElementById('loginButton');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Signing in...';
            btn.disabled = true;
        });
    }

    // Handle logout via query parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logout') === '1') {
        // Force logout
        window.location.href = '<?= SITE_URL ?>auth/logout.php'; // create a simple logout script or use direct request
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>