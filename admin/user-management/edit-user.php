<?php
// admin/user-management/edit-user.php
require_once __DIR__ . '/../includes/admin_functions.php';

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: users.php');
    exit;
}

// Fetch user
$stmt = $conn->prepare("SELECT u.*, a.role as admin_role, a.permissions FROM users u LEFT JOIN admins a ON u.id = a.user_id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("User not found.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'member';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $new_password = $_POST['new_password'] ?? '';

    if (empty($full_name) || empty($email)) {
        $errors[] = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    } else {
        // Check email uniqueness except current user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already taken.';
        } else {
            $updateFields = "full_name = ?, email = ?, role = ?, is_active = ?";
            $params = [$full_name, $email, $role, $is_active];
            if (!empty($new_password)) {
                if (strlen($new_password) < 8) {
                    $errors[] = 'Password must be at least 8 characters.';
                } else {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateFields .= ", password_hash = ?";
                    $params[] = $hash;
                }
            }
            if (empty($errors)) {
                $params[] = $userId; // for WHERE id = ?
                $stmt = $conn->prepare("UPDATE users SET $updateFields WHERE id = ?");
                if ($stmt->execute($params)) {
                    // Update admins table if needed
                    if ($role === 'admin') {
                        // Ensure admin entry exists
                        $stmt2 = $conn->prepare("INSERT IGNORE INTO admins (user_id, role, permissions) VALUES (?, 'content', '{}')");
                        $stmt2->execute([$userId]);
                    } else {
                        // Remove from admins if not admin
                        $stmt2 = $conn->prepare("DELETE FROM admins WHERE user_id = ?");
                        $stmt2->execute([$userId]);
                    }
                    $success = 'User updated.';
                    // Refresh user data
                    $stmt = $conn->prepare("SELECT u.*, a.role as admin_role FROM users u LEFT JOIN admins a ON u.id = a.user_id WHERE u.id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                } else {
                    $errors[] = 'Update failed.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Edit User #<?= $user['id'] ?></h4>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" class="form-control" minlength="8">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="member" <?= ($user['admin_role'] ?? $user['role']) == 'member' ? 'selected' : '' ?>>Member</option>
                            <option value="pastor" <?= ($user['admin_role'] ?? $user['role']) == 'pastor' ? 'selected' : '' ?>>Pastor</option>
                            <option value="admin" <?= ($user['admin_role'] ?? $user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="guest" <?= ($user['admin_role'] ?? $user['role']) == 'guest' ? 'selected' : '' ?>>Guest</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                    <a href="users.php" class="btn btn-outline-secondary ms-2">Back to Users</a>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>