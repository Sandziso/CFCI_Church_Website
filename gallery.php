<?php
// gallery.php
require_once 'includes/header.php';

// Check if connection is available
if (!isset($conn) || $conn === null) {
    try {
        $host = 'localhost';
        $dbname = 'cfci_church_db';
        $username = 'root';
        $password = '';
        
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $local_conn = true;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        $conn = null;
    }
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uploadImage'])) {
    if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pastor')) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? 'general';
        
        if (!empty($title) && isset($_FILES['imageFile'])) {
            $uploadDir = 'uploads/gallery/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['imageFile']['name']);
            $targetFile = $uploadDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            
            // Check if image file is actual image
            $check = getimagesize($_FILES['imageFile']['tmp_name']);
            if ($check !== false) {
                // Check file size (5MB max)
                if ($_FILES['imageFile']['size'] <= 5000000) {
                    // Allow certain file formats
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($imageFileType, $allowedTypes)) {
                        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $targetFile)) {
                            try {
                                $stmt = $conn->prepare("
                                    INSERT INTO gallery (title, description, image_url, category, uploaded_by, is_published)
                                    VALUES (:title, :description, :image_url, :category, :uploaded_by, :is_published)
                                ");
                                
                                $is_published = ($_SESSION['role'] === 'admin') ? 1 : 0;
                                
                                $stmt->execute([
                                    ':title' => $title,
                                    ':description' => $description,
                                    ':image_url' => $targetFile,
                                    ':category' => $category,
                                    ':uploaded_by' => $_SESSION['user_id'],
                                    ':is_published' => $is_published
                                ]);
                                
                                $_SESSION['success_message'] = "Image uploaded successfully! " . 
                                    (($is_published == 0) ? "It will be reviewed by admin before publication." : "");
                                header("Location: gallery.php");
                                exit();
                            } catch (PDOException $e) {
                                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
                            }
                        } else {
                            $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
                    }
                } else {
                    $_SESSION['error_message'] = "Sorry, your file is too large. Maximum size is 5MB.";
                }
            } else {
                $_SESSION['error_message'] = "File is not an image.";
            }
        } else {
            $_SESSION['error_message'] = "Please fill in all required fields.";
        }
    }
}

// Filter variables
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';

// Fetch gallery images with filters
$gallery_items = [];
$categories = [];
$years = [];

if ($conn) {
    try {
        // Build query with filters
        $sql = "SELECT g.*, u.full_name as uploaded_by_name 
                FROM gallery g 
                LEFT JOIN users u ON g.uploaded_by = u.id 
                WHERE g.is_published = 1";
        
        $params = [];
        
        if ($category !== 'all') {
            $sql .= " AND g.category = ?";
            $params[] = $category;
        }
        
        if ($year !== 'all') {
            $sql .= " AND YEAR(g.created_at) = ?";
            $params[] = $year;
        }
        
        $sql .= " ORDER BY g.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get unique categories
        $cat_stmt = $conn->query("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND is_published = 1 ORDER BY category");
        $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get unique years
        $year_stmt = $conn->query("SELECT DISTINCT YEAR(created_at) as year FROM gallery WHERE is_published = 1 ORDER BY year DESC");
        $years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Gallery fetch error: " . $e->getMessage());
    }
}

// Default categories and years if database is empty
if (empty($categories)) {
    $categories = ['events', 'ministries', 'sermons', 'general'];
}

