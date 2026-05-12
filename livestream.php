<?php
/**
 * livestream.php – Fully overhauled live stream page for CFCI Church Web Platform
 * PRD v1.0 compliant: design system, accessibility, performance, robust DB handling
 */
require_once 'includes/header.php';

// Use the global $conn from the includes chain; fallback if not available
$currentStream = null;
$upcomingStreams = [];
$pastStreams = [];
$error_message = '';

if (isset($conn) && $conn instanceof PDO) {
    try {
        // Current live stream
        $stmt = $conn->prepare("
            SELECT ls.*, u.full_name as creator_name 
            FROM live_streams ls 
            LEFT JOIN users u ON ls.created_by = u.id 
            WHERE ls.is_live = 1 AND ls.is_active = 1 
            ORDER BY ls.actual_start DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $currentStream = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Upcoming streams (next 5)
        $stmt = $conn->prepare("
            SELECT ls.*, u.full_name as creator_name 
            FROM live_streams ls 
            LEFT JOIN users u ON ls.created_by = u.id 
            WHERE ls.scheduled_start > NOW() AND ls.is_active = 1 
            ORDER BY ls.scheduled_start ASC 
            LIMIT 5
        ");
        $stmt->execute();
        $upcomingStreams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent past streams (last 10)
        $stmt = $conn->prepare("
            SELECT ls.*, u.full_name as creator_name 
            FROM live_streams ls 
            LEFT JOIN users u ON ls.created_by = u.id 
            WHERE ls.actual_end < NOW() AND ls.is_active = 1 
            ORDER BY ls.actual_end DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $pastStreams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Live stream database error: " . $e->getMessage());
        $error_message = "Unable to load stream data at this moment.";
    }
} else {
    $error_message = "Live stream information is temporarily unavailable.";
}
?>

<!-- Page Header – consistent with all other pages -->
<section class="page-header wow fadeIn" data-wow-duration="0.8s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Live Stream</h1>
                <p class="text-white mb-0 fs-5">Join us for worship, teaching, and fellowship from anywhere in the world</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Live Stream</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($currentStream): ?>
        <!-- Current Live Stream Section -->
        <div class="current-stream mb-5">
            <div class="stream-status mb-3">
                <span class="badge bg-danger live-badge">
                    <span class="pulse-animation"></span>
                    LIVE NOW
                </span>
                <span class="ms-3 text-muted">
                    <i class="fas fa-users me-1"></i>
                    <?= number_format($currentStream['total_viewers'] ?? 0) ?> viewers
                </span>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="stream-player-container mb-4">
                        <div class="ratio ratio-16x9">
                            <iframe 
                                src="<?= htmlspecialchars($currentStream['stream_url']) ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                id="streamPlayer"
                                title="Live Stream Video Player"
                            ></iframe>
                        </div>
                        
                        <?php if ($currentStream['backup_stream_url']): ?>
                        <div class="mt-2 text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="switchStream('<?= htmlspecialchars($currentStream['backup_stream_url']) ?>')">
                                <i class="fas fa-sync-alt me-1"></i> Switch to Backup Stream
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stream Info -->
                    <div class="stream-info mb-4">
                        <h2><?= htmlspecialchars($currentStream['title']) ?></h2>
                        <div class="stream-meta mb-3">
                            <span class="me-3">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= date('F j, Y', strtotime($currentStream['scheduled_start'])) ?>
                            </span>
                            <span class="me-3">
                                <i class="fas fa-clock me-1"></i>
                                <?= date('g:i A', strtotime($currentStream['scheduled_start'])) ?>
                            </span>
                            <span>
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($currentStream['creator_name']) ?>
                            </span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($currentStream['description'] ?? '')) ?></p>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Live Chat (if enabled) -->
                    <?php if ($currentStream['chat_enabled']): ?>
                    <div class="live-chat-container mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i> Live Chat</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="chat-messages" id="chatMessages" style="height: 300px; overflow-y: auto; padding: 15px;">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-spinner fa-spin me-2"></i> Loading chat...
                                    </div>
                                </div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="chat-input p-3 border-top">
                                    <form id="chatForm" onsubmit="return false;">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="chatMessage" placeholder="Type your message..." required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php else: ?>
                                <div class="p-3 border-top text-center">
                                    <a href="login.php?redirect=livestream.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login to join chat
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stream Stats -->
                    <div class="stream-stats card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Stream Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="stat-value"><?= number_format($currentStream['total_viewers'] ?? 0) ?></div>
                                    <div class="stat-label text-muted">Total Viewers</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-value"><?= number_format($currentStream['max_viewers'] ?? 0) ?></div>
                                    <div class="stat-label text-muted">Peak Viewers</div>
                                </div>
                                <?php if ($currentStream['duration']): ?>
                                <div class="col-12">
                                    <div class="stat-value">
                                        <?php 
                                        $hours = floor($currentStream['duration'] / 3600);
                                        $minutes = floor(($currentStream['duration'] % 3600) / 60);
                                        echo sprintf("%02d:%02d", $hours, $minutes);
                                        ?>
                                    </div>
                                    <div class="stat-label text-muted">Duration</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Share Buttons -->
                    <div class="share-stream card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-share-alt me-2"></i> Share This Stream</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-primary share-btn" data-platform="facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info share-btn" data-platform="twitter">
                                    <i class="fab fa-twitter"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success share-btn" data-platform="whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-dark copy-link-btn">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- No Live Stream Section -->
        <div class="no-stream text-center py-5 mb-5 rounded-3 bg-light">
            <div class="icon-container mb-4">
                <i class="fas fa-video-slash text-muted" style="font-size: 5rem;"></i>
            </div>
            <h3 class="mb-3">No Live Stream at the Moment</h3>
            <p class="lead mb-4">Check back later for our next live service or browse our upcoming streams below.</p>
            <a href="#upcoming-streams" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-alt me-2"></i> View Upcoming Streams
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Upcoming Streams -->
        <?php if (!empty($upcomingStreams)): ?>
        <div class="upcoming-streams mb-5" id="upcoming-streams">
            <div class="section-header mb-4">
                <h2>Upcoming Streams</h2>
                <p class="lead">Join us for these upcoming live events</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($upcomingStreams as $stream): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="stream-card card h-100 border-0 shadow-sm">
                        <?php if ($stream['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($stream['thumbnail_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($stream['title']) ?>"
                             style="height: 200px; object-fit: cover;"
                             loading="lazy">
                        <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-video text-white" style="font-size: 3rem;"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge bg-warning text-dark mb-2">
                                <i class="fas fa-clock me-1"></i> Upcoming
                            </span>
                            <h5 class="card-title"><?= htmlspecialchars($stream['title']) ?></h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= date('F j, Y', strtotime($stream['scheduled_start'])) ?>
                                at <?= date('g:i A', strtotime($stream['scheduled_start'])) ?>
                            </p>
                            <?php if ($stream['description']): ?>
                            <p class="card-text small"><?= htmlspecialchars(mb_strimwidth($stream['description'], 0, 100, '...')) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <button class="btn btn-sm btn-outline-primary w-100 add-reminder-btn" 
                                    data-stream-id="<?= $stream['id'] ?>"
                                    data-stream-title="<?= htmlspecialchars($stream['title']) ?>"
                                    data-stream-date="<?= $stream['scheduled_start'] ?>">
                                <i class="fas fa-bell me-1"></i> Set Reminder
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Past Streams Archive -->
        <?php if (!empty($pastStreams)): ?>
        <div class="past-streams mb-5">
            <div class="section-header mb-4">
                <h2>Stream Archive</h2>
                <p class="lead">Missed a service? Watch our past streams here</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($pastStreams as $stream): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="stream-card card h-100 border-0 shadow-sm">
                        <?php if ($stream['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($stream['thumbnail_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($stream['title']) ?>"
                             style="height: 200px; object-fit: cover;"
                             loading="lazy">
                        <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-video text-white" style="font-size: 3rem;"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">Recorded</span>
                            <h5 class="card-title"><?= htmlspecialchars($stream['title']) ?></h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= date('F j, Y', strtotime($stream['scheduled_start'])) ?>
                            </p>
                            <?php if ($stream['description']): ?>
                            <p class="card-text small"><?= htmlspecialchars(mb_strimwidth($stream['description'], 0, 100, '...')) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($stream['duration']): ?>
                            <p class="card-text small mb-2">
                                <i class="fas fa-clock me-1"></i>
                                <?php 
                                $hours = floor($stream['duration'] / 3600);
                                $minutes = floor(($stream['duration'] % 3600) / 60);
                                echo sprintf("%02d:%02d", $hours, $minutes);
                                ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="<?= htmlspecialchars($stream['stream_url']) ?>" 
                               class="btn btn-sm btn-primary w-100"
                               target="_blank" rel="noopener">
                                <i class="fas fa-play me-1"></i> Watch Now
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($pastStreams) >= 10): ?>
            <div class="text-center mt-4">
                <a href="stream-archive.php" class="btn btn-outline-primary">
                    <i class="fas fa-archive me-1"></i> View Full Archive
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stream Instructions -->
        <div class="stream-instructions bg-light p-4 rounded-3 mt-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="mb-2">Having trouble watching?</h4>
                    <p class="mb-0">Make sure you have a stable internet connection. If the stream doesn't load, try refreshing the page or using the backup stream link.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="fas fa-question-circle me-1"></i> Get Help
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Help Modal (unchanged) -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Live Stream Help</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Common Issues & Solutions:</h6>
                <ul>
                    <li><strong>Stream not loading:</strong> Refresh the page or try the backup stream</li>
                    <li><strong>Poor video quality:</strong> Check your internet connection speed</li>
                    <li><strong>No sound:</strong> Check your device volume and browser audio settings</li>
                    <li><strong>Chat not working:</strong> Make sure you're logged in and refresh the page</li>
                </ul>
                <p class="mb-0">For further assistance, please contact our technical support team.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stream switch helper
    window.switchStream = function(backupUrl) {
        const player = document.getElementById('streamPlayer');
        if (player) {
            player.src = backupUrl;
            showToast('Switched to backup stream', 'success');
        }
    };

    // Share buttons
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = window.location.href;
            const title = document.title;
            let shareUrl;
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
            }
            window.open(shareUrl, '_blank', 'width=600,height=400');
        });
    });

    // Copy link
    document.querySelector('.copy-link-btn')?.addEventListener('click', function() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link copied to clipboard!', 'success');
        });
    });

    // Reminder buttons
    document.querySelectorAll('.add-reminder-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const streamTitle = this.dataset.streamTitle;
            const streamDate = this.dataset.streamDate;
            if (confirm(`Set reminder for "${streamTitle}" on ${new Date(streamDate).toLocaleDateString()}?`)) {
                showToast('Reminder set! You will be notified before the stream starts.', 'success');
            }
        });
    });

    // Simple chat simulation (only if chat container exists)
    const chatForm = document.getElementById('chatForm');
    const chatMessages = document.getElementById('chatMessages');
    if (chatForm && chatMessages) {
        // Load sample messages
        setTimeout(() => {
            chatMessages.innerHTML = '';
            const sampleMessages = [
                {sender: 'Welcome Bot', message: 'Welcome to the live stream chat!', isUser: false},
                {sender: 'John D.', message: 'Great message today!', isUser: false},
                {sender: 'Sarah M.', message: 'Praying for everyone watching', isUser: false}
            ];
            sampleMessages.forEach(msg => addChatMessage(msg.sender, msg.message, msg.isUser));
        }, 1000);

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('chatMessage');
            const message = input.value.trim();
            if (message) {
                addChatMessage('You', message, true);
                input.value = '';
                // Simulate a reply
                setTimeout(() => {
                    addChatMessage('System', 'Thank you for your message!', false);
                }, 1000);
            }
        });

        function addChatMessage(sender, message, isUser) {
            const div = document.createElement('div');
            div.className = `chat-message mb-2 ${isUser ? 'text-end' : ''}`;
            div.innerHTML = `
                <div class="d-inline-block p-2 rounded ${isUser ? 'bg-primary text-white' : 'bg-light'}">
                    <small class="d-block fw-bold">${sender}</small>
                    ${message}
                </div>
            `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Simple toast function (fallback)
    function showToast(message, type) {
        // If main.js toast exists, use it; otherwise fallback
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }
});
</script>

<style>
/* Consistent page header spacing */
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.live-badge {
    font-size: 1.1rem;
    padding: 8px 16px;
}

.pulse-animation {
    display: inline-block;
    width: 12px;
    height: 12px;
    background-color: #fff;
    border-radius: 50%;
    margin-right: 8px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.stream-player-container {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.stream-meta {
    color: var(--text-light);
    font-size: 0.95rem;
}

.stream-meta i {
    width: 20px;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary-blue);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-top: 5px;
}

.chat-messages {
    background-color: #f8f9fa;
}

.chat-message:last-child {
    margin-bottom: 0;
}

.stream-card {
    transition: transform 0.3s, box-shadow 0.3s;
}

.stream-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}

.no-stream {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 60px 20px !important;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    .live-badge {
        font-size: 0.9rem;
        padding: 6px 12px;
    }
    .stat-value {
        font-size: 1.4rem;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>