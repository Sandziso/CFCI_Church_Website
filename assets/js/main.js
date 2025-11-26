// main.js - Enhanced Version for CFCI Church Website

document.addEventListener('DOMContentLoaded', function() {
    // =====================
    // 1. NAVBAR FUNCTIONALITY
    // =====================
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-links a');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navMenu = document.querySelector('.nav-links');
    
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
        
        // Update active nav link based on scroll position
        updateActiveNavLink();
    });
    
    // Mobile menu toggle
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuBtn.innerHTML = navMenu.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target) && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }

    // =====================
    // 2. SMOOTH SCROLLING
    // =====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (!targetElement) return;
            
            const headerOffset = 80; // Adjust based on your header height
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
            
            // Close mobile menu if open
            if (navMenu && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                if (mobileMenuBtn) {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        });
    });
    
    // Function to update active nav link based on scroll position
    function updateActiveNavLink() {
        const scrollPosition = window.scrollY;
        
        // Check each section to see if it's in view
        document.querySelectorAll('section').forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    // Check if the link's href matches the section's ID (after stripping BASE_URL if present)
                    const linkHrefId = link.getAttribute('href').split('#')[1];
                    if (linkHrefId === sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    // =====================
    // 3. BACK TO TOP BUTTON
    // =====================
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'back-to-top';
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
    
    // Scroll to top functionality
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // =====================
    // 4. FORM VALIDATION & TOAST NOTIFICATIONS
    // =====================
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        // Add real-time validation
        const formInputs = contactForm.querySelectorAll('input, textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('invalid');
                } else {
                    this.classList.add('invalid');
                }
            });
        });
        
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let valid = true;
            
            // Validate all required fields
            const requiredInputs = this.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('invalid');
                    valid = false;
                } else {
                    input.classList.remove('invalid');
                }
            });
            
            // Validate email format
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && !validateEmail(emailInput.value)) {
                emailInput.classList.add('invalid');
                valid = false;
            }
            
            if (!valid) {
                showToast('Please fill in all required fields correctly', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual AJAX call to a PHP processing script)
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                showToast('Thank you for your message! We will get back to you soon.', 'success');
                this.reset();
                
                // Remove invalid classes
                formInputs.forEach(input => input.classList.remove('invalid'));
            }, 1500);
        });
    }
    
    // Email validation helper
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Toast notification function
    function showToast(message, type) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto-remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    // =====================
    // 5. LAZY LOADING IMAGES
    // =====================
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.onload = () => {
                        img.classList.add('loaded');
                    };
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }

    // =====================
    // 6. ANIMATIONS ON SCROLL
    // =====================
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    
    if ('IntersectionObserver' in window) {
        const elementObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        });
        
        animatedElements.forEach(element => {
            elementObserver.observe(element);
        });
    } else {
        // Fallback for older browsers
        animatedElements.forEach(element => {
            element.classList.add('visible');
        });
    }

    // =====================
    // 7. GOOGLE MAPS INTEGRATION
    // =====================
    // Make initMap globally accessible if it's called by the Google Maps API script
    window.initMap = function() {
        const churchLocation = { 
            lat: -26.525779, 
            lng: 31.314510 // Coordinates for Manzini, Swaziland
        };
        
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: churchLocation,
            styles: [
                {
                    "featureType": "poi",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "featureType": "transit.station",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.fill",
                    "stylers": [{"color": "#ffffff"}]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.stroke",
                    "stylers": [{"color": "#e0e0e0"}]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry.fill",
                    "stylers": [{"color": "#e0f2f7"}]
                },
                {
                    "featureType": "landscape",
                    "elementType": "geometry.fill",
                    "stylers": [{"color": "#f5f5f5"}]
                },
                {
                    "featureType": "administrative.locality",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#333333"}]
                }
            ]
        });
        
        new google.maps.Marker({
            position: churchLocation,
            map: map,
            title: 'Christian Family Centre International, Manzini',
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });
    };
    
    // Load Google Maps API only when needed
    const mapElement = document.getElementById('map'); // Corrected ID
    let mapLoaded = false;
    
    function loadGoogleMapsScript() {
        if (mapLoaded) return;
        
        const script = document.createElement('script');
        // IMPORTANT: Replace 'YOUR_API_KEY' with your actual Google Maps API Key
        script.src = `https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        
        mapLoaded = true;
    }
    
    // Load map when user scrolls near it
    if (mapElement) { // Use mapElement instead of mapSection
        const mapObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadGoogleMapsScript();
                mapObserver.disconnect();
            }
        }, { threshold: 0.1 });
        
        mapObserver.observe(mapElement);
    }

    // =====================
    // 8. SERVICE TIMES COUNTDOWN
    // =====================
    function updateServiceCountdown() {
        const now = new Date();
        const day = now.getDay(); // Sunday = 0, Monday = 1, etc.
        const nextSunday = new Date(now);
        
        // Calculate days until next Sunday
        const daysUntilSunday = day === 0 ? 7 : 7 - day;
        nextSunday.setDate(now.getDate() + daysUntilSunday);
        nextSunday.setHours(9, 0, 0, 0); // Set to 9:00 AM
        
        const diff = nextSunday - now;
        
        if (diff > 0) {
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const countdownEl = document.getElementById('service-countdown');
            if (countdownEl) {
                countdownEl.innerHTML = `
                    <div>${days}<span>Days</span></div>
                    <div>${hours}<span>Hours</span></div>
                    <div>${minutes}<span>Minutes</span></div>
                    <div>${seconds}<span>Seconds</span></div>
                `;
            }
        }
    }
    
    // Initialize and update countdown every second
    const countdownEl = document.getElementById('service-countdown');
    if (countdownEl) {
        updateServiceCountdown();
        setInterval(updateServiceCountdown, 1000);
    }
});
