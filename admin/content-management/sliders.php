<?php
// admin/content-management/sliders.php
require_once __DIR__ . '/../includes/admin_functions.php';

// Ensure sliders table exists
$conn->exec("CREATE TABLE IF NOT EXISTS sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    image_url VARCHAR(500) NOT NULL,
    cta_text VARCHAR(100) DEFAULT NULL,
    cta_link VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$msg = '';

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $sliderId = $_POST['slider_id'] ? (int)$_POST['slider_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image_url = trim($_POST['image_url']);
    $cta_text = trim($_POST['cta_text'] ?? '');
    $cta_link = trim($_POST['cta_link'] ?? '');
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($image_url)) {
        $msg = '<div class="alert alert-danger">Image URL is required.</div>';
    } else {
        if ($sliderId) {
            $stmt = $conn->prepare("UPDATE sliders SET title=?, subtitle=?, image_url=?, cta_text=?, cta_link=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $image_url, $cta_text, $cta_link, $sort_order, $is_active, $sliderId]);
            $msg = '<div class="alert alert-success">Slider updated.</div>';
        } else {
            $stmt = $conn->prepare("INSERT INTO sliders (title, subtitle, image_url, cta_text, cta_link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $image_url, $cta_text, $cta_link, $sort_order, $is_active]);
            $msg = '<div class="alert alert-success">Slider added.</div>';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM sliders WHERE id = ?")->execute([$delId]);
    header("Location: sliders.php?msg=deleted");
    exit;
}

// Fetch all sliders
$sliders = $conn->query("SELECT * FROM sliders ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sliders | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .slider-preview { width: 200px; height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; }
        .badge-status { padding: 0.2em 0.8em; border-radius: 30px; font-size: 0.75rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Manage Homepage Sliders</h4>
        <?= $msg ?>

        <!-- Add/Edit Form -->
        <div class="card p-4 mb-4">
            <h5 class="fw-semibold mb-3" id="formTitle">Add New Slide</h5>
            <form method="post" id="sliderForm">
                <input type="hidden" name="slider_id" id="sliderId" value="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image URL <span class="text-danger">*</span></label>
                        <input type="text" name="image_url" id="image_url" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CTA Text</label>
                        <input type="text" name="cta_text" id="cta_text" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CTA Link</label>
                        <input type="text" name="cta_link" id="cta_link" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Slide</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">Clear Form</button>
                </div>
            </form>
        </div>

        <!-- Existing Sliders -->
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title / Subtitle</th>
                            <th>CTA</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sliders as $s): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($s['image_url']) ?>" alt="" class="slider-preview"></td>
                            <td>
                                <strong><?= htmlspecialchars($s['title'] ?? '') ?></strong>
                                <?php if ($s['subtitle']): ?><br><small class="text-muted"><?= htmlspecialchars($s['subtitle']) ?></small><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['cta_text'] ?? '') ?> <br><small class="text-muted"><?= htmlspecialchars($s['cta_link'] ?? '') ?></small></td>
                            <td><?= $s['sort_order'] ?></td>
                            <td>
                                <?php if ($s['is_active']): ?>
                                    <span class="badge-status bg-success bg-opacity-10 text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge-status bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="editSlider(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete=<?= $s['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this slider?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sliders)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No sliders created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
function editSlider(slider) {
    document.getElementById('formTitle').textContent = 'Edit Slide';
    document.getElementById('sliderId').value = slider.id;
    document.getElementById('title').value = slider.title || '';
    document.getElementById('subtitle').value = slider.subtitle || '';
    document.getElementById('image_url').value = slider.image_url;
    document.getElementById('cta_text').value = slider.cta_text || '';
    document.getElementById('cta_link').value = slider.cta_link || '';
    document.getElementById('sort_order').value = slider.sort_order;
    document.getElementById('is_active').checked = slider.is_active == 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function clearForm() {
    document.getElementById('formTitle').textContent = 'Add New Slide';
    document.getElementById('sliderId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('subtitle').value = '';
    document.getElementById('image_url').value = '';
    document.getElementById('cta_text').value = '';
    document.getElementById('cta_link').value = '';
    document.getElementById('sort_order').value = '0';
    document.getElementById('is_active').checked = true;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>