<?php
// ===================================================
// MEMBER - My Profile (View)
// ===================================================

require_once '../../includes/config.php';
require_once '../../includes/main-functions.php';

if (!is_logged_in()) {
    header('Location: ../../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$user_role = $_SESSION['user_role'];

// Redirect admins/pastors if needed
if (is_admin()) {
    header('Location: ../../admin/dashboard.php');
    exit();
} elseif (is_pastor()) {
    header('Location: ../../pastor/dashboard.php');
    exit();
}

require_once '../../includes/database.php';
$database = Database::getInstance();
$db = $database->getConnection();

// Fetch user data
$user = [];
$profile = [];

try {
    $stmt = $db->prepare("SELECT id, full_name, email, avatar_url, city, country, membership_status, join_date, last_login, profile_completion_percentage FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Profile view error: " . $e->getMessage());
    die("Could not load profile.");
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => 0,
    'prayer_requests'    => 0,
    'ministries_involved' => 0,
];
$profile_completion = $user['profile_completion_percentage'] ?? 0;

require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #1a5276; --secondary: #e67e22; }
        body { background: #f8f9fa; padding-top: 56px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 767px) { .main-content { margin-left: 0; } }
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 100px; height: 100px; border-radius: 50%; object-fit: cover;
            background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; color: white; margin-right: 1.5rem;
        }
        .card-custom { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Profile Header -->
                <div class="profile-header d-flex align-items-center">
                    <div class="profile-avatar">
                        <?php if (!empty($user['avatar_url']) && $user['avatar_url'] != '/assets/images/default-avatar.png'): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="avatar" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                        <?php else: 
                            $initials = getUserInitials($user['full_name']);
                            echo $initials;
                        endif; ?>
                    </div>
                    <div>
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="mb-1"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if (!empty($profile['phone'])): ?>
                            <p class="mb-1"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($profile['phone']); ?></p>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark"><?php echo ucfirst($user['membership_status']); ?></span>
                        <?php if ($user['profile_completion_percentage'] < 100): ?>
                            <span class="badge bg-warning text-dark ms-2">Profile <?php echo $user['profile_completion_percentage']; ?>% complete</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card-custom">
                            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Member since:</strong> <?php echo date('M d, Y', strtotime($user['join_date'])); ?></li>
                                <li><strong>Last login:</strong> <?php echo $user['last_login'] ? date('M d, Y g:i A', strtotime($user['last_login'])) : 'N/A'; ?></li>
                                <?php if (!empty($profile['gender'])): ?><li><strong>Gender:</strong> <?php echo htmlspecialchars($profile['gender']); ?></li><?php endif; ?>
                                <?php if (!empty($profile['birth_date'])): ?><li><strong>Date of Birth:</strong> <?php echo date('M d, Y', strtotime($profile['birth_date'])); ?></li><?php endif; ?>
                                <?php if (!empty($profile['marital_status'])): ?><li><strong>Marital Status:</strong> <?php echo htmlspecialchars($profile['marital_status']); ?></li><?php endif; ?>
                                <?php if (!empty($profile['occupation'])): ?><li><strong>Occupation:</strong> <?php echo htmlspecialchars($profile['occupation']); ?></li><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-custom">
                            <h5><i class="fas fa-map-marker-alt me-2"></i>Contact & Location</h5>
                            <ul class="list-unstyled mb-0">
                                <?php if (!empty($profile['address'])): ?><li><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($profile['address'])); ?></li><?php endif; ?>
                                <?php if (!empty($user['city'])): ?><li><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></li><?php endif; ?>
                                <?php if (!empty($user['country'])): ?><li><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></li><?php endif; ?>
                                <?php if (!empty($profile['emergency_contact_name'])): ?><li><strong>Emergency Contact:</strong> <?php echo htmlspecialchars($profile['emergency_contact_name']); ?></li><?php endif; ?>
                                <?php if (!empty($profile['emergency_contact_phone'])): ?><li><strong>Emergency Phone:</strong> <?php echo htmlspecialchars($profile['emergency_contact_phone']); ?></li><?php endif; ?>
                            </ul>
                        </div>
                        <?php if (!empty($profile['bio'])): ?>
                        <div class="card-custom">
                            <h5><i class="fas fa-user-edit me-2"></i>About Me</h5>
                            <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="edit.php" class="btn btn-primary"><i class="fas fa-pencil-alt me-1"></i> Edit Profile</a>
                <a href="settings.php" class="btn btn-outline-secondary"><i class="fas fa-cog me-1"></i> Account Settings</a>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>