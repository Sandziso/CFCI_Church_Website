// main.js - Enhanced Version for CFCI Church Website
// Integrates all libraries from lib/ folder: WOW, OwlCarousel, Waypoints, Easing

document.addEventListener('DOMContentLoaded', function() {
    // =====================
    // 1. INIT WOW ANIMATIONS
    // =====================
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }

    // =====================
    // 2. NAVBAR SCROLL EFFECT
    // =====================
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (navbar) {
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        }
    });

    // =====================
    // 3. SMOOTH SCROLLING
    // =====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            const targetElement = document.querySelector(targetId);
            if (!targetElement) return;
            const headerOffset = 80;
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
        });
    });

    // =====================
    // 4. BACK TO TOP BUTTON
    // =====================
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
    }

    // =====================
    // 5. FORM VALIDATION & TOAST NOTIFICATIONS
    // =====================
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
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
            const requiredInputs = this.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('invalid');
                    valid = false;
                } else {
                    input.classList.remove('invalid');
                }
            });
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && !validateEmail(emailInput.value)) {
                emailInput.classList.add('invalid');
                valid = false;
            }
            if (!valid) {
                showToast('Please fill in all required fields correctly', 'error');
                return;
            }
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                showToast('Thank you for your message! We will get back to you soon.', 'success');
                this.reset();
                formInputs.forEach(input => input.classList.remove('invalid'));
            }, 1500);
        });
    }

    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    function showToast(message, type) {
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // =====================
    // 6. LAZY LOADING IMAGES
    // =====================
    const lazyImages = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.onload = () => img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        lazyImages.forEach(img => img.src = img.dataset.src);
    }

    // =====================
    // 7. ANIMATIONS ON SCROLL (CSS‑based, for .fade‑in, .slide‑in‑left, etc.)
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
        }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });
        animatedElements.forEach(element => elementObserver.observe(element));
    } else {
        animatedElements.forEach(element => element.classList.add('visible'));
    }

    // =====================
    // 8. GOOGLE MAPS (unchanged)
    // =====================
    window.initMap = function() {
        const churchLocation = { lat: -26.525779, lng: 31.314510 };
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: churchLocation,
            styles: [
                { "featureType": "poi", "stylers": [{ "visibility": "off" }] },
                { "featureType": "transit.station", "stylers": [{ "visibility": "off" }] },
                { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }] },
                { "featureType": "road", "elementType": "geometry.stroke", "stylers": [{ "color": "#e0e0e0" }] },
                { "featureType": "water", "elementType": "geometry.fill", "stylers": [{ "color": "#e0f2f7" }] },
                { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [{ "color": "#f5f5f5" }] },
                { "featureType": "administrative.locality", "elementType": "labels.text.fill", "stylers": [{ "color": "#333333" }] }
            ]
        });
        new google.maps.Marker({
            position: churchLocation,
            map: map,
            title: 'Christian Family Centre International, Manzini',
            icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        });
    };

    const mapElement = document.getElementById('map');
    if (mapElement) {
        let mapLoaded = false;
        function loadGoogleMapsScript() {
            if (mapLoaded) return;
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            mapLoaded = true;
        }
        const mapObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadGoogleMapsScript();
                mapObserver.disconnect();
            }
        }, { threshold: 0.1 });
        mapObserver.observe(mapElement);
    }

    // =====================
    // 9. SERVICE TIMES COUNTDOWN
    // =====================
    function updateServiceCountdown() {
        const now = new Date();
        const day = now.getDay();
        const nextSunday = new Date(now);
        const daysUntilSunday = day === 0 ? 7 : 7 - day;
        nextSunday.setDate(now.getDate() + daysUntilSunday);
        nextSunday.setHours(9, 0, 0, 0);
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
                    <div>${seconds}<span>Seconds</span></div>`;
            }
        }
    }
    const countdownEl = document.getElementById('service-countdown');
    if (countdownEl) {
        updateServiceCountdown();
        setInterval(updateServiceCountdown, 1000);
    }

    // =====================
    // 10. OWL CAROUSEL INITIALIZATION
    // =====================
    if (typeof $.fn.owlCarousel === 'function') {
        $('.owl-carousel').not('.testimonial-carousel').each(function() {
            $(this).owlCarousel({
                loop: true,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 5000,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });
        });
        // Separate config for testimonial carousel (1 item)
        $('.testimonial-carousel').each(function() {
            $(this).owlCarousel({
                loop: true,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 7000,
                responsive: {
                    0: { items: 1 },
                    768: { items: 1 },
                    992: { items: 1 }
                }
            });
        });
    }

    // =====================
    // 11. COUNTER ANIMATION (Custom, no counterUp plugin conflict)
    // =====================
    if (typeof Waypoint !== 'undefined') {
        $('.counter').each(function() {
            const $this = $(this);
            // Read target from 'data-target' attribute, fallback to current text
            let target = parseInt($this.attr('data-target'), 10);
            if (isNaN(target)) {
                target = parseInt($this.text(), 10) || 0;
            }
            // Store target for closure
            $this.data('target', target);

            new Waypoint({
                element: this,
                handler: function() {
                    const targetNum = $this.data('target');
                    $({ countNum: 0 }).animate(
                        { countNum: targetNum },
                        {
                            duration: 2000,
                            easing: 'swing',
                            step: function(now) {
                                $this.text(Math.floor(now));
                            },
                            complete: function() {
                                $this.text(targetNum);
                            }
                        }
                    );
                    this.destroy();
                },
                offset: '90%'
            });
        });
    }
});

// Hide spinner once the page has fully loaded
window.addEventListener('load', () => {
    const spinner = document.getElementById('spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
});
// main.js - Enhanced Version for CFCI Church Website
// Integrates all libraries from lib/ folder: WOW, OwlCarousel, Waypoints, Easing

document.addEventListener('DOMContentLoaded', function() {
    // =====================
    // 1. INIT WOW ANIMATIONS
    // =====================
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }

    // =====================
    // 2. NAVBAR SCROLL EFFECT (TARGET .cfci-header AND ADD class 'scrolled')
    // =====================
    const cfciHeader = document.querySelector('.cfci-header');
    if (cfciHeader) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                cfciHeader.classList.add('scrolled');
            } else {
                cfciHeader.classList.remove('scrolled');
            }
        });
    }

    // =====================
    // 3. SMOOTH SCROLLING
    // =====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            const targetElement = document.querySelector(targetId);
            if (!targetElement) return;
            const headerOffset = 80;
            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
        });
    });

    // =====================
    // 4. BACK TO TOP BUTTON
    // =====================
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
    }

    // =====================
    // 5. FORM VALIDATION & TOAST NOTIFICATIONS
    // =====================
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
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
            const requiredInputs = this.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('invalid');
                    valid = false;
                } else {
                    input.classList.remove('invalid');
                }
            });
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && !validateEmail(emailInput.value)) {
                emailInput.classList.add('invalid');
                valid = false;
            }
            if (!valid) {
                showToast('Please fill in all required fields correctly', 'error');
                return;
            }
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                showToast('Thank you for your message! We will get back to you soon.', 'success');
                this.reset();
                formInputs.forEach(input => input.classList.remove('invalid'));
            }, 1500);
        });
    }

    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    // Global showToast function (used by footer too)
    window.showToast = function(message, type) {
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    };

    // =====================
    // 6. LAZY LOADING IMAGES
    // =====================
    const lazyImages = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.onload = () => img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        lazyImages.forEach(img => img.src = img.dataset.src);
    }

    // =====================
    // 7. ANIMATIONS ON SCROLL (CSS‑based, for .fade‑in, .slide‑in‑left, etc.)
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
        }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });
        animatedElements.forEach(element => elementObserver.observe(element));
    } else {
        animatedElements.forEach(element => element.classList.add('visible'));
    }

    // =====================
    // 8. GOOGLE MAPS (lazy loaded)
    // =====================
    window.initMap = function() {
        const churchLocation = { lat: -26.525779, lng: 31.314510 };
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: churchLocation,
            styles: [
                { "featureType": "poi", "stylers": [{ "visibility": "off" }] },
                { "featureType": "transit.station", "stylers": [{ "visibility": "off" }] },
                { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }] },
                { "featureType": "road", "elementType": "geometry.stroke", "stylers": [{ "color": "#e0e0e0" }] },
                { "featureType": "water", "elementType": "geometry.fill", "stylers": [{ "color": "#e0f2f7" }] },
                { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [{ "color": "#f5f5f5" }] },
                { "featureType": "administrative.locality", "elementType": "labels.text.fill", "stylers": [{ "color": "#333333" }] }
            ]
        });
        new google.maps.Marker({
            position: churchLocation,
            map: map,
            title: 'Christian Family Centre International, Manzini',
            icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        });
    };

    const mapElement = document.getElementById('map');
    if (mapElement) {
        let mapLoaded = false;
        function loadGoogleMapsScript() {
            if (mapLoaded) return;
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            mapLoaded = true;
        }
        const mapObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadGoogleMapsScript();
                mapObserver.disconnect();
            }
        }, { threshold: 0.1 });
        mapObserver.observe(mapElement);
    }

    // =====================
    // 9. SERVICE TIMES COUNTDOWN
    // =====================
    function updateServiceCountdown() {
        const now = new Date();
        const day = now.getDay();
        const nextSunday = new Date(now);
        const daysUntilSunday = day === 0 ? 7 : 7 - day;
        nextSunday.setDate(now.getDate() + daysUntilSunday);
        nextSunday.setHours(9, 0, 0, 0);
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
                    <div>${seconds}<span>Seconds</span></div>`;
            }
        }
    }
    const countdownEl = document.getElementById('service-countdown');
    if (countdownEl) {
        updateServiceCountdown();
        setInterval(updateServiceCountdown, 1000);
    }

    // =====================
    // 10. OWL CAROUSEL INIT
    // =====================
    if (typeof $.fn.owlCarousel === 'function') {
        $('.owl-carousel').not('.testimonial-carousel').each(function() {
            $(this).owlCarousel({
                loop: true,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 5000,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });
        });
        $('.testimonial-carousel').each(function() {
            $(this).owlCarousel({
                loop: true,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 7000,
                responsive: {
                    0: { items: 1 },
                    768: { items: 1 },
                    992: { items: 1 }
                }
            });
        });
    }

    // =====================
    // 11. COUNTER ANIMATION
    // =====================
    if (typeof Waypoint !== 'undefined') {
        $('.counter').each(function() {
            const $this = $(this);
            let target = parseInt($this.attr('data-target'), 10);
            if (isNaN(target)) {
                target = parseInt($this.text(), 10) || 0;
            }
            $this.data('target', target);

            new Waypoint({
                element: this,
                handler: function() {
                    const targetNum = $this.data('target');
                    $({ countNum: 0 }).animate(
                        { countNum: targetNum },
                        {
                            duration: 2000,
                            easing: 'swing',
                            step: function(now) {
                                $this.text(Math.floor(now));
                            },
                            complete: function() {
                                $this.text(targetNum);
                            }
                        }
                    );
                    this.destroy();
                },
                offset: '90%'
            });
        });
    }
});

// Hide spinner once the page has fully loaded
window.addEventListener('load', () => {
    const spinner = document.getElementById('spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
});