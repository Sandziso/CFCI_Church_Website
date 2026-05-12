<?php
// admin/includes/admin_topbar.php
require_once __DIR__ . '/admin_functions.php';
?>
<header class="admin-topbar">
    <div class="topbar-left">
        <button class="btn btn-icon d-lg-none me-2" onclick="toggleMobileSidebar()" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <a class="topbar-brand d-none d-lg-flex" href="dashboard.php">
            <i class="fas fa-church me-2"></i> CFCI <span class="text-muted ms-1">Admin</span>
        </a>
    </div>

    <div class="topbar-center">
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search members, pages, settings..." id="globalSearch">
            <div class="search-results" id="searchResults" style="display:none;"></div>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Notifications -->
        <div class="dropdown me-2">
            <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge-dot bg-danger"><?= getPendingPrayersCount() ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="width: 300px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fs-6 fw-semibold">Notifications</h6>
                    <a href="#" class="small text-decoration-none">Mark all read</a>
                </div>
                <div class="notification-list">
                    <a href="#" class="notification-item">
                        <div class="icon bg-primary"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <p class="mb-0 small fw-medium">New member registered</p>
                            <small class="text-muted">2 min ago</small>
                        </div>
                    </a>
                    <a href="#" class="notification-item">
                        <div class="icon bg-success"><i class="fas fa-donate"></i></div>
                        <div>
                            <p class="mb-0 small fw-medium">Donation of SZL 1,500 received</p>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                    </a>
                    <a href="#" class="notification-item">
                        <div class="icon bg-warning"><i class="fas fa-pray"></i></div>
                        <div>
                            <p class="mb-0 small fw-medium">New prayer request submitted</p>
                            <small class="text-muted">3 hours ago</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="dropdown me-3">
            <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-envelope"></i>
                <span class="badge-dot bg-primary"><?= getUnreadMessagesCount() ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="width: 300px;">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="mb-0 fs-6 fw-semibold">Messages</h6>
                    <a href="#" class="small text-decoration-none">View all</a>
                </div>
                <a href="#" class="message-item">
                    <div class="avatar bg-info text-white">JD</div>
                    <div>
                        <strong>Jane Dlamini</strong>
                        <p class="mb-0 small">When is the next bible study?</p>
                        <small class="text-muted">10:30 AM</small>
                    </div>
                </a>
                <a href="#" class="message-item">
                    <div class="avatar bg-secondary text-white">SM</div>
                    <div>
                        <strong>Sipho Mamba</strong>
                        <p class="mb-0 small">Thank you for your guidance...</p>
                        <small class="text-muted">Yesterday</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- User Profile -->
        <div class="dropdown">
            <button class="btn user-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                <div class="user-avatar-sm">
                    <?= getUserInitials($_SESSION['full_name'] ?? 'A') ?>
                </div>
                <div class="d-none d-md-flex flex-column align-items-start ms-2">
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
                    <small class="text-muted" style="font-size:0.7rem;">Administrator</small>
                </div>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <div class="px-3 py-2 text-center">
                    <div class="user-avatar-lg mx-auto"><?= getUserInitials($_SESSION['full_name'] ?? 'A') ?></div>
                    <h6 class="mt-2 mb-0"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></h6>
                    <small class="text-muted">Administrator</small>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a>
                <a class="dropdown-item" href="system-settings/general-settings.php"><i class="fas fa-cog me-2"></i> Settings</a>
                <a class="dropdown-item" href="security/activity-logs.php"><i class="fas fa-history me-2"></i> Activity Logs</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> View Website</a>
                <a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>
    </div>
</header>

<style>
.admin-topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 0.5rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}
.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
}
.topbar-brand {
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--primary);
    text-decoration: none;
}
.btn-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #555;
    position: relative;
    transition: 0.2s;
}
.btn-icon:hover {
    background: #f1f5f9;
    color: #1a5276;
}
.badge-dot {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: block;
}
.search-wrapper {
    position: relative;
    max-width: 360px;
    width: 100%;
}
.search-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.search-wrapper input {
    width: 100%;
    padding: 0.45rem 1rem 0.45rem 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    background: #f8fafc;
    outline: none;
    font-size: 0.85rem;
    transition: 0.2s;
}
.search-wrapper input:focus {
    border-color: #1a5276;
    background: #fff;
}
.notification-item,
.message-item {
    display: flex;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: #1e293b;
}
.notification-item:hover,
.message-item:hover {
    background: #f8fafc;
}
.notification-item .icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: white;
}
.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 600;
}
.user-dropdown {
    display: flex;
    align-items: center;
    background: transparent;
    border: none;
    text-decoration: none;
}
.user-avatar-sm {
    width: 36px;
    height: 36px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 0.8rem;
}
.user-avatar-lg {
    width: 60px;
    height: 60px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 1.2rem;
}
@media (max-width: 767.98px) {
    .topbar-center { display: none; }
}
</style>

<script>
function toggleMobileSidebar() {
    document.getElementById('adminSidebar').classList.toggle('mobile-show');
}
</script>