<?php
// Member Top Navigation Bar - Modern Glass Edition
if (!isset($db)) {
    require_once __DIR__ . '/../../includes/config.php';
    require_once __DIR__ . '/../../includes/main-functions.php';
    require_once __DIR__ . '/../../includes/database.php';
    $database = Database::getInstance();
    $db = $database->getConnection();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['full_name'] ?? 'Member';
$user_role = $_SESSION['user_role'] ?? 'member';

// Fetch user profile image
$user_profile = [];
if ($user_id) {
    try {
        $stmt = $db->prepare("SELECT u.full_name, up.profile_image FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Silently fail – use defaults
    }
}

// User initials helper
function getUserInitialsFallback($name) {
    if (empty($name)) return '?';
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
<!-- Modern Topbar with Glass Effect -->
<nav class="navbar navbar-expand-lg fixed-top" id="memberTopbar">
    <div class="container-fluid px-3">
        <!-- Sidebar toggle + Brand -->
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-icon me-2" title="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center fw-bold" href="dashboard.php">
                <i class="fas fa-church me-2"></i>
                <span class="brand-text"><?php echo defined('SITE_NAME') ? SITE_NAME : 'CFCI'; ?></span>
            </a>
        </div>

        <!-- Right side items -->
        <div class="d-flex align-items-center ms-auto">
            <!-- Live Notification Bell -->
            <div class="dropdown me-3 position-relative">
                <button class="btn btn-icon position-relative" id="notificationDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fa-lg"></i>
                    <span id="notificationBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.65rem; display: none;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2" style="min-width: 320px;" aria-labelledby="notificationDropdownBtn">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <a href="#" class="text-decoration-none small" id="markAllReadBtn">Mark all read</a>
                    </li>
                    <div id="notificationList" class="px-2">
                        <div class="text-center text-muted py-3 small">
                            <i class="fas fa-spinner fa-pulse me-2"></i> Loading...
                        </div>
                    </div>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center small text-primary" href="notifications/">View all</a></li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn d-flex align-items-center text-white dropdown-toggle p-0" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar me-2">
                        <?php if (!empty($user_profile['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user_profile['profile_image']); ?>" alt="avatar" class="rounded-circle img-fluid">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?php echo getUserInitialsFallback($user_name); ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="d-none d-md-inline fw-semibold"><?php echo htmlspecialchars($user_name); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="profile/"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="profile/settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Styles specific to topbar (can be embedded here or in dashboard.php) -->
<style>
    /* ========== Modern Topbar Styling ========== */
    #memberTopbar {
        background: rgba(26, 82, 118, 0.85) !important; /* primary color with opacity */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        transition: background 0.3s ease;
    }
    #memberTopbar .navbar-brand {
        color: white;
        font-size: 1.25rem;
    }
    #memberTopbar .btn-icon {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: white;
        border-radius: 10px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    #memberTopbar .btn-icon:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(255,255,255,0.8);
    }
    .user-avatar img, .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .dropdown-menu {
        animation: fadeInDown 0.2s ease;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #notificationBadge {
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }
    /* Mobile brand text */
    @media (max-width: 576px) {
        .brand-text { display: none; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const badge = document.getElementById('notificationBadge');
    const listContainer = document.getElementById('notificationList');
    const markAllBtn = document.getElementById('markAllReadBtn');

    // Fetch notification count and previews
    function fetchNotifications() {
        fetch('../../api/get_notifications.php?action=count_and_preview')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update badge
                    const count = data.unread_count || 0;
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'flex' : 'none';
                    // Animate badge if count increased
                    if (count > 0) {
                        badge.classList.add('pulse-animation');
                        setTimeout(() => badge.classList.remove('pulse-animation'), 300);
                    }
                    // Update dropdown list
                    const previews = data.previews || [];
                    let html = '';
                    if (previews.length === 0) {
                        html = '<div class="text-center text-muted py-3 small">No new notifications</div>';
                    } else {
                        html = previews.map(n => `
                            <a class="dropdown-item d-flex py-2 px-0 ${n.is_read ? '' : 'fw-semibold bg-light rounded-2'}" href="#">
                                <div class="me-2 text-${n.type === 'alert' ? 'danger' : 'primary'}">
                                    <i class="fas ${n.icon || 'fa-bell'}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small">${n.title}</div>
                                    <div class="text-muted x-small">${n.time_ago}</div>
                                </div>
                            </a>
                        `).join('');
                    }
                    listContainer.innerHTML = html;
                }
            })
            .catch(err => console.error('Notification fetch error:', err));
    }

    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('../../api/get_notifications.php?action=mark_all_read')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') fetchNotifications();
                });
        });
    }

    // Initial fetch & periodic refresh every 30 seconds
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
});
</script>