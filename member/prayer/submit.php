<?php
// ===================================================
// MEMBER - Submit Prayer Request
// ===================================================

require_once '../../includes/config.php';
require_once '../../includes/main-functions.php';

if (!is_logged_in()) {
    header('Location: ../../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$user_role = $_SESSION['user_role'];

// Redirect admins/pastors if needed
if (is_admin()) {
    header('Location: ../../admin/dashboard.php');
    exit();
} elseif (is_pastor()) {
    header('Location: ../../pastor/dashboard.php');
    exit();
}

require_once '../../includes/database.php';
$database = Database::getInstance();
$db = $database->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_text = trim($_POST['request_text'] ?? '');
    $category     = $_POST['category'] ?? 'other';
    $urgency      = $_POST['urgency'] ?? 'normal';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
    $allow_prayer_team = isset($_POST['allow_prayer_team']) ? 1 : 0;

    if (empty($request_text)) {
        $error = 'Please enter your prayer request.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO prayer_requests (user_id, request_text, category, urgency, is_anonymous, allow_comments, allow_prayer_team, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$user_id, $request_text, $category, $urgency, $is_anonymous, $allow_comments, $allow_prayer_team]);

            $_SESSION['flash_message'] = 'Your prayer request has been submitted. Our prayer team will be praying for you.';
            $_SESSION['flash_type'] = 'success';
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            error_log("Prayer submit error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

// Quick stats for sidebar
$quick_stats = [
    'upcoming_events'    => 0,
    'sermons_available'  => 0,
    'prayer_requests'    => 0,
    'ministries_involved' => 0,
];
$profile_completion = 0;

require_once '../includes/member_topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Prayer Request - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 56px;
        }
        .main-content {
            margin-left: 240px;
            padding: 20px;
        }
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
            }
        }
        .submit-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-width: 700px;
            margin: 0 auto;
        }
        .form-section {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="submit-card">
                    <h2 class="mb-4"><i class="fas fa-pray me-2"></i>Submit a Prayer Request</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="request_text" class="form-label">Your Prayer Request <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="request_text" name="request_text" rows="5" placeholder="Share your prayer need..." required><?php echo htmlspecialchars($_POST['request_text'] ?? ''); ?></textarea>
                        </div>

                        <div class="row form-section">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="health" <?php echo (isset($_POST['category']) && $_POST['category'] == 'health') ? 'selected' : ''; ?>>Health</option>
                                    <option value="financial" <?php echo (isset($_POST['category']) && $_POST['category'] == 'financial') ? 'selected' : ''; ?>>Financial</option>
                                    <option value="family" <?php echo (isset($_POST['category']) && $_POST['category'] == 'family') ? 'selected' : ''; ?>>Family</option>
                                    <option value="spiritual" <?php echo (isset($_POST['category']) && $_POST['category'] == 'spiritual') ? 'selected' : ''; ?>>Spiritual</option>
                                    <option value="work" <?php echo (isset($_POST['category']) && $_POST['category'] == 'work') ? 'selected' : ''; ?>>Work</option>
                                    <option value="other" <?php echo (!isset($_POST['category']) || $_POST['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="urgency" class="form-label">Urgency</label>
                                <select class="form-select" id="urgency" name="urgency">
                                    <option value="low" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="normal" <?php echo (!isset($_POST['urgency']) || $_POST['urgency'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                                    <option value="high" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'high') ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" <?php echo isset($_POST['is_anonymous']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_anonymous">Submit anonymously (your name will not be shown)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="allow_comments" id="allow_comments" checked>
                                <label class="form-check-label" for="allow_comments">Allow others to comment and pray</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allow_prayer_team" id="allow_prayer_team" checked>
                                <label class="form-check-label" for="allow_prayer_team">Share with the prayer team</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-1"></i> Submit Request</button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>