<?php
// member/prayer-requests.php

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'submit_prayer':
            $prayer_text = trim($_POST['prayer_text'] ?? '');
            $category = $_POST['category'] ?? 'other';
            $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
            
            if (!empty($prayer_text)) {
                $result = $db->submitPrayerRequest($user_id, $prayer_text, $category, $is_anonymous);
                if ($result) {
                    $session->setFlash('success', 'Prayer request submitted successfully');
                } else {
                    $session->setFlash('error', 'Failed to submit prayer request');
                }
            } else {
                $session->setFlash('error', 'Please enter your prayer request');
            }
            break;
            
        case 'update_prayer_status':
            $prayer_id = $_POST['prayer_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            if (!empty($prayer_id) && !empty($status)) {
                // Only allow users to close their own prayers
                if ($db->isUserPrayerOwner($user_id, $prayer_id)) {
                    $result = $db->updatePrayerRequestStatus($prayer_id, $status);
                    $session->setFlash($result ? 'success' : 'error', 
                        $result ? 'Prayer status updated' : 'Failed to update prayer status');
                } else {
                    $session->setFlash('error', 'You can only update your own prayer requests');
                }
            }
            break;
    }
    
    header('Location: prayer-requests.php');
    exit;
}

// Get prayer requests data
try {
    $user_prayers = $db->getUserPrayerRequests($user_id);
    $prayer_stats = $db->getUserPrayerStats($user_id);
} catch (Exception $e) {
    error_log("Prayer requests error: " . $e->getMessage());
    $user_prayers = [];
    $prayer_stats = ['total' => 0, 'pending' => 0, 'addressed' => 0, 'closed' => 0];
    $session->setFlash('error', 'Unable to load prayer requests. Please try again.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Requests - <?php echo SITE_NAME; ?></title>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.4rem;
            color: var(--dark-text);
            font-weight: 600;
        }

        .card-body {
            padding: 25px;
        }

        /* Prayer Form */
        .prayer-form {
            background: var(--light-purple);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        /* Prayer List */
        .prayer-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .prayer-item {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border-left: 4px solid var(--accent-purple);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .prayer-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .prayer-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .prayer-category {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .category-health { background: #ffe6e6; color: #cc0000; }
        .category-financial { background: #fff3cd; color: #856404; }
        .category-family { background: #d1ecf1; color: #0c5460; }
        .category-spiritual { background: var(--light-purple); color: var(--accent-purple); }
        .category-work { background: #e2e3e5; color: #383d41; }
        .category-other { background: var(--light-gray); color: var(--light-text); }

        .prayer-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-addressed { background: var(--light-green); color: #2d5016; }
        .status-closed { background: #e2e3e5; color: #383d41; }

        .prayer-text {
            line-height: 1.6;
            margin-bottom: 15px;
            color: var(--dark-text);
        }

        .prayer-meta {
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .prayer-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .prayer-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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

        .btn-success {
            background: var(--accent-green);
            color: white;
        }

        .btn-success:hover {
            background: #00c571;
        }

        .btn-block {
            display: block;
            width: 100%;
            justify-content: center;
            padding: 12px;
        }

        /* Prayer Response */
        .prayer-response {
            background: var(--light-green);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border-left: 3px solid var(--accent-green);
        }

        .response-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2d5016;
        }

        .response-text {
            line-height: 1.5;
            color: #2d5016;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--light-text);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--light-text);
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

        /* Responsive */
        @media (max-width: 768px) {
            .prayer-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .prayer-meta {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .prayer-actions {
                width: 100%;
                justify-content: flex-start;
            }
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
                <h1>Prayer Requests</h1>
                <p>Share your prayer needs and see how God is working</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="scrollToForm()">
                    <i class="fas fa-plus"></i> New Prayer Request
                </button>
            </div>
        </div>

        <!-- Prayer Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $prayer_stats['total']; ?></div>
                <div class="stat-label">Total Prayers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $prayer_stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $prayer_stats['addressed']; ?></div>
                <div class="stat-label">Addressed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $prayer_stats['closed']; ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </div>

        <!-- Submit Prayer Form -->
        <div class="card" id="prayerForm">
            <div class="card-header">
                <h2><i class="fas fa-praying-hands"></i> Submit Prayer Request</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="prayer-form">
                    <input type="hidden" name="action" value="submit_prayer">
                    
                    <div class="form-group">
                        <label for="prayer_text">Your Prayer Request *</label>
                        <textarea 
                            name="prayer_text" 
                            id="prayer_text" 
                            class="form-control" 
                            placeholder="Share your prayer request here..." 
                            required
                            rows="5"
                        ></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="health">Health</option>
                                <option value="financial">Financial</option>
                                <option value="family">Family</option>
                                <option value="spiritual">Spiritual</option>
                                <option value="work">Work</option>
                                <option value="other" selected>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Privacy Settings</label>
                            <div class="checkbox-group">
                                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1">
                                <label for="is_anonymous">Submit anonymously</label>
                            </div>
                            <small style="color: var(--light-text); display: block; margin-top: 5px;">
                                Your name will not be shown to other members
                            </small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Prayer Request
                    </button>
                </form>
            </div>
        </div>

        <!-- Prayer Requests List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> My Prayer Requests</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($user_prayers)): ?>
                    <div class="prayer-list">
                        <?php foreach ($user_prayers as $prayer): ?>
                            <div class="prayer-item">
                                <div class="prayer-header">
                                    <span class="prayer-category category-<?php echo $prayer['category']; ?>">
                                        <?php echo ucfirst($prayer['category']); ?>
                                    </span>
                                    <span class="prayer-status status-<?php echo $prayer['status']; ?>">
                                        <?php echo ucfirst($prayer['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="prayer-text">
                                    <?php echo nl2br(htmlspecialchars($prayer['request_text'])); ?>
                                </div>
                                
                                <?php if (!empty($prayer['response_text'])): ?>
                                    <div class="prayer-response">
                                        <div class="response-header">
                                            <i class="fas fa-reply"></i>
                                            <span>Pastor's Response</span>
                                        </div>
                                        <div class="response-text">
                                            <?php echo nl2br(htmlspecialchars($prayer['response_text'])); ?>
                                        </div>
                                        <?php if (!empty($prayer['addressed_at'])): ?>
                                            <div style="margin-top: 8px; font-size: 0.8rem; color: #2d5016;">
                                                Responded on <?php echo date('M j, Y', strtotime($prayer['addressed_at'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="prayer-meta">
                                    <div class="prayer-date">
                                        <i class="fas fa-calendar"></i>
                                        Submitted on <?php echo date('M j, Y g:i A', strtotime($prayer['submitted_at'])); ?>
                                    </div>
                                    
                                    <div class="prayer-actions">
                                        <?php if ($prayer['status'] === 'pending' || $prayer['status'] === 'addressed'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_prayer_status">
                                                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                                                <input type="hidden" name="status" value="closed">
                                                <button type="submit" class="btn btn-secondary" 
                                                        onclick="return confirm('Mark this prayer request as closed?')">
                                                    <i class="fas fa-check"></i> Mark Closed
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($prayer['status'] === 'closed'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_prayer_status">
                                                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-redo"></i> Reopen
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-pray"></i>
                        <h3>No prayer requests yet</h3>
                        <p>Submit your first prayer request using the form above</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function scrollToForm() {
            document.getElementById('prayerForm').scrollIntoView({
                behavior: 'smooth'
            });
            document.getElementById('prayer_text').focus();
        }

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                msg.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);

        // Add character counter to prayer text
        const prayerText = document.getElementById('prayer_text');
        if (prayerText) {
            const counter = document.createElement('div');
            counter.style.textAlign = 'right';
            counter.style.fontSize = '0.8rem';
            counter.style.color = 'var(--light-text)';
            counter.style.marginTop = '5px';
            prayerText.parentNode.appendChild(counter);
            
            function updateCounter() {
                const length = prayerText.value.length;
                counter.textContent = `${length} characters`;
                
                if (length > 1000) {
                    counter.style.color = '#cc0000';
                } else if (length > 500) {
                    counter.style.color = '#f6c343';
                } else {
                    counter.style.color = 'var(--light-text)';
                }
            }
            
            prayerText.addEventListener('input', updateCounter);
            updateCounter();
        }
    </script>
</body>
</html>
