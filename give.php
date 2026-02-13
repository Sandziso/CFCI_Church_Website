<?php
// give.php
require_once 'includes/header.php';

// Initialize variables
$donationAmount = '';
$donationType = 'tithe';
$frequency = 'once';
$errorMessage = '';
$successMessage = '';

// Handle form submission (simplified - in real app, integrate with payment gateway)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Security token validation failed. Please try again.';
    } else {
        // Get form data
        $donationAmount = floatval($_POST['amount'] ?? 0);
        $donationType = $_POST['type'] ?? 'tithe';
        $frequency = $_POST['frequency'] ?? 'once';
        $customAmount = $_POST['custom_amount'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validate amount
        if ($donationAmount <= 0 && empty($customAmount)) {
            $errorMessage = 'Please enter a valid donation amount.';
        } elseif (!empty($customAmount) && floatval($customAmount) <= 0) {
            $errorMessage = 'Please enter a valid custom amount.';
        } else {
            // Use custom amount if provided
            if (!empty($customAmount)) {
                $donationAmount = floatval($customAmount);
            }
            
            // In a real application, this would:
            // 1. Process payment via payment gateway
            // 2. Save to database
            // 3. Send receipt email
            // 4. Redirect to payment page
            
            // For demo purposes, we'll simulate a successful donation
            try {
                // Generate a transaction ID
                $transactionId = 'MTN_' . bin2hex(random_bytes(6));
                
                // Save to database
                $userId = $_SESSION['user_id'] ?? null;
                
                $stmt = $conn->prepare("
                    INSERT INTO donations (user_id, amount, purpose, payment_method, transaction_id, status, donation_date)
                    VALUES (?, ?, ?, 'mobile_money', ?, 'completed', NOW())
                ");
                
                // Map donation type to purpose
                $purposeMap = [
                    'tithe' => 'Tithe',
                    'offering' => 'Offering',
                    'building' => 'Building Fund',
                    'missions' => 'Missions',
                    'benevolence' => 'Benevolence Fund',
                    'general' => 'General Donation'
                ];
                
                $purpose = $purposeMap[$donationType] ?? 'General Donation';
                
                $stmt->execute([$userId, $donationAmount, $purpose, $transactionId]);
                
                // Set success message
                $successMessage = "Thank you for your generous donation of E" . number_format($donationAmount, 2) . "! Transaction ID: " . $transactionId;
                
                // Clear form
                $donationAmount = '';
                $customAmount = '';
                $notes = '';
                
            } catch (PDOException $e) {
                error_log("Donation error: " . $e->getMessage());
                $errorMessage = 'Sorry, there was an error processing your donation. Please try again.';
            }
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Give Generously</h1>
                <p class="text-white mb-0">Partner with us in advancing God's kingdom through your generosity</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Give</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if ($successMessage): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Donation Successful!</strong> <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php elseif ($errorMessage): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Biblical Giving</h2>
                <p class="lead">"Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver." - 2 Corinthians 9:7</p>
            </div>
        </div>

        <div class="row">
            <!-- Giving Options -->
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="giving-card p-4 rounded shadow">
                    <h3 class="mb-4">Make a Donation</h3>
                    
                    <form action="give.php" method="POST" id="donationForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <!-- Donation Type -->
                        <div class="mb-4">
                            <h5 class="mb-3">What would you like to give to?</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="tithe" value="tithe" 
                                           <?php echo ($donationType === 'tithe') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="tithe">
                                        <i class="fas fa-church display-6 mb-2"></i>
                                        <div>Tithe</div>
                                        <small class="text-muted">10% of income</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="offering" value="offering" 
                                           <?php echo ($donationType === 'offering') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="offering">
                                        <i class="fas fa-hand-holding-heart display-6 mb-2"></i>
                                        <div>Offering</div>
                                        <small class="text-muted">Freewill giving</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="building" value="building" 
                                           <?php echo ($donationType === 'building') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="building">
                                        <i class="fas fa-home display-6 mb-2"></i>
                                        <div>Building Fund</div>
                                        <small class="text-muted">Facility expansion</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="missions" value="missions" 
                                           <?php echo ($donationType === 'missions') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="missions">
                                        <i class="fas fa-globe display-6 mb-2"></i>
                                        <div>Missions</div>
                                        <small class="text-muted">Global outreach</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="benevolence" value="benevolence" 
                                           <?php echo ($donationType === 'benevolence') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="benevolence">
                                        <i class="fas fa-hands-helping display-6 mb-2"></i>
                                        <div>Benevolence</div>
                                        <small class="text-muted">Helping those in need</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="type" id="general" value="general" 
                                           <?php echo ($donationType === 'general') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3" for="general">
                                        <i class="fas fa-gift display-6 mb-2"></i>
                                        <div>General Fund</div>
                                        <small class="text-muted">Church operations</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Donation Amount -->
                        <div class="mb-4">
                            <h5 class="mb-3">Select Amount</h5>
                            <div class="row mb-3">
                                <div class="col-6 col-md-3 mb-2">
                                    <input type="radio" class="btn-check" name="amount" id="amount50" value="50" 
                                           <?php echo ($donationAmount == 50) ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100" for="amount50">E50</label>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <input type="radio" class="btn-check" name="amount" id="amount100" value="100" 
                                           <?php echo ($donationAmount == 100) ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100" for="amount100">E100</label>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <input type="radio" class="btn-check" name="amount" id="amount200" value="200" 
                                           <?php echo ($donationAmount == 200) ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100" for="amount200">E200</label>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <input type="radio" class="btn-check" name="amount" id="amount500" value="500" 
                                           <?php echo ($donationAmount == 500) ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100" for="amount500">E500</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="custom_amount" class="form-label">Or enter custom amount (E)</label>
                                <input type="number" class="form-control" id="custom_amount" name="custom_amount" 
                                       value="<?php echo htmlspecialchars($_POST['custom_amount'] ?? ''); ?>" 
                                       min="1" step="0.01" placeholder="Enter amount">
                            </div>
                        </div>
                        
                        <!-- Frequency -->
                        <div class="mb-4">
                            <h5 class="mb-3">Frequency</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="frequency" id="once" value="once" 
                                           <?php echo ($frequency === 'once') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-info w-100 h-100 p-3" for="once">
                                        <i class="fas fa-calendar-day display-6 mb-2"></i>
                                        <div>One Time</div>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="frequency" id="weekly" value="weekly" 
                                           <?php echo ($frequency === 'weekly') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-info w-100 h-100 p-3" for="weekly">
                                        <i class="fas fa-calendar-week display-6 mb-2"></i>
                                        <div>Weekly</div>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="frequency" id="monthly" value="monthly" 
                                           <?php echo ($frequency === 'monthly') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-info w-100 h-100 p-3" for="monthly">
                                        <i class="fas fa-calendar-alt display-6 mb-2"></i>
                                        <div>Monthly</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="mb-4">
                            <h5 class="mb-3">Payment Method</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="mtn" value="mtn" checked>
                                    <label class="btn btn-outline-success w-100 h-100 p-3" for="mtn">
                                        <i class="fas fa-mobile-alt display-6 mb-2"></i>
                                        <div>MTN Mobile Money</div>
                                        <small class="text-muted">+268 2505 5960</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="bank" value="bank" disabled>
                                    <label class="btn btn-outline-secondary w-100 h-100 p-3" for="bank">
                                        <i class="fas fa-university display-6 mb-2"></i>
                                        <div>Bank Transfer</div>
                                        <small class="text-muted">Coming Soon</small>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="card" value="card" disabled>
                                    <label class="btn btn-outline-secondary w-100 h-100 p-3" for="card">
                                        <i class="fas fa-credit-card display-6 mb-2"></i>
                                        <div>Credit/Debit Card</div>
                                        <small class="text-muted">Coming Soon</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any comments or dedication for your donation"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and understand that donations are non-refundable.
                                </label>
                                <div class="invalid-feedback">You must agree to the terms before donating.</div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" name="donate" class="btn btn-primary btn-lg">
                                <i class="fas fa-heart me-2"></i>Give Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Giving Information -->
            <div class="col-lg-4">
                <!-- Stewardship Principles -->
                <div class="info-card mb-4 p-4 rounded shadow">
                    <h4 class="mb-3">Biblical Stewardship</h4>
                    <div class="principle mb-3">
                        <h5><i class="fas fa-seedling text-primary me-2"></i>Sow Generously</h5>
                        <p class="small mb-0">"Remember this: Whoever sows sparingly will also reap sparingly, and whoever sows generously will also reap generously." - 2 Corinthians 9:6</p>
                    </div>
                    <div class="principle mb-3">
                        <h5><i class="fas fa-heart text-primary me-2"></i>Give Cheerfully</h5>
                        <p class="small mb-0">"Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver." - 2 Corinthians 9:7</p>
                    </div>
                    <div class="principle">
                        <h5><i class="fas fa-shield-alt text-primary me-2"></i>Trust God's Provision</h5>
                        <p class="small mb-0">"And my God will meet all your needs according to the riches of his glory in Christ Jesus." - Philippians 4:19</p>
                    </div>
                </div>
                
                <!-- How Your Giving Helps -->
                <div class="info-card mb-4 p-4 rounded shadow">
                    <h4 class="mb-3">How Your Giving Helps</h4>
                    <div class="help-item d-flex align-items-center mb-3">
                        <div class="help-icon me-3">
                            <i class="fas fa-church text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Church Operations</h6>
                            <p class="small mb-0">Facility maintenance, utilities, and staff support</p>
                        </div>
                    </div>
                    <div class="help-item d-flex align-items-center mb-3">
                        <div class="help-icon me-3">
                            <i class="fas fa-utensils text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Community Outreach</h6>
                            <p class="small mb-0">Feeding programs, medical camps, and disaster relief</p>
                        </div>
                    </div>
                    <div class="help-item d-flex align-items-center mb-3">
                        <div class="help-icon me-3">
                            <i class="fas fa-graduation-cap text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Discipleship Programs</h6>
                            <p class="small mb-0">Bible studies, youth camps, and leadership training</p>
                        </div>
                    </div>
                    <div class="help-item d-flex align-items-center">
                        <div class="help-icon me-3">
                            <i class="fas fa-globe text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Missions Support</h6>
                            <p class="small mb-0">Supporting missionaries and church planting efforts</p>
                        </div>
                    </div>
                </div>
                
                <!-- Other Ways to Give -->
                <div class="info-card p-4 rounded shadow">
                    <h4 class="mb-3">Other Ways to Give</h4>
                    <div class="mb-3">
                        <h6><i class="fas fa-mobile-alt me-2 text-primary"></i>Mobile Money</h6>
                        <p class="small mb-2">Send to: <strong>+268 2505 5960</strong></p>
                        <p class="small text-muted">Use your name as reference</p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-university me-2 text-primary"></i>Bank Transfer</h6>
                        <p class="small mb-2">Bank: Standard Bank<br>Account: CFCI Church<br>Account #: 123456789</p>
                    </div>
                    <div>
                        <h6><i class="fas fa-hand-holding-usd me-2 text-primary"></i>In Person</h6>
                        <p class="small mb-0">Drop your offering in the giving boxes during service or visit the church office.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Transparency -->
        <div class="transparency-section bg-light p-5 rounded mt-5">
            <h3 class="text-center mb-4">Financial Transparency</h3>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="transparency-item">
                        <div class="display-4 fw-bold text-primary mb-2">90%</div>
                        <h5>Ministry & Outreach</h5>
                        <p class="small">Directly funds church programs and community service</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="transparency-item">
                        <div class="display-4 fw-bold text-primary mb-2">8%</div>
                        <h5>Administration</h5>
                        <p class="small">Covers operational costs and staff support</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="transparency-item">
                        <div class="display-4 fw-bold text-primary mb-2">2%</div>
                        <h5>Fundraising</h5>
                        <p class="small">Supports giving platform maintenance</p>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <a href="financial-reports.php" class="btn btn-outline-primary">View Financial Reports</a>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="testimonials-section mt-5 pt-5">
            <h3 class="text-center mb-5">Stories of Impact</h3>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow">
                        <div class="testimonial-content mb-3">
                            <p>"Since we started tithing faithfully, God has opened doors we never imagined. Our business has grown, and our family is blessed."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>John & Sarah D.</strong>
                            <span class="text-muted">Members for 5 years</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow">
                        <div class="testimonial-content mb-3">
                            <p>"Giving to the benevolence fund allowed us to help a family in our community during a crisis. It's amazing to see God work through generosity."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>Thomas M.</strong>
                            <span class="text-muted">Outreach Team Leader</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow">
                        <div class="testimonial-content mb-3">
                            <p>"The building fund contributions helped us renovate our children's ministry space. Now we have a safe, beautiful place for our kids to learn about Jesus."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>Grace N.</strong>
                            <span class="text-muted">Children's Ministry Volunteer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="faq-section mt-5 pt-5">
            <h3 class="text-center mb-5">Giving FAQ</h3>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">Are donations tax-deductible?</h5>
                        <p>Yes, CFCI is a registered non-profit organization. All donations are tax-deductible. We issue annual donation statements for tax purposes.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">How do I get a receipt?</h5>
                        <p>Automated receipts are sent via email for online donations. For cash or check donations, receipts are available at the church office.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">Can I cancel recurring donations?</h5>
                        <p>Yes, you can manage or cancel recurring donations through your member portal or by contacting the finance office.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">Is my payment information secure?</h5>
                        <p>Absolutely. We use industry-standard encryption and security measures to protect your financial information.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="support-section text-center py-5 mt-5">
            <h3 class="mb-4">Need Help With Giving?</h3>
            <p class="lead mb-4">Our finance team is here to assist you with any questions about giving.</p>
            <div class="cta-buttons">
                <a href="contact.php" class="btn btn-primary btn-lg me-3"><i class="fas fa-envelope me-2"></i>Email Support</a>
                <a href="tel:+26825055960" class="btn btn-outline-primary btn-lg"><i class="fas fa-phone me-2"></i>Call +268 2505 5960</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.giving-card {
    background: white;
    border: 1px solid #eee;
}

.btn-check:checked + .btn-outline-primary {
    background-color: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-check:checked + .btn-outline-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-check:checked + .btn-outline-info {
    background-color: #0dcaf0;
    color: white;
    border-color: #0dcaf0;
}

.btn-check:checked + .btn-outline-success {
    background-color: #198754;
    color: white;
    border-color: #198754;
}

.btn-check:disabled + .btn-outline-secondary {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-check + .btn {
    transition: var(--transition);
}

.btn-check + .btn:hover {
    transform: translateY(-2px);
}

.info-card {
    background: white;
    border: 1px solid #eee;
}

.principle h5 {
    font-size: 1rem;
    color: var(--primary);
}

.principle p {
    font-size: 0.85rem;
}

.help-icon {
    width: 40px;
    height: 40px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.help-icon i {
    font-size: 1.2rem;
}

.transparency-item {
    padding: 20px;
}

.transparency-item .display-4 {
    font-size: 3rem;
}

.testimonial-card {
    background: white;
    border: 1px solid #eee;
    height: 100%;
}

.testimonial-content p {
    font-style: italic;
    color: #555;
}

.testimonial-author {
    border-top: 1px solid #eee;
    padding-top: 15px;
}

.testimonial-author strong {
    display: block;
}

.testimonial-author span {
    font-size: 0.85rem;
}

.faq-item {
    padding: 20px;
    background: white;
    border: 1px solid #eee;
    border-radius: 8px;
    height: 100%;
}

.faq-item h5 {
    color: var(--primary);
}

.support-section {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
    border-radius: 10px;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .btn-check + .btn {
        padding: 10px 5px;
        font-size: 0.9rem;
    }
    
    .transparency-item .display-4 {
        font-size: 2.5rem;
    }
    
    .help-icon {
        width: 35px;
        height: 35px;
    }
    
    .help-icon i {
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const donationForm = document.getElementById('donationForm');
    if (donationForm) {
        donationForm.addEventListener('submit', function(event) {
            // Check if amount is selected
            const amountSelected = document.querySelector('input[name="amount"]:checked');
            const customAmount = document.getElementById('custom_amount').value;
            
            if (!amountSelected && !customAmount) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show error
                const customAmountField = document.getElementById('custom_amount');
                customAmountField.classList.add('is-invalid');
                customAmountField.focus();
                
                // Create error message if it doesn't exist
                let errorDiv = customAmountField.nextElementSibling;
                if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    customAmountField.parentNode.insertBefore(errorDiv, customAmountField.nextSibling);
                }
                errorDiv.textContent = 'Please select an amount or enter a custom amount.';
            }
            
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
    
    // Amount selection logic
    const amountRadios = document.querySelectorAll('input[name="amount"]');
    const customAmountInput = document.getElementById('custom_amount');
    
    amountRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                customAmountInput.value = '';
                customAmountInput.classList.remove('is-invalid');
            }
        });
    });
    
    customAmountInput.addEventListener('input', function() {
        // Uncheck amount radios when custom amount is entered
        amountRadios.forEach(radio => {
            radio.checked = false;
        });
        
        // Clear validation
        this.classList.remove('is-invalid');
    });
    
    // Frequency selection effects
    const frequencyRadios = document.querySelectorAll('input[name="frequency"]');
    frequencyRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update form message based on frequency
            const submitBtn = document.querySelector('button[type="submit"]');
            if (this.value === 'once') {
                submitBtn.innerHTML = '<i class="fas fa-heart me-2"></i>Give Now';
            } else if (this.value === 'weekly') {
                submitBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Set Up Weekly Giving';
            } else if (this.value === 'monthly') {
                submitBtn.innerHTML = '<i class="fas fa-calendar-alt me-2"></i>Set Up Monthly Giving';
            }
        });
    });
    
    // Donation type effects
    const typeRadios = document.querySelectorAll('input[name="type"]');
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // You could add specific messages or actions based on donation type
            console.log('Selected donation type:', this.value);
        });
    });
    
    // Calculate tithe if user selects tithe and enters income
    // This is a placeholder for more advanced functionality
    const calculateTitheBtn = document.createElement('button');
    calculateTitheBtn.type = 'button';
    calculateTitheBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
    calculateTitheBtn.innerHTML = '<i class="fas fa-calculator me-1"></i>Calculate 10%';
    calculateTitheBtn.style.display = 'none';
    
    // Insert after custom amount field
    const customAmountGroup = customAmountInput.closest('.mb-3');
    if (customAmountGroup) {
        customAmountGroup.appendChild(calculateTitheBtn);
        
        // Show/hide calculate button based on donation type
        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'tithe') {
                    calculateTitheBtn.style.display = 'block';
                } else {
                    calculateTitheBtn.style.display = 'none';
                }
            });
        });
        
        // Calculate tithe functionality
        calculateTitheBtn.addEventListener('click', function() {
            const income = prompt('Enter your monthly income (E):');
            if (income && !isNaN(income) && parseFloat(income) > 0) {
                const titheAmount = parseFloat(income) * 0.1;
                customAmountInput.value = titheAmount.toFixed(2);
                
                // Uncheck fixed amount radios
                amountRadios.forEach(radio => {
                    radio.checked = false;
                });
            }
        });
    }
    
    // Payment method info
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'mtn') {
                // Show MTN specific instructions
                alert('Please send your donation to MTN Mobile Money number: +268 2505 5960\n\nUse your name as reference.');
            }
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>