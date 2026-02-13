<?php
// ===================================================
// MEMBER DASHBOARD - Christian Family Centre International
// ===================================================

// Start session and check login
require_once '../includes/config.php';
require_once '../includes/main-functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get user ID and role
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$user_name = $_SESSION['full_name'] ?? null;

// If user is admin or pastor, redirect to appropriate dashboard
if (is_admin()) {
    header('Location: ../admin/dashboard.php');
    exit();
} elseif (is_pastor()) {
    header('Location: ../pastor/dashboard.php');
    exit();
}

// Get database connection from auth system
global $auth;
$db = null;

// Try to get database connection
try {
    // Use the Database class directly
    require_once '../includes/database.php';
    $database = Database::getInstance();
    $db = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection error in member dashboard: " . $e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}

// Check if we got a valid database connection
if (!$db) {
    die("Database connection failed. Please check your configuration.");
}

// Load dashboard functions
require_once '../includes/dashboard.php';

// Initialize DashboardStats with the database connection
$dashboardStats = new DashboardStats($db);
$dashboardWidgets = new DashboardWidgets($db);

// Get dashboard data
$member_stats = $dashboardStats->getMemberStats($user_id);
$calendar_events = $dashboardWidgets->getCalendarEvents();
$prayer_wall = $dashboardWidgets->getPrayerWallWidget(5);
$announcements = $dashboardWidgets->getAnnouncementsWidget(5);
$birthdays = $dashboardWidgets->getBirthdaysWidget();

// Handle Quick Prayer Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prayer'])) {
    $prayer_text = $_POST['prayer_text'] ?? '';
    $category = $_POST['category'] ?? 'other';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (!empty($prayer_text)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO prayer_requests (user_id, prayer_text, category, is_anonymous, status, created_at) 
                VALUES (?, ?, ?, ?, 'active', NOW())
            ");
            $result = $stmt->execute([$user_id, $prayer_text, $category, $is_anonymous]);
            
            if ($result) {
                setFlashMessage('Your prayer request has been submitted successfully.', 'success');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = 'Failed to submit prayer request. Please try again.';
            }
        } catch (Exception $e) {
            error_log("Prayer request error: " . $e->getMessage());
            $error = 'Failed to submit prayer request. Please try again.';
        }
    } else {
        $error = 'Please enter your prayer request.';
    }
}

// Handle Quick Event Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $event_id = (int)($_POST['event_id'] ?? 0);
    
    if ($event_id > 0) {
        try {
            // Check if already registered
            $stmt = $db->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            
            if ($stmt->rowCount() == 0) {
                $stmt = $db->prepare("INSERT INTO event_registrations (user_id, event_id, status, registered_at) VALUES (?, ?, 'registered', NOW())");
                $result = $stmt->execute([$user_id, $event_id]);
                
                if ($result) {
                    setFlashMessage('Successfully registered for the event!', 'success');
                } else {
                    setFlashMessage('Failed to register for the event.', 'danger');
                }
            } else {
                setFlashMessage('You are already registered for this event.', 'warning');
            }
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (Exception $e) {
            error_log("Event registration error: " . $e->getMessage());
            setFlashMessage('Failed to register for the event.', 'danger');
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Handle Mark Notification as Read
if (isset($_GET['mark_read'])) {
    $notification_id = (int)$_GET['mark_read'];
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        error_log("Mark notification read error: " . $e->getMessage());
    }
}

// Handle Mark All Notifications as Read
if (isset($_GET['mark_all_read'])) {
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        error_log("Mark all notifications read error: " . $e->getMessage());
    }
}

// Get user profile
$user_profile = [];
try {
    $stmt = $db->prepare("
        SELECT u.*, up.phone, up.address, up.birth_date, up.profile_image, up.bio 
        FROM users u 
        LEFT JOIN user_profiles up ON u.id = up.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user_profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Get user profile error: " . $e->getMessage());
}

// Get recent donations
$recent_donations = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM donations 
        WHERE user_id = ? AND status = 'completed'
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get donations error: " . $e->getMessage());
}

// Calculate profile completion percentage
function calculateProfileCompletion($user_id, $db) {
    try {
        $completion = 0;
        
        // Check basic info
        $stmt = $db->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($user['full_name'])) $completion += 25;
        if (!empty($user['email'])) $completion += 25;
        
        // Check profile info
        $stmt = $db->prepare("SELECT phone, address, birth_date FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($profile['phone'])) $completion += 10;
        if (!empty($profile['address'])) $completion += 20;
        if (!empty($profile['birth_date'])) $completion += 20;
        
        return $completion;
    } catch (Exception $e) {
        error_log("Profile completion calculation error: " . $e->getMessage());
        return 0;
    }
}

$profile_completion = calculateProfileCompletion($user_id, $db);

// Get quick stats from member_stats
$quick_stats = [
    'upcoming_events' => $member_stats['upcoming_events'] ?? 0,
    'sermons_available' => 0, // You'll need to implement this
    'prayer_requests' => $member_stats['my_prayer_requests'] ?? 0,
    'ministries_involved' => $member_stats['ministries'] ?? 0,
];

// Get upcoming events for display
$upcoming_events_display = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM events 
        WHERE event_date >= CURDATE() 
        AND status = 'active'
        ORDER BY event_date ASC 
        LIMIT 5
    ");
    $stmt->execute();
    $upcoming_events_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get upcoming events error: " . $e->getMessage());
}

