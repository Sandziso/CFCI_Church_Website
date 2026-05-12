<?php
/**
 * event-register.php – CFCI Event Registration
 * Handles form submission and cancellation with CSRF protection.
 */
require_once 'includes/header.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$event = null;

// Redirect if no ID or not logged in
if (!$event_id) {
    header('Location: ' . SITE_URL . 'events.php');
    exit();
}
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = 'event-register.php?id=' . $event_id;
    header('Location: ' . SITE_URL . 'auth/login.php');
    exit();
}

$user_id = getUserId();

// Fetch event details
if (isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->prepare("SELECT e.*, ec.name AS category_name, ec.color AS category_color FROM events e LEFT JOIN event_categories ec ON e.category_id = ec.id WHERE e.id = ? AND e.is_active = 1 AND e.is_published = 1");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $_SESSION['error'] = 'Event not found.';
            header('Location: ' . SITE_URL . 'events.php');
            exit();
        }

        // Process registration cancellation (POST with cancel flag)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
            validateCsrf();
            $stmt = $conn->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE event_id = ? AND user_id = ? AND status != 'cancelled'");
            $stmt->execute([$event_id, $user_id]);
            if ($stmt->rowCount()) {
                SessionManager::setFlash('success', 'Registration cancelled.');
            } else {
                SessionManager::setFlash('warning', 'No active registration found.');
            }
            header('Location: ' . SITE_URL . 'event-details.php?id=' . $event_id);
            exit();
        }

        // Process registration submission (POST without cancel)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['cancel'])) {
            validateCsrf();

            $guests          = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
            $special_needs   = isset($_POST['special_needs']) ? trim($_POST['special_needs']) : '';
            $terms_accepted  = isset($_POST['terms']);

            // Validate
            if ($guests < 0 || $guests > 5) $errors[] = 'Guests must be 0-5.';
            if (!$terms_accepted) $errors[] = 'You must accept the terms.';

            // Check capacity
            $total_registered = (int)$conn->query("SELECT COUNT(*) FROM event_registrations WHERE event_id = $event_id AND status != 'cancelled'")->fetchColumn();
            $total_with_guests = $total_registered + 1 + $guests;
            if ($event['max_attendees'] && $total_with_guests > $event['max_attendees']) {
                $errors[] = 'Registration would exceed capacity.';
            }

            // Check deadline
            if (!empty($event['registration_deadline']) && strtotime($event['registration_deadline']) < time()) {
                $errors[] = 'Registration deadline has passed.';
            }

            if (empty($errors)) {
                try {
                    $conn->beginTransaction();
                    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id, guests, special_needs, payment_amount, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $payment_amount = $event['cost'] ? $event['cost'] * (1 + $guests) : 0.00;
                    $payment_status = $payment_amount > 0 ? 'pending' : 'free';
                    $stmt->execute([$event_id, $user_id, $guests, $special_needs, $payment_amount, $payment_status, 'confirmed']);

                    // Notify user
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, 'Registration Confirmed', ?, 'event', ?, 'event')");
                    $message = "You are registered for {$event['title']} on " . date('F j, Y', strtotime($event['event_date'])) . ".";
                    $stmt->execute([$user_id, $message, $event_id]);

                    $conn->commit();
                    SessionManager::setFlash('success', 'Registration successful!');
                    header('Location: ' . SITE_URL . 'event-details.php?id=' . $event_id);
                    exit();
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log("Registration error: " . $e->getMessage());
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }

        // Check if user already registered (for display)
        $stmt = $conn->prepare("SELECT id, status, guests FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'");
        $stmt->execute([$event_id, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // User already registered, redirect back to details
            SessionManager::setFlash('info', 'You are already registered.');
            header('Location: ' . SITE_URL . 'event-details.php?id=' . $event_id);
            exit();
        }

    } catch (PDOException $e) {
        error_log("Event registration DB error: " . $e->getMessage());
        $errors[] = 'Database error. Please try later.';
    }
} else {
    $errors[] = 'System unavailable.';
}

// Format date/time
$event_date = new DateTime($event['event_date'] ?? 'now');
$formatted_date = $event_date->format('l, F j, Y');
$start_time = date('g:i A', strtotime($event['start_time'] ?? ''));
$end_time   = !empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '';
$time_range = $end_time ? "$start_time - $end_time" : $start_time;
?>

<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Event Registration</h1>
                <p class="text-white mb-0 fs-5"><?= htmlspecialchars($event['title'] ?? 'Event') ?></p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>events.php" class="text-white-50">Events</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>event-details.php?id=<?= $event_id ?>" class="text-white-50">Details</a></li>
                        <li class="breadcrumb-item active text-white">Register</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body p-4">
                        <!-- Event Summary -->
                        <div class="mb-4 pb-4 border-bottom">
                            <h4><?= htmlspecialchars($event['title']) ?></h4>
                            <p class="text-muted mb-1"><i class="far fa-calendar-alt me-1"></i> <?= $formatted_date ?></p>
                            <p class="text-muted"><i class="far fa-clock me-1"></i> <?= $time_range ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($event['location']) ?></span>
                                <span class="fw-bold"><?= $event['cost'] > 0 ? 'SZL ' . number_format($event['cost'], 2) . ' per person' : 'Free' ?></span>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" class="needs-validation" novalidate>
                            <?= csrfField() ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="guests" class="form-label">Number of Guests <span class="text-danger">*</span></label>
                                    <select class="form-select" id="guests" name="guests" required>
                                        <option value="0" selected>0 (Only myself)</option>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> guest<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="cost-display p-3 bg-light rounded w-100">
                                        <div class="d-flex justify-content-between"><span>Your registration:</span><span>SZL <?= number_format($event['cost'] ?? 0, 2) ?></span></div>
                                        <div class="d-flex justify-content-between"><span>Guests (×<span id="guestCount">0</span>):</span><span id="guestsCost">SZL 0.00</span></div>
                                        <hr class="my-1">
                                        <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="totalCost">SZL <?= number_format($event['cost'] ?? 0, 2) ?></span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="special_needs" class="form-label">Special Requirements</label>
                                <textarea class="form-control" id="special_needs" name="special_needs" rows="2" placeholder="Dietary restrictions, accessibility needs, etc."></textarea>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">I agree to the event policies and refund terms. <span class="text-danger">*</span></label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Complete Registration</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guestsSelect = document.getElementById('guests');
    const eventCost = <?= $event['cost'] ?? 0 ?>;
    function updateCost() {
        const guests = parseInt(guestsSelect.value);
        document.getElementById('guestCount').textContent = guests;
        document.getElementById('guestsCost').textContent = 'SZL ' + (eventCost * guests).toFixed(2);
        document.getElementById('totalCost').textContent = 'SZL ' + (eventCost * (1 + guests)).toFixed(2);
    }
    if (guestsSelect) {
        guestsSelect.addEventListener('change', updateCost);
        updateCost();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>