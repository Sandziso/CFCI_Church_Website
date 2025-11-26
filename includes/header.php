<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFCI Church - Christian Family Centre International</title>
    <meta name="description" content="Christian Family Centre International - Building strong families and empowering communities in Manzini, Eswatini through the word of God.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        /* ===== ENHANCED BASE STYLES & VARIABLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --primary-light: #2e86c1;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --text: #333;
            --text-light: #777;
            --white: #fff;
            --gray: #f8f9fa;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        }

        body {
            background-color: var(--white);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .container {
            width: 100%;
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 20px;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--gradient);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--gradient-secondary);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
        }

        .btn-outline-light {
            border: 2px solid var(--white);
            color: var(--white);
            background: transparent;
        }

        .btn-outline-light:hover {
            background: var(--white);
            color: var(--primary);
        }

        /* ===== ENHANCED HEADER STYLES ===== */
        header {
            background: var(--gradient);
            color: var(--white);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .header-top {
            background: rgba(0, 0, 0, 0.2);
            padding: 8px 0;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .header-top-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .church-info {
            display: flex;
            gap: 25px;
        }

        .church-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .church-info i {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .header-top .social-links {
            display: flex;
            gap: 12px;
        }

        .header-top .social-links a {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .header-top .social-links a:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .header-main {
            padding: 12px 0;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            height: 65px;
            width: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border-radius: 10px;
            padding: 8px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: var(--transition);
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            height: 100%;
            width: auto;
            object-fit: contain;
        }

        .church-name {
            display: flex;
            flex-direction: column;
        }

        .church-name h1 {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
            background: linear-gradient(45deg, var(--white), #e8f6f3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .church-name p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }

        .nav-menu {
            display: flex;
            gap: 5px;
            margin: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            padding: 12px 16px;
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            border-radius: 6px;
            transition: var(--transition);
            font-size: 0.9rem;
            color: rgba(255,255,255,0.9);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        .nav-link span {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            color: var(--white);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
        }

        .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px 2px 0 0;
        }

        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--white);
            min-width: 220px;
            box-shadow: var(--shadow-lg);
            border-radius: 8px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            border: none;
        }

        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 10px 20px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--light);
            color: var(--primary);
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        .auth-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .auth-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .login-btn {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.3);
            color: var(--white);
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            color: var(--white);
        }

        .give-btn {
            background: var(--secondary);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }

        .give-btn:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(230, 126, 34, 0.4);
            color: var(--white);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: var(--transition);
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
        }

        .mobile-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* ===== MOBILE RESPONSIVE STYLES ===== */
        @media (max-width: 1200px) {
            .nav-menu { gap: 2px; }
            .nav-link { padding: 10px 12px; }
            .church-name h1 { font-size: 1.6rem; }
        }

        @media (max-width: 992px) {
            .header-top { display: none; }
            .nav-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 320px;
                height: 100vh;
                background: var(--gradient);
                flex-direction: column;
                padding: 80px 25px 30px;
                transition: var(--transition);
                box-shadow: -5px 0 25px rgba(0, 0, 0, 0.15);
                z-index: 999;
                overflow-y: auto;
                gap: 5px;
            }
            .nav-menu.active { 
                right: 0;
                animation: slideInRight 0.3s ease-out;
            }
            .nav-link {
                flex-direction: row;
                justify-content: flex-start;
                padding: 15px 20px;
                border-radius: 8px;
                font-size: 1rem;
            }
            .nav-link i {
                font-size: 1.2rem;
                width: 24px;
            }
            .nav-link span {
                font-size: 0.9rem;
            }
            .nav-dropdown .dropdown-menu {
                position: static;
                background: rgba(255,255,255,0.1);
                box-shadow: none;
                margin: 10px 0;
                opacity: 1;
                visibility: visible;
                transform: none;
            }
            .dropdown-item {
                color: rgba(255,255,255,0.9);
                padding: 12px 20px 12px 40px;
            }
            .dropdown-item:hover {
                background: rgba(255,255,255,0.2);
                color: var(--white);
            }
            .mobile-toggle { 
                display: flex;
                z-index: 1001;
            }
            .auth-buttons { display: none; }
            .church-name h1 { font-size: 1.5rem; }
            .logo { height: 55px; }
        }

        @media (max-width: 576px) {
            .logo-section { gap: 10px; }
            .logo { height: 50px; padding: 6px 10px; }
            .church-name h1 { font-size: 1.3rem; }
            .church-name p { font-size: 0.75rem; }
            .nav-menu { width: 280px; }
        }

        /* Mobile Menu Animation */
        @keyframes slideInRight {
            from { 
                right: -100%;
                opacity: 0;
            }
            to { 
                right: 0;
                opacity: 1;
            }
        }

        /* Mobile Menu Overlay */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<header>
    <div class="header-top">
        <div class="container header-top-content">
            <div class="church-info">
                <span><i class="far fa-clock"></i> Sunday 9:00 AM - 12:00 PM</span>
                <span><i class="fas fa-map-marker-alt"></i> Ntunja Township behind William Pitcher College</span>
            </div>
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container header-content">
            <div class="logo-section">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="CFCI Church Logo">
                </a>
                <div class="church-name">
                    <h1>CFCI Church</h1>
                    <p>Christian Family Centre International</p>
                </div>
            </div>

            <nav class="nav-menu" id="navMenu">
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
                <a href="../auth/login.php" class="login-btn auth-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </div>

            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<main>
    <div class="container">