// Get recent sermons
$recent_sermons = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM sermons 
        WHERE status = 'published'
        ORDER BY sermon_date DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $recent_sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table might not exist yet, that's OK
}

// Include topbar and sidebar
require_once 'includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
            --accent-color: #2ecc71;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 56px; /* Height of top navbar */
        }
        
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            overflow-y: auto;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                top: 56px;
            }
        }
        
        .main-content {
            margin-left: 240px; /* Width of sidebar */
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
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .quick-action-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: var(--primary-color);
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .quick-action-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-title {
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #f0f8ff;
            border-left: 3px solid var(--primary-color);
        }
        
        .event-card {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }
        
        .event-card:hover {
            background-color: #f8f9fa;
        }
        
        .event-date {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-align: center;
            margin-right: 1rem;
            min-width: 80px;
        }
        
        .prayer-form {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        
        .sermon-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }
        
        .sermon-item:hover {
            background-color: #f8f9fa;
        }
        
        .sermon-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        @media (max-width: 768px) {
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-header {
                padding: 1.5rem 0;
            }
            
            .welcome-card {
                padding: 1rem;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include Topbar -->
    <?php require_once 'includes/member_topbar.php'; ?>
    
    <!-- Container with Sidebar -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php require_once 'includes/member_sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Flash Messages -->
                <?php 
                if (function_exists('getFlashMessage')) {
                    echo getFlashMessage();
                }
                ?>
                
                <!-- Error Messages -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="avatar-circle">
                                <?php 
                                if (!empty($user_profile['profile_image'])) {
                                    echo '<img src="' . htmlspecialchars($user_profile['profile_image']) . '" alt="' . htmlspecialchars($user_name) . '" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">';
                                } else {
                                    // Generate initials
                                    $initials = '';
                                    $words = explode(' ', $user_name);
                                    foreach ($words as $word) {
                                        if (!empty($word)) {
                                            $initials .= strtoupper(substr($word, 0, 1));
                                        }
                                    }
                                    echo substr($initials, 0, 2);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                            <p class="lead mb-3">Here's what's happening in your church community</p>
                            
                            <!-- Profile Completion -->
                            <?php if ($profile_completion < 100): ?>
                            <div class="profile-completion">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Profile Completion</small>
                                    <small><?php echo $profile_completion; ?>%</small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $profile_completion; ?>%" 
                                         aria-valuenow="<?php echo $profile_completion; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="d-block mt-1">Complete your profile to unlock more features</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="../profile/edit.php" class="quick-action-card">
                        <i class="fas fa-user-edit"></i>
                        <span>Update Profile</span>
                    </a>
                    <a href="../events/" class="quick-action-card">
                        <i class="fas fa-calendar-check"></i>
                        <span>View Events</span>
                    </a>
                    <a href="../prayer/" class="quick-action-card">
                        <i class="fas fa-praying-hands"></i>
                        <span>Prayer Requests</span>
                    </a>
                    <a href="../finances/donate.php" class="quick-action-card">
                        <i class="fas fa-donate"></i>
                        <span>Make Donation</span>
                    </a>
                    <a href="../ministries/" class="quick-action-card">
                        <i class="fas fa-hands-helping"></i>
                        <span>Ministries</span>
                    </a>
                    <a href="../profile/settings.php" class="quick-action-card">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card">
                            <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                            <div class="stat-number"><?php echo $quick_stats['upcoming_events']; ?></div>
                            <div class="stat-label">Upcoming Events</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card">
                            <i class="fas fa-podcast fa-2x text-primary mb-2"></i>
                            <div class="stat-number"><?php echo $quick_stats['sermons_available']; ?></div>
                            <div class="stat-label">Sermons Available</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card">
                            <i class="fas fa-pray fa-2x text-primary mb-2"></i>
                            <div class="stat-number"><?php echo $quick_stats['prayer_requests']; ?></div>
                            <div class="stat-label">Prayer Requests</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <div class="stat-number"><?php echo $quick_stats['ministries_involved']; ?></div>
                            <div class="stat-label">Ministries Involved</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Upcoming Events -->
                        <div class="dashboard-card">
                            <div class="card-title">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Events</h5>
                                <a href="../events/" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            
                            <?php if (!empty($upcoming_events_display)): ?>
                                <?php foreach ($upcoming_events_display as $event): ?>
                                <div class="event-card">
                                    <div class="d-flex align-items-center">
                                        <div class="event-date">
                                            <div class="fw-bold"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                            <div class="small"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?></h6>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php 
                                                    if (!empty($event['start_time'])) {
                                                        echo date('g:i A', strtotime($event['start_time']));
                                                    }
                                                    if (!empty($event['end_time'])) {
                                                        echo ' - ' . date('g:i A', strtotime($event['end_time']));
                                                    }
                                                    ?>
                                                </small>
                                                <?php if (!empty($event['location'])): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($event['location']); ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($event['description'])): ?>
                                            <p class="small text-muted mb-2">
                                                <?php 
                                                $description = htmlspecialchars($event['description']);
                                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                                ?>
                                            </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($event['registration_status'] == 'available'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" name="register_event" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-user-plus me-1"></i> Register
                                                    </button>
                                                </form>
                                            <?php elseif ($event['registration_status'] == 'full'): ?>
                                                <span class="badge bg-danger">Full</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No upcoming events</p>
                                    <a href="../events/" class="btn btn-primary">Browse Events</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Recent Sermons -->
                        <div class="dashboard-card">
                            <div class="card-title">
                                <h5 class="mb-0"><i class="fas fa-podcast me-2"></i> Recent Sermons</h5>
                                <a href="../sermons/" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            
                            <?php if (!empty($recent_sermons)): ?>
                                <?php foreach ($recent_sermons as $sermon): ?>
                                <div class="sermon-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($sermon['title'] ?? 'Untitled Sermon'); ?></h6>
                                        <small class="sermon-date"><?php echo date('M d, Y', strtotime($sermon['sermon_date'] ?? date('Y-m-d'))); ?></small>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <?php if (!empty($sermon['preacher_name'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($sermon['preacher_name']); ?>
                                        </small>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($sermon['bible_passage'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-bible me-1"></i>
                                            <?php echo htmlspecialchars($sermon['bible_passage']); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($sermon['audio_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($sermon['audio_url']); ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-headphones me-1"></i> Listen
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($sermon['video_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($sermon['video_url']); ?>" 
                                           class="btn btn-sm btn-outline-danger" target="_blank">
                                            <i class="fas fa-video me-1"></i> Watch
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-podcast fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent sermons available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Quick Prayer Request -->
                        <div class="dashboard-card">
                            <div class="card-title">
                                <h5 class="mb-0"><i class="fas fa-pray me-2"></i> Quick Prayer Request</h5>
                            </div>
                            
                            <form method="POST" class="prayer-form">
                                <div class="mb-3">
                                    <label for="prayer_text" class="form-label">Your Prayer Request</label>
                                    <textarea class="form-control" id="prayer_text" name="prayer_text" 
                                              rows="4" placeholder="Share your prayer request..." 
                                              required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="health">Health</option>
                                        <option value="financial">Financial</option>
                                        <option value="family">Family</option>
                                        <option value="spiritual">Spiritual</option>
                                        <option value="work">Work</option>
                                        <option value="other" selected>Other</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_anonymous" name="is_anonymous" value="1">
                                    <label class="form-check-label" for="is_anonymous">Submit anonymously</label>
                                </div>
                                
                                <button type="submit" name="submit_prayer" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Prayer Request
                                </button>
                            </form>
                        </div>

                        <!-- Recent Announcements -->
                        <div class="dashboard-card">
                            <div class="card-title">
                                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i> Announcements</h5>
                            </div>
                            
                            <?php if (!empty($announcements)): ?>
                                <?php foreach ($announcements as $announcement): ?>
                                <div class="notification-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title'] ?? 'Announcement'); ?></h6>
                                    <p class="small text-muted mb-1">
                                        <?php 
                                        $content = htmlspecialchars($announcement['content'] ?? '');
                                        echo strlen($content) > 80 ? substr($content, 0, 80) . '...' : $content;
                                        ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($announcement['publish_date'] ?? date('Y-m-d'))); ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <p class="text-muted">No announcements</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Upcoming Birthdays -->
                        <div class="dashboard-card">
                            <div class="card-title">
                                <h5 class="mb-0"><i class="fas fa-birthday-cake me-2"></i> Upcoming Birthdays</h5>
                            </div>
                            
                            <?php if (!empty($birthdays)): ?>
                                <?php foreach ($birthdays as $birthday): ?>
                                <div class="notification-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($birthday['full_name'] ?? 'Member'); ?></h6>
                                    <p class="small text-muted mb-1">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('F d', strtotime($birthday['birth_date'] ?? date('Y-m-d'))); ?>
                                    </p>
                                    <?php if (isset($birthday['days_until']) && $birthday['days_until'] > 0): ?>
                                    <small class="text-muted">
                                        In <?php echo $birthday['days_until']; ?> day<?php echo $birthday['days_until'] == 1 ? '' : 's'; ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <p class="text-muted">No upcoming birthdays</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>