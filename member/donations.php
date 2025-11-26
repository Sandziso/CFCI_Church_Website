<?php
// member/donations.php

// Start with minimal includes first
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

// Initialize database
try {
    $db = new ChurchDB($conn);
} catch (Exception $e) {
    error_log("Donations page error: " . $e->getMessage());
    $session->setFlash('error', 'Unable to load donations page. Please try again.');
}

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $purpose = $_POST['purpose'] ?? 'General Donation';
    $payment_method = $_POST['payment_method'] ?? 'card';
    $recurring = isset($_POST['recurring']) ? 1 : 0;
    $recurring_frequency = $_POST['recurring_frequency'] ?? 'monthly';
    
    // Validate amount
    if ($amount <= 0) {
        $session->setFlash('error', 'Please enter a valid donation amount.');
    } else {
        // Process based on payment method
        switch ($payment_method) {
            case 'card':
                // Process card payment (would integrate with payment gateway)
                $transaction_id = 'CARD_' . uniqid();
                $result = processCardPayment($amount, $purpose, $user_id);
                break;
                
            case 'eft':
                // Generate EFT reference
                $transaction_id = 'EFT_' . uniqid();
                $result = ['success' => true, 'message' => 'EFT payment instructions generated'];
                break;
                
            case 'mtn_mobile_money':
                $transaction_id = 'MTN_' . uniqid();
                $result = processMTNMobileMoney($amount, $user_id);
                break;
                
            case 'ecocash':
                $transaction_id = 'ECO_' . uniqid();
                $result = processEcoCash($amount, $user_id);
                break;
                
            case 'cash':
                $transaction_id = 'CASH_' . uniqid();
                $result = ['success' => true, 'message' => 'Cash donation recorded'];
                break;
                
            default:
                $result = ['success' => false, 'message' => 'Invalid payment method'];
        }
        
        if ($result['success']) {
            // Record donation in database
            $donation_result = $db->recordDonationWithDetails(
                $user_id, 
                $amount, 
                $purpose, 
                $payment_method, 
                $transaction_id,
                $recurring,
                $recurring_frequency
            );
            
            if ($donation_result) {
                $session->setFlash('success', $result['message']);
                header('Location: donation-history.php');
                exit;
            } else {
                $session->setFlash('error', 'Donation recorded but payment may not have been processed. Please contact support.');
            }
        } else {
            $session->setFlash('error', $result['message']);
        }
    }
}

// Get user's donation history
$donation_history = $db->getDonationsByUser($user_id, 5);

// Get total donations for the current year
$current_year = date('Y');
$yearly_total = $db->getUserYearlyDonations($user_id, $current_year);

// Mock payment processing functions (would be replaced with actual gateway integration)
function processCardPayment($amount, $purpose, $user_id) {
    // In a real implementation, this would integrate with a payment gateway like PayFast, PayPal, etc.
    // For now, we'll simulate a successful payment
    return ['success' => true, 'message' => 'Card payment processed successfully'];
}

function processMTNMobileMoney($amount, $user_id) {
    // Simulate MTN Mobile Money processing
    return ['success' => true, 'message' => 'MTN Mobile Money payment initiated. Please check your phone for confirmation.'];
}

