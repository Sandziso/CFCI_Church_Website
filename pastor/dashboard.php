<?php
/**
 * pastor/dashboard.php – CFCI Pastor Dashboard
 * Updated to work with root login.php and correct role handling
 */
require_once '../includes/main-functions.php';   // DB, Auth, Session, CSRF – no HTML output

// Must be logged in
if (!is_logged_in()) {
    // Redirect to root login page
    header('Location: ' . rtrim(SITE_URL, '/') . '/login.php');
    exit;
}

// *** FIXED: allow pastors who are also super/admins, avoid infinite loops ***
$baseRole = $_SESSION['base_role'] ?? false;
$effectiveRole = getUserRole();

// Allow if:
//  - the user's base role is 'pastor', OR
//  - the effective role is 'super' or 'admin' (admins should be able to see everything)
if (!($baseRole === 'pastor' || in_array($effectiveRole, ['super', 'admin']))) {
    // If they are an admin, send them to the admin dashboard instead
    if (in_array($effectiveRole, ['super', 'admin'])) {
        header('Location: ' . rtrim(SITE_URL, '/') . '/admin/dashboard.php');
        exit;
    }
    // Otherwise, they are a regular member
    header('Location: ' . rtrim(SITE_URL, '/') . '/member/dashboard.php');
    exit;
}

$user_id = getUserId();

// Fetch dashboard data
$stats = [];
$recent_prayers = [];
$recent_donations = [];
$recent_members = [];
$notifications = [];

if (isset($conn) && $conn instanceof PDO) {
    try {
        // --- Stats ---
        $stats['total_members'] = db_count('users', "is_active = 1 AND role IN ('member','pastor','elder','deacon')");
        $stats['pending_prayers'] = db_count('prayer_requests', "status = 'pending'");
        $stats['monthly_donations'] = db_fetch("SELECT COALESCE(SUM(amount),0) AS total FROM donations WHERE status = 'completed' AND MONTH(donation_date) = MONTH(CURDATE()) AND YEAR(donation_date) = YEAR(CURDATE())")['total'] ?? 0;
        $stats['upcoming_events'] = db_count('events', "event_date >= CURDATE() AND is_active = 1 AND is_published = 1");

        // --- Recent prayer requests (pending) ---
        $stmt = $conn->prepare("SELECT pr.*, u.full_name AS member_name FROM prayer_requests pr LEFT JOIN users u ON pr.user_id = u.id WHERE pr.status = 'pending' ORDER BY pr.submitted_at DESC LIMIT 5");
        $stmt->execute();
        $recent_prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Recent donations (completed) ---
        $stmt = $conn->prepare("SELECT d.*, u.full_name AS donor_name FROM donations d LEFT JOIN users u ON d.user_id = u.id WHERE d.status = 'completed' ORDER BY d.donation_date DESC LIMIT 5");
        $stmt->execute();
        $recent_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Recent members (last 5 joined) ---
        $stmt = $conn->prepare("SELECT id, full_name, join_date FROM users WHERE is_active = 1 AND role IN ('member','pastor','elder','deacon') ORDER BY join_date DESC LIMIT 5");
        $stmt->execute();
        $recent_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Notifications for this pastor ---
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Pastor dashboard error: " . $e->getMessage());
    }
}

// Handle POST actions (add announcement, event, sermon, update prayer, mark read)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_announcement':
            $title = trim($_POST['ann_title'] ?? '');
            $content = trim($_POST['ann_content'] ?? '');
            if ($title && $content) {
                db_insert('announcements', [
                    'title'       => $title,
                    'content'     => $content,
                    'author_id'   => $user_id,
                    'is_published'=> 1,
                    'priority'    => 'medium'
                ]);
                SessionManager::setFlash('success', 'Announcement added!');
            }
            break;

        case 'add_event':
            $title = trim($_POST['event_title'] ?? '');
            $date = $_POST['event_date'] ?? '';
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $location = trim($_POST['location'] ?? '');
            if ($title && $date && $start) {
                db_insert('events', [
                    'title'      => $title,
                    'event_date' => $date,
                    'start_time' => $start,
                    'end_time'   => $end,
                    'location'   => $location,
                    'created_by' => $user_id,
                    'is_published'=> 1,
                    'is_active'  => 1
                ]);
                SessionManager::setFlash('success', 'Event created!');
            }
            break;

        case 'add_sermon':
            $title = trim($_POST['sermon_title'] ?? '');
            $date = $_POST['sermon_date'] ?? '';
            $passage = trim($_POST['bible_passage'] ?? '');
            if ($title && $date) {
                db_insert('sermons', [
                    'title'        => $title,
                    'preacher_id'  => $user_id,
                    'sermon_date'  => $date,
                    'bible_passage'=> $passage,
                    'is_published' => 1
                ]);
                SessionManager::setFlash('success', 'Sermon logged!');
            }
            break;

        case 'update_prayer':
            $prayer_id = (int)($_POST['prayer_id'] ?? 0);
            $new_status = $_POST['status'] ?? '';
            if ($prayer_id > 0 && in_array($new_status, ['pending','addressed','closed'])) {
                db_update('prayer_requests', ['status' => $new_status], 'id = ?', [$prayer_id]);
                SessionManager::setFlash('success', 'Prayer status updated.');
            }
            break;

        case 'mark_all_read':
            db_exec("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user_id]);
            SessionManager::setFlash('success', 'Notifications marked as read.');
            break;
    }
    // Redirect to same page to avoid form resubmission
    header('Location: ' . rtrim(SITE_URL, '/') . '/pastor/dashboard.php');
    exit;
}

