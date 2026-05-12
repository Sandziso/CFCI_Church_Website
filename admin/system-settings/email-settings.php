<?php
// admin/system-settings/email-settings.php
require_once __DIR__ . '/../includes/admin_functions.php';

function get_setting($key, $default = '') {
    global $conn; static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cache[$key] = ($row ? $row['setting_value'] : $default);
}
function set_setting($key, $value, $type = 'string', $category = 'email') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, category) 
                           VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $stmt->execute([$key, $value, $type, $category]);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('smtp_host', $_POST['smtp_host']);
    set_setting('smtp_port', $_POST['smtp_port']);
    set_setting('smtp_user', $_POST['smtp_user']);
    // Only update password if provided
    if (!empty($_POST['smtp_pass'])) {
        set_setting('smtp_pass', $_POST['smtp_pass']);
    }
    set_setting('smtp_encryption', $_POST['smtp_encryption']);
    set_setting('mail_from_address', $_POST['mail_from_address']);
    set_setting('mail_from_name', $_POST['mail_from_name']);
    $message = '<div class="alert alert-success">Email settings saved.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Settings | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Email Settings</h4>
        <?= $message ?>

        <div class="card p-4">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars(get_setting('smtp_host')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars(get_setting('smtp_port', '587')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="tls" <?= get_setting('smtp_encryption') == 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= get_setting('smtp_encryption') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= get_setting('smtp_encryption') == 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars(get_setting('smtp_user')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Address</label>
                        <input type="email" name="mail_from_address" class="form-control" value="<?= htmlspecialchars(get_setting('mail_from_address')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="<?= htmlspecialchars(get_setting('mail_from_name')) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-save me-1"></i> Save Email Settings</button>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>