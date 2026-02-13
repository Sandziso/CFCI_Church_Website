<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFCI Church - Christian Family Centre International</title>
    <meta name="description" content="Christian Family Centre International - Building strong families and empowering communities in Manzini, Eswatini through the word of God.">
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
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    
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
    
    <!-- Page-specific CSS -->
    <?php 
    $page = basename($_SERVER['PHP_SELF']);
    if ($page == 'index.php') {
        echo '<link rel="stylesheet" href="assets/css/home.css">';
    } elseif ($page == 'about.php') {
        echo '<link rel="stylesheet" href="assets/css/about.css">';
    } elseif ($page == 'contact.php') {
        echo '<link rel="stylesheet" href="assets/css/contact.css">';
    }
    ?>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="sr-only skip-link">Skip to main content</a>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <header>
        <div class="header-top">
            <div class="container header-top-content">
                <div class="church-info">
                    <span><i class="far fa-clock"></i> Sunday 9:00 AM - 12:00 PM</span>
                    <span><i class="fas fa-map-marker-alt"></i> Ntunja Township behind William Pitcher College</span>
                    <span><i class="fas fa-phone"></i> +268 7600 0000</span>
                </div>
                <div class="social-links">
                    <a href="https://facebook.com/cfci-eswatini" title="Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/cfci-eswatini" title="Twitter" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                    <a href="https://youtube.com/c/cfci-eswatini" title="YouTube" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                    <a href="https://instagram.com/cfci-eswatini" title="Instagram" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container header-content">
                <div class="logo-section">
                    <a href="../index.php" class="logo">
                        <img src="assets/images/logo.png" alt="CFCI Church Logo" loading="lazy">
                    </a>
                    <div class="church-name">
                        <h1>CFCI Church</h1>
                        <p>Christian Family Centre International</p>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="nav-menu" id="navMenu" aria-label="Main Navigation">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item nav-dropdown">
                        <a href="about.php" class="nav-link">
                            <i class="fas fa-church"></i>
                            <span>About Us</span>
                            <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="about.php" class="dropdown-item"><i class="fas fa-info-circle"></i> Our Story</a>
                            <a href="beliefs.php" class="dropdown-item"><i class="fas fa-scroll"></i> Our Beliefs</a>
                            <a href="leadership.php" class="dropdown-item"><i class="fas fa-user-tie"></i> Leadership</a>
                            <a href="vision.php" class="dropdown-item"><i class="fas fa-eye"></i> Vision & Mission</a>
                        </div>
                    </li>
                    <li class="nav-item nav-dropdown">
                        <a href="ministries.php" class="nav-link">
                            <i class="fas fa-hands-helping"></i>
                            <span>Ministries</span>
                            <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="ministries.php" class="dropdown-item"><i class="fas fa-list"></i> All Ministries</a>
                            <a href="ministry.php?id=youth" class="dropdown-item"><i class="fas fa-users"></i> Youth Ministry</a>
                            <a href="ministry.php?id=children" class="dropdown-item"><i class="fas fa-child"></i> Children's Church</a>
                            <a href="ministry.php?id=women" class="dropdown-item"><i class="fas fa-female"></i> Women's Ministry</a>
                            <a href="ministry.php?id=men" class="dropdown-item"><i class="fas fa-male"></i> Men's Ministry</a>
                            <a href="ministry.php?id=outreach" class="dropdown-item"><i class="fas fa-hand-holding-heart"></i> Outreach</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="events.php" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Events</span>
                        </a>
                    </li>
                    <li class="nav-item nav-dropdown">
                        <a href="sermons.php" class="nav-link">
                            <i class="fas fa-podcast"></i>
                            <span>Media</span>
                            <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="sermons.php" class="dropdown-item"><i class="fas fa-podcast"></i> Sermons</a>
                            <a href="gallery.php" class="dropdown-item"><i class="fas fa-images"></i> Gallery</a>
                            <a href="blog.php" class="dropdown-item"><i class="fas fa-newspaper"></i> Blog</a>
                            <a href="livestream.php" class="dropdown-item"><i class="fas fa-video"></i> Live Stream</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                </nav>
                
                <div class="auth-buttons">
                    <a href="give.php" class="btn give-btn auth-btn">
                        <i class="fas fa-heart"></i>
                        Give
                    </a>
                    <a href="auth/login.php" class="login-btn auth-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                </div>
                
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle mobile menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <main id="main-content">
        <!-- Main content goes here -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Header JavaScript -->
    <script src="assets/js/header.js"></script>
    <!-- Page-specific JavaScript -->
    <?php 
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