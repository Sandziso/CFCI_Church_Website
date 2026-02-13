<?php
// prayer-request.php
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

// Variables for filtering and pagination
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch prayer requests
$prayer_requests = [];
$total_requests = 0;
$categories = ['urgent', 'healing', 'family', 'financial', 'guidance', 'thanksgiving'];

if ($conn) {
    try {
        // Build query based on filter
        $sql = "SELECT pr.*, u.full_name, u.email 
                FROM prayer_requests pr 
                LEFT JOIN users u ON pr.user_id = u.id 
                WHERE pr.is_public = 1";
        
        $count_sql = "SELECT COUNT(*) as total FROM prayer_requests WHERE is_public = 1";
        $params = [];
        
        if ($filter !== 'all') {
            $sql .= " AND pr.category = ?";
            $count_sql .= " AND category = ?";
            $params[] = $filter;
        }
        
        $sql .= " ORDER BY pr.is_urgent DESC, pr.created_at DESC LIMIT ? OFFSET ?";
        
        // Get total count
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute($params);
        $total_requests = $count_stmt->fetch(PDO::FETCH_COLUMN);
        
        // Get paginated results
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $prayer_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Prayer requests fetch error: " . $e->getMessage());
    }
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/prayer-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Prayer Requests</h1>
                <p class="text-white mb-0">Share your needs and pray for others</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Prayer Requests</li>
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
                <h2 class="mb-3">Carrying Each Other's Burdens</h2>
                <p class="lead">Join our prayer community in lifting up needs, celebrating answers to prayer, and supporting one another in faith.</p>
            </div>
        </div>

        <!-- Prayer Request Form -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="request-form-card">
                    <h3 class="text-center mb-4">Submit a Prayer Request</h3>
                    <form id="prayerRequestForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="requestName" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="requestName" name="name" 
                                       value="<?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : ''; ?>"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="requestCategory" class="form-label">Prayer Category *</label>
                                <select class="form-select" id="requestCategory" name="category" required>
                                    <option value="">Select category</option>
                                    <option value="urgent">Urgent Need</option>
                                    <option value="healing">Healing</option>
                                    <option value="family">Family</option>
                                    <option value="financial">Financial</option>
                                    <option value="guidance">Guidance</option>
                                    <option value="thanksgiving">Thanksgiving</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="requestTitle" class="form-label">Prayer Title *</label>
                            <input type="text" class="form-control" id="requestTitle" name="title" 
                                   placeholder="Brief title of your prayer request" required>
                        </div>
                        <div class="mb-3">
                            <label for="requestContent" class="form-label">Prayer Request *</label>
                            <textarea class="form-control" id="requestContent" name="content" rows="4" 
                                      placeholder="Please share your prayer request details..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isUrgent" name="is_urgent">
                                <label class="form-check-label" for="isUrgent">
                                    <span class="badge bg-danger">Urgent</span> This is an urgent prayer need
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isPublic" name="is_public" checked>
                                <label class="form-check-label" for="isPublic">
                                    Share publicly (others can pray for you)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isAnonymous" name="is_anonymous">
                                <label class="form-check-label" for="isAnonymous">
                                    Submit anonymously
                                </label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-pray me-2"></i> Submit Prayer Request
                            </button>
                        </div>
                        <div class="alert alert-info small mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            All prayer requests are reviewed before being published. Urgent requests receive immediate attention from our prayer team.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="prayer-filters bg-light p-3 rounded">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="mb-0">Filter Prayer Requests:</h5>
                        <div class="filter-buttons">
                            <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                All Requests
                            </a>
                            <?php foreach ($categories as $category): ?>
                                <a href="?filter=<?php echo $category; ?>" 
                                   class="btn btn-sm <?php echo $filter === $category ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <?php echo ucfirst($category); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prayer Requests List -->
        <div class="row" id="prayer-requests-container">
            <?php if (!empty($prayer_requests)): ?>
                <?php foreach ($prayer_requests as $request): ?>
                    <?php
                    $request_date = new DateTime($request['created_at']);
                    $time_ago = time_elapsed_string($request['created_at']);
                    $is_anonymous = $request['is_anonymous'] || empty($request['full_name']);
                    ?>
                    <div class="col-lg-6 mb-4">
                        <div class="prayer-request-card h-100">
                            <div class="request-header d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <?php if ($request['is_urgent']): ?>
                                        <span class="badge bg-danger me-2">Urgent</span>
                                    <?php endif; ?>
                                    <span class="badge bg-primary"><?php echo ucfirst($request['category']); ?></span>
                                </div>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i> <?php echo $time_ago; ?>
                                </div>
                            </div>
                            
                            <h5 class="mb-3"><?php echo htmlspecialchars($request['title']); ?></h5>
                            
                            <div class="request-content mb-4">
                                <p><?php echo nl2br(htmlspecialchars($request['content'])); ?></p>
                            </div>
                            
                            <div class="request-footer d-flex justify-content-between align-items-center">
                                <div class="request-meta">
                                    <div class="request-author small">
                                        <?php if ($is_anonymous): ?>
                                            <i class="fas fa-user-secret me-1"></i> Anonymous
                                        <?php else: ?>
                                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($request['full_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="prayer-count small text-muted">
                                        <i class="fas fa-hands-praying me-1"></i> 
                                        <?php echo number_format($request['prayer_count'] ?? 0); ?> people prayed
                                    </div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="prayForRequest(<?php echo $request['id']; ?>)">
                                        <i class="fas fa-pray me-1"></i> I Prayed
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default prayer requests if database is empty -->
                <div class="col-lg-6 mb-4">
                    <div class="prayer-request-card h-100">
                        <div class="request-header d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-danger me-2">Urgent</span>
                                <span class="badge bg-primary">Healing</span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-clock me-1"></i> 2 hours ago
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Prayer for Healing</h5>
                        
                        <div class="request-content mb-4">
                            <p>Please pray for my father who is in the hospital with pneumonia. He's 75 years old and his condition is serious. We believe God for complete healing and restoration.</p>
                        </div>
                        
                        <div class="request-footer d-flex justify-content-between align-items-center">
                            <div class="request-meta">
                                <div class="request-author small">
                                    <i class="fas fa-user me-1"></i> Sarah Nkosi
                                </div>
                                <div class="prayer-count small text-muted">
                                    <i class="fas fa-hands-praying me-1"></i> 47 people prayed
                                </div>
                            </div>
                            <div class="request-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="prayForRequest(1)">
                                    <i class="fas fa-pray me-1"></i> I Prayed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="prayer-request-card h-100">
                        <div class="request-header d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary">Family</span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-clock me-1"></i> 1 day ago
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Restoration in Marriage</h5>
                        
                        <div class="request-content mb-4">
                            <p>My husband and I are going through a difficult time in our marriage. Please pray for God's healing, wisdom for us both, and restoration of our relationship according to God's will.</p>
                        </div>
                        
                        <div class="request-footer d-flex justify-content-between align-items-center">
                            <div class="request-meta">
                                <div class="request-author small">
                                    <i class="fas fa-user-secret me-1"></i> Anonymous
                                </div>
                                <div class="prayer-count small text-muted">
                                    <i class="fas fa-hands-praying me-1"></i> 32 people prayed
                                </div>
                            </div>
                            <div class="request-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="prayForRequest(2)">
                                    <i class="fas fa-pray me-1"></i> I Prayed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_requests > $limit): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Prayer requests pagination">
                        <ul class="pagination justify-content-center">
                            <?php
                            $total_pages = ceil($total_requests / $limit);
                            
                            // Previous button
                            if ($page > 1) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?filter=' . $filter . '&page=' . ($page - 1) . '" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>';
                            }
                            
            // Page numbers
            $max_pages_to_show = 5;
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $start_page + $max_pages_to_show - 1);
            
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?filter=' . $filter . '&page=1">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<li class="page-item ' . $active . '">
                    <a class="page-link" href="?filter=' . $filter . '&page=' . $i . '">' . $i . '</a>
                </li>';
            }
            
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?filter=' . $filter . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
                            
                            // Next button
                            if ($page < $total_pages) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?filter=' . $filter . '&page=' . ($page + 1) . '" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

        <!-- Answered Prayers -->
        <div class="answered-prayers mt-5 pt-5 border-top">
            <h3 class="text-center mb-4">Answered Prayers</h3>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="answered-prayer-card">
                        <div class="prayer-status">
                            <span class="badge bg-success">Answered</span>
                        </div>
                        <div class="prayer-content">
                            <p class="mb-3"><em>"Thank you for praying for my job situation. After 6 months of unemployment, I got a job offer yesterday! God is faithful."</em></p>
                            <div class="prayer-meta text-end">
                                <span class="text-muted small">- John Dlamini, 2 weeks ago</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="answered-prayer-card mt-4">
                        <div class="prayer-status">
                            <span class="badge bg-success">Answered</span>
                        </div>
                        <div class="prayer-content">
                            <p class="mb-3"><em>"My daughter's fever broke last night after the church prayed. She's recovering well. Thank you for standing with us in prayer."</em></p>
                            <div class="prayer-meta text-end">
                                <span class="text-muted small">- Anonymous, 1 week ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prayer Resources -->
        <div class="prayer-resources bg-light p-5 rounded mt-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Need Prayer Now?</h3>
                    <p class="mb-0">Our 24/7 prayer line is available for immediate prayer support.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="tel:+26876000000" class="btn btn-primary btn-lg">
                        <i class="fas fa-phone me-2"></i> Call Prayer Line
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper function for time ago
function time_elapsed_string($datetime, $full = false) {
    if (empty($datetime)) return 'Recently';
    
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.request-form-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.prayer-request-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.prayer-request-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.prayer-request-card.urgent {
    border-left: 4px solid #dc3545;
}

.request-content {
    line-height: 1.6;
    color: #333;
}

.request-content p {
    margin-bottom: 0;
}

.prayer-count {
    margin-top: 5px;
}

.answered-prayer-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #28a745;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.prayer-resources {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
}

