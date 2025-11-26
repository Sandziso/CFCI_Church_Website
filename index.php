<?php
require_once 'includes/header.php';
?>

    <!-- Enhanced Hero Section -->
    <section class="hero" id="home">
        <div class="hero-background">
            <div class="hero-overlay"></div>
        </div>
        <div class="container hero-content">
            <div class="hero-text">
                <h1 class="hero-title animate-fade-in">Welcome to Christian Family Centre International</h1>
                <p class="hero-subtitle animate-fade-in-delay">Building strong families and empowering communities in Manzini, Eswatini, through the word of God.</p>
                <div class="hero-buttons animate-fade-in-delay-2">
                    <a href="#about" class="btn btn-primary btn-lg">Our Story</a>
                    <a href="#services" class="btn btn-outline-light btn-lg ml-3">Service Times</a>
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number" data-count="500">0</div>
                    <div class="stat-label">Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="15">0</div>
                    <div class="stat-label">Ministries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="52">0</div>
                    <div class="stat-label">Events Yearly</div>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Service Times Section -->
    <section class="service-times section-padding" id="services">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Join Us for Worship</h2>
                <p class="section-subtitle">Experience the power of fellowship and worship with our community</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="service-time-card card-hover">
                        <div class="service-icon-container">
                            <i class="fas fa-church service-icon floating"></i>
                        </div>
                        <h3 class="service-title">Sunday Main Service</h3>
                        <p class="service-time">10:00 AM - 12:30 PM</p>
                        <p class="service-address">
                            <i class="fas fa-map-marker-alt"></i> Manzini Civic Centre Hall, Eswatini
                        </p>
                        <div class="service-features">
                            <span class="feature-tag">Live Worship</span>
                            <span class="feature-tag">Children's Church</span>
                            <span class="feature-tag">Fellowship</span>
                        </div>
                        <a href="#contact" class="btn btn-primary btn-block mt-4">Get Directions</a>
                    </div>
                </div>
            </div>
            <div class="additional-services mt-5">
                <div class="row">
                    <div class="col-md-4">
                        <div class="service-mini-card">
                            <i class="fas fa-pray"></i>
                            <h4>Wednesday Prayer</h4>
                            <p>6:00 PM - 7:00 PM</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-mini-card">
                            <i class="fas fa-bible"></i>
                            <h4>Bible Study</h4>
                            <p>Friday 7:00 PM</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-mini-card">
                            <i class="fas fa-users"></i>
                            <h4>Youth Service</h4>
                            <p>Sunday 4:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section class="announcements-section section-padding bg-light" id="announcements">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Latest Announcements</h2>
                <p class="section-subtitle">Stay updated with our latest news and events</p>
            </div>
            <div class="announcements-grid" id="announcements-container">
                <div class="announcement-card card-hover">
                    <div class="announcement-badge">New</div>
                    <h3 class="announcement-title">Prayer and Fasting Week</h3>
                    <p class="announcement-date"><i class="far fa-calendar-alt"></i> June 23 - June 29, 2024</p>
                    <p>Join us for a powerful week of corporate prayer and fasting as we seek God's direction for the second half of the year. Evening services daily at 6 PM.</p>
                    <div class="announcement-actions">
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
                <div class="announcement-card card-hover">
                    <div class="announcement-badge">Upcoming</div>
                    <h3 class="announcement-title">Youth Takeover Sunday</h3>
                    <p class="announcement-date"><i class="far fa-calendar-alt"></i> July 7, 2024</p>
                    <p>Our dynamic youth ministry will be leading all aspects of the main Sunday service. Come and be blessed by the new generation!</p>
                    <div class="announcement-actions">
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
                <div class="announcement-card card-hover">
                    <h3 class="announcement-title">New Member Class Orientation</h3>
                    <p class="announcement-date"><i class="far fa-calendar-alt"></i> Every Saturday at 2 PM</p>
                    <p>If you're new to the family and wish to know more about CFCI, join our orientation class in the annex building.</p>
                    <div class="announcement-actions">
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                 <a href="announcements.php" class="btn btn-primary btn-lg">View All News</a>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events section-padding" id="events">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Upcoming Events</h2>
                <p class="section-subtitle">Join us for these life-changing experiences</p>
            </div>
            <div class="events-grid" id="events-container">
                <div class="event-card card-hover">
                    <div class="event-date">
                        <span class="event-day">15</span>
                        <span class="event-month">SEP</span>
                    </div>
                    <div class="event-content">
                        <span class="event-category">Family</span>
                        <h3 class="event-title">Annual Family Fun Day</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 9:00 AM - 4:00 PM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Prince of Wales Stadium</p>
                        <p>A day of fun, food, and fellowship for the whole family. Tickets available after service.</p>
                        <div class="event-actions">
                            <a href="#" class="btn btn-outline-primary">Learn More</a>
                            <span class="event-attendees">120+ attending</span>
                        </div>
                    </div>
                </div>

                <div class="event-card card-hover">
                    <div class="event-date">
                        <span class="event-day">05</span>
                        <span class="event-month">OCT</span>
                    </div>
                    <div class="event-content">
                        <span class="event-category">Women</span>
                        <h3 class="event-title">Women's Prayer Breakfast</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 8:00 AM - 11:00 AM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Church Annex Hall</p>
                        <p>An uplifting time of prayer, testimony, and an inspiring message for all women.</p>
                        <div class="event-actions">
                            <a href="#" class="btn btn-outline-primary">Learn More</a>
                            <span class="event-attendees">80+ attending</span>
                        </div>
                    </div>
                </div>

                <div class="event-card card-hover">
                    <div class="event-date">
                        <span class="event-day">24</span>
                        <span class="event-month">NOV</span>
                    </div>
                    <div class="event-content">
                        <span class="event-category">Thanksgiving</span>
                        <h3 class="event-title">Thanksgiving Service</h3>
                        <p class="event-time"><i class="far fa-clock"></i> 10:00 AM</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> Main Sanctuary</p>
                        <p>Join us as we give thanks to God for a wonderful year of grace and blessings.</p>
                        <div class="event-actions">
                            <a href="#" class="btn btn-outline-primary">Learn More</a>
                            <span class="event-attendees">200+ attending</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                 <a href="calendar.php" class="btn btn-secondary btn-lg">Full Calendar</a>
            </div>
        </div>
    </section>

    <!-- Ministries Section -->
    <section class="ministries section-padding bg-light" id="ministries">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Our Ministries</h2>
                <p class="section-subtitle">Find your place to serve and grow</p>
            </div>
            <div class="ministries-grid">
                <div class="ministry-card card-hover">
                    <div class="ministry-icon-container">
                        <i class="fas fa-child ministry-icon"></i>
                    </div>
                    <h3 class="ministry-title">Children's Ministry</h3>
                    <p class="ministry-desc">Nurturing faith in our youngest members through fun, engaging, and Bible-based lessons.</p>
                    <div class="ministry-meta">
                        <span class="ministry-age">Ages 3-12</span>
                        <span class="ministry-meets">Sundays 10AM</span>
                    </div>
                    <a href="#" class="btn btn-sm btn-primary">Join Ministry</a>
                </div>
                <div class="ministry-card card-hover">
                    <div class="ministry-icon-container">
                        <i class="fas fa-users ministry-icon"></i>
                    </div>
                    <h3 class="ministry-title">Youth Ministry</h3>
                    <p class="ministry-desc">Equipping teens and young adults to live purpose-driven lives in Christ and impact their world.</p>
                    <div class="ministry-meta">
                        <span class="ministry-age">Ages 13-25</span>
                        <span class="ministry-meets">Sundays 4PM</span>
                    </div>
                    <a href="#" class="btn btn-sm btn-primary">Join Ministry</a>
                </div>
                <div class="ministry-card card-hover">
                    <div class="ministry-icon-container">
                        <i class="fas fa-hand-holding-heart ministry-icon"></i>
                    </div>
                    <h3 class="ministry-title">Outreach Ministry</h3>
                    <p class="ministry-desc">Serving our local community and sharing the love of God through humanitarian and evangelistic efforts.</p>
                    <div class="ministry-meta">
                        <span class="ministry-age">All Ages</span>
                        <span class="ministry-meets">Monthly</span>
                    </div>
                    <a href="#" class="btn btn-sm btn-primary">Join Ministry</a>
                </div>
                <div class="ministry-card card-hover">
                    <div class="ministry-icon-container">
                        <i class="fas fa-male ministry-icon"></i>
                    </div>
                    <h3 class="ministry-title">Men's Ministry</h3>
                    <p class="ministry-desc">Building men of integrity, faith, and leadership to strengthen their families and the church.</p>
                    <div class="ministry-meta">
                        <span class="ministry-age">Men 18+</span>
                        <span class="ministry-meets">1st Saturday</span>
                    </div>
                    <a href="#" class="btn btn-sm btn-primary">Join Ministry</a>
                </div>
            </div>
            <div class="text-center mt-5">
                 <a href="ministries.php" class="btn btn-primary btn-lg">See All Ministries</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about section-padding" id="about">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Who We Are</h2>
                <p class="section-subtitle">A community of faith, hope, and love</p>
            </div>
            <div class="about-content">
                <div class="about-img">
                    <img src="https://via.placeholder.com/800x600" alt="Bishop and First Lady" class="img-fluid rounded">
                    <div class="about-img-overlay">
                        <div class="overlay-content">
                            <h4>Join Our Family</h4>
                            <p>Experience the love of Christ in our community</p>
                        </div>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Our Vision & Mission</h3>
                    <p>The Christian Family Centre International (CFCI) was founded on the principle of restoring the family unit and raising generations of faithful leaders. We are a non-denominational church passionately committed to teaching the undiluted Word of God and fostering a culture of genuine fellowship and outreach.</p>
                    
                    <div class="vision-points">
                        <div class="vision-point">
                            <i class="fas fa-cross"></i>
                            <div>
                                <h5>Biblical Teaching</h5>
                                <p>Practical, life-changing messages from God's Word</p>
                            </div>
                        </div>
                        <div class="vision-point">
                            <i class="fas fa-hands-praying"></i>
                            <div>
                                <h5>Powerful Prayer</h5>
                                <p>Experiencing God's power through corporate prayer</p>
                            </div>
                        </div>
                        <div class="vision-point">
                            <i class="fas fa-heart"></i>
                            <div>
                                <h5>Authentic Community</h5>
                                <p>Building genuine relationships that last</p>
                            </div>
                        </div>
                    </div>

                    <div class="bishop-quote">
                        <p>"Our mission is to make sure every family is a kingdom family, anchored in Christ and equipped to influence their generation."</p>
                        <div class="quote-author">
                            <strong>— Bishop J. Mfusi</strong>
                            <span>Senior Pastor</span>
                        </div>
                    </div>
                    <div class="about-actions">
                        <a href="about-details.php" class="btn btn-primary">Read Our Full Story</a>
                        <a href="beliefs.php" class="btn btn-outline-primary ml-3">Our Beliefs</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">What People Say</h2>
                <p class="section-subtitle">Stories of transformed lives</p>
            </div>
            <div class="testimonials-slider">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"CFCI has been a blessing to my family. The teachings have transformed our marriage and brought us closer to God."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://via.placeholder.com/60" alt="Sarah M.">
                        <div>
                            <h5>Sarah M.</h5>
                            <span>Member for 3 years</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The youth ministry helped me find purpose and direction in my life. I'm grateful for this spiritual family."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://via.placeholder.com/60" alt="David T.">
                        <div>
                            <h5>David T.</h5>
                            <span>Youth Member</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"From the first day I walked in, I felt the genuine love and acceptance. This is more than a church, it's family."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://via.placeholder.com/60" alt="Grace K.">
                        <div>
                            <h5>Grace K.</h5>
                            <span>New Member</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact section-padding" id="contact">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Get in Touch</h2>
                <p class="section-subtitle">We'd love to hear from you</p>
            </div>
            <div class="contact-grid">
                <div class="contact-card card-hover">
                    <div class="contact-icon-container">
                        <i class="fas fa-phone-alt contact-icon"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Phone</h3>
                        <p>+268 7600 0000</p>
                        <p>+268 2500 0000 (Office)</p>
                    </div>
                </div>

                <div class="contact-card card-hover">
                    <div class="contact-icon-container">
                        <i class="fas fa-envelope contact-icon"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Email</h3>
                        <p>info@cfc-eswatini.org</p>
                        <p>cfcimanzini@gmail.com</p>
                    </div>
                </div>

                <div class="contact-card card-hover">
                    <div class="contact-icon-container">
                        <i class="fas fa-map-marked-alt contact-icon"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Address</h3>
                        <p>Manzini Civic Centre Hall, 1st Floor</p>
                        <p>Manzini, Eswatini</p>
                    </div>
                </div>
            </div>

            <div class="contact-form mt-5">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-container">
                            <h3>Send Us a Message</h3>
                            <form action="send_email.php" method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                            <div class="invalid-feedback">Please enter your name.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                            <div class="invalid-feedback">Please enter a valid email.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="subject" class="form-control" placeholder="Subject">
                                </div>
                                <div class="form-group">
                                    <select name="department" class="form-control">
                                        <option value="">Select Department</option>
                                        <option value="prayer">Prayer Request</option>
                                        <option value="information">General Information</option>
                                        <option value="ministry">Ministry Inquiry</option>
                                        <option value="event">Event Information</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                                    <div class="invalid-feedback">Please enter your message.</div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="map-container">
                            <div id="map" class="rounded">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.7196023249124!2d31.3653158!3d-26.4764836!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1eb36021e1022839%3A0x6331908d1323f462!2sManzini%20Civic%20Centre!5e0!3m2!1sen!2szw!4v1687103250106!5m2!1sen!2szw" 
                                    width="100%" 
                                    height="100%" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                            <div class="map-info">
                                <h5>Visit Us</h5>
                                <p>We're located in the heart of Manzini. Plenty of parking available.</p>
                                <div class="visiting-hours">
                                    <strong>Office Hours:</strong><br>
                                    Mon-Fri: 8AM-5PM<br>
                                    Sat: 9AM-1PM
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3>Stay Connected</h3>
                    <p>Subscribe to our newsletter for updates, events, and spiritual insights.</p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-secondary" type="submit">Subscribe</button>
                        </div>
                        <small class="form-text">We respect your privacy. Unsubscribe at any time.</small>
                    </form>
                </div>
            </div>
        </div>
    </section>

