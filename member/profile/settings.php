<?php
// ===================================================
// MEMBER - Account Settings
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

$error   = '';
$success = '';

// --- Handle Password Change ---
if (isset($_POST['change_password'])) {
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw     = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    if (empty($current_pw) || empty($new_pw) || empty($confirm_pw)) {
        $error = 'All password fields are required.';
    } elseif ($new_pw !== $confirm_pw) {
        $error = 'New passwords do not match.';
    } else {
        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current_pw, $hash)) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password_hash = ?, last_password_change = NOW() WHERE id = ?")->execute([$new_hash, $user_id]);
            $success = 'Password updated successfully.';
        }
    }
}

// --- Handle Notification Preferences ---
if (isset($_POST['save_notifications'])) {
    $prefs = [
        'prayer_updates'      => isset($_POST['notify_prayer']) ? 1 : 0,
        'event_notifications' => isset($_POST['notify_events']) ? 1 : 0,
        'newsletter'          => isset($_POST['notify_newsletter']) ? 1 : 0,
        'ministry_updates'    => isset($_POST['notify_ministry']) ? 1 : 0,
        'sermon_releases'     => isset($_POST['notify_sermons']) ? 1 : 0,
    ];

    foreach ($prefs as $type => $enabled) {
        // Check if exists
        $check = $db->prepare("SELECT id FROM notification_preferences WHERE user_id = ? AND notification_type = ?");
        $check->execute([$user_id, $type]);
        if ($check->fetch()) {
            $db->prepare("UPDATE notification_preferences SET email_enabled = ? WHERE user_id = ? AND notification_type = ?")
               ->execute([$enabled, $user_id, $type]);
        } else {
            $db->prepare("INSERT INTO notification_preferences (user_id, notification_type, email_enabled) VALUES (?, ?, ?)")
               ->execute([$user_id, $type, $enabled]);
        }
    }

    // Update main user flags
    $receive_newsletter = isset($_POST['notify_newsletter']) ? 1 : 0;
    $receive_prayer    = isset($_POST['notify_prayer']) ? 1 : 0;
    $receive_events    = isset($_POST['notify_events']) ? 1 : 0;
    $db->prepare("UPDATE users SET receive_newsletter = ?, receive_prayer_updates = ?, receive_event_notifications = ? WHERE id = ?")
       ->execute([$receive_newsletter, $receive_prayer, $receive_events, $user_id]);

    $success = 'Notification preferences saved.';
}

// --- Handle Privacy Settings ---
if (isset($_POST['save_privacy'])) {
    $privacy = $_POST['profile_privacy'] ?? 'members';
    $db->prepare("UPDATE users SET profile_privacy = ? WHERE id = ?")->execute([$privacy, $user_id]);
    $success = 'Privacy settings updated.';
}

// Fetch current preferences & privacy
$privacy = $db->query("SELECT profile_privacy FROM users WHERE id = $user_id")->fetchColumn();
$notif_prefs = [];
$res = $db->query("SELECT notification_type, email_enabled FROM notification_preferences WHERE user_id = $user_id");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $notif_prefs[$row['notification_type']] = $row['email_enabled'];
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => 0,
    'prayer_requests'    => 0,
    'ministries_involved' => 0,
];
$profile_completion = 0;

require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #1a5276; --secondary: #e67e22; }
        body { background: #f8f9fa; padding-top: 56px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 767px) { .main-content { margin-left: 0; } }
        .settings-card { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 900px; }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Account Settings</h2>
                <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                <!-- Change Password -->
                <div class="settings-card">
                    <h5>Change Password</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary"><i class="fas fa-key me-1"></i> Update Password</button>
                    </form>
                </div>

                <!-- Notification Preferences -->
                <div class="settings-card">
                    <h5>Email Notifications</h5>
                    <form method="POST">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_prayer" <?php echo ($notif_prefs['prayer_updates'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Prayer request updates</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_events" <?php echo ($notif_prefs['event_notifications'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Event reminders and announcements</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_newsletter" <?php echo ($notif_prefs['newsletter'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Church newsletter</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_ministry" <?php echo ($notif_prefs['ministry_updates'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Ministry updates</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="notify_sermons" <?php echo ($notif_prefs['sermon_releases'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">New sermon releases</label>
                        </div>
                        <button type="submit" name="save_notifications" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Preferences</button>
                    </form>
                </div>

                <!-- Privacy Settings -->
                <div class="settings-card">
                    <h5>Profile Privacy</h5>
                    <p class="text-muted">Control who can see your information in the member directory.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <select name="profile_privacy" class="form-select">
                                <option value="public" <?php echo $privacy == 'public' ? 'selected' : ''; ?>>Public – visible to everyone</option>
                                <option value="members" <?php echo $privacy == 'members' ? 'selected' : ''; ?>>Members only</option>
                                <option value="private" <?php echo $privacy == 'private' ? 'selected' : ''; ?>>Private – only church staff</option>
                            </select>
                        </div>
                        <button type="submit" name="save_privacy" class="btn btn-primary"><i class="fas fa-shield-alt me-1"></i> Update Privacy</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>