<?php 
if (session_status() == PHP_SESSION_NONE) session_start();
include "./head.php"; 
?>

<style>
:root {
    --primary: #6366f1;
    --primary-light: #a5b4fc;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-light: #94a3b8;
    --bg-light: #fafafa;
    --border-light: #e2e8f0;
    --white: #ffffff;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-light);
    line-height: 1.5;
    color: var(--text-primary);
    font-size: 14px;
}

.hero-section {
    background:#0870A4;
    color: white;
    padding: 60px 0 40px;
    text-align: center;
}

.hero-title {
    font-size: 2rem;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1rem;
    font-weight: 400;
    opacity: 0.8;
    color: white;
    margin-bottom: 0;
}

.stats-section {
    padding: 40px 0;
}

.stat-card {
    background: var(--white);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
    margin-bottom: 20px;
}

.stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.stat-sublabel {
    font-size: 0.75rem;
    color: var(--text-light);
}

.recruiters-section {
    padding: 40px 0;
    background: var(--white);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    color: var(--text-primary);
}

.company-card {
    border-radius: 8px;
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
    margin-bottom: 16px;
    height: 100%;
}

.company-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.company-logo {
    height: 158px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    background: #f8fafc;
    border-radius: 6px;
}

.company-logo img {
    max-width: 180px;
    max-height: 258px;
    object-fit: contain;
}

.company-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
    text-align: center;
    line-height: 1.3;
}

.company-domain {
    color: var(--text-secondary);
    font-size: 0.75rem;
    text-align: center;
    margin-bottom: 8px;
}

.package-info {
    font-weight: 500;
    color: var(--text-primary);
    text-align: center;
    margin-bottom: 8px;
    font-size: 0.75rem;
}

.offer-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.625rem;
    font-weight: 500;
}

