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
    <div class="sidebar-header">
        <div class="church-logo">
            <i class="fas fa-church"></i>
            <div class="church-info">
                <div class="church-name"><?php echo SITE_NAME; ?></div>
                <div class="church-subtitle">Member Portal</div>
            </div>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="user-profile">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Member'); ?></div>
            <div class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'member'); ?></div>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-title">MAIN</div>
            
            <div class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="menu-text">Dashboard</span>
                    <?php if ($current_page == 'dashboard.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <div class="menu-section">
            <div class="menu-title">CONNECT</div>
            
            <div class="menu-item <?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
                <a href="events.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="menu-text">Events</span>
                    <?php if ($current_page == 'events.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo in_array($current_page, ['prayer-requests.php', 'submit-prayer.php']) ? 'active' : ''; ?>">
                <a href="prayer-requests.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-praying-hands"></i>
                    </div>
                    <span class="menu-text">Prayer Requests</span>
                    <?php if (in_array($current_page, ['prayer-requests.php', 'submit-prayer.php'])): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo $current_page == 'sermons.php' ? 'active' : ''; ?>">
                <a href="sermons.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <span class="menu-text">Sermons</span>
                    <?php if ($current_page == 'sermons.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo $current_page == 'ministries.php' ? 'active' : ''; ?>">
                <a href="ministries.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="menu-text">Ministries</span>
                    <?php if ($current_page == 'ministries.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo in_array($current_page, ['donations.php', 'make-donation.php', 'donation-history.php']) ? 'active' : ''; ?>">
                <a href="donations.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-donate"></i>
                    </div>
                    <span class="menu-text">Donations</span>
                    <?php if (in_array($current_page, ['donations.php', 'make-donation.php', 'donation-history.php'])): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <div class="menu-section">
            <div class="menu-title">COMMUNITY</div>
            
            <div class="menu-item <?php echo $current_page == 'directory.php' ? 'active' : ''; ?>">
                <a href="directory.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span class="menu-text">Directory</span>
                    <?php if ($current_page == 'directory.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
                <a href="messages.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span class="menu-text">Messages</span>
                    <span class="message-badge">3</span>
                    <?php if ($current_page == 'messages.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <div class="menu-section">
            <div class="menu-title">ACCOUNT</div>
            
            <div class="menu-item <?php echo in_array($current_page, ['profile.php', 'edit-profile.php']) ? 'active' : ''; ?>">
                <a href="profile.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="menu-text">My Profile</span>
                    <?php if (in_array($current_page, ['profile.php', 'edit-profile.php'])): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php" class="menu-link">
                    <div class="menu-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="menu-text">Settings</span>
                    <?php if ($current_page == 'settings.php'): ?>
                        <div class="active-indicator"></div>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="menu-item logout-item">
                <a href="../auth/logout.php" class="menu-link logout-link">
                    <div class="menu-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="menu-text">Logout</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <div class="church-verse">
            "For where two or three gather in my name, there am I with them."
            <div class="verse-ref">- Matthew 18:20</div>
        </div>
    </div>
</div>

<style>
:root {
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 80px;
    --sidebar-bg: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
    --sidebar-text: #ffffff;
    --sidebar-hover: rgba(255, 255, 255, 0.1);
    --sidebar-active: rgba(255, 255, 255, 0.15);
    --sidebar-border: rgba(255, 255, 255, 0.1);
    --accent-blue: #3b82f6;
    --accent-green: #10b981;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.sidebar-header {
    padding: 25px 20px 20px;
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.church-logo {
    display: flex;
    align-items: center;
    gap: 15px;
}

.church-logo i {
    font-size: 2rem;
    color: var(--accent-blue);
}

.church-info {
    display: flex;
    flex-direction: column;
}

.church-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    line-height: 1.2;
}

.church-subtitle {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-top: 2px;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: var(--sidebar-text);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: background 0.3s ease;
    display: none;
}

.sidebar-toggle:hover {
    background: var(--sidebar-hover);
}

.user-profile {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid var(--sidebar-border);
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--accent-blue);
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 2px;
}

.user-role {
    font-size: 0.85rem;
    opacity: 0.8;
    text-transform: capitalize;
}

.sidebar-menu {
    flex: 1;
    padding: 20px 0;
    overflow-y: auto;
}

.menu-section {
    margin-bottom: 25px;
}

.menu-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 25px 10px;
    opacity: 0.6;
}

.menu-item {
    position: relative;
    margin: 5px 15px;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: var(--sidebar-text);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.menu-link:hover {
    background: var(--sidebar-hover);
    transform: translateX(5px);
}

.menu-item.active .menu-link {
    background: var(--sidebar-active);
    color: white;
}

.menu-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.1rem;
}

.menu-text {
    font-weight: 500;
    font-size: 0.95rem;
    flex: 1;
}

.active-indicator {
    width: 4px;
    height: 20px;
    background: var(--accent-blue);
    border-radius: 2px;
    position: absolute;
    right: 15px;
}

.message-badge {
    background: var(--accent-green);
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: auto;
}

.logout-item .menu-link {
    color: #f87171;
}

.logout-item .menu-link:hover {
    background: rgba(248, 113, 113, 0.1);
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid var(--sidebar-border);
}

.church-verse {
    font-style: italic;
    font-size: 0.85rem;
    line-height: 1.4;
    text-align: center;
    opacity: 0.8;
}

.verse-ref {
    margin-top: 8px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .sidebar {
        width: var(--sidebar-collapsed-width);
        overflow: visible;
    }
    
    .sidebar-header {
        padding: 20px 15px;
        justify-content: center;
    }
    
    .church-info,
    .user-info,
    .menu-text,
    .menu-title,
    .sidebar-footer,
    .active-indicator {
        display: none;
    }
    
    .church-logo {
        justify-content: center;
    }
    
    .church-logo i {
        font-size: 1.8rem;
        margin-right: 0;
    }
    
    .user-profile {
        padding: 15px 10px;
        justify-content: center;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
        margin-right: 0;
    }
    
    .menu-item {
        margin: 5px 10px;
    }
    
    .menu-link {
        padding: 15px;
        justify-content: center;
    }
    
    .menu-icon {
        margin-right: 0;
    }
    
    .message-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        margin-left: 0;
    }
    
    .sidebar:hover {
        width: var(--sidebar-width);
    }
    
    .sidebar:hover .church-info,
    .sidebar:hover .user-info,
    .sidebar:hover .menu-text,
    .sidebar:hover .menu-title,
    .sidebar:hover .sidebar-footer,
    .sidebar:hover .active-indicator {
        display: block;
    }
    
    .sidebar:hover .church-logo {
        justify-content: flex-start;
    }
    
    .sidebar:hover .church-logo i {
        margin-right: 15px;
    }
    
    .sidebar:hover .user-profile {
        justify-content: flex-start;
    }
    
    .sidebar:hover .user-avatar {
        margin-right: 15px;
    }
    
    .sidebar:hover .menu-link {
        justify-content: flex-start;
        padding: 12px 15px;
    }
    
    .sidebar:hover .menu-icon {
        margin-right: 15px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width);
    }
    
    .sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: block;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 999;
        background: var(--sidebar-bg);
        color: white;
        box-shadow: var(--shadow);
    }
    
    .main-content {
        margin-left: 0 !important;
    }
}

/* Scrollbar Styling */
.sidebar-menu::-webkit-scrollbar {
    width: 4px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    
    // Mobile sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
    
    // Update main content margin based on sidebar state
    function updateContentMargin() {
        if (window.innerWidth > 1200) {
            mainContent.style.marginLeft = sidebar.offsetWidth + 'px';
        } else if (window.innerWidth > 768) {
            mainContent.style.marginLeft = '80px';
        } else {
            mainContent.style.marginLeft = '0';
        }
    }
    
    // Initial setup
    updateContentMargin();
    
    // Update on resize
    window.addEventListener('resize', updateContentMargin);
    
    // Add hover effect delay for collapsed sidebar
    let hoverTimeout;
    sidebar.addEventListener('mouseenter', function() {
        if (window.innerWidth > 1200) return;
        clearTimeout(hoverTimeout);
        sidebar.style.overflow = 'visible';
    });
    
    sidebar.addEventListener('mouseleave', function() {
        if (window.innerWidth > 1200) return;
        hoverTimeout = setTimeout(() => {
            sidebar.style.overflow = 'hidden';
        }, 300);
    });
});
</script>