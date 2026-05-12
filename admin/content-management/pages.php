<?php
// admin/content-management/pages.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Ensure the CMS pages table exists
$conn->exec("CREATE TABLE IF NOT EXISTS cms_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    is_published TINYINT(1) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pageId = (int)($_POST['page_id'] ?? 0);
    if ($_POST['action'] === 'delete' && $pageId) {
        $stmt = $conn->prepare("DELETE FROM cms_pages WHERE id = ?");
        $stmt->execute([$pageId]);
    } elseif ($_POST['action'] === 'toggle' && $pageId) {
        $stmt = $conn->prepare("UPDATE cms_pages SET is_published = NOT is_published WHERE id = ?");
        $stmt->execute([$pageId]);
    }
    header("Location: pages.php");
    exit;
}

// Fetch all pages
$pages = $conn->query("SELECT * FROM cms_pages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pages | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; transition: margin 0.3s; }
        .sidebar.collapsed + .admin-main { margin-left: 70px; }
        @media (max-width: 991.98px) { .admin-main { margin-left: 0 !important; } }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .table th { font-weight: 600; color: #64748b; border-top: none; }
        .badge-status { padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.75rem; font-weight: 500; }
        .btn-sm { border-radius: 30px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark">Manage Pages</h4>
            <a href="edit-page.php" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle me-1"></i> New Page</a>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($page['title']) ?></td>
                            <td><code><?= htmlspecialchars($page['slug']) ?></code></td>
                            <td>
                                <?php if ($page['is_published']): ?>
                                    <span class="badge-status bg-success bg-opacity-10 text-success">Published</span>
                                <?php else: ?>
                                    <span class="badge-status bg-warning bg-opacity-10 text-warning">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($page['updated_at'])) ?></td>
                            <td class="text-end">
                                <a href="edit-page.php?id=<?= $page['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="post" style="display:inline" onsubmit="return confirm('Toggle publish status?')">
                                    <input type="hidden" name="page_id" value="<?= $page['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="btn btn-outline-info btn-sm"><i class="fas fa-eye<?= $page['is_published'] ? '-slash' : '' ?>"></i></button>
                                </form>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this page permanently?')">
                                    <input type="hidden" name="page_id" value="<?= $page['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pages)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No pages created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>