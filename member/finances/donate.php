<?php
// ===================================================
// MEMBER - Make a Donation (Record Offline Giving)
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
    $amount         = trim($_POST['amount'] ?? '');
    $purpose        = trim($_POST['purpose'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes          = trim($_POST['notes'] ?? '');
    $recurring      = isset($_POST['recurring']) ? 1 : 0;
    $frequency      = $_POST['frequency'] ?? 'monthly';

    // Validate
    if (!is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid donation amount.';
    } elseif (!in_array($payment_method, ['cash','card','bank_transfer','mobile_money'])) {
        $error = 'Invalid payment method selected.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO donations (user_id, amount, donation_date, purpose, payment_method, notes, recurring, recurring_frequency, status) 
                                  VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $amount, $purpose, $payment_method, $notes, $recurring, $frequency]);

            $_SESSION['flash_message'] = 'Your donation has been recorded and is pending confirmation. Thank you for your generosity!';
            $_SESSION['flash_type'] = 'success';
            header('Location: donations.php');
            exit();
        } catch (Exception $e) {
            error_log("Donation insert error: " . $e->getMessage());
            $error = 'An error occurred while recording your donation. Please try again.';
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
    <title>Make a Donation - <?php echo SITE_NAME; ?></title>
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
        .donate-card {
            background: white; border-radius: 12px; padding: 2rem; max-width: 700px; margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .btn-donate { background-color: var(--primary-color); color: white; }
        .btn-donate:hover { background-color: var(--secondary-color); color: white; }
    </style>
</head>
<body>
    <?php require_once '../includes/member_topbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/member_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="donate-card">
                    <h2 class="mb-4"><i class="fas fa-hand-holding-heart me-2"></i>Record Your Donation</h2>
                    <p class="text-muted mb-4">Use this form to record an offering or tithe made via cash, mobile money, or bank transfer. All records will be verified by the finance team.</p>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (SZL) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="e.g. 100.00" required value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Tithe, Offering, Building Fund..." value="<?php echo htmlspecialchars($_POST['purpose'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="mobile_money" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'mobile_money') ? 'selected' : ''; ?>>Mobile Money</option>
                                <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'card') ? 'selected' : ''; ?>>Card</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any additional information..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="recurring" id="recurring" <?php echo isset($_POST['recurring']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="recurring">This is a recurring donation</label>
                        </div>
                        <div class="mb-3" id="frequencyGroup" style="display: <?php echo isset($_POST['recurring']) ? 'block' : 'none'; ?>;">
                            <label for="frequency" class="form-label">Recurring Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="weekly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo (!isset($_POST['frequency']) || (isset($_POST['frequency']) && $_POST['frequency'] == 'monthly')) ? 'selected' : ''; ?>>Monthly</option>
                                <option value="quarterly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                <option value="yearly" <?php echo (isset($_POST['frequency']) && $_POST['frequency'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-donate btn-lg"><i class="fas fa-donate me-1"></i> Submit Donation Record</button>
                            <a href="donations.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const recurringCheck = document.getElementById('recurring');
        const freqGroup = document.getElementById('frequencyGroup');
        recurringCheck.addEventListener('change', () => {
            freqGroup.style.display = recurringCheck.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>