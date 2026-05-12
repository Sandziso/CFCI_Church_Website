<?php
// ===================================================
// MEMBER - Event Registration Confirmation
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

// Fetch event
$stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "<script>alert('Event not found.'); window.location.href='index.php';</script>";
    exit();
}

// Check registration status
$reg_stmt = $db->prepare("SELECT id, status FROM event_registrations WHERE user_id = ? AND event_id = ?");
$reg_stmt->execute([$user_id, $event_id]);
$registration = $reg_stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if (!$registration && $event['registration_status'] == 'available') {
        try {
            $stmt = $db->prepare("INSERT INTO event_registrations (user_id, event_id, status, registered_at) VALUES (?, ?, 'registered', NOW())");
            $stmt->execute([$user_id, $event_id]);
            $registration = ['status' => 'registered'];
            $success = "Thank you! You are registered for this event.";
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
    <title>Register for <?php echo htmlspecialchars($event['title']); ?> - <?php echo SITE_NAME; ?></title>
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
        .registration-card {
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
                <div class="registration-card text-center">
                    <?php if (isset($success)): ?>
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h2>Registration Successful!</h2>
                        <p class="lead">You're all set for <strong><?php echo htmlspecialchars($event['title']); ?></strong>.</p>
                        <p class="text-muted"><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?> at <?php echo !empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD'; ?></p>
                        <a href="details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary mt-3">View Event Details</a>
                        <a href="index.php" class="btn btn-outline-primary mt-3">Back to Events</a>
                    <?php elseif ($registration && $registration['status'] == 'registered'): ?>
                        <i class="fas fa-info-circle fa-4x text-info mb-3"></i>
                        <h2>Already Registered</h2>
                        <p>You've already signed up for this event.</p>
                        <a href="details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary mt-3">View Details</a>
                    <?php elseif ($event['registration_status'] != 'available'): ?>
                        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                        <h2>Registration Unavailable</h2>
                        <p>Registration for this event is currently closed or full.</p>
                        <a href="index.php" class="btn btn-primary mt-3">Browse Other Events</a>
                    <?php else: ?>
                        <i class="fas fa-calendar-check fa-4x text-primary mb-3"></i>
                        <h2>Register for Event</h2>
                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                        <p class="text-muted"><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></p>
                        <?php if (!empty($event['location'])): ?>
                            <p><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                        <?php endif; ?>
                        <p>You are about to register as <strong><?php echo htmlspecialchars($user_name); ?></strong>.</p>
                        <form method="POST">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="confirm" class="btn btn-success btn-lg mt-2">Confirm Registration</button>
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