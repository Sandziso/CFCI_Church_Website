<?php
// ministry-details.php
session_start();

// Database connection
require_once 'includes/config.php';

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

// Check if ministry ID is provided
$ministry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ministry = null;
$upcoming_events = [];
$ministry_members = [];
$related_ministries = [];

if ($ministry_id > 0 && $conn) {
    try {
        // Fetch ministry details
        $stmt = $conn->prepare("
            SELECT m.*, 
                   u.full_name as leader_name,
                   u.email as leader_email,
                   u.phone as leader_phone
            FROM ministries m
            LEFT JOIN users u ON m.leader_id = u.id
            WHERE m.id = ? AND m.status = 'active'
        ");
        $stmt->execute([$ministry_id]);
        $ministry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ministry) {
            // Get member count
            $count_stmt = $conn->prepare("
                SELECT COUNT(*) as member_count 
                FROM ministry_members 
                WHERE ministry_id = ? AND status = 'active'
            ");
            $count_stmt->execute([$ministry_id]);
            $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
            $ministry['member_count'] = $count['member_count'] ?? 0;
            
            // Fetch upcoming events for this ministry
            $event_stmt = $conn->prepare("
                SELECT id, title, description, event_date, start_time, end_time, location
                FROM events 
                WHERE ministry_id = ? AND event_date >= CURDATE() 
                AND status = 'active'
                ORDER BY event_date ASC 
                LIMIT 3
            ");
            $event_stmt->execute([$ministry_id]);
            $upcoming_events = $event_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fetch ministry members (leaders and active members)
            $member_stmt = $conn->prepare("
                SELECT mm.*, u.full_name, u.email, u.profile_image
                FROM ministry_members mm
                JOIN users u ON mm.user_id = u.id
                WHERE mm.ministry_id = ? AND mm.status = 'active'
                ORDER BY 
                    CASE mm.role 
                        WHEN 'leader' THEN 1
                        WHEN 'co-leader' THEN 2
                        WHEN 'coordinator' THEN 3
                        ELSE 4
                    END,
                    u.full_name
                LIMIT 10
            ");
            $member_stmt->execute([$ministry_id]);
            $ministry_members = $member_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fetch related ministries (same category)
            if (!empty($ministry['category'])) {
                $related_stmt = $conn->prepare("
                    SELECT id, name, description, image
                    FROM ministries 
                    WHERE category = ? AND id != ? AND status = 'active'
                    ORDER BY name
                    LIMIT 3
                ");
                $related_stmt->execute([$ministry['category'], $ministry_id]);
                $related_ministries = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
    } catch (PDOException $e) {
        error_log("Ministry details fetch error: " . $e->getMessage());
        // Continue with default data if needed
    }
}

// If ministry not found, redirect to ministries page
if (!$ministry && $ministry_id > 0) {
    header('Location: ministries.php');
    exit();
}

// Default ministry if database is empty
if (!$ministry) {
    $ministry = [
        'id' => 1,
        'name' => 'Youth Ministry',
        'description' => '<p>The Youth Ministry at CFCI is dedicated to empowering the next generation of Christian leaders. We provide a safe, engaging environment where youth can grow in their faith, build meaningful relationships, and discover their God-given purpose.</p>
        
        <h3>Our Vision</h3>
        <p>To raise a generation of young people who are passionate about God, grounded in His Word, and committed to transforming their communities through Christ-like leadership.</p>
        
        <h3>What We Do</h3>
        <ul>
            <li><strong>Weekly Youth Services:</strong> Every Friday at 6:00 PM with worship, relevant teaching, and small group discussions</li>
            <li><strong>Discipleship Groups:</strong> Age-specific small groups meeting weekly for deeper relationship building</li>
            <li><strong>Annual Youth Camp:</strong> A transformative 3-day camping experience focused on spiritual growth</li>
            <li><strong>Community Outreach:</strong> Monthly service projects to impact our local community</li>
            <li><strong>Leadership Training:</strong> Programs to develop leadership skills and spiritual gifts</li>
        </ul>',
        'category' => 'youth',
        'meeting_day' => 'Friday',
        'meeting_time' => '6:00 PM',
        'meeting_location' => 'Youth Hall (Main Church Building)',
        'contact_email' => 'youth@cfci.org.sz',
        'contact_phone' => '+268 2505 5961',
        'image' => 'assets/images/ministries/youth-ministry.jpg',
        'leader_name' => 'Pastor Sarah Mkhwanazi',
        'leader_email' => 'sarah.mkhwanazi@cfci.org.sz',
        'member_count' => 85
    ];
    
    $upcoming_events = [
        [
            'id' => 1, 
            'title' => 'Youth Camp 2025', 
            'description' => 'Annual youth camping experience', 
            'event_date' => '2025-07-15', 
            'start_time' => '08:00:00',
            'location' => 'Mhlambanyatsi Campsite'
        ],
        [
            'id' => 2, 
            'title' => 'Worship Night', 
            'description' => 'Special youth worship service', 
            'event_date' => '2025-06-28', 
            'start_time' => '18:30:00',
            'location' => 'Youth Hall'
        ]
    ];
    
    $ministry_members = [
        ['full_name' => 'Pastor Sarah Mkhwanazi', 'role' => 'leader', 'email' => 'sarah@cfci.org.sz'],
        ['full_name' => 'John Dlamini', 'role' => 'co-leader', 'email' => 'john@cfci.org.sz'],
        ['full_name' => 'Thando Nkosi', 'role' => 'worship leader', 'email' => 'thando@cfci.org.sz']
    ];
    
    $related_ministries = [
        ['id' => 5, 'name' => 'Children\'s Church', 'description' => 'For ages 3-12', 'image' => 'assets/images/ministries/children-church.jpg'],
        ['id' => 3, 'name' => 'Worship Team', 'description' => 'Leading in worship', 'image' => 'assets/images/ministries/worship-team.jpg']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ministry['name']); ?> - CFCI Church</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #e67e22;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .page-header {
            padding: 100px 0 60px;
            margin-top: -1px;
        }
        
        .ministry-details-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .ministry-image img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .info-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            height: 100%;
            border-left: 4px solid var(--primary);
        }
        
        .info-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .info-icon i {
            font-size: 1.3rem;
        }
        
        .info-content h5 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .avatar-initials-lg {
            width: 100px;
            height: 100px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 2rem;
            margin: 0 auto;
        }
        
        .avatar-initials-sm {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .event-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .event-card:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .event-date {
            min-width: 70px;
            text-align: center;
        }
        
        .event-date .month {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #666;
        }
        
        .event-date .day {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1;
            color: var(--primary);
        }
        
        .event-date .year {
            font-size: 0.8rem;
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            line-height: 1;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .share-buttons .btn-facebook {
            background: #3b5998;
            color: white;
            border-color: #3b5998;
        }
        
        .share-buttons .btn-success {
            background: #25d366;
            color: white;
            border-color: #25d366;
        }
        
        .member-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .member-item:last-child {
            border-bottom: none;
        }
        
        .related-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .related-item:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 80px 0 40px;
            }
            
            .ministry-details-card {
                padding: 20px;
            }
            
            .info-card {
                flex-direction: column;
                text-align: center;
                padding: 20px 15px;
            }
            
            .info-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .share-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navigation -->
    <?php include_once 'includes/header.php'; ?>

    <section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/ministries-bg.jpg') center/cover no-repeat;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="text-white"><?php echo htmlspecialchars($ministry['name']); ?></h1>
                    <?php if (!empty($ministry['category'])): ?>
                        <p class="text-white mb-0"><?php echo htmlspecialchars(ucfirst($ministry['category'])); ?> Ministry</p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                            <li class="breadcrumb-item"><a href="ministries.php" class="text-white">Ministries</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Details</li>
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
                    <div class="ministry-details-card">
                        <!-- Ministry Header -->
                        <div class="ministry-header mb-5">
                            <!-- Ministry Badges -->
                            <div class="ministry-badge mb-3">
                                <?php if (!empty($ministry['category'])): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($ministry['category'])); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($ministry['meeting_day']) && !empty($ministry['meeting_time'])): ?>
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        <?php echo htmlspecialchars($ministry['meeting_day'] . ' ' . $ministry['meeting_time']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Ministry Image -->
                            <div class="ministry-image mb-4">
                                <img src="<?php echo htmlspecialchars($ministry['image'] ?? 'assets/images/ministries/default.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($ministry['name']); ?>" 
                                     class="img-fluid"
                                     onerror="this.src='assets/images/ministries/default.jpg'">
                            </div>
                        </div>
                        
                        <!-- Ministry Details -->
                        <div class="ministry-body mb-5">
                            <!-- Quick Info -->
                            <div class="quick-info mb-5">
                                <div class="row g-4">
                                    <?php if (!empty($ministry['meeting_day']) && !empty($ministry['meeting_time'])): ?>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Meeting Schedule</h5>
                                                <p class="mb-0">
                                                    <?php echo htmlspecialchars($ministry['meeting_day']); ?> at 
                                                    <?php echo htmlspecialchars($ministry['meeting_time']); ?>
                                                    <?php if (!empty($ministry['meeting_frequency'])): ?>
                                                        <br><small class="text-muted">(<?php echo htmlspecialchars($ministry['meeting_frequency']); ?>)</small>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ministry['meeting_location'])): ?>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Location</h5>
                                                <p class="mb-0"><?php echo htmlspecialchars($ministry['meeting_location']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Members</h5>
                                                <p class="mb-0"><?php echo number_format($ministry['member_count'] ?? 0); ?> active members</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($ministry['contact_email'])): ?>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="info-icon">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div class="info-content">
                                                <h5>Contact</h5>
                                                <p class="mb-0"><?php echo htmlspecialchars($ministry['contact_email']); ?></p>
                                                <?php if (!empty($ministry['contact_phone'])): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars($ministry['contact_phone']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Ministry Description -->
                            <div class="ministry-description mb-5">
                                <h3 class="mb-4">About This Ministry</h3>
                                <?php if (!empty($ministry['description'])): ?>
                                    <?php if (strpos($ministry['description'], '<p>') !== false): ?>
                                        <?php echo $ministry['description']; ?>
                                    <?php else: ?>
                                        <p><?php echo nl2br(htmlspecialchars($ministry['description'])); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p>No description available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Upcoming Events -->
                        <?php if (!empty($upcoming_events)): ?>
                        <div class="upcoming-events mb-5">
                            <h3 class="mb-4">Upcoming Events</h3>
                            <div class="row">
                                <?php foreach ($upcoming_events as $event): ?>
                                    <?php
                                    $event_date = !empty($event['event_date']) ? new DateTime($event['event_date']) : new DateTime();
                                    $start_time = !empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : '';
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="event-card card h-100">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="event-date me-3">
                                                        <div class="month"><?php echo $event_date->format('M'); ?></div>
                                                        <div class="day"><?php echo $event_date->format('d'); ?></div>
                                                        <div class="year"><?php echo $event_date->format('Y'); ?></div>
                                                    </div>
                                                    <div>
                                                        <h5 class="card-title"><?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?></h5>
                                                        <?php if ($start_time): ?>
                                                            <p class="card-text small text-muted mb-1">
                                                                <i class="far fa-clock me-1"></i> <?php echo $start_time; ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($event['location'])): ?>
                                                            <p class="card-text small text-muted">
                                                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location']); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="events.php?ministry=<?php echo $ministry_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-alt me-2"></i>View All Events
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Join Ministry Section -->
                        <div class="join-section mt-5 pt-4 border-top">
                            <h3 class="mb-4">Get Involved</h3>
                            <p class="mb-4">Ready to join this ministry? Express your interest and we'll get in touch with you.</p>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form id="joinForm">
                                    <input type="hidden" name="ministry_id" value="<?php echo $ministry_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="interest_role" class="form-label">Area of Interest</label>
                                            <select class="form-select" id="interest_role" name="interest_role">
                                                <option value="">Select area</option>
                                                <option value="member">General Member</option>
                                                <option value="volunteer">Volunteer</option>
                                                <option value="worship">Worship Team</option>
                                                <option value="outreach">Outreach</option>
                                                <option value="admin">Administration</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="availability" class="form-label">Availability</label>
                                            <select class="form-select" id="availability" name="availability">
                                                <option value="">Select availability</option>
                                                <option value="weekdays">Weekdays</option>
                                                <option value="weekends">Weekends</option>
                                                <option value="evenings">Evenings</option>
                                                <option value="flexible">Flexible</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Why do you want to join this ministry? *</label>
                                        <textarea class="form-control" id="message" name="message" rows="3" required placeholder="Share your interest and any relevant experience..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-2"></i> Express Interest
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please <a href="auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">login</a> or <a href="auth/register.php" class="alert-link">create an account</a> to join this ministry.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="ministry-sidebar">
                        <!-- Ministry Leader -->
                        <?php if (!empty($ministry['leader_name'])): ?>
                        <div class="sidebar-widget mb-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Ministry Leader</h5>
                                    <div class="leader-info text-center">
                                        <div class="leader-avatar mb-3">
                                            <div class="avatar-initials-lg">
                                                <?php 
                                                $initials = '';
                                                $names = explode(' ', $ministry['leader_name']);
                                                foreach ($names as $name) {
                                                    if (!empty($name)) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                }
                                                echo substr($initials, 0, 2);
                                                ?>
                                            </div>
                                        </div>
                                        <h5><?php echo htmlspecialchars($ministry['leader_name']); ?></h5>
                                        <?php if (!empty($ministry['leader_email'])): ?>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($ministry['leader_email']); ?></p>
                                        <?php endif; ?>
                                        <div class="leader-contact mt-3">
                                            <?php if (!empty($ministry['leader_email'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($ministry['leader_email']); ?>" class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="fas fa-envelope me-1"></i> Email
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($ministry['contact_phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($ministry['contact_phone']); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-phone me-1"></i> Call
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Ministry Members -->
                        <?php if (!empty($ministry_members)): ?>
                        <div class="sidebar-widget mb-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Leadership Team</h5>
                                    <div class="members-list">
                                        <?php foreach ($ministry_members as $member): ?>
                                            <div class="member-item d-flex align-items-center mb-3">
                                                <div class="member-avatar me-3">
                                                    <div class="avatar-initials-sm">
                                                        <?php 
                                                        $initials = '';
                                                        $names = explode(' ', $member['full_name'] ?? 'User');
                                                        foreach ($names as $name) {
                                                            if (!empty($name)) {
                                                                $initials .= strtoupper(substr($name, 0, 1));
                                                            }
                                                        }
                                                        echo substr($initials, 0, 2);
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="member-info flex-grow-1">
                                                    <div class="member-name fw-bold"><?php echo htmlspecialchars($member['full_name'] ?? 'Unknown'); ?></div>
                                                    <?php if (!empty($member['role'])): ?>
                                                        <div class="member-role small text-muted"><?php echo htmlspecialchars(ucfirst($member['role'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (($ministry['member_count'] ?? 0) > count($ministry_members)): ?>
                                        <div class="text-center mt-3">
                                            <a href="ministry-members.php?id=<?php echo $ministry_id; ?>" class="btn btn-sm btn-outline-primary">
                                                View All <?php echo $ministry['member_count']; ?> Members
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Quick Stats -->
                        <div class="sidebar-widget mb-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Ministry Stats</h5>
                                    <div class="stats-grid">
                                        <div class="stat-item text-center">
                                            <div class="stat-number"><?php echo number_format($ministry['member_count'] ?? 0); ?></div>
                                            <div class="stat-label small">Active Members</div>
                                        </div>
                                        <div class="stat-item text-center">
                                            <div class="stat-number"><?php echo count($upcoming_events); ?></div>
                                            <div class="stat-label small">Upcoming Events</div>
                                        </div>
                                        <div class="stat-item text-center">
                                            <div class="stat-number"><?php echo count($ministry_members); ?></div>
                                            <div class="stat-label small">Leaders</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Related Ministries -->
                        <?php if (!empty($related_ministries)): ?>
                        <div class="sidebar-widget mb-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Related Ministries</h5>
                                    <div class="related-list">
                                        <?php foreach ($related_ministries as $related): ?>
                                            <div class="related-item mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="related-image me-3">
                                                        <img src="<?php echo htmlspecialchars($related['image'] ?? 'assets/images/ministries/default.jpg'); ?>" 
                                                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                                             class="rounded" width="50" height="50" style="object-fit: cover;"
                                                             onerror="this.src='assets/images/ministries/default.jpg'">
                                                    </div>
                                                    <div class="related-info">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($related['name']); ?></h6>
                                                        <p class="small text-muted mb-1">
                                                            <?php echo htmlspecialchars(substr($related['description'] ?? '', 0, 50)) . (strlen($related['description'] ?? '') > 50 ? '...' : ''); ?>
                                                        </p>
                                                        <a href="ministry-details.php?id=<?php echo $related['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Share Ministry -->
                        <div class="sidebar-widget">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Share This Ministry</h5>
                                    <div class="share-buttons d-flex gap-2 mb-3">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-facebook flex-grow-1">
                                            <i class="fab fa-facebook-f me-1"></i> Facebook
                                        </a>
                                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($ministry['name'] . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success flex-grow-1">
                                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                        </a>
                                    </div>
                                    <div class="mt-3">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="ministry-link" 
                                                   value="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" 
                                                   readonly>
                                            <button class="btn btn-sm btn-secondary" type="button" onclick="copyMinistryLink()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function copyMinistryLink() {
            const urlInput = document.getElementById('ministry-link');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999); // For mobile devices
            
            navigator.clipboard.writeText(urlInput.value).then(() => {
                // Show feedback
                const copyBtn = document.querySelector('button[onclick="copyMinistryLink()"]');
                const originalHTML = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                copyBtn.classList.remove('btn-secondary');
                copyBtn.classList.add('btn-success');
                
                setTimeout(() => {
                    copyBtn.innerHTML = originalHTML;
                    copyBtn.classList.remove('btn-success');
                    copyBtn.classList.add('btn-secondary');
                }, 2000);
            });
        }
        
        // Join form submission
        document.getElementById('joinForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const message = this.message.value.trim();
            if (!message) {
                alert('Please tell us why you want to join this ministry.');
                this.message.focus();
                return;
            }
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            button.disabled = true;
            
            // Simulate API call (replace with actual fetch in production)
            setTimeout(() => {
                alert('Thank you for your interest! The ministry leader will contact you soon.');
                this.reset();
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1500);
        });
        
        // Image fallback
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                if (this.src.includes('ministries/') || this.src.includes('assets/images/')) {
                    this.src = 'assets/images/ministries/default.jpg';
                }
            });
        });
        
        // Make copyMinistryLink function available globally
        window.copyMinistryLink = copyMinistryLink;
    });
    </script>
</body>
</html>