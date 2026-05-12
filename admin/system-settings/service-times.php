<?php
// admin/system-settings/service-times.php
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

// Load current service times from JSON
$serviceTimes = json_decode(get_setting('service_times', '{}'), true);
if (!$serviceTimes) $serviceTimes = [];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Build array from submitted fields
    $services = [];
    if (isset($_POST['day']) && is_array($_POST['day'])) {
        foreach ($_POST['day'] as $i => $day) {
            $day = trim($day);
            $time = trim($_POST['time'][$i] ?? '');
            if ($day !== '' && $time !== '') {
                $services[strtolower($day)] = $time;
            }
        }
    }
    set_setting('service_times', json_encode($services), 'json');
    $message = '<div class="alert alert-success">Service times updated.</div>';
    $serviceTimes = $services;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Times | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .service-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .service-row input { flex: 1; }
        .remove-btn { color: #e74c3c; cursor: pointer; font-size: 1.2rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Service Times</h4>
        <?= $message ?>

        <div class="card p-4">
            <form method="post" id="serviceForm">
                <div id="service-container">
                    <?php $i = 0; foreach ($serviceTimes as $day => $time): ?>
                    <div class="service-row">
                        <input type="text" name="day[]" class="form-control" value="<?= htmlspecialchars(ucfirst($day)) ?>" placeholder="Day (e.g., Sunday)">
                        <input type="text" name="time[]" class="form-control" value="<?= htmlspecialchars($time) ?>" placeholder="Time (e.g., 9:00 AM - 12:00 PM)">
                        <span class="remove-btn" onclick="this.parentElement.remove()"><i class="fas fa-trash-alt"></i></span>
                    </div>
                    <?php $i++; endforeach; ?>
                    <?php if ($i === 0): ?>
                    <div class="service-row">
                        <input type="text" name="day[]" class="form-control" placeholder="Day (e.g., Sunday)">
                        <input type="text" name="time[]" class="form-control" placeholder="Time (e.g., 9:00 AM - 12:00 PM)">
                        <span class="remove-btn" onclick="this.parentElement.remove()"><i class="fas fa-trash-alt"></i></span>
                    </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addServiceRow()">
                    <i class="fas fa-plus"></i> Add Service
                </button>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Times</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
function addServiceRow() {
    const container = document.getElementById('service-container');
    const row = document.createElement('div');
    row.className = 'service-row';
    row.innerHTML = `
        <input type="text" name="day[]" class="form-control" placeholder="Day (e.g., Sunday)">
        <input type="text" name="time[]" class="form-control" placeholder="Time (e.g., 9:00 AM - 12:00 PM)">
        <span class="remove-btn" onclick="this.parentElement.remove()"><i class="fas fa-trash-alt"></i></span>
    `;
    container.appendChild(row);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>