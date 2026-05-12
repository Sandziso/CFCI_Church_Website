<?php 
    $current_year = date('Y');
?>
    <!-- ==================== FOOTER ==================== -->
    <footer class="cfci-footer" role="contentinfo">
        <div class="cfci-footer-pattern"></div>
        <div class="cfci-footer-container">
            <div class="cfci-footer-grid">
                <div class="cfci-footer-col">
                    <img src="assets/images/logo-light.png" alt="CFCI Logo" class="cfci-footer-logo">
                    <h4>About CFCI</h4>
                    <p class="cfci-footer-about">Christian Family Centre International – building strong families and empowering communities in Manzini, Eswatini.</p>
                    <div class="cfci-footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="cfci-footer-col">
                    <h4>Quick Links</h4>
                    <ul class="cfci-footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="ministries.php">Ministries</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="cfci-footer-col">
                    <h4>Contact</h4>
                    <ul class="cfci-footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> Ntunja Township behind William Pitcher College, Eswatini</li>
                        <li><i class="fas fa-phone-alt"></i> +268 7600 0000</li>
                        <li><i class="fas fa-envelope"></i> support@cfci.org.sz</li>
                        <li><i class="fas fa-clock"></i> Sunday 9:00 AM - 12:00 PM</li>
                    </ul>
                </div>
                <div class="cfci-footer-col">
                    <h4>Stay Connected</h4>
                    <p class="text-white-50">Subscribe for weekly inspiration.</p>
                    <form class="cfci-footer-newsletter-form" onsubmit="handleNewsletterSubmit(event)">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                    <div class="mt-3 d-flex gap-2">
                        <a href="prayer-request.php" class="btn btn-warning btn-sm">Prayer Request</a>
                        <a href="give.php" class="btn btn-outline-light btn-sm">Give</a>
                    </div>
                </div>
            </div>
            <hr class="cfci-footer-divider">
            <div class="cfci-footer-bottom">
                <p class="copyright">&copy; <?= $current_year ?> Christian Family Centre International. Made with <i class="fas fa-heart"></i> in Eswatini. All Rights Reserved.</p>
                <ul class="cfci-footer-legal">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Use</a></li>
                    <li><a href="#">Cookie Policy</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries (ORDER IS CRITICAL) -->
    <script src="assets/js/jquery-1.11.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>

    <!-- Custom main.js (contains showToast, navbar scroll, etc.) -->
    <script src="assets/js/main.js"></script>

    <!-- Additional footer-only styles (no overrides needed) -->
    <style>
        .cfci-footer {
            background: linear-gradient(180deg, #1a2b3c 0%, #152230 40%, #0f1a26 100%);
            color: rgba(255,255,255,0.8);
            padding-top: 60px;
            position: relative;
            margin-top: 0;
        }
        .cfci-footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-yellow, #e67e22), #f39c12, var(--primary-yellow, #e67e22));
            z-index: 2;
        }
        .cfci-footer-pattern {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(230,126,34,0.04) 0%, transparent 60%),
                        radial-gradient(circle at 80% 70%, rgba(26,82,118,0.06) 0%, transparent 50%);
            pointer-events: none;
        }
        .cfci-footer-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 3;
        }
        .cfci-footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1.2fr;
            gap: 40px;
            padding-bottom: 40px;
        }
        .cfci-footer-col h4 {
            font-family: 'Montserrat', sans-serif;
            color: #fff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            position: relative;
        }
        .cfci-footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 35px; height: 3px;
            background: var(--primary-yellow, #e67e22);
            transition: width 0.3s;
        }
        .cfci-footer-col:hover h4::after { width: 55px; }
        .cfci-footer-logo { height: 45px; margin-bottom: 15px; }
        .cfci-footer-about { font-size: 0.9rem; color: rgba(255,255,255,0.65); margin-bottom: 20px; }
        .cfci-footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: #fff;
            margin-right: 8px;
            transition: all 0.3s;
        }
        .cfci-footer-social a:hover {
            background: var(--primary-yellow, #e67e22);
            color: #000;
            transform: translateY(-3px);
        }
        .cfci-footer-links { list-style: none; padding: 0; }
        .cfci-footer-links li { margin-bottom: 10px; }
        .cfci-footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
        }
        .cfci-footer-links a:hover { color: var(--primary-yellow, #e67e22); padding-left: 5px; }
        .cfci-footer-contact { list-style: none; padding: 0; }
        .cfci-footer-contact li {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            color: rgba(255,255,255,0.65);
        }
        .cfci-footer-contact i { color: var(--primary-yellow, #e67e22); width: 18px; margin-top: 3px; }
        .cfci-footer-newsletter-form {
            display: flex;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .cfci-footer-newsletter-form input {
            flex: 1;
            padding: 10px 18px;
            border: none;
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .cfci-footer-newsletter-form input::placeholder { color: rgba(255,255,255,0.4); }
        .cfci-footer-newsletter-form button {
            background: var(--primary-yellow, #e67e22);
            color: #000;
            border: none;
            font-weight: 600;
            padding: 10px 18px;
            cursor: pointer;
        }
        .cfci-footer-divider { border-color: rgba(255,255,255,0.2); margin: 30px 0 20px; }
        .cfci-footer-bottom {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
        }
        .cfci-footer-legal { list-style: none; padding: 0; display: flex; gap: 20px; }
        .cfci-footer-legal a { color: rgba(255,255,255,0.5); text-decoration: none; }
        .cfci-footer-legal a:hover { color: var(--primary-yellow, #e67e22); }
        .copyright i { color: #e74c3c; animation: heartbeat 1.5s infinite; }
        @keyframes heartbeat {
            0%,100%{transform:scale(1);} 15%{transform:scale(1.2);} 30%{transform:scale(1);} 45%{transform:scale(1.15);} 60%{transform:scale(1);}
        }
        @media (max-width: 768px) {
            .cfci-footer-grid { grid-template-columns: 1fr; }
            .cfci-footer-bottom { flex-direction: column; text-align: center; gap: 10px; }
        }
    </style>

    <!-- Newsletter & Toast handling -->
    <script>
        // This function is triggered by the newsletter form.
        // It uses the global showToast() defined in main.js.
        function handleNewsletterSubmit(e) {
            e.preventDefault();
            const input = e.target.querySelector('input');
            if (input && input.value.trim()) {
                // showToast() is defined in main.js; if for some reason it's not loaded,
                // we fall back to an alert after a tiny delay (avoids race condition).
                if (typeof showToast === 'function') {
                    showToast('🎉 Thank you for subscribing!', 'success');
                } else {
                    // Fallback – extremely unlikely with correct script load order
                    setTimeout(() => {
                        if (typeof showToast === 'function') {
                            showToast('🎉 Thank you for subscribing!', 'success');
                        } else {
                            alert('Thank you for subscribing!');
                        }
                    }, 300);
                }
                input.value = '';
            }
        }
    </script>
</body>
</html>