<?php
require_once 'includes/header.php';

// Variables for filtering and pagination
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Fetch resources
$resources = [];
$total_resources = 0;
$categories = ['bible-study', 'teaching', 'prayer', 'family', 'leadership', 'worship'];
$types = ['document', 'video', 'audio', 'link'];

try {
    // Build query based on filters
    $sql = "SELECT r.*, u.full_name as author_name 
            FROM resources r 
            LEFT JOIN users u ON r.author_id = u.id 
            WHERE r.is_published = 1";
    
    $count_sql = "SELECT COUNT(*) as total FROM resources WHERE is_published = 1";
    $params = [];
    
    if ($category !== 'all') {
        $sql .= " AND r.category = ?";
        $count_sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($type !== 'all') {
        $sql .= " AND r.resource_type = ?";
        $count_sql .= " AND resource_type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
    
    // Get total count
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_resources = $count_stmt->fetch(PDO::FETCH_COLUMN);
    
    // Get paginated results
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Resources fetch error: " . $e->getMessage());
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Resources</h1>
                <p class="text-white mb-0">Tools for spiritual growth and development</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Resources</li>
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
                <h2 class="mb-3">Grow in Your Faith Journey</h2>
                <p class="lead">Access free resources including Bible studies, teaching materials, prayer guides, and more to help you grow in your relationship with God.</p>
            </div>
        </div>

        <!-- Quick Access Cards -->
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-4">
                <a href="#bible-studies" class="resource-category-card">
                    <div class="category-icon">
                        <i class="fas fa-bible"></i>
                    </div>
                    <h5>Bible Studies</h5>
                    <p class="small">In-depth studies of Scripture</p>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <a href="#prayer-guides" class="resource-category-card">
                    <div class="category-icon">
                        <i class="fas fa-pray"></i>
                    </div>
                    <h5>Prayer Guides</h5>
                    <p class="small">Guides for effective prayer</p>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <a href="#teaching-materials" class="resource-category-card">
                    <div class="category-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h5>Teaching Materials</h5>
                    <p class="small">Sermon notes and outlines</p>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <a href="#family-resources" class="resource-category-card">
                    <div class="category-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h5>Family Resources</h5>
                    <p class="small">For strong Christian families</p>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="resource-filters bg-light p-4 rounded">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <h5 class="mb-0">Filter Resources:</h5>
                        </div>
                        <div class="col-md-9">
                            <form id="filterForm" class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select" name="category" id="categoryFilter">
                                        <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                                <?php echo ucwords(str_replace('-', ' ', $cat)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="type" id="typeFilter">
                                        <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All Types</option>
                                        <?php foreach ($types as $t): ?>
                                            <option value="<?php echo $t; ?>" <?php echo $type === $t ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($t); ?>
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

        <!-- Resources Grid -->
        <div class="row" id="resources-container">
            <?php if (!empty($resources)): ?>
                <?php foreach ($resources as $resource): ?>
                    <?php
                    $resource_date = new DateTime($resource['created_at']);
                    $file_extension = strtolower(pathinfo($resource['file_url'], PATHINFO_EXTENSION));
                    $icon_class = get_resource_icon($resource['resource_type'], $file_extension);
                    $file_size = $resource['file_size'] ? format_file_size($resource['file_size']) : '';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="resource-card h-100">
                            <div class="resource-header d-flex justify-content-between align-items-start mb-3">
                                <div class="resource-type-icon">
                                    <i class="<?php echo $icon_class; ?>"></i>
                                </div>
                                <div>
                                    <span class="badge bg-primary"><?php echo ucwords(str_replace('-', ' ', $resource['category'])); ?></span>
                                </div>
                            </div>
                            
                            <h5 class="mb-3"><?php echo htmlspecialchars($resource['title']); ?></h5>
                            
                            <p class="resource-description small mb-4"><?php echo htmlspecialchars(substr($resource['description'], 0, 100)); ?>...</p>
                            
                            <div class="resource-footer d-flex justify-content-between align-items-center">
                                <div class="resource-meta">
                                    <div class="resource-author small text-muted">
                                        <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($resource['author_name'] ?? 'CFCI'); ?>
                                    </div>
                                    <div class="resource-date small text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> <?php echo $resource_date->format('M d, Y'); ?>
                                    </div>
                                </div>
                                <div class="resource-actions">
                                    <?php if ($resource['resource_type'] === 'link'): ?>
                                        <a href="<?php echo htmlspecialchars($resource['file_url']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt me-1"></i> Visit
                                        </a>
                                    <?php else: ?>
                                        <button onclick="downloadResource(<?php echo $resource['id']; ?>)" 
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i> Download
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default resources if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="resource-card h-100">
                        <div class="resource-header d-flex justify-content-between align-items-start mb-3">
                            <div class="resource-type-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <span class="badge bg-primary">Bible Study</span>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Romans 8 Study Guide</h5>
                        
                        <p class="resource-description small mb-4">A comprehensive study guide for Romans chapter 8 covering themes of victory in Christ.</p>
                        
                        <div class="resource-footer d-flex justify-content-between align-items-center">
                            <div class="resource-meta">
                                <div class="resource-author small text-muted">
                                    <i class="fas fa-user me-1"></i> Pastor Sarah
                                </div>
                                <div class="resource-date small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> Jun 15, 2025
                                </div>
                            </div>
                            <div class="resource-actions">
                                <button onclick="downloadResource(1)" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="resource-card h-100">
                        <div class="resource-header d-flex justify-content-between align-items-start mb-3">
                            <div class="resource-type-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div>
                                <span class="badge bg-primary">Teaching</span>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">How to Study the Bible</h5>
                        
                        <p class="resource-description small mb-4">Video tutorial on effective Bible study methods and tools for personal growth.</p>
                        
                        <div class="resource-footer d-flex justify-content-between align-items-center">
                            <div class="resource-meta">
                                <div class="resource-author small text-muted">
                                    <i class="fas fa-user me-1"></i> Bishop Zakes
                                </div>
                                <div class="resource-date small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> Jun 8, 2025
                                </div>
                            </div>
                            <div class="resource-actions">
                                <a href="https://www.youtube.com/watch?v=example" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> Watch
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="resource-card h-100">
                        <div class="resource-header d-flex justify-content-between align-items-start mb-3">
                            <div class="resource-type-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <span class="badge bg-primary">Prayer</span>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">30-Day Prayer Guide</h5>
                        
                        <p class="resource-description small mb-4">Daily prayer prompts and Scriptures for a month of focused prayer and spiritual growth.</p>
                        
                        <div class="resource-footer d-flex justify-content-between align-items-center">
                            <div class="resource-meta">
                                <div class="resource-author small text-muted">
                                    <i class="fas fa-user me-1"></i> Prayer Team
                                </div>
                                <div class="resource-date small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> Jun 1, 2025
                                </div>
                            </div>
                            <div class="resource-actions">
                                <button onclick="downloadResource(3)" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_resources > $limit): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Resources pagination">
                        <ul class="pagination justify-content-center">
                            <?php
                            $total_pages = ceil($total_resources / $limit);
                            
                            // Previous button
                            if ($page > 1) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?category=' . $category . '&type=' . $type . '&page=' . ($page - 1) . '" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>';
                            }
                            
                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = $i == $page ? 'active' : '';
                                echo '<li class="page-item ' . $active . '">
                                    <a class="page-link" href="?category=' . $category . '&type=' . $type . '&page=' . $i . '">' . $i . '</a>
                                </li>';
                            }
                            
                            // Next button
                            if ($page < $total_pages) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?category=' . $category . '&type=' . $type . '&page=' . ($page + 1) . '" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

        <!-- Featured Resources -->
        <div class="featured-resources bg-light p-5 rounded mt-5">
            <h3 class="text-center mb-4">Featured Resources</h3>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="featured-resource-card text-center p-4 h-100">
                        <div class="featured-icon mb-3">
                            <i class="fas fa-book"></i>
                        </div>
                        <h4 class="mb-3">Daily Devotional</h4>
                        <p class="mb-4">Start your day with God's Word. Get our free daily devotional delivered to your email.</p>
                        <form class="devotional-form">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="featured-resource-card text-center p-4 h-100">
                        <div class="featured-icon mb-3">
                            <i class="fas fa-podcast"></i>
                        </div>
                        <h4 class="mb-3">Sermon Podcast</h4>
                        <p class="mb-4">Listen to our latest sermons on your favorite podcast platform.</p>
                        <div class="podcast-platforms">
                            <a href="#" class="btn btn-outline-primary btn-sm m-1">
                                <i class="fab fa-spotify"></i> Spotify
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm m-1">
                                <i class="fab fa-apple"></i> Apple Podcasts
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm m-1">
                                <i class="fab fa-google"></i> Google Podcasts
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="featured-resource-card text-center p-4 h-100">
                        <div class="featured-icon mb-3">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="mb-3">Church App</h4>
                        <p class="mb-4">Download our church app for instant access to resources, event registration, and giving.</p>
                        <div class="app-download">
                            <a href="#" class="btn btn-outline-primary btn-sm m-1">
                                <i class="fab fa-app-store-ios"></i> App Store
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm m-1">
                                <i class="fab fa-google-play"></i> Play Store
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resource Categories -->
        <div class="resource-categories mt-5 pt-5 border-top">
            <h3 class="text-center mb-4">Browse by Category</h3>
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <a href="?category=bible-study" class="category-link">
                        <div class="category-card text-center p-3">
                            <i class="fas fa-bible mb-2"></i>
                            <h5>Bible Studies</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="?category=prayer" class="category-link">
                        <div class="category-card text-center p-3">
                            <i class="fas fa-pray mb-2"></i>
                            <h5>Prayer Guides</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="?category=family" class="category-link">
                        <div class="category-card text-center p-3">
                            <i class="fas fa-home mb-2"></i>
                            <h5>Family Resources</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="?category=teaching" class="category-link">
                        <div class="category-card text-center p-3">
                            <i class="fas fa-chalkboard-teacher mb-2"></i>
                            <h5>Teaching Materials</h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper functions
function get_resource_icon($type, $extension) {
    switch ($type) {
        case 'document':
            switch ($extension) {
                case 'pdf': return 'fas fa-file-pdf';
                case 'doc':
                case 'docx': return 'fas fa-file-word';
                case 'xls':
                case 'xlsx': return 'fas fa-file-excel';
                case 'ppt':
                case 'pptx': return 'fas fa-file-powerpoint';
                default: return 'fas fa-file-alt';
            }
        case 'video': return 'fas fa-video';
        case 'audio': return 'fas fa-file-audio';
        case 'link': return 'fas fa-external-link-alt';
        default: return 'fas fa-file';
    }
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.resource-category-card {
    display: block;
    text-align: center;
    padding: 20px 15px;
    background: white;
    border-radius: 10px;
    border: 1px solid #eee;
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
    height: 100%;
}

.resource-category-card:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.resource-category-card:hover .category-icon i {
    color: white;
}

.category-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.category-icon i {
    color: var(--primary);
}

.resource-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.resource-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.resource-type-icon {
    width: 50px;
    height: 50px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.resource-type-icon i {
    font-size: 1.8rem;
    color: var(--primary);
}

.resource-description {
    color: var(--text);
    line-height: 1.6;
}

.featured-resource-card {
    background: white;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
}

.featured-resource-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.featured-icon {
    font-size: 3rem;
    color: var(--primary);
}

.podcast-platforms, .app-download {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

.category-link {
    text-decoration: none;
    color: inherit;
}

.category-card {
    border: 1px solid #eee;
    border-radius: 10px;
    transition: var(--transition);
}

.category-card:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
}

.category-card i {
    font-size: 2rem;
}

.resource-filters {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .resource-category-card {
        padding: 15px 10px;
    }
    
    .category-icon {
        font-size: 2rem;
    }
    
    .resource-card {
        padding: 20px;
    }
    
    .resource-type-icon {
        width: 40px;
        height: 40px;
    }
    
    .resource-type-icon i {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Filter form submission
document.getElementById('categoryFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('typeFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Resource download simulation
function downloadResource(resourceId) {
    // Simulate download
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-1"></i> Downloaded';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        // Reset button after 3 seconds
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-download me-1"></i> Download';
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
            button.disabled = false;
        }, 3000);
        
        alert('Resource downloaded successfully!');
    }, 1500);
}

// Devotional subscription
document.querySelector('.devotional-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = this.querySelector('input[type="email"]').value;
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        alert(`Thank you for subscribing to our daily devotional! Check your email at ${email} for confirmation.`);
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1500);
});

// Smooth scroll to categories
document.querySelectorAll('.resource-category-card').forEach(card => {
    card.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId.startsWith('#')) {
            document.querySelector(targetId)?.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>