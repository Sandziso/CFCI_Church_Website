<?php
// ===================================================
// MEMBER - Join a Ministry
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

require_once '../../includes/database.php';
$database = Database::getInstance();
$db = $database->getConnection();

$ministry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ministry_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch ministry details
$stmt = $db->prepare("SELECT * FROM ministries WHERE id = ?");
$stmt->execute([$ministry_id]);
$ministry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ministry) {
    $_SESSION['flash_message'] = 'Ministry not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Check if already a member
$is_member = false;
$check = $db->prepare("SELECT id FROM ministry_members WHERE user_id = ? AND ministry_id = ?");
$check->execute([$user_id, $ministry_id]);
if ($check->fetch()) {
    $is_member = true;
}

// Handle join request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_join'])) {
    if (!$is_member) {
        try {
            $stmt = $db->prepare("INSERT INTO ministry_members (user_id, ministry_id, joined_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $ministry_id]);
            $_SESSION['flash_message'] = "You have successfully joined the " . htmlspecialchars($ministry['name']) . " ministry!";
            $_SESSION['flash_type'] = 'success';
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
            error_log("Join ministry error: " . $e->getMessage());
        }
    } else {
        $error = "You are already a member of this ministry.";
    }
}

// Quick stats for sidebar
$user_ministries_count = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM ministry_members WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_ministries_count = $stmt->fetchColumn();
} catch (Exception $e) {
    // ignore
}
$quick_stats = [
    'upcoming_events' => 0,
    'sermons_available' => 0,
    'prayer_requests' => 0,
    'ministries_involved' => $user_ministries_count,
];
$profile_completion = 0;

require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join <?php echo htmlspecialchars($ministry['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
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
        .join-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="join-card text-center">
                    <?php if ($is_member): ?>
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h2>Already a Member</h2>
                        <p>You are currently a member of <strong><?php echo htmlspecialchars($ministry['name']); ?></strong>.</p>
                        <a href="index.php" class="btn btn-primary mt-3">Back to Ministries</a>
                    <?php else: ?>
                        <i class="fas fa-hands-helping fa-4x text-primary mb-3"></i>
                        <h2>Join <?php echo htmlspecialchars($ministry['name']); ?></h2>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($ministry['description'] ?? '')); ?></p>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <p>You are about to join as <strong><?php echo htmlspecialchars($user_name); ?></strong>.</p>
                        <form method="POST">
                            <button type="submit" name="confirm_join" class="btn btn-success btn-lg mt-2">
                                <i class="fas fa-user-plus me-1"></i> Confirm Join
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg mt-2">Cancel</a>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>