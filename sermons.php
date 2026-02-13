<?php
// sermons.php
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

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$preacher = isset($_GET['preacher']) ? $_GET['preacher'] : 'all';
$year = isset($_GET['year']) ? $_GET['year'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch sermons with filters
$sermons = [];
$categories = [];
$preachers = [];
$years = [];

if ($conn) {
    try {
        // Build query with filters
        $sql = "SELECT s.*, u.full_name as preacher_name 
                FROM sermons s 
                LEFT JOIN users u ON s.preacher_id = u.id 
                WHERE s.is_published = 1";
        
        $params = [];
        
        if ($category !== 'all') {
            $sql .= " AND s.category = ?";
            $params[] = $category;
        }
        
        if ($preacher !== 'all') {
            $sql .= " AND s.preacher_id = ?";
            $params[] = $preacher;
        }
        
        if ($year !== 'all') {
            $sql .= " AND YEAR(s.sermon_date) = ?";
            $params[] = $year;
        }
        
        if (!empty($search)) {
            $sql .= " AND (s.title LIKE ? OR s.bible_passage LIKE ? OR s.notes_text LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY s.sermon_date DESC LIMIT 12";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch distinct categories
        $stmt = $conn->query("SELECT DISTINCT category FROM sermons WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Fetch preachers
        $stmt = $conn->query("SELECT DISTINCT u.id, u.full_name FROM sermons s JOIN users u ON s.preacher_id = u.id WHERE s.is_published = 1 ORDER BY u.full_name");
        $preachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch years
        $stmt = $conn->query("SELECT DISTINCT YEAR(sermon_date) as year FROM sermons WHERE is_published = 1 ORDER BY year DESC");
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Error fetching sermons: " . $e->getMessage());
    }
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/sermons-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Sermons & Teaching</h1>
                <p class="text-white mb-0">Biblical teaching to encourage, equip, and transform your life</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Sermons</li>
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
                <h2 class="mb-4">Biblical Teaching for Life Transformation</h2>
                <p class="lead">Access our library of sermons and biblical teachings. Listen, watch, or download messages to grow in your faith journey.</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="filter-card p-4 rounded shadow">
                    <form action="sermons.php" method="GET" class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search sermons..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select name="category" class="form-select">
                                <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(htmlspecialchars($cat)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select name="preacher" class="form-select">
                                <option value="all" <?php echo $preacher === 'all' ? 'selected' : ''; ?>>All Preachers</option>
                                <?php foreach ($preachers as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p['id']); ?>" <?php echo $preacher == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select name="year" class="form-select">
                                <option value="all" <?php echo $year === 'all' ? 'selected' : ''; ?>>All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?php echo htmlspecialchars($y); ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($y); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sermons Grid -->
        <div class="row" id="sermons-container">
            <?php if (empty($sermons)): ?>
                <!-- Default sermons if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="sermon-card h-100">
                        <div class="sermon-image position-relative">
                            <img src="assets/images/sermons/forgiveness.jpg" alt="The Power of Forgiveness" class="img-fluid">
                            <div class="sermon-duration">45:30</div>
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="sermon-content p-4">
                            <div class="sermon-meta mb-2">
                                <span class="badge bg-primary">Teaching</span>
                                <span class="text-muted ms-2"><i class="far fa-calendar-alt me-1"></i> June 29, 2025</span>
                            </div>
                            <h3 class="h5 mb-3">The Power of Forgiveness</h3>
                            <p class="sermon-passage mb-3"><i class="fas fa-bible me-2 text-primary"></i>Matthew 6:14-15</p>
                            <p class="sermon-description mb-4">A sermon on the importance of forgiveness in Christian life and how to practice it daily.</p>
                            <div class="sermon-actions">
                                <a href="sermon-details.php?id=1" class="btn btn-sm btn-outline-primary"><i class="fas fa-headphones"></i> Listen</a>
                                <a href="sermon-details.php?id=1" class="btn btn-sm btn-outline-primary"><i class="fas fa-video"></i> Watch</a>
                                <a href="sermon-download.php?id=1" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Notes</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="sermon-card h-100">
                        <div class="sermon-image position-relative">
                            <img src="assets/images/sermons/faith.jpg" alt="Walking by Faith" class="img-fluid">
                            <div class="sermon-duration">52:15</div>
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="sermon-content p-4">
                            <div class="sermon-meta mb-2">
                                <span class="badge bg-primary">Teaching</span>
                                <span class="text-muted ms-2"><i class="far fa-calendar-alt me-1"></i> June 22, 2025</span>
                            </div>
                            <h3 class="h5 mb-3">Walking by Faith</h3>
                            <p class="sermon-passage mb-3"><i class="fas fa-bible me-2 text-primary"></i>2 Corinthians 5:7</p>
                            <p class="sermon-description mb-4">Exploring what it means to live a life guided by faith, not by sight, in today's world.</p>
                            <div class="sermon-actions">
                                <a href="sermon-details.php?id=2" class="btn btn-sm btn-outline-primary"><i class="fas fa-headphones"></i> Listen</a>
                                <a href="sermon-details.php?id=2" class="btn btn-sm btn-outline-primary"><i class="fas fa-video"></i> Watch</a>
                                <a href="sermon-download.php?id=2" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Notes</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="sermon-card h-100">
                        <div class="sermon-image position-relative">
                            <img src="assets/images/sermons/hope.jpg" alt="Hope in Difficult Times" class="img-fluid">
                            <div class="sermon-duration">48:20</div>
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="sermon-content p-4">
                            <div class="sermon-meta mb-2">
                                <span class="badge bg-primary">Encouragement</span>
                                <span class="text-muted ms-2"><i class="far fa-calendar-alt me-1"></i> June 15, 2025</span>
                            </div>
                            <h3 class="h5 mb-3">Hope in Difficult Times</h3>
                            <p class="sermon-passage mb-3"><i class="fas fa-bible me-2 text-primary"></i>Romans 15:13</p>
                            <p class="sermon-description mb-4">Finding hope and strength in God during challenging seasons of life.</p>
                            <div class="sermon-actions">
                                <a href="sermon-details.php?id=3" class="btn btn-sm btn-outline-primary"><i class="fas fa-headphones"></i> Listen</a>
                                <a href="sermon-details.php?id=3" class="btn btn-sm btn-outline-primary"><i class="fas fa-video"></i> Watch</a>
                                <a href="sermon-download.php?id=3" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Notes</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($sermons as $sermon): ?>
                    <?php
                    $sermon_date = new DateTime($sermon['sermon_date']);
                    $formatted_date = $sermon_date->format('F j, Y');
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="sermon-card h-100">
                            <div class="sermon-image position-relative">
                                <img src="<?php echo htmlspecialchars($sermon['thumbnail_url'] ?: 'assets/images/sermons/default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($sermon['title']); ?>" 
                                     class="img-fluid">
                                <?php if ($sermon['duration']): ?>
                                    <div class="sermon-duration"><?php echo gmdate("i:s", $sermon['duration']); ?></div>
                                <?php endif; ?>
                                <div class="play-button" data-sermon-id="<?php echo htmlspecialchars($sermon['id']); ?>">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="sermon-content p-4">
                                <div class="sermon-meta mb-2">
                                    <?php if ($sermon['category']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($sermon['category']); ?></span>
                                    <?php endif; ?>
                                    <span class="text-muted ms-2"><i class="far fa-calendar-alt me-1"></i><?php echo $formatted_date; ?></span>
                                </div>
                                <h3 class="h5 mb-3"><?php echo htmlspecialchars($sermon['title']); ?></h3>
                                <?php if ($sermon['bible_passage']): ?>
                                    <p class="sermon-passage mb-3"><i class="fas fa-bible me-2 text-primary"></i><?php echo htmlspecialchars($sermon['bible_passage']); ?></p>
                                <?php endif; ?>
                                <p class="sermon-description mb-4"><?php echo htmlspecialchars(substr($sermon['notes_text'] ?: 'Biblical teaching for spiritual growth.', 0, 100)); ?>...</p>
                                <div class="sermon-actions">
                                    <?php if ($sermon['audio_url']): ?>
                                        <a href="sermon-details.php?id=<?php echo htmlspecialchars($sermon['id']); ?>&type=audio" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-headphones"></i> Listen
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($sermon['video_url']): ?>
                                        <a href="sermon-details.php?id=<?php echo htmlspecialchars($sermon['id']); ?>&type=video" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-video"></i> Watch
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($sermon['slides_url'] || $sermon['notes_text']): ?>
                                        <a href="sermon-download.php?id=<?php echo htmlspecialchars($sermon['id']); ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Notes
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sermon Series -->
        <div class="sermon-series-section mt-5 pt-5">
            <h3 class="text-center mb-4">Sermon Series</h3>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="series-card">
                        <div class="series-image">
                            <img src="assets/images/series/foundations.jpg" alt="Foundations of Faith" class="img-fluid">
                            <div class="series-overlay">
                                <a href="series.php?id=1" class="btn btn-primary">View Series</a>
                            </div>
                        </div>
                        <div class="series-content p-4">
                            <h4 class="h5 mb-2">Foundations of Faith</h4>
                            <p class="text-muted small mb-3">6 sermons • Jan - Mar 2025</p>
                            <p class="small">A series exploring the core foundations of Christian faith and doctrine.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="series-card">
                        <div class="series-image">
                            <img src="assets/images/series/victory.jpg" alt="Walking in Victory" class="img-fluid">
                            <div class="series-overlay">
                                <a href="series.php?id=2" class="btn btn-primary">View Series</a>
                            </div>
                        </div>
                        <div class="series-content p-4">
                            <h4 class="h5 mb-2">Walking in Victory</h4>
                            <p class="text-muted small mb-3">8 sermons • Apr - Jun 2025</p>
                            <p class="small">Learning to live in the victory that Christ has won for us through practical biblical principles.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="series-card">
                        <div class="series-image">
                            <img src="assets/images/series/worship.jpg" alt="The Heart of Worship" class="img-fluid">
                            <div class="series-overlay">
                                <a href="series.php?id=3" class="btn btn-primary">View Series</a>
                            </div>
                        </div>
                        <div class="series-content p-4">
                            <h4 class="h5 mb-2">The Heart of Worship</h4>
                            <p class="text-muted small mb-3">5 sermons • Jul - Sep 2025</p>
                            <p class="small">Understanding true worship and developing a lifestyle of worship in daily life.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile App Section -->
        <div class="mobile-app-section bg-primary text-white p-5 rounded mt-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-3">Take Sermons With You</h3>
                    <p class="mb-4">Download our church app to access sermons, events, and devotionals on the go.</p>
                    <div class="app-buttons">
                        <a href="#" class="btn btn-light me-3">
                            <i class="fab fa-apple me-2"></i>App Store
                        </a>
                        <a href="#" class="btn btn-light">
                            <i class="fab fa-google-play me-2"></i>Google Play
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <i class="fas fa-mobile-alt display-1"></i>
                </div>
            </div>
        </div>

        <!-- Popular Sermons -->
        <div class="popular-sermons mt-5 pt-5">
            <h3 class="text-center mb-4">Most Popular Sermons</h3>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="popular-sermon-card d-flex align-items-center p-3 rounded">
                        <div class="popular-sermon-number me-3">
                            <span class="display-6 fw-bold text-primary">1</span>
                        </div>
                        <div class="popular-sermon-content">
                            <h5 class="mb-1">The Power of Prayer</h5>
                            <p class="small text-muted mb-1">Bishop Zakes Nxumalo • 2,500+ views</p>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="popular-sermon-card d-flex align-items-center p-3 rounded">
                        <div class="popular-sermon-number me-3">
                            <span class="display-6 fw-bold text-primary">2</span>
                        </div>
                        <div class="popular-sermon-content">
                            <h5 class="mb-1">Overcoming Fear</h5>
                            <p class="small text-muted mb-1">Bishop Zakes Nxumalo • 1,800+ views</p>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section text-center py-5 mt-5">
            <h2 class="mb-4">Subscribe to Our Podcast</h2>
            <p class="lead mb-4">Get new sermons delivered to your favorite podcast app automatically.</p>
            <div class="cta-buttons">
                <a href="podcast.php" class="btn btn-primary btn-lg me-3"><i class="fas fa-podcast me-2"></i>Apple Podcasts</a>
                <a href="rss.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-rss me-2"></i>RSS Feed</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.filter-card {
    background: white;
    border: 1px solid #eee;
}

.sermon-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.sermon-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.sermon-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.sermon-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.sermon-card:hover .sermon-image img {
    transform: scale(1.05);
}

.sermon-duration {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 0.8rem;
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.play-button i {
    color: #1a5276;
    font-size: 1.5rem;
    margin-left: 5px;
}

.sermon-card:hover .play-button {
    opacity: 1;
}

.sermon-meta .badge {
    font-size: 0.7rem;
    font-weight: 500;
}

.sermon-passage {
    color: #666;
    font-style: italic;
}

.sermon-description {
    color: #555;
    font-size: 0.9rem;
}

.sermon-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.series-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.series-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.series-image {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.series-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.series-overlay {
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

.series-card:hover .series-overlay {
    opacity: 1;
}

.mobile-app-section {
    background: linear-gradient(135deg, #1a5276 0%, #154360 100%);
}

.popular-sermon-card {
    background: #f8f9fa;
    border: 1px solid #eee;
    transition: all 0.3s ease;
}

.popular-sermon-card:hover {
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.popular-sermon-number {
    width: 60px;
    text-align: center;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .filter-card .col-lg-2 {
        margin-bottom: 10px;
    }
    
    .sermon-image {
        height: 150px;
    }
    
    .play-button {
        width: 50px;
        height: 50px;
    }
    
    .play-button i {
        font-size: 1.2rem;
    }
    
    .mobile-app-section .btn-light {
        margin-bottom: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Play button functionality
    const playButtons = document.querySelectorAll('.play-button');
    playButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sermonId = this.getAttribute('data-sermon-id');
            if (sermonId) {
                window.location.href = `sermon-details.php?id=${sermonId}`;
            } else {
                const card = this.closest('.sermon-card');
                const title = card.querySelector('h3').textContent;
                alert(`Now playing: ${title}\n\nThis is a demo. In the real app, this would play the sermon audio/video.`);
            }
        });
    });
    
    // Filter form submission
    const filterForm = document.querySelector('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Allow empty search to still filter by other criteria
            return true;
        });
    }
    
    // Search clear button
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        const form = searchInput.closest('form');
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-outline-secondary';
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.style.position = 'absolute';
        clearBtn.style.right = '10px';
        clearBtn.style.top = '50%';
        clearBtn.style.transform = 'translateY(-50%)';
        clearBtn.style.zIndex = '5';
        clearBtn.style.display = searchInput.value ? 'block' : 'none';
        
        const inputGroup = searchInput.closest('.input-group');
        if (inputGroup) {
            inputGroup.style.position = 'relative';
            inputGroup.appendChild(clearBtn);
            
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearBtn.style.display = 'none';
                form.submit();
            });
            
            searchInput.addEventListener('input', function() {
                clearBtn.style.display = this.value ? 'block' : 'none';
            });
        }
    }
    
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