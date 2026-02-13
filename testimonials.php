<?php
require_once 'includes/header.php';

// Variables for filtering and pagination
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch testimonials
$testimonials = [];
$total_testimonials = 0;
$categories = ['salvation', 'healing', 'provision', 'family', 'ministry', 'other'];

try {
    // Build query based on filters
    $sql = "SELECT t.*, u.full_name, u.email 
            FROM testimonials t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.is_approved = 1";
    
    $count_sql = "SELECT COUNT(*) as total FROM testimonials WHERE is_approved = 1";
    $params = [];
    
    if ($category !== 'all') {
        $sql .= " AND t.category = ?";
        $count_sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
    
    // Get total count
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_testimonials = $count_stmt->fetch(PDO::FETCH_COLUMN);
    
    // Get paginated results
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Testimonials fetch error: " . $e->getMessage());
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Testimonies</h1>
                <p class="text-white mb-0">Stories of God's faithfulness in our church family</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Testimonies</li>
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
                <h2 class="mb-3">What God Has Done</h2>
                <p class="lead">These stories testify to God's power, love, and faithfulness in the lives of our church members. Be encouraged and strengthened by what He has done!</p>
            </div>
        </div>

        <!-- Submit Testimonial -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="submit-testimonial-card text-center p-5 rounded">
                    <h3 class="mb-3">Share Your Story</h3>
                    <p class="mb-4">Has God done something amazing in your life? Share your testimony to encourage others and give God the glory!</p>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#submitTestimonialModal">
                        <i class="fas fa-plus-circle me-2"></i> Share Your Testimony
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="testimonial-filters bg-light p-3 rounded">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="mb-0">Filter Testimonies:</h5>
                        <div class="filter-buttons">
                            <a href="?category=all" class="btn btn-sm <?php echo $category === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                All Testimonies
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="?category=<?php echo $cat; ?>" 
                                   class="btn btn-sm <?php echo $category === $cat ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <?php echo ucfirst($cat); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Grid -->
        <div class="row" id="testimonials-container">
            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <?php
                    $testimonial_date = new DateTime($testimonial['created_at']);
                    $time_ago = time_elapsed_string($testimonial['created_at']);
                    $is_anonymous = $testimonial['is_anonymous'] || empty($testimonial['full_name']);
                    $content_preview = substr($testimonial['content'], 0, 150);
                    if (strlen($testimonial['content']) > 150) {
                        $content_preview .= '...';
                    }
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="testimonial-card h-100">
                            <div class="testimonial-header d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-primary"><?php echo ucfirst($testimonial['category']); ?></span>
                                </div>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i> <?php echo $time_ago; ?>
                                </div>
                            </div>
                            
                            <div class="testimonial-content mb-4">
                                <p>"<?php echo htmlspecialchars($content_preview); ?>"</p>
                            </div>
                            
                            <div class="testimonial-footer">
                                <div class="author-info d-flex align-items-center">
                                    <div class="author-avatar me-3">
                                        <?php if ($is_anonymous): ?>
                                            <div class="avatar-initials">
                                                <i class="fas fa-user-secret"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-initials">
                                                <?php echo strtoupper(substr($testimonial['full_name'], 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="author-name fw-bold">
                                            <?php echo $is_anonymous ? 'Anonymous' : htmlspecialchars($testimonial['full_name']); ?>
                                        </div>
                                        <div class="author-date small text-muted">
                                            <?php echo $testimonial_date->format('M d, Y'); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Read More Button -->
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#readTestimonialModal"
                                            data-title="<?php echo htmlspecialchars($testimonial['title'] ?? 'Testimony'); ?>"
                                            data-content="<?php echo htmlspecialchars($testimonial['content']); ?>"
                                            data-author="<?php echo $is_anonymous ? 'Anonymous' : htmlspecialchars($testimonial['full_name']); ?>"
                                            data-date="<?php echo $testimonial_date->format('F j, Y'); ?>"
                                            data-category="<?php echo ucfirst($testimonial['category']); ?>">
                                        <i class="fas fa-book-open me-1"></i> Read Full Story
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default testimonials if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card h-100">
                        <div class="testimonial-header d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary">Healing</span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-clock me-1"></i> 2 weeks ago
                            </div>
                        </div>
                        
                        <div class="testimonial-content mb-4">
                            <p>"After doctors said there was no hope for my recovery from the accident, our church prayed. Today I'm walking and praising God for His miraculous healing!"</p>
                        </div>
                        
                        <div class="testimonial-footer">
                            <div class="author-info d-flex align-items-center">
                                <div class="author-avatar me-3">
                                    <div class="avatar-initials">JD</div>
                                </div>
                                <div>
                                    <div class="author-name fw-bold">John Dlamini</div>
                                    <div class="author-date small text-muted">Jun 1, 2025</div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#readTestimonialModal"
                                        data-title="Miracle Healing"
                                        data-content="After a terrible car accident last year, doctors told me I might never walk again. The church rallied around me in prayer. For months, people visited, called, and most importantly, prayed. Last month, against all medical predictions, I took my first steps. Today I'm walking without assistance. God is truly a healer!"
                                        data-author="John Dlamini"
                                        data-date="June 1, 2025"
                                        data-category="Healing">
                                    <i class="fas fa-book-open me-1"></i> Read Full Story
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card h-100">
                        <div class="testimonial-header d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary">Salvation</span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-clock me-1"></i> 1 month ago
                            </div>
                        </div>
                        
                        <div class="testimonial-content mb-4">
                            <p>"I came to church broken and hopeless. Through the love shown to me and the message of the Gospel, I found new life in Christ. My family is being restored!"</p>
                        </div>
                        
                        <div class="testimonial-footer">
                            <div class="author-info d-flex align-items-center">
                                <div class="author-avatar me-3">
                                    <div class="avatar-initials">TM</div>
                                </div>
                                <div>
                                    <div class="author-name fw-bold">Thomas Mbeki</div>
                                    <div class="author-date small text-muted">May 15, 2025</div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#readTestimonialModal"
                                        data-title="New Life in Christ"
                                        data-content="For years I struggled with addiction and broken relationships. My wife had left me, and I had lost my job. One Sunday, a friend invited me to CFCI. I felt the love of God through the people there. When the altar call was made, I couldn't stay in my seat. I gave my life to Christ that day. Since then, I've been sober for 6 months, my wife has returned, and I have a new job. God has completely transformed my life!"
                                        data-author="Thomas Mbeki"
                                        data-date="May 15, 2025"
                                        data-category="Salvation">
                                    <i class="fas fa-book-open me-1"></i> Read Full Story
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card h-100">
                        <div class="testimonial-header d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary">Provision</span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-clock me-1"></i> 3 weeks ago
                            </div>
                        </div>
                        
                        <div class="testimonial-content mb-4">
                            <p>"After losing my job, I didn't know how I would pay rent or feed my children. God provided through this church family in ways I never imagined possible."</p>
                        </div>
                        
                        <div class="testimonial-footer">
                            <div class="author-info d-flex align-items-center">
                                <div class="author-avatar me-3">
                                    <div class="avatar-initials">
                                        <i class="fas fa-user-secret"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="author-name fw-bold">Anonymous</div>
                                    <div class="author-date small text-muted">May 22, 2025</div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#readTestimonialModal"
                                        data-title="God's Provision"
                                        data-content="When I lost my job earlier this year, I was terrified. As a single mother of three, I didn't know how we would survive. I shared my need with my small group leader, not expecting much. The next day, someone anonymously paid my rent for three months. A week later, groceries started appearing at my door. Members of the church helped me update my resume and even found me a better job than the one I lost. God used His people to provide for us in our time of need."
                                        data-author="Anonymous"
                                        data-date="May 22, 2025"
                                        data-category="Provision">
                                    <i class="fas fa-book-open me-1"></i> Read Full Story
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_testimonials > $limit): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Testimonials pagination">
                        <ul class="pagination justify-content-center">
                            <?php
                            $total_pages = ceil($total_testimonials / $limit);
                            
                            // Previous button
                            if ($page > 1) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?category=' . $category . '&page=' . ($page - 1) . '" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>';
                            }
                            
                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = $i == $page ? 'active' : '';
                                echo '<li class="page-item ' . $active . '">
                                    <a class="page-link" href="?category=' . $category . '&page=' . $i . '">' . $i . '</a>
                                </li>';
                            }
                            
                            // Next button
                            if ($page < $total_pages) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="?category=' . $category . '&page=' . ($page + 1) . '" aria-label="Next">
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

        <!-- Video Testimonials -->
        <div class="video-testimonials mt-5 pt-5 border-top">
            <h3 class="text-center mb-4">Video Testimonies</h3>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="video-testimonial-card">
                        <div class="video-container">
                            <iframe src="https://www.youtube.com/embed/example1" 
                                    title="Video testimony" 
                                    frameborder="0" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                        <div class="video-info p-3">
                            <h5 class="mb-2">Healed from Cancer</h5>
                            <p class="small text-muted mb-0">Sarah shares how God healed her from stage 4 cancer</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="video-testimonial-card">
                        <div class="video-container">
                            <iframe src="https://www.youtube.com/embed/example2" 
                                    title="Video testimony" 
                                    frameborder="0" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                        <div class="video-info p-3">
                            <h5 class="mb-2">Restored Marriage</h5>
                            <p class="small text-muted mb-0">A couple shares how God saved their marriage</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Encouragement Section -->
        <div class="encouragement-section bg-light p-5 rounded mt-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">Your Story Matters</h3>
                    <p class="mb-0">Your testimony could be exactly what someone needs to hear today. Don't keep what God has done for you to yourself!</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#submitTestimonialModal">
                        <i class="fas fa-share-alt me-2"></i> Share Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Read Testimonial Modal -->
<div class="modal fade" id="readTestimonialModal" tabindex="-1" aria-labelledby="readTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="readTestimonialModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="testimonial-meta mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="author-info d-flex align-items-center">
                                <div class="author-avatar me-3">
                                    <div class="avatar-initials" id="modalAuthorInitials"></div>
                                </div>
                                <div>
                                    <div class="author-name fw-bold" id="modalAuthorName"></div>
                                    <div class="author-date small text-muted" id="modalAuthorDate"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-primary" id="modalCategory"></span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-content">
                    <p id="modalTestimonialContent"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="shareTestimonial()">
                    <i class="fas fa-share-alt me-1"></i> Share This Story
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Submit Testimonial Modal -->
<div class="modal fade" id="submitTestimonialModal" tabindex="-1" aria-labelledby="submitTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitTestimonialModalLabel">Share Your Testimony</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="submitTestimonialForm">
                    <div class="mb-3">
                        <label for="testimonialTitle" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="testimonialTitle" name="title" required placeholder="Brief title of your testimony">
                    </div>
                    <div class="mb-3">
                        <label for="testimonialCategory" class="form-label">Category *</label>
                        <select class="form-select" id="testimonialCategory" name="category" required>
                            <option value="">Select category</option>
                            <option value="salvation">Salvation</option>
                            <option value="healing">Healing</option>
                            <option value="provision">Provision</option>
                            <option value="family">Family</option>
                            <option value="ministry">Ministry</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="testimonialContent" class="form-label">Your Testimony *</label>
                        <textarea class="form-control" id="testimonialContent" name="content" rows="5" required placeholder="Share your story of what God has done in your life..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isAnonymous" name="is_anonymous">
                            <label class="form-check-label" for="isAnonymous">
                                Share anonymously
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i>
                        Your testimony will be reviewed before being published. We may edit for clarity and length.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTestimonial()">Submit Testimony</button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for time ago
function time_elapsed_string($datetime, $full = false) {
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

.submit-testimonial-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
}

.testimonial-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.testimonial-content {
    flex: 1;
    font-style: italic;
    line-height: 1.6;
    color: var(--text);
}

.testimonial-content p {
    margin-bottom: 0;
}

.author-avatar .avatar-initials {
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.author-avatar .avatar-initials i {
    font-size: 1.2rem;
}

.video-testimonial-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: var(--transition);
}

.video-testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.video-info {
    background: white;
}

.encouragement-section {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
}

.testimonial-filters .filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

#readTestimonialModal .modal-dialog {
    max-width: 800px;
}

