<?php
require_once 'includes/header.php';

// Check if event ID is provided
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;
$attendees = [];
$user_registered = false;
$total_registered = 0;

try {
    // Fetch event details
    $stmt = $conn->prepare("
        SELECT e.*, u.full_name as created_by_name 
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.id = ? AND e.is_active = 1
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        // Check if user is registered
        if (isset($_SESSION['user_id'])) {
            $check_stmt = $conn->prepare("
                SELECT id, status, guests 
                FROM event_attendance 
                WHERE event_id = ? AND user_id = ?
            ");
            $check_stmt->execute([$event_id, $_SESSION['user_id']]);
            $user_registered = $check_stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Get total registered attendees
        $count_stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM event_attendance 
            WHERE event_id = ? AND status != 'cancelled'
        ");
        $count_stmt->execute([$event_id]);
        $total_registered = $count_stmt->fetch(PDO::FETCH_COLUMN);
        
        // Get recent attendees (for display)
        $attendees_stmt = $conn->prepare("
            SELECT ea.*, u.full_name, u.email 
            FROM event_attendance ea 
            JOIN users u ON ea.user_id = u.id 
            WHERE ea.event_id = ? AND ea.status != 'cancelled' 
            ORDER BY ea.attended_at DESC 
            LIMIT 10
        ");
        $attendees_stmt->execute([$event_id]);
        $attendees = $attendees_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Event details fetch error: " . $e->getMessage());
}

// If event not found, redirect to events page
if (!$event && $event_id > 0) {
    header('Location: events.php');
    exit();
}

// Default event if database is empty
if (!$event && $event_id == 0) {
    $event = [
        'id' => 1,
        'title' => 'Annual Family Fun Day',
        'event_date' => '2025-09-15',
        'start_time' => '09:00:00',
        'end_time' => '16:00:00',
        'location' => 'Prince of Wales Stadium',
        'address' => 'Prince of Wales Park, Manzini, Eswatini',
        'description' => '<p>Join us for our much-anticipated Annual Family Fun Day! This is a day packed with activities, food, and fellowship for the entire family.</p>
        
        <h3>Event Highlights</h3>
        <ul>
            <li>Live worship and praise sessions</li>
            <li>Children\'s games and inflatable playgrounds</li>
            <li>Sports competitions for all ages</li>
            <li>Delicious barbecue and refreshments</li>
            <li>Family photo booth</li>
            <li>Gospel message and prayer station</li>
        </ul>
        
        <h3>What to Bring</h3>
        <p>Please bring:</p>
        <ul>
            <li>Sun protection (hats, sunscreen)</li>
            <li>Comfortable clothing and shoes</li>
            <li>Swimming gear (for water games)</li>
            <li>A joyful spirit!</li>
        </ul>
        
        <h3>Tickets & Registration</h3>
        <p>Entry is free for all church members. Non-members are welcome with a small donation of E50 per family. Please register in advance to help us plan for food and activities.</p>',
        'max_attendees' => 200,
        'registration_deadline' => '2025-09-10',
        'image_url' => 'https://via.placeholder.com/800x400',
        'created_by_name' => 'Bishop Zakes Nxumalo',
        'is_recurring' => 0,
        'cost' => 'Free for members, E50 per family for guests'
    ];
    
    $total_registered = 125;
    $attendees = [
        ['full_name' => 'John Dlamini', 'status' => 'registered', 'guests' => 3],
        ['full_name' => 'Sarah Nkosi', 'status' => 'registered', 'guests' => 2],
        ['full_name' => 'Thomas Mbeki', 'status' => 'attended', 'guests' => 4]
    ];
}

// Format dates and times
$event_date = new DateTime($event['event_date']);
$formatted_date = $event_date->format('l, F j, Y');
$start_time = date('g:i A', strtotime($event['start_time']));
$end_time = $event['end_time'] ? date('g:i A', strtotime($event['end_time'])) : '';
$time_range = $end_time ? $start_time . ' - ' . $end_time : $start_time;

// Check if registration is closed
$registration_closed = false;
if ($event['registration_deadline']) {
    $deadline = new DateTime($event['registration_deadline']);
    $today = new DateTime();
    if ($today > $deadline) {
        $registration_closed = true;
    }
}

// Check if event is full
$is_full = $event['max_attendees'] && $total_registered >= $event['max_attendees'];
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Event Details</h1>
                <p class="text-white mb-0">Get all the information about this exciting event</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="events.php" class="text-white">Events</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Event Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="event-details-card">
                    <!-- Event Header -->
                    <div class="event-header mb-5">
                        <?php if ($is_full): ?>
                            <span class="badge bg-danger float-end">Event Full</span>
                        <?php elseif ($registration_closed): ?>
                            <span class="badge bg-warning float-end">Registration Closed</span>
                        <?php endif; ?>
                        
                        <div class="event-date-badge mb-4">
                            <div class="date-badge">
                                <span class="month"><?php echo $event_date->format('M'); ?></span>
                                <span class="day"><?php echo $event_date->format('d'); ?></span>
                                <span class="year"><?php echo $event_date->format('Y'); ?></span>
                            </div>
                            <div class="date-text ms-4">
                                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                                <div class="event-meta">
                                    <span><i class="far fa-calendar-alt me-2"></i><?php echo $formatted_date; ?></span>
                                    <span class="mx-3">•</span>
                                    <span><i class="far fa-clock me-2"></i><?php echo $time_range; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Image -->
                        <div class="event-image mb-4">
                            <img src="<?php echo htmlspecialchars($event['image_url'] ?: 'https://via.placeholder.com/800x400'); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                 class="img-fluid rounded">
                        </div>
                    </div>
                    
                    <!-- Event Details -->
                    <div class="event-body mb-5">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <h5><i class="fas fa-map-marker-alt text-primary me-2"></i>Location</h5>
                                    <p class="mb-1"><?php echo htmlspecialchars($event['location']); ?></p>
                                    <?php if (!empty($event['address'])): ?>
                                        <p class="text-muted small"><?php echo htmlspecialchars($event['address']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item mb-3">
                                    <h5><i class="fas fa-users text-primary me-2"></i>Attendance</h5>
                                    <p><?php echo number_format($total_registered); ?> registered</p>
                                    <?php if ($event['max_attendees']): ?>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min(100, ($total_registered / $event['max_attendees']) * 100); ?>%"
                                                 aria-valuenow="<?php echo $total_registered; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="<?php echo $event['max_attendees']; ?>">
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo $total_registered; ?> of <?php echo $event['max_attendees']; ?> spots filled</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($event['cost']): ?>
                            <div class="detail-item mb-4">
                                <h5><i class="fas fa-money-bill-wave text-primary me-2"></i>Cost</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($event['cost']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-description">
                            <h4 class="mb-3">About This Event</h4>
                            <?php echo $event['description']; ?>
                        </div>
                        
                        <?php if ($event['registration_deadline']): ?>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Registration deadline: <?php echo date('F j, Y', strtotime($event['registration_deadline'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Registration Section -->
                    <div class="registration-section mt-5 pt-4 border-top">
                        <h3 class="mb-4">Registration</h3>
                        
                        <?php if ($is_full): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This event has reached maximum capacity. Please check back for cancellations or join the waitlist.
                            </div>
                        <?php elseif ($registration_closed): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Registration for this event has closed. Please contact us if you have special circumstances.
                            </div>
                        <?php elseif ($user_registered): ?>
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>You are registered for this event!</strong>
                                        <div class="small mt-1">
                                            Status: <?php echo ucfirst($user_registered['status']); ?>
                                            <?php if ($user_registered['guests'] > 0): ?>
                                                • Bringing <?php echo $user_registered['guests']; ?> guest(s)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelRegistration()">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="registration-form">
                                    <form id="registrationForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="guests" class="form-label">Number of Guests</label>
                                                <input type="number" class="form-control" id="guests" name="guests" min="0" max="5" value="0">
                                                <div class="form-text">Maximum 5 guests allowed per registration</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="special_needs" class="form-label">Special Requirements</label>
                                                <textarea class="form-control" id="special_needs" name="special_needs" rows="2" placeholder="Dietary restrictions, accessibility needs, etc."></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-user-plus me-2"></i> Register for Event
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please <a href="auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">login</a> or <a href="auth/register.php" class="alert-link">create an account</a> to register for this event.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="event-sidebar">
                    <!-- Quick Info -->
                    <div class="sidebar-widget mb-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Event Quick Info</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <strong><i class="far fa-calendar-alt me-2 text-primary"></i>Date:</strong><br>
                                        <?php echo $formatted_date; ?>
                                    </li>
                                    <li class="mb-3">
                                        <strong><i class="far fa-clock me-2 text-primary"></i>Time:</strong><br>
                                        <?php echo $time_range; ?>
                                    </li>
                                    <li class="mb-3">
                                        <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Venue:</strong><br>
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </li>
                                    <?php if ($event['cost']): ?>
                                        <li class="mb-3">
                                            <strong><i class="fas fa-money-bill-wave me-2 text-primary"></i>Cost:</strong><br>
                                            <?php echo htmlspecialchars($event['cost']); ?>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($event['max_attendees']): ?>
                                        <li class="mb-3">
                                            <strong><i class="fas fa-users me-2 text-primary"></i>Capacity:</strong><br>
                                            <?php echo number_format($event['max_attendees']); ?> people
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                
                                <div class="d-grid gap-2">
                                    <?php if (!$is_full && !$registration_closed && !$user_registered && isset($_SESSION['user_id'])): ?>
                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('registrationForm').scrollIntoView()">
                                            <i class="fas fa-user-plus me-2"></i> Register Now
                                        </button>
                                    <?php endif; ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($event['location']); ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-directions me-2"></i> Get Directions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendees -->
                    <?php if (!empty($attendees)): ?>
                        <div class="sidebar-widget mb-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Recently Registered</h5>
                                    <div class="attendees-list">
                                        <?php foreach ($attendees as $attendee): ?>
                                            <div class="attendee d-flex align-items-center mb-2">
                                                <div class="attendee-avatar me-2">
                                                    <div class="avatar-initials-sm">
                                                        <?php echo strtoupper(substr($attendee['full_name'], 0, 2)); ?>
                                                    </div>
                                                </div>
                                                <div class="attendee-info">
                                                    <div class="attendee-name small fw-bold"><?php echo htmlspecialchars($attendee['full_name']); ?></div>
                                                    <?php if ($attendee['guests'] > 0): ?>
                                                        <div class="attendee-guests small text-muted">
                                                            +<?php echo $attendee['guests']; ?> guest(s)
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($total_registered > count($attendees)): ?>
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                +<?php echo $total_registered - count($attendees); ?> more registered
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Share Event -->
                    <div class="sidebar-widget mb-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Share This Event</h5>
                                <div class="share-buttons d-flex gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($event['title']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($event['title'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-success">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <button onclick="copyEventLink()" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Organizer -->
                    <div class="sidebar-widget">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Questions?</h5>
                                <p class="card-text small mb-3">Contact the event organizer for more information.</p>
                                <div class="organizer-info">
                                    <div class="organizer-avatar mb-2">
                                        <div class="avatar-initials-md">
                                            <?php echo strtoupper(substr($event['created_by_name'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div class="organizer-name fw-bold"><?php echo htmlspecialchars($event['created_by_name']); ?></div>
                                    <div class="organizer-role small text-muted">Event Organizer</div>
                                </div>
                                <a href="contact.php" class="btn btn-outline-primary btn-sm mt-3 w-100">
                                    <i class="fas fa-envelope me-1"></i> Contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Events -->
        <?php if ($event['is_recurring'] || !empty($event['category'])): ?>
            <div class="related-events mt-5 pt-5 border-top">
                <h3 class="mb-4">You Might Also Like</h3>
                <div class="row">
                    <!-- Example related events -->
                    <div class="col-md-4 mb-4">
                        <div class="event-card-sm">
                            <div class="event-date-sm">
                                <span class="day">22</span>
                                <span class="month">SEP</span>
                            </div>
                            <div class="event-content-sm">
                                <h5 class="mb-2">Marriage Enrichment Seminar</h5>
                                <p class="small text-muted mb-2">Strengthen your marriage with biblical principles</p>
                                <a href="event-details.php?id=2" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="event-card-sm">
                            <div class="event-date-sm">
                                <span class="day">05</span>
                                <span class="month">OCT</span>
                            </div>
                            <div class="event-content-sm">
                                <h5 class="mb-2">Women's Prayer Breakfast</h5>
                                <p class="small text-muted mb-2">Uplifting time of prayer and fellowship</p>
                                <a href="event-details.php?id=3" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="event-card-sm">
                            <div class="event-date-sm">
                                <span class="day">24</span>
                                <span class="month">NOV</span>
                            </div>
                            <div class="event-content-sm">
                                <h5 class="mb-2">Thanksgiving Service</h5>
                                <p class="small text-muted mb-2">Give thanks for God's blessings</p>
                                <a href="event-details.php?id=4" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.event-details-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.event-date-badge {
    display: flex;
    align-items: center;
}

.date-badge {
    width: 80px;
    height: 80px;
    background: var(--primary);
    color: white;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.date-badge .month {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.date-badge .day {
    font-size: 1.8rem;
    font-weight: bold;
    line-height: 1;
}

.date-badge .year {
    font-size: 0.8rem;
    opacity: 0.9;
}

.event-meta {
    color: var(--text-light);
    font-size: 1.1rem;
}

.event-image img {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
}

.detail-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.progress {
    background-color: #e9ecef;
}

.avatar-initials-sm {
    width: 35px;
    height: 35px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.avatar-initials-md {
    width: 60px;
    height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
    margin: 0 auto;
}

.attendees-list {
    max-height: 300px;
    overflow-y: auto;
}

.attendee {
    padding: 8px;
    border-radius: 6px;
    transition: var(--transition);
}

.attendee:hover {
    background: #f8f9fa;
}

.share-buttons .btn-facebook {
    background: #3b5998;
    color: white;
    border-color: #3b5998;
}

.share-buttons .btn-twitter {
    background: #1da1f2;
    color: white;
    border-color: #1da1f2;
}

.share-buttons .btn-success {
    background: #25d366;
    color: white;
    border-color: #25d366;
}

.event-card-sm {
    display: flex;
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    transition: var(--transition);
}

.event-card-sm:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.event-date-sm {
    background: var(--primary);
    color: white;
    width: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.event-date-sm .day {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.event-date-sm .month {
    font-size: 0.8rem;
    text-transform: uppercase;
}

.event-content-sm {
    flex: 1;
    padding: 15px;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .event-details-card {
        padding: 20px;
    }
    
    .event-date-badge {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .date-badge {
        margin-bottom: 15px;
    }
    
    .event-meta {
        font-size: 1rem;
    }
    
    .share-buttons .btn {
        font-size: 0.8rem;
        padding: 5px 10px;
    }
}
</style>

<script>
function cancelRegistration() {
    if (confirm('Are you sure you want to cancel your registration for this event?')) {
        // Simulate cancellation
        alert('Registration cancelled successfully.');
        location.reload();
    }
}

function copyEventLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Event link copied to clipboard!');
    });
}

// Registration form submission
document.getElementById('registrationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        guests: parseInt(this.guests.value),
        special_needs: this.special_needs.value
    };
    
    // Validate guests
    if (formData.guests > 5) {
        alert('Maximum 5 guests allowed per registration.');
        return;
    }
    
    // Simulate registration
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
    button.disabled = true;
    
    setTimeout(() => {
        alert('Successfully registered for the event!');
        location.reload();
    }, 1500);
});
</script>

<?php
require_once 'includes/footer.php';
?>