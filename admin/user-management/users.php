<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pastor') {
    header('Location: ../../login.php?redirect=admin');
    exit();
}

$db = new ChurchDB($conn);
$users = $db->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CFCI Admin</title>
    <!-- Include CSS from dashboard -->
</head>
<body>
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/admin_topbar.php'; ?>
        
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col">
                <h2 class="h4">User Management</h2>
                <p class="text-muted mb-0">Manage church members, pastors, and administrators</p>
            </div>
            <div class="col-auto">
                <a href="add-user.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>
        </div>
        
        <!-- User Management Card -->
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">All Users</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Search users..." id="searchUsers">
                    <select class="form-select form-select-sm" style="width: 150px;" id="filterRole">
                        <option value="">All Roles</option>
                        <option value="pastor">Pastors</option>
                        <option value="member">Members</option>
                        <option value="admin">Admins</option>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $user['avatar_url'] ?? 'https://via.placeholder.com/40'; ?>" 
                                         class="user-avatar me-3" alt="User">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'pastor' ? 'primary' : ($user['role'] == 'admin' ? 'danger' : 'info'); ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['join_date'])); ?></td>
                            <td>
                                <?php if($user['last_login']): ?>
                                <small><?php echo time_elapsed_string($user['last_login']); ?> ago</small>
                                <?php else: ?>
                                <small class="text-muted">Never</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    
    <script>
        // Search and filter functionality
        document.getElementById('searchUsers').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        document.getElementById('filterRole').addEventListener('change', function(e) {
            const filter = e.target.value;
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                const role = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (!filter || role.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // User actions
        function viewUser(id) {
            window.location.href = `view-user.php?id=${id}`;
        }
        
        function editUser(id) {
            window.location.href = `edit-user.php?id=${id}`;
        }
        
        function deleteUser(id) {
            if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch(`../../api/users.php?action=delete&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>