<?php
// blog.php - Blog Listing Page

// 1. DATABASE LOGIC FIRST
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

// 2. FETCH BLOG POSTS DATA
$posts_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : '';
$author = isset($_GET['author']) ? $_GET['author'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = ($page - 1) * $posts_per_page;

$blog_posts = [];
$total_posts = 0;
$total_pages = 1;
$categories = [];
$featured_posts = [];

if ($conn) {
    try {
        // Build WHERE clause
        $where_conditions = ["bp.status = 'published'"];
        $params = [];
        
        if ($category) {
            $where_conditions[] = "bp.category = ?";
            $params[] = $category;
        }
        
        if ($author) {
            $where_conditions[] = "u.full_name LIKE ?";
            $params[] = "%$author%";
        }
        
        if ($search) {
            $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ? OR bp.category LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        // Fetch featured posts (latest 2)
        $featured_stmt = $conn->prepare("
            SELECT bp.*, u.full_name as author_name
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE bp.is_published = 1 
            ORDER BY bp.created_at DESC 
            LIMIT 2
        ");
        $featured_stmt->execute();
        $featured_posts = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch categories for sidebar
        $cat_stmt = $conn->query("
            SELECT category, COUNT(*) as count 
            FROM blog_posts 
            WHERE is_published = 1 
            GROUP BY category 
            ORDER BY count DESC
        ");
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count total posts
        $count_stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM blog_posts bp
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE $where_clause
        ");
        $count_stmt->execute($params);
        $total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_posts / $posts_per_page);
        
        // Fetch paginated posts
        $stmt = $conn->prepare("
            SELECT bp.*, u.full_name as author_name
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE $where_clause 
            ORDER BY bp.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bindParam(1, $posts_per_page, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        
        // Execute with category/author params plus pagination params
        $all_params = array_merge($params, [$posts_per_page, $offset]);
        $stmt->execute($all_params);
        
        $blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Blog posts fetch error: " . $e->getMessage());
    }
}

// 3. FALLBACK DATA (if no posts found or database empty)
if (empty($blog_posts)) {
    $blog_posts = [
        [
            'id' => 1,
            'title' => 'Finding Peace in Difficult Times',
            'excerpt' => 'Discover how to find God\'s peace even when circumstances seem overwhelming...',
            'author_name' => 'Bishop Zakes Nxumalo',
            'created_at' => '2025-06-15 10:00:00',
            'category' => 'Encouragement',
            'featured_image' => 'assets/images/blog/peace.jpg',
            'views_count' => 125
        ],
        [
            'id' => 2,
            'title' => 'The Power of Family Prayer',
            'excerpt' => 'Learn how regular family prayer can transform your home and relationships...',
            'author_name' => 'Pastor Sarah Nxumalo',
            'created_at' => '2025-06-08 14:30:00',
            'category' => 'Family',
            'featured_image' => 'assets/images/blog/family-prayer.jpg',
            'views_count' => 89
        ],
        [
            'id' => 3,
            'title' => 'Living Generously: Beyond Finances',
            'excerpt' => 'Generosity isn\'t just about money - discover the many ways to live generously...',
            'author_name' => 'Elder John Dlamini',
            'created_at' => '2025-06-01 09:15:00',
            'category' => 'Giving',
            'featured_image' => 'assets/images/blog/generosity.jpg',
            'views_count' => 73
        ],
        [
            'id' => 4,
            'title' => 'Walking in the Spirit Daily',
            'excerpt' => 'Practical steps to cultivate a consistent walk with the Holy Spirit...',
            'author_name' => 'Pastor David Mamba',
            'created_at' => '2025-05-25 16:45:00',
            'category' => 'Spiritual Growth',
            'featured_image' => 'assets/images/blog/spirit-walk.jpg',
            'views_count' => 67
        ],
        [
            'id' => 5,
            'title' => 'Building Strong Christian Families',
            'excerpt' => 'Biblical principles for establishing God-honoring families in today\'s world...',
            'author_name' => 'Bishop Zakes Nxumalo',
            'created_at' => '2025-05-18 11:20:00',
            'category' => 'Family',
            'featured_image' => 'assets/images/blog/strong-family.jpg',
            'views_count' => 94
        ],
        [
            'id' => 6,
            'title' => 'Overcoming Fear with Faith',
            'excerpt' => 'How to confront and conquer different types of fear through Scripture...',
            'author_name' => 'Pastor Sarah Nxumalo',
            'created_at' => '2025-05-11 08:45:00',
            'category' => 'Encouragement',
            'featured_image' => 'assets/images/blog/faith-over-fear.jpg',
            'views_count' => 81
        ]
    ];
    
    $featured_posts = array_slice($blog_posts, 0, 2);
    $categories = [
        ['category' => 'Encouragement', 'count' => 2],
        ['category' => 'Family', 'count' => 2],
        ['category' => 'Giving', 'count' => 1],
        ['category' => 'Spiritual Growth', 'count' => 1]
    ];
    $total_posts = count($blog_posts);
    $total_pages = 1;
}

// 4. NOW INCLUDE THE HEADER
require_once 'includes/header.php';
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/blog-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Our Blog</h1>
                <p class="text-white mb-0">Inspiration, teaching, and updates from our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Blog</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <!-- Search Bar -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <form action="blog.php" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search blog posts..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Featured Posts -->
        <?php if (!empty($featured_posts)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title mb-4">Featured Articles</h2>
            </div>
            <?php foreach ($featured_posts as $featured): ?>
                <?php 
                $featured_date = new DateTime($featured['created_at']);
                $featured_read_time = isset($featured['content']) ? ceil(strlen(strip_tags($featured['content'])) / 1500) : 5;
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="featured-post card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="row g-0 h-100">
                            <div class="col-md-5">
                                <img src="<?php echo htmlspecialchars($featured['featured_image'] ?: 'assets/images/blog/default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($featured['title']); ?>" 
                                     class="img-fluid h-100 object-fit-cover">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="mb-2">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($featured['category']); ?></span>
                                    </div>
                                    <h3 class="h5 card-title">
                                        <a href="blog-post.php?id=<?php echo htmlspecialchars($featured['id']); ?>" class="text-dark">
                                            <?php echo htmlspecialchars($featured['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="card-text flex-grow-1">
                                        <?php echo htmlspecialchars($featured['excerpt'] ?? substr(strip_tags($featured['content'] ?? ''), 0, 150) . '...'); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initials-sm me-2">
                                                <?php echo strtoupper(substr($featured['author_name'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="small"><?php echo htmlspecialchars($featured['author_name']); ?></div>
                                                <div class="small text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    <?php echo $featured_date->format('M d, Y'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="far fa-clock me-1"></i> <?php echo $featured_read_time; ?> min read
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Blog Posts Grid -->
            <div class="col-lg-8">
                <h2 class="section-title mb-4">Latest Articles</h2>
                
                <?php if (!empty($blog_posts)): ?>
                    <div class="row g-4">
                        <?php foreach ($blog_posts as $post): ?>
                            <?php 
                            $post_date = new DateTime($post['created_at']);
                            $read_time = isset($post['content']) ? ceil(strlen(strip_tags($post['content'])) / 1500) : 5;
                            ?>
                            <div class="col-md-6 col-lg-6">
                                <article class="blog-card card h-100 border-0 shadow-sm">
                                    <div class="position-relative">
                                        <img src="<?php echo htmlspecialchars($post['featured_image'] ?: 'assets/images/blog/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                             class="card-img-top">
                                        <div class="card-category">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($post['category']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="post-meta small text-muted mb-2">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo $post_date->format('F j, Y'); ?>
                                            <span class="mx-2">•</span>
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo $read_time; ?> min read
                                        </div>
                                        <h3 class="h5 card-title">
                                            <a href="blog-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="text-dark">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content'] ?? ''), 0, 120) . '...'); ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initials-xs me-2">
                                                <?php echo strtoupper(substr($post['author_name'], 0, 2)); ?>
                                            </div>
                                            <div class="small"><?php echo htmlspecialchars($post['author_name']); ?></div>
                                        </div>
                                        <a href="blog-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="btn btn-sm btn-outline-primary">
                                            Read More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Blog pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h3>No blog posts found</h3>
                        <p class="text-muted">Check back soon for new articles!</p>
                        <a href="blog.php" class="btn btn-primary">View All Posts</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="blog-sidebar sticky-top" style="top: 100px;">
                    <!-- Categories -->
                    <div class="sidebar-widget mb-5">
                        <h4 class="widget-title mb-4">Categories</h4>
                        <div class="list-group">
                            <a href="blog.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo empty($category) ? 'active' : ''; ?>">
                                All Categories
                                <span class="badge bg-primary rounded-pill"><?php echo $total_posts; ?></span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $category == $cat['category'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $cat['count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Popular Posts -->
                    <div class="sidebar-widget mb-5">
                        <h4 class="widget-title mb-4">Popular Posts</h4>
                        <?php 
                        $popular_posts = array_slice($blog_posts, 0, 3);
                        foreach ($popular_posts as $popular): 
                            $popular_date = new DateTime($popular['created_at']);
                        ?>
                            <div class="popular-post mb-3 pb-3 border-bottom">
                                <div class="row g-3 align-items-center">
                                    <div class="col-4">
                                        <img src="<?php echo htmlspecialchars($popular['featured_image'] ?: 'assets/images/blog/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($popular['title']); ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-8">
                                        <h6 class="mb-1">
                                            <a href="blog-post.php?id=<?php echo htmlspecialchars($popular['id']); ?>" class="text-dark">
                                                <?php echo htmlspecialchars($popular['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="small text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo $popular_date->format('M d, Y'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="sidebar-widget mb-5">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Join Our Newsletter</h5>
                                <p class="card-text small">Get updates on new blog posts, events, and church news.</p>
                                <form class="newsletter-form" id="blogNewsletterForm">
                                    <div class="mb-3">
                                        <input type="email" class="form-control" placeholder="Your email address" required>
                                    </div>
                                    <button type="submit" class="btn btn-light w-100">
                                        <i class="fas fa-envelope me-1"></i> Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tags -->
                    <div class="sidebar-widget">
                        <h4 class="widget-title mb-3">Popular Tags</h4>
                        <div class="tags-cloud">
                            <a href="blog.php?category=Encouragement" class="tag">Encouragement</a>
                            <a href="blog.php?category=Family" class="tag">Family</a>
                            <a href="blog.php?category=Prayer" class="tag">Prayer</a>
                            <a href="blog.php?category=Faith" class="tag">Faith</a>
                            <a href="blog.php?category=Giving" class="tag">Giving</a>
                            <a href="blog.php?category=Spiritual Growth" class="tag">Spiritual Growth</a>
                            <a href="blog.php?category=Christian Living" class="tag">Christian Living</a>
                            <a href="blog.php?category=Church" class="tag">Church</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.search-form .input-group {
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.search-form input {
    border: none;
    padding: 15px 25px;
    font-size: 1rem;
}

.search-form button {
    padding: 15px 30px;
    border: none;
}

.featured-post {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.featured-post:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.featured-post img {
    transition: transform 0.5s ease;
}

.featured-post:hover img {
    transform: scale(1.05);
}

.blog-card {
    transition: all 0.3s ease;
    height: 100%;
}

.blog-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.blog-card img {
    height: 200px;
    object-fit: cover;
}

.card-category {
    position: absolute;
    top: 15px;
    left: 15px;
}

.avatar-initials-sm {
    width: 35px;
    height: 35px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.avatar-initials-xs {
    width: 25px;
    height: 25px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.7rem;
}

.widget-title {
    position: relative;
    padding-bottom: 10px;
    color: #1a5276;
}

.widget-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: #1a5276;
}

.sidebar-widget {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tags-cloud .tag {
    display: inline-block;
    padding: 5px 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    font-size: 0.85rem;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.tags-cloud .tag:hover {
    background: #1a5276;
    color: white;
    border-color: #1a5276;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .featured-post {
        flex-direction: column !important;
    }
    
    .featured-post .col-md-5 {
        height: 200px;
        width: 100%;
    }
    
    .search-form input {
        padding: 12px 20px;
    }
    
    .search-form button {
        padding: 12px 20px;
    }
}
</style>

<script>
// Newsletter form submission
document.getElementById('blogNewsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    const button = this.querySelector('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
    button.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert(`Thank you for subscribing to our newsletter! We've sent a confirmation email to: ${email}`);
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1500);
});

// Smooth scroll for search form
document.querySelector('.search-form')?.addEventListener('submit', function(e) {
    if (this.querySelector('input').value.trim() === '') {
        e.preventDefault();
    }
});

// Category filter active state
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
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