<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/main-functions.php';
require_once __DIR__ . '/includes/admin_functions.php';

requireAdminAccess();

$totalMembers = getTotalUsersCount();
$monthlyNew = getMonthlyNewMembers();
$monthlyDonations = getMonthlyDonationsTotal();
$pendingPrayers = getPendingPrayersCount();
$donationChartData = getDonationChartData();
$eventAttendance = getEventAttendanceStats();

// Recent donations (using the global connection)
global $conn;
$recentDonations = [];
try {
    if ($conn) {
        $stmt = $conn->query("
            SELECT d.*, u.full_name 
            FROM donations d 
            LEFT JOIN users u ON d.user_id = u.id 
            ORDER BY d.donation_date DESC 
            LIMIT 8
        ");
        $recentDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // handle silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CFCI Admin</title>
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a5276;
            --primary-light: #2e86c1;
            --primary-dark: #0f2e45;
            --accent: #e67e22;
            --accent-light: #f39c12;
            --bg-light: #f4f6f9;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
            --radius: 14px;
            --transition: 0.2s ease;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-light);
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 260px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.collapsed + .admin-main {
            margin-left: 70px;
        }
        
        @media (max-width: 991.98px) {
            .admin-main {
                margin-left: 0 !important;
            }
        }
        
        .page-content {
            flex: 1;
            padding: 1.75rem 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-weight: 600;
            font-size: 1.75rem;
            color: var(--primary-dark);
        }
        
        .btn-refresh {
            background: var(--primary);
            border: none;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        
        .btn-refresh:hover {
            background: var(--primary-dark);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(26,82,118,0.07);
            color: var(--primary);
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            color: var(--primary-dark);
        }
        
        .stat-info p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 992px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }
        
        .chart-container {
            position: relative;
            height: 280px;
        }
        
        .table-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }
        
        .table-card h5 {
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--text-muted);
            border-top: none;
        }
        
        .status-badge {
            padding: 0.2em 0.8em;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-outline-sm {
            border-radius: 30px;
            padding: 0.3rem 1rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include 'includes/admin_topbar.php'; ?>
        
        <div class="page-content">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
                <button class="btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($totalMembers) ?></h3>
                        <p>Total Members</p>
                        <small class="text-success">+<?= $monthlyNew ?> this month</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(39,174,96,0.1); color: #27ae60;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>SZL <?= $monthlyDonations ?></h3>
                        <p>Monthly Donations</p>
                        <small><?= date('F Y') ?></small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(243,156,18,0.1); color: #f39c12;">
                        <i class="fas fa-pray"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $pendingPrayers ?></h3>
                        <p>Pending Prayers</p>
                        <a href="#" class="small">View requests →</a>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(52,152,219,0.1); color: #3498db;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= getUpcomingEventsCount() ?></h3>
                        <p>Upcoming Events</p>
                        <a href="#" class="small">Manage events →</a>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="chart-grid">
                <div class="chart-card">
                    <h5 class="mb-3">Donations Trend (6 months)</h5>
                    <div class="chart-container">
                        <canvas id="donationChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h5 class="mb-3">Upcoming Event Registrations</h5>
                    <div class="chart-container">
                        <canvas id="eventChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Recent Donations Table -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Donations</h5>
                    <a href="#" class="btn-outline-sm" style="border:1px solid var(--border); color: var(--text-primary);">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentDonations)): ?>
                                <?php foreach ($recentDonations as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['full_name'] ?? 'Anonymous') ?></td>
                                    <td class="fw-bold text-success">SZL <?= number_format($d['amount'], 2) ?></td>
                                    <td><?= date('M d, Y', strtotime($d['donation_date'])) ?></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success status-badge">Completed</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">No recent donations</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Donation Chart
const donationCtx = document.getElementById('donationChart').getContext('2d');
const donationData = <?= json_encode($donationChartData) ?>;
new Chart(donationCtx, {
    type: 'line',
    data: {
        labels: Object.keys(donationData),
        datasets: [{
            label: 'Donations (SZL)',
            data: Object.values(donationData),
            borderColor: '#1a5276',
            backgroundColor: 'rgba(26,82,118,0.08)',
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#1a5276',
            pointRadius: 3,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: '#e2e8f0' }
            },
            x: { grid: { display: false } }
        }
    }
});

// Event Chart
const eventCtx = document.getElementById('eventChart').getContext('2d');
const eventData = <?= json_encode($eventAttendance) ?>;
new Chart(eventCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(eventData),
        datasets: [{
            label: 'Registrations',
            data: Object.values(eventData),
            backgroundColor: '#e67e22',
            borderRadius: 8,
            barPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: '#e2e8f0' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>