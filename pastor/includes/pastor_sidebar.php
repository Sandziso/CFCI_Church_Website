<?php
// pastor/includes/pastor_sidebar.php
?>
<div class="pastor-sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-church"></i>
            </div>
            <div class="logo-text">
                <h2>CFCI</h2>
                <span>Pastor Dashboard</span>
            </div>
    </div>
    
    <div class="sidebar-menu">
        <ul class="menu-list">
            <li class="menu-item active">
                <a href="dashboard.php" class="menu-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="members/" class="menu-link">
                    <i class="fas fa-users"></i>
                    <span>Members</span>
                </a>
            </li>
            
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="submenu">
                    <li><a href="event-management/events.php">All Events</a></li>
                    <li><a href="event-management/create-event.php">Create Event</a></li>
                    <li><a href="event-management/event-registrations.php">Registrations</a></li>
                    <li><a href="event-management/attendance.php">Attendance</a></li>
                </ul>
            </li>
            
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-video"></i>
                    <span>Sermons</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="submenu">
                    <li><a href="sermon-management/sermons.php">All Sermons</a></li>
                    <li><a href="sermon-management/add-sermon.php">Add Sermon</a></li>
                    <li><a href="sermon-management/sermon-series.php">Series</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="ministries/" class="menu-link">
                    <i class="fas fa-hands-helping"></i>
                    <span>Ministries</span>
                </a>
            </li>
            
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-comments-dollar"></i>
                    <span>Finances</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="submenu">
                    <li><a href="finances/donations.php">Donations</a></li>
                    <li><a href="finances/expenses.php">Expenses</a></li>
                    <li><a href="finances/financial-reports.php">Reports</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="prayer-management/" class="menu-link">
                    <i class="fas fa-praying-hands"></i>
                    <span>Prayer Requests</span>
                </a>
            </li>
            
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>Communications</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="submenu">
                    <li><a href="communications/announcements.php">Announcements</a></li>
                    <li><a href="communications/create-announcement.php">Create Announcement</a></li>
                    <li><a href="communications/email-blast.php">Email Blast</a></li>
                    <li><a href="communications/sms-blast.php">SMS Blast</a></li>
                </ul>
            </li>
            
            <li class="menu-item has-submenu">
                <a href="#" class="menu-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="submenu">
                    <li><a href="reports/membership-reports.php">Membership</a></li>
                    <li><a href="reports/attendance-reports.php">Attendance</a></li>
                    <li><a href="reports/growth-reports.php">Growth</a></li>
                </ul>
            </li>
            
            <li class="menu-divider"></li>
            
            <li class="menu-item">
                <a href="../index.php" class="menu-link" target="_blank">
                    <i class="fas fa-globe"></i>
                    <span>Visit Website</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="../auth/logout.php" class="menu-link logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.pastor-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: var(--primary);
    color: white;
    z-index: 1000;
    transition: all 0.3s ease;
    overflow-y: auto;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.logo-text h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: white;
}

.logo-text span {
    font-size: 0.8rem;
    opacity: 0.8;
}

.sidebar-menu {
    padding: 20px 0;
}

.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    margin-bottom: 5px;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.menu-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.menu-link.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-left: 4px solid var(--secondary);
}

.menu-link i {
    width: 20px;
    margin-right: 12px;
    font-size: 1rem;
}

.menu-link span {
    flex: 1;
    font-size: 0.9rem;
    font-weight: 500;
}

.menu-link .fa-chevron-right {
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.has-submenu.active .fa-chevron-right {
    transform: rotate(90deg);
}

.submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: rgba(0, 0, 0, 0.2);
}

.has-submenu.active .submenu {
    max-height: 500px;
}

.submenu li {
    list-style: none;
}

.submenu a {
    display: block;
    padding: 10px 20px 10px 50px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.submenu a:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
}

.menu-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 15px 20px;
}

.menu-link.logout {
    color: #ff6b6b;
}

.menu-link.logout:hover {
    background: rgba(255, 107, 107, 0.1);
}

/* Mobile responsive */
@media (max-width: 1024px) {
    .pastor-sidebar {
        transform: translateX(-100%);
    }
    
    .pastor-sidebar.active {
        transform: translateX(0);
    }
    
    .main-content.sidebar-active {
        margin-left: 250px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle submenus
    document.querySelectorAll('.has-submenu > .menu-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });
    
    // Set active menu item based on current page
    const currentPath = window.location.pathname;
    document.querySelectorAll('.menu-link').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
            // Expand parent if in submenu
            let parent = link.closest('.has-submenu');
            while (parent) {
                parent.classList.add('active');
                parent = parent.parentElement.closest('.has-submenu');
            }
        }
    });
});
</script>