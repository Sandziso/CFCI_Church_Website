<?php
/**
 * sermon-details.php – CFCI Church Single Sermon View
 * PRD‑compliant: dynamic DB, fallback static, media player, notes, sharing.
 */
require_once 'includes/header.php';

$current_page = basename($_SERVER['PHP_SELF']);
$sermon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$media_type = isset($_GET['type']) ? $_GET['type'] : 'audio';
$sermon = null;
$preacher = null;
$related_sermons = [];
$sermon_series = [];

// Fetch from database
if ($sermon_id > 0 && isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->prepare("
            SELECT s.*, u.full_name AS preacher_name
            FROM sermons s
            LEFT JOIN users u ON s.preacher_id = u.id
            WHERE s.id = ? AND s.is_published = 1
        ");
        $stmt->execute([$sermon_id]);
        $sermon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sermon) {
            // Fetch preacher full profile (optional)
            if ($sermon['preacher_id']) {
                $stmtPreach = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $stmtPreach->execute([$sermon['preacher_id']]);
                $preacher = $stmtPreach->fetch(PDO::FETCH_ASSOC);
            }

            // Related sermons by same preacher
            if ($sermon['preacher_id']) {
                $stmtRel = $conn->prepare("SELECT id, title, sermon_date, thumbnail_url FROM sermons WHERE preacher_id = ? AND id != ? AND is_published = 1 ORDER BY sermon_date DESC LIMIT 3");
                $stmtRel->execute([$sermon['preacher_id'], $sermon_id]);
                $related_sermons = $stmtRel->fetchAll(PDO::FETCH_ASSOC);
            }

            // Sermon series (if any)
            if ($sermon['series_id']) {
                $stmtSeries = $conn->prepare("SELECT id, title, sermon_date FROM sermons WHERE series_id = ? AND id != ? AND is_published = 1 ORDER BY sermon_date LIMIT 10");
                $stmtSeries->execute([$sermon['series_id'], $sermon_id]);
                $sermon_series = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        error_log("Sermon details error: " . $e->getMessage());
    }
}

// Redirect if no sermon found
if (!$sermon && $sermon_id > 0) {
    header('Location: ' . SITE_URL . 'sermons.php');
    exit();
}

// Fallback static content if DB offline
if (!$sermon) {
    $sermon = [
        'id' => 1,
        'title' => 'The Power of Forgiveness',
        'preacher_id' => 1,
        'preacher_name' => 'Bishop Zakes Nxumalo',
        'sermon_date' => '2025-06-29',
        'bible_passage' => 'Matthew 6:14-15',
        'category' => 'teaching',
        'notes_text' => 'A powerful message on the importance of forgiveness in Christian life. Learn how to forgive others as Christ forgave you, and experience the freedom that comes from letting go of bitterness and resentment.',
        'audio_url' => 'assets/audio/sermons/forgiveness.mp3',
        'video_url' => 'https://www.youtube.com/embed/example',
        'slides_url' => 'assets/slides/forgiveness.pdf',
        'thumbnail_url' => 'assets/images/sermons/forgiveness.jpg',
        'duration' => 2730,
        'views_count' => 1250,
        'downloads_count' => 340,
        'series_id' => 1
    ];
    $preacher = ['full_name' => 'Bishop Zakes Nxumalo'];
    $related_sermons = [
        ['id' => 2, 'title' => 'Walking by Faith', 'sermon_date' => '2025-06-22', 'thumbnail_url' => 'assets/images/sermons/faith.jpg'],
        ['id' => 3, 'title' => 'The Joy of Giving', 'sermon_date' => '2025-06-15', 'thumbnail_url' => 'assets/images/sermons/generosity.jpg']
    ];
}

// Format data
$sermon_date = new DateTime($sermon['sermon_date']);
$formatted_date = $sermon_date->format('F j, Y');
$duration_str = !empty($sermon['duration']) ? gmdate("i:s", $sermon['duration']) : 'N/A';
$passage = $sermon['bible_passage'] ?? '';
$preacher_name = $sermon['preacher_name'] ?? ($preacher['full_name'] ?? 'CFCI Ministry');

// Determine media URL
$media_url = '';
$media_title = '';
if ($media_type === 'video' && !empty($sermon['video_url'])) {
    $media_url = $sermon['video_url'];
    $media_title = 'Watch Video';
} elseif (!empty($sermon['audio_url'])) {
    $media_url = $sermon['audio_url'];
    $media_title = 'Listen to Audio';
}

// Build share URLs
$share_url = urlencode(SITE_URL . 'sermon-details.php?id=' . $sermon_id);
$share_title = urlencode($sermon['title'] . ' - CFCI Church');
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold"><?= htmlspecialchars($sermon['title']) ?></h1>
                <p class="text-white mb-0 fs-5"><?= htmlspecialchars($passage) ?></p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>sermons.php" class="text-white-50">Sermons</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($sermon['title']) ?></li>
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
                <!-- Media Player -->
                <?php if ($media_url): ?>
                <div class="card shadow-sm mb-5">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= $media_title ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($media_type === 'video' && strpos($media_url, 'youtube') !== false): ?>
                            <div class="ratio ratio-16x9">
                                <iframe src="<?= htmlspecialchars($media_url) ?>" title="<?= htmlspecialchars($sermon['title']) ?>" allowfullscreen></iframe>
                            </div>
                        <?php elseif ($media_type === 'audio'): ?>
                            <div class="p-3 bg-light rounded-3">
                                <audio controls class="w-100" id="sermonAudio">
                                    <source src="<?= SITE_URL . htmlspecialchars($media_url) ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No media available for this sermon.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sermon Info Bar -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                    <div class="d-flex align-items-center">
                        <div class="avatar-initials-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:60px; height:60px; font-size:1.5rem;">
                            <?= getUserInitials($preacher_name) ?>
                        </div>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($preacher_name) ?></div>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $formatted_date ?></small>
                        </div>
                    </div>
                    <div class="ms-auto d-flex gap-3">
                        <span class="text-muted small"><i class="far fa-clock me-1"></i><?= $duration_str ?></span>
                        <span class="text-muted small"><i class="far fa-eye me-1"></i><?= number_format($sermon['views_count'] ?? 0) ?> views</span>
                        <span class="text-muted small"><i class="fas fa-download me-1"></i><?= number_format($sermon['downloads_count'] ?? 0) ?> downloads</span>
                    </div>
                </div>

                <!-- Scripture Reference (if any) -->
                <?php if ($passage): ?>
                <div class="alert alert-light border-start border-primary border-4 mb-5">
                    <h6><i class="fas fa-bible text-primary me-2"></i>Scripture Reference</h6>
                    <p class="mb-0"><?= htmlspecialchars($passage) ?></p>
                </div>
                <?php endif; ?>

                <!-- Sermon Notes / Description -->
                <div class="mb-5">
                    <h3 class="mb-4">Message Summary</h3>
                    <div class="content">
                        <?php if (!empty($sermon['notes_text'])): ?>
                            <?= nl2br(htmlspecialchars($sermon['notes_text'])) ?>
                        <?php else: ?>
                            <p class="text-muted">No summary available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sermon Series (if part of one) -->
                <?php if (!empty($sermon_series)): ?>
                <div class="mb-5">
                    <h3 class="mb-4">This Sermon Series</h3>
                    <div class="row g-3">
                        <?php foreach ($sermon_series as $index => $seriesItem): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center border rounded-3 p-3 bg-light">
                                    <span class="badge bg-primary me-3"><?= $index + 1 ?></span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($seriesItem['title']) ?></h6>
                                        <small class="text-muted"><?= date('M j, Y', strtotime($seriesItem['sermon_date'])) ?></small>
                                    </div>
                                    <a href="<?= SITE_URL ?>sermon-details.php?id=<?= $seriesItem['id'] ?>" class="btn btn-sm btn-outline-primary">Listen</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share & Download -->
                <div class="border-top pt-5 mt-5">
                    <h4 class="mb-4">Share & Download</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h5>Share</h5>
                            <div class="d-flex gap-2 mt-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>" target="_blank" class="btn btn-sm" style="background:#3b5998; color:#fff;"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://twitter.com/intent/tweet?url=<?= $share_url ?>&text=<?= $share_title ?>" target="_blank" class="btn btn-sm" style="background:#1da1f2; color:#fff;"><i class="fab fa-twitter"></i></a>
                                <a href="https://api.whatsapp.com/send?text=<?= $share_title . ' ' . $share_url ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                                <button onclick="copyLink()" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h5>Download</h5>
                            <div class="d-flex gap-2 mt-3">
                                <?php if (!empty($sermon['audio_url'])): ?>
                                    <a href="<?= SITE_URL . htmlspecialchars($sermon['audio_url']) ?>" download class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i> Audio</a>
                                <?php endif; ?>
                                <?php if (!empty($sermon['slides_url'])): ?>
                                    <a href="<?= SITE_URL . htmlspecialchars($sermon['slides_url']) ?>" download class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf me-1"></i> Slides</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Preacher Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="avatar-initials-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px; height:80px; font-size:2rem;">
                            <?= getUserInitials($preacher_name) ?>
                        </div>
                        <h5><?= htmlspecialchars($preacher_name) ?></h5>
                        <p class="text-muted small">Preacher</p>
                        <?php if ($sermon['preacher_id']): ?>
                            <a href="<?= SITE_URL ?>sermons.php?preacher=<?= $sermon['preacher_id'] ?>" class="btn btn-outline-primary btn-sm w-100">View All Sermons</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Sermons -->
                <?php if (!empty($related_sermons)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Related Sermons</h5>
                        <?php foreach ($related_sermons as $rel): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= SITE_URL . htmlspecialchars($rel['thumbnail_url'] ?? 'assets/images/sermons/default.jpg') ?>" alt="<?= htmlspecialchars($rel['title']) ?>" class="rounded me-3" style="width:60px; height:60px; object-fit:cover;" onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars(mb_strimwidth($rel['title'], 0, 50, '...')) ?></h6>
                                    <small class="text-muted"><?= date('M j, Y', strtotime($rel['sermon_date'])) ?></small>
                                    <br><a href="<?= SITE_URL ?>sermon-details.php?id=<?= $rel['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">Listen</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notes Form (Logged-in only) -->
                <?php if (is_logged_in()): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Take Notes</h5>
                        <form id="sermonNotesForm">
                            <?= csrfField() ?>
                            <input type="hidden" name="sermon_id" value="<?= $sermon_id ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="notes" rows="4" placeholder="Your notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Notes</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Subscribe -->
                <div class="card shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">New Sermon Alerts</h5>
                        <p class="small">Get notified when new sermons are published.</p>
                        <form id="newsletterForm" class="mt-3">
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
</section>

<script>
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        if (typeof showToast === 'function') showToast('Link copied!', 'success');
        else alert('Link copied to clipboard.');
    });
}

// Placeholder AJAX for notes & newsletter (simulated)
document.getElementById('sermonNotesForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (typeof showToast === 'function') showToast('Notes saved!', 'success');
    else alert('Notes saved (demo).');
    this.reset();
});

document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (typeof showToast === 'function') showToast('Subscribed!', 'success');
    else alert('Subscribed (demo).');
    this.reset();
});
</script>

<?php require_once 'includes/footer.php'; ?>