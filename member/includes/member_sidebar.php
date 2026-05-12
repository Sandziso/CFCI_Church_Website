<?php
// Member Sidebar - Collapsible & Dynamic
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="memberSidebar" class="sidebar expanded">
    <div class="sidebar-content">
        <!-- User profile summary -->
        <div class="sidebar-profile p-3 border-bottom">
            <div class="d-flex align-items-center">
                <?php if (!empty($user_profile['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user_profile['profile_image']); ?>" alt="avatar" class="rounded-circle me-3" style="width:42px; height:42px; object-fit:cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:42px; height:42px; font-weight:bold; font-size:1rem;">
                        <?php echo getUserInitials($user_name); ?>
                    </div>
                <?php endif; ?>
                <div class="overflow-hidden">
                    <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($user_name); ?></h6>
                    <small class="text-muted"><?php echo ucfirst($user_role); ?></small>
                </div>
            </div>
            <?php if ($profile_completion < 100): ?>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Profile</span><span><?php echo $profile_completion; ?>%</span>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $profile_completion; ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav flex-grow-1 py-2">
            <ul class="nav flex-column">
                <?php
                $nav_items = [
                    ['icon' => 'fa-home', 'text' => 'Dashboard', 'link' => 'dashboard.php'],
                    ['icon' => 'fa-user', 'text' => 'My Profile', 'link' => 'profile/'],
                    ['icon' => 'fa-calendar-alt', 'text' => 'Events', 'link' => 'events/', 'badge' => $quick_stats['upcoming_events']],
                    ['icon' => 'fa-podcast', 'text' => 'Sermons', 'link' => 'sermon/', 'badge' => $quick_stats['sermons_available']],
                    ['icon' => 'fa-praying-hands', 'text' => 'Prayer Requests', 'link' => 'prayer/', 'badge' => $quick_stats['prayer_requests']],
                    ['icon' => 'fa-hands-helping', 'text' => 'Ministries', 'link' => 'ministries/', 'badge' => $quick_stats['ministries_involved']],
                    ['icon' => 'fa-donate', 'text' => 'Finances', 'link' => 'finances/donations.php'],
                    ['icon' => 'fa-envelope', 'text' => 'Messages', 'link' => 'communications/messages.php'],
                    ['icon' => 'fa-address-book', 'text' => 'Directory', 'link' => 'communications/directory.php'],
                ];
                foreach ($nav_items as $item):
                    $is_active = (strpos($_SERVER['REQUEST_URI'], $item['link']) !== false);
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $item['link']; ?>">
                        <i class="fas <?php echo $item['icon']; ?> fa-fw me-3"></i>
                        <span class="link-text"><?php echo $item['text']; ?></span>
                        <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                            <span class="badge bg-primary rounded-pill ms-auto"><?php echo $item['badge']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Quick Stats (live) -->
        <div class="sidebar-stats border-top p-3">
            <h6 class="text-muted small text-uppercase fw-bold mb-3">Quick Stats</h6>
            <div class="row g-2">
                <div class="col-6">
                    <div class="stat-item rounded-3 p-2 text-center">
                        <span class="stat-value d-block fw-bold" id="statUpcomingEvents"><?php echo $quick_stats['upcoming_events'] ?? 0; ?></span>
                        <small class="text-muted">Events</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-item rounded-3 p-2 text-center">
                        <span class="stat-value d-block fw-bold" id="statPrayers"><?php echo $quick_stats['prayer_requests'] ?? 0; ?></span>
                        <small class="text-muted">Prayers</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-item rounded-3 p-2 text-center">
                        <span class="stat-value d-block fw-bold" id="statSermons"><?php echo $quick_stats['sermons_available'] ?? 0; ?></span>
                        <small class="text-muted">Sermons</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-item rounded-3 p-2 text-center">
                        <span class="stat-value d-block fw-bold" id="statMinistries"><?php echo $quick_stats['ministries_involved'] ?? 0; ?></span>
                        <small class="text-muted">Ministries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<style>
    /* ========== Modern Sidebar Styling ========== */
    #memberSidebar {
        width: 260px;
        height: calc(100vh - 56px);
        position: fixed;
        top: 56px;
        left: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 2px 0 15px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        overflow-x: hidden;
    }
    #memberSidebar.collapsed {
        width: 70px;
    }
    #memberSidebar .link-text,
    #memberSidebar .badge,
    #memberSidebar .sidebar-profile div,
    #memberSidebar .sidebar-stats {
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    #memberSidebar.collapsed .link-text,
    #memberSidebar.collapsed .badge,
    #memberSidebar.collapsed .sidebar-profile div,
    #memberSidebar.collapsed .sidebar-stats {
        opacity: 0;
        visibility: hidden;
        width: 0;
    }
    #memberSidebar .nav-link {
        padding: 12px 20px;
        color: var(--primary-color);
        border-radius: 10px;
        margin: 2px 10px;
        transition: all 0.2s ease;
        white-space: nowrap;
        overflow: hidden;
    }
    #memberSidebar .nav-link:hover {
        background: rgba(26, 82, 118, 0.1);
        transform: translateX(5px);
    }
    #memberSidebar .nav-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    #memberSidebar .stat-item {
        background: rgba(26, 82, 118, 0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }
    #memberSidebar .stat-item:hover {
        background: rgba(26, 82, 118, 0.1);
        transform: translateY(-2px);
    }
    .stat-value {
        font-size: 1.1rem;
        color: var(--primary-color);
    }
    /* Offcanvas for mobile */
    @media (max-width: 767.98px) {
        #memberSidebar {
            width: 260px;
            position: fixed;
            left: -280px;
            top: 56px;
            height: 100vh;
            z-index: 1050;
        }
        #memberSidebar.show {
            left: 0;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('memberSidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                // Adjust main content margin
                const main = document.querySelector('.main-content');
                if (main) main.style.marginLeft = sidebar.classList.contains('collapsed') ? '70px' : '260px';
            }
        });
    }

    // Live update stats every 60 seconds
    function updateQuickStats() {
        fetch('../../api/get_notifications.php?action=stats')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('statUpcomingEvents').textContent = data.upcoming_events || 0;
                    document.getElementById('statPrayers').textContent = data.prayer_requests || 0;
                    document.getElementById('statSermons').textContent = data.sermons_available || 0;
                    document.getElementById('statMinistries').textContent = data.ministries_involved || 0;
                }
            });
    }
    setInterval(updateQuickStats, 60000);
});
</script>