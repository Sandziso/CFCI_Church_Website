<?php
// admin/user-management/roles-permissions.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Fetch all admin entries
$admins = $conn->query("
    SELECT a.id, a.user_id, a.role, a.permissions, u.full_name, u.email
    FROM admins a
    JOIN users u ON a.user_id = u.id
    ORDER BY u.full_name
")->fetchAll(PDO::FETCH_ASSOC);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    $adminId = (int)($_POST['admin_id'] ?? 0);
    $permissions = $_POST['perms'] ?? [];
    $adminRole = $_POST['admin_role'] ?? 'content';
    // Update permissions as JSON
    $jsonPerms = json_encode($permissions);
    $stmt = $conn->prepare("UPDATE admins SET role = ?, permissions = ? WHERE id = ?");
    $stmt->execute([$adminRole, $jsonPerms, $adminId]);
    $msg = 'Permissions updated.';
    // Refresh
    $admins = $conn->query("
        SELECT a.id, a.user_id, a.role, a.permissions, u.full_name, u.email
        FROM admins a
        JOIN users u ON a.user_id = u.id
        ORDER BY u.full_name
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roles & Permissions | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .permission-tag { display: inline-block; background: #e2e8f0; padding: 0.2em 0.7em; border-radius: 20px; font-size: 0.75rem; margin: 2px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Admin Roles & Permissions</h4>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <?php foreach ($admins as $admin): ?>
            <div class="card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><?= htmlspecialchars($admin['full_name']) ?> <small class="text-muted">(<?= $admin['email'] ?>)</small></h5>
                    <span class="badge bg-primary"><?= $admin['role'] ?></span>
                </div>
                <?php
                $currentPerms = json_decode($admin['permissions'], true) ?? [];
                $availablePerms = ['manage_users', 'manage_content', 'manage_events', 'view_reports', 'manage_settings', 'manage_donations'];
                ?>
                <form method="post">
                    <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                    <div class="mb-2"><strong>Admin Role:</strong>
                        <select name="admin_role" class="form-select form-select-sm d-inline-block ms-2" style="width:auto;">
                            <option value="super" <?= $admin['role']=='super'?'selected':'' ?>>Super Admin</option>
                            <option value="content" <?= $admin['role']=='content'?'selected':'' ?>>Content Manager</option>
                            <option value="financial" <?= $admin['role']=='financial'?'selected':'' ?>>Financial Manager</option>
                        </select>
                    </div>
                    <div class="mb-2"><strong>Permissions:</strong></div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($availablePerms as $perm): ?>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="perms[]" value="<?= $perm ?>" <?= in_array($perm, $currentPerms) ? 'checked' : '' ?>>
                                <span class="form-check-label"><?= ucwords(str_replace('_', ' ', $perm)) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="save_permissions" class="btn btn-outline-primary btn-sm mt-3">Save Permissions</button>
                </form>
            </div>
        <?php endforeach; ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>