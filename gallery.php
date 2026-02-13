<?php
// gallery.php
require_once 'includes/header.php';

// Check if connection is available
if (!isset($conn) || $conn === null) {
    try {
        $host = 'localhost';
        $dbname = 'cfci_church';
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
        $cat_stmt = $conn->query("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL ORDER BY category");
        $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get unique years
        $year_stmt = $conn->query("SELECT DISTINCT YEAR(created_at) as year FROM gallery ORDER BY year DESC");
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
                <!-- Default gallery items if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4 gallery-item events">
                    <div class="gallery-card">
                        <div class="gallery-image">
                            <img src="assets/images/gallery/worship-service.jpg" 
                                 alt="Sunday Worship Service" 
                                 class="img-fluid"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal"
                                 data-title="Sunday Worship Service"
                                 data-description="Our vibrant Sunday worship service with heartfelt praise and worship."
                                 data-date="Jun 15, 2025"
                                 data-category="Events"
                                 data-image="assets/images/gallery/worship-service.jpg">
                            <div class="gallery-overlay">
                                <div class="overlay-content">
                                    <h5>Sunday Worship Service</h5>
                                    <div class="image-meta">
                                        <span class="badge bg-primary">Events</span>
                                        <span class="text-light">Jun 15, 2025</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 gallery-item ministries">
                    <div class="gallery-card">
                        <div class="gallery-image">
                            <img src="assets/images/gallery/youth-camp.jpg" 
                                 alt="Youth Ministry Camp" 
                                 class="img-fluid"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal"
                                 data-title="Youth Ministry Camp"
                                 data-description="Annual youth camping trip with worship, games, and spiritual growth activities."
                                 data-date="Jun 8, 2025"
                                 data-category="Ministries"
                                 data-image="assets/images/gallery/youth-camp.jpg">
                            <div class="gallery-overlay">
                                <div class="overlay-content">
                                    <h5>Youth Ministry Camp</h5>
                                    <div class="image-meta">
                                        <span class="badge bg-primary">Ministries</span>
                                        <span class="text-light">Jun 8, 2025</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 gallery-item sermons">
                    <div class="gallery-card">
                        <div class="gallery-image">
                            <img src="assets/images/gallery/bishop-teaching.jpg" 
                                 alt="Bishop Teaching" 
                                 class="img-fluid"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal"
                                 data-title="Bishop Teaching"
                                 data-description="Bishop Zakes Nxumalo delivering a powerful message during Sunday service."
                                 data-date="Jun 1, 2025"
                                 data-category="Sermons"
                                 data-image="assets/images/gallery/bishop-teaching.jpg">
                            <div class="gallery-overlay">
                                <div class="overlay-content">
                                    <h5>Bishop Teaching</h5>
                                    <div class="image-meta">
                                        <span class="badge bg-primary">Sermons</span>
                                        <span class="text-light">Jun 1, 2025</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($gallery_items as $item): ?>
                    <?php
                    $item_date = new DateTime($item['created_at']);
                    $category_class = strtolower($item['category'] ?? 'general');
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item <?php echo htmlspecialchars($category_class); ?>">
                        <div class="gallery-card">
                            <div class="gallery-image">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'assets/images/gallery/default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="img-fluid"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                     data-description="<?php echo htmlspecialchars($item['description'] ?? ''); ?>"
                                     data-date="<?php echo $item_date->format('M d, Y'); ?>"
                                     data-category="<?php echo ucfirst($item['category'] ?? 'General'); ?>"
                                     data-image="<?php echo htmlspecialchars($item['image_url'] ?: 'assets/images/gallery/default.jpg'); ?>">
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
        <?php if (empty($gallery_items)): ?>
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
            <div class="modal-body">
                <form id="uploadForm">
                    <div class="mb-3">
                        <label for="imageTitle" class="form-label">Image Title *</label>
                        <input type="text" class="form-control" id="imageTitle" name="imageTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="imageDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="imageDescription" name="imageDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="imageCategory" class="form-label">Category *</label>
                        <select class="form-select" id="imageCategory" name="imageCategory" required>
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
                        <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</div>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i>
                        Uploaded images will be reviewed before being published to the gallery.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitUpload()">Upload Image</button>
            </div>
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
}

.gallery-card:hover {
    transform: translateY(-5px);
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
}

.info-item h6 {
    font-size: 0.9rem;
    color: #1a5276;
    margin-bottom: 5px;
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
        
        const modalTitle = imageModal.querySelector('.modal-title');
        const modalImage = imageModal.querySelector('#modalImage');
        const modalDescription = imageModal.querySelector('#modalDescription');
        const modalDate = imageModal.querySelector('#modalDate');
        const modalCategory = imageModal.querySelector('#modalCategory');
        
        modalTitle.textContent = title;
        modalImage.src = image;
        modalImage.alt = title;
        modalDescription.textContent = description || 'No description available.';
        modalDate.textContent = date;
        modalCategory.textContent = category;
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
        navigator.share({
            title: document.querySelector('#imageModalLabel').textContent,
            text: document.querySelector('#modalDescription').textContent,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
}

// Upload functionality (admin/pastor only)
function submitUpload() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    
    // Validate file size (simulated)
    const fileInput = document.getElementById('imageFile');
    if (fileInput.files[0] && fileInput.files[0].size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit.');
        return;
    }
    
    // Simulate upload
    const button = document.querySelector('#uploadModal .btn-primary');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    button.disabled = true;
    
    setTimeout(() => {
        alert('Image uploaded successfully! It will be reviewed before publication.');
        form.reset();
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
        modal.hide();
        
        // Refresh page to show new image (simulated)
        setTimeout(() => location.reload(), 1000);
    }, 2000);
}

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
</script>

<?php
// Close database connection if created locally
if (isset($local_conn) && $local_conn) {
    $conn = null;
}
require_once 'includes/footer.php';
?>