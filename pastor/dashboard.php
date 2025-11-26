<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastor Dashboard - Christian Family Centre International</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-blue: #1a6b9e;
            --dark-blue: #135a87;
            --light-blue: #e1f0ff;
            --accent-green: #4caf50;
            --dark-green: #3e8c42;
            --light-green: #e6f9f1;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --text-dark: #212529;
            --sidebar-bg: #1a6b9e;
            --sidebar-text: #ffffff;
            --sidebar-hover: #135a87;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: #f5f7f9;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            padding: 20px 0;
            transition: var(--transition);
            z-index: 100;
            overflow-y: auto;
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .church-logo {
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        .church-logo i {
            font-size: 2rem;
            color: var(--accent-green);
        }

        .church-logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.2rem;
            margin-left: 10px;
        }

        .church-logo span {
            color: var(--accent-green);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 0;
            border-left: 3px solid transparent;
            transition: var(--transition);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.85);
        }

        .menu-item:hover, .menu-item.active {
            background: var(--sidebar-hover);
            border-left: 3px solid var(--accent-green);
            color: white;
        }

        .menu-item i {
            width: 30px;
            font-size: 1.1rem;
        }

        .menu-item span {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: var(--transition);
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left h1 {
            font-size: 1.5rem;
            color: var(--dark-blue);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border-radius: 30px;
            border: 1px solid var(--medium-gray);
            font-size: 0.9rem;
            width: 250px;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(26, 107, 158, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dark-gray);
        }

        .notifications, .user-profile {
            position: relative;
            cursor: pointer;
        }

        .notification-icon, .user-profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-blue);
            color: var(--primary-blue);
            font-size: 1.2rem;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-green);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .user-profile-img img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .welcome-banner h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            max-width: 600px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: var(--dark-blue);
        }

        .stat-info p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .dashboard-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .card-header h3 {
            font-size: 1.3rem;
            color: var(--dark-blue);
        }

        .card-header a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .recent-members {
            max-height: 400px;
            overflow-y: auto;
        }

        .member-list {
            list-style: none;
        }

        .member-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--medium-gray);
            transition: var(--transition);
        }

        .member-item:hover {
            background-color: rgba(26, 107, 158, 0.05);
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
            background: var(--light-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary-blue);
            font-size: 1.2rem;
        }

        .member-info h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .member-info p {
            font-size: 0.8rem;
            color: var(--dark-gray);
        }

        .member-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            color: var(--dark-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--primary-blue);
            color: white;
        }

        .upcoming-events {
            max-height: 400px;
            overflow-y: auto;
        }

        .event-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--medium-gray);
            transition: var(--transition);
        }

        .event-item:hover {
            background-color: rgba(26, 107, 158, 0.05);
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-date {
            display: inline-block;
            background: var(--light-blue);
            color: var(--primary-blue);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .event-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .event-details {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--dark-gray);
            gap: 15px;
        }

        .event-details span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: var(--transition);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-success {
            background: var(--accent-green);
            color: white;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: white;
        }

        .action-card h4 {
            margin-bottom: 10px;
            color: var(--dark-blue);
        }

        .action-card p {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        .chart-container {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 1.3rem;
            color: var(--dark-blue);
        }

        .chart-wrapper {
            height: 300px;
            position: relative;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .sidebar .church-logo-text,
            .sidebar .menu-item span {
                display: none;
            }
            
            .sidebar .menu-item {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar .menu-item i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .header {
                padding: 15px;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="church-logo">
                <i class="fas fa-church"></i>
                <div class="church-logo-text">CFCI <span>Church</span></div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-users"></i>
                <span>Members</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-praying-hands"></i>
                <span>Prayer Requests</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Sermons</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-donate"></i>
                <span>Donations</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Pastor Dashboard</h1>
            </div>
            
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                
                <div class="notifications">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notification-count">3</div>
                </div>
                
                <div class="user-profile">
                    <div class="user-profile-img">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Pastor">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>Welcome, Bishop Zakes Nxumalo</h2>
                <p>You have 5 new prayer requests, 3 upcoming events, and 8 new member registrations this week.</p>
                <button class="btn btn-success">View Recent Activity</button>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e1f0ff; color: #1a6b9e;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1,250</h3>
                        <p>Total Members</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e6f9f1; color: #4caf50;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>28</h3>
                        <p>New This Month</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef6e6; color: #f39c12;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>7</h3>
                        <p>Upcoming Events</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce8e6; color: #e74c3c;">
                        <i class="fas fa-praying-hands"></i>
                    </div>
                    <div class="stat-info">
                        <h3>15</h3>
                        <p>Prayer Requests</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Members -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Members</h3>
                        <a href="#">View All</a>
                    </div>
                    
                    <div class="recent-members">
                        <ul class="member-list">
                            <li class="member-item">
                                <div class="member-avatar">JD</div>
                                <div class="member-info">
                                    <h4>John Dlamini</h4>
                                    <p>Joined: 2 days ago</p>
                                </div>
                                <div class="member-actions">
                                    <div class="action-btn"><i class="fas fa-envelope"></i></div>
                                    <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                                </div>
                            </li>
                            <li class="member-item">
                                <div class="member-avatar">SN</div>
                                <div class="member-info">
                                    <h4>Sarah Nkosi</h4>
                                    <p>Joined: 3 days ago</p>
                                </div>
                                <div class="member-actions">
                                    <div class="action-btn"><i class="fas fa-envelope"></i></div>
                                    <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                                </div>
                            </li>
                            <li class="member-item">
                                <div class="member-avatar">TM</div>
                                <div class="member-info">
                                    <h4>Thomas Mbeki</h4>
                                    <p>Joined: 4 days ago</p>
                                </div>
                                <div class="member-actions">
                                    <div class="action-btn"><i class="fas fa-envelope"></i></div>
                                    <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                                </div>
                            </li>
                            <li class="member-item">
                                <div class="member-avatar">PN</div>
                                <div class="member-info">
                                    <h4>Phumzile Ndlovu</h4>
                                    <p>Joined: 5 days ago</p>
                                </div>
                                <div class="member-actions">
                                    <div class="action-btn"><i class="fas fa-envelope"></i></div>
                                    <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                                </div>
                            </li>
                            <li class="member-item">
                                <div class="member-avatar">RM</div>
                                <div class="member-info">
                                    <h4>Robert Mthethwa</h4>
                                    <p>Joined: 6 days ago</p>
                                </div>
                                <div class="member-actions">
                                    <div class="action-btn"><i class="fas fa-envelope"></i></div>
                                    <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Upcoming Events</h3>
                        <a href="#">View All</a>
                    </div>
                    
                    <div class="upcoming-events">
                        <div class="event-item">
                            <span class="event-date">July 6, 2025</span>
                            <h4 class="event-title">Sunday Worship Service</h4>
                            <div class="event-details">
                                <span><i class="fas fa-clock"></i> 9:00 AM - 12:00 PM</span>
                                <span><i class="fas fa-map-marker-alt"></i> Main Sanctuary</span>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary">Details</button>
                                <button class="btn btn-outline">Remind</button>
                            </div>
                        </div>
                        
                        <div class="event-item">
                            <span class="event-date">July 8, 2025</span>
                            <h4 class="event-title">Youth Fellowship</h4>
                            <div class="event-details">
                                <span><i class="fas fa-clock"></i> 4:00 PM - 6:00 PM</span>
                                <span><i class="fas fa-map-marker-alt"></i> Youth Hall</span>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary">Details</button>
                                <button class="btn btn-outline">Remind</button>
                            </div>
                        </div>
                        
                        <div class="event-item">
                            <span class="event-date">July 12, 2025</span>
                            <h4 class="event-title">Men's Prayer Breakfast</h4>
                            <div class="event-details">
                                <span><i class="fas fa-clock"></i> 7:00 AM - 9:00 AM</span>
                                <span><i class="fas fa-map-marker-alt"></i> Fellowship Hall</span>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary">Details</button>
                                <button class="btn btn-outline">Remind</button>
                            </div>
                        </div>
                        
                        <div class="event-item">
                            <span class="event-date">July 19, 2025</span>
                            <h4 class="event-title">Community Outreach Day</h4>
                            <div class="event-details">
                                <span><i class="fas fa-clock"></i> 10:00 AM - 2:00 PM</span>
                                <span><i class="fas fa-map-marker-alt"></i> Local Park</span>
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-primary">Details</button>
                                <button class="btn btn-outline">Remind</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Attendance & Engagement</h3>
                    <div>
                        <select class="form-control" style="padding: 5px 10px; border-radius: 20px; border: 1px solid #ddd;">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Last 90 Days</option>
                        </select>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                
                <div class="quick-actions">
                    <div class="action-card">
                        <div class="action-icon" style="background: #1a6b9e;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h4>Add New Member</h4>
                        <p>Register a new church member</p>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon" style="background: #4caf50;">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h4>Create Event</h4>
                        <p>Schedule a new church event</p>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon" style="background: #f39c12;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4>Upload Sermon</h4>
                        <p>Share your latest message</p>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon" style="background: #e74c3c;">
                            <i class="fas fa-pray"></i>
                        </div>
                        <h4>Prayer Requests</h4>
                        <p>View and respond to requests</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Simulate real-time data updates
        document.addEventListener('DOMContentLoaded', function() {
            // Attendance Chart
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    datasets: [
                        {
                            label: 'Sunday Service',
                            data: [850, 0, 0, 0, 0, 0, 0],
                            borderColor: '#1a6b9e',
                            backgroundColor: 'rgba(26, 107, 158, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Bible Study',
                            data: [0, 0, 0, 120, 0, 0, 0],
                            borderColor: '#4caf50',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Youth Fellowship',
                            data: [0, 0, 0, 0, 0, 65, 0],
                            borderColor: '#f39c12',
                            backgroundColor: 'rgba(243, 156, 18, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Weekly Attendance'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Simulate real-time updates
            setInterval(() => {
                // Update stats with random fluctuations
                document.querySelectorAll('.stat-info h3').forEach((stat, index) => {
                    const currentValue = parseInt(stat.textContent);
                    const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                    if (currentValue + change >= 0) {
                        stat.textContent = currentValue + change;
                    }
                });
                
                // Update notification count
                const notificationCount = document.querySelector('.notification-count');
                const currentCount = parseInt(notificationCount.textContent);
                notificationCount.textContent = Math.max(0, currentCount + Math.floor(Math.random() * 3) - 1);
                
                // Update chart data
                attendanceChart.data.datasets.forEach(dataset => {
                    dataset.data = dataset.data.map(value => {
                        if (value > 0) {
                            const change = Math.floor(Math.random() * 10) - 5;
                            return Math.max(0, value + change);
                        }
                        return value;
                    });
                });
                attendanceChart.update();
                
            }, 5000); // Update every 5 seconds
            
            // Simulate member registration notification
            setTimeout(() => {
                const notificationCount = document.querySelector('.notification-count');
                notificationCount.textContent = parseInt(notificationCount.textContent) + 1;
                
                // Add new member to the list
                const memberList = document.querySelector('.member-list');
                const newMember = document.createElement('li');
                newMember.className = 'member-item';
                newMember.innerHTML = `
                    <div class="member-avatar">NS</div>
                    <div class="member-info">
                        <h4>Nokulunga Sibiya</h4>
                        <p>Joined: Just now</p>
                    </div>
                    <div class="member-actions">
                        <div class="action-btn"><i class="fas fa-envelope"></i></div>
                        <div class="action-btn"><i class="fas fa-user-plus"></i></div>
                    </div>
                `;
                memberList.prepend(newMember);
            }, 8000);
        });
    </script>
</body>
</html>