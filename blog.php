<?php
/**
 * blog.php – CFCI Church Blog Listing
 * PRD‑compliant: dynamic DB, fallback static, filterable, paginated.
 */
require_once 'includes/header.php';

$current_page = basename($_SERVER['PHP_SELF']);
$category = $_GET['category'] ?? '';
$author   = $_GET['author']   ?? '';
$search   = $_GET['search']   ?? '';
$tag      = $_GET['tag']      ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9;
$offset   = ($page - 1) * $per_page;

$posts = [];
$total_posts = 0;
$categories_list = [];
$featured_posts = [];

if (isset($conn) && $conn instanceof PDO) {
    try {
        // Base WHERE clause for published posts
        $conditions = ['bp.is_published = 1'];
        $params = [];

        if (!empty($category)) {
            $conditions[] = 'bp.category = ?';
            $params[] = $category;
        }
        if (!empty($author)) {
            $conditions[] = 'u.full_name LIKE ?';
            $params[] = "%$author%";
        }
        if (!empty($search)) {
            $conditions[] = '(bp.title LIKE ? OR bp.content LIKE ? OR bp.meta_keywords LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($tag)) {
            $conditions[] = '(bp.meta_keywords LIKE ? OR bp.title LIKE ?)';
            $params[] = "%$tag%";
            $params[] = "%$tag%";
        }

        $where = implode(' AND ', $conditions);

        // Count total matching posts
        $stmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE {$where}");
        $stmt->execute($params);
        $total_posts = (int)$stmt->fetchColumn();

        // Fetch posts for current page
        $sql = "SELECT bp.id, bp.title, bp.excerpt, bp.content, bp.featured_image, bp.category,
                       bp.views_count, bp.likes_count, bp.created_at,
                       u.full_name AS author_name
                FROM blog_posts bp
                LEFT JOIN users u ON bp.author_id = u.id
                WHERE {$where}
                ORDER BY bp.created_at DESC
                LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get featured posts (latest 2)
        $stmt = $conn->prepare("SELECT id, title, excerpt, featured_image, category, created_at, u.full_name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.is_published = 1 ORDER BY bp.created_at DESC LIMIT 2");
        $stmt->execute();
        $featured_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Categories for sidebar
        $stmt = $conn->query("SELECT category, COUNT(*) AS cnt FROM blog_posts WHERE is_published = 1 GROUP BY category ORDER BY cnt DESC");
        $categories_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Blog fetch error: " . $e->getMessage());
    }
}

// Fallback static data if empty or DB offline
if (empty($posts)) {
    $posts = [
        ['id' => 1, 'title' => 'Finding Peace in Difficult Times',   'excerpt' => 'Discover how to find God\'s peace...',   'content' => '...', 'category' => 'Encouragement',    'author_name' => 'Bishop Zakes Nxumalo', 'created_at' => '2025-06-15 10:00:00', 'featured_image' => 'assets/images/blog/peace.jpg',        'views_count' => 125],
        ['id' => 2, 'title' => 'The Power of Family Prayer',          'excerpt' => 'Learn how regular family prayer...',      'content' => '...', 'category' => 'Family',           'author_name' => 'Pastor Sarah Nxumalo', 'created_at' => '2025-06-08 14:30:00', 'featured_image' => 'assets/images/blog/family-prayer.jpg', 'views_count' => 89],
        ['id' => 3, 'title' => 'Living Generously: Beyond Finances',  'excerpt' => 'Generosity isn\'t just about money...',    'content' => '...', 'category' => 'Giving',            'author_name' => 'Elder John Dlamini',   'created_at' => '2025-06-01 09:15:00', 'featured_image' => 'assets/images/blog/generosity.jpg',     'views_count' => 73],
        ['id' => 4, 'title' => 'Walking in the Spirit Daily',        'excerpt' => 'Practical steps to cultivate...',         'content' => '...', 'category' => 'Spiritual Growth',  'author_name' => 'Pastor David Mamba',   'created_at' => '2025-05-25 16:45:00', 'featured_image' => 'assets/images/blog/spirit-walk.jpg',    'views_count' => 67],
        ['id' => 5, 'title' => 'Building Strong Christian Families', 'excerpt' => 'Biblical principles for establishing...', 'content' => '...', 'category' => 'Family',           'author_name' => 'Bishop Zakes Nxumalo', 'created_at' => '2025-05-18 11:20:00', 'featured_image' => 'assets/images/blog/strong-family.jpg',  'views_count' => 94],
        ['id' => 6, 'title' => 'Overcoming Fear with Faith',         'excerpt' => 'How to confront and conquer...',          'content' => '...', 'category' => 'Encouragement',    'author_name' => 'Pastor Sarah Nxumalo', 'created_at' => '2025-05-11 08:45:00', 'featured_image' => 'assets/images/blog/faith-over-fear.jpg','views_count' => 81],
    ];
    $featured_posts = array_slice($posts, 0, 2);
    $categories_list = [
        ['category' => 'Encouragement', 'cnt' => 2],
        ['category' => 'Family',        'cnt' => 2],
        ['category' => 'Giving',        'cnt' => 1],
        ['category' => 'Spiritual Growth', 'cnt' => 1],
    ];
    $total_posts = count($posts);
}

