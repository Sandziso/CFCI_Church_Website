<?php
// admin/dashboard.php

// Start session and include required files
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/main-functions.php';

// Check authentication - Use the renamed requireAdminAccess() function
requireAdminAccess();

// Initialize ChurchDB
$db = new ChurchDB($conn);

// Get dashboard statistics - Use ChurchDB methods for these
$stats = $db->getChurchStats();
$recentDonations = $db->getDonationsByUser(null, 5); // Get recent donations
$pendingPrayers = $db->getPrayerRequests('pending', 5);

// Get recent activities from functions.php
$recentActivities = getRecentActivitiesList(10);

// Log dashboard access using function from functions.php
logUserActivity($_SESSION['user_id'], 'Admin Dashboard Access', 'Accessed admin dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #e67e22;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #154360 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                margin-left: -200px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
        }
        
        .sidebar .logo {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--secondary);
        }
        
        .sidebar .nav-link i {
            width: 25px;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-card .stat-title {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .dashboard-card .card-title {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .quick-actions .btn-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--dark);
        }
        
        .quick-actions .btn-action:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .quick-actions .btn-action i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .progress-bar-custom {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .mobile-menu-btn {
            display: none;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h4 class="mb-1">
                <i class="fas fa-church"></i>
            </h4>
            <small><?php echo htmlspecialchars(SITE_NAME); ?></small>
        </div>
        
        <nav class="mt-3">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i> Users
            </a>
            
            <a href="events.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            
            <a href="ministries.php" class="nav-link">
                <i class="fas fa-hands-helping"></i> Ministries
            </a>
            
            <a href="sermons.php" class="nav-link">
                <i class="fas fa-bible"></i> Sermons
            </a>
            
            <a href="donations.php" class="nav-link">
                <i class="fas fa-donate"></i> Donations
            </a>
            
            <a href="prayers.php" class="nav-link">
                <i class="fas fa-pray"></i> Prayer Requests
            </a>
            
            <a href="announcements.php" class="nav-link">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            
            <div class="mt-4"></div>
            
            <a href="../index.php" class="nav-link text-warning">
                <i class="fas fa-eye"></i> View Site
            </a>
            
            <a href="../logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
    
    <!-- Mobile Menu Button -->
    <button class="btn btn-primary mobile-menu-btn d-lg-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Admin Dashboard</h4>
                    <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                <?php echo getUserInitials($_SESSION['full_name'] ?? 'Admin'); ?>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_members'] ?? 0; ?></div>
                    <div class="stat-title">Total Members</div>
                    <small class="text-muted">Active church members</small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-donate"></i>
                    </div>
                    <div class="stat-number"><?php echo formatCurrencyAmount($stats['recent_donations'] ?? 0); ?></div>
                    <div class="stat-title">Monthly Donations</div>
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-pray"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pending_prayers'] ?? 0; ?></div>
                    <div class="stat-title">Prayer Requests</div>
                    <small class="text-muted">Awaiting response</small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['upcoming_events'] ?? 0; ?></div>
                    <div class="stat-title">Upcoming Events</div>
                    <small class="text-muted">Scheduled activities</small>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Chart -->
                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Donation Trends</h5>
                    <canvas id="donationChart" height="250"></canvas>
                </div>
                
                <!-- Recent Activities -->
                <div class="dashboard-card">
                    <h5 class="card-title">Recent Activities</h5>
                    <div class="list-group">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach($recentActivities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity']); ?></h6>
                                    <small><?php echo formatDateString($activity['created_at'], 'H:i'); ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($activity['details']); ?></p>
                                <small><i class="fas fa-user"></i> <?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item">
                                <p class="mb-0 text-muted">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="row g-3 quick-actions">
                        <div class="col-6">
                            <a href="users.php?action=create" class="btn-action">
                                <i class="fas fa-user-plus"></i>
                                <span>Add User</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="events.php?action=create" class="btn-action">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Create Event</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="announcements.php?action=create" class="btn-action">
                                <i class="fas fa-bullhorn"></i>
                                <span>Announcement</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="sermons.php?action=create" class="btn-action">
                                <i class="fas fa-bible"></i>
                                <span>Add Sermon</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Donations -->
                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Recent Donations</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <?php if (!empty($recentDonations)): ?>
                                    <?php foreach($recentDonations as $donation): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle fa-lg text-muted me-2"></i>
                                                <div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($donation['donor_name'] ?? 'Anonymous'); ?></div>
                                                    <small class="text-muted"><?php echo formatDateString($donation['donation_date'], 'M d'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?php echo formatCurrencyAmount($donation['amount']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-muted text-center">No recent donations</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="dashboard-card">
                    <h5 class="card-title">System Status</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Database</span>
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Connected
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Storage</span>
                            <span>65% Used</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar progress-bar-custom" style="width: 65%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Security</span>
                            <span class="text-success">
                                <i class="fas fa-shield-alt"></i> Active
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Last Backup</span>
                            <span class="text-success">Today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Prayer Requests -->
        <div class="dashboard-card mt-4">
            <h5 class="card-title">Recent Prayer Requests</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingPrayers)): ?>
                            <?php foreach($pendingPrayers as $prayer): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <?php if(isset($prayer['is_anonymous']) && $prayer['is_anonymous']): ?>
                                            <i class="fas fa-user-secret fa-lg text-muted"></i>
                                            <?php else: ?>
                                            <i class="fas fa-user-circle fa-lg text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0"><?php echo (isset($prayer['is_anonymous']) && $prayer['is_anonymous']) ? 'Anonymous' : htmlspecialchars($prayer['full_name'] ?? 'User'); ?></h6>
                                            <p class="mb-0 text-muted small"><?php echo truncateTextContent($prayer['request_text'] ?? '', 60); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatDateString($prayer['submitted_at'] ?? ''); ?></td>
                                <td><?php echo getStatusBadgeHtml($prayer['status'] ?? 'pending'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPrayer(<?php echo $prayer['id'] ?? 0; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-muted text-center">No pending prayer requests</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Chart.js
        const ctx = document.getElementById('donationChart').getContext('2d');
        const donationChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Donations (ETB)',
                    data: [12500, 14200, 13100, 18500, 16200, 21000, 19500],
                    borderColor: '#1a5276',
                    backgroundColor: 'rgba(26, 82, 118, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'ETB ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // View prayer request
        function viewPrayer(id) {
            window.location.href = 'prayers.php?action=view&id=' + id;
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>