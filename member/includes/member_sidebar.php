<?php
// Member Sidebar Navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <!-- User Profile Summary -->
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center">
                <?php if (!empty($user_profile['avatar_url']) && $user_profile['avatar_url'] != '/assets/images/default-avatar.png'): ?>
                <img src="<?php echo htmlspecialchars($user_profile['avatar_url']); ?>" 
                     alt="<?php echo htmlspecialchars($user_name); ?>" 
                     class="rounded-circle me-3" style="width: 48px; height: 48px; object-fit: cover;">
                <?php else: ?>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                     style="width: 48px; height: 48px; font-weight: bold; font-size: 1.2rem;">
                    <?php echo getUserInitials($user_name); ?>
                </div>
                <?php endif; ?>
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($user_name); ?></h6>
                    <small class="text-muted"><?php echo ucfirst($user_role); ?> Member</small>
                </div>
            </div>
            
            <?php if ($profile_completion < 100): ?>
            <div class="mt-3">
                <small>Profile: <?php echo $profile_completion; ?>% complete</small>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success" style="width: <?php echo $profile_completion; ?>%"></div>
                </div>
                <a href="../profile/edit.php" class="small text-decoration-none">Complete Profile</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-home me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <!-- Profile Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (in_array($current_page, ['index.php', 'edit.php', 'settings.php'])) ? 'active' : ''; ?>"
                   href="../profile/">
                    <i class="fas fa-user me-2"></i>
                    My Profile
                </a>
            </li>
            
            <!-- Events Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/events/') !== false) ? 'active' : ''; ?>"
                   href="../events/">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Events
                    <?php if ($quick_stats['upcoming_events'] > 0): ?>
                    <span class="badge bg-primary float-end"><?php echo $quick_stats['upcoming_events']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Sermons Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/sermons/') !== false) ? 'active' : ''; ?>"
                   href="../sermons/">
                    <i class="fas fa-podcast me-2"></i>
                    Sermons
                    <?php if ($quick_stats['sermons_available'] > 0): ?>
                    <span class="badge bg-primary float-end"><?php echo $quick_stats['sermons_available']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Prayer Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/prayer/') !== false) ? 'active' : ''; ?>"
                   href="../prayer/">
                    <i class="fas fa-praying-hands me-2"></i>
                    Prayer Requests
                    <?php if ($quick_stats['prayer_requests'] > 0): ?>
                    <span class="badge bg-primary float-end"><?php echo $quick_stats['prayer_requests']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Ministries Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/ministries/') !== false) ? 'active' : ''; ?>"
                   href="../ministries/">
                    <i class="fas fa-hands-helping me-2"></i>
                    Ministries
                    <?php if ($quick_stats['ministries_involved'] > 0): ?>
                    <span class="badge bg-primary float-end"><?php echo $quick_stats['ministries_involved']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Finances Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/finances/') !== false) ? 'active' : ''; ?>"
                   href="../finances/donations.php">
                    <i class="fas fa-donate me-2"></i>
                    Finances
                </a>
            </li>
            
            <!-- Communications Section -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/communications/') !== false) ? 'active' : ''; ?>"
                   href="../communications/messages.php">
                    <i class="fas fa-envelope me-2"></i>
                    Communications
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/communications/directory.php') !== false) ? 'active' : ''; ?>"
                   href="../communications/directory.php">
                    <i class="fas fa-address-book me-2"></i>
                    Member Directory
                </a>
            </li>
        </ul>

        <!-- Quick Stats -->
        <div class="p-3 border-top">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Quick Stats</span>
            </h6>
            <div class="row g-2">
                <div class="col-6">
                    <div class="text-center p-2 border rounded">
                        <small class="d-block fw-bold text-primary"><?php echo $quick_stats['upcoming_events']; ?></small>
                        <small class="text-muted">Events</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 border rounded">
                        <small class="d-block fw-bold text-primary"><?php echo $quick_stats['prayer_requests']; ?></small>
                        <small class="text-muted">Prayers</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 border rounded">
                        <small class="d-block fw-bold text-primary"><?php echo $quick_stats['sermons_available']; ?></small>
                        <small class="text-muted">Sermons</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2 border rounded">
                        <small class="d-block fw-bold text-primary"><?php echo $quick_stats['ministries_involved']; ?></small>
                        <small class="text-muted">Ministries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>