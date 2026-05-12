<?php
// admin/content-management/edit-page.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Ensure table exists
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

$pageId = $_GET['id'] ?? null;
$isEdit = !empty($pageId);
$page = ['title' => '', 'slug' => '', 'content' => '', 'meta_title' => '', 'meta_description' => '', 'is_published' => 1];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM cms_pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$page) {
        die("Page not found.");
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Auto-generate slug if empty
    if (empty($slug) && !empty($title)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }

    if (empty($title) || empty($content)) {
        $errors[] = 'Title and content are required.';
    } else {
        // Check slug uniqueness (except current page)
        $stmt = $conn->prepare("SELECT id FROM cms_pages WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $isEdit ? $pageId : 0]);
        if ($stmt->fetch()) {
            $errors[] = 'This slug is already taken. Please choose a different one.';
        }

        if (empty($errors)) {
            if ($isEdit) {
                $stmt = $conn->prepare("UPDATE cms_pages SET title=?, slug=?, content=?, meta_title=?, meta_description=?, is_published=? WHERE id=?");
                $stmt->execute([$title, $slug, $content, $meta_title, $meta_description, $is_published, $pageId]);
            } else {
                $stmt = $conn->prepare("INSERT INTO cms_pages (title, slug, content, meta_title, meta_description, is_published) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $meta_title, $meta_description, $is_published]);
            }
            $success = 'Page saved successfully.';
            if (!$isEdit) {
                // Redirect to edit page after creation
                $newId = $conn->lastInsertId();
                header("Location: edit-page.php?id=$newId&msg=created");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Page | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#page_content',
            height: 400,
            menubar: false,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            branding: false
        });
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .form-label { font-weight: 500; }
        .note-editor { border: 1px solid #e2e8f0; border-radius: 8px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4"><?= $isEdit ? 'Edit Page' : 'New Page' ?></h4>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="post">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Page Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($page['slug']) ?>" placeholder="auto-generated if empty">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea id="page_content" name="content"><?= htmlspecialchars($page['content']) ?></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Meta Title (SEO)</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($page['meta_title']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($page['meta_description']) ?></textarea>
                    </div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" <?= $page['is_published'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_published">Publish</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Page</button>
                    <a href="pages.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>