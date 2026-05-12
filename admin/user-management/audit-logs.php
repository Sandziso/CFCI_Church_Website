<?php
// admin/user-management/audit-logs.php
require_once __DIR__ . '/../includes/admin_functions.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

// Total
$totalStmt = $conn->query("SELECT COUNT(*) FROM audit_log");
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $conn->prepare("SELECT a.*, u.full_name as user_name FROM audit_log a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .log-entry { border-bottom: 1px solid #f1f5f9; padding: 0.75rem 0; }
        .log-entry:last-child { border-bottom: none; }
        .badge-action { background: #e2e8f0; color: #1a5276; padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.7rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Audit Logs</h4>
        <div class="card p-4">
            <?php foreach ($logs as $log): ?>
                <div class="log-entry d-flex justify-content-between align-items-center">
                    <div>
                        <?php if ($log['user_id']): ?>
                            <span class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?></span>
                        <?php else: ?>
                            <span class="text-muted">System</span>
                        <?php endif; ?>
                        <span class="badge-action ms-2"><?= htmlspecialchars($log['action_type']) ?></span>
                        <small class="text-muted ms-2"><?= htmlspecialchars($log['action_table'] ?? '') ?></small>
                        <?php if ($log['record_id']): ?>
                            <small class="text-muted"> #<?= $log['record_id'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></small>
                        <div><small class="text-muted">IP: <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></small></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <p class="text-center text-muted py-4">No audit logs found.</p>
            <?php endif; ?>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>