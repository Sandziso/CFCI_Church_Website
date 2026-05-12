<?php
// admin/security/security-settings.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Helper to get a security setting
function get_security_setting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM security_settings WHERE setting_key = ? AND is_enabled = 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['setting_value'] : $default;
}

// Helper to update/insert a security setting
function set_security_setting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO security_settings (setting_key, setting_value, description, category, is_enabled) 
                           VALUES (?, ?, ?, ?, 1) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $stmt->execute([$key, $value, $key, 'general']);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    set_security_setting('max_login_attempts', (int)$_POST['max_login_attempts']);
    set_security_setting('lockout_duration_minutes', (int)$_POST['lockout_duration_minutes']);
    set_security_setting('session_timeout_minutes', (int)$_POST['session_timeout_minutes']);
    set_security_setting('password_min_length', (int)$_POST['password_min_length']);
    set_security_setting('password_require_uppercase', isset($_POST['password_require_uppercase']) ? '1' : '0');
    set_security_setting('password_require_lowercase', isset($_POST['password_require_lowercase']) ? '1' : '0');
    set_security_setting('password_require_numbers', isset($_POST['password_require_numbers']) ? '1' : '0');
    set_security_setting('password_require_special', isset($_POST['password_require_special']) ? '1' : '0');
    set_security_setting('password_history_count', (int)$_POST['password_history_count']);
    set_security_setting('password_expiry_days', (int)$_POST['password_expiry_days']);
    set_security_setting('enable_2fa', isset($_POST['enable_2fa']) ? '1' : '0');
    set_security_setting('require_email_verification', isset($_POST['require_email_verification']) ? '1' : '0');
    $message = '<div class="alert alert-success">Security settings updated.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Settings | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .form-label { font-weight: 500; }
        .form-switch .form-check-input { width: 3em; height: 1.5em; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Security Settings</h4>
        <?= $message ?>

        <div class="card p-4">
            <form method="post">
                <h5 class="fw-semibold mb-3">Login & Lockout</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" class="form-control" value="<?= get_security_setting('max_login_attempts', 5) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lockout Duration (minutes)</label>
                        <input type="number" name="lockout_duration_minutes" class="form-control" value="<?= get_security_setting('lockout_duration_minutes', 15) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Session Timeout (minutes)</label>
                        <input type="number" name="session_timeout_minutes" class="form-control" value="<?= get_security_setting('session_timeout_minutes', 60) ?>">
                    </div>
                </div>

                <h5 class="fw-semibold mt-4 mb-3">Password Policy</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Min Length</label>
                        <input type="number" name="password_min_length" class="form-control" value="<?= get_security_setting('password_min_length', 8) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">History Count</label>
                        <input type="number" name="password_history_count" class="form-control" value="<?= get_security_setting('password_history_count', 5) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Expiry (days, 0 = never)</label>
                        <input type="number" name="password_expiry_days" class="form-control" value="<?= get_security_setting('password_expiry_days', 0) ?>">
                    </div>
                    <div class="col-md-3 d-flex flex-column justify-content-end">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="uppercase" name="password_require_uppercase" <?= get_security_setting('password_require_uppercase', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="uppercase">Require Uppercase</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="lowercase" name="password_require_lowercase" <?= get_security_setting('password_require_lowercase', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="lowercase">Require Lowercase</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="number" name="password_require_numbers" <?= get_security_setting('password_require_numbers', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="number">Require Numbers</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="special" name="password_require_special" <?= get_security_setting('password_require_special', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="special">Require Special Chars</label>
                        </div>
                    </div>
                </div>

                <h5 class="fw-semibold mt-4 mb-3">Authentication Enhancements</h5>
                <div class="d-flex gap-4 flex-wrap">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" <?= get_security_setting('enable_2fa', '0') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enable_2fa">Enable Two‑Factor Authentication (2FA)</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" <?= get_security_setting('require_email_verification', '1') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="require_email_verification">Require Email Verification</label>
                    </div>
                </div>

                <button type="submit" name="save" class="btn btn-primary mt-4"><i class="fas fa-save me-1"></i> Update Security Settings</button>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>