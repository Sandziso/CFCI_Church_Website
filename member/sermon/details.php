<?php
// ===================================================
// MEMBER - Sermon Details
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

$sermon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($sermon_id <= 0) {
    header('Location: index.php');
    exit();
}

// ** Fix: use is_published = 1 **
$stmt = $db->prepare("SELECT * FROM sermons WHERE id = ? AND is_published = 1");
$stmt->execute([$sermon_id]);
$sermon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sermon) {
    echo "<script>alert('Sermon not found or not available.'); window.location.href='index.php';</script>";
    exit();
}

// Quick stats for sidebar (dummy, adjust as needed)
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
    <title><?php echo htmlspecialchars($sermon['title']); ?> - <?php echo SITE_NAME; ?></title>
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
        .sermon-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .info-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        .info-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .sermon-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .media-section {
            margin-top: 1.5rem;
        }
        .btn-listen {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        .btn-watch {
            background-color: #dc3545;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Sermon Hero -->
                <div class="sermon-hero">
                    <h1><?php echo htmlspecialchars($sermon['title']); ?></h1>
                    <ul class="info-list">
                        <?php if (!empty($sermon['preacher_name'])): ?>
                            <li><i class="fas fa-user me-2"></i><strong>Preacher:</strong> <?php echo htmlspecialchars($sermon['preacher_name']); ?></li>
                        <?php endif; ?>
                        <li><i class="fas fa-calendar-alt me-2"></i><strong>Date:</strong> <?php echo date('F j, Y', strtotime($sermon['sermon_date'])); ?></li>
                        <?php if (!empty($sermon['bible_passage'])): ?>
                            <li><i class="fas fa-bible me-2"></i><strong>Scripture:</strong> <?php echo htmlspecialchars($sermon['bible_passage']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($sermon['duration'])): ?>
                            <li><i class="fas fa-clock me-2"></i><strong>Duration:</strong> <?php echo htmlspecialchars($sermon['duration']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Sermon Content -->
                <div class="sermon-content">
                    <h4>About This Sermon</h4>
                    <?php if (!empty($sermon['description'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($sermon['description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description available.</p>
                    <?php endif; ?>

                    <?php if (!empty($sermon['notes_text'])): ?>
                        <div class="mt-4">
                            <h5><i class="fas fa-file-alt me-2"></i>Sermon Notes</h5>
                            <p><?php echo nl2br(htmlspecialchars($sermon['notes_text'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Media Links -->
                    <div class="media-section border-top pt-3 mt-3">
                        <div class="row">
                            <?php if (!empty($sermon['audio_url'])): ?>
                                <div class="col-md-6 mb-2">
                                    <a href="<?php echo htmlspecialchars($sermon['audio_url']); ?>" 
                                       class="btn btn-listen w-100" target="_blank">
                                        <i class="fas fa-headphones me-2"></i> Listen to Audio
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($sermon['video_url'])): ?>
                                <div class="col-md-6 mb-2">
                                    <a href="<?php echo htmlspecialchars($sermon['video_url']); ?>" 
                                       class="btn btn-watch w-100" target="_blank">
                                        <i class="fas fa-video me-2"></i> Watch Video
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Sermons
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>