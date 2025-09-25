<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f8fafc;
}

.hero-section {
    background: #0870A4;
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h20v20H0z" fill="none"/><circle cx="3" cy="3" r="1" fill="rgba(255,255,255,0.2)"/></svg>') repeat;
    opacity: 0.3;
}

.startup-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: all 0.3s ease;
    border: 1px solid rgba(99, 102, 241, 0.1);
}

.startup-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
}

.program-stage {
    background: white;
    color: #1e293b;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 25px;
    text-align: center;
    border: 2px solid;
    transition: all 0.3s ease;
}

.program-stage:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">Startup Club</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9; color:white">Empowering student entrepreneurs to build innovative solutions</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-lightbulb" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Club Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="startup-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">About Our Startup Club</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            The SRKREC Startup Club is a dynamic ecosystem designed to foster innovation and entrepreneurship 
                            among students and faculty. We provide comprehensive support including mentorship, funding guidance, workspace, 
                            and resources to transform innovative ideas into successful businesses.
                        </p>
                        <p style="color: #64748b; line-height: 1.8;">
                            Our mission is to create a culture of entrepreneurship and innovation that contributes to economic development 
                            and societal progress through technology-driven solutions. We help students take their first steps into 
                            the startup world and connect them with the right resources and opportunities.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                        <h4>Our Impact</h4>
                        <div style="margin: 25px 0;">
                            <h3>5+</h3>
                            <p>Active Startups</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>200+</h3>
                            <p>Daily Customers</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>3+</h3>
                            <p>Industry Sectors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs & Activities -->
    

    <style>
    .startups-section {
        background: linear-gradient(to bottom, #f8fafc, #ffffff);
        padding: 80px 0;
    }

    .section-title {
        text-align: center;
        margin-bottom: 50px;
        position: relative;
    }

    .section-title:after {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, #3b82f6, #2563eb);
        margin: 15px auto 0;
        border-radius: 2px;
    }

    .startup-card {
        border: 2px solid transparent;
        transition: all 0.3s ease;
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .startup-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.2);
    }
    
    .startup-logo-container {
        width: 200px;
        height: 120px;
        margin: 0 auto 20px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 10px;
        padding: 15px;
        overflow: hidden;
    }
    
    .startup-logo {
        width: 300px;
        height: 280px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .startup-logo-container:hover .startup-logo {
        transform: scale(1.05);
    }

    .startup-info {
        text-align: center;
        margin-bottom: 20px;
    }

    .startup-title {
        margin: 0;
        color: #1e293b;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .startup-subtitle {
        margin-top: 5px;
        color: #64748b;
        font-size: 1rem;
    }

    .startup-description {
        color: #64748b;
        margin-bottom: 20px;
        flex-grow: 1;
        line-height: 1.6;
    }

    .startup-tags {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .startup-tag {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .bo-card {
        border-color: #3b82f6;
    }
    
    .lb-card {
        border-color: #ef4444;
    }
    
    .bd-card {
        border-color: #0ea5e9;
    }
    
    .sw-card {
        border-color: #14b8a6;
    }
    
    .nd-card {
        border-color: #22c55e;
    }

    .row-eq-height {
        display: flex;
        flex-wrap: wrap;
    }

    .row-eq-height > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }
    </style>

    <!-- Featured Startups -->
    <section class="startups-section">
        <div class="container">
            <h2 class="section-title">Our Successful Startups</h2>
            <div class="row row-eq-height">
                <!-- Bhimavaram Online -->
                <div class="col-md-4 mb-4">
                    <div class="startup-card bo-card">
                        <div class="startup-logo-container">
                            <img src="assets/company_logos/logos/22.png" alt="Bhimavaram Online" class="startup-logo">
                        </div>
                        <div class="startup-info">
                            <h5 class="startup-title">Bhimavaram Online</h5>
                            <p class="startup-subtitle">E-Commerce Platform</p>
                        </div>
                        <p class="startup-description">
                            First ONDC enabled app in AP & Telangana. A one-stop portal for shopping, food ordering, and local services in Bhimavaram.
                        </p>
                        <div class="startup-tags">
                            <span class="badge bg-primary">E-Commerce</span>
                            <span class="badge bg-info">ONDC</span>
                        </div>
                    </div>
                </div>

                <!-- Lunch Box -->
                <div class="col-md-4 mb-4">
                    <div class="startup-card lb-card">
                        <div class="startup-logo-container">
                            <img src="assets/company_logos/logos/25.png" alt="Lunch Box" class="startup-logo">
                        </div>
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="margin: 0; color: #1e293b; font-size: 1.5rem;">Lunch Box</h5>
                            <p style="margin-top: 5px; color: #64748b;">School Lunch Delivery</p>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Delivering 200+ lunchboxes daily. Monthly subscription-based school lunch delivery from home to school.
                        </p>
                        <div style="display: flex; gap: 10px">
                            <span class="badge bg-success">FoodTech</span>
                            <span class="badge bg-warning">Logistics</span>
                        </div>
                    </div>
                </div>

                <!-- Bhimavaram Digitals -->
                <div class="col-md-4 mb-4">
                    <div class="startup-card bd-card">
                        <div class="startup-logo-container">
                            <img src="assets/company_logos/logos/20.png" alt="Bhimavaram Digitals" class="startup-logo">
                        </div>
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="margin: 0; color: #1e293b; font-size: 1.5rem;">Bhimavaram Digitals</h5>
                            <p style="margin-top: 5px; color: #64748b;">Digital Marketing</p>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Digital marketing startup specializing in digital billboards, SEO, social media management, and content creation.
                        </p>
                        <div style="display: flex; gap: 10px">
                            <span class="badge bg-primary">Marketing</span>
                            <span class="badge bg-info">Digital</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Smart Wash -->
                <div class="col-md-6 mb-4">
                    <div class="startup-card sw-card">
                        <div class="startup-logo-container">
                            <img src="assets/company_logos/logos/23.png" alt="Smart Wash" class="startup-logo">
                        </div>
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="margin: 0; color: #1e293b; font-size: 1.5rem;">Smart Wash</h5>
                            <p style="margin-top: 5px; color: #64748b;">Laundry Services</p>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Student-run laundry startup offering dry cleaning, shoe cleaning, and saree rolling with eco-friendly methods.
                        </p>
                        <div style="display: flex; gap: 10px">
                            <span class="badge bg-success">Service</span>
                            <span class="badge bg-warning">EcoFriendly</span>
                        </div>
                    </div>
                </div>

                <!-- NutriDelight -->
                <div class="col-md-6 mb-4">
                    <div class="startup-card nd-card">
                        <div class="startup-logo-container">
                            <img src="assets/company_logos/logos/25.png" alt="NutriDelight" class="startup-logo">
                        </div>
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="margin: 0; color: #1e293b; font-size: 1.5rem;">NutriDelight</h5>
                            <p style="margin-top: 5px; color: #64748b;">Health Food Delivery</p>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Health-focused cloud kitchen startup delivering nutritious meals using fresh, locally-sourced ingredients.
                        </p>
                        <div style="display: flex; gap: 10px">
                            <span class="badge bg-success">FoodTech</span>
                            <span class="badge bg-info">Health</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Resources -->
    
    

    <?php include "footer.php"; ?>
</body>
</html>
