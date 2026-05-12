<?php
// admin/security/block-users.php
require_once __DIR__ . '/../includes/admin_functions.php';

$message = '';

// Block IP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_ip'])) {
    $ip = trim($_POST['ip_address']);
    $reason = $_POST['reason'] ?? 'manual';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $stmt = $conn->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_by) VALUES (?, ?, ?)");
        $stmt->execute([$ip, $reason, $_SESSION['user_id']]);
        $message = '<div class="alert alert-success">IP blocked successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Invalid IP address.</div>';
    }
}

// Unblock IP
if (isset($_GET['unblock'])) {
    $id = (int)$_GET['unblock'];
    $conn->prepare("DELETE FROM blocked_ips WHERE id = ?")->execute([$id]);
    header("Location: block-users.php?msg=unblocked");
    exit;
}

$blocked = $conn->query("SELECT b.*, u.full_name as blocked_by_name FROM blocked_ips b LEFT JOIN users u ON b.blocked_by = u.id ORDER BY b.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blocked IPs | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .badge-reason { padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.75rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Blocked IP Addresses</h4>
        <?= $message ?>

        <div class="card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Block an IP</h5>
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reason</label>
                    <select name="reason" class="form-select">
                        <option value="brute_force">Brute Force</option>
                        <option value="spam">Spam</option>
                        <option value="malicious">Malicious Activity</option>
                        <option value="manual">Manual Block</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="block_ip" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Block</button>
                </div>
            </form>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Blocked By</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocked as $b): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($b['ip_address']) ?></code></td>
                            <td><span class="badge-reason bg-danger bg-opacity-10 text-danger"><?= $b['reason'] ?></span></td>
                            <td><?= htmlspecialchars($b['blocked_by_name'] ?? 'System') ?></td>
                            <td><?= date('M d, Y H:i', strtotime($b['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="?unblock=<?= $b['id'] ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Unblock this IP?')"><i class="fas fa-unlock-alt"></i> Unblock</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($blocked)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No blocked IP addresses.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>