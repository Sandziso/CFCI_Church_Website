<?php
// about.php – Polished, accessible, performant
require_once 'includes/header.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Page Header -->
<section class="page-header wow fadeIn" data-wow-duration="1s"
         style="background: linear-gradient(135deg, rgba(26, 82, 118, 0.92), rgba(230, 126, 34, 0.78)), url('assets/images/hero/main-hero.jpg') center/cover no-repeat;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white display-4 fw-bold">Our Church Family</h1>
                <p class="text-white mb-0 fs-5">Rooted in faith, growing in love, serving Manzini &amp; beyond since 2004.</p>
            </div>
            <div class="col-lg-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">About Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- ========== OUR STORY + TIMELINE ========== -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 position-relative">
                <div class="about-image-container">
                    <img src="assets/images/about.jpg"
                         alt="CFCI congregation worshipping together in the main sanctuary"
                         class="img-fluid rounded-3 shadow-lg"
                         loading="lazy"
                         width="600" height="400">
                    <div class="years-badge" aria-hidden="true">
                        <span class="years-number">20+</span>
                        <span class="years-text">Years<br>Serving</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-5">
                    <h2 class="section-title">Our Story</h2>
                    <p class="lead">Welcome to Christian Family Centre International (CFCI). We’re glad you’re here!</p>
                    <p>Founded in 2004, CFCI began as a small prayer group meeting in homes around Manzini, Eswatini. What started with just a handful of families has grown into a vibrant community of believers dedicated to transforming lives through the Gospel.</p>
                    <p>From humble beginnings, we’ve witnessed God’s faithfulness. Today we’re blessed with multiple ministries serving every age group, a growing congregation, and a heart to reach our nation and the world.</p>
                    <div class="feature-list mt-4">
                        <div class="feature-item">
                            <i class="fas fa-check-circle me-2" style="color: var(--primary-yellow);" aria-hidden="true"></i>
                            <span>Family‑focused ministry approach</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle me-2" style="color: var(--primary-yellow);" aria-hidden="true"></i>
                            <span>Bible‑based teaching and preaching</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle me-2" style="color: var(--primary-yellow);" aria-hidden="true"></i>
                            <span>Community outreach programmes</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle me-2" style="color: var(--primary-yellow);" aria-hidden="true"></i>
                            <span>Multi‑generational worship</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Church History Timeline -->
        <div class="mt-5 pt-5">
            <h2 class="section-title text-center">Our Journey with God</h2>
            <div class="timeline mt-5">
                <div class="timeline-item wow fadeInLeft" data-wow-duration="0.6s">
                    <div class="timeline-badge bg-primary shadow"><i class="fas fa-home"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">2004 – Humble Beginnings</h4>
                        <p>First prayer meeting held in a family living room in Ntunja Township. A handful of believers gathered weekly.</p>
                    </div>
                </div>
                <div class="timeline-item wow fadeInRight" data-wow-duration="0.6s">
                    <div class="timeline-badge bg-warning shadow"><i class="fas fa-church"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">2006 – First Permanent Sanctuary</h4>
                        <p>The congregation moved into its own building on the current site behind William Pitcher College.</p>
                    </div>
                </div>
                <div class="timeline-item wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="timeline-badge bg-primary shadow"><i class="fas fa-users"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">2010 – Ministry Expansion</h4>
                        <p>Youth, Women’s, and Men’s ministries were officially launched.</p>
                    </div>
                </div>
                <div class="timeline-item wow fadeInRight" data-wow-delay="0.1s">
                    <div class="timeline-badge bg-warning shadow"><i class="fas fa-globe"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">2015 – Community Outreach Launch</h4>
                        <p>CFCI Outreach Ministry began serving the vulnerable in Manzini with food drives, school support, and prison ministry.</p>
                    </div>
                </div>
                <div class="timeline-item wow fadeInLeft" data-wow-delay="0.2s">
                    <div class="timeline-badge bg-primary shadow"><i class="fas fa-broadcast-tower"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">2020 – Digital Transformation</h4>
                        <p>Livestream services and an online prayer wall brought the church to homes globally.</p>
                    </div>
                </div>
                <div class="timeline-item wow fadeInRight" data-wow-delay="0.2s">
                    <div class="timeline-badge bg-warning shadow"><i class="fas fa-hand-holding-heart"></i></div>
                    <div class="timeline-card">
                        <h4 class="timeline-title">Today – Building Generations</h4>
                        <p>Over 20 ministries actively serve a growing family of believers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== VISION & MISSION ========== -->
