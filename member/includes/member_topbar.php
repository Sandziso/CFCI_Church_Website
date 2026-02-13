<?php
// Member Top Navigation Bar
if (!isset($conn)) {
    // Use correct relative path from member/includes/ to includes/
    require_once __DIR__ . '/../../includes/config.php';
    require_once __DIR__ . '/../../includes/main-functions.php';
}

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['full_name'] ?? 'Member';
$user_role = $_SESSION['user_role'] ?? 'member';

// Get user profile data
$user_profile = [];
if ($user_id) {
    try {
        // Get database connection
        require_once __DIR__ . '/../../includes/database.php';
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("
            SELECT u.*, up.profile_image 
            FROM users u 
            LEFT JOIN user_profiles up ON u.id = up.user_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user_profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        // Silent fail - use default values
    }
}

// Helper function for user initials (fallback)
function getUserInitialsFallback($name) {
    if (empty($name)) return 'U';
    $initials = '';
    $words = explode(' ', $name);
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return substr($initials, 0, 2);
}
?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--primary-color); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <!-- Mobile Toggle & Brand -->
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <i class="fas fa-church me-2"></i>
            <span class="d-none d-md-inline"><?php echo defined('SITE_NAME') ? SITE_NAME : 'Church Management System'; ?></span>
            <span class="d-inline d-md-none">Member Portal</span>
        </a>

        <!-- Right Side Items -->
        <div class="d-flex align-items-center">
            <!-- Notifications (simplified) -->
            <div class="dropdown me-3">
                <a href="#" class="nav-link text-white position-relative" id="notificationDropdown" 
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><a class="dropdown-item text-center text-muted">No new notifications</a></li>
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" 
                   id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($user_profile['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user_profile['profile_image']); ?>" 
                         alt="<?php echo htmlspecialchars($user_name); ?>" 
                         class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                    <?php else: ?>
                    <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-2" 
                         style="width: 32px; height: 32px; font-weight: bold; font-size: 0.9rem;">
                        <?php echo getUserInitialsFallback($user_name); ?>
                    </div>
                    <?php endif; ?>
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($user_name); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="../profile/">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../profile/settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="../../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>