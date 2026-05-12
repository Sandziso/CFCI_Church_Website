// Header functionality – matches actual HTML IDs/classes
document.addEventListener('DOMContentLoaded', function () {
    const primaryNav    = document.getElementById('primaryNav');
    const mobileToggle  = document.getElementById('mobileToggle');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const siteHeader    = document.getElementById('siteHeader');

    // Safety: if critical elements missing, stop
    if (!primaryNav || !mobileToggle || !mobileOverlay) return;

    // Mobile menu toggle
    mobileToggle.addEventListener('click', () => {
        primaryNav.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        document.body.style.overflow = primaryNav.classList.contains('active') ? 'hidden' : '';
        const icon = mobileToggle.querySelector('i');
        if (icon) {
            if (primaryNav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });

    // Close mobile menu when clicking overlay
    mobileOverlay.addEventListener('click', () => {
        primaryNav.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
        const icon = mobileToggle.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Mobile dropdown toggle – uses .has-dropdown and .dropdown
    document.querySelectorAll('.has-dropdown > .nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            if (window.innerWidth < 992) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = link.parentElement; // .has-dropdown
                // Close other dropdowns
                document.querySelectorAll('.has-dropdown.active').forEach(other => {
                    if (other !== dropdown) other.classList.remove('active');
                });
                dropdown.classList.toggle('active');
            }
        });
    });

    // Close mobile menu when clicking on non-dropdown links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                primaryNav.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
                document.querySelectorAll('.has-dropdown.active').forEach(d => d.classList.remove('active'));
            }
        });
    });

    // Desktop dropdown hover (CSS handles most, but we can add smooth transitions)
    function initDropdownHover() {
        if (window.innerWidth >= 992) {
            document.querySelectorAll('.has-dropdown').forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown');
                if (!menu) return;
                dropdown.addEventListener('mouseenter', () => {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                });
                dropdown.addEventListener('mouseleave', () => {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(10px)';
                });
            });
        }
    }

    // Sticky header
    let lastScrollTop = 0;
    window.addEventListener('scroll', () => {
        if (!siteHeader) return;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > 100) {
            siteHeader.classList.add('sticky');
        } else {
            siteHeader.classList.remove('sticky');
        }
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            siteHeader.style.transform = 'translateY(-100%)';
        } else {
            siteHeader.style.transform = 'translateY(0)';
        }
        lastScrollTop = scrollTop;
    });

    // Initialize on load and resize
    initDropdownHover();
    window.addEventListener('resize', () => {
        initDropdownHover();
        if (window.innerWidth >= 992) {
            primaryNav.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            const icon = mobileToggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
            document.querySelectorAll('.has-dropdown.active').forEach(d => d.classList.remove('active'));
        }
    });

    // Active page highlighting
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
});