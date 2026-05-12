<?php
// ===================================================
// MEMBER - Events Listing
// ===================================================

require_once '../../includes/config.php';
require_once '../../includes/main-functions.php';

if (!is_logged_in()) {
    header('Location: ../../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
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

// --- Pagination & Filters ---
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$month_filter = $_GET['month'] ?? '';  // YYYY-MM

// Build query – without status (fixes unknown column error)
$where = "WHERE e.event_date >= CURDATE()";
$params = [];

if (!empty($search)) {
    $where .= " AND (e.title LIKE ? OR e.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($month_filter)) {
    $where .= " AND DATE_FORMAT(e.event_date, '%Y-%m') = ?";
    $params[] = $month_filter;
}

// Count total
$count_query = "SELECT COUNT(*) FROM events e $where";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_events = $stmt->fetchColumn();
$total_pages = ceil($total_events / $per_page);

// Fetch events
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) as registered_count
          FROM events e
          $where
          ORDER BY e.event_date ASC
          LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quick stats for sidebar (dummy values, can be replaced with real ones later)
$quick_stats = [
    'upcoming_events' => $total_events,
    'sermons_available' => 0,
    'prayer_requests' => 0,
    'ministries_involved' => 0,
];
$profile_completion = 0;

// Include topbar + sidebar
require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - <?php echo SITE_NAME; ?></title>
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
        .event-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .event-card:hover {
            transform: translateY(-3px);
        }
        .event-date-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            min-width: 70px;
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
                    <h1><i class="fas fa-calendar-alt me-2"></i> Upcoming Events</h1>
                    <p class="lead mb-0">Join us in fellowship and worship</p>
                </div>

                <!-- Search & Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($month_filter); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </form>

                <!-- Events List -->
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="event-date-badge">
                                        <div class="h4 mb-0"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                        <small><?php echo date('M', strtotime($event['event_date'])); ?></small>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        <?php if (!empty($event['start_time'])): ?>
                                        <span class="text-muted"><i class="fas fa-clock me-1"></i><?php echo date('g:i A', strtotime($event['start_time'])); ?><?php if (!empty($event['end_time'])) echo ' - ' . date('g:i A', strtotime($event['end_time'])); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($event['location'])): ?>
                                        <span class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($event['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                    <p class="text-muted mb-2"><?php echo strlen($event['description']) > 120 ? substr(htmlspecialchars($event['description']), 0, 120) . '...' : htmlspecialchars($event['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php
                                        // Safely determine registration status without the status column
                                        $reg_status = 'available'; // default
                                        if (isset($event['registration_status'])) {
                                            $reg_status = $event['registration_status'];
                                        } elseif (isset($event['capacity']) && $event['capacity'] > 0) {
                                            if ($event['registered_count'] >= $event['capacity']) {
                                                $reg_status = 'full';
                                            }
                                        }
                                        if ($reg_status == 'available'): ?>
                                            <span class="badge bg-success">Open</span>
                                        <?php elseif ($reg_status == 'full'): ?>
                                            <span class="badge bg-danger">Full</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Closed</span>
                                        <?php endif; ?>
                                        <div>
                                            <a href="details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">Details</a>
                                            <?php if ($reg_status == 'available'): ?>
                                                <a href="register.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Register</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&month=<?php echo urlencode($month_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4>No events found</h4>
                        <p class="text-muted">Check back later or adjust your filters.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>