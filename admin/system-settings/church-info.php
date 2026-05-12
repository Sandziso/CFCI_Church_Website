<?php
// admin/system-settings/church-info.php
require_once __DIR__ . '/../includes/admin_functions.php';

function get_setting($key, $default = '') {
    global $conn; static $cache = [];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('meta_description', $_POST['meta_description']);
    set_setting('meta_keywords', $_POST['meta_keywords']);
    set_setting('facebook_url', $_POST['facebook_url']);
    set_setting('youtube_url', $_POST['youtube_url']);
    set_setting('instagram_url', $_POST['instagram_url']);
    set_setting('twitter_url', $_POST['twitter_url']);
    set_setting('whatsapp_url', $_POST['whatsapp_url']);
    set_setting('google_analytics_id', $_POST['google_analytics_id']);
    $message = '<div class="alert alert-success">Church info updated.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Church Info | CFCI Admin</title>
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
        <h4 class="fw-bold mb-4">Church Information</h4>
        <?= $message ?>

        <div class="card p-4">
            <h5 class="fw-semibold mb-3">SEO & Analytics</h5>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars(get_setting('meta_description')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars(get_setting('meta_keywords')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Analytics ID</label>
                    <input type="text" name="google_analytics_id" class="form-control" value="<?= htmlspecialchars(get_setting('google_analytics_id')) ?>">
                </div>

                <h5 class="fw-semibold mt-4 mb-3">Social Media Links</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars(get_setting('facebook_url')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube</label>
                        <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars(get_setting('youtube_url')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <input type="url" name="instagram_url" class="form-control" value="<?= htmlspecialchars(get_setting('instagram_url')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter / X</label>
                        <input type="url" name="twitter_url" class="form-control" value="<?= htmlspecialchars(get_setting('twitter_url')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp</label>
                        <input type="url" name="whatsapp_url" class="form-control" value="<?= htmlspecialchars(get_setting('whatsapp_url')) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-save me-1"></i> Save Info</button>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>