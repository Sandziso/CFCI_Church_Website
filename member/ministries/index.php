<?php
// ===================================================
// MEMBER - Ministries Listing
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

// Fetch all active ministries
$ministries = [];
try {
    $stmt = $db->prepare("SELECT * FROM ministries ORDER BY name ASC");
    $stmt->execute();
    $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ministries fetch error: " . $e->getMessage());
    $ministries = [];
}

// Get user's current ministry memberships
$user_ministries = [];
try {
    $stmt = $db->prepare("SELECT ministry_id FROM ministry_members WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_ministries = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
    error_log("User ministries fetch error: " . $e->getMessage());
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events' => 0,
    'sermons_available' => 0,
    'prayer_requests' => 0,
    'ministries_involved' => count($user_ministries),
];
$profile_completion = 0; // will be defined in dashboard.php, but we need a dummy here for sidebar

// Include topbar + sidebar
require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ministries - <?php echo SITE_NAME; ?></title>
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
        .ministry-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .ministry-card:hover {
            transform: translateY(-3px);
        }
        .ministry-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        .btn-join {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-join:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .joined-badge {
            background: var(--accent-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
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
                    <h1><i class="fas fa-hands-helping me-2"></i> Ministries</h1>
                    <p class="lead mb-0">Discover where you can serve and grow</p>
                </div>

                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

                <!-- Ministries Grid -->
                <?php if (count($ministries) > 0): ?>
                    <div class="row">
                        <?php foreach ($ministries as $ministry): 
                            $is_member = in_array($ministry['id'], $user_ministries);
                        ?>
                            <div class="col-lg-6">
                                <div class="ministry-card">
                                    <div class="ministry-icon">
                                        <i class="fas <?php echo !empty($ministry['icon']) ? htmlspecialchars($ministry['icon']) : 'fa-users'; ?>"></i>
                                    </div>
                                    <h4 class="text-center"><?php echo htmlspecialchars($ministry['name']); ?></h4>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($ministry['description'] ?? 'No description available.')); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if ($is_member): ?>
                                            <span class="joined-badge"><i class="fas fa-check me-1"></i> Joined</span>
                                            <span class="text-muted"><i class="fas fa-user-check me-1"></i> You're a member</span>
                                        <?php else: ?>
                                            <span></span>
                                            <a href="join.php?id=<?php echo $ministry['id']; ?>" class="btn btn-join btn-sm">
                                                <i class="fas fa-plus-circle me-1"></i> Join Ministry
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4>No ministries available</h4>
                        <p class="text-muted">Check back later or contact the church office.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>