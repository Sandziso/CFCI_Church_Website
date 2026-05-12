<?php
// admin/includes/admin_sidebar.php
require_once __DIR__ . '/admin_functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="adminSidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="dashboard.php" class="brand-link">
            <div class="brand-icon">
                <i class="fas fa-church"></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">CFCI</span>
                <span class="brand-subtitle">Admin Panel</span>
            </div>
        </a>
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <!-- User Quick Info -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?php echo getUserInitials($_SESSION['full_name'] ?? 'A'); ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator') ?></span>
            <span class="user-role">Administrator</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-section">User Management</li>
            <li class="nav-item">
                <a href="user-management/users.php" class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>All Users</span>
                    <span class="badge ms-auto"><?= getTotalUsersCount() ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="user-management/add-user.php" class="nav-link">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="user-management/roles-permissions.php" class="nav-link">
                    <i class="fas fa-user-shield"></i>
                    <span>Roles & Permissions</span>
                </a>
            </li>

            <li class="nav-section">Content</li>
            <li class="nav-item">
                <a href="content-management/pages.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Pages</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="content-management/media-library.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    <span>Media Library</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="content-management/sliders.php" class="nav-link">
                    <i class="fas fa-sliders-h"></i>
                    <span>Sliders</span>
                </a>
            </li>

            <li class="nav-section">Settings</li>
            <li class="nav-item">
                <a href="system-settings/general-settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>General</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="system-settings/church-info.php" class="nav-link">
                    <i class="fas fa-church"></i>
                    <span>Church Info</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="system-settings/email-settings.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Email Settings</span>
                </a>
            </li>

            <li class="nav-section">Security</li>
            <li class="nav-item">
                <a href="security/security-settings.php" class="nav-link">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="security/activity-logs.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    <span>Activity Logs</span>
                </a>
            </li>

            <li class="nav-section">Backup</li>
            <li class="nav-item">
                <a href="backup/database-backup.php" class="nav-link">
                    <i class="fas fa-database"></i>
                    <span>Database Backup</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer links -->
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank" class="footer-link">
            <i class="fas fa-external-link-alt"></i> Visit Website
        </a>
        <a href="../logout.php" class="footer-link text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<style>
/* Sidebar Styles */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #0f2e45 0%, #1a3f5c 100%);
    color: #ecf0f1;
    display: flex;
    flex-direction: column;
    z-index: 1050;
    transition: width 0.3s ease;
    box-shadow: 4px 0 20px rgba(0,0,0,0.06);
}
.sidebar.collapsed {
    width: 70px;
}
.sidebar.collapsed .brand-text,
.sidebar.collapsed .user-info,
.sidebar.collapsed .nav-link span,
.sidebar.collapsed .nav-section,
.sidebar.collapsed .badge,
.sidebar.collapsed .footer-link span {
    display: none;
}
.sidebar.collapsed .sidebar-toggle i {
    transform: rotate(180deg);
}
.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.brand-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #fff;
    gap: 0.75rem;
    overflow: hidden;
}
.brand-icon {
    width: 40px;
    height: 40px;
    background: var(--accent);
    color: #0f2e45;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.brand-title {
    font-weight: 800;
    font-size: 1.25rem;
    line-height: 1.2;
    display: block;
    color: #fff;
}
.brand-subtitle {
    font-size: 0.7rem;
    opacity: 0.7;
    letter-spacing: 1px;
}
.sidebar-toggle {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.6);
    font-size: 1.1rem;
    cursor: pointer;
    transition: 0.3s;
}
.sidebar-toggle:hover {
    color: #fff;
}
.sidebar-user {
    display: flex;
    align-items: center;
    padding: 1rem;
    gap: 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--accent);
    color: #0f2e45;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-weight: 700;
    flex-shrink: 0;
}
.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    display: block;
    color: #fff;
}
.user-role {
    font-size: 0.75rem;
    opacity: 0.7;
}
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem 0;
}
.nav {
    flex-direction: column;
}
.nav-section {
    text-transform: uppercase;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 1rem 1.25rem 0.5rem;
    opacity: 0.5;
}
.nav-item {
    margin-bottom: 2px;
}
.nav-link {
    display: flex;
    align-items: center;
    padding: 0.7rem 1.25rem;
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    border-radius: 0 20px 20px 0;
    margin-right: 0.75rem;
    transition: 0.2s;
    white-space: nowrap;
    gap: 0.75rem;
}
.nav-link i {
    width: 20px;
    text-align: center;
    font-size: 0.95rem;
}
.nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}
.nav-link.active {
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-weight: 500;
    box-shadow: inset 3px 0 0 var(--accent);
}
.badge {
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 0.7rem;
    padding: 0.2em 0.6em;
    border-radius: 50px;
}
.sidebar-footer {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 0.75rem 0;
}
.footer-link {
    display: flex;
    align-items: center;
    padding: 0.6rem 1.25rem;
    color: rgba(255,255,255,0.65);
    text-decoration: none;
    font-size: 0.85rem;
    gap: 0.75rem;
}
.footer-link:hover {
    background: rgba(255,255,255,0.05);
    color: #fff;
}
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}
.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}
@media (max-width: 991.98px) {
    .sidebar {
        left: -100%;
    }
    .sidebar.mobile-show {
        left: 0;
    }
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
}
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.getElementById('adminSidebar').classList.add('collapsed');
    }
});
</script>