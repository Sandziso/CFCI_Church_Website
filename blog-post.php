<?php
// blog-post.php - Single Blog Post

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

// 2. FETCH POST DATA
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$blog_post = null;
$related_posts = [];

if ($conn) {
    try {
        // Fetch the main blog post
        $stmt = $conn->prepare("
            SELECT bp.*, u.full_name as author_name, u.email as author_email, u.bio as author_bio
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE bp.id = ? AND bp.status = 'published'
        ");
        $stmt->execute([$post_id]);
        $blog_post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($blog_post) {
            // Fetch related posts (same category, different post)
            $stmt = $conn->prepare("
                SELECT id, title, created_at, featured_image, category 
                FROM blog_posts 
                WHERE category = ? AND id != ? AND is_published = 1 
                ORDER BY created_at DESC 
                LIMIT 3
            ");
            $stmt->execute([$blog_post['category'], $post_id]);
            $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Increment view count
            $update_stmt = $conn->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ?");
            $update_stmt->execute([$post_id]);
        }
    } catch (PDOException $e) {
        error_log("Blog post fetch error: " . $e->getMessage());
    }
}

// 3. VALIDATE AND REDIRECT (Before any HTML is sent)
if (!$blog_post && $post_id > 0) {
    header('Location: blog.php');
    exit();
}

// 4. FALLBACK DATA (If no ID provided or database empty)
if (!$blog_post && $post_id == 0) {
    $blog_post = [
        'id' => 1,
        'title' => 'Finding Peace in Difficult Times',
        'content' => '<p>In our journey of faith, we often encounter seasons of difficulty and uncertainty. These times can test our faith, challenge our resolve, and sometimes leave us feeling overwhelmed. Yet, it is precisely in these moments that God\'s peace becomes most precious and real to us.</p>
        
        <h3>Understanding God\'s Peace</h3>
        <p>The peace of God is not merely the absence of trouble, but the presence of His sustaining grace in the midst of it. As Jesus told His disciples, "Peace I leave with you; my peace I give you. I do not give to you as the world gives. Do not let your hearts be troubled and do not be afraid" (John 14:27).</p>
        
        <p>This divine peace differs fundamentally from worldly peace. The world\'s peace depends on favorable circumstances, but God\'s peace transcends circumstances. It\'s a deep, abiding assurance that God is in control, that He loves us, and that He is working all things for our good (Romans 8:28).</p>
        
        <h3>Practical Steps to Peace</h3>
        <p>How do we access this peace in practical terms? The Apostle Paul gives us clear guidance in Philippians 4:6-7:</p>
        
        <blockquote>
            "Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God. And the peace of God, which transcends all understanding, will guard your hearts and your minds in Christ Jesus."
        </blockquote>
        
        <ol>
            <li><strong>Replace Anxiety with Prayer</strong>: When worries arise, immediately turn them into prayers. Speak to God honestly about your fears and concerns.</li>
            <li><strong>Practice Thanksgiving</strong>: Even in difficult situations, find aspects to be thankful for. Gratitude shifts our focus from problems to God\'s faithfulness.</li>
            <li><strong>Present Your Requests</strong>: Specifically articulate what you need from God. He invites us to be specific in our prayers.</li>
            <li><strong>Trust the Outcome</strong>: After praying, consciously release the situation to God, trusting that His peace will guard your heart and mind.</li>
        </ol>
        
        <h3>The Role of Scripture</h3>
        <p>God\'s Word is a powerful source of peace. When we meditate on Scripture, we\'re reminded of God\'s character, His promises, and His track record of faithfulness. Memorizing key verses about peace can provide instant comfort in moments of distress.</p>
        
        <p>Consider these promises:</p>
        <ul>
            <li>"You will keep in perfect peace those whose minds are steadfast, because they trust in you" (Isaiah 26:3).</li>
            <li>"Cast all your anxiety on him because he cares for you" (1 Peter 5:7).</li>
            <li>"The Lord gives strength to his people; the Lord blesses his people with peace" (Psalm 29:11).</li>
        </ul>
        
        <h3>Community Support</h3>
        <p>We were not meant to face difficulties alone. The church community provides prayer support, practical help, and encouragement. When you\'re struggling, reach out to your small group, pastor, or trusted Christian friends. There is strength in bearing one another\'s burdens (Galatians 6:2).</p>
        
        <h3>Conclusion</h3>
        <p>Peace in difficult times is not an elusive dream but a tangible reality available to every believer. It comes not from ignoring our problems, but from bringing them to the One who has overcome the world (John 16:33). As we practice prayer, thanksgiving, Scripture meditation, and community support, we\'ll discover that God\'s peace truly does guard our hearts and minds.</p>
        
        <p>Remember, the storm may rage around you, but with Christ in your boat, you can have peace within you.</p>',
        'author_name' => 'Bishop Zakes Nxumalo',
        'author_bio' => 'Senior Pastor with over 20 years of ministry experience, passionate about family restoration and practical Christian living.',
        'created_at' => '2025-06-15 10:00:00',
        'category' => 'Encouragement',
        'featured_image' => 'assets/images/blog/peace.jpg',
        'views_count' => 125,
        'tags' => 'peace, prayer, faith, encouragement'
    ];
    
    $related_posts = [
        [
            'id' => 2, 
            'title' => 'The Power of Family Prayer', 
            'created_at' => '2025-06-08 14:30:00', 
            'featured_image' => 'assets/images/blog/family-prayer.jpg', 
            'category' => 'Family'
        ],
        [
            'id' => 3, 
            'title' => 'Living Generously: Beyond Finances', 
            'created_at' => '2025-06-01 09:15:00', 
            'featured_image' => 'assets/images/blog/generosity.jpg', 
            'category' => 'Giving'
        ],
        [
            'id' => 4, 
            'title' => 'Walking in the Spirit Daily', 
            'created_at' => '2025-05-25 16:45:00', 
            'featured_image' => 'assets/images/blog/spirit-walk.jpg', 
            'category' => 'Spiritual Growth'
        ]
    ];
}

// 5. NOW INCLUDE THE HEADER
require_once 'includes/header.php';

// Format dates and calculate read time
$post_date = new DateTime($blog_post['created_at']);
$formatted_date = $post_date->format('F j, Y');
$read_time = isset($blog_post['content']) ? ceil(strlen(strip_tags($blog_post['content'])) / 1500) : 5;
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/blog-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Blog Post</h1>
                <p class="text-white mb-0">Read and be inspired by teachings from our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="blog.php" class="text-white">Blog</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Article</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <article class="blog-post-content">
                    <!-- Post Header -->
                    <div class="post-header mb-5">
                        <div class="post-category mb-3">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($blog_post['category'] ?: 'General'); ?></span>
                        </div>
                        
                        <h1 class="mb-4"><?php echo htmlspecialchars($blog_post['title']); ?></h1>
                        
                        <div class="post-meta d-flex align-items-center mb-4">
                            <div class="author-avatar me-3">
                                <div class="avatar-initials-lg">
                                    <?php echo strtoupper(substr($blog_post['author_name'], 0, 2)); ?>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="me-4">
                                        <div class="author-name fw-bold"><?php echo htmlspecialchars($blog_post['author_name']); ?></div>
                                        <div class="post-date text-muted small">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo $formatted_date; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <span class="text-muted small">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo $read_time; ?> min read
                                        </span>
                                        <span class="text-muted small">
                                            <i class="far fa-eye me-1"></i>
                                            <?php echo number_format($blog_post['views_count'] ?? 0); ?> views
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured Image -->
                        <div class="featured-image mb-4">
                            <img src="<?php echo htmlspecialchars($blog_post['featured_image'] ?: 'assets/images/blog/default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($blog_post['title']); ?>" 
                                 class="img-fluid rounded">
                        </div>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="post-body">
                        <?php echo $blog_post['content']; ?>
                    </div>
                    
                    <!-- Post Footer -->
                    <div class="post-footer mt-5 pt-4 border-top">
                        <!-- Tags -->
                        <?php if (!empty($blog_post['tags'])): ?>
                            <div class="post-tags mb-4">
                                <h6 class="mb-3"><i class="fas fa-tags me-2"></i>Tags:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php
                                    $tags = explode(',', $blog_post['tags']);
                                    foreach ($tags as $tag):
                                        $tag = trim($tag);
                                        if (!empty($tag)):
                                    ?>
                                        <a href="blog.php?tag=<?php echo urlencode($tag); ?>" class="btn btn-sm btn-outline-secondary">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Share Buttons -->
                        <div class="share-buttons mb-5">
                            <h6 class="mb-3"><i class="fas fa-share-alt me-2"></i>Share This Article:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-facebook">
                                    <i class="fab fa-facebook-f me-1"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($blog_post['title']); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-twitter">
                                    <i class="fab fa-twitter me-1"></i> Twitter
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($blog_post['title'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <button onclick="copyToClipboard()" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-link me-1"></i> Copy Link
                                </button>
                            </div>
                        </div>
                        
                        <!-- Author Bio -->
                        <div class="author-bio card mb-5">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <div class="avatar-initials-xl">
                                            <?php echo strtoupper(substr($blog_post['author_name'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <h5 class="card-title">About <?php echo htmlspecialchars($blog_post['author_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($blog_post['author_bio'] ?: 'Author and contributor to CFCI blog.'); ?></p>
                                        <?php if (isset($blog_post['author_email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($blog_post['author_email']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-envelope me-1"></i> Contact Author
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                
                <!-- Comments Section -->
                <div class="comments-section mt-5">
                    <h3 class="mb-4">Comments & Discussion</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The comments feature is currently being developed. For now, please share your thoughts on our social media pages or contact us directly.
                    </div>
                    
                    <!-- Comments coming soon -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h4>Comments Coming Soon</h4>
                            <p class="text-muted">We're working on adding a comments system to allow discussion and fellowship.</p>
                            <p class="small text-muted">Until then, feel free to share your thoughts on our Facebook page!</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="blog-sidebar sticky-top" style="top: 100px;">
                    <!-- Author Card -->
                    <div class="sidebar-widget mb-5">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="avatar-initials-xxl mb-3">
                                    <?php echo strtoupper(substr($blog_post['author_name'], 0, 2)); ?>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($blog_post['author_name']); ?></h5>
                                <p class="card-text small text-muted">Author & Contributor</p>
                                <a href="blog.php?author=<?php echo urlencode($blog_post['author_name']); ?>" class="btn btn-sm btn-outline-primary">
                                    View All Articles
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related Posts -->
                    <?php if (!empty($related_posts)): ?>
                        <div class="sidebar-widget mb-5">
                            <h4 class="widget-title mb-4">Related Articles</h4>
                            <div class="related-posts">
                                <?php foreach ($related_posts as $related): ?>
                                    <?php
                                    $related_date = new DateTime($related['created_at']);
                                    ?>
                                    <div class="related-post mb-3 pb-3 border-bottom">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-4">
                                                <img src="<?php echo htmlspecialchars($related['featured_image'] ?: 'assets/images/blog/default.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                                     class="img-fluid rounded">
                                            </div>
                                            <div class="col-8">
                                                <h6 class="mb-1">
                                                    <a href="blog-post.php?id=<?php echo htmlspecialchars($related['id']); ?>" class="text-dark">
                                                        <?php echo htmlspecialchars($related['title']); ?>
                                                    </a>
                                                </h6>
                                                <div class="small text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    <?php echo $related_date->format('M d, Y'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter Subscription -->
                    <div class="sidebar-widget mb-5">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Never Miss a Post</h5>
                                <p class="card-text small">Subscribe to receive new blog posts directly in your inbox.</p>
                                <form class="newsletter-form" id="postNewsletterForm">
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
                    
                    <!-- Popular Tags -->
                    <div class="sidebar-widget">
                        <h4 class="widget-title mb-3">Browse Categories</h4>
                        <div class="tags-cloud">
                            <a href="blog.php?category=Encouragement" class="tag">Encouragement</a>
                            <a href="blog.php?category=Family" class="tag">Family Life</a>
                            <a href="blog.php?category=Prayer" class="tag">Prayer</a>
                            <a href="blog.php?category=Leadership" class="tag">Christian Leadership</a>
                            <a href="blog.php?category=Outreach" class="tag">Outreach</a>
                            <a href="blog.php?category=Worship" class="tag">Worship</a>
                            <a href="blog.php?category=Discipleship" class="tag">Discipleship</a>
                            <a href="blog.php?category=Missions" class="tag">Missions</a>
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

.blog-post-content {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.author-avatar .avatar-initials-lg {
    width: 50px;
    height: 50px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.avatar-initials-xl {
    width: 80px;
    height: 80px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
}

.avatar-initials-xxl {
    width: 100px;
    height: 100px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 2rem;
    margin: 0 auto;
}

.featured-image img {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
    border-radius: 8px;
}

.post-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.post-body h2, .post-body h3, .post-body h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #1a5276;
}

.post-body h2 {
    font-size: 1.8rem;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 0.5rem;
}

.post-body h3 {
    font-size: 1.5rem;
}

.post-body p {
    margin-bottom: 1.5rem;
}

.post-body ul, .post-body ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.post-body li {
    margin-bottom: 0.5rem;
}

.post-body blockquote {
    border-left: 4px solid #e67e22;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #666;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 0 8px 8px 0;
}

.post-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.share-buttons .btn-facebook {
    background: #3b5998;
    color: white;
    border: none;
}

.share-buttons .btn-twitter {
    background: #1da1f2;
    color: white;
    border: none;
}

.share-buttons .btn-success {
    background: #25d366;
    color: white;
    border: none;
}

.share-buttons .btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
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

.related-post:last-child {
    border-bottom: none !important;
}

.sidebar-widget {
    background: white;
    padding: 25px;
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
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .blog-post-content {
        padding: 20px;
    }
    
    .post-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .post-meta .author-avatar {
        margin-bottom: 15px;
    }
    
    .share-buttons .btn {
        font-size: 0.8rem;
        padding: 5px 10px;
        margin-bottom: 5px;
    }
    
    .featured-image img {
        max-height: 250px;
    }
}
</style>

<script>
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        // Show temporary notification
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        button.classList.add('btn-success');
        button.classList.remove('btn-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-secondary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
        alert('Failed to copy link to clipboard. Please copy manually.');
    });
}

// Newsletter form submission
document.getElementById('postNewsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    const button = this.querySelector('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
    button.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert(`Thank you for subscribing! You'll now receive updates about new blog posts.`);
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1500);
});

// Smooth scroll for anchor links within content
document.querySelectorAll('.post-body a[href^="#"]').forEach(anchor => {
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

// Add table of contents functionality for headings
document.addEventListener('DOMContentLoaded', function() {
    const headings = document.querySelectorAll('.post-body h2, .post-body h3');
    if (headings.length > 3) {
        // Create table of contents
        const toc = document.createElement('div');
        toc.className = 'table-of-contents card mb-4';
        toc.innerHTML = `
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Table of Contents</h5>
            </div>
            <div class="card-body">
                <nav class="toc-nav">
                    ${Array.from(headings).map((heading, index) => {
                        const id = heading.id || `heading-${index}`;
                        heading.id = id;
                        return `<a href="#${id}" class="d-block mb-2">${heading.textContent}</a>`;
                    }).join('')}
                </nav>
            </div>
        `;
        
        // Insert after the post header
        const postHeader = document.querySelector('.post-header');
        if (postHeader) {
            postHeader.parentNode.insertBefore(toc, postHeader.nextSibling);
        }
    }
});
</script>

<?php
// Close database connection if created locally
if (isset($local_conn) && $local_conn) {
    $conn = null;
}
require_once 'includes/footer.php';
?>