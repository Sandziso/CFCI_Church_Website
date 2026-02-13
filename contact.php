<?php
// contact.php
require_once 'includes/header.php';

// Handle form submission
$messageSent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Security token validation failed. Please try again.';
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
        $department = htmlspecialchars(trim($_POST['department'] ?? 'general'));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($message)) {
            $errorMessage = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } else {
            try {
                // In a real application, you would:
                // 1. Save to database
                // 2. Send email notification
                // 3. Possibly send auto-responder
                
                // For demo purposes, we'll simulate saving to database
                $stmt = $conn->prepare("
                    INSERT INTO contact_messages (name, email, phone, subject, department, message, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                
                $stmt->execute([$name, $email, $phone, $subject, $department, $message, $ip_address, $user_agent]);
                
                $messageSent = true;
                
                // Clear form data
                unset($_POST);
                
            } catch (PDOException $e) {
                error_log("Contact form error: " . $e->getMessage());
                $errorMessage = 'Sorry, there was an error sending your message. Please try again later.';
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
                <h1 class="text-white">Contact Us</h1>
                <p class="text-white mb-0">We'd love to hear from you. Get in touch with any questions or prayer requests.</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if ($messageSent): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Thank you for your message!</strong> We have received it and will get back to you as soon as possible.
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
                <h2 class="mb-4">Get in Touch</h2>
                <p class="lead">Whether you have questions, need prayer, or want to visit, we're here to help.</p>
            </div>
        </div>

        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="contact-info-card p-4 rounded shadow h-100">
                    <h3 class="mb-4">Contact Information</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Our Location</h5>
                            <p>Ntunja Township behind William Pitcher College<br>Manzini, Eswatini<br>M200</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt text-primary"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Phone Numbers</h5>
                            <p>Church Office: +268 2505 5960<br>Pastor's Line: +268 7600 0001<br>Emergency: +268 7600 0002</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-envelope text-primary"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Email Addresses</h5>
                            <p>General: info@cfci.org.sz<br>Prayer: prayer@cfci.org.sz<br>Support: support@cfci.org.sz</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="far fa-clock text-primary"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Office Hours</h5>
                            <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM<br>Sunday: 7:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-5">
                        <h5 class="mb-3">Follow Us</h5>
                        <div class="d-flex gap-3">
                            <a href="#" class="social-link" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" target="_blank" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" target="_blank" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="social-link" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-card p-4 rounded shadow">
                    <h3 class="mb-4">Send Us a Message</h3>
                    
                    <form action="contact.php" method="POST" id="contactForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
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
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="general" <?php echo ($_POST['department'] ?? '') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="prayer" <?php echo ($_POST['department'] ?? '') === 'prayer' ? 'selected' : ''; ?>>Prayer Request</option>
                                <option value="pastoral" <?php echo ($_POST['department'] ?? '') === 'pastoral' ? 'selected' : ''; ?>>Pastoral Care</option>
                                <option value="ministry" <?php echo ($_POST['department'] ?? '') === 'ministry' ? 'selected' : ''; ?>>Ministry Information</option>
                                <option value="events" <?php echo ($_POST['department'] ?? '') === 'events' ? 'selected' : ''; ?>>Events & Activities</option>
                                <option value="giving" <?php echo ($_POST['department'] ?? '') === 'giving' ? 'selected' : ''; ?>>Giving & Donations</option>
                                <option value="technical" <?php echo ($_POST['department'] ?? '') === 'technical' ? 'selected' : ''; ?>>Technical Support</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" 
                                      required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please enter your message.</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" checked>
                                <label class="form-check-label" for="newsletter">
                                    Subscribe to our newsletter for updates and spiritual insights
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="g-recaptcha" data-sitekey="your-site-key-here"></div>
                            <div class="invalid-feedback">Please complete the captcha.</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Map Section -->
                <div class="map-section mt-5">
                    <div class="map-container rounded overflow-hidden shadow">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.7196023249124!2d31.3653158!3d-26.4764836!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1eb36021e1022839%3A0x6331908d1323f462!2sManzini%20Civic%20Centre!5e0!3m2!1sen!2szw!4v1687103250106!5m2!1sen!2szw" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="map-instructions mt-3">
                        <h5>Getting Here</h5>
                        <p class="mb-2"><strong>By Car:</strong> Ample parking available on church premises. Look for the CFCI sign.</p>
                        <p class="mb-2"><strong>By Public Transport:</strong> Take any bus to Manzini Civic Centre. We're located behind William Pitcher College.</p>
                        <p class="mb-0"><strong>Accessibility:</strong> Our facilities are wheelchair accessible with designated parking spots.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section mt-5 pt-5">
            <h3 class="text-center mb-5">Frequently Asked Questions</h3>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">What time are your services?</h5>
                        <p>Our main Sunday service is at 9:00 AM - 12:00 PM. We also have Wednesday prayer at 6:00 PM and Friday Bible study at 7:00 PM.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">Do you have programs for children?</h5>
                        <p>Yes! We have Children's Church during Sunday service for ages 3-12, and youth programs for teenagers on Fridays at 6:00 PM.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">How can I request prayer?</h5>
                        <p>You can submit prayer requests through our <a href="prayer-request.php">online form</a>, call our prayer line, or visit during office hours.</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h5 class="mb-3">Are visitors welcome?</h5>
                        <p>Absolutely! We welcome everyone to join us. Look for our Welcome Team when you arrive - they'll help you get settled.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="emergency-contact bg-light p-5 rounded mt-5 text-center">
            <i class="fas fa-phone-alt display-1 text-primary mb-4"></i>
            <h3 class="mb-3">Need Immediate Pastoral Care?</h3>
            <p class="lead mb-4">For urgent pastoral needs outside office hours, call our emergency line:</p>
            <div class="emergency-number display-4 fw-bold text-danger mb-4">+268 7600 0002</div>
            <p class="text-muted">Available 24/7 for urgent spiritual or emotional support.</p>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.contact-info-card {
    background: white;
    border: 1px solid #eee;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.contact-icon {
    width: 40px;
    height: 40px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.contact-icon i {
    font-size: 1.2rem;
}

.contact-details h5 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: #333;
}

.contact-details p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0;
    line-height: 1.5;
}

.social-link {
    width: 45px;
    height: 45px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    text-decoration: none;
    transition: var(--transition);
}

.social-link:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-3px);
}

.contact-form-card {
    background: white;
    border: 1px solid #eee;
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

.map-container {
    border: 1px solid #eee;
}

.map-instructions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #eee;
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

.emergency-contact {
    border: 2px solid #ff6b6b;
}

.emergency-number {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .contact-item {
        gap: 10px;
    }
    
    .contact-icon {
        width: 35px;
        height: 35px;
    }
    
    .contact-icon i {
        font-size: 1rem;
    }
    
    .emergency-number {
        font-size: 2.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(event) {
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
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = '+' + value;
            }
            e.target.value = value;
        });
    }
    
    // Auto-expand textarea
    const messageTextarea = document.getElementById('message');
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    
    // Department selection effects
    const departmentSelect = document.getElementById('department');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            // You could add specific logic based on department selection
            // For example, show/hide additional fields
            if (this.value === 'prayer') {
                // Show prayer-specific guidance
                const messageField = document.getElementById('message');
                if (messageField) {
                    messageField.placeholder = 'Please share your prayer request...';
                }
            }
        });
    }
    
    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Initialize Google Maps (if needed)
    // This is a placeholder for when you add custom map functionality
    function initMap() {
        // In a real application, you would initialize Google Maps API here
        console.log('Map initialization would go here');
    }
});
</script>

<!-- Load Google Maps API (remove if using iframe) -->
<!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script> -->

<?php
require_once 'includes/footer.php';
?>