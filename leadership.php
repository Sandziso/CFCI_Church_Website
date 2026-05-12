<?php
require_once 'includes/header.php';

// Safely get DB connection
$conn = null;
try {
    $conn = DBConnect::getConnection();
} catch (Exception $e) {
    error_log("Leadership DB connect error: " . $e->getMessage());
}

$leadership_team = [];
$elders_deacons = [];
$ministry_leaders = [];

if ($conn) {
    try {
        $stmt = $conn->query("SELECT id, full_name, email, role FROM users WHERE role = 'pastor' AND is_active = 1 ORDER BY join_date ASC");
        $leadership_team = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->query("SELECT id, full_name, email, role FROM users WHERE role IN ('elder','deacon') AND is_active = 1 ORDER BY role, join_date ASC");
        $elders_deacons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->query("SELECT m.id, m.name, m.description, m.leader_id, u.full_name AS leader_name, u.email AS leader_email
                              FROM ministries m
                              LEFT JOIN users u ON m.leader_id = u.id
                              WHERE m.is_active = 1
                              ORDER BY m.name ASC");
        $ministry_leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Leadership data fetch error: " . $e->getMessage());
    }
}

// Fallback static data
$fallback_leaders = [
    ['full_name' => 'Bishop Zakes Nxumalo', 'email' => 'bishop.zakes@cfci.org', 'role' => 'pastor', 'bio' => 'Bishop Nxumalo has served as our senior pastor for over 20 years with a passion for family restoration and community transformation.'],
    ['full_name' => 'Pastor Sarah Mkhwanazi', 'email' => 'sarah.m@cfci.org', 'role' => 'pastor', 'bio' => 'Pastor Sarah oversees our women\'s and children\'s ministries, bringing energy and creativity to discipleship.'],
    ['full_name' => 'Deacon Thomas Dlamini', 'email' => 'thomas.d@cfci.org', 'role' => 'deacon', 'bio' => 'Deacon Thomas leads our servant leadership team and oversees church operations and outreach.']
];
$fallback_elders_deacons = [
    ['full_name' => 'John Dlamini', 'role' => 'elder', 'tenure' => '5 years'],
    ['full_name' => 'Phumzile Ndlovu', 'role' => 'elder', 'tenure' => '3 years'],
    ['full_name' => 'Robert Mthethwa', 'role' => 'elder', 'tenure' => '7 years'],
    ['full_name' => 'Sarah Nkosi', 'role' => 'deacon', 'tenure' => '4 years'],
    ['full_name' => 'Thomas Mbeki', 'role' => 'deacon', 'tenure' => '2 years'],
    ['full_name' => 'Nokulunga Sibiya', 'role' => 'deacon', 'tenure' => '6 years']
];

$displayLeaders = !empty($leadership_team) ? $leadership_team : $fallback_leaders;
$displayEldersDeacons = !empty($elders_deacons) ? $elders_deacons : $fallback_elders_deacons;
?>
<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Our Leadership</h1>
                <p class="text-white mb-0 fs-5">Meet the dedicated leaders serving our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="about.php" class="text-white-50">About</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Leadership</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center wow fadeInUp" data-wow-delay="0.1s">
                <h2 class="section-title">Guided by Servant Leaders</h2>
                <p class="lead">
                    Our leadership team is committed to serving God and our community with humility, wisdom, and dedication.
                    Each leader brings unique gifts and a shared passion for seeing lives transformed by the Gospel.
                </p>
            </div>
        </div>

        <!-- Senior Leadership Cards -->
        <div class="senior-leadership mb-5">
            <h3 class="text-center mb-4">Senior Leadership Team</h3>
            <div class="row justify-content-center g-4">
                <?php foreach ($displayLeaders as $leader):
                    $name = $leader['full_name'] ?? '';
                    $email = $leader['email'] ?? '';
                    $role = $leader['role'] ?? 'pastor';
                    $roleLabel = ($role == 'pastor') ? 'Pastor' : ucfirst($role);
                    $bio = $leader['bio'] ?? 'Dedicated servant of God, leading with faith and love.';
                ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="leader-card text-center p-4 h-100 bg-white rounded-3 shadow-sm">
                            <div class="leader-avatar mb-3">
                                <div class="avatar-initials" aria-hidden="true"><?= strtoupper(substr($name, 0, 2)) ?></div>
                                <span class="sr-only"><?= htmlspecialchars($name) ?></span>
                            </div>
                            <h4 class="mb-1"><?= htmlspecialchars($name) ?></h4>
                            <p class="text-warning fw-bold mb-2"><?= htmlspecialchars($roleLabel) ?></p>
                            <p class="small text-muted"><?= htmlspecialchars($bio) ?></p>
                            <?php if ($email): ?>
                                <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-envelope me-1" aria-hidden="true"></i> Email
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Elders & Deacons -->
        <div class="elders-deacons bg-light p-5 rounded-3 mb-5 wow fadeInUp" data-wow-delay="0.1s">
            <h3 class="text-center mb-4">Elders & Deacons Council</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <h4 class="mb-3"><i class="fas fa-pray me-2" style="color: var(--primary-blue);" aria-hidden="true"></i>Elders</h4>
                    <ul class="list-unstyled">
                        <?php foreach ($displayEldersDeacons as $person):
                            if (($person['role'] ?? '') !== 'elder') continue;
                        ?>
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span><strong><?= htmlspecialchars($person['full_name'] ?? '') ?></strong></span>
                                <span class="small text-muted"><?= $person['tenure'] ?? 'Faithful servant' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-3"><i class="fas fa-hands-helping me-2" style="color: var(--primary-yellow);" aria-hidden="true"></i>Deacons</h4>
                    <ul class="list-unstyled">
                        <?php foreach ($displayEldersDeacons as $person):
                            if (($person['role'] ?? '') !== 'deacon') continue;
                        ?>
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span><strong><?= htmlspecialchars($person['full_name'] ?? '') ?></strong></span>
                                <span class="small text-muted"><?= $person['tenure'] ?? 'Faithful servant' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ministry Leaders -->
        <?php if (!empty($displayMinistryLeaders)): ?>
        <div class="ministry-leaders mb-5">
            <h3 class="text-center mb-4">Ministry Leaders</h3>
            <div class="row g-4">
                <?php foreach ($displayMinistryLeaders as $ministry): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="ministry-card text-center p-4 h-100 bg-white rounded-3 shadow-sm">
                            <div class="ministry-icon mb-3">
                                <i class="fas fa-users" style="color: var(--primary-blue); font-size: 2rem;" aria-hidden="true"></i>
                            </div>
                            <h5 class="mb-2"><?= htmlspecialchars($ministry['name'] ?? '') ?></h5>
                            <p class="fw-bold small mb-1"><?= htmlspecialchars($ministry['leader_name'] ?? 'TBA') ?></p>
                            <p class="small text-muted">
                                <?= htmlspecialchars(mb_strimwidth($ministry['description'] ?? 'Serving with dedication.', 0, 80, '...')) ?>
                            </p>
                            <a href="ministry.php?id=<?= htmlspecialchars($ministry['id'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-info-circle me-1" aria-hidden="true"></i> Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Leadership Philosophy -->
        <div class="leadership-philosophy mb-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="philosophy-card p-4 p-lg-5 bg-white rounded-3 shadow-sm">
                        <h3 class="text-center mb-4">Our Leadership Philosophy</h3>
                        <div class="row g-4">
                            <div class="col-md-6 text-center">
                                <div class="mb-3"><span class="philosophy-icon"><i class="fas fa-hands-helping"></i></span></div>
                                <h5>Servant Leadership</h5>
                                <p class="small text-muted">We follow Jesus' example of washing feet—leading by serving others with humility and compassion.</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="mb-3"><span class="philosophy-icon"><i class="fas fa-user-friends"></i></span></div>
                                <h5>Team Ministry</h5>
                                <p class="small text-muted">We believe in shared leadership, where each person's gifts complement and strengthen the whole team.</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="mb-3"><span class="philosophy-icon"><i class="fas fa-seedling"></i></span></div>
                                <h5>Multiplication Focus</h5>
                                <p class="small text-muted">Our goal is to develop new leaders who can continue the work and expand God's kingdom.</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="mb-3"><span class="philosophy-icon"><i class="fas fa-heart"></i></span></div>
                                <h5>Relational Integrity</h5>
                                <p class="small text-muted">We prioritize healthy relationships, transparency, and accountability in all our interactions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta-section text-center py-5 wow fadeInUp" data-wow-delay="0.1s"
             style="background: var(--gradient-primary); border-radius: 12px;">
            <h3 class="mb-4 text-white">Connect with Our Leaders</h3>
            <p class="mb-4 text-white-50">Our leaders are here to serve you. Feel free to reach out for prayer, counsel, or questions.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="contact.php" class="btn btn-warning btn-lg">Contact Us</a>
                <a href="prayer-request.php" class="btn btn-outline-light btn-lg">Request Prayer</a>
            </div>
        </div>
    </div>
</section>

<style>
/* ---------- Leadership‑specific styles ---------- */
.leader-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.leader-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}
.avatar-initials {
    width: 120px;
    height: 120px;
    background: var(--primary-blue);
    color: #fff;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 2.5rem;
    border: 5px solid var(--white);
    box-shadow: var(--shadow);
}
.sr-only {
    position: absolute;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border: 0;
}
.ministry-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.ministry-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}
.philosophy-card {
    border-top: 4px solid var(--primary-yellow);
}
.philosophy-icon {
    display: inline-flex;
    width: 70px;
    height: 70px;
    background: rgba(26, 82, 118, 0.08);
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--primary-blue);
}
</style>

<?php require_once 'includes/footer.php'; ?>