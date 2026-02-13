<?php
// pastor/includes/pastor_topbar.php

// Get database connection from global scope
global $conn;

// Get user ID from session safely
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Handle case where user is not logged in
    header('Location: ../auth/login.php');
    exit;
}

// Initialize user data with default
$user = ['full_name' => 'Pastor'];

// Get user profile
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?? $user;
    } catch (Exception $e) {
        error_log("Error getting user profile: " . $e->getMessage());
    }
}

// Get unread notifications count
$unread = 0;
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $unread = $result['unread_count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
    }
}

// Get notifications for display
$notifications = [];
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
    }
}
?>
<div class="pastor-topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">
            <h3>Dashboard</h3>
            <span>Welcome, Pastor <?php echo htmlspecialchars($user['full_name'] ?? 'Pastor'); ?></span>
        </div>
    </div>
    
    <div class="topbar-right">
        <div class="topbar-actions">
            <button class="action-btn" onclick="openModal('announcementModal')">
                <i class="fas fa-bullhorn"></i>
            </button>
            
            <button class="action-btn" onclick="openModal('eventModal')">
                <i class="fas fa-calendar-plus"></i>
            </button>
            
            <div class="notifications-dropdown">
                <button class="action-btn notification-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread > 0): ?>
                    <span class="notification-badge"><?php echo $unread; ?></span>
                    <?php endif; ?>
                </button>
                <div class="notifications-panel">
                    <div class="notifications-header">
                        <h4>Notifications</h4>
                        <?php if ($unread > 0): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_all_notifications_read">
                            <button type="submit" class="btn btn-sm">Mark All Read</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="notifications-list">
                        <?php if (!empty($notifications)): 
                            foreach ($notifications as $notif): ?>
                            <div class="notification-item <?php echo ($notif['is_read'] ?? 0) ? '' : 'unread'; ?>">
                                <div class="notification-icon">
                                    <i class="fas fa-<?php 
                                        $type = $notif['type'] ?? 'system';
                                        switch($type) {
                                            case 'event': echo 'calendar-alt'; break;
                                            case 'prayer': echo 'praying-hands'; break;
                                            case 'sermon': echo 'video'; break;
                                            default: echo 'info-circle';
                                        }
                                    ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h5><?php echo htmlspecialchars($notif['title'] ?? 'Notification'); ?></h5>
                                    <p><?php echo htmlspecialchars(truncateText($notif['message'] ?? '', 60)); ?></p>
                                    <span class="notification-time">
                                        <?php 
                                        // Use formatRelativeDate if it exists, otherwise show raw date
                                        if (function_exists('formatRelativeDate')) {
                                            echo formatRelativeDate($notif['created_at'] ?? date('Y-m-d H:i:s'));
                                        } else {
                                            echo date('M j, Y', strtotime($notif['created_at'] ?? date('Y-m-d H:i:s')));
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; 
                        else: ?>
                            <div class="empty-notifications">
                                <i class="fas fa-bell-slash"></i>
                                <p>No notifications</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="notifications-footer">
                        <a href="#" onclick="viewAllNotifications()">View All Notifications</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="user-profile-dropdown">
            <button class="user-profile-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <?php 
                    $initials = 'P';
                    if (isset($user['full_name'])) {
                        $names = explode(' ', $user['full_name']);
                        $initials = strtoupper(substr($names[0], 0, 1));
                        if (count($names) > 1) {
                            $initials .= strtoupper(substr($names[1], 0, 1));
                        }
                    }
                    echo $initials;
                    ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? 'Pastor'); ?></span>
                    <span class="user-role">Pastor</span>
                </div>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-dropdown-menu">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../index.php" class="dropdown-item" target="_blank">
                    <i class="fas fa-globe"></i>
                    <span>Visit Website</span>
                </a>
                <a href="../auth/logout.php" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.pastor-topbar {
    position: sticky;
    top: 0;
    left: 250px;
    right: 0;
    background: white;
    padding: 15px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 999;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-radius: 0 0 12px 12px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sidebar-toggle {
    display: none;
    background: var(--primary);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.2rem;
}

.page-title h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0;
}

.page-title span {
    font-size: 0.85rem;
    color: var(--text-light);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid var(--border-color);
    color: var(--dark);
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    position: relative;
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.notification-btn {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--secondary);
    color: white;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.notifications-dropdown {
    position: relative;
}

.notifications-panel {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: none;
    margin-top: 10px;
    z-index: 1001;
}

.notifications-dropdown:hover .notifications-panel {
    display: block;
}

.notifications-header {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notifications-header h4 {
    margin: 0;
    font-size: 1rem;
    color: var(--dark);
}

.notifications-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #e6f0ff;
}

.notification-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.notification-content h5 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: var(--dark);
}

.notification-content p {
    margin: 0 0 5px 0;
    font-size: 0.85rem;
    color: var(--text);
}

.notification-time {
    font-size: 0.75rem;
    color: var(--text-light);
}

.empty-notifications {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-light);
}

.empty-notifications i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #e0e0e0;
}

.notifications-footer {
    padding: 15px;
    text-align: center;
    border-top: 1px solid var(--border-color);
}

.notifications-footer a {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
}

.notifications-footer a:hover {
    text-decoration: underline;
}

.user-profile-dropdown {
    position: relative;
}

.user-profile-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.user-profile-btn:hover {
    background: #f8f9fa;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.user-info {
    text-align: left;
}

.user-name {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--dark);
}

.user-role {
    display: block;
    font-size: 0.8rem;
    color: var(--text-light);
}

.user-profile-btn i {
    font-size: 0.9rem;
    color: var(--text-light);
}

.user-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 200px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: none;
    margin-top: 10px;
    z-index: 1001;
}

.user-profile-dropdown:hover .user-dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: var(--dark);
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: var(--primary);
}

.dropdown-item i {
    width: 20px;
    color: var(--text-light);
}

.dropdown-divider {
    height: 1px;
    background: var(--border-color);
    margin: 5px 0;
}

.dropdown-item.logout {
    color: #ff6b6b;
}

.dropdown-item.logout:hover {
    background: #ffebee;
}

/* Mobile responsive */
@media (max-width: 1024px) {
    .pastor-topbar {
        left: 0;
        padding: 12px 15px;
    }
    
    .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-title span {
        display: none;
    }
    
    .notifications-panel {
        width: 300px;
        right: -100px;
    }
    
    .user-info {
        display: none;
    }
    
    .user-profile-btn i {
        display: none;
    }
}
</style>

<script>
function toggleNotifications() {
    const panel = document.querySelector('.notifications-panel');
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
}

function toggleUserMenu() {
    const menu = document.querySelector('.user-dropdown-menu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function viewAllNotifications() {
    window.location.href = '#';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notifications-dropdown')) {
        document.querySelector('.notifications-panel').style.display = 'none';
    }
    
    if (!e.target.closest('.user-profile-dropdown')) {
        document.querySelector('.user-dropdown-menu').style.display = 'none';
    }
});

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.pastor-sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('sidebar-active');
}
</script>