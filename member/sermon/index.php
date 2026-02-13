<?php
// member/sermons.php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a member
$session->requireLogin();
if ($session->getUserRole() !== 'member') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $session->getUserId();
$db = new ChurchDB($conn);

// Handle filters and search
$category_filter = $_GET['category'] ?? 'all';
$preacher_filter = $_GET['preacher'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';
$search_term = $_GET['search'] ?? '';

// Get sermons data with filters
try {
    $sermons = $db->getFilteredSermons($category_filter, $preacher_filter, $year_filter, $search_term);
    $sermon_categories = $db->getSermonCategories();
    $preachers = $db->getSermonPreachers();
    $sermon_years = $db->getSermonYears();
    $recent_sermons = $db->getRecentSermons(5);
} catch (Exception $e) {
    error_log("Sermons page error: " . $e->getMessage());
    $sermons = [];
    $sermon_categories = [];
    $preachers = [];
    $sermon_years = [];
    $recent_sermons = [];
    $session->setFlash('error', 'Unable to load sermons. Please try again.');
}

// Handle sermon playback tracking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'track_play') {
        $sermon_id = $_POST['sermon_id'] ?? '';
        $media_type = $_POST['media_type'] ?? '';
        
        if (!empty($sermon_id) && !empty($media_type)) {
            $db->trackSermonPlay($sermon_id, $user_id, $media_type);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sermons - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2c7be5;
            --accent-blue: #1c65c9;
            --light-blue: #e6f0ff;
            --accent-green: #00d97e;
            --light-green: #e6fff2;
            --accent-orange: #f6c343;
            --light-orange: #fff9e6;
            --accent-purple: #9b59b6;
            --light-purple: #f5eef8;
            --dark-text: #2d3748;
            --light-text: #718096;
            --light-gray: #f8f9fa;
            --border-color: #e2e8f0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Main Content Layout */
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .page-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        /* Sermons Layout */
        .sermons-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        @media (max-width: 1200px) {
            .sermons-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Sidebar */
        .sidebar {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 30px;
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-section h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--dark-text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-section h3 i {
            color: var(--primary-blue);
        }

        /* Filter Form */
        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            color: var(--dark-text);
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-text);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-block {
            display: block;
            width: 100%;
            justify-content: center;
        }

        /* Recent Sermons List */
        .recent-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .recent-item {
            display: flex;
            align-items: start;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            background: var(--light-gray);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .recent-item:hover {
            background: var(--light-blue);
            transform: translateX(5px);
        }

        .recent-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .recent-content h4 {
            font-size: 0.9rem;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .recent-content .date {
            font-size: 0.8rem;
            color: var(--light-text);
        }

        /* Main Content */
        .sermons-main {
            min-height: 500px;
        }

        /* Sermons Grid */
        .sermons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .sermons-grid {
                grid-template-columns: 1fr;
            }
        }

        .sermon-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .sermon-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .sermon-image {
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .sermon-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
        }

        .sermon-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            color: var(--dark-text);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .sermon-content {
            padding: 20px;
        }

        .sermon-content h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--dark-text);
            line-height: 1.3;
        }

        .sermon-passage {
            color: var(--primary-blue);
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .sermon-meta {
            display: flex;
            justify-content: between;
            font-size: 0.85rem;
            color: var(--light-text);
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 5px;
        }

        .sermon-preacher {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sermon-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .sermon-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
        }

        .sermon-btn.primary {
            background: var(--primary-blue);
            color: white;
        }

        .sermon-btn.secondary {
            background: var(--light-gray);
            color: var(--dark-text);
        }

        .sermon-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .sermon-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .sermon-stats {
            display: flex;
            gap: 15px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
            font-size: 0.8rem;
            color: var(--light-text);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--light-text);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--light-text);
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        /* Results Header */
        .results-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .view-toggle {
            display: flex;
            gap: 5px;
            background: white;
            padding: 4px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .view-btn {
            padding: 8px 12px;
            border: none;
            background: none;
            border-radius: 6px;
            cursor: pointer;
            color: var(--light-text);
            transition: all 0.3s ease;
        }

        .view-btn.active {
            background: var(--primary-blue);
            color: white;
        }

        /* Flash Messages */
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }

        .flash-message {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            animation: slideInRight 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: var(--light-green);
            color: #2d5016;
            border-left: 4px solid var(--accent-green);
        }

        .flash-error {
            background: #ffe6e6;
            color: #cc0000;
            border-left: 4px solid #ff4444;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Audio Player */
        .audio-player {
            background: var(--light-gray);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .audio-player audio {
            width: 100%;
            border-radius: 4px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            color: var(--dark-text);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--light-text);
        }

        .modal-body {
            padding: 25px;
            max-height: calc(90vh - 80px);
            overflow-y: auto;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            margin-bottom: 20px;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../includes/member_sidebar.php'; ?>
    
    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php 
        $flash_messages = isset($_SESSION['flash_messages']) ? $_SESSION['flash_messages'] : [];
        foreach ($flash_messages as $key => $flash): 
        ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php 
        endforeach; 
        unset($_SESSION['flash_messages']);
        ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Sermons</h1>
                <p>Watch or listen to messages from our services</p>
            </div>
        </div>

        <div class="sermons-layout">
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Search & Filters -->
                <form method="GET" class="sidebar-section">
                    <h3><i class="fas fa-filter"></i> Filter Sermons</h3>
                    
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   class="search-input" 
                                   placeholder="Search sermons..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="filter-select">
                            <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                            <?php foreach ($sermon_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($category)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="preacher">Preacher</label>
                        <select name="preacher" id="preacher" class="filter-select">
                            <option value="all" <?php echo $preacher_filter === 'all' ? 'selected' : ''; ?>>All Preachers</option>
                            <?php foreach ($preachers as $preacher): ?>
                                <option value="<?php echo htmlspecialchars($preacher['id']); ?>" 
                                    <?php echo $preacher_filter == $preacher['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($preacher['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="year">Year</label>
                        <select name="year" id="year" class="filter-select">
                            <option value="all" <?php echo $year_filter === 'all' ? 'selected' : ''; ?>>All Years</option>
                            <?php foreach ($sermon_years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" 
                                    <?php echo $year_filter == $year ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <?php if ($category_filter !== 'all' || $preacher_filter !== 'all' || $year_filter !== 'all' || !empty($search_term)): ?>
                        <a href="sermons.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Recent Sermons -->
                <div class="sidebar-section">
                    <h3><i class="fas fa-history"></i> Recent Sermons</h3>
                    <div class="recent-list">
                        <?php if (!empty($recent_sermons)): ?>
                            <?php foreach ($recent_sermons as $recent): ?>
                                <a href="sermons.php?search=<?php echo urlencode($recent['title']); ?>" class="recent-item">
                                    <div class="recent-icon">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <div class="recent-content">
                                        <h4><?php echo htmlspecialchars(mb_strimwidth($recent['title'], 0, 50, '...')); ?></h4>
                                        <div class="date"><?php echo date('M j, Y', strtotime($recent['sermon_date'])); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: var(--light-text); padding: 20px;">
                                <i class="fas fa-headphones"></i>
                                <p>No recent sermons</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="sermons-main">
                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-count">
                        <?php echo count($sermons); ?> sermon<?php echo count($sermons) !== 1 ? 's' : ''; ?> found
                        <?php if (!empty($search_term)): ?>
                            for "<?php echo htmlspecialchars($search_term); ?>"
                        <?php endif; ?>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <!-- Sermons Grid -->
                <?php if (!empty($sermons)): ?>
                    <div class="sermons-grid" id="sermonsView">
                        <?php foreach ($sermons as $sermon): ?>
                            <div class="sermon-card">
                                <div class="sermon-image">
                                    <i class="fas fa-bible"></i>
                                    <?php if ($sermon['audio_url'] && $sermon['video_url']): ?>
                                        <span class="sermon-badge">Audio & Video</span>
                                    <?php elseif ($sermon['audio_url']): ?>
                                        <span class="sermon-badge">Audio</span>
                                    <?php elseif ($sermon['video_url']): ?>
                                        <span class="sermon-badge">Video</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="sermon-content">
                                    <h3><?php echo htmlspecialchars($sermon['title']); ?></h3>
                                    
                                    <?php if (!empty($sermon['bible_passage'])): ?>
                                        <div class="sermon-passage">
                                            <i class="fas fa-book-bible"></i>
                                            <?php echo htmlspecialchars($sermon['bible_passage']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="sermon-meta">
                                        <div class="sermon-preacher">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($sermon['preacher_name'] ?? 'Pastor'); ?>
                                        </div>
                                        <div class="sermon-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M j, Y', strtotime($sermon['sermon_date'])); ?>
                                        </div>
                                    </div>

                                    <div class="sermon-actions">
                                        <?php if ($sermon['audio_url']): ?>
                                            <button class="sermon-btn primary" 
                                                    onclick="playSermon(<?php echo $sermon['id']; ?>, '<?php echo htmlspecialchars($sermon['audio_url']); ?>', 'audio', '<?php echo htmlspecialchars($sermon['title']); ?>')">
                                                <i class="fas fa-play"></i> Listen
                                            </button>
                                        <?php else: ?>
                                            <button class="sermon-btn secondary" disabled>
                                                <i class="fas fa-play"></i> No Audio
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($sermon['video_url']): ?>
                                            <button class="sermon-btn secondary" 
                                                    onclick="playSermon(<?php echo $sermon['id']; ?>, '<?php echo htmlspecialchars($sermon['video_url']); ?>', 'video', '<?php echo htmlspecialchars($sermon['title']); ?>')">
                                                <i class="fas fa-play-circle"></i> Watch
                                            </button>
                                        <?php else: ?>
                                            <button class="sermon-btn secondary" disabled>
                                                <i class="fas fa-play-circle"></i> No Video
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($sermon['slides_url'] || $sermon['notes_text']): ?>
                                        <div class="sermon-actions" style="margin-top: 10px; grid-template-columns: 1fr 1fr;">
                                            <?php if ($sermon['slides_url']): ?>
                                                <a href="<?php echo htmlspecialchars($sermon['slides_url']); ?>" 
                                                   class="sermon-btn secondary" 
                                                   target="_blank"
                                                   onclick="trackDownload(<?php echo $sermon['id']; ?>)">
                                                    <i class="fas fa-download"></i> Slides
                                                </a>
                                            <?php else: ?>
                                                <button class="sermon-btn secondary" disabled>
                                                    <i class="fas fa-download"></i> No Slides
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($sermon['notes_text']): ?>
                                                <button class="sermon-btn secondary" 
                                                        onclick="showNotes(<?php echo $sermon['id']; ?>, '<?php echo htmlspecialchars($sermon['title']); ?>', `<?php echo addslashes($sermon['notes_text']); ?>`)">
                                                    <i class="fas fa-file-alt"></i> Notes
                                                </button>
                                            <?php else: ?>
                                                <button class="sermon-btn secondary" disabled>
                                                    <i class="fas fa-file-alt"></i> No Notes
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="sermon-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-play"></i>
                                            <?php echo $sermon['views_count'] ?? 0; ?> plays
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-download"></i>
                                            <?php echo $sermon['downloads_count'] ?? 0; ?> downloads
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-headphones"></i>
                        <h3>No sermons found</h3>
                        <p>Try adjusting your search criteria or browse all sermons</p>
                        <a href="sermons.php" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> View All Sermons
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="videoModalTitle">Sermon Video</h3>
                <button class="close-modal" onclick="closeVideoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <iframe id="videoFrame" src="" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div class="modal" id="notesModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="notesModalTitle">Sermon Notes</h3>
                <button class="close-modal" onclick="closeNotesModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="notesContent"></div>
            </div>
        </div>
    </div>

    <script>
        // Sermon playback function
        function playSermon(sermonId, url, type, title) {
            // Track the play
            trackSermonPlay(sermonId, type);
            
            if (type === 'audio') {
                // Create audio element and play
                const audio = new Audio(url);
                audio.play().catch(e => {
                    alert('Unable to play audio. Please try again.');
                    console.error('Audio play error:', e);
                });
                
                // Show success message
                showTempMessage(`Now playing: ${title}`, 'success');
                
            } else if (type === 'video') {
                // Show video in modal
                document.getElementById('videoModalTitle').textContent = title;
                document.getElementById('videoFrame').src = url;
                document.getElementById('videoModal').classList.add('active');
            }
        }

        // Track sermon play
        function trackSermonPlay(sermonId, mediaType) {
            const formData = new FormData();
            formData.append('action', 'track_play');
            formData.append('sermon_id', sermonId);
            formData.append('media_type', mediaType);
            
            fetch('sermons.php', {
                method: 'POST',
                body: formData
            }).catch(error => {
                console.error('Tracking error:', error);
            });
        }

        // Track download
        function trackDownload(sermonId) {
            // You can implement download tracking here
            console.log('Download tracked for sermon:', sermonId);
        }

        // Show notes
        function showNotes(sermonId, title, notes) {
            document.getElementById('notesModalTitle').textContent = `Notes: ${title}`;
            document.getElementById('notesContent').innerHTML = `<div style="line-height: 1.6; white-space: pre-wrap;">${notes}</div>`;
            document.getElementById('notesModal').classList.add('active');
        }

        // Close modals
        function closeVideoModal() {
            document.getElementById('videoModal').classList.remove('active');
            document.getElementById('videoFrame').src = '';
        }

        function closeNotesModal() {
            document.getElementById('notesModal').classList.remove('active');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeVideoModal();
                closeNotesModal();
            }
        });

        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Update active button
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Update view
                const sermonsView = document.getElementById('sermonsView');
                if (view === 'list') {
                    sermonsView.style.gridTemplateColumns = '1fr';
                    sermonsView.classList.add('list-view');
                } else {
                    sermonsView.style.gridTemplateColumns = 'repeat(auto-fill, minmax(320px, 1fr))';
                    sermonsView.classList.remove('list-view');
                }
            });
        });

        // Auto-hide flash messages
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                msg.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);

        // Show temporary message
        function showTempMessage(message, type) {
            const flashContainer = document.querySelector('.flash-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `flash-message flash-${type}`;
            messageDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            flashContainer.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }

        // Add animation to sermon cards when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        // Observe all sermon cards for animation
        document.querySelectorAll('.sermon-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
