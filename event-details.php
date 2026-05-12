<?php
/**
 * event-details.php – CFCI Church Single Event View
 * PRD‑compliant: dynamic DB, fallback static, integrated auth & CSRF.
 */
require_once 'includes/header.php';  // loads SITE_URL, $conn, auth, session, helpers

$current_page = basename($_SERVER['PHP_SELF']);
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;
$attendees = [];
$user_registration = null;
$total_registered = 0;

// Fetch from database
if ($event_id > 0 && isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->prepare("
            SELECT e.*, 
                   ec.name AS category_name,
                   ec.color AS category_color,
                   ec.icon  AS category_icon,
                   u.full_name AS created_by_name
            FROM events e
            LEFT JOIN event_categories ec ON e.category_id = ec.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ? AND e.is_active = 1 AND e.is_published = 1
        ");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            // Total registered (from event_registrations)
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM event_registrations 
                WHERE event_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$event_id]);
            $total_registered = (int)$stmt->fetchColumn();

            // Check if current user is registered
            if (is_logged_in()) {
                $stmt = $conn->prepare("
                    SELECT id, status, guests 
                    FROM event_registrations 
                    WHERE event_id = ? AND user_id = ? AND status != 'cancelled'
                ");
                $stmt->execute([$event_id, getUserId()]);
                $user_registration = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Recent attendees (from event_registrations)
            $stmt = $conn->prepare("
                SELECT er.status, er.guests, u.full_name, u.email
                FROM event_registrations er
                JOIN users u ON er.user_id = u.id
                WHERE er.event_id = ? AND er.status != 'cancelled'
                ORDER BY er.registration_date DESC
                LIMIT 10
            ");
            $stmt->execute([$event_id]);
            $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Event details fetch error: " . $e->getMessage());
    }
}

// Redirect if no event found
if (!$event && $event_id > 0) {
    header('Location: ' . SITE_URL . 'events.php');
    exit();
}

// Provide a static fallback if DB is unavailable
if (!$event) {
    $event = [
        'id'               => 1,
        'title'            => 'Annual Family Fun Day',
        'event_date'       => '2025-09-15',
        'start_time'       => '09:00:00',
        'end_time'         => '16:00:00',
        'location'         => 'Prince of Wales Stadium',
        'address'          => 'Prince of Wales Park, Manzini, Eswatini',
        'description'      => '<p>Join us for our much-anticipated Annual Family Fun Day!</p>...',
        'max_attendees'    => 200,
        'registration_deadline' => '2025-09-10',
        'image_url'        => 'assets/images/events/default.jpg',
        'category_name'    => 'Special Event',
        'category_color'   => '#f39c12',
        'category_icon'    => 'fas fa-star',
        'created_by_name'  => 'Bishop Zakes Nxumalo',
        'cost'             => 0,
        'is_recurring'     => 0,
        'recurring_pattern'=> null,
    ];
    $total_registered = 125;
    $attendees = [
        ['full_name' => 'John Dlamini', 'status' => 'confirmed', 'guests' => 3],
        ['full_name' => 'Sarah Nkosi',  'status' => 'confirmed', 'guests' => 2],
        ['full_name' => 'Thomas Mbeki', 'status' => 'attended',  'guests' => 4],
    ];
}

// Format dates and times
$event_date = new DateTime($event['event_date']);
$formatted_date = $event_date->format('l, F j, Y');
$start_time = date('g:i A', strtotime($event['start_time']));
$end_time   = !empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '';
$time_range  = $end_time ? "$start_time - $end_time" : $start_time;

// Registration deadline status
$registration_closed = false;
if (!empty($event['registration_deadline'])) {
    $deadline = new DateTime($event['registration_deadline']);
    if ((new DateTime()) > $deadline) {
        $registration_closed = true;
    }
}

// Check if event is full
$is_full = !empty($event['max_attendees']) && $total_registered >= $event['max_attendees'];
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold"><?= htmlspecialchars($event['title']) ?></h1>
                <p class="text-white mb-0 fs-5"><?= htmlspecialchars($event['category_name'] ?? 'Church Event') ?></p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>events.php" class="text-white-50">Events</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($event['title']) ?></li>
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
                <div class="event-details-card bg-white p-4 rounded-3 shadow-sm mb-5">
                    <!-- Event Header -->
                    <div class="d-flex flex-wrap align-items-center mb-4">
                        <div class="event-date-badge me-4">
                            <div class="bg-primary text-white rounded-3 d-flex flex-column align-items-center justify-content-center" style="width:80px; height:80px;">
                                <span class="small"><?= $event_date->format('M') ?></span>
                                <span class="fs-3 fw-bold lh-1"><?= $event_date->format('d') ?></span>
                                <span class="small"><?= $event_date->format('Y') ?></span>
                            </div>
                        </div>
                        <div>
                            <?php if ($is_full): ?>
                                <span class="badge bg-danger mb-2">Event Full</span>
                            <?php elseif ($registration_closed): ?>
                                <span class="badge bg-warning mb-2">Registration Closed</span>
                            <?php endif; ?>
                            <div class="event-meta text-muted">
                                <span><i class="far fa-clock me-2"></i><?= $time_range ?></span>
                                <span class="mx-2">•</span>
                                <span><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Event Image -->
                    <?php if (!empty($event['image_url'])): ?>
                    <div class="mb-4">
                        <img src="<?= SITE_URL . htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="img-fluid rounded-3" onerror="this.src='<?= SITE_URL ?>assets/images/events/default.jpg'">
                    </div>
                    <?php endif; ?>

                    <!-- Quick Info Grid -->
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <div class="info-card p-3 bg-light rounded-3">
                                <h5><i class="fas fa-map-marker-alt text-primary me-2"></i>Location</h5>
                                <p class="mb-1"><?= htmlspecialchars($event['location']) ?></p>
                                <?php if (!empty($event['address'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($event['address']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card p-3 bg-light rounded-3">
                                <h5><i class="fas fa-users text-primary me-2"></i>Attendance</h5>
                                <p><?= number_format($total_registered) ?> registered</p>
                                <?php if (!empty($event['max_attendees'])): ?>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width:<?= min(100, ($total_registered / $event['max_attendees']) * 100) ?>%" aria-valuenow="<?= $total_registered ?>" aria-valuemin="0" aria-valuemax="<?= $event['max_attendees'] ?>"></div>
                                    </div>
                                    <small class="text-muted"><?= $total_registered ?> / <?= $event['max_attendees'] ?> spots</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($event['cost'] > 0): ?>
                        <div class="col-md-6">
                            <div class="info-card p-3 bg-light rounded-3">
                                <h5><i class="fas fa-money-bill-wave text-primary me-2"></i>Cost</h5>
                                <p>SZL <?= number_format($event['cost'], 2) ?> per person</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Event Description -->
                    <div class="event-description mb-5">
                        <h3 class="mb-4">About This Event</h3>
                        <?php if (!empty($event['description'])): ?>
                            <?= $event['description'] ?>
                        <?php else: ?>
                            <p class="text-muted">No detailed description available yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Registration Section -->
                    <div class="border-top pt-5">
                        <h3 class="mb-4">Registration</h3>
                        <?php if ($is_full): ?>
                            <div class="alert alert-warning">This event is fully booked.</div>
                        <?php elseif ($registration_closed): ?>
                            <div class="alert alert-warning">Registration has closed.</div>
                        <?php elseif (is_logged_in() && $user_registration): ?>
                            <div class="alert alert-success d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle me-2"></i>
                                    You are registered (Status: <?= ucfirst($user_registration['status']) ?><?= $user_registration['guests'] ? ", +{$user_registration['guests']} guest(s)" : '' ?>)
                                </div>
                                <form action="<?= SITE_URL ?>event-register.php" method="post" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                    <input type="hidden" name="cancel" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel your registration?')">Cancel</button>
                                </form>
                            </div>
                        <?php elseif (is_logged_in()): ?>
                            <a href="<?= SITE_URL ?>event-register.php?id=<?= $event_id ?>" class="btn btn-primary btn-lg">Register Now</a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Please <a href="<?= SITE_URL ?>auth/login.php?redirect=<?= urlencode(SITE_URL . 'event-details.php?id=' . $event_id) ?>" class="alert-link">login</a> to register.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Event Organiser -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title">Event Organiser</h5>
                        <div class="avatar-initials-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:100px; height:100px; font-size:2rem;">
                            <?= getUserInitials($event['created_by_name'] ?? 'CFCI') ?>
                        </div>
                        <h6 class="mt-3"><?= htmlspecialchars($event['created_by_name'] ?? 'CFCI Team') ?></h6>
                        <a href="<?= SITE_URL ?>contact.php?subject=<?= urlencode('Event: ' . $event['title']) ?>" class="btn btn-outline-primary btn-sm mt-2">Contact</a>
                    </div>
                </div>

                <!-- Attendees (if any) -->
                <?php if (!empty($attendees)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Recently Registered</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($attendees as $att): ?>
                                <li class="list-group-item d-flex align-items-center px-0 py-2">
                                    <div class="avatar-initials-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-size:0.9rem;">
                                        <?= getUserInitials($att['full_name']) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($att['full_name']) ?></div>
                                        <small class="text-muted"><?= ucfirst($att['status']) ?><?= $att['guests'] ? " +{$att['guests']} guest(s)" : '' ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Share This Event</h5>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . 'event-details.php?id=' . $event_id) ?>" target="_blank" class="btn btn-sm" style="background:#3b5998; color:#fff;"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($event['title'] . ': ' . SITE_URL . 'event-details.php?id=' . $event_id) ?>" target="_blank" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                            <button class="btn btn-sm btn-secondary" onclick="copyEventLink()"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyEventLink() {
    const url = '<?= SITE_URL ?>event-details.php?id=<?= $event_id ?>';
    navigator.clipboard.writeText(url).then(() => {
        if (typeof showToast === 'function') showToast('Link copied!', 'success');
        else alert('Link copied to clipboard.');
    }).catch(() => {
        prompt('Copy manually:', url);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>