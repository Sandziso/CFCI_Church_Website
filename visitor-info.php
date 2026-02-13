<?php
require_once 'includes/header.php';

// Fetch service times from settings
$service_times = [];
try {
    $stmt = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'service_times'");
    $times = $stmt->fetch(PDO::FETCH_COLUMN);
    $service_times = $times ? json_decode($times, true) : [];
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $service_times = [
        'sunday' => '9:00 AM - 12:00 PM',
        'wednesday' => '6:00 PM - 7:30 PM',
        'friday' => '6:00 AM - 7:00 AM'
    ];
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">Visitor Information</h1>
                <p class="text-white mb-0">Everything you need to know for your first visit</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Visitor Info</li>
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
                <h2 class="mb-3">Welcome to CFCI!</h2>
                <p class="lead">We're so glad you're considering visiting us. Here's everything you need to know to make your first visit comfortable and meaningful.</p>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="welcome-message bg-primary text-white p-5 rounded mb-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-3">You're Welcome Here</h3>
                    <p class="mb-0">At Christian Family Centre International, you'll find a warm, friendly community where you can grow in your relationship with God and connect with others on the same journey.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn btn-light btn-lg">
                        <i class="fas fa-envelope me-2"></i> Plan Your Visit
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Info Cards -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="info-card text-center h-100">
                    <div class="info-icon mb-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4 class="mb-3">Service Times</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Sunday:</strong> <?php echo htmlspecialchars($service_times['sunday'] ?? '9:00 AM - 12:00 PM'); ?></li>
                        <li class="mb-2"><strong>Wednesday:</strong> <?php echo htmlspecialchars($service_times['wednesday'] ?? '6:00 PM - 7:30 PM'); ?></li>
                        <li><strong>Friday:</strong> <?php echo htmlspecialchars($service_times['friday'] ?? '6:00 AM - 7:00 AM'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="info-card text-center h-100">
                    <div class="info-icon mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4 class="mb-3">Location</h4>
                    <p>Ntunja Township behind William Pitcher College, Manzini, Eswatini</p>
                    <a href="location.php" class="btn btn-sm btn-outline-primary">Get Directions</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="info-card text-center h-100">
                    <div class="info-icon mb-4">
                        <i class="fas fa-child"></i>
                    </div>
                    <h4 class="mb-3">Children's Ministry</h4>
                    <p>We have excellent programs for children of all ages during our Sunday service.</p>
                    <a href="#children" class="btn btn-sm btn-outline-primary">Learn More</a>
                </div>
            </div>
        </div>

        <!-- What to Expect -->
        <div class="expectations mb-5">
            <h3 class="text-center mb-4">What to Expect</h3>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="expectation-card h-100">
                        <div class="expectation-header d-flex align-items-center mb-3">
                            <div class="expectation-icon me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="mb-0">Friendly Atmosphere</h4>
                        </div>
                        <p>From the moment you arrive, you'll be greeted by friendly faces. Our Welcome Team is ready to help you find your way around and answer any questions.</p>
                        <ul>
                            <li>Greeters at every entrance</li>
                            <li>Welcome center for information</li>
                            <li>Complimentary refreshments</li>
                            <li>Free visitor gift</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="expectation-card h-100">
                        <div class="expectation-header d-flex align-items-center mb-3">
                            <div class="expectation-icon me-3">
                                <i class="fas fa-music"></i>
                            </div>
                            <h4 class="mb-0">Powerful Worship</h4>
                        </div>
                        <p>Our services begin with passionate, contemporary worship led by our talented worship team. You're welcome to participate as much or as little as you're comfortable with.</p>
                        <ul>
                            <li>Contemporary worship music</li>
                            <li>Traditional hymns</li>
                            <li>Freedom to worship as you feel led</li>
                            <li>Lyrics displayed on screens</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="expectation-card h-100">
                        <div class="expectation-header d-flex align-items-center mb-3">
                            <div class="expectation-icon me-3">
                                <i class="fas fa-bible"></i>
                            </div>
                            <h4 class="mb-0">Relevant Teaching</h4>
                        </div>
                        <p>Each week, you'll hear practical, Bible-based teaching that applies to everyday life. Messages are designed to help you grow spiritually and live out your faith.</p>
                        <ul>
                            <li>Practical biblical teaching</li>
                            <li>Life application focus</li>
                            <li>Sermon notes provided</li>
                            <li>Audio/video available online</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="expectation-card h-100">
                        <div class="expectation-header d-flex align-items-center mb-3">
                            <div class="expectation-icon me-3">
                                <i class="fas fa-coffee"></i>
                            </div>
                            <h4 class="mb-0">Community Connection</h4>
                        </div>
                        <p>After service, stay for refreshments and meet new people. We believe church is about relationships, not just services.</p>
                        <ul>
                            <li>Free coffee and refreshments</li>
                            <li>Meet-and-greet opportunities</li>
                            <li>Information about small groups</li>
                            <li>Next steps guidance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dress Code -->
        <div class="dress-code bg-light p-5 rounded mb-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">What to Wear</h3>
                    <p class="mb-0">Come as you are! You'll see everything from traditional attire to casual wear. We care more about you than what you're wearing.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="dress-examples">
                        <i class="fas fa-tshirt fa-3x me-3 text-primary"></i>
                        <i class="fas fa-user-tie fa-3x me-3 text-primary"></i>
                        <i class="fas fa-female fa-3x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children's Ministry -->
        <div class="children-ministry mb-5" id="children">
            <h3 class="text-center mb-4">For Families with Children</h3>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="children-card text-center h-100">
                        <div class="children-icon mb-3">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4 class="mb-3">Nursery (0-2 years)</h4>
                        <p>Safe, clean nursery with caring volunteers. Parents can worship knowing their little ones are well cared for.</p>
                        <ul class="list-unstyled">
                            <li>Age-appropriate toys</li>
                            <li>Secure check-in system</li>
                            <li>Trained caregivers</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="children-card text-center h-100">
                        <div class="children-icon mb-3">
                            <i class="fas fa-child"></i>
                        </div>
                        <h4 class="mb-3">Preschool (3-5 years)</h4>
                        <p>Fun, interactive Bible lessons through play, songs, and crafts. Perfect introduction to God's love.</p>
                        <ul class="list-unstyled">
                            <li>Bible stories and songs</li>
                            <li>Creative play areas</li>
                            <li>Simple crafts</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="children-card text-center h-100">
                        <div class="children-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="mb-3">Kids Church (6-12 years)</h4>
                        <p>Engaging worship, relevant teaching, and small groups designed just for kids.</p>
                        <ul class="list-unstyled">
                            <li>Interactive Bible lessons</li>
                            <li>Age-appropriate worship</li>
                            <li>Small group activities</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="safety-note text-center mt-4">
                <div class="alert alert-info">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Safety First:</strong> All children's ministry workers are trained and background-checked. We use a secure check-in/check-out system for your child's safety.
                </div>
            </div>
        </div>

        <!-- First-Time Visitor Form -->
        <div class="visitor-form mb-5">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">Plan Your Visit</h3>
                            <p class="text-center mb-4">Let us know you're coming so we can give you a special welcome!</p>
                            <form id="visitorForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="visitorName" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="visitorName" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="visitorPhone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="visitorPhone" name="phone">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="visitorEmail" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="visitorEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="visitDate" class="form-label">When are you planning to visit? *</label>
                                    <select class="form-select" id="visitDate" name="visit_date" required>
                                        <option value="">Select a date</option>
                                        <option value="this_sunday">This Sunday</option>
                                        <option value="next_sunday">Next Sunday</option>
                                        <option value="not_sure">Not sure yet</option>
                                        <option value="other">Other (specify in comments)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="familySize" class="form-label">How many in your group? *</label>
                                    <select class="form-select" id="familySize" name="family_size" required>
                                        <option value="">Select number</option>
                                        <option value="1">Just me</option>
                                        <option value="2">2 people</option>
                                        <option value="3">3 people</option>
                                        <option value="4">4 people</option>
                                        <option value="5+">5 or more</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="childrenAges" class="form-label">Children's Ages (if applicable)</label>
                                    <input type="text" class="form-control" id="childrenAges" name="children_ages" placeholder="e.g., 3, 7, 10">
                                </div>
                                <div class="mb-3">
                                    <label for="specialNeeds" class="form-label">Special Needs or Questions</label>
                                    <textarea class="form-control" id="specialNeeds" name="special_needs" rows="3" placeholder="Any special requirements or questions you have..."></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calendar-check me-2"></i> Plan My Visit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="faq-section mb-5">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="accordion" id="visitorFAQ">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How long are your services?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#visitorFAQ">
                        <div class="accordion-body">
                            Our Sunday services typically last about 2 hours. This includes worship (30 minutes), announcements (10 minutes), the message (45 minutes), and response/ministry time (15-20 minutes).
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Is there parking available?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                        <div class="accordion-body">
                            Yes! We have ample free parking on site. Our parking attendants will guide you to available spaces. We also have reserved parking for visitors, seniors, and those with disabilities.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            What COVID-19 precautions are in place?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                        <div class="accordion-body">
                            We follow all local health guidelines. Hand sanitizer stations are available throughout the building, and we maintain enhanced cleaning procedures. Masks are optional.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Will I be singled out as a visitor?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                        <div class="accordion-body">
                            No, we won't ask you to stand up or introduce yourself during the service. However, we do encourage visitors to stop by our Welcome Center after the service for a special gift and to meet some of our team.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Do I need to bring a Bible?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#visitorFAQ">
                        <div class="accordion-body">
                            If you have a Bible, please bring it. If not, that's okay too! Scripture references are displayed on screens, and we have Bibles available at our Welcome Center if you'd like to use one.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="next-steps bg-light p-5 rounded">
            <h3 class="text-center mb-4">Your Next Steps</h3>
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <a href="sermons.php" class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-headphones"></i>
                        </div>
                        <h5>Listen Online</h5>
                        <p class="small">Check out a sermon before you visit</p>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="beliefs.php" class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h5>Our Beliefs</h5>
                        <p class="small">Learn what we believe</p>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="contact.php" class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>Contact Us</h5>
                        <p class="small">Ask any questions</p>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <a href="giving.php" class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h5>Give Online</h5>
                        <p class="small">Support the ministry</p>
                    </a>
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

.welcome-message {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

.info-card {
    background: white;
    padding: 30px 20px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
    height: 100%;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.info-icon {
    width: 70px;
    height: 70px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.info-icon i {
    font-size: 2rem;
    color: var(--primary);
}

.expectation-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
}

.expectation-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.expectation-icon {
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.expectation-icon i {
    font-size: 1.5rem;
}

.expectation-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.expectation-card li {
    margin-bottom: 8px;
    color: var(--text);
}

.dress-code {
    background: linear-gradient(135deg, rgba(26, 82, 118, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
}

.dress-examples i {
    opacity: 0.8;
}

.children-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: var(--transition);
    height: 100%;
}

.children-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.children-icon {
    font-size: 2.5rem;
    color: var(--primary);
}

.children-card ul li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.children-card ul li:last-child {
    border-bottom: none;
}

.visitor-form .card {
    border: none;
    box-shadow: var(--shadow);
    border-radius: 10px;
}

.accordion-button:not(.collapsed) {
    background: var(--primary);
    color: white;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.2rem rgba(26, 82, 118, 0.25);
    border-color: var(--primary);
}

.step-card {
    display: block;
    text-align: center;
    padding: 20px 15px;
    background: white;
    border-radius: 10px;
    border: 1px solid #eee;
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
    height: 100%;
}

.step-card:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.step-card:hover .step-icon {
    background: white;
}

.step-card:hover .step-icon i {
    color: var(--primary);
}

.step-icon {
    width: 60px;
    height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.step-icon i {
    font-size: 1.8rem;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .welcome-message {
        text-align: center;
    }
    
    .welcome-message .text-lg-end {
        text-align: center !important;
        margin-top: 20px;
    }
    
    .info-card {
        padding: 20px 15px;
    }
    
    .info-icon {
        width: 60px;
        height: 60px;
    }
    
    .info-icon i {
        font-size: 1.5rem;
    }
    
    .expectation-card {
        padding: 20px;
    }
    
    .expectation-icon {
        width: 40px;
        height: 40px;
    }
    
    .step-card {
        padding: 15px 10px;
    }
    
    .step-icon {
        width: 50px;
        height: 50px;
    }
    
    .step-icon i {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Visitor form submission
document.getElementById('visitorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: this.visitorName.value,
        phone: this.visitorPhone.value,
        email: this.visitorEmail.value,
        visit_date: this.visitDate.value,
        family_size: this.familySize.value,
        children_ages: this.childrenAges.value,
        special_needs: this.specialNeeds.value
    };
    
    // Validate form
    if (!formData.name || !formData.email || !formData.visit_date || !formData.family_size) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Simulate submission
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Planning...';
    button.disabled = true;
    
    setTimeout(() => {
        alert(`Thank you ${formData.name}! We\'re excited about your visit. We\'ll send a confirmation email to ${formData.email} with more details.`);
        this.reset();
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 2000);
});

// FAQ accordion
const faqItems = document.querySelectorAll('.accordion-button');
faqItems.forEach(item => {
    item.addEventListener('click', function() {
        // Close other items
        faqItems.forEach(otherItem => {
            if (otherItem !== this && otherItem.getAttribute('aria-expanded') === 'true') {
                const collapseId = otherItem.getAttribute('data-bs-target');
                const collapseElement = document.querySelector(collapseId);
                new bootstrap.Collapse(collapseElement, { toggle: false }).hide();
            }
        });
    });
});

// Smooth scroll for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>