$total_pages = ceil($total_posts / $per_page);
$current_url = SITE_URL . 'blog.php';
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Our Blog</h1>
                <p class="text-white mb-0 fs-5">Inspiration, teaching, and updates from our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Blog</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <!-- Search Form -->
        <div class="row mb-5 justify-content-center">
            <div class="col-lg-6">
                <form action="<?= SITE_URL ?>blog.php" method="GET" class="input-group shadow-sm rounded-pill overflow-hidden">
                    <input type="text" name="search" class="form-control border-0 ps-4" placeholder="Search blog posts..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Featured Posts -->
        <?php if (!empty($featured_posts)): ?>
        <div class="row mb-5">
            <div class="col-12"><h2 class="section-title mb-4">Featured Articles</h2></div>
            <?php foreach ($featured_posts as $feat): $feat_date = new DateTime($feat['created_at']); ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100 border-0 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <img src="<?= SITE_URL . htmlspecialchars($feat['featured_image'] ?: 'assets/images/blog/default.jpg') ?>" alt="<?= htmlspecialchars($feat['title']) ?>" class="img-fluid h-100 object-fit-cover" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column h-100">
                                    <span class="badge bg-primary mb-2"><?= htmlspecialchars($feat['category']) ?></span>
                                    <h3 class="h5"><a href="<?= SITE_URL ?>blog-post.php?id=<?= $feat['id'] ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($feat['title']) ?></a></h3>
                                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($feat['excerpt'] ?? strip_tags($feat['content'] ?? ''), 0, 120, '...')) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div>
                                            <div class="fw-bold small"><?= htmlspecialchars($feat['author_name']) ?></div>
                                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $feat_date->format('M d, Y') ?></small>
                                        </div>
                                        <a href="<?= SITE_URL ?>blog-post.php?id=<?= $feat['id'] ?>" class="btn btn-sm btn-outline-primary">Read</a>
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
            <!-- Posts Grid -->
            <div class="col-lg-8">
                <h2 class="section-title mb-4">Latest Articles</h2>
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h3>No posts found</h3>
                        <p class="text-muted"><?= $search ? 'No results for "' . htmlspecialchars($search) . '"' : 'Check back soon!' ?></p>
                        <a href="<?= SITE_URL ?>blog.php" class="btn btn-primary">View All</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $p): $p_date = new DateTime($p['created_at']); $read = max(1, ceil(strlen(strip_tags($p['content'] ?? '')) / 1500)); ?>
                            <div class="col-md-6">
                                <article class="blog-card card h-100 shadow-sm border-0 overflow-hidden">
                                    <img src="<?= SITE_URL . htmlspecialchars($p['featured_image'] ?: 'assets/images/blog/default.jpg') ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="card-img-top object-fit-cover" style="height:200px;" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                    <div class="card-body d-flex flex-column">
                                        <span class="badge bg-primary mb-2"><?= htmlspecialchars($p['category']) ?></span>
                                        <h3 class="h5"><a href="<?= SITE_URL ?>blog-post.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($p['title']) ?></a></h3>
                                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($p['excerpt'] ?? strip_tags($p['content'] ?? ''), 0, 120, '...')) ?></p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initials-xs bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:25px; height:25px; font-size:0.7rem;"><?= getUserInitials($p['author_name']) ?></div>
                                                <small class="fw-bold"><?= htmlspecialchars($p['author_name']) ?></small>
                                            </div>
                                            <small class="text-muted"><i class="far fa-clock me-1"></i><?= $read ?> min read</small>
                                        </div>
                                        <small class="text-muted mt-1"><i class="far fa-calendar-alt me-1"></i><?= $p_date->format('M d, Y') ?></small>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-5">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPageUrl($page-1) ?>">&laquo;</a>
                                </li>
                                <?php for ($i=1; $i<=$total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPageUrl($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPageUrl($page+1) ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top:100px;">
                    <!-- Categories -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Categories</h5>
                            <div class="list-group list-group-flush">
                                <a href="<?= SITE_URL ?>blog.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= empty($category) ? 'active' : '' ?>">
                                    All Categories <span class="badge bg-primary rounded-pill"><?= $total_posts ?></span>
                                </a>
                                <?php foreach ($categories_list as $cat): ?>
                                    <a href="<?= SITE_URL ?>blog.php?category=<?= urlencode($cat['category']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $category == $cat['category'] ? 'active' : '' ?>">
                                        <?= htmlspecialchars($cat['category']) ?> <span class="badge bg-primary rounded-pill"><?= $cat['cnt'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Posts (by views) -->
                    <?php if (!empty($posts)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Popular Posts</h5>
                            <?php 
                            $popular = $posts;
                            usort($popular, fn($a,$b) => ($b['views_count']??0) - ($a['views_count']??0));
                            $popular = array_slice($popular, 0, 3);
                            foreach ($popular as $pop): $pop_date = new DateTime($pop['created_at']);
                            ?>
                                <div class="d-flex mb-3">
                                    <img src="<?= SITE_URL . htmlspecialchars($pop['featured_image'] ?: 'assets/images/blog/default.jpg') ?>" alt="<?= htmlspecialchars($pop['title']) ?>" class="rounded me-3" style="width:60px; height:60px; object-fit:cover;" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                    <div>
                                        <h6 class="mb-1"><a href="<?= SITE_URL ?>blog-post.php?id=<?= $pop['id'] ?>" class="text-dark"><?= htmlspecialchars(mb_strimwidth($pop['title'], 0, 50, '...')) ?></a></h6>
                                        <small class="text-muted"><?= $pop_date->format('M d, Y') ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Newsletter -->
                    <div class="card shadow-sm bg-primary text-white mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Stay Inspired</h5>
                            <p class="small">Get blog updates and devotionals weekly.</p>
                            <form onsubmit="event.preventDefault(); if(typeof showToast==='function') showToast('Subscribed!','success'); else alert('Subscribed!');">
                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="Your email" required>
                                    <button class="btn btn-light" type="submit">Subscribe</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * Build pagination URL preserving current filters
 */
function buildPageUrl($page): string {
    global $category, $author, $search, $tag;
    $query = http_build_query(array_filter([
        'category' => $category,
        'author'   => $author,
        'search'   => $search,
        'tag'      => $tag,
        'page'     => $page > 1 ? $page : null,
    ]));
    return SITE_URL . 'blog.php' . ($query ? '?' . $query : '');
}
?>

<style>
.blog-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.blog-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg) !important; }
.featured-post:hover { transform: translateY(-5px); }
.avatar-initials-xs { width:25px; height:25px; font-size:0.7rem; }
</style>

<?php require_once 'includes/footer.php'; ?>