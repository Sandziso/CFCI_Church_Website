<?php
// ===================================================
// MEMBER - Donation History (List)
// ===================================================

require_once '../../includes/config.php';
require_once '../../includes/main-functions.php';

if (!is_logged_in()) {
    header('Location: ../../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$user_role = $_SESSION['user_role'];

// Redirect admins/pastors if needed
if (is_admin()) {
    header('Location: ../../admin/dashboard.php');
    exit();
} elseif (is_pastor()) {
    header('Location: ../../pastor/dashboard.php');
    exit();
}

require_once '../../includes/database.php';
$database = Database::getInstance();
$db = $database->getConnection();

// --- Pagination ---
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset   = ($page - 1) * $per_page;

// --- Filters ---
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($status_filter)) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}
if (!empty($method_filter)) {
    $where .= " AND payment_method = ?";
    $params[] = $method_filter;
}

// Count total
$count_stmt = $db->prepare("SELECT COUNT(*) FROM donations $where");
$count_stmt->execute($params);
$total_donations = $count_stmt->fetchColumn();
$total_pages = ceil($total_donations / $per_page);

// Fetch donations
$stmt = $db->prepare("SELECT * FROM donations $where ORDER BY donation_date DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message after donation
if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => 0,
    'prayer_requests'    => 0,
    'ministries_involved' => 0,
];
$profile_completion = 0;

require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
        }
        body { background-color: #f8f9fa; padding-top: 56px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 767.98px) { .main-content { margin-left: 0; } }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white; padding: 2rem 1rem; margin-bottom: 2rem; border-radius: 10px;
        }
        .donation-table { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .badge-status-pending { background-color: #ffc107; color: #000; }
        .badge-status-completed { background-color: #28a745; }
        .badge-status-failed { background-color: #dc3545; }
        .badge-status-refunded { background-color: #6c757d; }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="dashboard-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-donate me-2"></i>My Donations</h1>
                        <p class="lead mb-0">Record of your generosity</p>
                    </div>
                    <a href="donate.php" class="btn btn-light btn-lg"><i class="fas fa-plus me-1"></i> New Donation</a>
                </div>

                <?php if (isset($flash)): ?>
                    <div class="alert alert-<?php echo $flash_type; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($flash); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $status_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" <?php echo $method_filter == 'cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="mobile_money" <?php echo $method_filter == 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                            <option value="bank_transfer" <?php echo $method_filter == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="card" <?php echo $method_filter == 'card' ? 'selected' : ''; ?>>Card</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                </form>

                <!-- Donations Table -->
                <?php if (count($donations) > 0): ?>
                    <div class="donation-table table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount (SZL)</th>
                                    <th>Purpose</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $d): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($d['donation_date'])); ?></td>
                                        <td class="fw-bold"><?php echo number_format($d['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($d['purpose'] ?: '—'); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $d['payment_method'])); ?></td>
                                        <td><span class="badge badge-status-<?php echo $d['status']; ?>"><?php echo ucfirst($d['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($d['notes'] ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&method=<?php echo urlencode($method_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-hand-holding-heart fa-4x text-muted mb-3"></i>
                        <h4>No donations recorded yet</h4>
                        <p class="text-muted">Your giving history will appear here once you record a donation.</p>
                        <a href="donate.php" class="btn btn-primary">Record Your First Donation</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>