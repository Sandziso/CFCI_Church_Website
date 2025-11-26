<?php
// member/submit-prayer.php

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prayer_text = trim($_POST['prayer_text'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $urgency = $_POST['urgency'] ?? 'normal';
    
    if (!empty($prayer_text)) {
        $result = $db->submitDetailedPrayerRequest($user_id, $prayer_text, $category, $is_anonymous, $urgency);
        if ($result) {
            $session->setFlash('success', 'Prayer request submitted successfully');
            header('Location: prayer-requests.php');
            exit;
        } else {
            $session->setFlash('error', 'Failed to submit prayer request');
        }
    } else {
        $session->setFlash('error', 'Please enter your prayer request');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Prayer Request - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2c7be5;
            --accent-blue: #1c65c9;
            --light-blue: #e6f0ff;
            --accent-green: #00d97e;
            --light-green: #e6fff2;
            --accent-purple: #9b59b6;
            --light-purple: #f5eef8;
            --dark-text: #2d3748;
            --light-text: #718096;
            --light-gray: #f8f9fa;
            --border-color: #e2e8f0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
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
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .prayer-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }

        .prayer-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-purple) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .prayer-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .prayer-header p {
            opacity: 0.9;
        }

        .prayer-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
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
            min-height: 150px;
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

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
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

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .character-counter {
            text-align: right;
            font-size: 0.8rem;
            color: var(--light-text);
            margin-top: 5px;
        }

        .character-counter.warning {
            color: #f6c343;
        }

        .character-counter.error {
            color: #cc0000;
        }

        .flash-messages {
            margin-bottom: 20px;
        }

        .flash-message {
            padding: 15px 20px;
            border-radius: 8px;
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
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../includes/member_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="prayer-container">
            <div class="prayer-header">
                <h1><i class="fas fa-praying-hands"></i> Submit Prayer Request</h1>
                <p>Share your prayer needs with our church community</p>
            </div>
            
            <div class="prayer-body">
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

                <form method="POST">
                    <div class="form-group">
                        <label for="prayer_text">Your Prayer Request *</label>
                        <textarea 
                            name="prayer_text" 
                            id="prayer_text" 
                            class="form-control" 
                            placeholder="Please share your prayer request in detail. Our prayer team will lift this up to God and someone may follow up with you if needed." 
                            required
                            rows="6"
                        ><?php echo isset($_POST['prayer_text']) ? htmlspecialchars($_POST['prayer_text']) : ''; ?></textarea>
                        <div class="character-counter" id="charCounter">0 characters</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="health">Health & Healing</option>
                                <option value="financial">Financial Needs</option>
                                <option value="family">Family & Relationships</option>
                                <option value="spiritual">Spiritual Growth</option>
                                <option value="work">Work & Career</option>
                                <option value="other" selected>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="urgency">Urgency Level</label>
                            <select name="urgency" id="urgency" class="form-control">
                                <option value="low">Low - General prayer</option>
                                <option value="normal" selected>Normal - Ongoing situation</option>
                                <option value="high">High - Immediate need</option>
                                <option value="urgent">Urgent - Critical situation</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1"
                                <?php echo isset($_POST['is_anonymous']) ? 'checked' : ''; ?>>
                            <label for="is_anonymous">Submit this prayer request anonymously</label>
                        </div>
                        <small style="color: var(--light-text); display: block; margin-top: 5px;">
                            Your name will not be shown to other members. Only pastors and prayer team leaders will see your identity.
                        </small>
                    </div>
                    
                    <div class="button-group">
                        <a href="prayer-requests.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Prayers
                        </a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-paper-plane"></i> Submit Prayer Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Character counter with warning levels
        const prayerText = document.getElementById('prayer_text');
        const charCounter = document.getElementById('charCounter');
        
        function updateCharacterCounter() {
            const length = prayerText.value.length;
            charCounter.textContent = `${length} characters`;
            
            // Remove all classes first
            charCounter.className = 'character-counter';
            
            // Add appropriate class based on length
            if (length > 1000) {
                charCounter.classList.add('error');
            } else if (length > 500) {
                charCounter.classList.add('warning');
            }
        }
        
        prayerText.addEventListener('input', updateCharacterCounter);
        updateCharacterCounter(); // Initialize counter
        
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s ease';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
