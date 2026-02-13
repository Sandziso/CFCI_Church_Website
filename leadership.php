<?php
// leadership.php
require_once 'includes/header.php';

// Check if connection is available
if (!isset($conn) || $conn === null) {
    try {
        $host = 'localhost';
        $dbname = 'cfci_church';
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

// Fetch leadership team
$leadership_team = [];
$ministry_leaders = [];

if ($conn) {
    try {
        // Fetch senior leadership
        $stmt = $conn->prepare("
            SELECT u.* 
            FROM users u 
            WHERE u.role = 'pastor' AND u.is_active = 1 
            ORDER BY u.join_date ASC
        ");
        $stmt->execute();
        $leadership_team = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch ministry leaders
        $stmt = $conn->prepare("
            SELECT m.*, u.full_name as leader_name, u.email as leader_email
            FROM ministries m 
            LEFT JOIN users u ON m.leader_id = u.id 
            WHERE m.is_active = 1 
            ORDER BY m.name ASC
        ");
        $stmt->execute();
        $ministry_leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Leadership fetch error: " . $e->getMessage());
    }
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/leadership-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Our Leadership</h1>
                <p class="text-white mb-0">Meet the dedicated leaders serving our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="about.php" class="text-white">About</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Leadership</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center">
                <h2 class="mb-3">Guided by Servant Leaders</h2>
                <p class="lead">Our leadership team is committed to serving God and our community with humility, wisdom, and dedication. Each leader brings unique gifts and a shared passion for seeing lives transformed by the Gospel.</p>
            </div>
        </div>

        <!-- Senior Leadership -->
        <div class="senior-leadership mb-5">
            <h3 class="text-center mb-4">Senior Leadership Team</h3>
            <div class="row justify-content-center">
                <?php if (!empty($leadership_team)): ?>
                    <?php foreach ($leadership_team as $leader): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="leader-card text-center h-100">
                                <div class="leader-avatar mb-4">
                                    <div class="avatar-initials-lg">
                                        <?php echo strtoupper(substr($leader['full_name'] ?: 'L', 0, 2)); ?>
                                    </div>
                                </div>
                                <h4 class="mb-2"><?php echo htmlspecialchars($leader['full_name']); ?></h4>
                                <p class="text-primary fw-bold mb-3">
                                    <?php echo $leader['role'] == 'pastor' ? 'Pastor' : 'Church Leader'; ?>
                                </p>
                                <p class="mb-3">Serving the church with dedication and commitment to God's calling.</p>
                                <div class="leader-contact mt-3">
                                    <?php if ($leader['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($leader['email']); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-envelope me-1"></i> Email
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default leadership team if database is empty -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="leader-card text-center h-100">
                            <div class="leader-avatar mb-4">
                                <img src="assets/images/leadership/bishop.jpg" alt="Bishop Zakes Nxumalo" class="rounded-circle">
                            </div>
                            <h4 class="mb-2">Bishop Zakes Nxumalo</h4>
                            <p class="text-primary fw-bold mb-3">Senior Pastor</p>
                            <p class="mb-3">Bishop Nxumalo has served as our senior pastor for over 20 years with a passion for family restoration and community transformation.</p>
                            <div class="leader-contact mt-3">
                                <a href="mailto:bishop.zakes@cfci.org" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                                <a href="contact.php" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="fas fa-calendar me-1"></i> Schedule
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="leader-card text-center h-100">
                            <div class="leader-avatar mb-4">
                                <img src="assets/images/leadership/sarah.jpg" alt="Pastor Sarah Mkhwanazi" class="rounded-circle">
                            </div>
                            <h4 class="mb-2">Pastor Sarah Mkhwanazi</h4>
                            <p class="text-primary fw-bold mb-3">Associate Pastor</p>
                            <p class="mb-3">Pastor Sarah oversees our women's and children's ministries, bringing energy and creativity to discipleship.</p>
                            <div class="leader-contact mt-3">
                                <a href="mailto:sarah.m@cfci.org" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="leader-card text-center h-100">
                            <div class="leader-avatar mb-4">
                                <img src="assets/images/leadership/thomas.jpg" alt="Deacon Thomas Dlamini" class="rounded-circle">
                            </div>
                            <h4 class="mb-2">Deacon Thomas Dlamini</h4>
                            <p class="text-primary fw-bold mb-3">Deacon Chairman</p>
                            <p class="mb-3">Deacon Thomas leads our servant leadership team and oversees church operations and outreach.</p>
                            <div class="leader-contact mt-3">
                                <a href="mailto:thomas.d@cfci.org" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Elders & Deacons -->
        <div class="elders-deacons bg-light p-5 rounded mb-5">
            <h3 class="text-center mb-4">Elders & Deacons Council</h3>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="council-card">
                        <h4 class="mb-3">Elders Council</h4>
                        <p>The Elders provide spiritual oversight, biblical teaching, and pastoral care to the church family. They work closely with the pastoral team in prayer, discernment, and decision-making.</p>
                        <div class="elders-list">
                            <div class="elder-item">
                                <div class="elder-info">
                                    <strong>John Dlamini</strong>
                                    <span class="text-muted">Elder (Chair)</span>
                                </div>
                                <div class="elder-tenure">5 years serving</div>
                            </div>
                            <div class="elder-item">
                                <div class="elder-info">
                                    <strong>Phumzile Ndlovu</strong>
                                    <span class="text-muted">Elder</span>
                                </div>
                                <div class="elder-tenure">3 years serving</div>
                            </div>
                            <div class="elder-item">
                                <div class="elder-info">
                                    <strong>Robert Mthethwa</strong>
                                    <span class="text-muted">Elder</span>
                                </div>
                                <div class="elder-tenure">7 years serving</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="council-card">
                        <h4 class="mb-3">Deacons Council</h4>
                        <p>The Deacons serve the practical needs of the church community, overseeing ministry operations, facilities, and member care. They exemplify servant leadership in action.</p>
                        <div class="deacons-list">
                            <div class="deacon-item">
                                <div class="deacon-info">
                                    <strong>Sarah Nkosi</strong>
                                    <span class="text-muted">Deacon (Hospitality)</span>
                                </div>
                                <div class="deacon-tenure">4 years serving</div>
                            </div>
                            <div class="deacon-item">
                                <div class="deacon-info">
                                    <strong>Thomas Mbeki</strong>
                                    <span class="text-muted">Deacon (Facilities)</span>
                                </div>
                                <div class="deacon-tenure">2 years serving</div>
                            </div>
                            <div class="deacon-item">
                                <div class="deacon-info">
                                    <strong>Nokulunga Sibiya</strong>
                                    <span class="text-muted">Deacon (Finance)</span>
                                </div>
                                <div class="deacon-tenure">6 years serving</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ministry Leaders -->
        <?php if (!empty($ministry_leaders)): ?>
            <div class="ministry-leaders mb-5">
                <h3 class="text-center mb-4">Ministry Leaders</h3>
                <div class="row">
                    <?php foreach ($ministry_leaders as $ministry): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="ministry-leader-card text-center h-100">
                                <div class="ministry-icon mb-3">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                                <h5 class="mb-2"><?php echo htmlspecialchars($ministry['name']); ?></h5>
                                <div class="leader-name mb-2">
                                    <strong><?php echo htmlspecialchars($ministry['leader_name'] ?? 'TBA'); ?></strong>
                                </div>
                                <p class="small text-muted mb-3"><?php echo htmlspecialchars(substr($ministry['description'] ?: 'Ministry leader', 0, 100) . '...'); ?></p>
                                <a href="ministry-details.php?id=<?php echo htmlspecialchars($ministry['id']); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-info-circle me-1"></i> Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Leadership Philosophy -->
        <div class="leadership-philosophy mb-5">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="philosophy-card p-4">
                        <h3 class="text-center mb-4">Our Leadership Philosophy</h3>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="philosophy-point">
                                    <div class="point-icon mb-3">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <h5>Servant Leadership</h5>
                                    <p>We follow Jesus' example of washing feet—leading by serving others with humility and compassion.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="philosophy-point">
                                    <div class="point-icon mb-3">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <h5>Team Ministry</h5>
                                    <p>We believe in shared leadership, where each person's gifts complement and strengthen the whole team.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="philosophy-point">
                                    <div class="point-icon mb-3">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                    <h5>Multiplication Focus</h5>
                                    <p>Our goal is to develop new leaders who can continue the work and expand God's kingdom.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="philosophy-point">
                                    <div class="point-icon mb-3">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h5>Relational Integrity</h5>
                                    <p>We prioritize healthy relationships, transparency, and accountability in all our interactions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Leadership -->
        <div class="contact-leadership text-center py-5">
            <h3 class="mb-4">Connect with Our Leaders</h3>
            <p class="mb-4">Our leaders are here to serve you. Feel free to reach out for prayer, counsel, or questions.</p>
            <div class="cta-buttons">
                <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
                <a href="prayer-request.php" class="btn btn-outline-primary btn-lg ms-3">Request Prayer</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.leader-card {
    background: white;
    padding: 30px 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.leader-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.leader-avatar {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto;
}

.leader-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.avatar-initials-lg {
    width: 150px;
    height: 150px;
    background: #1a5276;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 3rem;
    margin: 0 auto;
    border: 5px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.council-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    height: 100%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.elder-item, .deacon-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.elder-item:last-child, .deacon-item:last-child {
    border-bottom: none;
}

.elder-info, .deacon-info {
    display: flex;
    flex-direction: column;
}

.elder-tenure, .deacon-tenure {
    font-size: 0.85rem;
    color: #666;
    background: #f8f9fa;
    padding: 3px 10px;
    border-radius: 15px;
}

.ministry-leader-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: all 0.3s ease;
    height: 100%;
}

.ministry-leader-card:hover {
    background: #1a5276;
    color: white;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.ministry-leader-card:hover .ministry-icon i {
    color: white;
}

.ministry-leader-card:hover .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.ministry-leader-card:hover .btn-outline-primary {
    color: white;
    border-color: white;
}

.ministry-leader-card:hover .btn-outline-primary:hover {
    background: white;
    color: #1a5276;
}

.ministry-icon i {
    font-size: 2.5rem;
    color: #1a5276;
}

.philosophy-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-top: 4px solid #e67e22;
}

.philosophy-point {
    text-align: center;
}

.point-icon {
    width: 70px;
    height: 70px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.point-icon i {
    font-size: 1.8rem;
    color: #1a5276;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .leader-avatar {
        width: 120px;
        height: 120px;
    }
    
    .avatar-initials-lg {
        width: 120px;
        height: 120px;
        font-size: 2.5rem;
    }
    
    .elder-item, .deacon-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .point-icon {
        width: 60px;
        height: 60px;
    }
    
    .point-icon i {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
});
</script>

<?php
// Close database connection if created locally
if (isset($local_conn) && $local_conn) {
    $conn = null;
}
require_once 'includes/footer.php';
?>