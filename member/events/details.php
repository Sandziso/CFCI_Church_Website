<?php
// ===================================================
// MEMBER - Event Details
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

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch event details
$stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "<script>alert('Event not found.'); window.location.href='index.php';</script>";
    exit();
}

// Check if already registered
$is_registered = false;
$reg_stmt = $db->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?");
$reg_stmt->execute([$user_id, $event_id]);
if ($reg_stmt->fetch()) {
    $is_registered = true;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $event_id = (int)$_POST['event_id'];
    if (!$is_registered && $event['registration_status'] == 'available') {
        try {
            $stmt = $db->prepare("INSERT INTO event_registrations (user_id, event_id, status, registered_at) VALUES (?, ?, 'registered', NOW())");
            $stmt->execute([$user_id, $event_id]);
            $is_registered = true;
            $success = "You have successfully registered for this event.";
        } catch (Exception $e) {
            $error = "Registration failed. Please try again.";
        }
    }
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events' => 0,
    'sermons_available' => 0,
    'prayer_requests' => 0,
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
    <title><?php echo htmlspecialchars($event['title']); ?> - <?php echo SITE_NAME; ?></title>
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
        .event-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
        }
        .event-detail-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .info-item {
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-weight: 600;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Event Hero -->
                <div class="event-hero">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                            <p class="lead mb-0">
                                <i class="fas fa-calendar-alt me-2"></i><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <?php if ($event['registration_status'] == 'available' && !$is_registered): ?>
                                <form method="POST">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="register" class="btn btn-light btn-lg">Register Now</button>
                                </form>
                            <?php elseif ($is_registered): ?>
                                <span class="btn btn-success btn-lg disabled"><i class="fas fa-check me-2"></i>Registered</span>
                            <?php elseif ($event['registration_status'] == 'full'): ?>
                                <span class="btn btn-danger btn-lg disabled">Registration Full</span>
                            <?php else: ?>
                                <span class="btn btn-secondary btn-lg disabled">Registration Closed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Event Details -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="event-detail-card mb-4">
                            <h4>About This Event</h4>
                            <p><?php echo nl2br(htmlspecialchars($event['description'] ?? 'No description available.')); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="event-detail-card">
                            <h4>Event Information</h4>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-clock me-2"></i> Date & Time</div>
                                <p><?php echo date('F j, Y', strtotime($event['event_date'])); ?><br>
                                <?php 
                                if (!empty($event['start_time'])) {
                                    echo date('g:i A', strtotime($event['start_time']));
                                    if (!empty($event['end_time'])) {
                                        echo ' - ' . date('g:i A', strtotime($event['end_time']));
                                    }
                                } else {
                                    echo 'Time not specified';
                                }
                                ?></p>
                            </div>
                            <?php if (!empty($event['location'])): ?>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-map-marker-alt me-2"></i> Location</div>
                                <p><?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($event['organizer'])): ?>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-user-tie me-2"></i> Organizer</div>
                                <p><?php echo htmlspecialchars($event['organizer']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($event['capacity'])): ?>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-users me-2"></i> Capacity</div>
                                <p><?php echo (int)$event['registered_count'] . ' / ' . (int)$event['capacity']; ?></p>
                            </div>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline-primary w-100 mt-3"><i class="fas fa-arrow-left me-2"></i> Back to Events</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>