<?php
require_once 'includes/footer.php';
?>

<!-- Additional CSS -->
<style>
/* Enhanced Hero Section */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, var(--primary) 0%, #2c3e50 100%);
    color: white;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://via.placeholder.com/1920x1080') center/cover no-repeat;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(26, 82, 118, 0.8);
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    opacity: 0;
    animation: fadeInUp 1s ease forwards;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0;
    animation: fadeInUp 1s ease 0.3s forwards;
}

.hero-buttons {
    opacity: 0;
    animation: fadeInUp 1s ease 0.6s forwards;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 4rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--secondary);
}

.stat-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
}

.scroll-arrow {
    width: 30px;
    height: 30px;
    border-right: 2px solid white;
    border-bottom: 2px solid white;
    transform: rotate(45deg);
    animation: bounce 2s infinite;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: rotate(45deg) translateY(0);
    }
    40% {
        transform: rotate(45deg) translateY(-10px);
    }
    60% {
        transform: rotate(45deg) translateY(-5px);
    }
}

/* Card Hover Effects */
.card-hover {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

/* Section Headers */
.section-header {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--text-light);
}

/* Enhanced Service Cards */
.service-time-card {
    background: white;
    padding: 3rem 2rem;
    border-radius: 15px;
    text-align: center;
    position: relative;
}

