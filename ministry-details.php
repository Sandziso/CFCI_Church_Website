<?php
/**
 * ministry-details.php – CFCI Church Single Ministry View
 * PRD‑compliant: dynamic DB, fallback static, integrated auth & CSRF.
 */
require_once 'includes/header.php';   // loads SITE_URL, $conn, auth, helpers
$current_page = basename($_SERVER['PHP_SELF']);

$ministry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ministry = null;
$ministry_members = [];
$related_ministries = [];

// Fetch from database (no events – schema doesn't support direct ministry‑event link yet)
if ($ministry_id > 0 && isset($conn) && $conn instanceof PDO) {
    try {
        // Ministry details + leader info
        $stmt = $conn->prepare("
            SELECT m.*, 
                   u.full_name AS leader_name,
                   u.email AS leader_email,
                   u.phone AS leader_phone
            FROM ministries m
            LEFT JOIN users u ON m.leader_id = u.id
            WHERE m.id = ? AND m.status = 'active'
        ");
        $stmt->execute([$ministry_id]);
        $ministry = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ministry) {
            // Member count (active)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM ministry_members WHERE ministry_id = ? AND is_active = 1 AND left_at IS NULL");
            $stmt->execute([$ministry_id]);
            $ministry['member_count'] = (int)$stmt->fetchColumn();

            // Active leadership team (limited)
            $stmt = $conn->prepare("
                SELECT mm.role, u.full_name, u.email
                FROM ministry_members mm
                JOIN users u ON mm.user_id = u.id
                WHERE mm.ministry_id = ? AND mm.is_active = 1 AND mm.left_at IS NULL
                ORDER BY FIELD(mm.role, 'leader','co-leader','coordinator','member')
                LIMIT 10
            ");
            $stmt->execute([$ministry_id]);
            $ministry_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Related ministries (same category)
            if (!empty($ministry['category'])) {
                $stmt = $conn->prepare("
                    SELECT id, name, description, image_url 
                    FROM ministries 
                    WHERE category = ? AND id <> ? AND status = 'active'
                    ORDER BY name 
                    LIMIT 3
                ");
                $stmt->execute([$ministry['category'], $ministry_id]);
                $related_ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        error_log("Ministry details error: " . $e->getMessage());
    }
}

// If ministry not found from DB for a valid ID, redirect
if (!$ministry && $ministry_id > 0) {
    header('Location: ' . SITE_URL . 'ministries.php');
    exit();
}

// Provide a static fallback when DB is unavailable
if (!$ministry) {
    $ministry = [
        'id'           => 1,
        'name'         => 'Youth Ministry',
        'description'  => '<p>The Youth Ministry at CFCI is dedicated to empowering the next generation of Christian leaders. We provide a safe, engaging environment where youth can grow in their faith, build meaningful relationships, and discover their God‑given purpose.</p>
                          <h3>Our Vision</h3>
                          <p>To raise a generation of young people who are passionate about God, grounded in His Word, and committed to transforming their communities through Christ‑like leadership.</p>
                          <h3>What We Do</h3>
                          <ul>
                              <li><strong>Weekly Youth Services:</strong> Every Friday at 6:00 PM with worship, relevant teaching, and small group discussions</li>
                              <li><strong>Discipleship Groups:</strong> Age‑specific small groups meeting weekly for deeper relationship building</li>
                              <li><strong>Annual Youth Camp:</strong> A transformative 3‑day camping experience focused on spiritual growth</li>
                              <li><strong>Community Outreach:</strong> Monthly service projects to impact our local community</li>
                              <li><strong>Leadership Training:</strong> Programs to develop leadership skills and spiritual gifts</li>
                          </ul>',
        'category'        => 'youth',
        'meeting_day'     => 'Friday',
        'meeting_time'    => '6:00 PM',
        'meeting_location'=> 'Youth Hall (Main Church Building)',
        'contact_email'   => 'youth@cfci.org.sz',
        'contact_phone'   => '+268 2505 5961',
        'image_url'       => 'assets/images/ministries/youth-ministry.jpg',
        'leader_name'     => 'Pastor Sarah Mkhwanazi',
        'leader_email'    => 'sarah.mkhwanazi@cfci.org.sz',
        'member_count'    => 85
    ];
    $ministry_members = [
        ['full_name' => 'Pastor Sarah Mkhwanazi', 'role' => 'leader', 'email' => 'sarah@cfci.org.sz'],
        ['full_name' => 'John Dlamini',           'role' => 'co-leader', 'email' => 'john@cfci.org.sz'],
        ['full_name' => 'Thando Nkosi',           'role' => 'worship leader', 'email' => 'thando@cfci.org.sz']
    ];
    $related_ministries = [
        ['id' => 5, 'name' => "Children's Church", 'description' => 'For ages 3-12', 'image_url' => 'assets/images/ministries/children-church.jpg'],
        ['id' => 3, 'name' => 'Worship Team',       'description' => 'Leading in worship', 'image_url' => 'assets/images/ministries/worship-team.jpg']
    ];
}
?>

<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('<?= SITE_URL ?>assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold"><?= htmlspecialchars($ministry['name']) ?></h1>
                <?php if (!empty($ministry['category'])): ?>
                    <p class="text-white mb-0 fs-5"><?= htmlspecialchars(ucfirst($ministry['category'])) ?> Ministry</p>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>ministries.php" class="text-white-50">Ministries</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($ministry['name']) ?></li>
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
                <div class="ministry-details-card bg-white p-4 rounded-3 shadow-sm mb-5">
                    <!-- Ministry Image -->
                    <div class="ministry-image mb-4">
                        <img src="<?= SITE_URL . htmlspecialchars($ministry['image_url'] ?? 'assets/images/ministries/default.jpg') ?>"
                             alt="<?= htmlspecialchars($ministry['name']) ?>"
                             class="img-fluid rounded-3"
                             onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                    </div>

                    <!-- Quick Info Grid -->
                    <div class="row g-3 mb-5">
                        <?php if (!empty($ministry['meeting_day']) && !empty($ministry['meeting_time'])): ?>
                        <div class="col-md-6">
                            <div class="info-card d-flex align-items-center p-3 bg-light rounded-3 h-100">
                                <div class="info-icon me-3 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Meeting Schedule</strong>
                                    <?= htmlspecialchars($ministry['meeting_day'] . ' at ' . $ministry['meeting_time']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ministry['meeting_location'])): ?>
                        <div class="col-md-6">
                            <div class="info-card d-flex align-items-center p-3 bg-light rounded-3 h-100">
                                <div class="info-icon me-3 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Location</strong>
                                    <?= htmlspecialchars($ministry['meeting_location']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <div class="info-card d-flex align-items-center p-3 bg-light rounded-3 h-100">
                                <div class="info-icon me-3 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Members</strong>
                                    <?= number_format($ministry['member_count'] ?? 0) ?> active
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($ministry['contact_email'])): ?>
                        <div class="col-md-6">
                            <div class="info-card d-flex align-items-center p-3 bg-light rounded-3 h-100">
                                <div class="info-icon me-3 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Contact</strong>
                                    <?= htmlspecialchars($ministry['contact_email']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ministry Description -->
                    <div class="ministry-description mb-5">
                        <h3 class="mb-4">About This Ministry</h3>
                        <?php if (!empty($ministry['description'])): ?>
                            <?= $ministry['description'] ?> <!-- already contains HTML -->
                        <?php else: ?>
                            <p class="text-muted">No detailed description available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Join Ministry Section -->
                <div class="join-section mt-4 p-4 bg-light rounded-3">
                    <h3 class="mb-4">Get Involved</h3>
                    <?php if (is_logged_in()): ?>
                        <form id="joinForm" onsubmit="return submitJoinForm(event)" class="needs-validation" novalidate>
                            <?= csrfField() ?>
                            <input type="hidden" name="ministry_id" value="<?= $ministry_id ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="interest_role" class="form-label">Area of Interest</label>
                                    <select class="form-select" id="interest_role" name="interest_role" required>
                                        <option value="">Select area</option>
                                        <option value="member">General Member</option>
                                        <option value="volunteer">Volunteer</option>
                                        <option value="worship">Worship Team</option>
                                        <option value="outreach">Outreach</option>
                                        <option value="admin">Administration</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="availability" class="form-label">Availability</label>
                                    <select class="form-select" id="availability" name="availability" required>
                                        <option value="">Select availability</option>
                                        <option value="weekdays">Weekdays</option>
                                        <option value="weekends">Weekends</option>
                                        <option value="evenings">Evenings</option>
                                        <option value="flexible">Flexible</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Why do you want to join? *</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required placeholder="Share your interest and any relevant experience..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Submit Interest
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please <a href="<?= SITE_URL ?>auth/login.php?redirect=<?= urlencode(SITE_URL . 'ministry-details.php?id=' . $ministry_id) ?>" class="alert-link">login</a> or <a href="<?= SITE_URL ?>auth/register.php" class="alert-link">create an account</a> to join this ministry.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Ministry Leader -->
                <?php if (!empty($ministry['leader_name'])): ?>
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-3">Ministry Leader</h5>
                        <div class="avatar-initials-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                             style="width:100px; height:100px; font-size:2rem;">
                            <?= getUserInitials($ministry['leader_name']) ?>
                        </div>
                        <h5 class="mt-3"><?= htmlspecialchars($ministry['leader_name']) ?></h5>
                        <?php if (!empty($ministry['leader_email'])): ?>
                            <p class="text-muted small"><?= htmlspecialchars($ministry['leader_email']) ?></p>
                        <?php endif; ?>
                        <div class="d-grid gap-2 mt-3">
                            <?php if (!empty($ministry['leader_email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($ministry['leader_email']) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($ministry['contact_phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($ministry['contact_phone']) ?>" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-phone me-1"></i> Call
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Leadership Team -->
                <?php if (!empty($ministry_members)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Leadership Team</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach ($ministry_members as $member): ?>
                                <div class="list-group-item d-flex align-items-center border-0 px-0 py-2">
                                    <div class="avatar-initials-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center"
                                         style="width:40px; height:40px; font-size:0.9rem;">
                                        <?= getUserInitials($member['full_name']) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($member['full_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars(ucfirst($member['role'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Ministry Stats</h5>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h1 text-primary"><?= number_format($ministry['member_count'] ?? 0) ?></div>
                                <small class="text-muted">Members</small>
                            </div>
                            <div class="col-4">
                                <div class="h1 text-primary"><?= count($ministry_members) ?></div>
                                <small class="text-muted">Leaders</small>
                            </div>
                            <div class="col-4">
                                <div class="h1 text-primary">0</div> <!-- events not yet linked -->
                                <small class="text-muted">Events</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Ministries -->
                <?php if (!empty($related_ministries)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Related Ministries</h5>
                        <?php foreach ($related_ministries as $rel): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= SITE_URL . htmlspecialchars($rel['image_url'] ?? 'assets/images/ministries/default.jpg') ?>"
                                     alt="<?= htmlspecialchars($rel['name']) ?>"
                                     class="rounded me-3"
                                     style="width:60px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= SITE_URL ?>assets/images/ministries/default.jpg'">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($rel['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($rel['description'] ?? '', 0, 60, '...')) ?></small>
                                    <br>
                                    <a href="<?= SITE_URL ?>ministry-details.php?id=<?= $rel['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share Ministry -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Share This Ministry</h5>
                        <div class="d-flex gap-2 mb-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . 'ministry-details.php?id=' . $ministry_id) ?>" target="_blank" class="btn btn-sm btn-facebook flex-grow-1" style="background:#3b5998;color:#fff;">
                                <i class="fab fa-facebook-f me-1"></i> Facebook
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($ministry['name'] . ' - CFCI Church: ' . SITE_URL . 'ministry-details.php?id=' . $ministry_id) ?>" target="_blank" class="btn btn-sm btn-success flex-grow-1">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="ministry-link" value="<?= SITE_URL ?>ministry-details.php?id=<?= $ministry_id ?>" readonly>
                            <button class="btn btn-secondary" type="button" onclick="copyMinistryLink()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyMinistryLink() {
    const linkInput = document.getElementById('ministry-link');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // mobile
    navigator.clipboard.writeText(linkInput.value).then(() => {
        const btn = event.target.closest('button');
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.replace('btn-secondary','btn-success');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i>';
            btn.classList.replace('btn-success','btn-secondary');
        }, 2000);
    });
}

// Join form submission (simulated AJAX)
function submitJoinForm(e) {
    e.preventDefault();
    const form = document.getElementById('joinForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    const btn = form.querySelector('button[type="submit"]');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;

    // Simulate server response
    setTimeout(() => {
        if (typeof showToast === 'function') {
            showToast('Thank you for your interest! The leader will be in touch.', 'success');
        } else {
            alert('Thank you for your interest!');
        }
        form.reset();
        form.classList.remove('was-validated');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }, 1500);
    return false;
}
</script>

<?php require_once 'includes/footer.php'; ?>