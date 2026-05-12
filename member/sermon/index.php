<?php
// ===================================================
// MEMBER - Sermons Listing
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

// Database connection
require_once '../../includes/database.php';
$database = Database::getInstance();
$db = $database->getConnection();

// --- Pagination ---
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 8;
$offset   = ($page - 1) * $per_page;

// --- Search & Filter ---
$search   = $_GET['search'] ?? '';
$year     = $_GET['year'] ?? '';

// ** Fix: Use is_published = 1 instead of status = 'published' **
$where = "WHERE is_published = 1";
$params = [];

if (!empty($search)) {
    $where .= " AND (title LIKE ? OR preacher_name LIKE ? OR bible_passage LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($year)) {
    $where .= " AND YEAR(sermon_date) = ?";
    $params[] = $year;
}

// Count total
$count_query = "SELECT COUNT(*) FROM sermons $where";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_sermons = $stmt->fetchColumn();
$total_pages   = ceil($total_sermons / $per_page);

// Fetch sermons
$query = "SELECT * FROM sermons $where ORDER BY sermon_date DESC LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => $total_sermons,
    'prayer_requests'    => 0,
    'ministries_involved' => 0,
];
$profile_completion = 0; // Sidebar requires this variable

// Include topbar + sidebar
require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sermons - <?php echo SITE_NAME; ?></title>
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
        .sermon-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .sermon-card:hover {
            transform: translateY(-3px);
        }
        .sermon-date {
            color: var(--primary-color);
            font-weight: 600;
        }
        .btn-listen {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-listen:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-watch {
            color: #dc3545;
            border-color: #dc3545;
        }
        .btn-watch:hover {
            background-color: #dc3545;
            color: white;
        }
        .pagination .page-link {
            color: var(--primary-color);
        }
        .pagination .active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                <div class="dashboard-header">
                    <h1><i class="fas fa-podcast me-2"></i> Sermons</h1>
                    <p class="lead mb-0">Listen to the latest messages from our services</p>
                </div>

                <!-- Search & Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by title, preacher, or scripture..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            <?php
                            // ** Fix: also changed to is_published = 1 **
                            $y_stmt = $db->query("SELECT DISTINCT YEAR(sermon_date) as yr FROM sermons WHERE is_published = 1 ORDER BY yr DESC");
                            $years = $y_stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($years as $y) {
                                $selected = ($year == $y) ? 'selected' : '';
                                echo "<option value=\"$y\" $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                </form>

                <!-- Sermons List -->
                <?php if (count($sermons) > 0): ?>
                    <?php foreach ($sermons as $sermon): ?>
                        <div class="sermon-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1">
                                        <a href="details.php?id=<?php echo $sermon['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($sermon['title']); ?>
                                        </a>
                                    </h5>
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        <?php if (!empty($sermon['preacher_name'])): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($sermon['preacher_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($sermon['bible_passage'])): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-bible me-1"></i><?php echo htmlspecialchars($sermon['bible_passage']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="sermon-date">
                                            <i class="fas fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($sermon['sermon_date'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($sermon['description'])): ?>
                                        <p class="text-muted mb-2">
                                            <?php 
                                            $desc = strip_tags($sermon['description']);
                                            echo strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <?php if (!empty($sermon['audio_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($sermon['audio_url']); ?>" 
                                           class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                            <i class="fas fa-headphones me-1"></i> Listen
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($sermon['video_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($sermon['video_url']); ?>" 
                                           class="btn btn-sm btn-outline-danger" target="_blank">
                                            <i class="fas fa-video me-1"></i> Watch
                                        </a>
                                    <?php endif; ?>
                                    <a href="details.php?id=<?php echo $sermon['id']; ?>" 
                                       class="btn btn-sm btn-outline-secondary mt-2 mt-md-0 d-block d-md-inline-block">
                                        Details
                                    </a>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo urlencode($year); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-podcast fa-4x text-muted mb-3"></i>
                        <h4>No sermons found</h4>
                        <p class="text-muted">No sermons match your criteria. Please adjust your filters.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>