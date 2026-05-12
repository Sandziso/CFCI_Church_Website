<?php
/**
 * header.php – Enhanced Header with Security & Bootstrap
 * Uses absolute URLs so it works from any page location.
 * Loads main-functions.php to initialise DB, Auth, CSRF, etc.
 */
require_once __DIR__ . '/main-functions.php';   // defines SITE_URL, auth, session, helpers

// Make navigation active‑state work even if the page didn’t set $current_page
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>CFCI Church – Christian Family Centre International</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="CFCI, church, Manzini, Eswatini, Christian, family, worship, prayer" name="keywords" />
    <meta content="Christian Family Centre International – Building strong families and empowering communities in Manzini, Eswatini through the word of God." name="description" />

    <!-- Favicon -->
    <link href="<?= SITE_URL ?>assets/images/favicon.ico" rel="icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Open+Sans:wght@400;500;600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Icon Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Libraries (local copies) -->
    <link href="<?= SITE_URL ?>lib/animate/animate.min.css" rel="stylesheet" />
    <link href="<?= SITE_URL ?>lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet" />

    <!-- Bootstrap CSS (local) -->
    <link href="<?= SITE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>assets/css/style.css" rel="stylesheet" />

    <!-- Enhanced Header Styles (all variables are defined in style.css) -->
    <style>
        :root {
            /* Fallback in case style.css not loaded */
            --primary-blue: #1a5276;
            --primary-yellow: #e67e22;
            --text-light: #777777;
        }

        /* ============ ENHANCED HEADER ============ */
        .cfci-header {
            position: sticky;
            top: 0;
            z-index: 1100;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(26, 82, 118, 0.06);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.35s ease;
        }
        .cfci-header.scrolled {
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.10);
        }
        .cfci-header .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cfci-header .navbar-brand img {
            height: 46px;
            transition: height 0.35s;
        }
        .cfci-header.scrolled .navbar-brand img {
            height: 40px;
        }
        .cfci-header .navbar-brand h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.55rem;
            color: var(--primary-blue);
            margin: 0;
            line-height: 1.2;
        }
        .cfci-header .navbar-brand h1 .cfci-accent {
            color: var(--primary-yellow);
            position: relative;
        }
        .cfci-header .navbar-brand .cfci-accent::after {
            content: '';
            position: absolute;
            bottom: 2px; left: 0;
            width: 100%; height: 3px;
            background: var(--primary-yellow);
            border-radius: 2px;
            opacity: 0.5;
        }
        .cfci-header .navbar-brand .logo-subtitle {
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            font-size: 0.62rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-light);
            display: block;
        }

        /* Navigation pills */
        .cfci-header .navbar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            padding: 10px 16px !important;
            border-radius: 28px;
            transition: all 0.35s;
        }
        .cfci-header .navbar-nav .nav-link:hover {
            color: var(--primary-blue) !important;
            background: rgba(26, 82, 118, 0.06);
        }
        .cfci-header .navbar-nav .nav-link.active {
            color: var(--primary-blue) !important;
            background: rgba(26, 82, 118, 0.09);
            font-weight: 600;
        }
        .cfci-header .dropdown-menu {
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.14);
            border-radius: 20px;
            padding: 10px 6px;
            margin-top: 10px;
        }
        .cfci-header .dropdown-item {
            border-radius: 14px;
            padding: 10px 16px;
            transition: all 0.2s;
        }
        .cfci-header .dropdown-item:hover {
            background: rgba(26, 82, 118, 0.05);
            color: var(--primary-blue);
            padding-left: 24px;
        }

        /* Login/Register buttons */
        .cfci-btn-login {
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            background: transparent;
            padding: 9px 20px;
            border-radius: 28px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.35s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .cfci-btn-login:hover {
            background: var(--primary-blue);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(26,82,118,0.28);
        }
        .cfci-btn-register {
            background: linear-gradient(135deg, var(--primary-yellow), #d35400);
            color: #fff;
            padding: 9px 20px;
            border-radius: 28px;
            font-weight: 600;
            font-size: 0.88rem;
            border: none;
            transition: all 0.35s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 16px rgba(230,126,34,0.30);
        }
        .cfci-btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(230,126,34,0.44);
            color: #fff;
        }

        /* Mobile adjustments */
        @media (max-width: 992px) {
            .cfci-header .navbar-nav {
                background: rgba(255,255,255,0.98);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 10px;
                margin-top: 10px;
            }
            .cfci-header .navbar-nav .nav-link {
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>
    <!-- Spinner -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>

    <!-- ========== ENHANCED NAVBAR ========== -->
    <nav class="navbar navbar-expand-lg cfci-header" id="cfciHeader">
        <div class="container">
            <a href="<?= SITE_URL ?>" class="navbar-brand">
                <img src="<?= SITE_URL ?>assets/images/logo.png" alt="CFCI Church Logo">
                <div>
                    <h1 class="m-0"><span class="cfci-accent">CFCI</span> Church</h1>
                    <span class="logo-subtitle">Christian Family Centre International</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto">
                    <a href="<?= SITE_URL ?>" class="nav-item nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Home
                    </a>

                    <!-- About dropdown -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= in_array($current_page, ['about.php','beliefs.php','leadership.php','vision.php']) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            <i class="fas fa-info-circle"></i> About
                        </a>
                        <div class="dropdown-menu">
                            <a href="<?= SITE_URL ?>about.php" class="dropdown-item <?= ($current_page == 'about.php') ? 'active' : '' ?>">📖 Our Story</a>
                            <a href="<?= SITE_URL ?>beliefs.php" class="dropdown-item <?= ($current_page == 'beliefs.php') ? 'active' : '' ?>">✝️ Our Beliefs</a>
                            <a href="<?= SITE_URL ?>leadership.php" class="dropdown-item <?= ($current_page == 'leadership.php') ? 'active' : '' ?>">👥 Leadership</a>
                            <a href="<?= SITE_URL ?>vision.php" class="dropdown-item <?= ($current_page == 'vision.php') ? 'active' : '' ?>">🎯 Vision & Mission</a>
                        </div>
                    </div>

                    <!-- Ministries dropdown -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= in_array($current_page, ['ministries.php','ministry.php']) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            <i class="fas fa-hands-helping"></i> Ministries
                        </a>
                        <div class="dropdown-menu">
<a href="<?= SITE_URL ?>ministry-details.php?id=youth" class="dropdown-item">🔥 Youth</a>
<a href="<?= SITE_URL ?>ministry-details.php?id=children" class="dropdown-item">👶 Children</a>
<a href="<?= SITE_URL ?>ministry-details.php?id=women" class="dropdown-item">🌸 Women</a>
<a href="<?= SITE_URL ?>ministry-details.php?id=men" class="dropdown-item">🛡️ Men</a>
<a href="<?= SITE_URL ?>ministry-details.php?id=outreach" class="dropdown-item">🌍 Outreach</a>
                        </div>
                    </div>

                    <a href="<?= SITE_URL ?>events.php" class="nav-item nav-link <?= ($current_page == 'events.php') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i> Events
                    </a>

                    <!-- Media dropdown -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= in_array($current_page, ['sermons.php','gallery.php','blog.php','livestream.php']) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            <i class="fas fa-play-circle"></i> Media
                        </a>
                        <div class="dropdown-menu">
                            <a href="<?= SITE_URL ?>sermons.php" class="dropdown-item <?= ($current_page == 'sermons.php') ? 'active' : '' ?>">🎙️ Sermons</a>
                            <a href="<?= SITE_URL ?>gallery.php" class="dropdown-item <?= ($current_page == 'gallery.php') ? 'active' : '' ?>">🖼️ Gallery</a>
                            <a href="<?= SITE_URL ?>blog.php" class="dropdown-item <?= ($current_page == 'blog.php') ? 'active' : '' ?>">📝 Blog</a>
                            <a href="<?= SITE_URL ?>livestream.php" class="dropdown-item <?= ($current_page == 'livestream.php') ? 'active' : '' ?>">🔴 Live Stream</a>
                        </div>
                    </div>

                    <a href="<?= SITE_URL ?>contact.php" class="nav-item nav-link <?= ($current_page == 'contact.php') ? 'active' : '' ?>">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2 ms-lg-3">
                    <a href="<?= SITE_URL ?>login.php" class="cfci-btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?= SITE_URL ?>register.php" class="cfci-btn-register"><i class="fas fa-user-plus"></i> Register</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- END NAVBAR -->