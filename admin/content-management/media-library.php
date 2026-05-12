<?php
// admin/content-management/media-library.php
require_once __DIR__ . '/../includes/admin_functions.php';

$uploadDir = ROOT_PATH . 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'mp4', 'mp3'];
        if (in_array($ext, $allowed) && $file['size'] <= MAX_FILE_SIZE) {
            $newName = uniqid('media_', true) . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $message = '<div class="alert alert-success">File uploaded successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to move uploaded file.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid file type or too large (max 5MB).</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Upload error code: ' . $file['error'] . '</div>';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $fullPath = $uploadDir . $fileToDelete;
    if (file_exists($fullPath) && is_file($fullPath)) {
        unlink($fullPath);
        header("Location: media-library.php?msg=deleted");
        exit;
    }
}

// List files
$files = [];
if ($handle = opendir($uploadDir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_file($uploadDir . $entry)) {
            $files[] = $entry;
        }
    }
    closedir($handle);
}
sort($files);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Media Library | CFCI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; margin-left: 260px; padding: 1.5rem 2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: none; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
        .file-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; transition: 0.2s; }
        .file-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .file-preview { height: 120px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .file-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-preview i { font-size: 2.5rem; color: #94a3b8; }
        .file-info { padding: 0.75rem; }
        .file-name { font-size: 0.8rem; word-break: break-all; font-weight: 500; }
        .file-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; }
        .btn-xs { padding: 0.15rem 0.5rem; font-size: 0.7rem; border-radius: 20px; }
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 2rem; text-align: center; background: #f8fafc; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <main class="admin-main">
        <?php include __DIR__ . '/../includes/admin_topbar.php'; ?>
        <h4 class="fw-bold mb-4">Media Library</h4>

        <?= $message ?>

        <!-- Upload Form -->
        <div class="card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Upload New File</h5>
            <form method="post" enctype="multipart/form-data">
                <div class="upload-area mb-3">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Drag & drop or click to select</p>
                    <input type="file" name="file" id="fileInput" class="d-none" onchange="updateFileName()">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click()">Choose File</button>
                    <div id="fileName" class="mt-2 fw-semibold text-dark"></div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload</button>
            </form>
        </div>

        <!-- File Grid -->
        <div class="card p-3">
            <h5 class="fw-semibold mb-3">Files (<?= count($files) ?>)</h5>
            <?php if (empty($files)): ?>
                <p class="text-muted">No files uploaded yet.</p>
            <?php else: ?>
                <div class="file-grid">
                    <?php foreach ($files as $file):
                        $path = 'uploads/' . $file;
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
                    ?>
                    <div class="file-card">
                        <div class="file-preview">
                            <?php if ($isImage): ?>
                                <img src="<?= SITE_URL . $path ?>" alt="<?= $file ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-file"></i>
                            <?php endif; ?>
                        </div>
                        <div class="file-info">
                            <div class="file-name"><?= htmlspecialchars($file) ?></div>
                            <div class="file-actions">
                                <a href="<?= SITE_URL . $path ?>" target="_blank" class="btn btn-outline-secondary btn-xs"><i class="fas fa-eye"></i></a>
                                <a href="?delete=<?= urlencode($file) ?>" class="btn btn-outline-danger btn-xs" onclick="return confirm('Delete this file?')"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function updateFileName() {
    const input = document.getElementById('fileInput');
    const nameDiv = document.getElementById('fileName');
    if (input.files.length > 0) {
        nameDiv.textContent = input.files[0].name;
    } else {
        nameDiv.textContent = '';
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>