<?php
// member/dashboard.php

// Start with minimal includes first
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a member
$session->requireLogin();
if ($session->getUserRole() !== 'member') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $session->getUserId();

// Initialize database and get dashboard data
try {
    $db = new ChurchDB($conn);
    $dashboard_data = $db->getMemberDashboardData($user_id);
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $dashboard_data = [];
    $session->setFlash('error', 'Unable to load dashboard data. Please try again.');
}

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'submit_prayer':
            $prayer_text = $_POST['prayer_text'] ?? '';
            if (!empty($prayer_text)) {
                $result = $db->submitQuickPrayer($user_id, $prayer_text);
                if ($result) {
                    $session->setFlash('success', 'Prayer request submitted successfully');
                } else {
                    $session->setFlash('error', 'Failed to submit prayer request');
                }
            }
            break;
            
        case 'register_event':
            $event_id = $_POST['event_id'] ?? '';
            if (!empty($event_id)) {
                $result = $db->quickEventRegistration($event_id, $user_id);
                $session->setFlash($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;
            
        case 'mark_notification_read':
            $notification_id = $_POST['notification_id'] ?? '';
            if (!empty($notification_id)) {
                $db->markNotificationAsRead($notification_id, $user_id);
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
    <title>Member Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2c7be5;
            --accent-blue: #1c65c9;
            --light-blue: #e6f0ff;
            --accent-green: #00d97e;
            --light-green: #e6fff2;
            --accent-orange: #f6c343;
            --light-orange: #fff9e6;
            --accent-purple: #9b59b6;
            --light-purple: #f5eef8;
            --dark-text: #2d3748;
            --light-text: #718096;
            --light-gray: #f8f9fa;
            --border-color: #e2e8f0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Flash Messages */
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
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
            background: var(--light-green);
            color: #2d5016;
            border-left: 4px solid var(--accent-green);
        }

        .flash-error {
            background: #ffe6e6;
            color: #cc0000;
            border-left: 4px solid #ff4444;
        }

        .flash-info {
            background: var(--light-blue);
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
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

        /* Main Content Layout */
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Dashboard Header */
        .dashboard-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-section h1 {
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .date-display {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .date-display .day {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .date-display .date {
            font-size: 1rem;
            color: var(--light-text);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid var(--primary-blue);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.events { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.sermons { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.prayers { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.ministries { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            line-height: 1;
        }

        .stat-info p {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.4rem;
            color: var(--dark-text);
            font-weight: 600;
        }

        .card-header .actions {
            display: flex;
            gap: 10px;
        }

        .card-body {
            padding: 25px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            background: white;
            border: 2px dashed var(--border-color);
            border-radius: 12px;
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
            border-color: var(--primary-blue);
            background: var(--light-blue);
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            font-size: 1.8rem;
            color: var(--primary-blue);
        }

        .quick-action-btn span {
            font-weight: 500;
            color: var(--dark-text);
        }

        /* Events List */
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .event-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .event-item:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow);
        }

        .event-date {
            text-align: center;
            min-width: 60px;
        }

        .event-day {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            line-height: 1;
        }

        .event-month {
            font-size: 0.8rem;
            color: var(--light-text);
            text-transform: uppercase;
        }

        .event-info {
            flex: 1;
        }

        .event-info h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark-text);
        }

        .event-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .event-meta i {
            margin-right: 5px;
        }

        .event-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-registered { background: var(--light-green); color: #2d5016; }
        .status-available { background: var(--light-blue); color: var(--primary-blue); }
        .status-full { background: #ffe6e6; color: #cc0000; }

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
            border-radius: 12px;
            background: var(--light-gray);
            transition: all 0.3s ease;
        }

        .notification-item.unread {
            background: var(--light-blue);
            border-left: 3px solid var(--primary-blue);
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
            flex-shrink: 0;
        }

        .notification-icon.system { background: var(--primary-blue); }
        .notification-icon.event { background: var(--accent-green); }
        .notification-icon.prayer { background: var(--accent-purple); }
        .notification-icon.sermon { background: var(--accent-orange); }

        .notification-content {
            flex: 1;
        }

        .notification-content h4 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--dark-text);
        }

        .notification-content p {
            font-size: 0.9rem;
            color: var(--light-text);
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.8rem;
            color: var(--light-text);
            margin-top: 5px;
        }

        .notification-actions {
            display: flex;
            gap: 5px;
        }

        .mark-read-btn {
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .mark-read-btn:hover {
            background: var(--primary-blue);
            color: white;
        }

        /* Sermons Grid */
        .sermons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .sermon-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sermon-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .sermon-image {
            height: 160px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .sermon-content {
            padding: 20px;
        }

        .sermon-content h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--dark-text);
            line-height: 1.3;
        }

        .sermon-meta {
            display: flex;
            justify-content: between;
            font-size: 0.85rem;
            color: var(--light-text);
            margin-bottom: 15px;
        }

        .sermon-actions {
            display: flex;
            gap: 10px;
        }

        .sermon-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .sermon-btn.primary {
            background: var(--primary-blue);
            color: white;
        }

        .sermon-btn.secondary {
            background: var(--light-gray);
            color: var(--dark-text);
        }

        .sermon-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Prayer Requests */
        .prayer-requests {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .prayer-item {
            padding: 15px;
            border-radius: 12px;
            background: var(--light-gray);
            border-left: 4px solid var(--accent-purple);
        }

        .prayer-text {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .prayer-meta {
            display: flex;
            justify-content: between;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .prayer-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-addressed { background: var(--light-green); color: #2d5016; }
        .status-closed { background: #e2e3e5; color: #383d41; }

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
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            color: var(--dark-text);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--light-text);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-text);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-block {
            display: block;
            width: 100%;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .event-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .event-date {
                align-self: flex-start;
            }
            
            .sermons-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for new content */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php 
    $sidebar_file = '../includes/member_sidebar.php';
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    } else {
        echo "<!-- Sidebar file not found -->";
    }
    ?>
    
    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php 
        // Get flash messages from session
        $flash_messages = isset($_SESSION['flash_messages']) ? $_SESSION['flash_messages'] : [];
        foreach ($flash_messages as $key => $flash): 
        ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php 
        endforeach; 
        // Clear flash messages after displaying
        unset($_SESSION['flash_messages']);
        ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($dashboard_data['user']['full_name'] ?? 'Member'); ?>!</h1>
                <p>Here's what's happening at CFCI today</p>
            </div>
            <div class="date-display">
                <div class="day"><?php echo date('l'); ?></div>
                <div class="date"><?php echo date('F j, Y'); ?></div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon events">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['upcoming_events'] ?? 0; ?></h3>
                    <p>Upcoming Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sermons">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['sermons_available'] ?? 0; ?></h3>
                    <p>Sermons Available</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon prayers">
                    <i class="fas fa-pray"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['prayer_requests'] ?? 0; ?></h3>
                    <p>Prayer Requests</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon ministries">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $dashboard_data['stats']['ministries_involved'] ?? 0; ?></h3>
                    <p>Ministries Involved</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-action-btn" onclick="openPrayerModal()">
                <i class="fas fa-praying-hands"></i>
                <span>Submit Prayer</span>
            </div>
            <div class="quick-action-btn" onclick="window.location.href='events.php'">
                <i class="fas fa-calendar-plus"></i>
                <span>Register Event</span>
            </div>
            <div class="quick-action-btn" onclick="window.location.href='donations.php'">
                <i class="fas fa-donate"></i>
                <span>Make Donation</span>
            </div>
            <div class="quick-action-btn" onclick="window.location.href='ministries.php'">
                <i class="fas fa-hands-helping"></i>
                <span>Join Ministry</span>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Upcoming Events -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-calendar-week"></i> Upcoming Events</h2>
                        <div class="actions">
                            <a href="events.php" class="btn btn-secondary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="events-list">
                            <?php if (!empty($dashboard_data['upcoming_events'])): ?>
                                <?php foreach ($dashboard_data['upcoming_events'] as $event): ?>
                                    <div class="event-item animate-in">
                                        <div class="event-date">
                                            <div class="event-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                            <div class="event-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                        </div>
                                        <div class="event-info">
                                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                            <div class="event-meta">
                                                <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                            </div>
                                        </div>
                                        <div class="event-actions">
                                            <?php if ($event['attendance_status'] === 'registered'): ?>
                                                <span class="event-status status-registered">Registered</span>
                                            <?php elseif ($event['registration_status'] === 'available'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="register_event">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" class="btn btn-primary">Register</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="event-status status-full">Full</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No upcoming events</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Sermons -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-video"></i> Recent Sermons</h2>
                        <div class="actions">
                            <a href="sermons.php" class="btn btn-secondary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="sermons-grid">
                            <?php if (!empty($dashboard_data['recent_sermons'])): ?>
                                <?php foreach ($dashboard_data['recent_sermons'] as $sermon): ?>
                                    <div class="sermon-card animate-in">
                                        <div class="sermon-image">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <div class="sermon-content">
                                            <h4><?php echo htmlspecialchars($sermon['title']); ?></h4>
                                            <div class="sermon-meta">
                                                <span><?php echo date('M j, Y', strtotime($sermon['sermon_date'])); ?></span>
                                                <span><?php echo htmlspecialchars($sermon['preacher_name'] ?? 'Pastor'); ?></span>
                                            </div>
                                            <div class="sermon-actions">
                                                <?php if ($sermon['audio_url']): ?>
                                                    <button class="sermon-btn primary" onclick="playSermon('<?php echo $sermon['audio_url']; ?>', 'audio')">
                                                        <i class="fas fa-play"></i> Listen
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($sermon['video_url']): ?>
                                                    <button class="sermon-btn secondary" onclick="playSermon('<?php echo $sermon['video_url']; ?>', 'video')">
                                                        <i class="fas fa-play-circle"></i> Watch
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent sermons available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-bell"></i> Notifications</h2>
                        <div class="actions">
                            <?php if (!empty($dashboard_data['notifications'])): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="mark_all_notifications_read">
                                    <button type="submit" class="btn btn-secondary">Mark All Read</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="notifications-list">
                            <?php if (!empty($dashboard_data['notifications'])): ?>
                                <?php foreach ($dashboard_data['notifications'] as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?> animate-in">
                                        <div class="notification-icon <?php echo $notification['type']; ?>">
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
                                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <div class="notification-time">
                                                <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                                            </div>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <div class="notification-actions">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="mark_notification_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" class="mark-read-btn" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No notifications</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Prayer Requests -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-praying-hands"></i> My Prayer Requests</h2>
                        <div class="actions">
                            <a href="prayer-requests.php" class="btn btn-secondary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="prayer-requests">
                            <?php if (!empty($dashboard_data['recent_prayers'])): ?>
                                <?php foreach ($dashboard_data['recent_prayers'] as $prayer): ?>
                                    <div class="prayer-item animate-in">
                                        <div class="prayer-text">
                                            <?php echo htmlspecialchars($prayer['request_text']); ?>
                                        </div>
                                        <div class="prayer-meta">
                                            <span><?php echo date('M j, Y', strtotime($prayer['submitted_at'])); ?></span>
                                            <span class="prayer-status status-<?php echo $prayer['status']; ?>">
                                                <?php echo ucfirst($prayer['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent prayer requests</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prayer Request Modal -->
    <div class="modal" id="prayerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Prayer Request</h3>
                <button class="close-modal" onclick="closePrayerModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="prayerForm">
                    <input type="hidden" name="action" value="submit_prayer">
                    <div class="form-group">
                        <label for="prayer_text">Your Prayer Request</label>
                        <textarea 
                            name="prayer_text" 
                            id="prayer_text" 
                            class="form-control" 
                            placeholder="Share your prayer request here..." 
                            required
                        ></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Prayer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Functions
        function openPrayerModal() {
            document.getElementById('prayerModal').classList.add('active');
        }

        function closePrayerModal() {
            document.getElementById('prayerModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('prayerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePrayerModal();
            }
        });

        // Sermon playback function
        function playSermon(url, type) {
            if (type === 'audio') {
                // Create audio element and play
                const audio = new Audio(url);
                audio.play().catch(e => {
                    alert('Unable to play audio. Please try again.');
                });
            } else if (type === 'video') {
                // Open video in new tab or embed player
                window.open(url, '_blank');
            }
        }

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                msg.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);

        // Add animation to elements when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        });

        // Observe all cards for animation
        document.querySelectorAll('.stat-card, .event-item, .sermon-card, .notification-item, .prayer-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>