<?php
/**
 * blog-post.php – CFCI Single Blog Post
 * PRD‑compliant: dynamic DB, fallback static, comments form, sharing.
 */
require_once 'includes/header.php';

$current_page = basename($_SERVER['PHP_SELF']);
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$related_posts = [];
$author_bio = '';

if ($post_id > 0 && isset($conn) && $conn instanceof PDO) {
    try {
        // Fetch post + author details
        $stmt = $conn->prepare("
            SELECT bp.*, u.full_name AS author_name, u.email AS author_email,
                   up.bio AS author_bio, up.occupation AS author_occupation
            FROM blog_posts bp
            LEFT JOIN users u ON bp.author_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE bp.id = ? AND bp.is_published = 1
        ");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            // Increment view count
            $conn->exec("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = $post_id");

            // Related posts (same category, different post)
            $stmt = $conn->prepare("SELECT id, title, featured_image, created_at FROM blog_posts WHERE category = ? AND id != ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3");
            $stmt->execute([$post['category'], $post_id]);
            $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Author bio fallback
            if (empty($post['author_bio'])) {
                $stmt = $conn->prepare("SELECT bio FROM user_profiles WHERE user_id = ?");
                $stmt->execute([$post['author_id']]);
                $bio = $stmt->fetchColumn();
                $post['author_bio'] = $bio ?: 'Author and contributor to CFCI blog.';
            }
        }
    } catch (PDOException $e) {
        error_log("Blog post fetch error: " . $e->getMessage());
    }
}

// Redirect if not found
if (!$post && $post_id > 0) {
    header('Location: ' . SITE_URL . 'blog.php');
    exit();
}