.service-icon-container {
    margin-bottom: 1.5rem;
}

.service-icon {
    font-size: 3rem;
    color: var(--primary);
}

.service-features {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1rem 0;
}

.feature-tag {
    background: var(--light);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    color: var(--text);
}

/* Ministry Cards Enhancement */
.ministry-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    height: 100%;
}

.ministry-icon-container {
    width: 80px;
    height: 80px;
    background: var(--light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.ministry-icon {
    font-size: 2rem;
    color: var(--primary);
}

.ministry-meta {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 1rem 0;
}

.ministry-age, .ministry-meets {
    font-size: 0.8rem;
    color: var(--text-light);
}

/* Testimonials */
.testimonials-slider {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    border-left: 4px solid var(--secondary);
}

.testimonial-author {
    display: flex;
    align-items: center;
    margin-top: 1.5rem;
}

.testimonial-author img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-stats {
        gap: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}

/* Form Enhancements */
.form-control {
    border: 1px solid #e0e0e0;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(26, 82, 118, 0.25);
}

/* Newsletter Section */
.newsletter-section {
    padding: 4rem 0;
}

.newsletter-form .input-group {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.newsletter-form .form-control {
    border: none;
    padding: 1rem;
}

.newsletter-form .btn {
    padding: 1rem 2rem;
}

/* Additional Utility Classes */
.bg-light {
    background-color: #f8f9fa !important;
}

.rounded {
    border-radius: 10px !important;
}

.mt-5 {
    margin-top: 3rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.text-center {
    text-align: center !important;
}

/* Button Enhancements */
.btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary);
    border-color: var(--primary);
}

.btn-primary:hover {
    background: #154360;
    border-color: #154360;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--secondary);
    border-color: var(--secondary);
}

.btn-secondary:hover {
    background: #d35400;
    border-color: #d35400;
    transform: translateY(-2px);
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary);
}

/* Floating Animation */
.floating {
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}
</style>

<!-- Additional JavaScript -->
<script>
// Counter Animation
document.addEventListener('DOMContentLoaded', function() {
    // Stats counter
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText;
            
            const inc = target / speed;
            
            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target;
            }
        };
        
        updateCount();
    });

    // Scroll to sections
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Parallax effect for hero
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
});

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.service-time-card, .announcement-card, .event-card, .ministry-card');
    animateElements.forEach(el => observer.observe(el));
});
</script>