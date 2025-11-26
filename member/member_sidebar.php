<?php
// member/member_sidebar.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="church-logo">
        <i class="fas fa-church fa-2x"></i>
        <div class="church-name"><?php echo SITE_NAME; ?></div>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php" class="menu-link">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
            <a href="events.php" class="menu-link">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Events</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo in_array($current_page, ['prayer-requests.php', 'submit-prayer.php']) ? 'active' : ''; ?>">
            <a href="prayer-requests.php" class="menu-link">
                <i class="fas fa-pray"></i>
                <span class="menu-text">Prayer Requests</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'sermons.php' ? 'active' : ''; ?>">
            <a href="sermons.php" class="menu-link">
                <i class="fas fa-book-bible"></i>
                <span class="menu-text">Sermons</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'ministries.php' ? 'active' : ''; ?>">
            <a href="ministries.php" class="menu-link">
                <i class="fas fa-users"></i>
                <span class="menu-text">Ministries</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo in_array($current_page, ['donations.php', 'make-donation.php', 'donation-history.php']) ? 'active' : ''; ?>">
            <a href="donations.php" class="menu-link">
                <i class="fas fa-hand-holding-heart"></i>
                <span class="menu-text">Donations</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'directory.php' ? 'active' : ''; ?>">
            <a href="directory.php" class="menu-link">
                <i class="fas fa-user-friends"></i>
                <span class="menu-text">Directory</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
            <a href="messages.php" class="menu-link">
                <i class="fas fa-envelope"></i>
                <span class="menu-text">Messages</span>
                <span class="message-badge">3</span>
            </a>
        </div>
        
        <div class="menu-divider"></div>
        
        <div class="menu-item <?php echo in_array($current_page, ['profile.php', 'edit-profile.php']) ? 'active' : ''; ?>">
            <a href="profile.php" class="menu-link">
                <i class="fas fa-user"></i>
                <span class="menu-text">My Profile</span>
            </a>
        </div>
        
        <div class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php" class="menu-link">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="../auth/logout.php" class="menu-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </div>
    </div>
</div>

<style>
.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 25px;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
    width: 100%;
    position: relative;
}

.menu-link:hover {
    background: rgba(255,255,255,0.15);
    border-left: 4px solid white;
}

.menu-item.active .menu-link {
    background: rgba(255,255,255,0.15);
    border-left: 4px solid white;
}

.menu-item i {
    margin-right: 15px;
    width: 24px;
    text-align: center;
}

.menu-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 15px 25px;
}

.message-badge {
    background: var(--accent-green);
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 12px;
    margin-left: auto;
}

.logout-link {
    color: #ff6b6b;
}

.logout-link:hover {
    background: rgba(255,107,107,0.1);
}

/* Responsive sidebar */
@media (max-width: 992px) {
    .sidebar {
        width: 80px;
    }
    .church-name, .menu-text {
        display: none;
    }
    .church-logo {
        justify-content: center;
        padding: 0 10px 20px;
    }
    .menu-item {
        justify-content: center;
    }
    .menu-link {
        justify-content: center;
        padding: 15px;
    }
    .menu-item i {
        margin-right: 0;
    }
    .message-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        margin-left: 0;
    }
}
</style>