// Helper function to execute query
function db_exec($sql, $params = []) {
    $stmt = Database::getInstance()->getConnection()->prepare($sql);
    $stmt->execute($params);
}

// Start output
$current_page = 'pastor-dashboard';
require_once '../includes/header.php';
?>

<section class="page-header-sm bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-0">Pastor Dashboard</h1>
                <p class="text-white-50 mb-0">Manage your church with confidence</p>
            </div>
            <div class="col-lg-4 text-end">
                <span class="text-white-50"><?= date('l, F j, Y') ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <!-- Flash Messages -->
        <?= getFlashMessage() ?>

        <!-- Stats Grid -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white rounded-4 shadow-sm p-4 d-flex align-items-center">
                    <div class="stat-icon bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width:60px; height:60px;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Members</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['total_members'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white rounded-4 shadow-sm p-4 d-flex align-items-center">
                    <div class="stat-icon bg-warning text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width:60px; height:60px;">
                        <i class="fas fa-pray fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Prayers</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['pending_prayers'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white rounded-4 shadow-sm p-4 d-flex align-items-center">
                    <div class="stat-icon bg-success text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width:60px; height:60px;">
                        <i class="fas fa-donate fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Monthly Donations</div>
                        <div class="fs-3 fw-bold">SZL <?= number_format($stats['monthly_donations'] ?? 0, 2) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white rounded-4 shadow-sm p-4 d-flex align-items-center">
                    <div class="stat-icon bg-danger text-white rounded-3 d-flex align-items-center justify-content-center me-3" style="width:60px; height:60px;">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Upcoming Events</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['upcoming_events'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <button class="btn btn-outline-primary w-100 p-4 rounded-4 shadow-sm quick-action-btn" data-bs-toggle="modal" data-bs-target="#announcementModal">
                    <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                    Add Announcement
                </button>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-outline-primary w-100 p-4 rounded-4 shadow-sm quick-action-btn" data-bs-toggle="modal" data-bs-target="#eventModal">
                    <i class="fas fa-calendar-plus fa-2x mb-2 d-block"></i>
                    Add Event
                </button>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-outline-primary w-100 p-4 rounded-4 shadow-sm quick-action-btn" data-bs-toggle="modal" data-bs-target="#sermonModal">
                    <i class="fas fa-video fa-2x mb-2 d-block"></i>
                    Add Sermon
                </button>
            </div>
            <div class="col-6 col-md-3">
                <a href="reports/" class="btn btn-outline-primary w-100 p-4 rounded-4 shadow-sm text-decoration-none">
                    <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                    View Reports
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Column -->
            <div class="col-lg-8">
                <!-- Prayer Requests -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-praying-hands text-primary me-2"></i>Prayer Requests</h5>
                        <a href="prayers.php" class="btn btn-sm btn-outline-primary">All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($recent_prayers): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_prayers as $p): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($p['member_name'] ?? 'Anonymous') ?></strong>
                                            <p class="mb-0 small text-muted"><?= htmlspecialchars(mb_strimwidth($p['request_text'], 0, 80, '...')) ?></p>
                                        </div>
                                        <form method="post" class="d-flex align-items-center">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="update_prayer">
                                            <input type="hidden" name="prayer_id" value="<?= $p['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:130px;">
                                                <option value="pending" <?= $p['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="addressed" <?= $p['status'] == 'addressed' ? 'selected' : '' ?>>Addressed</option>
                                                <option value="closed" <?= $p['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                            </select>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">No pending prayer requests.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Donations -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-donate text-primary me-2"></i>Recent Donations</h5>
                        <a href="finances.php" class="btn btn-sm btn-outline-primary">All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($recent_donations): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead><tr><th>Donor</th><th>Amount</th><th>Date</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recent_donations as $d): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($d['donor_name'] ?? 'Anonymous') ?></td>
                                                <td class="text-success fw-bold">SZL <?= number_format($d['amount'], 2) ?></td>
                                                <td><?= date('M j, Y', strtotime($d['donation_date'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">No recent donations.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- Recent Members -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-plus text-primary me-2"></i>Recent Members</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($recent_members): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_members as $m): ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <div class="avatar-initials bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><?= getUserInitials($m['full_name']) ?></div>
                                        <div><strong><?= htmlspecialchars($m['full_name']) ?></strong><br><small class="text-muted"><?= date('M j, Y', strtotime($m['join_date'])) ?></small></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">No recent members.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-bell text-primary me-2"></i>Notifications</h5>
                        <?php if ($notifications): ?>
                            <form method="post" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="mark_all_read">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Mark All Read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($notifications): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $n): ?>
                                    <div class="list-group-item <?= $n['is_read'] ? '' : 'list-group-item-light' ?>">
                                        <h6 class="mb-1"><?= htmlspecialchars($n['title']) ?></h6>
                                        <p class="mb-0 small text-muted"><?= htmlspecialchars($n['message']) ?></p>
                                        <small class="text-muted"><?= date('M j, Y H:i', strtotime($n['created_at'])) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">No notifications.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modals (Add Announcement, Event, Sermon) -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_announcement">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="announcementModalLabel">Add Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="ann_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="ann_content" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_event">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eventModalLabel">Create Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="event_title" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start *</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="sermonModal" tabindex="-1" aria-labelledby="sermonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_sermon">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="sermonModalLabel">Log Sermon</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="sermon_title" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="sermon_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bible Passage</label>
                            <input type="text" name="bible_passage" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Sermon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stat-card { transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
.quick-action-btn { transition: all 0.3s ease; }
.quick-action-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates for forms
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="event_date"]')?.value = today;
    document.querySelector('input[name="sermon_date"]')?.value = today;
});
</script>

<?php require_once '../includes/footer.php'; ?>