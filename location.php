<?php
require_once 'includes/header.php';

// Fetch location data from settings
$church_info = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('church_address', 'church_phone', 'church_email', 'service_times')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $church_info = [
        'address' => $settings['church_address'] ?? 'Ntunja Township behind William Pitcher College, Manzini, Eswatini',
        'phone' => $settings['church_phone'] ?? '+268 2505 5960',
        'email' => $settings['church_email'] ?? 'info@cfci.org.sz',
        'service_times' => isset($settings['service_times']) ? json_decode($settings['service_times'], true) : [
            'sunday' => '9:00 AM - 12:00 PM',
            'wednesday' => '6:00 PM - 7:30 PM',
            'friday' => '6:00 AM - 7:00 AM'
        ]
    ];
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $church_info = [
        'address' => 'Ntunja Township behind William Pitcher College, Manzini, Eswatini',
        'phone' => '+268 2505 5960',
        'email' => 'info@cfci.org.sz',
        'service_times' => [
            'sunday' => '9:00 AM - 12:00 PM',
            'wednesday' => '6:00 PM - 7:30 PM',
            'friday' => '6:00 AM - 7:00 AM'
        ]
    ];
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Location & Contact</h1>
                <p class="text-white mb-0">Find us and get in touch</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Location</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto text-center">
                <h2 class="mb-3">Visit Our Church</h2>
                <p class="lead">We're located in the heart of Manzini, Eswatini. Join us for worship and experience the warmth of our church family.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Map and Location Details -->
            <div class="col-lg-8">
                <div class="location-card mb-4">
                    <div class="card h-100">
                        <div class="card-body p-0">
                            <!-- Map -->
                            <div class="map-container">
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
                            
                            <!-- Location Details -->
                            <div class="location-details p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h4><i class="fas fa-map-marker-alt text-primary me-2"></i>Address</h4>
                                        <p class="mb-1"><?php echo htmlspecialchars($church_info['address']); ?></p>
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($church_info['address']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-directions me-1"></i> Get Directions
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h4><i class="fas fa-car text-primary me-2"></i>Parking</h4>
                                        <p class="mb-1">Free parking available on site</p>
                                        <p class="small text-muted">Ample parking for cars and buses</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h4><i class="fas fa-bus text-primary me-2"></i>Public Transport</h4>
                                        <ul class="small mb-0">
                                            <li>Taxi rank: 5 min walk</li>
                                            <li>Bus stop: 10 min walk</li>
                                            <li>Accessible via kombis from all areas</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h4><i class="fas fa-wheelchair text-primary me-2"></i>Accessibility</h4>
                                        <ul class="small mb-0">
                                            <li>Wheelchair accessible</li>
                                            <li>Accessible restrooms</li>
                                            <li>Hearing assistance available</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="contact-info-card mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Contact Information</h3>
                            
                            <div class="contact-item d-flex align-items-start mb-4">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone-alt text-primary"></i>
                                </div>
                                <div>
                                    <h5>Phone Numbers</h5>
                                    <p class="mb-1"><?php echo htmlspecialchars($church_info['phone']); ?></p>
                                    <p class="small text-muted mb-0">Office hours: Mon-Fri 8AM-5PM</p>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-start mb-4">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <h5>Email</h5>
                                    <p class="mb-1"><?php echo htmlspecialchars($church_info['email']); ?></p>
                                    <p class="small text-muted mb-0">General inquiries</p>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-start mb-4">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-clock text-primary"></i>
                                </div>
                                <div>
                                    <h5>Office Hours</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li>Monday - Friday: 8:00 AM - 5:00 PM</li>
                                        <li>Saturday: 9:00 AM - 1:00 PM</li>
                                        <li>Sunday: 7:00 AM - 1:00 PM</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-headset text-primary"></i>
                                </div>
                                <div>
                                    <h5>Emergency Contact</h5>
                                    <p class="mb-1">+268 7600 0000</p>
                                    <p class="small text-muted mb-0">24/7 pastoral care line</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Times -->
        <div class="service-times bg-light p-5 rounded mb-5">
            <h3 class="text-center mb-4">Service Times</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="service-time-card text-center p-4 h-100">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-church"></i>
                                </div>
                                <h4>Sunday Service</h4>
                                <p class="text-primary fw-bold"><?php echo htmlspecialchars($church_info['service_times']['sunday']); ?></p>
                                <p class="small">Main worship service with children's church and nursery available</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="service-time-card text-center p-4 h-100">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-pray"></i>
                                </div>
                                <h4>Wednesday Prayer</h4>
                                <p class="text-primary fw-bold"><?php echo htmlspecialchars($church_info['service_times']['wednesday']); ?></p>
                                <p class="small">Mid-week prayer meeting for healing and breakthrough</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="service-time-card text-center p-4 h-100">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-bible"></i>
                                </div>
                                <h4>Friday Bible Study</h4>
                                <p class="text-primary fw-bold"><?php echo htmlspecialchars($church_info['service_times']['friday']); ?></p>
                                <p class="small">In-depth Bible study and discussion groups</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Contacts -->
        <div class="department-contacts mb-5">
            <h3 class="text-center mb-4">Department Contacts</h3>
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="department-card text-center p-3 h-100">
                        <div class="department-icon mb-2">
                            <i class="fas fa-child"></i>
                        </div>
                        <h5>Children's Ministry</h5>
                        <p class="small mb-2">children@cfci.org.sz</p>
                        <a href="contact.php?dept=children" class="btn btn-sm btn-outline-primary">Contact</a>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="department-card text-center p-3 h-100">
                        <div class="department-icon mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Youth Ministry</h5>
                        <p class="small mb-2">youth@cfci.org.sz</p>
                        <a href="contact.php?dept=youth" class="btn btn-sm btn-outline-primary">Contact</a>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="department-card text-center p-3 h-100">
                        <div class="department-icon mb-2">
                            <i class="fas fa-music"></i>
                        </div>
                        <h5>Worship Ministry</h5>
                        <p class="small mb-2">worship@cfci.org.sz</p>
                        <a href="contact.php?dept=worship" class="btn btn-sm btn-outline-primary">Contact</a>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="department-card text-center p-3 h-100">
                        <div class="department-icon mb-2">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h5>Outreach</h5>
                        <p class="small mb-2">outreach@cfci.org.sz</p>
                        <a href="contact.php?dept=outreach" class="btn btn-sm btn-outline-primary">Contact</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section mb-5">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="accordion" id="locationFAQ">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            What should I wear to church?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#locationFAQ">
                        <div class="accordion-body">
                            Come as you are! We have people who dress in traditional attire, business casual, and casual wear. The most important thing is your presence, not your appearance.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Is there parking available?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#locationFAQ">
                        <div class="accordion-body">
                            Yes, we have ample free parking on site for both cars and buses. Our parking attendants will guide you to available spaces.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            What programs are available for children?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#locationFAQ">
                        <div class="accordion-body">
                            We have age-appropriate programs for children from 6 months to 12 years during our Sunday service. All our children's workers are trained and background-checked for your child's safety.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            How can I get involved in ministry?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#locationFAQ">
                        <div class="accordion-body">
                            Visit our ministries page or speak with one of our pastors after service. We have a ministry orientation program that helps you discover where your gifts can best serve.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-section">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">Send Us a Message</h3>
                            <form id="contactForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="prayer">Prayer Request</option>
                                        <option value="visit">Planning a Visit</option>
                                        <option value="ministry">Ministry Involvement</option>
                                        <option value="giving">Giving Questions</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i> Send Message
                                    </button>
                                </div>
                            </form>
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

.location-card .card, .contact-info-card .card {
    border: none;
    box-shadow: var(--shadow);
    border-radius: 10px;
    overflow: hidden;
}

.map-container iframe {
    border-radius: 10px 10px 0 0;
}

.location-details h4 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: var(--primary);
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

.service-time-card {
    background: white;
    border-radius: 10px;
    transition: var(--transition);
    border: 1px solid #eee;
}

.service-time-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.service-icon {
    font-size: 2.5rem;
    color: var(--primary);
}

.department-card {
    border: 1px solid #eee;
    border-radius: 8px;
    transition: var(--transition);
}

.department-card:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.department-card:hover .department-icon i {
    color: white;
}

.department-card:hover .btn-outline-primary {
    color: white;
    border-color: white;
}

.department-card:hover .btn-outline-primary:hover {
    background: white;
    color: var(--primary);
}

.department-icon i {
    font-size: 2rem;
    color: var(--primary);
}

.accordion-button:not(.collapsed) {
    background: var(--primary);
    color: white;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.2rem rgba(26, 82, 118, 0.25);
    border-color: var(--primary);
}

.contact-form-section .card {
    border: none;
    box-shadow: var(--shadow);
    border-radius: 10px;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .map-container iframe {
        height: 300px;
    }
    
    .contact-icon {
        width: 35px;
        height: 35px;
    }
    
    .contact-icon i {
        font-size: 1rem;
    }
    
    .service-icon {
        font-size: 2rem;
    }
    
    .department-icon i {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: this.name.value,
        email: this.email.value,
        subject: this.subject.value,
        message: this.message.value
    };
    
    // Validate form
    if (!formData.name || !formData.email || !formData.subject || !formData.message) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Simulate form submission
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    button.disabled = true;
    
    setTimeout(() => {
        alert('Thank you for your message! We will get back to you within 24-48 hours.');
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Scroll to top of form
        this.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 2000);
});

// Initialize Google Maps if needed
function initMap() {
    // Map is already embedded via iframe
    console.log('Location map loaded');
}

// Load map on page load
window.addEventListener('load', initMap);
</script>

<?php
require_once 'includes/footer.php';
?>