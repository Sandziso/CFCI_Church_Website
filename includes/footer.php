<?php 
    $current_year = date('Y');
?>
    </div>
</main>

<footer class="bg-dark text-light pt-5">
    <a href="#" id="backToTop" class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <div class="container">
        <div class="row g-4">
            <!-- About Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-about">
                    <div class="footer-logo mb-3">
                        <img src="assets/images/logo-light.png" alt="CFCI Logo" style="height: 50px;" class="me-2">
                        <span class="h5 mb-0">CFCI Church</span>
                    </div>
                    <p class="mb-4">Christian Family Centre International is a church dedicated to transforming lives through faith, fellowship, and service. We invite you to join our family.</p>
                    
                    <div class="contact-info">
                        <div class="contact-item d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt text-secondary me-3"></i>
                            <span>Ntunja Township behind William Pitcher College, Eswatini</span>
                        </div>
                        <div class="contact-item d-flex align-items-center mb-3">
                            <i class="far fa-clock text-secondary me-3"></i>
                            <span>Service Time: Sunday 9:00 AM - 12:00 PM</span>
                        </div>
                        <div class="contact-item d-flex align-items-center mb-3">
                            <i class="fas fa-phone-alt text-secondary me-3"></i>
                            <span>+268 2505 5960</span>
                        </div>
                        <div class="contact-item d-flex align-items-center">
                            <i class="fas fa-envelope text-secondary me-3"></i>
                            <span>support@cfci.org.sz</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-4">Quick Links</h5>
                <ul class="footer-links list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Home</a></li>
                    <li class="mb-2"><a href="about.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>About Us</a></li>
                    <li class="mb-2"><a href="beliefs.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Our Beliefs</a></li>
                    <li class="mb-2"><a href="leadership.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Leadership</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Contact</a></li>
                </ul>
            </div>

            <!-- Ministries Column -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-4">Ministries</h5>
                <ul class="footer-links list-unstyled">
                    <li class="mb-2"><a href="ministries.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>All Ministries</a></li>
                    <li class="mb-2"><a href="ministry.php?id=youth" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Youth Ministry</a></li>
                    <li class="mb-2"><a href="ministry.php?id=children" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Children's Church</a></li>
                    <li class="mb-2"><a href="ministry.php?id=women" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Women's Fellowship</a></li>
                    <li class="mb-2"><a href="ministry.php?id=men" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Men's Ministry</a></li>
                    <li class="mb-2"><a href="ministry.php?id=outreach" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Outreach Ministry</a></li>
                </ul>
            </div>

            <!-- Resources & Newsletter Column -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-4">Resources</h5>
                <ul class="footer-links list-unstyled mb-4">
                    <li class="mb-2"><a href="sermons.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Sermon Library</a></li>
                    <li class="mb-2"><a href="blog.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Church Blog</a></li>
                    <li class="mb-2"><a href="prayer-request.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Prayer Requests</a></li>
                    <li class="mb-2"><a href="events.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Events Calendar</a></li>
                    <li class="mb-2"><a href="give.php" class="text-light text-decoration-none hover-secondary"><i class="fas fa-caret-right me-2 text-secondary"></i>Online Giving</a></li>
                </ul>

                <div class="newsletter">
                    <h5 class="text-white mb-3">Stay Connected</h5>
                    <p class="small mb-3">Subscribe to our newsletter for updates and spiritual insights.</p>
                    <form id="newsletterForm" class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm" placeholder="Enter your email" required>
                            <button class="btn btn-secondary btn-sm" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>

                    <div class="social-links mt-4">
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

        <div class="footer-bottom border-top border-secondary mt-5 pt-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo $current_year; ?> Christian Family Centre International. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-bottom-links">
                        <a href="privacy.php" class="text-light text-decoration-none me-3 hover-secondary">Privacy Policy</a>
                        <a href="terms.php" class="text-light text-decoration-none me-3 hover-secondary">Terms of Service</a>
                        <a href="sitemap.php" class="text-light text-decoration-none hover-secondary">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* Footer Styles */
    footer {
        position: relative;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
    }

    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: var(--shadow-lg);
        transition: var(--transition);
        opacity: 0;
        visibility: hidden;
        z-index: 99;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background: var(--secondary-dark);
        transform: translateY(-3px);
        color: white;
    }

    .footer-logo {
        display: flex;
        align-items: center;
    }

    .hover-secondary:hover {
        color: var(--secondary) !important;
        transform: translateX(5px);
    }

    .footer-links a {
        transition: var(--transition);
    }

    .social-links {
        display: flex;
        gap: 12px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        color: var(--light);
        text-decoration: none;
    }

    .social-link:hover {
        background: var(--secondary);
        transform: translateY(-3px);
        color: white;
    }

    .newsletter-form .form-control {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
    }

    .newsletter-form .form-control::placeholder {
        color: rgba(255,255,255,0.7);
    }

    .newsletter-form .form-control:focus {
        background: rgba(255,255,255,0.15);
        border-color: var(--secondary);
        box-shadow: 0 0 0 0.2rem rgba(230, 126, 34, 0.25);
        color: white;
    }

    .footer-bottom {
        border-color: rgba(255,255,255,0.1) !important;
    }

    @media (max-width: 768px) {
        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
        }
        
        .footer-bottom-links {
            margin-top: 15px;
        }
        
        .footer-bottom-links a {
            display: block;
            margin-bottom: 8px;
        }
    }
</style>

<script>
    // Back to top functionality
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    
    backToTop.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Mobile menu functionality
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    mobileToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    });
    
    mobileOverlay.addEventListener('click', () => {
        navMenu.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Close mobile menu when clicking on links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    // Newsletter form submission
    const newsletterForm = document.getElementById('newsletterForm');
    
    newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = newsletterForm.querySelector('input[type="email"]').value;
        
        // Simulate form submission
        const submitBtn = newsletterForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;
        
        setTimeout(() => {
            alert(`Thank you for subscribing with: ${email}`);
            newsletterForm.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);
    });
    
    // Add current year to copyright (fallback)
    document.querySelector('.footer-bottom p').innerHTML = `&copy; ${new Date().getFullYear()} Christian Family Centre International. All Rights Reserved.`;
</script>
</body>
</html>