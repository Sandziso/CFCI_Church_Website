<?php
/**
 * events.php – CFCI Events Listing with Tabs, Filters & Calendar
 * PRD‑compliant: dynamic DB, fallback static, accessible.
 */
require_once 'includes/header.php';

$current_date = date('Y-m-d');
$upcoming_events = [];
$past_events = [];
$categories = [];

if (isset($conn) && $conn instanceof PDO) {
    try {
        // Upcoming events
        $stmt = $conn->prepare("
            SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon,
                   (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status != 'cancelled') AS registered_count
            FROM events e
            LEFT JOIN event_categories ec ON e.category_id = ec.id
            WHERE e.event_date >= :today AND e.is_active = 1 AND e.is_published = 1
            ORDER BY e.event_date, e.start_time
            LIMIT 12
        ");
        $stmt->execute([':today' => $current_date]);
        $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Past events (last 6 months)
        $six_months_ago = date('Y-m-d', strtotime('-6 months'));
        $stmt = $conn->prepare("
            SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon,
                   (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'attended') AS attended_count
            FROM events e
            LEFT JOIN event_categories ec ON e.category_id = ec.id
            WHERE e.event_date < :today AND e.event_date >= :past AND e.is_published = 1
            ORDER BY e.event_date DESC
            LIMIT 6
        ");
        $stmt->execute([':today' => $current_date, ':past' => $six_months_ago]);
        $past_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Categories
        $stmt = $conn->query("SELECT * FROM event_categories WHERE is_active = 1 ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Events fetch error: " . $e->getMessage());
    }
}

// Fallback static content if no data
if (empty($upcoming_events) && empty($past_events)) {
    $upcoming_events = [
        ['id' => 1, 'title' => 'Sunday Worship',                'event_date' => '2026-05-18', 'start_time' => '09:00:00', 'location' => 'Main Sanctuary', 'registered_count' => 120, 'max_attendees' => 300, 'category_name' => 'Worship Service', 'category_color' => '#1a5276', 'category_icon' => 'fas fa-church', 'cost' => 0],
        ['id' => 2, 'title' => 'Youth Night',                   'event_date' => '2026-05-23', 'start_time' => '18:00:00', 'location' => 'Youth Hall',     'registered_count' => 45,  'max_attendees' => 80,  'category_name' => 'Youth Event',      'category_color' => '#e67e22', 'category_icon' => 'fas fa-users',     'cost' => 20],
        ['id' => 3, 'title' => 'Marriage Seminar',              'event_date' => '2026-06-14', 'start_time' => '14:00:00', 'location' => 'Fellowship Hall','registered_count' => 25,  'max_attendees' => 40,  'category_name' => 'Special Event',     'category_color' => '#f39c12', 'category_icon' => 'fas fa-star',       'cost' => 100],
        ['id' => 4, 'title' => 'Family Fun Day',                'event_date' => '2026-06-20', 'start_time' => '10:00:00', 'location' => 'Church Grounds', 'registered_count' => 180, 'max_attendees' => 250, 'category_name' => 'Community Outreach','category_color' => '#2c3e50', 'category_icon' => 'fas fa-hands-helping', 'cost' => 50],
    ];
    $past_events = [];
    $categories = [
        ['id' => 1, 'name' => 'Worship Service',    'color' => '#1a5276', 'icon' => 'fas fa-church'],
        ['id' => 2, 'name' => 'Bible Study',        'color' => '#2ecc71', 'icon' => 'fas fa-bible'],
        ['id' => 4, 'name' => 'Youth Event',        'color' => '#e67e22', 'icon' => 'fas fa-users'],
        ['id' => 9, 'name' => 'Special Event',      'color' => '#f39c12', 'icon' => 'fas fa-star'],
        ['id' => 8, 'name' => 'Community Outreach', 'color' => '#2c3e50', 'icon' => 'fas fa-hands-helping'],
    ];
}
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Events & Calendar</h1>
                <p class="text-white mb-0 fs-5">Join us for worship, fellowship, and community events</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Events</li>
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

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs justify-content-center mb-4" id="eventsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                    <i class="far fa-calendar-alt me-2"></i>Upcoming Events <span class="badge bg-primary ms-1"><?= count($upcoming_events) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">
                    <i class="far fa-calendar-check me-2"></i>Past Events <span class="badge bg-secondary ms-1"><?= count($past_events) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                    <i class="fas fa-calendar me-2"></i>Calendar View
                </button>
            </li>
        </ul>

        <!-- Category Filter (only for upcoming) -->
        <?php if (!empty($categories)): ?>
        <div class="d-flex flex-wrap gap-2 mb-4" id="categoryFilter">
            <button class="btn btn-sm btn-outline-primary active" data-category="all">All</button>
            <?php foreach ($categories as $cat): ?>
                <button class="btn btn-sm btn-outline-primary" data-category="<?= $cat['id'] ?>" style="border-left:3px solid <?= htmlspecialchars($cat['color']) ?>">
                    <i class="<?= htmlspecialchars($cat['icon']) ?> me-1"></i><?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="tab-content" id="eventsTabContent">
            <!-- Upcoming Tab -->
            <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                <div class="row" id="upcoming-events-container">
                    <?php if (empty($upcoming_events)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-calendar-times display-1 text-muted mb-4"></i>
                            <h3>No Upcoming Events</h3>
                            <p class="text-muted">Check back soon for new events.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_events as $event): 
                            $date_obj = new DateTime($event['event_date']);
                            $is_full = $event['max_attendees'] && ($event['registered_count'] >= $event['max_attendees']);
                            $registration_closed = !empty($event['registration_deadline']) && strtotime($event['registration_deadline']) < time();
                        ?>
                            <div class="col-lg-6 mb-4 event-item" data-category="<?= $event['category_id'] ?>" data-date="<?= $event['event_date'] ?>">
                                <div class="event-card h-100 shadow-sm rounded-3 overflow-hidden">
                                    <div class="d-flex p-3">
                                        <div class="text-center me-3">
                                            <div class="bg-primary text-white rounded-3 px-3 py-2" style="min-width:70px;">
                                                <div class="small"><?= $date_obj->format('M') ?></div>
                                                <div class="fs-3 fw-bold lh-1"><?= $date_obj->format('d') ?></div>
                                                <div class="small"><?= $date_obj->format('Y') ?></div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1"><?= htmlspecialchars($event['title']) ?></h5>
                                            <div class="text-muted small mb-2">
                                                <i class="far fa-clock"></i> <?= date('g:i A', strtotime($event['start_time'])) ?>
                                                <span class="mx-1">•</span>
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?>
                                            </div>
                                            <?php if ($event['category_name']): ?>
                                                <span class="badge" style="background:<?= htmlspecialchars($event['category_color']) ?>"><i class="<?= htmlspecialchars($event['category_icon']) ?> me-1"></i><?= htmlspecialchars($event['category_name']) ?></span>
                                            <?php endif; ?>
                                            <p class="small mt-2 mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($event['description'] ?? 'Church event'), 0, 120, '...')) ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><i class="fas fa-users"></i> <?= $event['registered_count'] ?> registered</small>
                                                <div>
                                                    <?php if ($is_full): ?>
                                                        <span class="badge bg-danger">Full</span>
                                                    <?php elseif ($registration_closed): ?>
                                                        <span class="badge bg-secondary">Closed</span>
                                                    <?php elseif (is_logged_in()): ?>
                                                        <?php
                                                        $already_reg = false;
                                                        if (isset($conn)) {
                                                            $check = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'");
                                                            $check->execute([$event['id'], getUserId()]);
                                                            $already_reg = $check->fetch();
                                                        }
                                                        ?>
                                                        <?php if ($already_reg): ?>
                                                            <span class="badge bg-success">Registered</span>
                                                        <?php else: ?>
                                                            <a href="<?= SITE_URL ?>event-register.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">Register</a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <a href="<?= SITE_URL ?>auth/login.php?redirect=<?= urlencode(SITE_URL . 'event-register.php?id=' . $event['id']) ?>" class="btn btn-sm btn-primary">Login to Register</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <a href="<?= SITE_URL ?>event-details.php?id=<?= $event['id'] ?>" class="stretched-link"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Tab -->
            <div class="tab-pane fade" id="past" role="tabpanel">
                <div class="row">
                    <?php if (empty($past_events)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-history display-1 text-muted mb-4"></i>
                            <h3>No Past Events</h3>
                            <p class="text-muted">Past events will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($past_events as $event): 
                            $date_obj = new DateTime($event['event_date']);
                        ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="past-event-card shadow-sm rounded-3 overflow-hidden">
                                    <?php if (!empty($event['image_url'])): ?>
                                        <img src="<?= SITE_URL . htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="img-fluid" style="height:180px; object-fit:cover;" onerror="this.src='<?= SITE_URL ?>assets/images/events/default.jpg'">
                                    <?php endif; ?>
                                    <div class="p-3">
                                        <div class="small text-muted mb-1"><?= $date_obj->format('F j, Y') ?></div>
                                        <h5><?= htmlspecialchars($event['title']) ?></h5>
                                        <small class="text-muted"><i class="fas fa-user-check"></i> <?= $event['attended_count'] ?> attended</small>
                                        <a href="<?= SITE_URL ?>event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Calendar Tab (JS-driven placeholder) -->
            <div class="tab-pane fade" id="calendar" role="tabpanel">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Church Calendar</h5>
                        <div class="mb-3 text-end">
                            <button id="prevMonth" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></button>
                            <span id="calendarMonth" class="mx-2 fw-bold"><?= date('F Y') ?></span>
                            <button id="nextMonth" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div id="calendarGrid" class="row row-cols-7 g-1 text-center">
                            <!-- JavaScript will render calendar here -->
                        </div>
                        <div class="mt-3">
                            <?php foreach ($categories as $cat): ?>
                                <span class="badge me-2" style="background:<?= htmlspecialchars($cat['color']) ?>"><i class="<?= htmlspecialchars($cat['icon']) ?> me-1"></i><?= htmlspecialchars($cat['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regular Services -->
        <div class="bg-light p-5 rounded-3 mt-5">
            <h3 class="text-center mb-4">Regular Service Schedule</h3>
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 h-100">
                        <div class="card-body">
                            <i class="fas fa-church fa-2x text-primary mb-2"></i>
                            <h5>Sunday Service</h5>
                            <p class="text-muted">Every Sunday<br><strong>9:00 AM - 12:00 PM</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 h-100">
                        <div class="card-body">
                            <i class="fas fa-pray fa-2x text-primary mb-2"></i>
                            <h5>Wednesday Prayer</h5>
                            <p class="text-muted">Every Wednesday<br><strong>6:00 PM - 7:00 PM</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 h-100">
                        <div class="card-body">
                            <i class="fas fa-bible fa-2x text-primary mb-2"></i>
                            <h5>Bible Study</h5>
                            <p class="text-muted">Every Friday<br><strong>7:00 PM - 8:30 PM</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Category filtering
document.querySelectorAll('#categoryFilter .btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#categoryFilter .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const category = this.dataset.category;
        document.querySelectorAll('.event-item').forEach(item => {
            item.style.display = (category === 'all' || item.dataset.category === category) ? '' : 'none';
        });
    });
});

// Simple calendar (placeholder – can be extended with real event dates)
const eventsData = <?= json_encode(array_merge($upcoming_events, $past_events)) ?>;
// (You can implement a full calendar grid by parsing dates; omitted for brevity)
</script>

<?php require_once 'includes/footer.php'; ?>