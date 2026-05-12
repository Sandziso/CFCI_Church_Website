<?php
// admin/security/activity-logs.php
require_once __DIR__ . '/../includes/admin_functions.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$typeFilter = $_GET['type'] ?? '';

$where = '1=1';
$params = [];
if ($typeFilter) {
    $where .= ' AND event_type = ?';
    $params[] = $typeFilter;
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM security_logs WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT * FROM security_logs WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$types = $conn->query("SELECT DISTINCT event_type FROM security_logs")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .log-entry { padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .log-entry:last-child { border-bottom: none; }
        .badge-type { padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.7rem; font-weight: 600; }
        .badge-info-custom { background: #e0f2fe; color: #0369a1; }
        .badge-warning-custom { background: #fef3c7; color: #b45309; }
        .badge-danger-custom { background: #fee2e2; color: #b91c1c; }
        .badge-success-custom { background: #dcfce7; color: #15803d; }
        .pagination .page-link { border-radius: 8px; margin: 0 3px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Activity Logs</h4>

        <div class="mb-4">
            <form class="row g-2">
                <div class="col-auto">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Event Types</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>" <?= $t === $typeFilter ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="activity-logs.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>

        <div class="card p-3">
            <?php if (empty($logs)): ?>
                <p class="text-center text-muted py-4">No logs found.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): 
                    $badgeClass = 'badge-info-custom';
                    if (strpos($log['event_type'], 'FAIL') !== false) $badgeClass = 'badge-danger-custom';
                    elseif (strpos($log['event_type'], 'SUCCESS') !== false) $badgeClass = 'badge-success-custom';
                    elseif (strpos($log['event_type'], 'WARNING') !== false) $badgeClass = 'badge-warning-custom';
                ?>
                <div class="log-entry">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge-type <?= $badgeClass ?>"><?= htmlspecialchars($log['event_type']) ?></span>
                        <div>
                            <?php if ($log['user_id']): ?>
                                <span class="fw-semibold">User #<?= $log['user_id'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">System</span>
                            <?php endif; ?>
                            <small class="text-muted ms-2">IP: <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></small>
                            <?php if ($log['details']): ?>
                                <p class="mb-0 text-muted small mt-1"><?= htmlspecialchars(substr($log['details'], 0, 100)) ?><?= strlen($log['details']) > 100 ? '...' : '' ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&type=<?= urlencode($typeFilter) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>