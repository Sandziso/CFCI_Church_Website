<?php
$page = basename($_SERVER['PHP_SELF']);
?>
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
    if ($page == 'index.php') {
        echo '<link rel="stylesheet" href="assets/css/home.css">';
    } elseif ($page == 'about.php') {
        echo '<link rel="stylesheet" href="assets/css/about.css">';
    } elseif ($page == 'contact.php') {
        echo '<link rel="stylesheet" href="assets/css/contact.css">';
    } elseif ($page == 'ministries.php') {
        echo '<link rel="stylesheet" href="assets/css/ministries.css">';
    } elseif ($page == 'events.php') {
        echo '<link rel="stylesheet" href="assets/css/events.css">';
    } elseif ($page == 'sermons.php') {
        echo '<link rel="stylesheet" href="assets/css/sermons.css">';
    }
    ?>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="sr-only skip-link">Skip to main content</a>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>