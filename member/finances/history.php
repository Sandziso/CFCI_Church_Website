<?php
// ===================================================
// MEMBER - Donation History with Summary
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

// --- Summary Stats ---
$stats = [];
try {
    // All time total (completed only)
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM donations WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $stats['all_time'] = $stmt->fetchColumn();

    // This year
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM donations WHERE user_id = ? AND status = 'completed' AND YEAR(donation_date) = YEAR(CURDATE())");
    $stmt->execute([$user_id]);
    $stats['this_year'] = $stmt->fetchColumn();

    // This month
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM donations WHERE user_id = ? AND status = 'completed' AND MONTH(donation_date) = MONTH(CURDATE()) AND YEAR(donation_date) = YEAR(CURDATE())");
    $stmt->execute([$user_id]);
    $stats['this_month'] = $stmt->fetchColumn();

    // Pending count
    $stmt = $db->prepare("SELECT COUNT(*) FROM donations WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $stats['pending'] = $stmt->fetchColumn();
} catch (Exception $e) {
    error_log("History stats error: " . $e->getMessage());
    $stats = ['all_time' => 0, 'this_year' => 0, 'this_month' => 0, 'pending' => 0];
}

// --- Date Range Filter ---
$from_date = $_GET['from'] ?? '';
$to_date   = $_GET['to'] ?? '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($from_date)) {
    $where .= " AND donation_date >= ?";
    $params[] = $from_date . ' 00:00:00';
}
if (!empty($to_date)) {
    $where .= " AND donation_date <= ?";
    $params[] = $to_date . ' 23:59:59';
}

// Fetch filtered donations
$stmt = $db->prepare("SELECT * FROM donations $where ORDER BY donation_date DESC LIMIT 50");
$stmt->execute($params);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Donation History - <?php echo SITE_NAME; ?></title>
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
        .stat-card {
            background: white; border-radius: 10px; padding: 1.5rem; text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 1.5rem;
        }
        .stat-number { font-size: 1.8rem; font-weight: bold; color: var(--primary-color); }
        .donation-table { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="dashboard-header">
                    <h1><i class="fas fa-chart-line me-2"></i>Giving History</h1>
                    <p class="lead mb-0">Summary of your generosity</p>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-infinity fa-2x text-primary mb-2"></i>
                            <div class="stat-number">SZL <?php echo number_format($stats['all_time'], 2); ?></div>
                            <div class="text-muted">Lifetime Total</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                            <div class="stat-number">SZL <?php echo number_format($stats['this_year'], 2); ?></div>
                            <div class="text-muted">This Year</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                            <div class="stat-number">SZL <?php echo number_format($stats['this_month'], 2); ?></div>
                            <div class="text-muted">This Month</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                            <div class="stat-number"><?php echo $stats['pending']; ?></div>
                            <div class="text-muted">Pending Donations</div>
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($from_date); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($to_date); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
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
                                    <th>Recurring</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $d): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($d['donation_date'])); ?></td>
                                        <td class="fw-bold"><?php echo number_format($d['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($d['purpose'] ?: '—'); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $d['payment_method'])); ?></td>
                                        <td><span class="badge bg-<?php echo $d['status'] == 'completed' ? 'success' : ($d['status'] == 'pending' ? 'warning text-dark' : 'secondary'); ?>"><?php echo ucfirst($d['status']); ?></span></td>
                                        <td><?php echo $d['recurring'] ? '<span class="text-success"><i class="fas fa-sync-alt me-1"></i>Yes</span>' : 'No'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                        <h4>No donations in this period</h4>
                        <p class="text-muted">Try adjusting the date range or <a href="donate.php">record a new donation</a>.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>