if (empty($years)) {
    $current_year = date('Y');
    $years = [$current_year, $current_year - 1, $current_year - 2];
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/gallery-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Photo Gallery</h1>
                <p class="text-white mb-0">Capturing moments of worship, fellowship, and service at CFCI</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Gallery</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-3">Church Life in Pictures</h2>
                <p class="lead">Browse through our collection of photos from services, events, ministries, and community outreach activities.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="gallery-filters bg-light p-4 rounded">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <h5 class="mb-0">Filter Gallery:</h5>
                        </div>
                        <div class="col-md-8">
                            <form id="filterForm" class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select" name="category" id="category">
                                        <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                                <?php echo ucfirst(htmlspecialchars($cat)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="year" id="year">
                                        <option value="all" <?php echo $year === 'all' ? 'selected' : ''; ?>>All Years</option>
                                        <?php foreach ($years as $y): ?>
                                            <option value="<?php echo htmlspecialchars($y); ?>" <?php echo $year === $y ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($y); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="row" id="gallery-container">
            <?php if (empty($gallery_items)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-images display-1 text-muted mb-4"></i>
                    <h3 class="mb-3">No Photos Yet</h3>
                    <p class="text-muted mb-4">Check back soon for gallery updates or upload some photos if you're an admin.</p>
                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pastor')): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload First Photo
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($gallery_items as $item): ?>
                    <?php
                    $item_date = new DateTime($item['created_at']);
                    $category_class = strtolower($item['category'] ?? 'general');
                    $image_url = $item['image_url'];
                    if (!empty($image_url) && !file_exists($image_url)) {
                        $image_url = 'assets/images/gallery/default.jpg';
                    }
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item <?php echo htmlspecialchars($category_class); ?>">
                        <div class="gallery-card">
                            <div class="gallery-image">
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="img-fluid"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                     data-description="<?php echo htmlspecialchars($item['description'] ?? ''); ?>"
                                     data-date="<?php echo $item_date->format('M d, Y'); ?>"
                                     data-category="<?php echo ucfirst($item['category'] ?? 'General'); ?>"
                                     data-image="<?php echo htmlspecialchars($image_url); ?>"
                                     data-uploader="<?php echo htmlspecialchars($item['uploaded_by_name'] ?? 'Anonymous'); ?>">
                                <div class="gallery-overlay">
                                    <div class="overlay-content">
                                        <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <div class="image-meta">
                                            <span class="badge bg-primary"><?php echo ucfirst($item['category'] ?? 'General'); ?></span>
                                            <span class="text-light"><?php echo $item_date->format('M d, Y'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- No Results Message -->
        <?php if (!empty($gallery_items) && empty(array_filter($gallery_items, function($item) use ($category, $year) {
            if ($category !== 'all' && $item['category'] !== $category) return false;
            if ($year !== 'all' && date('Y', strtotime($item['created_at'])) != $year) return false;
            return true;
        }))): ?>
            <div class="text-center py-5">
                <i class="fas fa-images display-1 text-muted mb-4"></i>
                <h3 class="mb-3">No Photos Found</h3>
                <p class="text-muted mb-4">Try selecting different filters or check back later for new uploads.</p>
                <button onclick="resetFilters()" class="btn btn-primary">Reset Filters</button>
            </div>
        <?php endif; ?>

        <!-- Upload Call to Action -->
        <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pastor')): ?>
            <div class="upload-cta bg-light p-5 rounded mt-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Share Your Photos</h3>
                        <p class="mb-0">Have photos from church events? Upload them to share with the church family.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload Photos
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <img id="modalImage" src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-lg-4">
                        <div class="image-info">
                            <div class="info-item mb-3">
                                <h6>Description</h6>
                                <p id="modalDescription" class="small"></p>
                            </div>
                            <div class="info-item mb-3">
                                <h6>Category</h6>
                                <span id="modalCategory" class="badge bg-primary"></span>
                            </div>
                            <div class="info-item mb-3">
                                <h6>Uploaded By</h6>
                                <p id="modalUploader" class="small mb-0"></p>
                            </div>
                            <div class="info-item mb-3">
                                <h6>Date</h6>
                                <p id="modalDate" class="small mb-0"></p>
                            </div>
                            <div class="info-item">
                                <h6>Share</h6>
                                <div class="share-buttons d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="downloadImage()">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="shareImage()">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal (Admin/Pastor Only) -->
<?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pastor')): ?>
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Gallery Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="modal-body">
                    <input type="hidden" name="uploadImage" value="1">
                    <div class="mb-3">
                        <label for="imageTitle" class="form-label">Image Title *</label>
                        <input type="text" class="form-control" id="imageTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="imageDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="imageDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="imageCategory" class="form-label">Category *</label>
                        <select class="form-select" id="imageCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="events">Events</option>
                            <option value="ministries">Ministries</option>
                            <option value="sermons">Sermons</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="imageFile" class="form-label">Image File *</label>
                        <input type="file" class="form-control" id="imageFile" name="imageFile" accept="image/*" required>
                        <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF, WEBP</div>
                        <div id="filePreview" class="mt-3" style="display: none;">
                            <img id="previewImage" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i>
                        Uploaded images will <?php echo ($_SESSION['role'] === 'admin') ? 'be published immediately.' : 'be reviewed before being published to the gallery.'; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadButton">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Upload Image
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.gallery-card {
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.gallery-image {
    position: relative;
    height: 250px;
    overflow: hidden;
    border-radius: 10px;
    cursor: pointer;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.gallery-card:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 20px;
    opacity: 0;
    transition: all 0.3s ease;
}

.gallery-card:hover .gallery-overlay {
    opacity: 1;
}

.overlay-content h5 {
    font-size: 1rem;
    margin-bottom: 5px;
    font-weight: 600;
}

.image-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
}

.image-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    height: 100%;
}

.info-item h6 {
    font-size: 0.9rem;
    color: #1a5276;
    margin-bottom: 5px;
    font-weight: 600;
}

#imageModal .modal-dialog {
    max-width: 900px;
}

#imageModal .modal-body img {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: contain;
}

.upload-cta {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
    border: 2px dashed #1a5276;
}

.gallery-filters {
    border: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .gallery-image {
        height: 200px;
    }
    
    .gallery-overlay {
        opacity: 1;
        padding: 10px;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
    }
    
    .overlay-content h5 {
        font-size: 0.9rem;
    }
    
    .image-meta {
        font-size: 0.75rem;
    }
    
    #imageModal .modal-dialog {
        margin: 10px;
    }
    
    #imageModal .modal-body .row {
        flex-direction: column;
    }
    
    .upload-cta {
        padding: 20px !important;
    }
}
</style>

