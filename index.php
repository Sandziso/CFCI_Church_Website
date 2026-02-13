<?php
// Set the page variable for header/footer includes
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFCI Church - Christian Family Centre International</title>
    
    <!-- Meta Description for SEO -->
    <meta name="description" content="Christian Family Centre International (CFCI) in Manzini, Swaziland - A vibrant Christian community dedicated to worship, fellowship, and spreading God's love.">
    <meta name="keywords" content="CFCI, church, Manzini, Eswatini, Christian, family, worship, prayer">
    <meta name="author" content="Christian Family Centre International">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://cfci-eswatini.org/">
    <meta property="og:title" content="CFCI Church - Christian Family Centre International">
    <meta property="og:description" content="Building strong families and empowering communities in Manzini, Eswatini">
    <meta property="og:image" content="https://cfci-eswatini.org/assets/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- Header CSS -->
    <link rel="stylesheet" href="assets/css/header.css">
    <!-- Home Page CSS -->
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
    <!-- Include Header -->
    <?php include('includes/header.php'); ?>
    
    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay">
            <div class="container">
                <div class="hero-content fade-in">
                    <h1 class="hero-title">Welcome to Christian Family Centre International</h1>
                    <p class="hero-subtitle">A vibrant Christian community in Manzini, Swaziland dedicated to worship, fellowship, and spreading God's love</p>
                    
                    <!-- Service Times -->
                    <div class="service-times slide-in-left">
                        <div class="service-time-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Sunday Service</h3>
                                <p>9:00 AM - 11:30 AM</p>
                            </div>
                        </div>
                        <div class="service-time-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Bible Study</h3>
                                <p>Wednesday 6:00 PM</p>
                            </div>
                        </div>
                        <div class="service-time-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Prayer Meeting</h3>
                                <p>Friday 6:00 PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Countdown to Next Service -->
                    <div class="countdown-container slide-in-right">
                        <h3>Next Sunday Service In:</h3>
                        <div id="service-countdown" class="countdown">
                            <!-- Countdown will be populated by JavaScript -->
                        </div>
                        <a href="#services" class="btn btn-secondary mt-3">
                            <i class="fas fa-calendar-alt"></i> View All Services
                        </a>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="hero-cta">
                        <a href="#about" class="btn btn-primary btn-lg">
                            <i class="fas fa-info-circle"></i> Learn More About Us
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-map-marker-alt"></i> Visit Us This Sunday
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">About CFCI</h2>
                <p class="section-subtitle">Our Mission, Vision, and Beliefs</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card p-4 h-100">
                        <div class="card-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h3 class="card-title">Our Mission</h3>
                        <p class="card-text">To build a Christ-centered community that transforms lives through the power of the Gospel, nurturing spiritual growth, and extending God's love to our city and nation.</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card p-4 h-100">
                        <div class="card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="card-title">Our Vision</h3>
                        <p class="card-text">To be a beacon of hope and transformation in Swaziland, raising disciples who impact their families, communities, and nation for Christ.</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <div class="card p-5">
                    <h3 class="text-center mb-4">Our Core Beliefs</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="belief-item">
                                <i class="fas fa-book-bible"></i>
                                <h4>The Bible</h4>
                                <p>We believe the Bible is the inspired, infallible Word of God and our final authority in all matters of faith and conduct.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="belief-item">
                                <i class="fas fa-dove"></i>
                                <h4>The Trinity</h4>
                                <p>We believe in one God eternally existing in three persons: Father, Son, and Holy Spirit.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="belief-item">
                                <i class="fas fa-church"></i>
                                <h4>The Church</h4>
                                <p>We believe the Church is the body of Christ, called to worship, fellowship, and spread the Gospel to all nations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section-padding">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Services</h2>
                <p class="section-subtitle">Join us for worship and fellowship</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="service-card card h-100">
                        <div class="service-icon">
                            <i class="fas fa-sun"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Sunday Worship</h3>
                            <p class="service-time"><i class="fas fa-clock"></i> 9:00 AM - 11:30 AM</p>
                            <p class="card-text">Our main worship service featuring inspiring worship, biblical teaching, and fellowship. Children's church and nursery available.</p>
                            <a href="#contact" class="btn btn-outline-primary mt-3">Get Directions</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="service-card card h-100">
                        <div class="service-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Bible Study</h3>
                            <p class="service-time"><i class="fas fa-clock"></i> Wednesday, 6:00 PM</p>
                            <p class="card-text">Mid-week study delving deeper into God's Word. Interactive discussions and prayer for spiritual growth.</p>
                            <a href="#contact" class="btn btn-outline-primary mt-3">Join Us</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="service-card card h-100">
                        <div class="service-icon">
                            <i class="fas fa-pray"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Prayer Meeting</h3>
                            <p class="service-time"><i class="fas fa-clock"></i> Friday, 6:00 PM</p>
                            <p class="card-text">Corporate prayer for our church, community, nation, and personal needs. Experience the power of prayer together.</p>
                            <a href="#contact" class="btn btn-outline-primary mt-3">Pray With Us</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="#events" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-check"></i> View Upcoming Events
                </a>
            </div>
        </div>
    </section>

    <!-- Ministries Section -->
    <section id="ministries" class="section-padding bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Ministries</h2>
                <p class="section-subtitle">Get involved in our various ministry groups</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Children's Ministry</h3>
                            <p class="card-text">Age-appropriate Bible teaching and activities for children from nursery to pre-teen. Safe and nurturing environment.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> Ages 0-12</span>
                                <span><i class="fas fa-clock"></i> Sundays, 9:00 AM</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-music"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Worship Team</h3>
                            <p class="card-text">Leading the congregation in worship through music. Open to vocalists and instrumentalists with a heart for worship.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> All Ages</span>
                                <span><i class="fas fa-clock"></i> Rehearsals: Saturdays</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Outreach Ministry</h3>
                            <p class="card-text">Serving our community through practical assistance, evangelism, and demonstrating God's love in tangible ways.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> All Ages</span>
                                <span><i class="fas fa-clock"></i> Monthly Activities</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Youth Ministry</h3>
                            <p class="card-text">Engaging teenagers with relevant Bible teaching, fellowship, and activities to grow in their faith.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> Ages 13-18</span>
                                <span><i class="fas fa-clock"></i> Fridays, 5:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Men's Fellowship</h3>
                            <p class="card-text">Building godly men through Bible study, accountability, and fellowship. Monthly meetings and events.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> Men 18+</span>
                                <span><i class="fas fa-clock"></i> First Saturday Monthly</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="ministry-card card h-100">
                        <div class="ministry-image">
                            <i class="fas fa-female"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Women's Ministry</h3>
                            <p class="card-text">Encouraging women in their faith journey through Bible studies, prayer, and fellowship events.</p>
                            <div class="ministry-info">
                                <span><i class="fas fa-users"></i> Women 18+</span>
                                <span><i class="fas fa-clock"></i> Second Saturday Monthly</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="section-padding">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Upcoming Events</h2>
                <p class="section-subtitle">Join us for these special gatherings</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="event-card card h-100">
                        <div class="event-date">
                            <div class="event-day">15</div>
                            <div class="event-month">DEC</div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Christmas Celebration Service</h3>
                            <p class="event-time"><i class="fas fa-clock"></i> 10:00 AM - 12:00 PM</p>
                            <p class="card-text">Special Christmas service with carols, message of hope, and children's nativity play. Bring the whole family!</p>
                            <div class="event-tags">
                                <span class="event-tag">Family</span>
                                <span class="event-tag">Holiday</span>
                                <span class="event-tag">Special Service</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="event-card card h-100">
                        <div class="event-date">
                            <div class="event-day">24</div>
                            <div class="event-month">DEC</div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Christmas Eve Candlelight Service</h3>
                            <p class="event-time"><i class="fas fa-clock"></i> 7:00 PM - 8:30 PM</p>
                            <p class="card-text">A beautiful candlelight service celebrating the birth of our Savior. Communion will be served.</p>
                            <div class="event-tags">
                                <span class="event-tag">Candlelight</span>
                                <span class="event-tag">Communion</span>
                                <span class="event-tag">Holiday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="event-card card h-100">
                        <div class="event-date">
                            <div class="event-day">31</div>
                            <div class="event-month">DEC</div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">New Year's Eve Prayer Service</h3>
                            <p class="event-time"><i class="fas fa-clock"></i> 9:00 PM - 12:30 AM</p>
                            <p class="card-text">Welcome the new year with prayer, worship, and thanksgiving. Let's dedicate the coming year to God together.</p>
                            <div class="event-tags">
                                <span class="event-tag">Prayer</span>
                                <span class="event-tag">New Year</span>
                                <span class="event-tag">Overnight</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="event-card card h-100">
                        <div class="event-date">
                            <div class="event-day">10</div>
                            <div class="event-month">JAN</div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">Church Family Picnic</h3>
                            <p class="event-time"><i class="fas fa-clock"></i> 1:00 PM - 5:00 PM</p>
                            <p class="card-text">Annual church picnic at Manzini Park. Food, games, and fellowship for all ages. Bring a dish to share!</p>
                            <div class="event-tags">
                                <span class="event-tag">Outdoor</span>
                                <span class="event-tag">Family</span>
                                <span class="event-tag">Fellowship</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">We'd love to hear from you</p>
            </div>
            
            <div class="row">
                <!-- Contact Information -->
                <div class="col-md-4 mb-4">
                    <div class="contact-info card h-100 p-4">
                        <h3 class="mb-4">Get In Touch</h3>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4>Our Location</h4>
                                <p>Mhlakuvane Street<br>Manzini, Swaziland</p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4>Phone Number</h4>
                                <p>+268 7800 1234</p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4>Email Address</h4>
                                <p>info@cfci-sz.org</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h4>Office Hours</h4>
                                <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-md-8 mb-4">
                    <div class="card p-4 h-100">
                        <h3 class="mb-4">Send Us a Message</h3>
                        <form class="contact-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Map -->
            <div class="mt-5">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="map" style="height: 400px; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include('includes/footer.php'); ?>
    
    <!-- Main JavaScript -->
    <script src="assets/js/main.js"></script>
    <!-- Home Page JavaScript -->
    <script src="assets/js/home.js"></script>
</body>
</html>