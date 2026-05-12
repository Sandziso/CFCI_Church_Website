<?php
/**
 * ministries.php – CFCI Church Ministries Listing
 * PRD‑compliant: dynamic DB, fallback static, accessible, performance‑optimised.
 */
require_once 'includes/header.php';   // loads SITE_URL, $conn, auth session, helpers
$current_page = basename($_SERVER['PHP_SELF']);

$ministries = [];
$categories = [];

// Fetch from database if available
if (isset($conn) && $conn instanceof PDO) {
    try {
        // Active ministries, sorted by name
        $stmt = $conn->prepare("SELECT * FROM ministries WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get unique categories for filter buttons
        $stmt = $conn->prepare(
            "SELECT DISTINCT COALESCE(category, 'other') as category, COUNT(*) as cnt 
             FROM ministries WHERE status = 'active' 
             GROUP BY category ORDER BY category"
        );
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Ministries fetch error: " . $e->getMessage());
        // $ministries stays empty → fallback static content will be shown
    }
}

// Fallback static data when DB is empty (e.g., first run)
if (empty($ministries)) {
    $ministries = [
        ['id' => 1, 'name' => "Men's Fellowship",      'category' => 'adults',   'description' => 'Building godly men through fellowship, prayer, and biblical teaching.',               'image_url' => 'assets/images/ministries/mens-fellowship.jpg',  'meeting_day' => '1st Saturday', 'meeting_time' => '8 AM'],
        ['id' => 2, 'name' => "Women's Ministry",       'category' => 'adults',   'description' => 'Empowering women to grow in faith and support one another in Christ.',                'image_url' => 'assets/images/ministries/womens-ministry.jpg','meeting_day' => 'Thursday',     'meeting_time' => '5 PM'],
        ['id' => 3, 'name' => 'Worship Team',           'category' => 'worship',  'description' => 'Leading the congregation in heartfelt worship and praise to God.',                    'image_url' => 'assets/images/ministries/worship-team.jpg',    'meeting_day' => 'Tue & Thu',   'meeting_time' => '6 PM'],
        ['id' => 4, 'name' => 'Youth Ministry',         'category' => 'youth',    'description' => 'Engaging and nurturing the faith of our young members.',                              'image_url' => 'assets/images/ministries/youth-ministry.jpg',  'meeting_day' => 'Friday',      'meeting_time' => '6 PM'],
        ['id' => 5, 'name' => "Children's Church",      'category' => 'children', 'description' => 'Teaching children about Jesus in fun, age-appropriate ways.',                        'image_url' => 'assets/images/ministries/children-church.jpg', 'meeting_day' => 'Sunday',      'meeting_time' => '10 AM'],
        ['id' => 6, 'name' => 'Outreach Ministry',      'category' => 'outreach', 'description' => 'Serving our community and sharing the love of Christ through practical acts of service.', 'image_url' => 'assets/images/ministries/outreach.jpg',    'meeting_day' => 'Monthly',     'meeting_time' => ''],
    ];
    $categories = [
        ['category' => 'adults',   'cnt' => 2],
        ['category' => 'children', 'cnt' => 1],
        ['category' => 'outreach', 'cnt' => 1],
        ['category' => 'worship',  'cnt' => 1],
        ['category' => 'youth',    'cnt' => 1],
    ];
}
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Our Ministries</h1>
                <p class="text-white mb-0 fs-5">Find your place to serve, grow, and connect with others</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Ministries</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <!-- Intro -->
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center wow fadeInUp" data-wow-delay="0.1s">
                <h2 class="section-title">Serving Together in Christ</h2>
                <p class="lead">
                    At CFCI, we believe everyone has a role to play in God's kingdom. Our ministries provide opportunities for you to use your gifts, grow in faith, and make a difference in our community.
                </p>
            </div>
        </div>

        <!-- Filter Buttons -->
        <?php if (!empty($categories)): ?>
        <div class="row mb-5 wow fadeInUp" data-wow-delay="0.2s">
            <div class="col-12">
                <div class="ministry-filters text-center">
                    <button class="btn btn-outline-primary filter-btn active" data-filter="all">All Ministries</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="btn btn-outline-primary filter-btn" data-filter="<?= htmlspecialchars(strtolower($cat['category'])) ?>">
                            <?= htmlspecialchars(ucfirst($cat['category'])) ?> (<?= $cat['cnt'] ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ministries Grid -->
        <div class="row g-4" id="ministries-container">
            <?php $delay = 0.1; ?>
            <?php foreach ($ministries as $m): 
                $category = !empty($m['category']) ? strtolower($m['category']) : 'other';
                $img = !empty($m['image_url']) ? SITE_URL . $m['image_url'] : 'assets/images/ministries/default.jpg';
                $schedule = '';
                if (!empty($m['meeting_day'])) {
                    $schedule = $m['meeting_day'];
                    if (!empty($m['meeting_time'])) $schedule .= ' ' . $m['meeting_time'];
                }
            ?>
                <div class="col-lg-4 col-md-6 ministry-item wow fadeInUp" data-wow-delay="<?= $delay ?>s" data-category="<?= htmlspecialchars($category) ?>">
                    <div class="ministry-card h-100 bg-white rounded-3 shadow-sm overflow-hidden">
                        <div class="ministry-image position-relative">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="img-fluid" loading="lazy" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                            <div class="ministry-overlay d-flex align-items-center justify-content-center">
                                <a href="<?= SITE_URL ?>ministry-details.php?id=<?= $m['id'] ?>" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-2"><?= htmlspecialchars($m['name']) ?></h3>
                            <p class="small text-muted mb-3">
                                <?= htmlspecialchars(mb_strimwidth($m['description'] ?? 'Join us in serving and growing together.', 0, 100, '...')) ?>
                            </p>
                            <div class="ministry-meta small text-muted">
                                <?php if ($schedule): ?>
                                    <span class="me-3"><i class="far fa-clock me-1"></i><?= htmlspecialchars($schedule) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-user-friends me-1"></i><?= htmlspecialchars(ucfirst($category)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $delay += 0.1; ?>
            <?php endforeach; ?>
        </div>

        <!-- Call to Action -->
        <div class="cta-section text-center py-5 mt-5 rounded-3 wow fadeInUp" data-wow-delay="0.1s" style="background: var(--gradient-primary);">
            <h2 class="mb-3 text-white">Ready to Get Involved?</h2>
            <p class="lead mb-4 text-white-50">We have a place for you! Join a ministry and start serving today.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <?php if (is_logged_in()): ?>
                    <a href="<?= SITE_URL ?>member/dashboard.php?tab=ministries" class="btn btn-warning btn-lg">Join a Ministry</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>auth/login.php?redirect=<?= urlencode(SITE_URL . 'ministries.php') ?>" class="btn btn-warning btn-lg">Login to Join</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>contact.php?subject=Ministry%20Inquiry" class="btn btn-outline-light btn-lg">Contact Ministry Leader</a>
            </div>
        </div>

        <!-- Ministry Stats -->
        <div class="ministry-stats row mt-5 pt-5 wow fadeInUp" data-wow-delay="0.2s">
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold" style="color: var(--primary-blue);"><?= count($ministries) ?>+</div>
                <div class="stat-label">Active Ministries</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold" style="color: var(--primary-blue);">150+</div>
                <div class="stat-label">Ministry Members</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold" style="color: var(--primary-blue);">52+</div>
                <div class="stat-label">Events Yearly</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold" style="color: var(--primary-blue);">1000+</div>
                <div class="stat-label">Hours Served Monthly</div>
            </div>
        </div>
    </div>
</section>

<style>
/* ---------- Ministry cards (inline, complements style.css) ---------- */
.ministry-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.ministry-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg) !important;
}
.ministry-image {
    height: 200px;
    overflow: hidden;
}
.ministry-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.ministry-card:hover .ministry-image img {
    transform: scale(1.05);
}
.ministry-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.65);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.ministry-card:hover .ministry-overlay {
    opacity: 1;
}
.ministry-filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}
.filter-btn {
    padding: 8px 20px;
    border-radius: 30px;
    transition: all 0.3s ease;
    border: 2px solid var(--primary-blue);
    color: var(--primary-blue);
    background: transparent;
}
.filter-btn:hover,
.filter-btn.active {
    background: var(--gradient-primary);
    color: #fff;
    border-color: transparent;
}
.stat-number { line-height: 1; }
.stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-top: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const ministryItems = document.querySelectorAll('.ministry-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            ministryItems.forEach(item => {
                const category = item.getAttribute('data-category');
                item.style.display = (filter === 'all' || category === filter) ? 'block' : 'none';
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>