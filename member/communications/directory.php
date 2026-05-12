<?php
// ===================================================
// MEMBER - Member Directory
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

// --- Pagination & Search ---
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset   = ($page - 1) * $per_page;

$search   = trim($_GET['search'] ?? '');

$where = "WHERE u.is_active = 1 AND u.role = 'member' AND u.profile_privacy IN ('public', 'members')";
$params = [];

if (!empty($search)) {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR up.phone LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

// Count total
$count_stmt = $db->prepare("SELECT COUNT(*) FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch members
$stmt = $db->prepare("
    SELECT u.id, u.full_name, u.email, u.avatar_url, u.city, u.country,
           up.phone
    FROM users u
    LEFT JOIN user_profiles up ON u.id = up.user_id
    $where
    ORDER BY u.full_name ASC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Member Directory - <?php echo SITE_NAME; ?></title>
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
        .member-card {
            background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s;
        }
        .member-card:hover { transform: translateY(-3px); }
        .avatar {
            width: 60px; height: 60px; border-radius: 50%; object-fit: cover;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="dashboard-header">
                    <h1><i class="fas fa-address-book me-2"></i>Member Directory</h1>
                    <p class="lead mb-0">Connect with fellow members</p>
                </div>

                <!-- Search -->
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        <?php if (!empty($search)): ?>
                            <a href="directory.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Members Grid -->
                <?php if (count($members) > 0): ?>
                    <div class="row">
                        <?php foreach ($members as $m):
                            $initials = '';
                            $words = explode(' ', $m['full_name']);
                            foreach ($words as $w) if (!empty($w)) $initials .= strtoupper($w[0]);
                            $initials = substr($initials, 0, 2);
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="member-card text-center">
                                    <div class="avatar mx-auto mb-3">
                                        <?php if (!empty($m['avatar_url']) && $m['avatar_url'] != '/assets/images/default-avatar.png'): ?>
                                            <img src="<?php echo htmlspecialchars($m['avatar_url']); ?>" alt="<?php echo htmlspecialchars($m['full_name']); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                                        <?php else: ?>
                                            <?php echo $initials; ?>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($m['full_name']); ?></h5>
                                    <?php if (!empty($m['city'])): ?>
                                        <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($m['city']); ?><?php if (!empty($m['country'])) echo ', ' . htmlspecialchars($m['country']); ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($m['email']); ?></p>
                                    <?php if (!empty($m['phone'])): ?>
                                        <p class="small text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($m['phone']); ?></p>
                                    <?php endif; ?>
                                    <a href="../profile/?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4>No members found</h4>
                        <p class="text-muted">No members match your search criteria.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>