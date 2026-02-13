<?php
// ministries.php
require_once 'includes/header.php';

// Check if connection is available, if not create a local one
if (!isset($conn) || $conn === null) {
    try {
        $host = 'localhost';
        $dbname = 'cfci_church_db';
        $username = 'root';
        $password = '';
        
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        $conn = null;
    }
}

// Fetch ministries from database
$ministries = [];
$categories = [];
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM ministries WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get unique categories
        $cat_stmt = $conn->prepare("SELECT DISTINCT category, COUNT(*) as count FROM ministries WHERE status = 'active' GROUP BY category");
        $cat_stmt->execute();
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching ministries: " . $e->getMessage());
    }
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/ministries-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Our Ministries</h1>
                <p class="text-white mb-0">Find your place to serve, grow, and connect with others</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Ministries</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Serving Together in Christ</h2>
                <p class="lead">At CFCI, we believe everyone has a role to play in God's kingdom. Our ministries provide opportunities for you to use your gifts, grow in faith, and make a difference in our community.</p>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="ministry-filters text-center">
                    <button class="btn btn-outline-primary filter-btn active" data-filter="all">All Ministries</button>
                    <?php foreach ($categories as $cat): ?>
                        <?php if (!empty($cat['category'])): ?>
                            <button class="btn btn-outline-primary filter-btn" data-filter="<?php echo htmlspecialchars(strtolower($cat['category'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($cat['category'])); ?> (<?php echo $cat['count']; ?>)
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Ministries Grid -->
        <div class="row" id="ministries-container">
            <?php if (empty($ministries)): ?>
                <!-- Default ministries if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="adults">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/mens-fellowship.jpg" alt="Men's Fellowship" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=1" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Men's Fellowship</h3>
                            <p class="mb-3">Building godly men through fellowship, prayer, and biblical teaching.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> 1st Saturday, 8 AM</span>
                                <span><i class="fas fa-user-friends text-primary"></i> All Men</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="adults">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/womens-ministry.jpg" alt="Women's Ministry" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=2" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Women's Ministry</h3>
                            <p class="mb-3">Empowering women to grow in faith and support one another in Christ.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> Every Thursday, 5 PM</span>
                                <span><i class="fas fa-user-friends text-primary"></i> All Women</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="worship">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/worship-team.jpg" alt="Worship Team" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=3" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Worship Team</h3>
                            <p class="mb-3">Leading the congregation in heartfelt worship and praise to God.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> Tue & Thu, 6 PM</span>
                                <span><i class="fas fa-user-friends text-primary"></i> Audition Required</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="youth">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/youth-ministry.jpg" alt="Youth Ministry" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=4" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Youth Ministry</h3>
                            <p class="mb-3">Engaging and nurturing the faith of our young members.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> Friday, 6 PM</span>
                                <span><i class="fas fa-user-friends text-primary"></i> Ages 13-25</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="children">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/children-church.jpg" alt="Children's Church" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=5" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Children's Church</h3>
                            <p class="mb-3">Teaching children about Jesus in fun, age-appropriate ways.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> Sundays, 10 AM</span>
                                <span><i class="fas fa-user-friends text-primary"></i> Ages 3-12</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="outreach">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="assets/images/ministries/outreach.jpg" alt="Outreach Ministry" class="img-fluid">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=6" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3">Outreach Ministry</h3>
                            <p class="mb-3">Serving our community and sharing the love of Christ.</p>
                            <div class="ministry-meta">
                                <span class="me-3"><i class="far fa-clock text-primary"></i> Monthly</span>
                                <span><i class="fas fa-user-friends text-primary"></i> All Ages</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($ministries as $ministry): ?>
                <?php 
                    $category = !empty($ministry['category']) ? strtolower($ministry['category']) : 'general';
                    $image = !empty($ministry['image']) ? $ministry['image'] : 'assets/images/ministries/default.jpg';
                    $meeting_info = '';
                    if (!empty($ministry['meeting_day']) && !empty($ministry['meeting_time'])) {
                        $meeting_info = $ministry['meeting_day'] . ', ' . $ministry['meeting_time'];
                    }
                ?>
                <div class="col-lg-4 col-md-6 mb-4 ministry-item" data-category="<?php echo htmlspecialchars($category); ?>">
                    <div class="ministry-card h-100">
                        <div class="ministry-image">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($ministry['name']); ?>" 
                                 class="img-fluid"
                                 onerror="this.src='assets/images/ministries/default.jpg'">
                            <div class="ministry-overlay">
                                <a href="ministry-details.php?id=<?php echo htmlspecialchars($ministry['id']); ?>" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="ministry-content p-4">
                            <h3 class="h4 mb-3"><?php echo htmlspecialchars($ministry['name']); ?></h3>
                            <p class="mb-3"><?php 
                                $desc = $ministry['description'] ?? 'Join us in serving and growing together.';
                                echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc); 
                            ?></p>
                            <div class="ministry-meta">
                                <?php if (!empty($meeting_info)): ?>
                                <span class="me-3"><i class="far fa-clock text-primary"></i> <?php echo htmlspecialchars($meeting_info); ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-user-friends text-primary"></i> 
                                    <?php echo htmlspecialchars($ministry['category'] ?? 'General'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Call to Action -->
        <div class="cta-section bg-light p-5 rounded text-center mt-5">
            <h2 class="mb-4">Ready to Get Involved?</h2>
            <p class="lead mb-4">We have a place for you! Join a ministry and start serving today.</p>
            <div class="cta-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="member/dashboard.php?tab=ministries" class="btn btn-primary btn-lg me-3">Join Ministry</a>
                <?php else: ?>
                    <a href="auth/login.php?redirect=ministries.php" class="btn btn-primary btn-lg me-3">Login to Join</a>
                <?php endif; ?>
                <a href="contact.php?subject=Ministry%20Inquiry" class="btn btn-outline-primary btn-lg">Contact Ministry Leader</a>
            </div>
        </div>

        <!-- Ministry Stats -->
        <div class="ministry-stats row mt-5 pt-5">
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold text-primary"><?php echo count($ministries); ?>+</div>
                <div class="stat-label">Active Ministries</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold text-primary">150+</div>
                <div class="stat-label">Ministry Members</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold text-primary">52+</div>
                <div class="stat-label">Events Yearly</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number display-4 fw-bold text-primary">1000+</div>
                <div class="stat-label">Hours Served Monthly</div>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
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
}

.filter-btn.active {
    background: #1a5276;
    color: white;
    border-color: #1a5276;
}

.ministry-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.ministry-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.ministry-image {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.ministry-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.ministry-card:hover .ministry-image img {
    transform: scale(1.05);
}

.ministry-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.ministry-card:hover .ministry-overlay {
    opacity: 1;
}

.ministry-content {
    background: white;
}

.ministry-meta {
    font-size: 0.85rem;
    color: #666;
    margin-top: 10px;
}

.ministry-meta i {
    margin-right: 5px;
}

.cta-section {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
}

.stat-number {
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-top: 10px;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .ministry-filters {
        gap: 5px;
    }
    
    .filter-btn {
        padding: 6px 15px;
        font-size: 0.9rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ministry Filtering
    const filterBtns = document.querySelectorAll('.filter-btn');
    const ministryItems = document.querySelectorAll('.ministry-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            // Filter ministry items
            ministryItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
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