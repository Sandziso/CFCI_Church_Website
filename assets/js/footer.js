// Footer functionality with improvements
document.addEventListener('DOMContentLoaded', function() {
    // Back to top functionality with throttle
    const backToTop = document.getElementById('backToTop');
    
    if (backToTop) {
        let scrollTimeout;
        
        const handleScroll = () => {
            if (scrollTimeout) {
                window.cancelAnimationFrame(scrollTimeout);
            }
            
            scrollTimeout = window.requestAnimationFrame(() => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });
        };
        
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            backToTop.classList.remove('show');
            
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Newsletter form submission with improved UX
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const submitBtn = newsletterForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Add input validation styling
        emailInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                this.classList.remove('is-valid', 'is-invalid');
            } else if (validateEmail(this.value.trim())) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
        
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            
            if (!email) {
                showNotification('Please enter your email address.', 'error');
                emailInput.focus();
                return;
            }
            
            if (!validateEmail(email)) {
                showNotification('Please enter a valid email address.', 'error');
                emailInput.focus();
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('newsletter-loading');
            submitBtn.disabled = true;
            
            try {
                // Simulate API call - replace with actual API call in production
                await simulateAPICall(email);
                
                // Show success state
                newsletterForm.classList.add('newsletter-success');
                newsletterForm.reset();
                emailInput.classList.remove('is-valid', 'is-invalid');
                
                showNotification('Thank you for subscribing to our newsletter!', 'success');
                
                // Track subscription if analytics is available
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'newsletter_subscription', {
                        'event_category': 'engagement',
                        'event_label': 'footer_newsletter'
                    });
                }
                
            } catch (error) {
                showNotification('Unable to subscribe at the moment. Please try again later.', 'error');
                console.error('Newsletter subscription error:', error);
            } finally {
                // Reset button state
                submitBtn.classList.remove('newsletter-loading');
                submitBtn.disabled = false;
                
                // Remove success animation after delay
                setTimeout(() => {
                    newsletterForm.classList.remove('newsletter-success');
                }, 300);
            }
        });
    }
    
    // Simulate API call (replace with actual fetch in production)
    function simulateAPICall(email) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate 90% success rate
                if (Math.random() > 0.1) {
                    resolve({ success: true, message: 'Subscribed successfully' });
                } else {
                    reject(new Error('Network error'));
                }
            }, 800);
        });
    }
    
    // Email validation helper
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Enhanced notification system
    function showNotification(message, type = 'success') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type === 'success' ? 'success' : 'danger'} border-0`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 90vw;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            background: ${type === 'success' ? 'rgba(40, 167, 69, 0.95)' : 'rgba(220, 53, 69, 0.95)'};
            color: white;
            border: none;
            padding: 15px 50px 15px 20px;
        `;
        
        // Add icon based on type
        const icon = type === 'success' ? '✓' : '!';
        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="background: rgba(255,255,255,0.2); width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                    ${icon}
                </div>
                <div style="flex: 1; font-size: 0.9rem; line-height: 1.4;">${message}</div>
                <button type="button" class="btn-close btn-close-white" style="position: absolute; top: 15px; right: 15px; opacity: 0.8;"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Add close button functionality
        const closeBtn = notification.querySelector('.btn-close');
        closeBtn.addEventListener('click', () => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });
        
        // Auto remove after 5 seconds
        const autoRemove = setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Pause auto-remove on hover
        notification.addEventListener('mouseenter', () => {
            clearTimeout(autoRemove);
        });
        
        notification.addEventListener('mouseleave', () => {
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        });
    }
    
    // Enhanced hover effects for footer links
    document.querySelectorAll('.footer-links a, .footer-bottom-links a').forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Smooth scroll for footer anchor links
    document.querySelectorAll('footer a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Close mobile menu if open
                const mobileMenu = document.querySelector('.nav-menu.active');
                if (mobileMenu) {
                    mobileMenu.classList.remove('active');
                    document.querySelector('.mobile-overlay')?.classList.remove('active');
                }
                
                // Smooth scroll to target
                const headerHeight = document.querySelector('header')?.offsetHeight || 100;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Set current year in copyright
    const currentYear = new Date().getFullYear();
    const copyrightElements = document.querySelectorAll('[data-current-year]');
    
    copyrightElements.forEach(element => {
        element.textContent = currentYear;
    });
    
    // Fallback for PHP year
    const copyrightText = document.querySelector('.copyright');
    if (copyrightText && !copyrightText.textContent.includes(currentYear)) {
        copyrightText.innerHTML = `&copy; ${currentYear} Christian Family Centre International. All Rights Reserved.`;
    }
    
    // Initialize tooltips for social links
    const socialLinks = document.querySelectorAll('.social-link[title]');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'social-tooltip';
            tooltip.textContent = this.getAttribute('title');
            tooltip.style.cssText = `
                position: absolute;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.75rem;
                white-space: nowrap;
                z-index: 10000;
                transform: translateY(-100%);
                margin-top: -8px;
            `;
            this.appendChild(tooltip);
        });
        
        link.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.social-tooltip');
            if (tooltip) tooltip.remove();
        });
    });
    
    // Lazy load footer images
    const footerImages = document.querySelectorAll('footer img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        footerImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        footerImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
});

// Add CSS for validation states
const style = document.createElement('style');
style.textContent = `
    .is-valid {
        border-color: #28a745 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
    }
    
    .custom-notification {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);