<section class="section-padding bg-light" id="vision-mission">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="vision-card p-4 p-xl-5 h-100 bg-white rounded-3 shadow-sm">
                    <div class="icon-container mb-4">
                        <i class="fas fa-eye" style="color: var(--primary-blue); font-size: 2rem;" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-3">Our Vision</h3>
                    <p>To be a Christ‑centred church that transforms families and communities by making disciples who make disciples, impacting generations for God’s kingdom.</p>
                    <p class="text-muted">We envision every family as a beacon of God’s love, every home a place of prayer, and every member an active participant in God’s mission.</p>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="mission-card p-4 p-xl-5 h-100 bg-white rounded-3 shadow-sm">
                    <div class="icon-container mb-4">
                        <i class="fas fa-bullseye" style="color: var(--primary-blue); font-size: 2rem;" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-3">Our Mission</h3>
                    <p>To lead people into a growing relationship with Jesus Christ by:</p>
                    <ul class="list-unstyled mission-list">
                        <li class="py-2 border-bottom">Teaching the undiluted Word of God</li>
                        <li class="py-2 border-bottom">Creating authentic Christian community</li>
                        <li class="py-2 border-bottom">Serving our local community</li>
                        <li class="py-2 border-bottom">Developing spiritual leaders</li>
                        <li class="py-2">Worshipping God in spirit and truth</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== CORE VALUES ========== -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;">
            <h2 class="section-title">Our Core Values</h2>
            <p class="lead">These values guide everything we do at CFCI</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.1s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-bible"></i></div>
                    <h4>Biblical Authority</h4>
                    <p>We believe the Bible is God’s inspired Word and the final authority for our faith and practice.</p>
                </div>
            </div>
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-pray"></i></div>
                    <h4>Prayer &amp; Worship</h4>
                    <p>We prioritise prayer as our foundation and worship as our lifestyle before God.</p>
                </div>
            </div>
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.3s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-users"></i></div>
                    <h4>Authentic Community</h4>
                    <p>We build genuine relationships where people are known, loved, and cared for.</p>
                </div>
            </div>
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.4s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4>Servant Leadership</h4>
                    <p>We lead by serving, following Jesus’ example of humility and compassion.</p>
                </div>
            </div>
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.5s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-church"></i></div>
                    <h4>Family Focus</h4>
                    <p>We strengthen families as the foundation of society and the church.</p>
                </div>
            </div>
            <div class="col-md-4 wow fadeInUp" data-wow-delay="0.6s">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3"><i class="fas fa-globe"></i></div>
                    <h4>Missional Living</h4>
                    <p>We are called to share God’s love both locally and globally.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== LEADERSHIP TEAM ========== -->