<script>
// Filter form submission
document.getElementById('category').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('year').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Image Modal
const imageModal = document.getElementById('imageModal');
if (imageModal) {
    imageModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const date = button.getAttribute('data-date');
        const category = button.getAttribute('data-category');
        const image = button.getAttribute('data-image');
        const uploader = button.getAttribute('data-uploader');
        
        const modalTitle = imageModal.querySelector('.modal-title');
        const modalImage = imageModal.querySelector('#modalImage');
        const modalDescription = imageModal.querySelector('#modalDescription');
        const modalDate = imageModal.querySelector('#modalDate');
        const modalCategory = imageModal.querySelector('#modalCategory');
        const modalUploader = imageModal.querySelector('#modalUploader');
        
        modalTitle.textContent = title;
        modalImage.src = image;
        modalImage.alt = title;
        modalDescription.textContent = description || 'No description available.';
        modalDate.textContent = date;
        modalCategory.textContent = category;
        modalUploader.textContent = uploader;
    });
}

function resetFilters() {
    document.getElementById('category').value = 'all';
    document.getElementById('year').value = 'all';
    document.getElementById('filterForm').submit();
}

function downloadImage() {
    const imageUrl = document.getElementById('modalImage').src;
    const link = document.createElement('a');
    link.href = imageUrl;
    link.download = 'cfci-gallery-image.jpg';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function shareImage() {
    if (navigator.share) {
        const title = document.querySelector('#imageModalLabel').textContent;
        const description = document.querySelector('#modalDescription').textContent;
        const imageUrl = document.querySelector('#modalImage').src;
        
        navigator.share({
            title: title,
            text: description.substring(0, 100) + '...',
            url: window.location.href,
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        }).catch(err => {
            console.error('Could not copy text: ', err);
        });
    }
}

// File preview for upload
document.getElementById('imageFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.style.display = 'block';
            previewImage.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
        
        // Validate file size
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            e.target.value = '';
            preview.style.display = 'none';
        }
    } else {
        preview.style.display = 'none';
    }
});

// Upload form validation
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    const fileInput = document.getElementById('imageFile');
    const titleInput = document.getElementById('imageTitle');
    const categoryInput = document.getElementById('imageCategory');
    
    // Reset previous errors
    [fileInput, titleInput, categoryInput].forEach(input => {
        input.classList.remove('is-invalid');
    });
    
    let isValid = true;
    
    // Validate file
    if (!fileInput.files[0]) {
        fileInput.classList.add('is-invalid');
        isValid = false;
    } else {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(fileInput.files[0].type)) {
            fileInput.classList.add('is-invalid');
            fileInput.nextElementSibling.textContent = 'Please select a valid image file (JPG, PNG, GIF, WEBP).';
            isValid = false;
        }
    }
    
    // Validate title
    if (!titleInput.value.trim()) {
        titleInput.classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate category
    if (!categoryInput.value) {
        categoryInput.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    } else {
        // Show loading state
        const uploadButton = document.getElementById('uploadButton');
        uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Uploading...';
        uploadButton.disabled = true;
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
        }
    });
});

// Image lazy loading
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.gallery-image img');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => {
        const dataSrc = img.getAttribute('data-src');
        if (dataSrc) {
            img.src = 'assets/images/placeholder.jpg';
            imageObserver.observe(img);
        }
    });
});
</script>

<?php
// Close database connection if created locally
if (isset($local_conn) && $local_conn) {
    $conn = null;
}
require_once 'includes/footer.php';
?>