.prayer-filters .filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .request-form-card {
        padding: 20px;
    }
    
    .prayer-filters {
        padding: 15px;
    }
    
    .prayer-filters .filter-buttons {
        margin-top: 10px;
        justify-content: center;
    }
    
    .prayer-resources .row {
        text-align: center;
    }
    
    .prayer-resources .text-md-end {
        text-align: center !important;
        margin-top: 15px;
    }
}
</style>

<script>
// Prayer request form submission
document.getElementById('prayerRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: this.requestName.value,
        category: this.requestCategory.value,
        title: this.requestTitle.value,
        content: this.requestContent.value,
        is_urgent: this.isUrgent.checked,
        is_public: this.isPublic.checked,
        is_anonymous: this.isAnonymous.checked
    };
    
    // Validate form
    if (!formData.name || !formData.category || !formData.title || !formData.content) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Simulate submission
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    button.disabled = true;
    
    setTimeout(() => {
        alert('Thank you for sharing your prayer request. Our prayer team will lift it up and it will be reviewed for publication.');
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Scroll to requests section
        document.getElementById('prayer-requests-container').scrollIntoView({ behavior: 'smooth' });
        
        // Reload page to show new request (simulated)
        setTimeout(() => location.reload(), 2000);
    }, 2000);
});

// "I Prayed" button functionality
function prayForRequest(requestId) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-1"></i> Prayed';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        // Update prayer count in UI
        const prayerCountElement = button.closest('.request-footer').querySelector('.prayer-count');
        const currentCount = parseInt(prayerCountElement.textContent.match(/\d+/)[0]) || 0;
        prayerCountElement.innerHTML = `<i class="fas fa-hands-praying me-1"></i> ${currentCount + 1} people prayed`;
        
        alert('Thank you for praying! Your prayer makes a difference.');
    }, 1000);
}

// Filter buttons functionality
document.querySelectorAll('.filter-buttons a').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const filter = this.getAttribute('href').split('=')[1];
        window.location.href = `prayer-request.php?filter=${filter}`;
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
</script>

<?php
// Close database connection if created locally
if (isset($local_conn) && $local_conn) {
    $conn = null;
}
require_once 'includes/footer.php';
?>