<?php
// sermon-details.php - UPDATED VERSION
require_once 'includes/config.php';
require_once 'includes/main-functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
try {
    $host = 'localhost';
    $dbname = 'cfci_church_db';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get sermon ID with validation
$sermon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$media_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'audio';

// Fetch sermon details
$sermon = null;
if ($sermon_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM sermons WHERE id = ?");
        $stmt->execute([$sermon_id]);
        $sermon = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Error fetching sermon: " . $e->getMessage());
    }
}

// Redirect if sermon not found
if (!$sermon) {
    echo '<script>alert("Sermon not found or no longer available."); window.location.href = "sermons.php";</script>';
    exit();
}

// Fetch preacher details
$preacher = null;
if (!empty($sermon['preacher_id'])) {
    try {
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$sermon['preacher_id']]);
        $preacher = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Continue without preacher details
    }
}

// Fetch related sermons by same preacher
$related_sermons = [];
if (!empty($sermon['preacher_id'])) {
    try {
        $stmt = $conn->prepare("SELECT id, title, sermon_date, thumbnail_url FROM sermons WHERE preacher_id = ? AND id != ? ORDER BY sermon_date DESC LIMIT 3");
        $stmt->execute([$sermon['preacher_id'], $sermon_id]);
        $related_sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Continue without related sermons
    }
}

// Fetch sermons in same series
$sermon_series = [];
if (!empty($sermon['series'])) {
    try {
        $stmt = $conn->prepare("SELECT id, title, sermon_date FROM sermons WHERE series = ? AND id != ? ORDER BY sermon_date LIMIT 10");
        $stmt->execute([$sermon['series'], $sermon_id]);
        $sermon_series = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Continue without series data
    }
}

// Format dates
$formatted_date = date('F j, Y', strtotime($sermon['sermon_date']));
$duration = $sermon['duration'] ? gmdate("i:s", $sermon['duration']) : 'N/A';

// Determine media URL based on type
$media_url = null;
$media_title = '';
if ($media_type === 'video' && !empty($sermon['video_url'])) {
    $media_url = $sermon['video_url'];
    $media_title = 'Watch Video';
} elseif (!empty($sermon['audio_url'])) {
    $media_url = $sermon['audio_url'];
    $media_title = 'Listen to Audio';
}

