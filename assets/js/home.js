// Home page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Hero section animations
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.classList.add('animate__animated', 'animate__fadeInUp');
    }

    // Countdown timer
    function updateCountdown() {
        const now = new Date();
        const nextSunday = new Date();
        nextSunday.setDate(now.getDate() + ((7 - now.getDay()) % 7 || 7));
        nextSunday.setHours(9, 0, 0, 0);
        if (nextSunday <= now) nextSunday.setDate(nextSunday.getDate() + 7);

        const diff = nextSunday - now;
        if (diff <= 0) return;

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % 86400000) / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);

        const dayEl = document.getElementById('days');
        const hourEl = document.getElementById('hours');
        const minEl = document.getElementById('minutes');
        const secEl = document.getElementById('seconds');

        if (dayEl) dayEl.innerText = days.toString().padStart(2, '0');
        if (hourEl) hourEl.innerText = hours.toString().padStart(2, '0');
        if (minEl) minEl.innerText = minutes.toString().padStart(2, '0');
        if (secEl) secEl.innerText = seconds.toString().padStart(2, '0');
    }

    if (document.getElementById('days')) {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // Initialize home-only carousels (if any)
    if (typeof $ !== 'undefined' && $('.home-carousel').length) {
        $('.home-carousel').owlCarousel({
            loop: true,
            margin: 30,
            nav: true,
            dots: true,
            responsive: {
                0: { items: 1 },
                768: { items: 2 },
                992: { items: 3 }
            }
        });
    }
});