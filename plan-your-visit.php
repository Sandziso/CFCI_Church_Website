<?php
// plan-your-visit.php
require_once 'includes/header.php';

// Handle pre-visit registration form submission
$registrationSubmitted = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_visit'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Security token validation failed. Please try again.';
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $visit_date = htmlspecialchars(trim($_POST['visit_date'] ?? ''));
        $visit_time = htmlspecialchars(trim($_POST['visit_time'] ?? ''));
        $party_size = (int)($_POST['party_size'] ?? 1);
        $first_time = isset($_POST['first_time']) ? 1 : 0;
        $age_group = htmlspecialchars(trim($_POST['age_group'] ?? ''));
        $special_needs = htmlspecialchars(trim($_POST['special_needs'] ?? ''));
        $prayer_needs = htmlspecialchars(trim($_POST['prayer_needs'] ?? ''));
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($visit_date) || empty($visit_time)) {
            $errorMessage = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif ($party_size < 1) {
            $errorMessage = 'Party size must be at least 1.';
        } else {
            try {
                // Save pre-visit registration to database
                $stmt = $conn->prepare("
                    INSERT INTO pre_visit_registrations (
                        name, email, phone, visit_date, visit_time, party_size, 
                        first_time, age_group, special_needs, prayer_needs, 
                        ip_address, user_agent, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                
                $stmt->execute([
                    $name, $email, $phone, $visit_date, $visit_time, $party_size,
                    $first_time, $age_group, $special_needs, $prayer_needs,
                    $ip_address, $user_agent
                ]);
                
                $registrationSubmitted = true;
                
                // Clear form data
                unset($_POST);
                
            } catch (PDOException $e) {
                error_log("Pre-visit registration error: " . $e->getMessage());
                $errorMessage = 'Sorry, there was an error submitting your registration. Please try again later.';
            }
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get upcoming service times
$upcomingServices = [];
try {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT title, event_date, start_time, end_time, location, description 
        FROM events 
        WHERE event_date >= ? 
        AND category_id = 1 
        AND is_published = 1 
        AND is_active = 1 
        ORDER BY event_date ASC 
        LIMIT 4
    ");
    $stmt->execute([$today]);
    $upcomingServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching upcoming services: " . $e->getMessage());
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('assets/images/church-exterior.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Plan Your Visit</h1>
                <p class="text-white mb-0">We're excited to welcome you! Find everything you need to know about visiting CFCI.</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Plan Your Visit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if ($registrationSubmitted): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Thank you for letting us know you're coming!</strong> We've received your registration and look forward to welcoming you. A confirmation email has been sent to you.
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

        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Welcome to CFCI!</h2>
                <p class="lead">Whether you're new to faith or looking for a church home, we're glad you're here. At CFCI, you'll find a warm, family atmosphere where everyone is welcome.</p>
            </div>
        </div>

        <!-- Service Times & Location -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="info-card p-4 rounded shadow">
                    <h3 class="mb-4 text-center">Service Times & Location</h3>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="service-times">
                                <h4 class="mb-3"><i class="fas fa-church me-2 text-primary"></i>Regular Services</h4>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <strong>Sunday Worship:</strong> 9:00 AM - 12:00 PM
                                        <small class="text-muted d-block">Main Sanctuary</small>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Sunday School:</strong> 10:30 AM - 11:30 AM
                                        <small class="text-muted d-block">All ages</small>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Wednesday Prayer:</strong> 6:00 PM - 7:30 PM
                                        <small class="text-muted d-block">Fellowship Hall</small>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Friday Bible Study:</strong> 6:00 PM - 7:30 PM
                                        <small class="text-muted d-block">Main Sanctuary</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="location-info">
                                <h4 class="mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Our Location</h4>
                                <p class="mb-2">
                                    <strong>Address:</strong><br>
                                    Ntunja Township behind William Pitcher College<br>
                                    Manzini, Eswatini<br>
                                    M200
                                </p>
                                <p class="mb-2">
                                    <strong>Phone:</strong> +268 2505 5960
                                </p>
                                <p class="mb-0">
                                    <strong>Email:</strong> info@cfci.org.sz
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- What to Expect -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="text-center mb-4">What to Expect</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="expectation-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-clock fa-3x text-primary"></i>
                            </div>
                            <h5>Service Length</h5>
                            <p>Our Sunday service typically lasts about 2 hours. You're welcome to stay for fellowship and refreshments afterward.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="expectation-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-child fa-3x text-primary"></i>
                            </div>
                            <h5>Children's Ministry</h5>
                            <p>We have age-appropriate programs for children during the service. Check-in begins 15 minutes before service starts.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="expectation-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-tshirt fa-3x text-primary"></i>
                            </div>
                            <h5>Dress Code</h5>
                            <p>Come as you are! We have people dressed in everything from traditional attire to casual clothing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Your Visit Form -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="registration-card p-4 rounded shadow">
                    <h3 class="text-center mb-4">Let Us Know You're Coming</h3>
                    <p class="text-center mb-4">Fill out this form to help us prepare for your visit. We'll have a welcome packet ready for you!</p>
                    
                    <form action="plan-your-visit.php" method="POST" id="visitRegistrationForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="register_visit" value="1">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       required>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="+268 760 0000">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="visit_date" class="form-label">When are you planning to visit? *</label>
                                <input type="date" class="form-control" id="visit_date" name="visit_date" 
                                       value="<?php echo htmlspecialchars($_POST['visit_date'] ?? ''); ?>"
                                       min="<?php echo date('Y-m-d'); ?>"
                                       required>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="visit_time" class="form-label">Preferred Service Time *</label>
                                <select class="form-select" id="visit_time" name="visit_time" required>
                                    <option value="">Select a time</option>
                                    <option value="9:00 AM" <?php echo ($_POST['visit_time'] ?? '') === '9:00 AM' ? 'selected' : ''; ?>>Sunday 9:00 AM (Main Service)</option>
                                    <option value="6:00 PM" <?php echo ($_POST['visit_time'] ?? '') === '6:00 PM' ? 'selected' : ''; ?>>Wednesday 6:00 PM (Prayer)</option>
                                    <option value="6:00 PM" <?php echo ($_POST['visit_time'] ?? '') === '6:00 PM' ? 'selected' : ''; ?>>Friday 6:00 PM (Bible Study)</option>
                                </select>
                                <div class="invalid-feedback">Please select a service time.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="party_size" class="form-label">Number of People</label>
                                <select class="form-select" id="party_size" name="party_size">
                                    <option value="1" <?php echo ($_POST['party_size'] ?? 1) == 1 ? 'selected' : ''; ?>>1 person</option>
                                    <option value="2" <?php echo ($_POST['party_size'] ?? 1) == 2 ? 'selected' : ''; ?>>2 people</option>
                                    <option value="3" <?php echo ($_POST['party_size'] ?? 1) == 3 ? 'selected' : ''; ?>>3 people</option>
                                    <option value="4" <?php echo ($_POST['party_size'] ?? 1) == 4 ? 'selected' : ''; ?>>4 people</option>
                                    <option value="5" <?php echo ($_POST['party_size'] ?? 1) == 5 ? 'selected' : ''; ?>>5+ people</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="age_group" name="age_group">
                                    <option value="">Select age group</option>
                                    <option value="single" <?php echo ($_POST['age_group'] ?? '') === 'single' ? 'selected' : ''; ?>>Single Adult</option>
                                    <option value="couple" <?php echo ($_POST['age_group'] ?? '') === 'couple' ? 'selected' : ''; ?>>Married Couple</option>
                                    <option value="family" <?php echo ($_POST['age_group'] ?? '') === 'family' ? 'selected' : ''; ?>>Family with Children</option>
                                    <option value="senior" <?php echo ($_POST['age_group'] ?? '') === 'senior' ? 'selected' : ''; ?>>Senior Adult</option>
                                    <option value="student" <?php echo ($_POST['age_group'] ?? '') === 'student' ? 'selected' : ''; ?>>Student/Youth</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="first_time" name="first_time" 
                                           <?php echo isset($_POST['first_time']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="first_time">
                                        This will be my first time visiting CFCI
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_needs" class="form-label">Special Needs or Accommodations</label>
                            <textarea class="form-control" id="special_needs" name="special_needs" rows="2" 
                                      placeholder="Wheelchair access, hearing assistance, etc."><?php echo htmlspecialchars($_POST['special_needs'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prayer_needs" class="form-label">Prayer Needs (Optional)</label>
                            <textarea class="form-control" id="prayer_needs" name="prayer_needs" rows="2" 
                                      placeholder="How can we pray for you?"><?php echo htmlspecialchars($_POST['prayer_needs'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" checked>
                                <label class="form-check-label" for="newsletter">
                                    Send me updates about CFCI events and ministries
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-calendar-check me-2"></i>Submit My Visit Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Visitor FAQs -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center mb-4">Frequently Asked Questions</h3>
                <div class="accordion" id="visitorFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Where should I park?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#visitorFAQ">
                            <div class="accordion-body">
                                We have ample parking available on our church premises. Look for visitor parking signs near the main entrance. Parking attendants will be available to assist you.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What's available for my children?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                            <div class="accordion-body">
                                We have excellent children's programs for all ages:
                                <ul>
                                    <li><strong>Nursery (0-2 years):</strong> Available during all services</li>
                                    <li><strong>Preschool (3-5 years):</strong> Fun, age-appropriate Bible lessons</li>
                                    <li><strong>Elementary (6-12 years):</strong> Interactive Bible teaching and activities</li>
                                    <li><strong>Youth (13-18 years):</strong> Join the main service or youth-specific events</li>
                                </ul>
                                All children's ministry workers are background-checked and trained.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Will I be singled out as a visitor?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                            <div class="accordion-body">
                                No, you won't be put on the spot. We do have a visitor reception area where you can get more information and a welcome gift if you'd like. During the service, we'll ask everyone to greet each other, but you won't be asked to stand up or introduce yourself.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What's the music like?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                            <div class="accordion-body">
                                Our worship includes a blend of contemporary worship songs and traditional hymns. We have a full worship band and choir. Lyrics are projected on screens so everyone can participate.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                What happens after the service?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                            <div class="accordion-body">
                                After the service, we invite everyone to stay for fellowship and refreshments in our courtyard. This is a great time to meet people and ask questions. Our pastors and leaders are available to pray with you if needed.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Steps Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="steps-card bg-light p-5 rounded text-center">
                    <h3 class="mb-4">Your Next Steps</h3>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="step-number mb-3">1</div>
                            <h5>Plan Your Visit</h5>
                            <p>Fill out the form above to let us know you're coming.</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="step-number mb-3">2</div>
                            <h5>Join Us Sunday</h5>
                            <p>Arrive 15-20 minutes early to get parked and settled.</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="step-number mb-3">3</div>
                            <h5>Explore Community</h5>
                            <p>Visit our Welcome Center to learn about next steps.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center mb-4">Find Us Here</h3>
                <div class="map-container rounded overflow-hidden shadow">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.7196023249124!2d31.3653158!3d-26.4764836!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1eb36021e1022839%3A0x6331908d1323f462!2sManzini%20Civic%20Centre!5e0!3m2!1sen!2szw!4v1687103250106!5m2!1sen!2szw" 
                        width="100%" 
                        height="400" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="CFCI Location Map">
                    </iframe>
                </div>
                <div class="directions-info mt-3">
                    <h5>Directions</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>From Mbabane:</strong> Take MR3 south for approximately 30 minutes. Turn right at William Pitcher College. We're located behind the college.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>From Nhlangano:</strong> Take MR16 north toward Manzini. Follow signs for Ntunja Township. Look for our church signs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.info-card {
    background: white;
    border: 1px solid #eee;
    border-left: 5px solid var(--primary);
}

.expectation-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 10px;
    transition: var(--transition);
}

.expectation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.registration-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.steps-card {
    border: 2px solid var(--primary);
}

.step-number {
    width: 60px;
    height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0 auto;
}

.map-container {
    border: 1px solid #eee;
}

.directions-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #eee;
}

.accordion-button {
    font-weight: 600;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(26, 82, 118, 0.1);
    color: var(--primary);
}

.service-times li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.service-times li:last-child {
    border-bottom: none;
}

.form-label {
    font-weight: 500;
    color: #333;
}

.form-control, .form-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(26, 82, 118, 0.25);
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .expectation-card {
        margin-bottom: 20px;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const visitForm = document.getElementById('visitRegistrationForm');
    if (visitForm) {
        visitForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            this.classList.add('was-validated');
            
            // Additional custom validation
            const email = document.getElementById('email');
            if (email.value && !isValidEmail(email.value)) {
                email.classList.add('is-invalid');
                email.nextElementSibling.textContent = 'Please enter a valid email address.';
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Date validation
            const visitDate = document.getElementById('visit_date');
            if (visitDate.value) {
                const selectedDate = new Date(visitDate.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    visitDate.classList.add('is-invalid');
                    visitDate.nextElementSibling.textContent = 'Please select a future date.';
                    event.preventDefault();
                    event.stopPropagation();
                }
            }
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (!value.startsWith('+')) {
                    value = '+268' + value;
                }
            }
            e.target.value = formatPhoneNumber(value);
        });
    }
    
    // Set minimum date for visit date
    const visitDateInput = document.getElementById('visit_date');
    if (visitDateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        visitDateInput.min = tomorrow.toISOString().split('T')[0];
        
        // Set default date to next Sunday
        const nextSunday = getNextSunday();
        if (!visitDateInput.value) {
            visitDateInput.value = nextSunday.toISOString().split('T')[0];
        }
    }
    
    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Phone number formatting helper
    function formatPhoneNumber(phone) {
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.startsWith('268')) {
            const match = cleaned.match(/^268(\d{3})(\d{3})(\d{3})$/);
            if (match) {
                return '+268 ' + match[1] + ' ' + match[2] + ' ' + match[3];
            }
        }
        return phone;
    }
    
    // Get next Sunday date
    function getNextSunday() {
        const today = new Date();
        const dayOfWeek = today.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const daysUntilSunday = dayOfWeek === 0 ? 7 : 0; // If today is Sunday, return next Sunday
        const nextSunday = new Date(today);
        nextSunday.setDate(today.getDate() + (7 - dayOfWeek + daysUntilSunday));
        return nextSunday;
    }
    
    // Accordion animation
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(item => {
        item.addEventListener('shown.bs.collapse', function() {
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>