function processEcoCash($amount, $user_id) {
    // Simulate EcoCash processing
    return ['success' => true, 'message' => 'EcoCash payment initiated. Please check your phone for confirmation.'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Donation - <?php echo SITE_NAME; ?></title>
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

        /* Donation Grid */
        .donation-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        @media (max-width: 1200px) {
            .donation-grid {
                grid-template-columns: 1fr;
            }
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

        /* Donation Form */
        .donation-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
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
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(44, 123, 229, 0.1);
        }

        .amount-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .amount-option {
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .amount-option:hover {
            border-color: var(--primary-blue);
            background: var(--light-blue);
        }

        .amount-option.selected {
            border-color: var(--primary-blue);
            background: var(--primary-blue);
            color: white;
        }

        .custom-amount {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .currency-symbol {
            font-weight: 600;
            color: var(--dark-text);
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-method {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .payment-method:hover {
            border-color: var(--primary-blue);
            background: var(--light-blue);
        }

        .payment-method.selected {
            border-color: var(--primary-blue);
            background: var(--light-blue);
        }

        .payment-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .payment-icon.card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .payment-icon.eft { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .payment-icon.mtn { background: linear-gradient(135deg, #ffcc00 0%, #ff9900 100%); }
        .payment-icon.ecocash { background: linear-gradient(135deg, #ff6600 0%, #ff3300 100%); }
        .payment-icon.cash { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .payment-method span {
            font-weight: 500;
            color: var(--dark-text);
        }

        /* Payment Details */
        .payment-details {
            display: none;
            margin-top: 20px;
            padding: 20px;
            border-radius: 12px;
            background: var(--light-gray);
            border-left: 4px solid var(--primary-blue);
        }

        .payment-details.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .payment-instructions {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .bank-details {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .bank-detail {
            display: flex;
            justify-content: between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .bank-detail:last-child {
            border-bottom: none;
        }

        .bank-detail .label {
            font-weight: 500;
            color: var(--dark-text);
        }

        .bank-detail .value {
            color: var(--light-text);
        }

        /* Recurring Donation */
        .recurring-option {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .recurring-frequency {
            display: none;
            margin-left: 30px;
        }

        .recurring-frequency.active {
            display: block;
        }

        /* Buttons */
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

        /* Donation History */
        .donation-history {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .donation-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .donation-item:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow);
        }

        .donation-info h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark-text);
        }

        .donation-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .donation-amount {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .donation-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-completed { background: var(--light-green); color: #2d5016; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #ffe6e6; color: #cc0000; }

        /* Yearly Total */
        .yearly-total {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .yearly-total .amount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .yearly-total .label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .amount-options {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .donation-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .donation-amount {
                align-self: flex-end;
            }
        }

        @media (max-width: 480px) {
            .amount-options {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php 
    $sidebar_file = '../includes/member_sidebar.php';
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    } else {
        echo "<!-- Sidebar file not found -->";
    }
    ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Make a Donation</h1>
                <p>Support the ministry and mission of CFCI</p>
            </div>
        </div>

        <!-- Donation Grid -->
        <div class="donation-grid">
            <!-- Left Column - Donation Form -->
            <div class="left-column">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-donate"></i> Donation Details</h2>
                    </div>
                    <div class="card-body">
                        <form class="donation-form" method="POST" id="donationForm">
                            <!-- Amount Selection -->
                            <div class="form-group">
                                <label>Donation Amount (SZL)</label>
                                <div class="amount-options">
                                    <div class="amount-option" data-amount="50">SZL 50</div>
                                    <div class="amount-option" data-amount="100">SZL 100</div>
                                    <div class="amount-option" data-amount="200">SZL 200</div>
                                    <div class="amount-option" data-amount="500">SZL 500</div>
                                    <div class="amount-option" data-amount="1000">SZL 1,000</div>
                                    <div class="amount-option custom">Other</div>
                                </div>
                                <div class="custom-amount" style="display: none;">
                                    <span class="currency-symbol">SZL</span>
                                    <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter amount" min="1" step="0.01" required>
                                </div>
                            </div>

                            <!-- Purpose Selection -->
                            <div class="form-group">
                                <label for="purpose">Donation Purpose</label>
                                <select name="purpose" id="purpose" class="form-control" required>
                                    <option value="General Donation">General Donation</option>
                                    <option value="Tithe">Tithe</option>
                                    <option value="Offering">Offering</option>
                                    <option value="Building Fund">Building Fund</option>
                                    <option value="Missions">Missions</option>
                                    <option value="Benevolence">Benevolence Fund</option>
                                    <option value="Youth Ministry">Youth Ministry</option>
                                    <option value="Children's Ministry">Children's Ministry</option>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="form-group">
                                <label>Payment Method</label>
                                <div class="payment-methods">
                                    <div class="payment-method" data-method="card">
                                        <div class="payment-icon card">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <span>Credit/Debit Card</span>
                                    </div>
                                    <div class="payment-method" data-method="eft">
                                        <div class="payment-icon eft">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <span>Bank Transfer (EFT)</span>
                                    </div>
                                    <div class="payment-method" data-method="mtn_mobile_money">
                                        <div class="payment-icon mtn">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <span>MTN Mobile Money</span>
                                    </div>
                                    <div class="payment-method" data-method="ecocash">
                                        <div class="payment-icon ecocash">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <span>EcoCash</span>
                                    </div>
                                    <div class="payment-method" data-method="cash">
                                        <div class="payment-icon cash">
                                            <i class="fas fa-money-bill"></i>
                                        </div>
                                        <span>Cash</span>
                                    </div>
                                </div>
                                <input type="hidden" name="payment_method" id="payment_method" value="card" required>
                            </div>

                            <!-- Payment Details (Dynamic based on method) -->
                            <div class="payment-details" id="paymentDetails">
                                <!-- Content will be populated by JavaScript -->
                            </div>

                            <!-- Recurring Donation Option -->
                            <div class="form-group">
                                <div class="recurring-option">
                                    <input type="checkbox" name="recurring" id="recurring">
                                    <label for="recurring">Make this a recurring donation</label>
                                </div>
                                <div class="recurring-frequency" id="recurringFrequency">
                                    <label for="recurring_frequency">Frequency</label>
                                    <select name="recurring_frequency" id="recurring_frequency" class="form-control">
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Process Donation
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Donation History & Info -->
            <div class="right-column">
                <!-- Yearly Total -->
                <div class="yearly-total">
                    <div class="amount">SZL <?php echo number_format($yearly_total, 2); ?></div>
                    <div class="label">Your Donations This Year</div>
                </div>

                <!-- Recent Donations -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-history"></i> Recent Donations</h2>
                        <div class="actions">
                            <a href="donation-history.php" class="btn btn-secondary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="donation-history">
                            <?php if (!empty($donation_history)): ?>
                                <?php foreach ($donation_history as $donation): ?>
                                    <div class="donation-item animate-in">
                                        <div class="donation-info">
                                            <h4><?php echo htmlspecialchars($donation['purpose']); ?></h4>
                                            <div class="donation-meta">
                                                <span><?php echo date('M j, Y', strtotime($donation['donation_date'])); ?></span>
                                                <span><?php echo ucfirst(str_replace('_', ' ', $donation['payment_method'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="donation-amount">
                                            SZL <?php echo number_format($donation['amount'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No donation history found</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Donation Information -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-info-circle"></i> About Donations</h2>
                    </div>
                    <div class="card-body">
                        <p>Your donations help support:</p>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>Church operations and ministries</li>
                            <li>Community outreach programs</li>
                            <li>Mission work locally and abroad</li>
                            <li>Building maintenance and improvements</li>
                            <li>Pastoral care and support</li>
                        </ul>
                        <p style="margin-top: 15px; font-style: italic;">
                            "Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver." - 2 Corinthians 9:7
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Amount Selection
        document.querySelectorAll('.amount-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.amount-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                if (this.classList.contains('custom')) {
                    // Show custom amount input
                    document.querySelector('.custom-amount').style.display = 'flex';
                    document.getElementById('amount').focus();
                } else {
                    // Hide custom amount and set value
                    document.querySelector('.custom-amount').style.display = 'none';
                    document.getElementById('amount').value = this.getAttribute('data-amount');
                }
            });
        });

        // Payment Method Selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Update hidden input
                const paymentMethod = this.getAttribute('data-method');
                document.getElementById('payment_method').value = paymentMethod;
                
                // Show payment details
                showPaymentDetails(paymentMethod);
            });
        });

        // Recurring Donation Toggle
        document.getElementById('recurring').addEventListener('change', function() {
            const frequencyDiv = document.getElementById('recurringFrequency');
            if (this.checked) {
                frequencyDiv.classList.add('active');
            } else {
                frequencyDiv.classList.remove('active');
            }
        });

        // Show payment details based on selected method
        function showPaymentDetails(method) {
            const detailsDiv = document.getElementById('paymentDetails');
            
            switch(method) {
                case 'card':
                    detailsDiv.innerHTML = `
                        <div class="payment-instructions">
                            <p>You will be redirected to our secure payment gateway to complete your card payment.</p>
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" class="form-control" placeholder="123">
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'eft':
                    detailsDiv.innerHTML = `
                        <div class="payment-instructions">
                            <p>Please use the following bank details for your EFT payment. Use your name as reference.</p>
                        </div>
                        <div class="bank-details">
                            <div class="bank-detail">
                                <span class="label">Bank Name:</span>
                                <span class="value">First National Bank Eswatini</span>
                            </div>
                            <div class="bank-detail">
                                <span class="label">Account Name:</span>
                                <span class="value">Christian Family Centre International</span>
                            </div>
                            <div class="bank-detail">
                                <span class="label">Account Number:</span>
                                <span class="value">6209 4567 8901</span>
                            </div>
                            <div class="bank-detail">
                                <span class="label">Branch Code:</span>
                                <span class="value">280 167</span>
                            </div>
                            <div class="bank-detail">
                                <span class="label">Reference:</span>
                                <span class="value">Your Name + DONATION</span>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'mtn_mobile_money':
                    detailsDiv.innerHTML = `
                        <div class="payment-instructions">
                            <p>To pay with MTN Mobile Money:</p>
                            <ol style="margin-left: 20px; margin-top: 10px;">
                                <li>Dial *144# on your MTN mobile phone</li>
                                <li>Select "Pay Bill"</li>
                                <li>Enter Merchant Code: <strong>CFCI2024</strong></li>
                                <li>Enter the donation amount</li>
                                <li>Confirm payment with your PIN</li>
                            </ol>
                        </div>
                        <div class="form-group">
                            <label for="mtn_number">MTN Mobile Number</label>
                            <input type="text" id="mtn_number" class="form-control" placeholder="76XXXXXXX">
                        </div>
                    `;
                    break;
                    
                case 'ecocash':
                    detailsDiv.innerHTML = `
                        <div class="payment-instructions">
                            <p>To pay with EcoCash:</p>
                            <ol style="margin-left: 20px; margin-top: 10px;">
                                <li>Dial *150*00# on your EcoCash line</li>
                                <li>Select "Pay a Merchant/Bill"</li>
                                <li>Enter Merchant Code: <strong>CFCI</strong></li>
                                <li>Enter the donation amount</li>
                                <li>Confirm payment with your PIN</li>
                            </ol>
                        </div>
                        <div class="form-group">
                            <label for="ecocash_number">EcoCash Number</label>
                            <input type="text" id="ecocash_number" class="form-control" placeholder="76XXXXXXX">
                        </div>
                    `;
                    break;
                    
                case 'cash':
                    detailsDiv.innerHTML = `
                        <div class="payment-instructions">
                            <p>For cash donations, please:</p>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li>Visit the church office during business hours</li>
                                <li>Place your donation in the offering basket during services</li>
                                <li>Request a receipt for your records</li>
                            </ul>
                            <p style="margin-top: 10px;"><strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM</p>
                        </div>
                    `;
                    break;
                    
                default:
                    detailsDiv.innerHTML = '';
            }
            
            detailsDiv.classList.add('active');
        }

        // Initialize with card payment details
        document.addEventListener('DOMContentLoaded', function() {
            showPaymentDetails('card');
            
            // Select first amount option by default
            document.querySelector('.amount-option').click();
        });

        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const amount = document.getElementById('amount').value;
            if (!amount || amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid donation amount.');
                return false;
            }
            
            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return false;
            }
            
            // Additional validation based on payment method
            if (paymentMethod === 'card') {
                const cardNumber = document.getElementById('card_number').value;
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                
                if (!cardNumber || !expiryDate || !cvv) {
                    e.preventDefault();
                    alert('Please complete all card details.');
                    return false;
                }
            }
            
            if (paymentMethod === 'mtn_mobile_money') {
                const mtnNumber = document.getElementById('mtn_number').value;
                if (!mtnNumber) {
                    e.preventDefault();
                    alert('Please enter your MTN mobile number.');
                    return false;
                }
            }
            
            if (paymentMethod === 'ecocash') {
                const ecocashNumber = document.getElementById('ecocash_number').value;
                if (!ecocashNumber) {
                    e.preventDefault();
                    alert('Please enter your EcoCash number.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>