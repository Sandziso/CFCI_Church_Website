<?php
// ===================================================
// MEMBER - Edit Profile
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

$error = '';
$success = '';

// Fetch current data
$user = $db->prepare("SELECT id, full_name, email, avatar_url, city, country FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch(PDO::FETCH_ASSOC);

$profile = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$profile->execute([$user_id]);
$profile = $profile->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $country   = trim($_POST['country'] ?? 'Eswatini');
    $birth_date = $_POST['birth_date'] ?? null;
    $gender    = $_POST['gender'] ?? null;
    $marital_status = $_POST['marital_status'] ?? null;
    $occupation = trim($_POST['occupation'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    $emergency_name  = trim($_POST['emergency_contact_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_contact_phone'] ?? '');

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        try {
            // Update users table
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, city = ?, country = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $city, $country, $user_id]);

            // Update or insert user_profiles
            $existing = $db->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
            $existing->execute([$user_id]);

            if ($existing->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE user_profiles SET phone = ?, address = ?, birth_date = ?, gender = ?, marital_status = ?, occupation = ?, bio = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE user_id = ?");
                $stmt->execute([$phone, $address, $birth_date, $gender, $marital_status, $occupation, $bio, $emergency_name, $emergency_phone, $user_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO user_profiles (user_id, phone, address, birth_date, gender, marital_status, occupation, bio, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $phone, $address, $birth_date, $gender, $marital_status, $occupation, $bio, $emergency_name, $emergency_phone]);
            }

            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $target_dir = "../../uploads/avatars/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                $target_file = $target_dir . $filename;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                    $avatar_url = 'uploads/avatars/' . $filename;
                    $db->prepare("UPDATE users SET avatar_url = ? WHERE id = ?")->execute([$avatar_url, $user_id]);
                }
            }

            $_SESSION['full_name'] = $full_name; // update session name
            $success = 'Profile updated successfully.';
        } catch (Exception $e) {
            error_log("Profile edit error: " . $e->getMessage());
            $error = 'An error occurred while saving. Please try again.';
        }
    }
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
    <title>Edit Profile - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #1a5276; --secondary: #e67e22; }
        body { background: #f8f9fa; padding-top: 56px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 767px) { .main-content { margin-left: 0; } }
        .edit-card { background: white; border-radius: 12px; padding: 2rem; max-width: 900px; margin: 0 auto; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="edit-card">
                    <h2 class="mb-4"><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($profile['birth_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="male" <?php echo ($profile['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo ($profile['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo ($profile['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marital Status</label>
                                <select name="marital_status" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="single" <?php echo ($profile['marital_status'] ?? '') == 'single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="married" <?php echo ($profile['marital_status'] ?? '') == 'married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="divorced" <?php echo ($profile['marital_status'] ?? '') == 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="widowed" <?php echo ($profile['marital_status'] ?? '') == 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($user['country'] ?? 'Eswatini'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($profile['occupation'] ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($profile['emergency_contact_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($profile['emergency_contact_phone'] ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Profile Picture</label>
                                <?php if (!empty($user['avatar_url']) && $user['avatar_url'] != '/assets/images/default-avatar.png'): ?>
                                    <div class="mb-2"><img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Current avatar" style="height:80px; border-radius:8px;"></div>
                                <?php endif; ?>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>