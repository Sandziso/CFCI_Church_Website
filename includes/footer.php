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
                        <a href="https://facebook.com/cfci-eswatini" class="social-link" target="_blank" rel="noopener" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/cfci-eswatini" class="social-link" target="_blank" rel="noopener" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://youtube.com/c/cfci-eswatini" class="social-link" target="_blank" rel="noopener" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://instagram.com/cfci-eswatini" class="social-link" target="_blank" rel="noopener" title="Instagram">
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

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Footer JavaScript -->
<script src="assets/js/footer.js"></script>

<!-- Page-specific JavaScript -->
<?php 
$page = basename($_SERVER['PHP_SELF']);
if ($page == 'index.php') {
    echo '<script src="assets/js/home.js"></script>';
} elseif ($page == 'about.php') {
    echo '<script src="assets/js/about.js"></script>';
} elseif ($page == 'contact.php') {
    echo '<script src="assets/js/contact.js"></script>';
}
?>
</body>
</html>