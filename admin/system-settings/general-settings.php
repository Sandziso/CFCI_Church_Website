<?php
// admin/system-settings/general-settings.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Helper functions (shared)
function get_setting($key, $default = '') {
    global $conn;
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cache[$key] = ($row ? $row['setting_value'] : $default);
}
function set_setting($key, $value, $type = 'string', $category = 'general') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, category) 
                           VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $stmt->execute([$key, $value, $type, $category]);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    set_setting('church_name', $_POST['church_name']);
    set_setting('church_address', $_POST['church_address']);
    set_setting('church_phone', $_POST['church_phone']);
    set_setting('church_email', $_POST['church_email']);
    set_setting('registration_allowed', isset($_POST['registration_allowed']) ? '1' : '0', 'boolean');
    set_setting('site_maintenance', isset($_POST['site_maintenance']) ? '1' : '0', 'boolean');
    set_setting('primary_color', $_POST['primary_color']);
    set_setting('secondary_color', $_POST['secondary_color']);
    $message = '<div class="alert alert-success">General settings updated successfully.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Settings | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">General Settings</h4>
        <?= $message ?>

        <div class="card p-4">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Church Name</label>
                        <input type="text" name="church_name" class="form-control" value="<?= htmlspecialchars(get_setting('church_name')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="church_email" class="form-control" value="<?= htmlspecialchars(get_setting('church_email')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="church_phone" class="form-control" value="<?= htmlspecialchars(get_setting('church_phone')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="church_address" class="form-control" rows="2"><?= htmlspecialchars(get_setting('church_address')) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary Color</label>
                        <input type="color" name="primary_color" class="form-control form-control-color" value="<?= get_setting('primary_color', '#1a5276') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary Color</label>
                        <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= get_setting('secondary_color', '#e67e22') ?>">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="registration_allowed" name="registration_allowed" <?= get_setting('registration_allowed', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="registration_allowed">Allow New Registrations</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="site_maintenance" name="site_maintenance" <?= get_setting('site_maintenance', '0') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="site_maintenance">Maintenance Mode</label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="save" class="btn btn-primary mt-4"><i class="fas fa-save me-1"></i> Save Changes</button>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>