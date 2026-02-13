<?php
// pastor/dashboard.php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/session.php';
require_once '../includes/main-functions.php';


// Add these at the top of dashboard.php after includes

/**
 * Format relative date (e.g., "2 hours ago")
 */


/**
 * Truncate text with ellipsis
 */
function truncateText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>

// Check if user is logged in and is a pastor
$session->requireLogin();
if ($session->getUserRole() !== 'pastor') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $session->getUserId();
$db = new ChurchDB($database);

// Get dashboard data
$dashboard_data = [
    'user' => $db->getUserProfile($user_id),

    'upcoming_events' => $db->getUpcomingEvents(5)
];

// Handle pastor quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_announcement':
            $title = sanitize($_POST['title'] ?? '');
            $content = sanitize($_POST['content'] ?? '');
            $expires_at = $_POST['expires_at'] ?? null;
            
            if (!empty($title) && !empty($content)) {
                $result = $db->createAnnouncement($title, $content, $user_id, $expires_at);
                if ($result) {
                    $session->setFlash('success', 'Announcement added successfully');
                } else {
                    $session->setFlash('error', 'Failed to add announcement');
                }
            }
            break;
            
        case 'add_event':
            $event_title = sanitize($_POST['event_title'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $location = sanitize($_POST['location'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            
            if (!empty($event_title) && !empty($event_date)) {
                $result = $db->createEvent($event_title, $event_date, $start_time, $end_time, $location, $description);
                if ($result) {
                    $session->setFlash('success', 'Event added successfully');
                } else {
                    $session->setFlash('error', 'Failed to add event');
                }
            }
            break;
            
        case 'add_sermon':
            $sermon_title = sanitize($_POST['sermon_title'] ?? '');
            $sermon_date = $_POST['sermon_date'] ?? '';
            $audio_url = filter_var($_POST['audio_url'] ?? '', FILTER_VALIDATE_URL);
            $video_url = filter_var($_POST['video_url'] ?? '', FILTER_VALIDATE_URL);
            $notes_text = sanitize($_POST['notes_text'] ?? '');
            
            if (!empty($sermon_title) && !empty($sermon_date)) {
                $result = $db->addSermon($sermon_title, $user_id, $sermon_date, $audio_url, $video_url, $notes_text);
                if ($result) {
                    $session->setFlash('success', 'Sermon added successfully');
                } else {
                    $session->setFlash('error', 'Failed to add sermon');
                }
            }
            break;
            
        case 'update_prayer_status':
            $prayer_id = (int)$_POST['prayer_id'] ?? 0;
            $status = sanitize($_POST['status'] ?? '');
            
            if ($prayer_id > 0 && !empty($status)) {
                $result = $db->updatePrayerRequestStatus($prayer_id, $status, $user_id);
                if ($result) {
                    $session->setFlash('success', 'Prayer request status updated');
                } else {
                    $session->setFlash('error', 'Failed to update prayer request');
                }
            }
            break;
            
        case 'mark_all_notifications_read':
            $db->markAllNotificationsAsRead($user_id);
            $session->setFlash('success', 'All notifications marked as read');
            break;
    }
    
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastor Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #e67e22;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --text: #333;
            --text-light: #777;
            --border-color: #e2e8f0;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
            --shadow-lg: 0 15px 30px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
        }

        /* Main Content Layout */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        /* Dashboard Header */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-section h1 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .date-display {
            background: var(--primary);
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }

        .date-display .day {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .date-display .date {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Stats Grid - Matching index.php theme */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            background: var(--primary);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .stat-info p {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* Quick Actions - Matching index.php buttons */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            background: white;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .quick-action-btn:hover {
            border-color: var(--primary);
            background: #f0f7ff;
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .quick-action-btn span {
            font-weight: 500;
            color: var(--dark);
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards - Matching index.php cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 20px;
        }

        /* Buttons - Matching index.php buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #154360;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #d35400;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Prayer Requests */
        .prayer-requests-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .prayer-request-item {
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            border-left: 4px solid var(--secondary);
            transition: all 0.3s ease;
        }

        .prayer-request-item:hover {
            background: white;
            box-shadow: var(--shadow);
        }

        .prayer-text {
            margin-bottom: 10px;
            line-height: 1.5;
            color: var(--text);
        }

        .prayer-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .prayer-requester {
            font-weight: 500;
            color: var(--primary);
        }

        /* Recent Members */
        .members-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .member-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .member-item:hover {
            background: #f8f9fa;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .member-info h4 {
            font-size: 0.95rem;
            margin-bottom: 2px;
            color: var(--dark);
        }

        .member-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Notifications */
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification-item {
            display: flex;
            align-items: start;
            gap: 12px;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .notification-item.unread {
            background: #e6f0ff;
            border-left: 3px solid var(--primary);
        }

        .notification-item:hover {
            background: white;
            box-shadow: var(--shadow);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            background: var(--primary);
        }

        .notification-content h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .notification-content p {
            font-size: 0.85rem;
            color: var(--text);
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 5px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }

        /* Flash Messages */
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            max-width: 400px;
        }

        .flash-message {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            animation: slideInRight 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #e0e0e0;
        }

        .empty-state p {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/pastor_sidebar.php'; ?>
    
    <!-- Include Topbar -->
    <?php include 'includes/pastor_topbar.php'; ?>
    
    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php 
        $flash_messages = $session->getFlashMessages();
        foreach ($flash_messages as $flash): 
        ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome, Pastor <?php echo htmlspecialchars($dashboard_data['user']['full_name'] ?? 'Pastor'); ?>!</h1>
                <p>Church Management Dashboard</p>
            </div>
            <div class="date-display">
                <div class="day"><?php echo date('l'); ?></div>
                <div class="date"><?php echo date('F j, Y'); ?></div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card" onclick="window.location.href='members/'">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['total_members'] ?? 0; ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
            <div class="stat-card" onclick="window.location.href='prayer-management/'">
                <div class="stat-icon">
                    <i class="fas fa-pray"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['pending_prayers'] ?? 0; ?></h3>
                    <p>Pending Prayers</p>
                </div>
            </div>
            <div class="stat-card" onclick="window.location.href='finances/'">
                <div class="stat-icon">
                    <i class="fas fa-donate"></i>
                </div>
                <div class="stat-info">
                    <h3>SZL <?php echo number_format($dashboard_data['stats']['monthly_donations'] ?? 0, 2); ?></h3>
                    <p>Monthly Donations</p>
                </div>
            </div>
            <div class="stat-card" onclick="window.location.href='event-management/'">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['upcoming_events'] ?? 0; ?></h3>
                    <p>Upcoming Events</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-action-btn" onclick="openModal('announcementModal')">
                <i class="fas fa-bullhorn"></i>
                <span>Add Announcement</span>
            </div>
            <div class="quick-action-btn" onclick="openModal('eventModal')">
                <i class="fas fa-calendar-plus"></i>
                <span>Add Event</span>
            </div>
            <div class="quick-action-btn" onclick="openModal('sermonModal')">
                <i class="fas fa-video"></i>
                <span>Add Sermon</span>
            </div>
            <div class="quick-action-btn" onclick="window.location.href='reports/'">
                <i class="fas fa-chart-bar"></i>
                <span>View Reports</span>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Prayer Requests -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-praying-hands"></i> Recent Prayer Requests</h2>
                        <a href="prayer-management/" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['recent_prayers'])): ?>
                            <div class="prayer-requests-list">
                                <?php foreach ($dashboard_data['recent_prayers'] as $prayer): ?>
                                    <div class="prayer-request-item">
                                        <div class="prayer-text">
                                            <?php echo htmlspecialchars(truncateText($prayer['request_text'], 100)); ?>
                                        </div>
                                        <div class="prayer-meta">
                                            <div>
                                                <span class="prayer-requester"><?php echo htmlspecialchars($prayer['member_name']); ?></span>
                                                <span> • <?php echo formatRelativeDate($prayer['submitted_at']); ?></span>
                                            </div>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_prayer_status">
                                                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                                                <select name="status" class="form-control btn-sm" onchange="this.form.submit()" style="width: auto; padding: 4px 8px; font-size: 0.8rem;">
                                                    <option value="pending" <?php echo $prayer['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="addressed" <?php echo $prayer['status'] == 'addressed' ? 'selected' : ''; ?>>Addressed</option>
                                                    <option value="closed" <?php echo $prayer['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-pray"></i>
                                <p>No prayer requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Donations -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-donate"></i> Recent Donations</h2>
                        <a href="finances/" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['recent_donations'])): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f8f9fa;">
                                            <th style="padding: 10px; text-align: left;">Member</th>
                                            <th style="padding: 10px; text-align: left;">Amount</th>
                                            <th style="padding: 10px; text-align: left;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dashboard_data['recent_donations'] as $donation): ?>
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 10px;"><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                                <td style="padding: 10px; font-weight: 600; color: #28a745;">SZL <?php echo number_format($donation['amount'], 2); ?></td>
                                                <td style="padding: 10px;"><?php echo date('M j, Y', strtotime($donation['donation_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-donate"></i>
                                <p>No recent donations</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Recent Members -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-user-plus"></i> Recent Members</h2>
                        <a href="members/" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['recent_members'])): ?>
                            <div class="members-list">
                                <?php foreach ($dashboard_data['recent_members'] as $member): ?>
                                    <div class="member-item">
                                        <div class="member-avatar">
                                            <?php echo substr($member['full_name'], 0, 1); ?>
                                        </div>
                                        <div class="member-info">
                                            <h4><?php echo htmlspecialchars($member['full_name']); ?></h4>
                                            <p>Joined <?php echo date('M j, Y', strtotime($member['join_date'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No recent members</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-bell"></i> Notifications</h2>
                        <?php if (!empty($dashboard_data['notifications'])): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="mark_all_notifications_read">
                                <button type="submit" class="btn btn-secondary btn-sm">Mark All Read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="notifications-list">
                            <?php if (!empty($dashboard_data['notifications'])): ?>
                                <?php foreach ($dashboard_data['notifications'] as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                        <div class="notification-icon">
                                            <i class="fas fa-<?php 
                                                switch($notification['type']) {
                                                    case 'event': echo 'calendar-alt'; break;
                                                    case 'prayer': echo 'praying-hands'; break;
                                                    case 'sermon': echo 'video'; break;
                                                    default: echo 'info-circle';
                                                }
                                            ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                            <p><?php echo htmlspecialchars(truncateText($notification['message'], 50)); ?></p>
                                            <div class="notification-time">
                                                <?php echo formatRelativeDate($notification['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'includes/modals.php'; ?>

    <script>
        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Set default dates for forms
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 7);
            const nextWeek = tomorrow.toISOString().split('T')[0];
            
            // Set default dates
            document.getElementById('event_date')?.value = nextWeek;
            document.getElementById('sermon_date')?.value = today;
            
            // Set default time
            document.getElementById('start_time')?.value = '09:00';
            document.getElementById('end_time')?.value = '12:00';
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                msg.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.pastor-sidebar');
            const mainContent = document.querySelector('.main-content');
            
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
        }
    </script>
</body>
</html>