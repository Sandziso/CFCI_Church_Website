<?php
// admin/user-management/users.php
require_once __DIR__ . '/../includes/admin_functions.php';

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query conditions
$where = ['1=1'];
$params = [];
if ($search) {
    $where[] = "(u.full_name LIKE :search OR u.email LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}
if ($role_filter) {
    $where[] = "u.role = :role";
    $params[':role'] = $role_filter;
}
if ($status_filter !== '') {
    $where[] = "u.is_active = :active";
    $params[':active'] = (int)$status_filter;
}
$whereSQL = implode(' AND ', $where);

// Total count
$countStmt = $conn->prepare("SELECT COUNT(*) FROM users u WHERE $whereSQL");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Fetch users – FIXED: use `join_date` instead of `created_at`
$query = "SELECT u.*, a.role as admin_role, a.permissions
          FROM users u
          LEFT JOIN admins a ON u.id = a.user_id
          WHERE $whereSQL
          ORDER BY u.join_date DESC
          LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle quick actions (toggle, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($_POST['action'] === 'toggle_status') {
            $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$userId]);
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
        }
        header("Location: users.php?msg=success");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a5276; --accent: #e67e22; }
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; transition: margin 0.3s; }
        .sidebar.collapsed + .admin-main { margin-left: 70px; }
        @media (max-width: 991.98px) { .admin-main { margin-left: 0 !important; } }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .table th { font-weight: 600; color: #64748b; border-top: none; }
        .btn-sm { border-radius: 30px; padding: 0.25rem 1rem; font-size: 0.8rem; }
        .badge-role { background: #e2e8f0; color: #1a5276; padding: 0.2em 0.7em; border-radius: 30px; font-weight: 500; font-size: 0.75rem; }
        .pagination .page-link { border-radius: 8px; margin: 0 3px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark">Manage Users</h4>
            <a href="add-user.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i> Add User</a>
        </div>

        <!-- Filters -->
        <form class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or email..." class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="admin" <?= $role_filter==='admin'?'selected':'' ?>>Admin</option>
                        <option value="pastor" <?= $role_filter==='pastor'?'selected':'' ?>>Pastor</option>
                        <option value="member" <?= $role_filter==='member'?'selected':'' ?>>Member</option>
                        <option value="guest" <?= $role_filter==='guest'?'selected':'' ?>>Guest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" <?= $status_filter==='1'?'selected':'' ?>>Active</option>
                        <option value="0" <?= $status_filter==='0'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge-role"><?= $u['admin_role'] ?? $u['role'] ?></span></td>
                            <td>
                                <?= $u['is_active'] 
                                    ? '<span class="text-success fw-semibold">Active</span>' 
                                    : '<span class="text-danger fw-semibold">Inactive</span>' ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['join_date'])) ?></td>
                            <td class="text-end">
                                <a href="edit-user.php?id=<?= $u['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="post" style="display:inline" onsubmit="return confirm('Toggle status?')">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="btn btn-outline-warning btn-sm"><i class="fas fa-power-off"></i></button>
                                </form>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete user permanently?')">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-3">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= $role_filter ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>