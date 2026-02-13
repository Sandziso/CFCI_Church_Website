<?php
$current_page = basename($_SERVER['PHP_SELF']);
$active_class = 'active';
?>

<div class="sidebar">
    <div class="logo text-center">
        <h3 class="mb-0">
            <i class="fas fa-church me-2"></i>
            <span class="d-none d-lg-inline">CFCI Admin</span>
        </h3>
        <small class="text-muted d-none d-lg-block">Dashboard v2.0</small>
    </div>
    
    <nav class="mt-4">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? $active_class : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="user-management/users.php" class="nav-link <?php echo strpos($current_page, 'user') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                    <span class="badge bg-danger notification-badge">3</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="content-management/pages.php" class="nav-link <?php echo strpos($current_page, 'content') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Content</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="events/" class="nav-link <?php echo strpos($current_page, 'event') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                    <span class="badge bg-info notification-badge">5</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="ministries/" class="nav-link <?php echo strpos($current_page, 'ministr') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-hands-helping"></i>
                    <span>Ministries</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="sermons/" class="nav-link <?php echo strpos($current_page, 'sermon') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-bible"></i>
                    <span>Sermons</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="donations/" class="nav-link <?php echo strpos($current_page, 'donation') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-donate"></i>
                    <span>Donations</span>
                    <span class="badge bg-success notification-badge">12</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="prayer-requests/" class="nav-link <?php echo strpos($current_page, 'prayer') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-pray"></i>
                    <span>Prayer Requests</span>
                    <span class="badge bg-warning notification-badge"><?php echo $stats['pending_prayers'] ?? 0; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="announcements/" class="nav-link <?php echo strpos($current_page, 'announcement') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="gallery/" class="nav-link <?php echo strpos($current_page, 'gallery') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-images"></i>
                    <span>Gallery</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <a href="system-settings/general-settings.php" class="nav-link <?php echo strpos($current_page, 'settings') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="backup/database-backup.php" class="nav-link <?php echo strpos($current_page, 'backup') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>Backup</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="security/activity-logs.php" class="nav-link <?php echo strpos($current_page, 'security') !== false ? $active_class : ''; ?>">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <a href="../index.php" class="nav-link text-warning">
                    <i class="fas fa-eye"></i>
                    <span>View Site</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="../logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer mt-auto p-3 border-top border-dark">
        <small class="text-muted">
            <i class="fas fa-clock"></i> Last login: 
            <?php echo date('M d, H:i', strtotime($_SESSION['last_login'] ?? 'now')); ?>
        </small>
    </div>
</div>