// Generate share URLs
$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$share_text = urlencode("Listen to: {$sermon['title']} - Christian Family Centre International");
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($current_url);
$twitter_url = "https://twitter.com/intent/tweet?url=" . urlencode($current_url) . "&text=" . $share_text;
$whatsapp_url = "https://wa.me/?text=" . $share_text . " " . urlencode($current_url);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sermon['title']); ?> - CFCI Sermons</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --primary-light: #2e86c1;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        
        .sermon-player {
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .player-header {
            background: var(--primary);
            color: white;
            padding: 20px;
        }
        
        .player-body {
            padding: 30px;
        }
        
        .audio-player {
            width: 100%;
            border-radius: 10px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 10px;
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        
        .download-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }
        
        .download-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .related-sermon {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .related-sermon:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .related-sermon img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .notes-form textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .scripture-highlight {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
        }
        
        .share-buttons .btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
        }
        
        .btn-facebook {
            background: #1877f2;
            color: white;
            border: none;
        }
        
        .btn-twitter {
            background: #1da1f2;
            color: white;
            border: none;
        }
        
        .avatar-initials-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .avatar-initials-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .sermon-details-grid {
                grid-template-columns: 1fr;
            }
            
            .related-sermon {
                flex-direction: column;
                text-align: center;
            }
            
            .related-sermon img {
                width: 100%;
                height: 150px;
            }
        }
        
        .section-padding {
            padding: 4rem 0;
        }
        
        @media (max-width: 768px) {
            .section-padding {
                padding: 2rem 0;
            }
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0.75rem 0;
            margin-bottom: 0;
        }
        
        .breadcrumb-item a {
            text-decoration: none;
            color: var(--primary);
        }
        
        .breadcrumb-item.active {
            color: #666;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .content {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <!-- Simple Header (since we don't have the full includes) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span style="color: var(--primary); font-weight: 700; font-size: 1.5rem;">CFCI</span>
                <small class="d-block text-muted" style="font-size: 0.8rem;">Christian Family Centre International</small>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sermons.php">Sermons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb -->
    <nav class="bg-light py-3">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="sermons.php">Sermons</a></li>
                <li class="breadcrumb-item active"><?php echo substr(htmlspecialchars($sermon['title']), 0, 50); ?><?php echo strlen($sermon['title']) > 50 ? '...' : ''; ?></li>
            </ol>
        </div>
    </nav>
    
    <!-- Main Content -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Sermon Header -->
                    <div class="mb-4">
                        <?php if ($sermon['series']): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary">Part of Series: <?php echo htmlspecialchars($sermon['series']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <h1 class="mb-3"><?php echo htmlspecialchars($sermon['title']); ?></h1>
                        
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-initials-sm me-2">
                                    <?php 
                                    $initials = '';
                                    if ($preacher && isset($preacher['full_name'])) {
                                        $words = explode(' ', $preacher['full_name']);
                                        foreach ($words as $word) {
                                            if (!empty($word)) {
                                                $initials .= strtoupper(substr($word, 0, 1));
                                            }
                                        }
                                        $initials = substr($initials, 0, 2);
                                    } else {
                                        $initials = 'CF';
                                    }
                                    echo $initials;
                                    ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($preacher['full_name'] ?? 'CFCI Ministry'); ?></div>
                                    <div class="text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i><?php echo $formatted_date; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($sermon['scripture_reference'])): ?>
                            <div class="ms-auto">
                                <i class="fas fa-bible text-primary me-1"></i>
                                <strong><?php echo htmlspecialchars($sermon['scripture_reference']); ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stats -->
                        <div class="d-flex gap-4 mb-4">
                            <div class="stat-item">
                                <i class="far fa-eye me-1"></i>
                                <span><?php echo number_format($sermon['view_count'] ?? 0); ?> views</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-download me-1"></i>
                                <span><?php echo number_format($sermon['download_count'] ?? 0); ?> downloads</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-clock me-1"></i>
                                <span><?php echo $duration; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Media Player -->
                    <div class="sermon-player mb-5">
                        <div class="player-header">
                            <h4 class="mb-0"><?php echo $media_title; ?></h4>
                        </div>
                        <div class="player-body">
                            <?php if ($media_type === 'video' && $media_url): ?>
                            <div class="video-container">
                                <?php if (strpos($media_url, 'youtube.com') !== false || strpos($media_url, 'youtu.be') !== false): ?>
                                <iframe src="<?php echo htmlspecialchars($media_url); ?>" 
                                        title="<?php echo htmlspecialchars($sermon['title']); ?>"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
                                <?php else: ?>
                                <video controls class="w-100" style="max-height: 500px;">
                                    <source src="<?php echo htmlspecialchars($media_url); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <?php endif; ?>
                            </div>
                            <?php elseif ($media_url): ?>
                            <div class="audio-player">
                                <audio controls class="w-100" id="sermonAudio">
                                    <source src="<?php echo htmlspecialchars($media_url); ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                                <div class="mt-3 d-flex gap-2">
                                    <button onclick="downloadAudio()" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i> Download
                                    </button>
                                    <a href="<?php echo htmlspecialchars($media_url); ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i> Open
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Audio/Video not available for this sermon.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Sermon Description -->
                    <div class="mb-5">
                        <h3 class="mb-4">Message Summary</h3>
                        <?php if (!empty($sermon['description'])): ?>
                        <div class="content">
                            <?php echo nl2br(htmlspecialchars($sermon['description'])); ?>
                        </div>
                        <?php elseif (!empty($sermon['notes_text'])): ?>
                        <div class="content">
                            <?php echo nl2br(htmlspecialchars($sermon['notes_text'])); ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No description available for this sermon.</p>
                        <?php endif; ?>
                        
                        <?php if (!empty($sermon['scripture_reference'])): ?>
                        <div class="scripture-highlight mt-4">
                            <h5><i class="fas fa-bible me-2"></i>Scripture Reference</h5>
                            <p class="mb-0"><?php echo htmlspecialchars($sermon['scripture_reference']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sermon Series -->
                    <?php if (!empty($sermon_series)): ?>
                    <div class="mb-5">
                        <h3 class="mb-4">This Sermon Series</h3>
                        <div class="row">
                            <?php foreach ($sermon_series as $index => $series_sermon): ?>
                            <div class="col-md-6 mb-3">
                                <div class="related-sermon">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($series_sermon['title']); ?></h6>
                                        <div class="small text-muted">
                                            <?php echo date('M j, Y', strtotime($series_sermon['sermon_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="sermon-details.php?id=<?php echo $series_sermon['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            Listen
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share & Download -->
                    <div class="border-top pt-4">
                        <h4 class="mb-4">Share & Download</h4>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h5>Share This Sermon</h5>
                                <div class="share-buttons">
                                    <a href="<?php echo $facebook_url; ?>" 
                                       target="_blank" 
                                       class="btn btn-facebook" 
                                       title="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="<?php echo $twitter_url; ?>" 
                                       target="_blank" 
                                       class="btn btn-twitter" 
                                       title="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="<?php echo $whatsapp_url; ?>" 
                                       target="_blank" 
                                       class="btn btn-success" 
                                       title="Share on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <button onclick="copyLink()" 
                                            class="btn btn-secondary" 
                                            title="Copy link">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h5>Download Resources</h5>
                                <div class="row g-2">
                                    <?php if (!empty($sermon['notes_url'])): ?>
                                    <div class="col-6">
                                        <div class="download-card text-center">
                                            <i class="fas fa-file-pdf fa-2x text-danger mb-3"></i>
                                            <h6>Sermon Notes</h6>
                                            <a href="<?php echo htmlspecialchars($sermon['notes_url']); ?>" 
                                               class="btn btn-sm btn-outline-danger w-100">
                                                Download PDF
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($sermon['slides_url'])): ?>
                                    <div class="col-6">
                                        <div class="download-card text-center">
                                            <i class="fas fa-file-powerpoint fa-2x text-warning mb-3"></i>
                                            <h6>Presentation</h6>
                                            <a href="<?php echo htmlspecialchars($sermon['slides_url']); ?>" 
                                               class="btn btn-sm btn-outline-warning w-100">
                                                Download PPT
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Preacher Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">About the Preacher</h5>
                            <div class="text-center">
                                <div class="avatar-initials-lg mb-3 mx-auto">
                                    <?php 
                                    $preacher_initials = 'CF';
                                    if ($preacher && isset($preacher['full_name'])) {
                                        $words = explode(' ', $preacher['full_name']);
                                        $preacher_initials = '';
                                        foreach ($words as $word) {
                                            if (!empty($word)) {
                                                $preacher_initials .= strtoupper(substr($word, 0, 1));
                                            }
                                        }
                                        $preacher_initials = substr($preacher_initials, 0, 2);
                                    }
                                    echo $preacher_initials;
                                    ?>
                                </div>
                                <h5><?php echo htmlspecialchars($preacher['full_name'] ?? 'CFCI Ministry'); ?></h5>
                                <?php if ($preacher && isset($preacher['bio'])): ?>
                                <p class="small text-muted mb-3"><?php echo htmlspecialchars($preacher['bio']); ?></p>
                                <?php endif; ?>
                                <a href="sermons.php?preacher=<?php echo $sermon['preacher_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    View All Messages
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related Sermons -->
                    <?php if (!empty($related_sermons)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Related Sermons</h5>
                            <div class="related-sermons">
                                <?php foreach ($related_sermons as $related): ?>
                                <div class="mb-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <img src="<?php echo htmlspecialchars($related['thumbnail_url'] ?: 'assets/images/sermons/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($related['title']); ?>"
                                             class="rounded" width="60" height="60" style="object-fit: cover;">
                                        <div>
                                            <h6 class="mb-1"><?php echo substr(htmlspecialchars($related['title']), 0, 50); ?><?php echo strlen($related['title']) > 50 ? '...' : ''; ?></h6>
                                            <div class="small text-muted mb-1">
                                                <?php echo date('M j', strtotime($related['sermon_date'])); ?>
                                            </div>
                                            <a href="sermon-details.php?id=<?php echo $related['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Listen
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Sermon Notes Form -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Take Notes</h5>
                            <form id="sermonNotesForm">
                                <input type="hidden" name="sermon_id" value="<?php echo $sermon_id; ?>">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Your Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4" 
                                              placeholder="Write your notes from this sermon..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="application" class="form-label">Personal Application</label>
                                    <textarea class="form-control" id="application" name="application" rows="3" 
                                              placeholder="How will you apply this message?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Save Notes
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter -->
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">New Sermon Alerts</h5>
                            <p class="card-text small">Get notified when new sermons are published.</p>
                            <form id="newsletterForm">
                                <div class="mb-3">
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="Your email address" required>
                                </div>
                                <button type="submit" class="btn btn-light w-100">
                                    <i class="fas fa-bell me-1"></i> Subscribe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Simple Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>CFCI Church</h5>
                    <p>Christian Family Centre International is a church dedicated to transforming lives through faith, fellowship, and service.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Christian Family Centre International. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Audio download
        window.downloadAudio = function() {
            const audio = document.getElementById('sermonAudio');
            if (!audio || !audio.src) {
                alert('Audio file not available for download.');
                return;
            }
            
            const link = document.createElement('a');
            link.href = audio.src;
            link.download = 'sermon-audio.mp3';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
        
        // Copy link to clipboard
        window.copyLink = function() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert('Failed to copy link. Please copy manually.');
            });
        };
        
        // Sermon notes form
        const notesForm = document.getElementById('sermonNotesForm');
        if (notesForm) {
            notesForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
                button.disabled = true;
                
                try {
                    const response = await fetch('ajax/save-notes.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Notes saved successfully!');
                        this.reset();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save notes'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                } finally {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            });
        }
        
        // Newsletter form
        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const button = this.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subscribing...';
                button.disabled = true;
                
                try {
                    const response = await fetch('ajax/subscribe.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Thank you for subscribing to sermon alerts!');
                        this.reset();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to subscribe'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                } finally {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            });
        }
        
        // Initialize audio player with progress tracking
        const audioPlayer = document.getElementById('sermonAudio');
        if (audioPlayer) {
            let hasPlayed = false;
            
            audioPlayer.addEventListener('play', function() {
                if (!hasPlayed) {
                    // Track play event
                    fetch('ajax/track-play.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            sermon_id: <?php echo $sermon_id; ?>,
                            action: 'play'
                        })
                    });
                    hasPlayed = true;
                }
            });
        }
    });
    </script>
</body>
</html>
<?php $conn = null; ?>