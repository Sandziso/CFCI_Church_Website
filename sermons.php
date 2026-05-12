<?php
/**
 * sermons.php – CFCI Church Sermons Listing
 * PRD‑compliant: dynamic DB, fallback static, accessible, filter‑enabled.
 */
require_once 'includes/header.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Grab filter parameters
$category = $_GET['category'] ?? 'all';
$preacher = $_GET['preacher'] ?? 'all';
$year     = $_GET['year'] ?? 'all';
$search   = $_GET['search'] ?? '';

$sermons = [];
$categories = [];
$preachers = [];
$years = [];

// Fetch from database if available
if (isset($conn) && $conn instanceof PDO) {
    try {
        // Build dynamic query
        $sql = "SELECT s.*, u.full_name AS preacher_name
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                WHERE s.is_published = 1";
        $params = [];

        if ($category !== 'all') {
            $sql .= " AND s.category = ?";
            $params[] = $category;
        }
        if ($preacher !== 'all') {
            $sql .= " AND s.preacher_id = ?";
            $params[] = $preacher;
        }
        if ($year !== 'all') {
            $sql .= " AND YEAR(s.sermon_date) = ?";
            $params[] = $year;
        }
        if (!empty($search)) {
            $sql .= " AND (s.title LIKE ? OR s.bible_passage LIKE ? OR s.notes_text LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY s.sermon_date DESC LIMIT 12";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filters data
        $stmt = $conn->query("SELECT DISTINCT category FROM sermons WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $conn->query("SELECT DISTINCT u.id, u.full_name FROM sermons s JOIN users u ON s.preacher_id = u.id WHERE s.is_published = 1 ORDER BY u.full_name");
        $preachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->query("SELECT DISTINCT YEAR(sermon_date) AS year FROM sermons WHERE is_published = 1 ORDER BY year DESC");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        error_log("Sermons fetch error: " . $e->getMessage());
    }
}

// Fallback static data if database empty or offline
if (empty($sermons)) {
    $sermons = [
        ['id' => 1, 'title' => 'The Power of Forgiveness', 'preacher_name' => 'Bishop Zakes Nxumalo', 'sermon_date' => '2025-06-29', 'category' => 'teaching', 'bible_passage' => 'Matthew 6:14-15', 'notes_text' => 'A sermon on the importance of forgiveness in Christian life and how to practice it daily.', 'audio_url' => '#', 'video_url' => '#', 'slides_url' => '#', 'thumbnail_url' => 'assets/images/sermons/forgiveness.jpg', 'duration' => 2730, 'views_count' => 1250],
        ['id' => 2, 'title' => 'Walking by Faith', 'preacher_name' => 'Bishop Zakes Nxumalo', 'sermon_date' => '2025-06-22', 'category' => 'faith', 'bible_passage' => '2 Corinthians 5:7', 'notes_text' => 'Exploring what it means to live a life guided by faith, not by sight, in today\'s world.', 'audio_url' => '#', 'video_url' => '#', 'slides_url' => '#', 'thumbnail_url' => 'assets/images/sermons/faith.jpg', 'duration' => 3135, 'views_count' => 980],
        ['id' => 3, 'title' => 'The Joy of Giving', 'preacher_name' => 'Pastor Michael Ndlovu', 'sermon_date' => '2025-06-15', 'category' => 'generosity', 'bible_passage' => '2 Corinthians 9:6-8', 'notes_text' => 'Discovering the biblical principles of generosity and how giving brings joy and blessing.', 'audio_url' => '#', 'video_url' => '#', 'slides_url' => '#', 'thumbnail_url' => 'assets/images/sermons/generosity.jpg', 'duration' => 2900, 'views_count' => 1100],
    ];
    $categories = ['faith', 'generosity', 'teaching'];
    $preachers = [['id' => 1, 'full_name' => 'Bishop Zakes Nxumalo'], ['id' => 110, 'full_name' => 'Pastor Michael Ndlovu']];
    $years = ['2025', '2024'];
}
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Sermons & Teaching</h1>
                <p class="text-white mb-0 fs-5">Biblical teaching to encourage, equip, and transform your life</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Sermons</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <!-- Flash Messages -->
        <?= getFlashMessage() ?>

        <!-- Search & Filter Form -->
        <div class="card shadow-sm mb-5 p-4">
            <form action="<?= SITE_URL ?>sermons.php" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label"><i class="fas fa-search me-1"></i>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, passage, notes..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($cat)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Preacher</label>
                    <select name="preacher" class="form-select">
                        <option value="all" <?= $preacher === 'all' ? 'selected' : '' ?>>All Preachers</option>
                        <?php foreach ($preachers as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $preacher == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="all" <?= $year === 'all' ? 'selected' : '' ?>>All Years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <?php if ($category !== 'all' || $preacher !== 'all' || $year !== 'all' || !empty($search)): ?>
                        <a href="<?= SITE_URL ?>sermons.php" class="btn btn-outline-secondary mt-2">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Sermons Grid -->
        <div class="row g-4">
            <?php if (empty($sermons)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-microphone-slash display-1 text-muted mb-4"></i>
                    <h3>No Sermons Found</h3>
                    <p class="text-muted">No sermons match your filters. Try adjusting your search.</p>
                </div>
            <?php else: ?>
                <?php foreach ($sermons as $sermon):
                    $date = new DateTime($sermon['sermon_date']);
                    $img = $sermon['thumbnail_url'] ? SITE_URL . $sermon['thumbnail_url'] : 'assets/images/sermons/default.jpg';
                    $duration = !empty($sermon['duration']) ? gmdate("i:s", $sermon['duration']) : '';
                    $passage = $sermon['bible_passage'] ?? '';
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sermon-card h-100 bg-white rounded-3 shadow-sm overflow-hidden">
                            <div class="sermon-image position-relative">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($sermon['title']) ?>" class="img-fluid" loading="lazy" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                <?php if ($duration): ?>
                                    <div class="position-absolute top-0 end-0 m-2 px-2 py-1 bg-dark bg-opacity-75 text-white rounded-3 small"><?= $duration ?></div>
                                <?php endif; ?>
                                <div class="play-overlay d-flex align-items-center justify-content-center">
                                    <a href="<?= SITE_URL ?>sermon-details.php?id=<?= $sermon['id'] ?>" class="btn btn-primary btn-lg rounded-circle"><i class="fas fa-play"></i></a>
                                </div>
                            </div>
                            <div class="sermon-content p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($sermon['category'] ?? 'general')) ?></span>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $date->format('M j, Y') ?></small>
                                </div>
                                <h3 class="h5 mb-2"><?= htmlspecialchars($sermon['title']) ?></h3>
                                <?php if ($passage): ?>
                                    <p class="small text-primary mb-2"><i class="fas fa-bible me-1"></i><?= htmlspecialchars($passage) ?></p>
                                <?php endif; ?>
                                <p class="small text-muted mb-3"><?= htmlspecialchars(mb_strimwidth($sermon['notes_text'] ?? 'Biblical teaching.', 0, 100, '...')) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($sermon['audio_url'])): ?>
                                            <a href="<?= SITE_URL ?>sermon-details.php?id=<?= $sermon['id'] ?>&type=audio" class="btn btn-sm btn-outline-primary"><i class="fas fa-headphones"></i> Listen</a>
                                        <?php endif; ?>
                                        <?php if (!empty($sermon['video_url'])): ?>
                                            <a href="<?= SITE_URL ?>sermon-details.php?id=<?= $sermon['id'] ?>&type=video" class="btn btn-sm btn-outline-primary"><i class="fas fa-video"></i> Watch</a>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><i class="fas fa-eye me-1"></i><?= number_format($sermon['views_count'] ?? 0) ?> views</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sermon Series Section -->
        <div class="mt-5 pt-5 border-top">
            <h3 class="text-center mb-4">Sermon Series</h3>
            <div class="row g-4">
                <?php
                // Static series fallback (or fetch from sermon_series table)
                $series = [
                    ['title' => 'Foundations of Faith', 'count' => 6, 'period' => 'Jan - Mar 2025', 'img' => 'assets/images/series/foundations.jpg'],
                    ['title' => 'Walking in Victory',   'count' => 8, 'period' => 'Apr - Jun 2025', 'img' => 'assets/images/series/victory.jpg'],
                    ['title' => 'The Heart of Worship', 'count' => 5, 'period' => 'Jul - Sep 2025', 'img' => 'assets/images/series/worship.jpg'],
                ];
                foreach ($series as $s): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="series-card h-100 bg-white rounded-3 shadow-sm overflow-hidden">
                            <img src="<?= SITE_URL . htmlspecialchars($s['img']) ?>" alt="<?= htmlspecialchars($s['title']) ?>" class="img-fluid" style="height:180px; object-fit:cover;" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                            <div class="p-4">
                                <h4 class="h5"><?= htmlspecialchars($s['title']) ?></h4>
                                <p class="text-muted small"><?= $s['count'] ?> sermons • <?= $s['period'] ?></p>
                                <a href="#" class="btn btn-sm btn-outline-primary">View Series</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center py-5 mt-5 bg-primary text-white rounded-3">
            <h2 class="mb-3">Subscribe to Our Podcast</h2>
            <p class="lead mb-4">Get new sermons delivered to your favourite podcast app automatically.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#" class="btn btn-light"><i class="fas fa-podcast me-2"></i>Apple Podcasts</a>
                <a href="#" class="btn btn-outline-light"><i class="fas fa-rss me-2"></i>RSS Feed</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Inline complementing styles for sermons cards */
.sermon-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.sermon-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg) !important;
}
.sermon-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}
.sermon-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.sermon-card:hover .sermon-image img {
    transform: scale(1.05);
}
.play-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.sermon-card:hover .play-overlay {
    opacity: 1;
}
.series-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.series-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>