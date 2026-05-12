<?php
// ===================================================
// MEMBER - My Prayer Requests
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
$category_filter = $_GET['category'] ?? '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($status_filter)) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}
if (!empty($category_filter)) {
    $where .= " AND category = ?";
    $params[] = $category_filter;
}

// Count total
$count_stmt = $db->prepare("SELECT COUNT(*) FROM prayer_requests $where");
$count_stmt->execute($params);
$total_requests = $count_stmt->fetchColumn();
$total_pages = ceil($total_requests / $per_page);

// Fetch requests
$stmt = $db->prepare("SELECT * FROM prayer_requests $where ORDER BY submitted_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => 0,
    'prayer_requests'    => $total_requests,
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
    <title>My Prayer Requests - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
            --accent-color: #2ecc71;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 56px;
        }
        .main-content {
            margin-left: 240px;
            padding: 20px;
        }
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
            }
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .prayer-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .prayer-card:hover {
            transform: translateY(-2px);
        }
        .badge-urgency-high { background-color: #dc3545; }
        .badge-urgency-urgent { background-color: #ffc107; color: #000; }
        .badge-urgency-normal { background-color: #17a2b8; }
        .badge-urgency-low { background-color: #6c757d; }
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-submit:hover {
            background-color: var(--secondary-color);
            color: white;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="dashboard-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-praying-hands me-2"></i>My Prayer Requests</h1>
                        <p class="lead mb-0">Keep track of your prayer needs</p>
                    </div>
                    <a href="submit.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                </div>

                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="addressed" <?php echo $status_filter == 'addressed' ? 'selected' : ''; ?>>Addressed</option>
                            <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="health" <?php echo $category_filter == 'health' ? 'selected' : ''; ?>>Health</option>
                            <option value="financial" <?php echo $category_filter == 'financial' ? 'selected' : ''; ?>>Financial</option>
                            <option value="family" <?php echo $category_filter == 'family' ? 'selected' : ''; ?>>Family</option>
                            <option value="spiritual" <?php echo $category_filter == 'spiritual' ? 'selected' : ''; ?>>Spiritual</option>
                            <option value="work" <?php echo $category_filter == 'work' ? 'selected' : ''; ?>>Work</option>
                            <option value="other" <?php echo $category_filter == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                </form>

                <!-- Requests List -->
                <?php if (count($requests) > 0): ?>
                    <?php foreach ($requests as $req): ?>
                        <div class="prayer-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">
                                        <a href="details.php?id=<?php echo $req['id']; ?>" class="text-decoration-none text-dark">
                                            <?php 
                                            $excerpt = $req['request_text'];
                                            echo strlen($excerpt) > 80 ? htmlspecialchars(substr($excerpt, 0, 80)) . '...' : htmlspecialchars($excerpt);
                                            ?>
                                        </a>
                                    </h5>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-primary"><?php echo ucfirst($req['category']); ?></span>
                                        <span class="badge badge-urgency-<?php echo $req['urgency']; ?>"><?php echo ucfirst($req['urgency']); ?></span>
                                        <span class="badge <?php echo $req['status'] == 'addressed' ? 'bg-success' : ($req['status'] == 'closed' ? 'bg-secondary' : 'bg-warning'); ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                        <?php if ($req['is_anonymous']): ?>
                                            <span class="badge bg-dark">Anonymous</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i> <?php echo date('M d, Y g:i A', strtotime($req['submitted_at'])); ?>
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <span class="text-muted" title="People prayed">
                                        <i class="fas fa-pray me-1"></i> <?php echo $req['prayer_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-pray fa-4x text-muted mb-3"></i>
                        <h4>No prayer requests</h4>
                        <p class="text-muted">You haven't submitted any prayer requests yet.</p>
                        <a href="submit.php" class="btn btn-submit">Submit Your First Request</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>