// Fallback static post if DB offline
if (!$post) {
    $post = [
        'id' => 1,
        'title' => 'Finding Peace in Difficult Times',
        'content' => '<p>In our journey of faith, we often encounter seasons of difficulty... (full content).</p>',
        'excerpt' => 'Discover how to find God\'s peace...',
        'category' => 'Encouragement',
        'author_name' => 'Bishop Zakes Nxumalo',
        'author_email' => 'bishop.zakes@cfci.org',
        'author_bio' => 'Bishop Zakes has served as senior pastor of CFCI for over 20 years.',
        'author_occupation' => 'Senior Pastor',
        'created_at' => '2025-06-15 10:00:00',
        'featured_image' => 'assets/images/blog/peace.jpg',
        'meta_keywords' => 'peace, encouragement, faith',
        'views_count' => 125,
        'likes_count' => 23,
        'comments_count' => 5,
    ];
    $related_posts = [];
}
$post_date = new DateTime($post['created_at']);
$read_time = max(1, ceil(strlen(strip_tags($post['content'] ?? '')) / 1500));
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold"><?= htmlspecialchars($post['title']) ?></h1>
                <p class="text-white mb-0 fs-5"><?= htmlspecialchars($post['category']) ?></p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>blog.php" class="text-white-50">Blog</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <article class="blog-post-content bg-white p-4 rounded-3 shadow-sm mb-5">
                    <!-- Featured Image -->
                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="mb-4">
                            <img src="<?= SITE_URL . htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="img-fluid rounded-3" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                        </div>
                    <?php endif; ?>

                    <!-- Meta Info -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-initials-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px;"><?= getUserInitials($post['author_name']) ?></div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($post['author_name']) ?></div>
                                <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $post_date->format('F j, Y') ?> &bull; <i class="far fa-clock me-1"></i><?= $read_time ?> min read</small>
                            </div>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <span class="text-muted small"><i class="far fa-eye"></i> <?= number_format($post['views_count'] ?? 0) ?></span>
                            <?php if (($post['likes_count'] ?? 0) > 0): ?>
                                <span class="text-muted small"><i class="far fa-heart"></i> <?= $post['likes_count'] ?></span>
                            <?php endif; ?>
                            <?php if (($post['comments_count'] ?? 0) > 0): ?>
                                <span class="text-muted small"><i class="far fa-comment"></i> <?= $post['comments_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="post-body mb-5">
                        <?= $post['content'] ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($post['meta_keywords'])): $tags = explode(',', $post['meta_keywords']); ?>
                        <div class="mb-4">
                            <h6 class="mb-2">Tags:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($tags as $t): $t = trim($t); if ($t): ?>
                                    <a href="<?= SITE_URL ?>blog.php?tag=<?= urlencode($t) ?>" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars($t) ?></a>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Share Buttons -->
                    <div class="border-top pt-4">
                        <h6 class="mb-3">Share this article</h6>
                        <div class="d-flex gap-2">
                            <?php $shareUrl = SITE_URL . 'blog-post.php?id=' . $post_id; ?>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" class="btn btn-sm" style="background:#3b5998;color:#fff;"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="btn btn-sm" style="background:#1da1f2;color:#fff;"><i class="fab fa-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' - ' . $shareUrl) ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                            <button onclick="copyToClipboard()" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>

                    <!-- Author Bio -->
                    <div class="card mt-5">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center mb-3 mb-md-0">
                                    <div class="avatar-initials-xl bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:80px; height:80px; font-size:1.5rem;"><?= getUserInitials($post['author_name']) ?></div>
                                </div>
                                <div class="col-md-10">
                                    <h5 class="card-title">About <?= htmlspecialchars($post['author_name']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($post['author_bio'] ?? '') ?></p>
                                    <?php if (!empty($post['author_occupation'])): ?>
                                        <p class="card-text text-muted small"><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($post['author_occupation']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($post['author_email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($post['author_email']) ?>" class="btn btn-sm btn-outline-primary">Contact Author</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Navigation (Previous/Next) -->
                    <?php
                    if (isset($conn)) {
                        $prev = $conn->prepare("SELECT id, title FROM blog_posts WHERE created_at < ? AND is_published = 1 ORDER BY created_at DESC LIMIT 1");
                        $prev->execute([$post['created_at']]);
                        $prev_post = $prev->fetch();
                        $next = $conn->prepare("SELECT id, title FROM blog_posts WHERE created_at > ? AND is_published = 1 ORDER BY created_at ASC LIMIT 1");
                        $next->execute([$post['created_at']]);
                        $next_post = $next->fetch();
                    } else { $prev_post = $next_post = null; }
                    ?>
                    <?php if ($prev_post || $next_post): ?>
                        <div class="row mt-5">
                            <div class="col-md-6">
                                <?php if ($prev_post): ?>
                                    <a href="<?= SITE_URL ?>blog-post.php?id=<?= $prev_post['id'] ?>" class="d-block p-3 border rounded-3 text-decoration-none">
                                        <div class="small text-muted"><i class="fas fa-arrow-left me-1"></i> Previous</div>
                                        <div class="fw-bold"><?= htmlspecialchars($prev_post['title']) ?></div>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php if ($next_post): ?>
                                    <a href="<?= SITE_URL ?>blog-post.php?id=<?= $next_post['id'] ?>" class="d-block p-3 border rounded-3 text-decoration-none">
                                        <div class="small text-muted">Next <i class="fas fa-arrow-right ms-1"></i></div>
                                        <div class="fw-bold"><?= htmlspecialchars($next_post['title']) ?></div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Comments Section -->
                    <div class="mt-5 pt-4 border-top">
                        <h3 class="mb-4">Comments & Discussion</h3>
                        <?php
                        $comments = [];
                        if ($conn) {
                            $stmt = $conn->prepare("SELECT bc.*, u.full_name, u.avatar_url FROM blog_comments bc LEFT JOIN users u ON bc.user_id = u.id WHERE bc.post_id = ? AND bc.status = 'approved' ORDER BY bc.created_at DESC");
                            $stmt->execute([$post_id]);
                            $comments = $stmt->fetchAll();
                        }
                        ?>
                        <?php if (is_logged_in()): ?>
                            <form id="commentForm" class="mb-4">
                                <?= csrfField() ?>
                                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="content" rows="3" placeholder="Share your thoughts..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Comment</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">Please <a href="<?= SITE_URL ?>auth/login.php?redirect=<?= urlencode(SITE_URL . 'blog-post.php?id=' . $post_id) ?>" class="alert-link">login</a> to comment.</div>
                        <?php endif; ?>

                        <?php if (!empty($comments)): ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $c): $c_date = new DateTime($c['created_at']); ?>
                                    <div class="d-flex mb-3 pb-3 border-bottom">
                                        <div class="flex-shrink-0">
                                            <?php if ($c['avatar_url']): ?>
                                                <img src="<?= htmlspecialchars($c['avatar_url']) ?>" alt="<?= htmlspecialchars($c['full_name']) ?>" class="rounded-circle" width="40" height="40">
                                            <?php else: ?>
                                                <div class="avatar-initials-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><?= getUserInitials($c['full_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?= htmlspecialchars($c['full_name']) ?> <small class="text-muted ms-2"><?= $c_date->format('M d, Y') ?></small></h6>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No comments yet. Be the first!</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top:100px;">
                    <!-- Author Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <div class="avatar-initials-xl bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px; height:80px; font-size:2rem;"><?= getUserInitials($post['author_name']) ?></div>
                            <h5><?= htmlspecialchars($post['author_name']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($post['author_occupation'] ?? '') ?></p>
                            <a href="<?= SITE_URL ?>blog.php?author=<?= urlencode($post['author_name']) ?>" class="btn btn-sm btn-outline-primary w-100">All Articles</a>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <?php if (!empty($related_posts)): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Related Articles</h5>
                                <?php foreach ($related_posts as $rel): $rel_date = new DateTime($rel['created_at']); ?>
                                    <div class="d-flex mb-3">
                                        <img src="<?= SITE_URL . htmlspecialchars($rel['featured_image'] ?: 'assets/images/blog/default.jpg') ?>" alt="<?= htmlspecialchars($rel['title']) ?>" class="rounded me-3" style="width:60px; height:60px; object-fit:cover;" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                        <div>
                                            <h6 class="mb-1"><a href="<?= SITE_URL ?>blog-post.php?id=<?= $rel['id'] ?>" class="text-dark"><?= htmlspecialchars(mb_strimwidth($rel['title'], 0, 50, '...')) ?></a></h6>
                                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $rel_date->format('M d, Y') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Category Cloud -->
                    <?php
                    $allCats = [];
                    if ($conn) {
                        $stmt = $conn->query("SELECT DISTINCT category FROM blog_posts WHERE is_published = 1 ORDER BY category");
                        $allCats = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    }
                    if (!empty($allCats)):
                    ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Categories</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($allCats as $cat): ?>
                                    <a href="<?= SITE_URL ?>blog.php?category=<?= urlencode($cat) ?>" class="btn btn-sm btn-outline-secondary <?= $post['category'] == $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        if (typeof showToast === 'function') showToast('Link copied!', 'success');
        else alert('Link copied to clipboard.');
    });
}

// Comment form submission (simulated)
document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;
    setTimeout(() => {
        if (typeof showToast === 'function') showToast('Comment submitted for review.', 'success');
        else alert('Thank you! Your comment will appear after review.');
        this.reset();
        btn.innerHTML = 'Submit Comment';
        btn.disabled = false;
    }, 1500);
});
</script>

<?php require_once 'includes/footer.php'; ?>