.badge-success { background: #dcfce7; color: #166534; }
.badge-primary { background: #dbeafe; color: #1d4ed8; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.badge-info { background: #cffafe; color: #0891b2; }

.view-all-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.view-all-btn:hover {
    background: var(--primary-light);
    transform: translateY(-1px);
}

.remaining-cards {
    display: none;
}

.process-section {
    padding: 40px 0;
    background: var(--bg-light);
}

.process-step {
    background: var(--white);
    padding: 20px 16px;
    border-radius: 8px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-light);
    transition: all 0.2s ease;
    position: relative;
    height: 100%;
    margin-bottom: 20px;
}

.process-step:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.step-number {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 24px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
}

.step-icon {
    color: var(--primary);
    font-size: 1.5rem;
    margin: 8px 0 12px 0;
}

.step-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.step-description {
    color: var(--text-secondary);
    font-size: 0.75rem;
    line-height: 1.4;
}

.contact-section {
    padding: 40px 0;
    background: var(--primary);
    color: white;
}

.contact-card {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 20px 16px;
    border-radius: 8px;
    text-align: center;
    transition: all 0.2s ease;
    margin-bottom: 16px;
}

.contact-card:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.contact-icon {
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: white;
    opacity: 0.9;
}

.contact-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.contact-info {
    font-size: 0.75rem;
    opacity: 0.8;
}

.fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in:nth-child(1) { animation-delay: 0.1s; }
.fade-in:nth-child(2) { animation-delay: 0.2s; }
.fade-in:nth-child(3) { animation-delay: 0.3s; }

@media (max-width: 768px) {
    .hero-section { padding: 40px 0 30px; }
    .stats-section, .recruiters-section, .process-section, .contact-section { padding: 30px 0; }
    .hero-title { font-size: 1.5rem; }
    .section-title { font-size: 1.25rem; }
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title fade-in">Placements & Careers</h1>
                <p class="hero-subtitle fade-in">Connecting talent with top opportunities</p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card fade-in">
                        <div class="stat-number">₹12L</div>
                        <div class="stat-label">Highest Package</div>
                        <div class="stat-sublabel">Microsoft India</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card fade-in">
                        <div class="stat-number">₹5.1L</div>
                        <div class="stat-label">Average Package</div>
                        <div class="stat-sublabel">Industry Leading</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card fade-in">
                        <div class="stat-number">66%</div>
                        <div class="stat-label">Placement Rate</div>
                        <div class="stat-sublabel">Students Placed</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Recruiters -->
    <section class="recruiters-section">
        <div class="container">
            <h2 class="section-title">Top Recruiters</h2>
            
            <!-- Initial 8 cards -->
            <div class="row g-3">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/4.png" alt="TCS">
                        </div>
                        <h5 class="company-name">TCS</h5>
                        <p class="company-domain">Software Development</p>
                        <p class="package-info">₹3.36L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-success">9 Offers</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/6.png" alt="Akrivia HCM">
                        </div>
                        <h5 class="company-name">Akirivia HCM</h5>
                        <p class="company-domain">Cloud & AI Solutions</p>
                        <p class="package-info">₹10L - ₹12L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">1 Offer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/7.png" alt="Boson">
                        </div>
                        <h5 class="company-name">Boson</h5>
                        <p class="company-domain">Cloud Services</p>
                        <p class="package-info">₹3.5L - ₹9.5L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-warning">1 Offer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/1.png" alt="BLUCONN">
                        </div>
                        <h5 class="company-name">BLUCONN</h5>
                        <p class="company-domain">Software Engineering</p>
                        <p class="package-info">₹7.8L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-info">3 Offers</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/12.png" alt="Meeami">
                        </div>
                        <h5 class="company-name">Meeami</h5>
                        <p class="company-domain">Digital Services</p>
                        <p class="package-info">₹6L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-success">1 Offer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/9.png" alt="intelliPaat">
                        </div>
                        <h5 class="company-name">intelliPaat</h5>
                        <p class="company-domain">Consulting</p>
                        <p class="package-info">₹5L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-secondary">1 Offer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/8.png" alt="SmartED">
                        </div>
                        <h5 class="company-name">SmartED</h5>
                        <p class="company-domain">IT Services</p>
                        <p class="package-info">₹4.8L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-success">1 Offer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card fade-in">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/11.png" alt="Quanteon">
                        </div>
                        <h5 class="company-name">Quanteon</h5>
                        <p class="company-domain">AI & Cloud</p>
                        <p class="package-info">₹4.5L - ₹6.5L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">1 Offer</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remaining cards -->
            <div class="row g-3 remaining-cards">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/10.png" alt="AteliaHealth">
                        </div>
                        <h5 class="company-name">AteliaHealth</h5>
                        <p class="company-domain">Healthcare Tech</p>
                        <p class="package-info">₹4L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">3 Offers</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/15.png" alt="achala">
                        </div>
                        <h5 class="company-name">Achala</h5>
                        <p class="company-domain">Digital Innovation</p>
                        <p class="package-info">₹4L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">1 Offer</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/2.png" alt="Cognizant">
                        </div>
                        <h5 class="company-name">Cognizant</h5>
                        <p class="company-domain">Digital Transformation</p>
                        <p class="package-info">₹4L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">9 Offers</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="company-card">
                        <div class="company-logo">
                            <img src="assets/company_logos/logos/3.png" alt="Infosys">
                        </div>
                        <h5 class="company-name">Infosys</h5>
                        <p class="company-domain">Next-Gen Services</p>
                        <p class="package-info">₹3.6L</p>
                        <div class="text-center">
                            <span class="offer-badge badge-primary">6 Offers</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button id="viewAllBtn" class="view-all-btn" onclick="toggleRecruiters()">
                    View All
                </button>
            </div>
        </div>
    </section>



    <section class="photo-gallery-section" style="padding: 80px 0; margin-top: -80px; background: rgba(255, 255, 255, 1);">
        <div class="container">
            <div class="section-title text-center" style="margin-bottom: 60px;">
                <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 15px; line-height: 1.1;">Placement <span style="color: #3b82f6;">Gallery</span></h2>
                <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.5;">Celebrating our students' success stories and achievements</p>
            </div>
            <div class="gallery-carousel-wrapper" style="overflow: hidden; margin-bottom: 40px;">
                <!-- First Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-left 25s linear infinite; margin-bottom: 20px; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/28.png" alt="Placement Image 1" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/29.png" alt="Placement Image 2" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/30.png" alt="Placement Image 3" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/31.png" alt="Placement Image 4" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/32.png" alt="Placement Image 5" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/28.png" alt="Placement Image 1" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/29.png" alt="Placement Image 2" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                </div>

                <!-- Second Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-right 25s linear infinite; margin-bottom: 20px; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/33.png" alt="Placement Image 6" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/34.png" alt="Placement Image 7" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/35.png" alt="Placement Image 8" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/36.png" alt="Placement Image 9" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/37.png" alt="Placement Image 10" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/33.png" alt="Placement Image 6" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/34.png" alt="Placement Image 7" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                </div>

                <!-- Third Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-left 25s linear infinite; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/38.png" alt="Placement Image 11" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/39.png" alt="Placement Image 12" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/40.png" alt="Placement Image 13" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/31.png" alt="Placement Image 14" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/32.png" alt="Placement Image 15" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/38.png" alt="Placement Image 11" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="assets/placements/39.png" alt="Placement Image 12" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                </div>
            </div>

            <style>
                @keyframes gallery-scroll-left {
                    0% {
                        transform: translateX(0);
                    }
                    100% {
                        transform: translateX(-50%);
                    }
                }

                @keyframes gallery-scroll-right {
                    0% {
                        transform: translateX(-50%);
                    }
                    100% {
                        transform: translateX(0);
                    }
                }

                .gallery-carousel-row:hover {
                    animation-play-state: paused;
                }

                .gallery-item:hover {
                    transform: translateY(-10px) scale(1.1);
                    z-index: 10;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                }

                .gallery-item:hover img {
                    transform: scale(1.1);
                }

                @media (max-width: 768px) {
                    .gallery-item {
                        min-width: 250px !important;
                        height: 180px !important;
                    }
                }
            </style>
        </div>
        <style>
            .gallery-item:hover img {
                transform: scale(1.1);
            }
        </style>
    </section>

    


    <?php include "footer.php"; ?>
    
    <script>
        // Enhanced JavaScript with modern functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize fade-in animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all fade-in elements
            document.querySelectorAll('.fade-in').forEach(el => {
                observer.observe(el);
            });

            // Recruiters toggle functionality
            const remainingCards = document.querySelector('.remaining-cards');
            const viewAllBtn = document.getElementById('viewAllBtn');
            let isExpanded = false;

            function toggleRecruiters() {
                isExpanded = !isExpanded;
                
                if (isExpanded) {
                    remainingCards.style.display = 'flex';
                    viewAllBtn.innerHTML = '<i class="fas fa-chevron-up me-2"></i>Show Less';
                    
                    // Animate in the new cards
                    setTimeout(() => {
                        remainingCards.querySelectorAll('.company-card').forEach((card, index) => {
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, index * 100);
                        });
                    }, 50);
                    
                    // Smooth scroll to show new content
                    setTimeout(() => {
                        remainingCards.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest' 
                        });
                    }, 200);
                } else {
                    remainingCards.style.display = 'none';
                    viewAllBtn.innerHTML = '<i class="fas fa-chevron-down me-2"></i>View All Recruiters';
                    
                    // Scroll back to the button
                    viewAllBtn.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
            }

            // Initialize remaining cards state
            remainingCards.style.display = 'none';
            remainingCards.querySelectorAll('.company-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.4s ease';
            });

            viewAllBtn.innerHTML = '<i class="fas fa-chevron-down me-2"></i>View All Recruiters';
            viewAllBtn.addEventListener('click', toggleRecruiters);

            // Add smooth scrolling for any anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states and micro-interactions
            document.querySelectorAll('.view-all-btn, .contact-card').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Parallax effect for hero section (optional)
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.hero-section');
                if (heroSection && scrolled < heroSection.offsetHeight) {
                    heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });
        });

        // Global function for backward compatibility
        function toggleRecruiters() {
            document.getElementById('viewAllBtn').click();
        }
    </script>
</body>
</html>
