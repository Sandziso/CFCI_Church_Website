<?php
// events.php
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

// Get current date for filtering
$current_date = date('Y-m-d');

// Fetch upcoming events
$upcoming_events = [];
$past_events = [];

if ($conn) {
    try {
        // Fetch upcoming events (event_date >= today)
        $stmt = $conn->prepare("
            SELECT e.*, 
                   (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id AND status != 'cancelled') as registered_count
            FROM events e 
            WHERE e.event_date >= ? AND e.is_active = 1 AND e.is_published = 1
            ORDER BY e.event_date, e.start_time
            LIMIT 6
        ");
        $stmt->execute([$current_date]);
        $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch past events (event_date < today) - last 3 months
        $three_months_ago = date('Y-m-d', strtotime('-3 months'));
        $stmt = $conn->prepare("
            SELECT e.*,
                   (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id AND status = 'attended') as attended_count
            FROM events e 
            WHERE e.event_date < ? AND e.event_date >= ? AND e.is_published = 1
            ORDER BY e.event_date DESC
            LIMIT 4
        ");
        $stmt->execute([$current_date, $three_months_ago]);
        $past_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error fetching events: " . $e->getMessage());
    }
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/events-bg.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Events & Calendar</h1>
                <p class="text-white mb-0">Join us for worship, fellowship, and community events</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Events</li>
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
                <h2 class="mb-4">Upcoming Events</h2>
                <p class="lead">Stay connected with what's happening at CFCI. Join us for worship, fellowship, and community events.</p>
            </div>
        </div>

        <!-- Events Filter Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <ul class="nav nav-tabs justify-content-center" id="eventsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                            <i class="far fa-calendar-alt me-2"></i>Upcoming Events
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">
                            <i class="far fa-calendar-check me-2"></i>Past Events
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                            <i class="fas fa-calendar me-2"></i>Calendar View
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="eventsTabContent">
            <!-- Upcoming Events Tab -->
            <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                <div class="row" id="upcoming-events-container">
                    <?php if (empty($upcoming_events)): ?>
                        <!-- Default events if database is empty -->
                        <div class="col-lg-6 mb-4">
                            <div class="event-card h-100">
                                <div class="event-date">
                                    <span class="event-day">15</span>
                                    <span class="event-month">JUL</span>
                                </div>
                                <div class="event-content">
                                    <h3 class="h4 mb-2">Youth Camp 2024</h3>
                                    <div class="event-meta mb-3">
                                        <span class="me-3"><i class="far fa-clock text-primary"></i> 9:00 AM - 4:00 PM</span>
                                        <span><i class="fas fa-map-marker-alt text-primary"></i> Mbuluzi River Park</span>
                                    </div>
                                    <p class="mb-3">Annual youth camping trip with worship, games, and spiritual growth activities.</p>
                                    <div class="event-footer">
                                        <span class="attendees-count"><i class="fas fa-users"></i> 50+ attending</span>
                                        <a href="event-register.php?id=1" class="btn btn-primary btn-sm">Register</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="event-card h-100">
                                <div class="event-date">
                                    <span class="event-day">22</span>
                                    <span class="event-month">JUL</span>
                                </div>
                                <div class="event-content">
                                    <h3 class="h4 mb-2">Marriage Enrichment Seminar</h3>
                                    <div class="event-meta mb-3">
                                        <span class="me-3"><i class="far fa-clock text-primary"></i> 2:00 PM - 5:00 PM</span>
                                        <span><i class="fas fa-map-marker-alt text-primary"></i> Church Auditorium</span>
                                    </div>
                                    <p class="mb-3">Strengthen your marriage with biblical principles and practical tools.</p>
                                    <div class="event-footer">
                                        <span class="attendees-count"><i class="fas fa-users"></i> 30+ attending</span>
                                        <a href="event-register.php?id=2" class="btn btn-primary btn-sm">Register</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="event-card h-100">
                                <div class="event-date">
                                    <span class="event-day">05</span>
                                    <span class="event-month">AUG</span>
                                </div>
                                <div class="event-content">
                                    <h3 class="h4 mb-2">Back to School Prayer</h3>
                                    <div class="event-meta mb-3">
                                        <span class="me-3"><i class="far fa-clock text-primary"></i> 9:00 AM - 12:00 PM</span>
                                        <span><i class="fas fa-map-marker-alt text-primary"></i> Main Sanctuary</span>
                                    </div>
                                    <p class="mb-3">Special prayer service for students, teachers, and school staff.</p>
                                    <div class="event-footer">
                                        <span class="attendees-count"><i class="fas fa-users"></i> 100+ attending</span>
                                        <a href="event-register.php?id=3" class="btn btn-primary btn-sm">Register</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_events as $event): ?>
                            <?php
                            $event_date = new DateTime($event['event_date']);
                            $day = $event_date->format('d');
                            $month = $event_date->format('M');
                            $is_full = $event['max_attendees'] && ($event['registered_count'] >= $event['max_attendees']);
                            ?>
                            <div class="col-lg-6 mb-4">
                                <div class="event-card h-100">
                                    <div class="event-date">
                                        <span class="event-day"><?php echo htmlspecialchars($day); ?></span>
                                        <span class="event-month"><?php echo htmlspecialchars(strtoupper($month)); ?></span>
                                    </div>
                                    <div class="event-content">
                                        <?php if ($is_full): ?>
                                            <span class="badge bg-danger float-end">Full</span>
                                        <?php endif; ?>
                                        <h3 class="h4 mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <div class="event-meta mb-3">
                                            <span class="me-3"><i class="far fa-clock text-primary"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?></span>
                                            <span><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <p class="mb-3"><?php echo htmlspecialchars(substr($event['description'] ?: 'Join us for this special event.', 0, 120)); ?>...</p>
                                        <div class="event-footer">
                                            <span class="attendees-count"><i class="fas fa-users"></i> <?php echo htmlspecialchars($event['registered_count'] ?? 0); ?> attending</span>
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <a href="event-register.php?id=<?php echo htmlspecialchars($event['id']); ?>" 
                                                   class="btn btn-primary btn-sm <?php echo $is_full ? 'disabled' : ''; ?>">
                                                    <?php echo $is_full ? 'Event Full' : 'Register'; ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="auth/login.php?redirect=event-register.php?id=<?php echo htmlspecialchars($event['id']); ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    Login to Register
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- No Events Message -->
                <?php if (empty($upcoming_events) && empty($past_events)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times display-1 text-muted mb-4"></i>
                    <h3 class="mb-3">No Events Scheduled</h3>
                    <p class="text-muted mb-4">Check back soon for upcoming events and activities.</p>
                    <a href="contact.php?subject=Event%20Suggestion" class="btn btn-primary">Suggest an Event</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Past Events Tab -->
            <div class="tab-pane fade" id="past" role="tabpanel">
                <div class="row" id="past-events-container">
                    <?php if (!empty($past_events)): ?>
                        <?php foreach ($past_events as $event): ?>
                            <?php
                            $event_date = new DateTime($event['event_date']);
                            $day = $event_date->format('d');
                            $month = $event_date->format('M');
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="past-event-card">
                                    <div class="past-event-image">
                                        <img src="assets/images/events/<?php echo htmlspecialchars($event['id'] ?? 'default'); ?>.jpg" 
                                             alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                             class="img-fluid">
                                        <div class="past-event-date">
                                            <span class="day"><?php echo htmlspecialchars($day); ?></span>
                                            <span class="month"><?php echo htmlspecialchars(strtoupper($month)); ?></span>
                                        </div>
                                    </div>
                                    <div class="past-event-content p-3">
                                        <h4 class="h5 mb-2"><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p class="small text-muted mb-2">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo $event_date->format('F j, Y'); ?>
                                        </p>
                                        <p class="small"><?php echo htmlspecialchars(substr($event['description'] ?: 'Church event', 0, 100)); ?>...</p>
                                        <div class="attendance-count">
                                            <i class="fas fa-user-check text-success me-1"></i>
                                            <?php echo htmlspecialchars($event['attended_count'] ?? 0); ?> attended
                                        </div>
                                        <a href="event-details.php?id=<?php echo htmlspecialchars($event['id']); ?>" 
                                           class="btn btn-sm btn-outline-primary mt-2">
                                            View Photos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-history display-1 text-muted mb-4"></i>
                            <h3 class="mb-3">No Past Events</h3>
                            <p class="text-muted">Past events will appear here after they've occurred.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Calendar View Tab -->
            <div class="tab-pane fade" id="calendar" role="tabpanel">
                <div class="calendar-container bg-white p-4 rounded shadow">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h3 class="mb-0">Church Calendar</h3>
                            <p class="text-muted mb-0">View all scheduled events in calendar format</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" id="prevMonth">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="currentMonth">
                                    <?php echo date('F Y'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="nextMonth">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="calendar-wrapper">
                        <div class="calendar-header">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>
                        <div class="calendar-body" id="calendar-body">
                            <!-- Calendar will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="calendar-legend mt-4">
                        <div class="legend-item d-inline-flex align-items-center me-4">
                            <div class="legend-color bg-primary"></div>
                            <span class="ms-2">Regular Service</span>
                        </div>
                        <div class="legend-item d-inline-flex align-items-center me-4">
                            <div class="legend-color bg-success"></div>
                            <span class="ms-2">Special Event</span>
                        </div>
                        <div class="legend-item d-inline-flex align-items-center">
                            <div class="legend-color bg-warning"></div>
                            <span class="ms-2">Meeting</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regular Services -->
        <div class="regular-services bg-light p-5 rounded mt-5">
            <h3 class="text-center mb-4">Regular Service Schedule</h3>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon mb-3">
                            <i class="fas fa-church text-primary"></i>
                        </div>
                        <h4>Sunday Service</h4>
                        <p class="text-muted">Every Sunday</p>
                        <p><strong>9:00 AM - 12:00 PM</strong></p>
                        <p>Main Sanctuary worship with children's church and nursery available.</p>
                        <a href="plan-your-visit.php" class="btn btn-sm btn-outline-primary mt-2">Plan Your Visit</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon mb-3">
                            <i class="fas fa-pray text-primary"></i>
                        </div>
                        <h4>Wednesday Prayer</h4>
                        <p class="text-muted">Every Wednesday</p>
                        <p><strong>6:00 PM - 7:00 PM</strong></p>
                        <p>Mid-week prayer meeting for healing, breakthrough, and spiritual renewal.</p>
                        <a href="prayer-request.php" class="btn btn-sm btn-outline-primary mt-2">Submit Prayer Request</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon mb-3">
                            <i class="fas fa-bible text-primary"></i>
                        </div>
                        <h4>Bible Study</h4>
                        <p class="text-muted">Every Friday</p>
                        <p><strong>7:00 PM - 8:30 PM</strong></p>
                        <p>In-depth Bible study and discussion for spiritual growth.</p>
                        <a href="sermons.php" class="btn btn-sm btn-outline-primary mt-2">View Bible Studies</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section text-center py-5 mt-5">
            <h2 class="mb-4">Can't Find What You're Looking For?</h2>
            <p class="lead mb-4">Have an idea for an event or need more information?</p>
            <div class="cta-buttons">
                <a href="contact.php?subject=Event%20Inquiry" class="btn btn-primary btn-lg me-3">Contact Us</a>
                <a href="prayer-request.php" class="btn btn-outline-primary btn-lg">Request Prayer</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
    color: #666;
    font-weight: 500;
    padding: 12px 25px;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active {
    color: #1a5276;
    background: transparent;
    border-color: #1a5276;
}

.nav-tabs .nav-link:hover {
    color: #1a5276;
}

.event-card {
    display: flex;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.event-date {
    background: #1a5276;
    color: white;
    width: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.event-day {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}

.event-month {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.event-content {
    flex: 1;
    padding: 20px;
}

.event-meta {
    font-size: 0.9rem;
    color: #666;
}

.event-meta i {
    margin-right: 5px;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.attendees-count {
    font-size: 0.9rem;
    color: #666;
}

.attendees-count i {
    margin-right: 5px;
}

.past-event-card {
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.past-event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.past-event-image {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.past-event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.past-event-card:hover .past-event-image img {
    transform: scale(1.05);
}

.past-event-date {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #1a5276;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.past-event-date .day {
    font-size: 1.2rem;
    font-weight: bold;
    line-height: 1;
}

.past-event-date .month {
    font-size: 0.7rem;
    text-transform: uppercase;
}

.attendance-count {
    font-size: 0.85rem;
    color: #28a745;
}

.calendar-wrapper {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #1a5276;
    color: white;
    text-align: center;
    font-weight: 500;
    padding: 10px 0;
}

.calendar-day-header {
    padding: 5px;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    min-height: 400px;
}

.calendar-day {
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding: 10px;
    min-height: 80px;
    position: relative;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.empty {
    background: #f8f9fa;
}

.day-number {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.calendar-event {
    background: #1a5276;
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 0.75rem;
    margin-bottom: 2px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.calendar-event:hover {
    opacity: 0.9;
}

.calendar-event.special {
    background: #e67e22;
}

.calendar-event.meeting {
    background: #ffc107;
    color: #333;
}

.legend-color {
    width: 15px;
    height: 15px;
    border-radius: 3px;
}

.service-card {
    border: 1px solid #eee;
    border-radius: 10px;
    background: white;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.service-icon {
    font-size: 2.5rem;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .nav-tabs .nav-link {
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    
    .event-card {
        flex-direction: column;
    }
    
    .event-date {
        width: 100%;
        flex-direction: row;
        justify-content: center;
        gap: 10px;
        padding: 10px;
    }
    
    .event-day {
        font-size: 1.5rem;
    }
    
    .calendar-day {
        min-height: 60px;
        padding: 5px;
    }
    
    .day-number {
        font-size: 0.9rem;
    }
    
    .calendar-event {
        font-size: 0.65rem;
        padding: 1px 3px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calendar functionality
    let currentDate = new Date();
    
    function renderCalendar(date) {
        const calendarBody = document.getElementById('calendar-body');
        calendarBody.innerHTML = '';
        
        // Get first day of month and total days
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        const totalDays = lastDay.getDate();
        const startingDay = firstDay.getDay();
        
        // Update month display
        document.getElementById('currentMonth').textContent = 
            date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        
        // Add empty days for previous month
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarBody.appendChild(emptyDay);
        }
        
        // Add days of current month
        for (let day = 1; day <= totalDays; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.innerHTML = `<div class="day-number">${day}</div>`;
            
            // Add events based on the day (sample data)
            if (day === 15) {
                dayElement.innerHTML += '<div class="calendar-event special" data-bs-toggle="tooltip" title="Youth Camp">Youth Camp</div>';
            }
            if (day === 22) {
                dayElement.innerHTML += '<div class="calendar-event" data-bs-toggle="tooltip" title="Sunday Service">Sunday Service</div>';
            }
            if (day % 7 === 3) {
                dayElement.innerHTML += '<div class="calendar-event meeting" data-bs-toggle="tooltip" title="Bible Study">Bible Study</div>';
            }
            
            // Highlight today
            const today = new Date();
            if (date.getMonth() === today.getMonth() && 
                date.getFullYear() === today.getFullYear() && 
                day === today.getDate()) {
                dayElement.style.backgroundColor = 'rgba(26, 82, 118, 0.1)';
                dayElement.style.borderLeft = '3px solid #1a5276';
            }
            
            calendarBody.appendChild(dayElement);
        }
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initial render
    renderCalendar(currentDate);
    
    // Navigation buttons
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });
    
    document.getElementById('currentMonth').addEventListener('click', function() {
        currentDate = new Date();
        renderCalendar(currentDate);
    });
    
    // Tab functionality
    const triggerTabList = [].slice.call(document.querySelectorAll('#eventsTab button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
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