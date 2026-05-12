<?php
// ===================================================
// MEMBER - Internal Messaging
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

// --- Tabs ---
$tab = $_GET['tab'] ?? 'inbox'; // inbox | sent | compose

// --- Handle Compose ---
$message_sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $subject     = trim($_POST['subject'] ?? '');
    $content     = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Message content is required.';
    } elseif ($receiver_id <= 0) {
        $error = 'Please select a recipient.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $receiver_id, $subject, $content]);
            $message_sent = true;
        } catch (Exception $e) {
            error_log("Message send error: " . $e->getMessage());
            $error = 'Failed to send message. The messaging system may not be set up.';
        }
    }
}

// --- Fetch Messages ---
$messages = [];
if ($tab == 'inbox') {
    $stmt = $db->prepare("
        SELECT m.*, u.full_name as sender_name, u.avatar_url as sender_avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($tab == 'sent') {
    $stmt = $db->prepare("
        SELECT m.*, u.full_name as receiver_name, u.avatar_url as receiver_avatar
        FROM messages m
        JOIN users u ON m.receiver_id = u.id
        WHERE m.sender_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Mark as read ---
if (isset($_GET['mark_read'])) {
    $msg_id = (int)$_GET['mark_read'];
    $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?")->execute([$msg_id, $user_id]);
    header('Location: messages.php?tab=inbox');
    exit();
}

// --- Fetch users for recipient dropdown ---
$users = [];
$stmt = $db->prepare("SELECT id, full_name FROM users WHERE is_active = 1 AND id != ? AND role IN ('member','pastor','elder','deacon') ORDER BY full_name");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Messages - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #e67e22;
        }
        body { background-color: #f8f9fa; padding-top: 56px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 767.98px) { .main-content { margin-left: 0; } }
        .message-card {
            background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer;
        }
        .unread { border-left: 4px solid var(--primary-color); background-color: #f0f8ff; }
        .message-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <h2 class="mb-4"><i class="fas fa-envelope me-2"></i>Messages</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab == 'inbox' ? 'active' : ''; ?>" href="?tab=inbox">Inbox</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab == 'sent' ? 'active' : ''; ?>" href="?tab=sent">Sent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tab == 'compose' ? 'active' : ''; ?>" href="?tab=compose">Compose</a>
                    </li>
                </ul>

                <!-- Compose Form -->
                <?php if ($tab == 'compose'): ?>
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success">Message sent successfully!</div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <div class="card p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="receiver_id" class="form-label">To</label>
                                <select class="form-select" id="receiver_id" name="receiver_id" required>
                                    <option value="">-- Select recipient --</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?php echo $u['id']; ?>" <?php echo (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $u['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($u['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Send</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Inbox / Sent Messages -->
                <?php if ($tab == 'inbox' || $tab == 'sent'): ?>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-card <?php echo ($tab == 'inbox' && !$msg['is_read']) ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>
                                            <?php 
                                            if ($tab == 'inbox') {
                                                echo 'From: ' . htmlspecialchars($msg['sender_name']);
                                            } else {
                                                echo 'To: ' . htmlspecialchars($msg['receiver_name']);
                                            }
                                            ?>
                                        </strong>
                                        <h6 class="mt-1"><?php echo htmlspecialchars($msg['subject'] ?? '(No subject)'); ?></h6>
                                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars(substr($msg['content'], 0, 150))); ?>...</p>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></small><br>
                                        <?php if ($tab == 'inbox' && !$msg['is_read']): ?>
                                            <a href="?tab=inbox&mark_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-success mt-1"><i class="fas fa-check"></i> Mark read</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-envelope-open-text fa-4x text-muted mb-3"></i>
                            <h4>No messages</h4>
                            <p class="text-muted">Your <?php echo $tab; ?> is empty.</p>
                            <?php if ($tab == 'inbox'): ?>
                                <a href="?tab=compose" class="btn btn-primary">Compose a Message</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>