#readTestimonialModal .testimonial-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .submit-testimonial-card {
        padding: 30px 20px;
    }
    
    .testimonial-filters {
        padding: 15px;
    }
    
    .testimonial-filters .filter-buttons {
        margin-top: 10px;
        justify-content: center;
    }
    
    .author-avatar .avatar-initials {
        width: 40px;
        height: 40px;
    }
    
    .encouragement-section .row {
        text-align: center;
    }
    
    .encouragement-section .text-md-end {
        text-align: center !important;
        margin-top: 15px;
    }
}
</style>

<script>
// Read testimonial modal
const readTestimonialModal = document.getElementById('readTestimonialModal');
if (readTestimonialModal) {
    readTestimonialModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const title = button.getAttribute('data-title');
        const content = button.getAttribute('data-content');
        const author = button.getAttribute('data-author');
        const date = button.getAttribute('data-date');
        const category = button.getAttribute('data-category');
        
        // Get author initials
        let initials = '??';
        if (author === 'Anonymous') {
            initials = '<i class="fas fa-user-secret"></i>';
        } else {
            const nameParts = author.split(' ');
            initials = nameParts[0].charAt(0).toUpperCase();
            if (nameParts.length > 1) {
                initials += nameParts[1].charAt(0).toUpperCase();
            }
        }
        
        const modal = this;
        modal.querySelector('.modal-title').textContent = title;
        modal.querySelector('#modalAuthorInitials').innerHTML = initials;
        modal.querySelector('#modalAuthorName').textContent = author;
        modal.querySelector('#modalAuthorDate').textContent = date;
        modal.querySelector('#modalCategory').textContent = category;
        modal.querySelector('#modalTestimonialContent').textContent = content;
    });
}

// Share testimonial function
function shareTestimonial() {
    const title = document.querySelector('#readTestimonialModalLabel').textContent;
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Read this amazing testimony from CFCI',
            url: url,
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('Testimony link copied to clipboard!');
        });
    }
}

// Submit testimonial form
function submitTestimonial() {
    const form = document.getElementById('submitTestimonialForm');
    const title = form.testimonialTitle.value;
    const category = form.testimonialCategory.value;
    const content = form.testimonialContent.value;
    
    if (!title || !category || !content) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Simulate submission
    const button = document.querySelector('#submitTestimonialModal .btn-primary');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        alert('Thank you for sharing your testimony! It will be reviewed and published soon.');
        form.reset();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('submitTestimonialModal'));
        modal.hide();
        
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Refresh page to show new testimony (simulated)
        setTimeout(() => location.reload(), 2000);
    }, 2000);
}

// Filter buttons functionality
document.querySelectorAll('.filter-buttons a').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const category = this.getAttribute('href').split('=')[1];
        window.location.href = `testimonials.php?category=${category}`;
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>