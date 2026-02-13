<?php
// about.php
require_once 'includes/header.php';

// Check if user is logged in for personalized greeting
$userGreeting = '';
if (isset($_SESSION['user_id'])) {
    $userGreeting = ", " . htmlspecialchars($_SESSION['full_name']);
}
?>

<section class="page-header" style="background: linear-gradient(rgba(26, 82, 118, 0.9), rgba(26, 82, 118, 0.9)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white">About Our Church</h1>
                <p class="text-white mb-0">Discover our story, mission, and the values that guide us in serving God and our community.</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">About</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <div class="about-image-container position-relative">
                    <img src="https://via.placeholder.com/600x400" alt="Our Church Building" class="img-fluid rounded shadow-lg">
                    <div class="image-overlay"></div>
                    <div class="years-badge">
                        <span class="years-number">20+</span>
                        <span class="years-text">Years<br>Serving</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-5">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead">Welcome to Christian Family Centre International (CFCI). We're glad you're here!</p>
                    <p>Founded in 2004, CFCI began as a small prayer group meeting in homes around Manzini, Eswatini. What started with just a handful of families has grown into a vibrant community of believers dedicated to transforming lives through the power of the Gospel.</p>
                    <p>Our journey has been marked by God's faithfulness. From humble beginnings, we've witnessed miraculous growth, both spiritually and numerically. Today, we're blessed to have multiple ministries serving different age groups and needs within our community.</p>
                    
                    <div class="feature-list mt-4">
                        <div class="feature-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Family-focused ministry approach</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Bible-based teaching and preaching</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Community outreach programs</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Multi-generational worship</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vision-mission bg-light p-5 rounded mb-5">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="vision-card p-4 h-100">
                        <div class="icon-container mb-4">
                            <i class="fas fa-eye text-primary"></i>
                        </div>
                        <h3 class="mb-3">Our Vision</h3>
                        <p>To be a Christ-centered church that transforms families and communities by making disciples who make disciples, impacting generations for God's kingdom.</p>
                        <p>We envision a church where every family is a beacon of God's love, every home a place of prayer, and every member an active participant in God's mission.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mission-card p-4 h-100">
                        <div class="icon-container mb-4">
                            <i class="fas fa-bullseye text-primary"></i>
                        </div>
                        <h3 class="mb-3">Our Mission</h3>
                        <p>To lead people into a growing relationship with Jesus Christ by:</p>
                        <ul class="mission-list">
                            <li>Teaching the undiluted Word of God</li>
                            <li>Creating authentic Christian community</li>
                            <li>Serving our local community</li>
                            <li>Developing spiritual leaders</li>
                            <li>Worshipping God in spirit and truth</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="core-values mb-5">
            <div class="section-header text-center mb-5">
                <h2>Our Core Values</h2>
                <p class="lead">These values guide everything we do at CFCI</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-bible text-primary"></i>
                        </div>
                        <h4>Biblical Authority</h4>
                        <p>We believe the Bible is God's inspired Word and the final authority for our faith and practice.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-pray text-primary"></i>
                        </div>
                        <h4>Prayer & Worship</h4>
                        <p>We prioritize prayer as our foundation and worship as our lifestyle before God.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <h4>Authentic Community</h4>
                        <p>We build genuine relationships where people are known, loved, and cared for.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-hand-holding-heart text-primary"></i>
                        </div>
                        <h4>Servant Leadership</h4>
                        <p>We lead by serving, following Jesus' example of humility and compassion.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-church text-primary"></i>
                        </div>
                        <h4>Family Focus</h4>
                        <p>We strengthen families as the foundation of society and the church.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card text-center p-4 h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-globe text-primary"></i>
                        </div>
                        <h4>Missional Living</h4>
                        <p>We are called to share God's love both locally and globally.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="leadership-team bg-light p-5 rounded">
            <div class="section-header text-center mb-5">
                <h2>Meet Our Leadership</h2>
                <p class="lead">Godly leaders guiding our church family</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="leader-card text-center">
                        <div class="leader-image mb-3">
                            <img src="https://via.placeholder.com/200x200" alt="Bishop Zakes Nxumalo" class="rounded-circle">
                        </div>
                        <h4>Bishop Zakes Nxumalo</h4>
                        <p class="text-primary fw-bold">Senior Pastor</p>
                        <p>Bishop Nxumalo has served as our senior pastor for over 20 years with a passion for family restoration and community transformation.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="leader-card text-center">
                        <div class="leader-image mb-3">
                            <img src="https://via.placeholder.com/200x200" alt="Pastor Sarah Mkhwanazi" class="rounded-circle">
                        </div>
                        <h4>Pastor Sarah Mkhwanazi</h4>
                        <p class="text-primary fw-bold">Associate Pastor</p>
                        <p>Pastor Sarah oversees our women's and children's ministries, bringing energy and creativity to discipleship.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="leader-card text-center">
                        <div class="leader-image mb-3">
                            <img src="https://via.placeholder.com/200x200" alt="Deacon Thomas Dlamini" class="rounded-circle">
                        </div>
                        <h4>Deacon Thomas Dlamini</h4>
                        <p class="text-primary fw-bold">Deacon Chairman</p>
                        <p>Deacon Thomas leads our servant leadership team and oversees church operations and outreach.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-section text-center py-5 mt-5">
            <h2 class="mb-4">Join Our Church Family</h2>
            <p class="lead mb-4">We would love to welcome you into our community of faith.</p>
            <div class="cta-buttons">
                <a href="contact.php" class="btn btn-primary btn-lg me-3">Visit Us</a>
                <a href="ministries.php" class="btn btn-outline-primary btn-lg">Get Involved</a>
            </div>
        </div>
    </div>
</section>

<style>
.page-header {
    padding: 100px 0 60px;
    margin-top: -1px;
}

.feature-list {
    margin-left: 0;
    padding-left: 0;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.feature-item i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.about-image-container {
    position: relative;
}

.image-overlay {
    position: absolute;
    top: 20px;
    left: 20px;
    right: -20px;
    bottom: -20px;
    background: var(--primary);
    opacity: 0.1;
    border-radius: 10px;
    z-index: -1;
}

.years-badge {
    position: absolute;
    bottom: -20px;
    right: 20px;
    background: var(--secondary);
    color: white;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-lg);
}

.years-number {
    font-size: 1.8rem;
    font-weight: bold;
    line-height: 1;
}

.years-text {
    font-size: 0.8rem;
    text-align: center;
    line-height: 1.2;
}

.icon-container {
    width: 70px;
    height: 70px;
    background: rgba(26, 82, 118, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-container i {
    font-size: 1.8rem;
}

.mission-list {
    list-style: none;
    padding-left: 0;
}

.mission-list li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.mission-list li:last-child {
    border-bottom: none;
}

.mission-list li:before {
    content: "✓";
    color: var(--primary);
    font-weight: bold;
    margin-right: 10px;
}

.value-card {
    border: 1px solid #eee;
    border-radius: 10px;
    transition: var(--transition);
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.value-icon {
    font-size: 2.5rem;
}

.leader-image img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: var(--shadow);
}

.leader-card {
    padding: 30px 20px;
    border-radius: 10px;
    background: white;
    box-shadow: var(--shadow);
    height: 100%;
}

@media (max-width: 768px) {
    .page-header {
        padding: 80px 0 40px;
    }
    
    .years-badge {
        width: 80px;
        height: 80px;
    }
    
    .years-number {
        font-size: 1.4rem;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>