<section class="section-padding bg-light" id="leadership">
    <div class="container">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;">
            <h2 class="section-title">Meet Our Leadership</h2>
            <p class="lead">Godly leaders guiding our church family</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="leader-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="leader-image mb-3">
                        <img src="assets/images/leaders/bishop-zakes.jpg"
                             alt="Bishop Zakes Nxumalo"
                             class="rounded-circle"
                             width="200" height="200"
                             style="object-fit: cover;"
                             loading="lazy">
                    </div>
                    <h4 class="mb-1">Bishop Zakes Nxumalo</h4>
                    <p class="text-warning fw-bold mb-2">Senior Pastor</p>
                    <p class="small text-muted">Bishop Nxumalo has served as our senior pastor for over 20 years, with a passion for family restoration and community transformation.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="leader-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="leader-image mb-3">
                        <img src="assets/images/leaders/pastor-sarah.jpg"
                             alt="Pastor Sarah Mkhwanazi"
                             class="rounded-circle"
                             width="200" height="200"
                             style="object-fit: cover;"
                             loading="lazy">
                    </div>
                    <h4 class="mb-1">Pastor Sarah Mkhwanazi</h4>
                    <p class="text-warning fw-bold mb-2">Associate Pastor</p>
                    <p class="small text-muted">Pastor Sarah oversees our women’s and children’s ministries, bringing energy and creativity to discipleship.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="leader-card text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="leader-image mb-3">
                        <img src="assets/images/leaders/deacon-thomas.jpg"
                             alt="Deacon Thomas Dlamini"
                             class="rounded-circle"
                             width="200" height="200"
                             style="object-fit: cover;"
                             loading="lazy">
                    </div>
                    <h4 class="mb-1">Deacon Thomas Dlamini</h4>
                    <p class="text-warning fw-bold mb-2">Deacon Chairman</p>
                    <p class="small text-muted">Deacon Thomas leads our servant leadership team and oversees church operations and outreach.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== JOIN US CTA ========== -->
<section class="cta-section text-center py-5" style="background: var(--gradient-primary);">
    <div class="container py-4">
        <h2 class="mb-3 text-white">Join Our Church Family</h2>
        <p class="lead mb-4 text-white-50">We would love to welcome you into our community of faith.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="contact.php" class="btn btn-warning btn-lg shadow">Visit Us</a>
            <a href="ministries.php" class="btn btn-outline-light btn-lg">Get Involved</a>
        </div>
    </div>
</section>

<!-- PAGE‑SPECIFIC STYLES -->
<style>
/* About page enhancements – built on top of style.css variables */
.about-image-container {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.years-badge {
    position: absolute;
    bottom: -15px;
    right: 20px;
    background: var(--primary-yellow);
    color: #fff;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-lg);
    font-weight: 700;
    z-index: 2;
}

.years-number {
    font-size: 1.8rem;
    line-height: 1;
}

.years-text {
    font-size: 0.8rem;
    text-align: center;
    line-height: 1.2;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 28px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--primary-blue);
    opacity: 0.15;
}

.timeline-item {
    position: relative;
    margin-bottom: 2.5rem;
    padding-left: 80px;
}

.timeline-badge {
    position: absolute;
    left: 0;
    top: 0.5rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.5rem;
}

.timeline-card {
    background: #fff;
    padding: 1.5rem 1.8rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.timeline-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-3px);
}

.timeline-title {
    color: var(--primary-blue);
    font-weight: 700;
    margin-bottom: 0.5rem;
}

/* Vision/Mission cards */
.vision-card,
.mission-card {
    border-left: 4px solid var(--primary-yellow);
    transition: var(--transition);
}

.vision-card:hover,
.mission-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}

.mission-list li::before {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: var(--primary-yellow);
    margin-right: 10px;
}

/* Core values */
.value-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    height: 100%;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.value-icon {
    width: 70px;
    height: 70px;
    background: rgba(26, 82, 118, 0.08);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--primary-blue);
    transition: var(--transition);
}

.value-card:hover .value-icon {
    background: var(--gradient-primary);
    color: #fff;
    transform: rotateY(180deg);
}

/* Leader cards */
.leader-card {
    transition: var(--transition);
    border: 1px solid rgba(0,0,0,0.04);
}

.leader-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg) !important;
}

.leader-image img {
    border: 4px solid var(--primary-yellow);
    box-shadow: var(--shadow);
}

/* Accessible focus indicator */
a:focus-visible,
button:focus-visible {
    outline: 2px solid var(--primary-yellow);
    outline-offset: 3px;
}

@media (max-width: 768px) {
    .years-badge {
        width: 80px;
        height: 80px;
        right: 10px;
        bottom: -10px;
    }
    .years-number {
        font-size: 1.4rem;
    }
    .timeline {
        padding-left: 60px;
    }
    .timeline::before {
        left: 20px;
    }
    .timeline-item {
        padding-left: 60px;
    }
    .timeline-badge {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>