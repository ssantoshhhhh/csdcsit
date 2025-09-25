<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php";
?>
<link rel="stylesheet" href="./index.css">
<style>
    /* Ensure consistent font family and body styling with nav.php */
    body {
        font-family: 'Poppins', sans-serif;
        color: #333;
        background: #fefefe
    }


    .hero-section {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .hero-bg-effects {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
    }


    .hero-content {
        position: relative;
        z-index: 10;
        max-width: 600px;
    }

    .hero-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .hero-logo img {
        height: 40px;
        width: auto;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 30px;
        color: #1e293b;
    }

    .hero-title .highlight {
        color: var(--house-aakash);
    }

    .hero-buttons {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .houses-section {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 50px 20px;
    }

    .houses-container {
        position: relative;
        width: 500px;
        height: 500px;
    }

    .houses-circle {
        position: absolute;
        inset: 0;
        border: 1px solid rgba(255, 0, 0, 0.1);
        border-radius: 50%;
    }

    .center-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 20;
    }

    .center-logo img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid #374151;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .house-item {
        position: absolute;
        transform: translate(-50%, -50%);
        z-index: 10;
        transition: all 0.3s ease;
    }

    .house-button {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .house-button:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .house-button img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .house-name {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: 10px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        color: #374151;
    }

    .connecting-lines {
        position: absolute;
        inset: 0;
    }

    .line {
        position: absolute;
        top: 50%;
        left: 50%;
        height: 2px;
        background: linear-gradient(to right, rgba(107, 114, 128, 0.3), transparent);
        transform-origin: 0 50%;
        opacity: 0.6;
    }

    /* House positioning - Perfect 72° spacing (Pentagon layout) */
    /* Center at 250px, 250px with radius of 180px */
    .house-agni {
        top: 70px;
        left: 250px;
    }

    /* 0° - Top */
    .house-vayu {
        top: 145px;
        left: 421px;
    }

    /* 72° - Top Right */
    .house-prudhvi {
        top: 355px;
        left: 356px;
    }

    /* 144° - Bottom Right */
    .house-jal {
        top: 355px;
        left: 144px;
    }

    /* 216° - Bottom Left */
    .house-aakash {
        top: 145px;
        left: 79px;
    }

    /* 288° - Top Left */

    .house-agni .house-button {
        background-color: var(--house-agni);
    }

    .house-vayu .house-button {
        background-color: var(--house-vayu);
    }

    .house-prudhvi .house-button {
        background-color: var(--house-prudhvi);
    }

    .house-jal .house-button {
        background-color: var(--house-jal);
    }

    .house-aakash .house-button {
        background-color: var(--house-aakash);
    }

    /* Hover effects for better visual feedback */
    .houses-container:hover .line {
        opacity: 1;
        background: linear-gradient(to right, rgba(107, 114, 128, 0.6), transparent);
    }

    .house-item:hover {
        z-index: 20;
    }

    .house-item:hover .house-name {
        color: #1e293b;
        font-weight: 700;
    }

    .section-title {
        text-align: center;
        margin: 60px 0 40px;
    }

    .section-title h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #1e293b;
    }

    .section-title p {
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }

    .leaderboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 60px;
    }

    .house-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border-top: 4px solid;
    }

    .house-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .house-card.rank-1 {
        border-top-color: #ffd700;
    }

    .house-card.rank-2 {
        border-top-color: #c0c0c0;
    }

    .house-card.rank-3 {
        border-top-color: #cd7f32;
    }

    .house-card.rank-4 {
        border-top-color: #64748b;
    }

    .house-card.rank-5 {
        border-top-color: #94a3b8;
    }

    .house-card h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #1e293b;
    }

    .house-card .points {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .house-card .rank {
        color: #64748b;
        font-size: 0.9rem;
    }

    .stats-section {
        background: rgba(0, 0, 0, 0.02);
        padding: 60px 0;
        margin: 60px 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 24px;
    }

    .stat-icon.blue {
        background: #dbeafe;
        color: #3b82f6;
    }

    .stat-icon.red {
        background: #fee2e2;
        color: #ef4444;
    }

    .stat-icon.green {
        background: #dcfce7;
        color: #22c55e;
    }

    .stat-content h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: #1e293b;
    }

    .stat-content p {
        color: #64748b;
        font-size: 0.9rem;
    }

    .contributors-section {
        padding: 60px 0;
    }

    .contributors-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .contributors-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .contributors-table th {
        background: #f8fafc;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .contributors-table td {
        padding: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .contributors-table tr:hover {
        background: rgba(0, 0, 0, 0.02);
    }

    .house-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
    }

    .house-badge.aakash {
        background: var(--house-aakash);
    }

    .house-badge.agni {
        background: var(--house-agni);
    }

    .house-badge.vayu {
        background: var(--house-vayu);
    }

    .house-badge.jal {
        background: var(--house-jal);
    }

    .house-badge.prudhvi {
        background: var(--house-prudhvi);
    }

    .view-more-btn {
        text-align: center;
        padding: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .view-more-btn button {
        background: #f3f4f6;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #374151;
    }

    .view-more-btn button:hover {
        background: #e5e7eb;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes center-spin {
        from {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    @keyframes orbit {
        from {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    @keyframes icon-spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .hero-section {
            flex-direction: column;
            text-align: center;
        }

        .houses-container {
            width: 400px;
            height: 400px;
        }

        .hero-title {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .houses-container {
            width: 350px;
            height: 350px;
        }

        .hero-title {
            font-size: 2rem;
        }

        .hero-buttons {
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .houses-container {
            width: 300px;
            height: 300px;
        }

        .house-button {
            width: 60px;
            height: 60px;
        }

        .house-button img {
            width: 45px;
            height: 45px;
        }

        .contributors-table {
            overflow-x: auto;
        }

        /* Hover effects for combined overview section */
        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .hod-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(102, 126, 234, 0.4) !important;
        }

        /* Modern highlight cards */
        .highlight-card {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .highlight-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .highlight-card.large-card:hover {
            transform: translateY(-10px) scale(1.02);
        }

        .highlight-card.featured:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(30, 41, 59, 0.4) !important;
        }

        .bottom-feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(34, 197, 94, 0.3) !important;
        }

        /* Responsive grid */
        @media (max-width: 768px) {
            .highlights-masonry {
                grid-template-columns: 1fr !important;
            }

            .bottom-feature {
                grid-template-columns: 1fr !important;
                text-align: center;
            }
        }
    }
</style>

<body>
    <?php include "nav.php"; ?>


    <!-- Combined Hero and Houses Section -->
    <section class="hero-section" style="display: flex; min-height: 100vh; align-items: center; position: relative; overflow: hidden; background: linear-gradient(135deg, #fefefe 0%, #f8fafc 100%);">
        <!-- Animated Background Elements -->
        <div class="hero-bg-effects">
            <!-- Floating Shapes -->
            <div style="position: absolute; top: 10%; left: 5%; width: 100px; height: 100px; background: linear-gradient(45deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1)); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
            <div style="position: absolute; top: 60%; right: 10%; width: 80px; height: 80px; background: linear-gradient(45deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1)); border-radius: 30% 70% 70% 30%; animation: float 8s ease-in-out infinite reverse;"></div>
            <div style="position: absolute; bottom: 20%; left: 15%; width: 60px; height: 60px; background: linear-gradient(45deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.1)); border-radius: 40% 60% 30% 70%; animation: float 7s ease-in-out infinite;"></div>

            <!-- Grid Pattern -->
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(circle at 2px 2px, rgba(0,0,0,0.02) 1px, transparent 0); background-size: 40px 40px; opacity: 0.5;"></div>
        </div>

        <div class="container" style="position: relative; z-index: 10;">
            <div class="row align-items-center min-vh-100">
                <!-- Left Side - Hero Content -->
                <div class="col-lg-6">
                    <div class="hero-content" style="padding: 40px 0;">
                        <!-- Department Badge -->
                        <div class="hero-logo">
                            <img src="logo.png" alt="SRKR Logo">
                            <span style="color: #dc2626; font-weight: 700; font-size: 1.2rem;">SRKREC</span>
                            <span style="color: #1f2937; font-weight: 700; font-size: 1rem;">CSD & CSIT Department</span>
                        </div>

                        <!-- Main Tagline -->
                        <h1 class="hero-title" style="font-size: 4rem; font-weight: 900; line-height: 1.1; margin-bottom: 30px; color: #1e293b;">
                            Where Learning
                            <span style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></span>
                            <br>Meets
                            <span style="color: #10b981; position: relative;">
                                Innovation
                                <div style="position: absolute; bottom: -5px; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, #10b981, #059669); border-radius: 2px; animation: slideIn 2s ease-out;"></div>
                            </span>
                        </h1>

                        <!-- Subtitle with Impact -->
                        <div style="margin-bottom: 35px;">
                            <p style="font-size: 1.3rem; color: #374151; line-height: 1.5; margin-bottom: 15px; font-weight: 500;">
                                 <strong style="color: #1e293b;">Innovation Meets Opportunity</strong>
                            </p>
                            <p style="font-size: 1.1rem; color: #64748b; line-height: 1.6; max-width: 520px;">
                                Join 500+ bright minds in India’s leading computer science department. From coding fundamentals to career-defining projects – your journey to success begins here.
                            </p>
                        </div>

                        <!-- Achievement Highlights -->
                        <div style="background: rgba(255,255,255,0.8); border-radius: 20px; padding: 25px; margin-bottom: 35px; backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.05);">
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center;">
                                <div>
                                    <div style="font-size: 2.2rem; font-weight: 800; color: #10b981; margin-bottom: 5px;">₹5.1L</div>
                                    <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Average Ctc</div>
                                </div>
                                <div>
                                    <div style="font-size: 2.2rem; font-weight: 800; color: #3b82f6; margin-bottom: 5px;">₹12L</div>
                                    <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Highest Package</div>
                                </div>
                                <div>
                                    <div style="font-size: 2.2rem; font-weight: 800; color: #f59e0b; margin-bottom: 5px;">50+</div>
                                    <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">MNC Partners</div>
                                </div>
                            </div>
                        </div>

                        <!-- Power-packed Action Buttons -->
                        <div class="hero-buttons" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">

                            <a href="houses_dashboard.php" class="btn-secondary" style="background: rgba(255,255,255,0.9); color: #374151; padding: 18px 35px; border-radius: 30px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; border: 2px solid rgba(0,0,0,0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; font-size: 1.1rem;">
                                <i class="fas fa-rocket" style="font-size: 1.2rem;"></i> Explore House System

                            </a>
                        </div>


                    </div>
                </div>

                <!-- Right Side - Interactive Visual -->
                <div class="col-lg-6">
                    <div class="hero-visual" style="display: flex; align-items: center; justify-content: center; min-height: 80vh; position: relative;">
                        <!-- Central Innovation Hub -->
                        <div style="position: relative; width: 500px; height: 500px;">
                            <!-- Floating Achievement Cards -->

                        <!-- Achievement Cards Container with cyclic rotation and effects -->
                        <style>
                            @keyframes rotateCard {
                                0% { transform: rotateY(0deg); }
                                50% { transform: rotateY(15deg); }
                                100% { transform: rotateY(0deg); }
                            }
                            .achievement-cards-container {
                                display: flex;
                                flex-wrap: wrap;
                                justify-content: center;
                                gap: 20px;
                                max-width: 600px;
                                margin: 0 auto;
                                padding: 20px;
                                perspective: 1000px;
                            }
                            .achievement-card {
                                flex: 1 1 140px;
                                min-width: 140px;
                                padding: 20px;
                                border-radius: 15px;
                                text-align: center;
                                color: white;
                                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                                cursor: pointer;
                                transition: transform 0.6s ease, box-shadow 0.3s ease;
                                animation: rotateCard 6s ease-in-out infinite;
                                will-change: transform;
                                user-select: none;
                            }
                            .achievement-card:hover {
                                transform: scale(1.1) rotateY(0deg);
                                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                                animation-play-state: paused;
                                z-index: 10;
                            }
                            .achievement-card > div:first-child {
                                font-size: 2rem;
                                margin-bottom: 8px;
                                transition: transform 0.3s ease;
                            }
                            .achievement-card:hover > div:first-child {
                                transform: scale(1.3) rotate(15deg);
                            }
                            /* Staggered animation delays for cyclic effect */
                            .achievement-card:nth-child(1) { animation-delay: 0s; }
                            .achievement-card:nth-child(2) { animation-delay: 0.75s; }
                            .achievement-card:nth-child(3) { animation-delay: 1.5s; }
                            .achievement-card:nth-child(4) { animation-delay: 2.25s; }
                            .achievement-card:nth-child(5) { animation-delay: 3s; }
                            .achievement-card:nth-child(6) { animation-delay: 3.75s; }
                            .achievement-card:nth-child(7) { animation-delay: 4.5s; }
                            .achievement-card:nth-child(8) { animation-delay: 5.25s; }
                            /* Gradient backgrounds */
                            .ai-research { background: linear-gradient(135deg, #667eea, #764ba2); }
                            .startups { background: linear-gradient(135deg, #10b981, #059669); }
                            .industry { background: linear-gradient(135deg, #f59e0b, #d97706); }
                            .innovation { background: linear-gradient(135deg, #ec4899, #be185d); }
                            .excellence { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
                            .community { background: linear-gradient(135deg, #06b6d4, #0891b2); }
                            .research { background: linear-gradient(135deg, #ef4444, #dc2626); }
                            .skills { background: linear-gradient(135deg, #84cc16, #65a30d); }
                            /* Responsive adjustments */
                            @media (max-width: 768px) {
                                .achievement-cards-container {
                                    max-width: 100%;
                                    padding: 10px;
                                }
                                .achievement-card {
                                    flex: 1 1 45%;
                                    min-width: auto;
                                    margin-bottom: 15px;
                                }
                            }
                            @media (max-width: 576px) {
                                .houses-container {
                                    width: 280px !important;
                                    height: 280px !important;
                                    position: relative !important;
                                    display: block !important;
                                    padding: 0 !important;
                                }
                                .house-item {
                                    position: absolute !important;
                                    width: 60px !important;
                                    height: 60px !important;
                                    margin: 0 !important;
                                    text-align: center !important;
                                }
                                .house-item.house-agni {
                                    top: 10px !important;
                                    left: 50% !important;
                                    transform: translateX(-50%) !important;
                                }
                                .house-item.house-vayu {
                                    top: 80px !important;
                                    right: 10px !important;
                                    left: auto !important;
                                    transform: none !important;
                                }
                                .house-item.house-prudhvi {
                                    bottom: 70px !important;
                                    right: 40px !important;
                                    left: auto !important;
                                    transform: none !important;
                                }
                                .house-item.house-jal {
                                    bottom: 20px !important;
                                    left: 50% !important;
                                    transform: translateX(-50%) !important;
                                }
                                .house-item.house-aakash {
                                    bottom: 70px !important;
                                    left: 40px !important;
                                    transform: none !important;
                                }
                            }

                            @media (max-width: 480px) {
                                .achievement-card {
                                    flex: 1 1 100%;
                                }
                            }
                        </style>
                        <div class="achievement-cards-container">
                            <div class="achievement-card ai-research" style="background: white; border: 2px solid #667eea;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #667eea;">AI Research</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #667eea;">Neural Networks</div>
                            </div>
                            <div class="achievement-card startups" style="background: white; border: 2px solid #10b981;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #10b981;">Startups</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #10b981;">6+ Incubated</div>
                            </div>
                            <div class="achievement-card industry" style="background: white; border: 2px solid #f59e0b;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #f59e0b;">Industry</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #f59e0b;">Top MNCs</div>
                            </div>
                            <div class="achievement-card innovation" style="background: white; border: 2px solid #ec4899;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #ec4899;">Innovation</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #ec4899;">Patent Filed</div>
                            </div>
                            <div class="achievement-card excellence" style="background: white; border: 2px solid #8b5cf6;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #8b5cf6;">Excellence</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #8b5cf6;">Since 2008</div>
                            </div>
                            <div class="achievement-card community" style="background: white; border: 2px solid #06b6d4;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #06b6d4;">Community</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #06b6d4;">500+ Alumni</div>
                            </div>
                            <div class="achievement-card research" style="background: white; border: 2px solid #ef4444;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #ef4444;">Research</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #ef4444;">₹1.2Cr Funded</div>
                            </div>
                            <div class="achievement-card skills" style="background: white; border: 2px solid #84cc16;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #84cc16;">Skills</div>
                                <div style="font-size: 0.7rem; opacity: 0.7; color: #84cc16;">Future Ready</div>
                            </div>

                            <!-- Connecting Lines Animation -->
                            <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;">
                                <defs>
                                    <filter id="glow">
                                        <feGaussianBlur stdDeviation="3" result="coloredBlur" />
                                        <feMerge>
                                            <feMergeNode in="coloredBlur" />
                                            <feMergeNode in="SourceGraphic" />
                                        </feMerge>
                                    </filter>
                                </defs>
                                <!-- Animated connecting lines to center -->
                                <circle cx="250" cy="250" r="120" fill="none" stroke="rgba(102, 126, 234, 0.2)" stroke-width="1" stroke-dasharray="5,5">
                                    <animateTransform attributeName="transform" type="rotate" values="0 250 250;360 250 250" dur="30s" repeatCount="indefinite" />
                                </circle>
                                <circle cx="250" cy="250" r="150" fill="none" stroke="rgba(16, 185, 129, 0.2)" stroke-width="1" stroke-dasharray="3,7">
                                    <animateTransform attributeName="transform" type="rotate" values="360 250 250;0 250 250" dur="25s" repeatCount="indefinite" />
                                </circle>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="combined-overview-section" style="padding: 60px 0;">
        <div class="container">
            <div class="mb-5">
                <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 700;">Know CSD & CSIT Department</h2>
            </div>

            <div class="row align-items-stretch">


                <!-- Department Info & Vision/Mission - 60% Width -->
                <div class="col-md-7">
                    <!-- About Department Section -->
                    <div class="dept-about-card" style="border-radius: 20px; margin-bottom: 30px;">
                        <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 25px; text-align: justify;">
                            Founded in 2008, the Department of Computer Science & Information Technology at SRKREC stands at the intersection of multidisciplinary applied education and translational research. With state-of-the-art facilities across our campus, the department empowers students to discover their ikigai through global perspectives, industry collaborations, and holistic development. Guided by integrity and the pursuit of knowledge and moral values, CSD & CSIT shapes future-ready citizens who drive entrepreneurship, innovation, and create meaningful societal impact.
                        </p>

                    </div>

                    <!-- Vision Mission Tabs -->
                    <div class="vision-mission-container">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation" style="margin-bottom: 0;">
                            <button class="tab-btn active" data-tab="vision" style="background: #16a085; color: white; border: none; padding: 12px 30px; border-radius: 8px 8px 0 0; font-weight: 600; cursor: pointer; transition: all 0.3s ease; border-bottom: 3px solid #16a085;">
                                Vision
                            </button>
                            <button class="tab-btn" data-tab="mission" style="background: #e2e8f0; color: #64748b; border: none; padding: 12px 30px; border-radius: 8px 8px 0 0; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-left: 5px;">
                                Mission
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Vision Tab -->
                            <div id="vision-tab" class="tab-pane active" style="background: white; padding: 40px; border-radius: 0 20px 20px 20px; border-top: 3px solid #16a085;">

                                <p style="color: #64748b; font-size: 1.15rem; margin-bottom: 25px; text-align: justify;">
                                    CSD & CSIT will be an exceptional knowledge-driven department advancing on a culture of honesty and compassion to make a difference to the world. We aspire to be a premier center that produces globally competent computer science professionals and researchers who contribute significantly to technological advancement and societal development.
                                </p>
                                <div style="padding: 20px; background: linear-gradient(135deg, #e8f8f5, #d5f4e6); border-radius: 15px; border-left: 5px solid #16a085;">
                                    <p style="margin: 0; color: #2d3748; font-size: 1rem; font-weight: 600;">
                                        <i class="fas fa-lightbulb" style="color: #16a085; margin-right: 10px;"></i>
                                        Global Excellence | Innovation Leadership | Societal Impact | Knowledge-Driven Culture
                                    </p>
                                </div>
                            </div>

                            <!-- Mission Tab -->
                            <div id="mission-tab" class="tab-pane" style="background: white; padding: 40px; border-radius: 0 20px 20px 20px; border-top: 3px solid #16a085; display: none;">
                                <div style="display: flex; align-items: center; margin-bottom: 25px;">
                                </div>
                                <p style="color: #64748b; font-size: 1.15rem; margin-bottom: 25px; text-align: justify;">
                                    To provide quality education in computer science and information technology, foster innovation through research, and develop ethical professionals ready to meet industry challenges. We are committed to nurturing entrepreneurship, promoting lifelong learning, and creating meaningful industry partnerships that bridge academia and real-world applications.
                                </p>
                                <div style="padding: 20px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 15px; border-left: 5px solid #3b82f6;">
                                    <p style="margin: 0; color: #2d3748; font-size: 1rem; font-weight: 600;">
                                        <i class="fas fa-rocket" style="color: #3b82f6; margin-right: 10px;"></i>
                                        Quality Education | Research Innovation | Industry Ready | Ethical Development
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Know More Button -->
                        <div style="text-align: left; margin-top: 30px;">
                            <a href="#" class="know-more-btn" style="color: #16a085; font-size: 1.1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s ease;">
                                Know More
                                <i class="fas fa-arrow-right" style="margin-left: 10px; transition: transform 0.3s ease;"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HOD Message - 40% width -->
                <div class="col-md-5">
                    <div class="hod-card" style="padding: 30px; border-radius: 20px;border:#2d3748 1px solid; height: 90%; display: flex; flex-direction: column;">
                        <div class="text-center mb-3">
                            <h4 style="margin-bottom: 20px; font-weight: 700;">Message from Leadership</h4>
                        </div>

                        <!-- Leadership Members Side by Side -->
                        <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 20px;">
                            <!-- HOD Section -->
                            <div class="leadership-member" style="text-align: center; flex: 1;">
                                <div class="member-image-container" style="position: relative; display: inline-block; margin-bottom: 15px;">
                                    <img src="./assets/logos/sureshsir.png" alt="Head of Department"
                                        style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); object-fit: cover;">
                                </div>
                                <h6 style="color: #1e293b; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Dr. M Suresh Babu</h6>
                                <p style="color: #64748b; font-size: 0.75rem; margin-bottom: 10px;">Head of Department - CSD

                                </p>
                            </div>

                            <!-- Second Leadership Member -->
                            <div class="leadership-member" style="text-align: center; flex: 1;">
                                <div class="member-image-container" style="position: relative; display: inline-block; margin-bottom: 15px;">
                                    <img src="./assets/faculty_imgs/4.jpg" alt="Associate Head"
                                        style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); object-fit: cover;">
                                </div>
                                <h6 style="color: #1e293b; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Dr. N. Gopala Krishna Murthy</h6>
                                <p style="color: #64748b; font-size: 0.75rem; margin-bottom: 10px;">Head of Department - CSIT</p>
                            </div>
                        </div>

                        <!-- Combined Quote -->
                        <div style="text-align: center;">
                            <blockquote style="font-size: 0.85rem; line-height: 1.5; font-style: italic; margin-bottom: 0; color: #4b5563;">
                                "We nurture innovative minds and create technology leaders who will shape the future through excellence in education and innovation in research."
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Partners Section -->
    <section class="company-partners-section" style="padding: 80px 0; margin-top: -100px;">
        <div class="container">
            <div class="section-title">
                <h2>Our Industry Partners</h2>
                <p>Leading companies that trust our talent</p>
            </div>

            <div class="companies-carousel-wrapper" style="overflow: hidden; ">
                <!-- First Row -->
                <div class="carousel-row" style="display: flex; animation: scroll-left 30s linear infinite; margin-bottom: -100px; ">
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/1.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/2.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/3.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/4.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/5.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/6.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/7.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/8.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/9.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/10.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/1.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/2.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/3.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/4.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/5.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                </div>

                <!-- Second Row -->
                <div class="carousel-row" style="display: flex; animation: scroll-right 30s linear infinite; ">
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/11.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/12.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/13.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/14.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/15.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/16.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/17.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/18.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/19.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/20.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/11.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/12.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/13.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/14.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                    <div class="company-item" style="width:100%;">
                        <img src="./assets/company_logos/logos/15.png" alt="Company Partner" style="max-height: 200px; max-width: 360px; object-fit: contain;">
                    </div>
                </div>
            </div>

            <style>
                @keyframes scroll-left {
                    0% {
                        transform: translateX(0);
                    }

                    100% {
                        transform: translateX(-50%);
                    }
                }

                @keyframes scroll-right {
                    0% {
                        transform: translateX(-50%);
                    }

                    100% {
                        transform: translateX(0);
                    }
                }

                .carousel-row:hover {
                    animation-play-state: paused;
                }

                .company-item:hover {
                    transform: translateY(-5px);
                    transition: all 0.3s ease;
                }

                .primary-section {
                    padding: 60px 0;
                    min-height: 500px;
                }

                .main-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 20px;
                }

                .main-content {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 80px;
                    align-items: flex-start;
                }

                .content-left h1 {
                    font-size: 48px;
                    font-weight: 400;
                    color: #000;
                    line-height: 1.2;
                    letter-spacing: -0.02em;
                }

                .content-left h2 {
                    font-size: 48px;
                    font-weight: 400;
                    color: #000;
                    line-height: 1.2;
                    margin-bottom: 20px;
                    letter-spacing: -0.02em;
                }

                .content-left p {
                    font-size: 16px;
                    color: #6b7280;
                    line-height: 1.5;
                    margin-bottom: 40px;
                    max-width: 400px;
                }

                .primary-button {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    background: #000;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 25px;
                    text-decoration: none;
                    font-size: 14px;
                    font-weight: 500;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .primary-button:hover {
                    background: #333;
                    transform: translateY(-1px);
                }

                .content-right {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end;
                    position: relative;
                }

                /* Revenue Display */
                .revenue-display {
                    text-align: right;
                    margin-bottom: 50px;
                }

                .revenue-amount {
                    font-size: 120px;
                    font-weight: 700;
                    color: #000;
                    line-height: 1;
                    letter-spacing: -0.02em;
                }

                .revenue-label {
                    font-size: 14px;
                    color: #6b7280;
                    margin-top: 5px;
                    font-weight: 400;
                }

                /* Wavy Growth Chart */
                .growth-chart {
                    width: 350px;
                    height: 120px;
                    position: relative;
                    margin: 30px 0;
                    overflow: hidden;
                }

                .chart-background {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(249, 115, 22, 0.05) 100%);
                    border-radius: 8px;
                }

                /* Metrics Grid */
                .metrics-container {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 40px 60px;
                    max-width: 400px;
                }

                .metric-box {
                    text-align: left;
                    opacity: 0;
                    transform: translateY(20px);
                }

                .metric-value {
                    font-size: 36px;
                    font-weight: 700;
                    color: #000;
                    line-height: 1.2;
                    margin-bottom: 5px;
                }

                .metric-description {
                    font-size: 14px;
                    color: #6b7280;
                    font-weight: 400;
                }

                /* Animations */
                @keyframes drawLine {
                    to {
                        stroke-dashoffset: 0;
                    }
                }

                @keyframes growArea {
                    from {
                        opacity: 0;
                        transform: scaleY(0);
                    }

                    to {
                        opacity: 1;
                        transform: scaleY(1);
                    }
                }

                @keyframes showPoint {
                    to {
                        opacity: 1;
                        transform: scale(1.2);
                    }
                }

                @keyframes slideUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .slide-in {
                    animation: slideUp 0.6s ease forwards;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .main-content {
                        grid-template-columns: 1fr;
                        gap: 40px;
                        text-align: center;
                    }

                    .content-left h1,
                    .content-left h2 {
                        font-size: 32px;
                    }

                    .revenue-amount {
                        font-size: 72px;
                    }

                    .growth-chart {
                        width: 280px;
                        margin: 20px auto;
                    }

                    .metrics-container {
                        max-width: none;
                        margin: 0 auto;
                    }

                    .content-right {
                        align-items: center;
                    }

                    .revenue-display {
                        text-align: center;
                    }
                }
            </style>

            <div class="text-center">
                <p style="color: #64748b; font-style: italic;">And many more leading companies...</p>
            </div>
        </div>
    </section>


    <!-- Startup Partners Section -->
    <section class="startup-testimonials-section" style="padding: 100px 0; position: relative; overflow: hidden;">
        <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 20px; position: relative;">
            <!-- Floating Images Background -->
            <div class="floating-images" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 1;">
                <!-- Top Left Cluster -->
                <img src="./assets/company_logos/logos/21.png" alt="" style="position: absolute; top: 10%; left: -2%; margin: 10; width: 400px; height: 400px;  object-fit: cover;  ">
                <img src="./assets/company_logos/logos/22.png" alt="" style="position: absolute; top: 45%; left: 5%; margin: 10; width: 400px; height: 400px;  object-fit: cover; ">

                <!-- Top Right Cluster -->
                <img src="./assets/company_logos/logos/23.png" alt="" style="position: absolute; top: 18%; right: 1%; margin: 10; width: 400px; height: 400px;  object-fit: cover; ">
                <img src="./assets/company_logos/logos/24.png" alt="" style="position: absolute; top: 45%; right: 5%; margin: 10; width: 400px; height: 400px;  object-fit: cover; ">

                <!-- Bottom Left Cluster -->
                <img src="./assets/company_logos/logos/25.png" alt="" style="position: absolute; bottom: 20%; left: 2%; margin: 10; width: 400px; height: 400px;  object-fit: cover; ">

                <!-- Bottom Right Cluster -->
                <img src="./assets/company_logos/logos/26.png" alt="" style="position: absolute; bottom: 20%; right: 2%; margin: 10; width: 400px; height: 400px;  object-fit: cover; ">
            </div>

            <!-- Main Content -->
            <div style="position: relative; z-index: 2; text-align: center; margin-top:-100px">
                <div class="testimonials-header" style="margin-bottom: 60px;">
                    <h2 style="color: #1a202c; font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.1;">
                        Empowering <span style="color: #7c3aed;">entrepreneurs</span>
                    </h2>
                    <h3 style="color: #718096; font-size: 2.2rem; font-weight: 400; margin-bottom: 30px; line-height: 1.2;">
                        across diverse sectors
                    </h3>
                    <p style="color: #4a5568; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.6;">
                        Discover how our startup incubation program nurtures innovative ideas and transforms them into successful ventures across various industries.
                    </p>
                </div>
                <p style="color: #7c3aed; font-weight: 600; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Startup Ecosystem</p>

                <!-- Success Stories Button -->
                <div style="margin-top: 50px;">
                    <a href="startup-incubator.php" class="success-stories-btn" style="display: inline-flex; align-items: center; background: #1a202c; color: white; padding: 18px 35px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1rem; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(26, 32, 44, 0.2);">
                        <span>Read Success Stories</span>
                        <svg style="margin-left: 10px; width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <style>
            .startup-testimonials-section .floating-images img:hover {
                transform: scale(1.1) rotate(0deg) !important;
                z-index: 10;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2) !important;
            }

            .success-stories-btn:hover {
                background: #2d3748 !important;
                transform: translateY(-2px);
                box-shadow: 0 15px 35px rgba(26, 32, 44, 0.3) !important;
            }

            @media (max-width: 768px) {
                .startup-testimonials-section .floating-images {
                    display: none;
                }

                .testimonials-header h2 {
                    font-size: 2.5rem !important;
                }

                .testimonials-header h3 {
                    font-size: 1.8rem !important;
                }
            }
        </style>
    </section>



    <!-- Statistics Section -->
    <section class="primary-section">
        <div class="main-container">
            <div class="main-content">
                <div class="content-left">
                    <h1>Exceptional Placement</h1>
                    <h2>Record at CSD & CSIT</h2>
                    <p>66% placement rate, 50+ students placed in top MNCs out of 70, and average CTC of ₹5.1L with highest reaching ₹12L.</p>
                    <button class="primary-button">
                        View Placements
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <div class="growth-chart">
                        <div class="chart-background">
                            <svg class="growth-line" width="100%" height="100%" viewBox="0 0 310 80" style="position: absolute; bottom: 20px; left: 20px; right: 20px;">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#f97316;stop-opacity:1" />
                                    </linearGradient>
                                    <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#ef4444;stop-opacity:0.3" />
                                        <stop offset="100%" style="stop-color:#ef4444;stop-opacity:0" />
                                    </linearGradient>
                                </defs>
                                <!-- Area under curve -->
                                <path d="M 0 60 Q 50 45 100 35 T 200 25 T 310 15 L 310 80 L 0 80 Z"
                                    fill="url(#areaGradient)"
                                    style="animation: growArea 2s ease-out;">
                                </path>
                                <!-- Growth line -->
                                <path d="M 0 60 Q 50 45 100 35 T 200 25 T 310 15"
                                    stroke="url(#lineGradient)"
                                    stroke-width="3"
                                    fill="none"
                                    stroke-linecap="round"
                                    style="stroke-dasharray: 400; stroke-dashoffset: 400; animation: drawLine 2s ease-out forwards;">
                                </path>
                                <!-- Data points -->
                                <circle cx="0" cy="60" r="4" fill="#ef4444" style="animation: showPoint 2.2s ease-out forwards; opacity: 0;"></circle>
                                <circle cx="100" cy="35" r="4" fill="#f97316" style="animation: showPoint 2.4s ease-out forwards; opacity: 0;"></circle>
                                <circle cx="200" cy="25" r="4" fill="#f97316" style="animation: showPoint 2.6s ease-out forwards; opacity: 0;"></circle>
                                <circle cx="310" cy="15" r="4" fill="#f97316" style="animation: showPoint 2.8s ease-out forwards; opacity: 0;"></circle>
                            </svg>
                        </div>
                    </div>

                    <style>
                        @keyframes drawLine {
                            to {
                                stroke-dashoffset: 0;
                            }
                        }

                        @keyframes growArea {
                            from {
                                opacity: 0;
                                transform: scaleY(0);
                            }

                            to {
                                opacity: 1;
                                transform: scaleY(1);
                            }
                        }

                        @keyframes showPoint {
                            to {
                                opacity: 1;
                                transform: scale(1.2);
                            }
                        }
                    </style>
                </div>

                <div class="content-right">
                    <div class="revenue-display">
                        <div class="revenue-amount">₹5.1L</div>
                        <div class="revenue-label">Average CTC</div>
                    </div>



                    <div class="metrics-container">
                        <div class="metric-box">
                            <div class="metric-value">500+</div>
                            <div class="metric-description">Students</div>
                        </div>

                        <div class="metric-box">
                            <div class="metric-value">50+</div>
                            <div class="metric-description">Internships from 2nd year</div>
                        </div>

                        <div class="metric-box">
                            <div class="metric-value">25+</div>
                            <div class="metric-description">Top Faculty</div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Simple slide-in animation on scroll
        const observerSettings = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const scrollObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('slide-in');
                }
            });
        }, observerSettings);

        document.querySelectorAll('.metric-box').forEach(element => {
            scrollObserver.observe(element);
        });
    </script>

    <!-- Department Highlights Section -->
    <section class="dept-highlights-section" style="padding: 60px 0; margin-top: -70px;">
        <div class="container">
            <div class="section-title text-center mb-4">
                <h2 style="color: #1e293b; font-size: 2.2rem; font-weight: 700; margin-bottom: 15px;">Best Practices of CSD & CSIT</h2>
                <p style="color: #64748b; font-size: 1rem; max-width: 600px; margin: 0 auto;">
                    Empowering students with software development, startup culture, research, and holistic learning.
                </p>
            </div>

            <div class="highlights-masonry" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 30px;">
                <!-- Large Feature Card -->
                <div class="highlight-card large-card" style="grid-row: span 2; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 15px; padding: 25px; position: relative; overflow: hidden;transition: all 0.3s ease;">
                    <div style="margin-top: 20px;">
                        <h3 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 12px; line-height: 1.2;">50 Seated Software Development Centre</h3>
                        <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 20px; line-height: 1.4;">
                            20+ software applications developed by students with 50+ internships/live projects from startups.
                        </p>
                        <div style="display: inline-flex; align-items: center; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                            <span style="margin-right: 6px;">Explore Projects</span>
                            <i class="fas fa-arrow-right" style="transition: transform 0.3s ease; font-size: 0.7rem;"></i>
                        </div>
                    </div>
                </div>

                <!-- Side Cards Column 1 -->
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div class="highlight-card" style="background: linear-gradient(135deg, #e0f2fe, #b3e5fc); color: #0277bd; border-radius: 12px; padding: 20px; transition: all 0.3s ease;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px;">Earn While Studying</h4>
                        <p style="font-size: 0.8rem; margin-bottom: 15px; line-height: 1.4;">
                            On-campus paid internships, consultancy projects, and part-time jobs in campus startups.
                        </p>
                    </div>

                    <div class="highlight-card" style="background: linear-gradient(135deg, #f3e5f5, #e1bee7); color: #7b1fa2; border-radius: 12px; padding: 20px;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px;">Strong Startup Culture</h4>
                        <p style="font-size: 0.8rem; margin-bottom: 15px; line-height: 1.4;">
                            3 startups owned by alumni with a strong 20+ member Startup Club executing on-campus ventures.
                        </p>
                    </div>

                    <div class="highlight-card" style="background: linear-gradient(135deg, #fff8e1, #ffecb3); color: #f57c00; border-radius: 12px; padding: 20px;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px;">Stress Free Education</h4>
                        <p style="font-size: 0.8rem; margin-bottom: 15px; line-height: 1.4;">
                            Learning by doing, joyful learning, and holistic development with focus on sports, culturals, and arts.
                        </p>
                    </div>
                </div>

                <!-- Side Cards Column 2 -->
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div class="highlight-card featured" style="background: linear-gradient(135deg, #1e293b 0%, #374151 100%); color: white; border-radius: 12px; padding: 25px; position: relative; overflow: hidden; transition: all 0.3s ease;">
                        <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 10px; margin-top: 15px;">Industry Connect</h3>
                        <p style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 18px; line-height: 1.4;">
                            Exclusive tie-ups with 6+ startups and MNCs, regular industrial visits, expert talks & interactions.
                        </p>
                    </div>

                    <div class="highlight-card" style="background: linear-gradient(135deg, #f0f9ff, #dbeafe); color: #1e40af; border-radius: 12px; padding: 20px; position: relative;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; margin-top: 15px;">Research & Consultancy</h4>
                        <p style="font-size: 0.8rem; margin-bottom: 15px; line-height: 1.4;">
                            Faculty hold funded research projects worth ₹1.2 crore and consultancy raised through startups.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Feature Section -->
            <div class="bottom-feature" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 20px; padding: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center;">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 15px; line-height: 1.2;">Skill Development & Holistic Learning</h2>
                    <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 20px; line-height: 1.5;">
                        MOUs with Swecha AP, Wadhwani Foundation, AICTE IDEALab, KAIZEN, PurpleLane. Student-led clubs and houses focusing on emotional intelligence, leadership, design thinking, and entrepreneurship.
                    </p>
                    <div style="display: inline-flex; align-items: center; background: #22c55e; color: white; padding: 10px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); font-size: 0.85rem;">
                        <span style="margin-right: 8px;">Explore Student Clubs</span>
                        <i class="fas fa-arrow-right" style="transition: transform 0.3s ease; font-size: 0.7rem;"></i>
                    </div>
                </div>
                <div style="position: relative;">
                    <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); transform: rotate(2deg);">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="fas fa-users" style="color: white; font-size: 16px;"></i>
                            </div>
                            <h4 style="margin: 0; color: #1e293b; font-weight: 700; font-size: 1rem;">Skills Focused</h4>
                        </div>
                        <p style="color: #6b7280; margin: 0; line-height: 1.4; font-size: 0.8rem;">
                            Creativity, design & development, critical thinking, innovation, problem-solving, leadership, and self-learning.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <section class="hero-section" style="flex: 1; padding: 150px; margin-top: -180px;">
        <div class="hero-content">


            <h1 class="hero-title">
                Where Talent Meets <span class="highlight">Competition</span>
            </h1>

            <div class="hero-buttons">
                <a href="houses_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-trophy"></i> House Activites </a>
                <a href="students_overview.php" class="btn" style="background: #f1f5f9; color: #475569;">
                    Students Overview
                </a>
            </div>
        </div>

        <!-- Houses Section -->
        <section class="houses-section" style="flex: 1;">
            <div class="houses-container">
                <!-- Rotating circle -->
                <div class="houses-circle"></div>

                <!-- Center logo -->
                <div class="center-logo">
                    <img src="./assets/logos/allhouses.webp" alt="All Houses">
                </div>

                <!-- House items -->
                <div class="house-item house-agni">
                    <button class="house-button" onclick="window.location.href='#'">
                        <img src="./assets/logos/3.jpg" alt="Agni">
                    </button>
                    <div class="house-name">Agni</div>
                </div>

                <div class="house-item house-vayu">
                    <button class="house-button" onclick="window.location.href='#'">
                        <img src="./assets/logos/2.jpg" alt="Vayu">
                    </button>
                    <div class="house-name">Vayu</div>
                </div>

                <div class="house-item house-prudhvi">
                    <button class="house-button" onclick="window.location.href='#'">
                        <img src="./assets/logos/4.jpg" alt="Prudhvi">
                    </button>
                    <div class="house-name">Prudhvi</div>
                </div>

                <div class="house-item house-jal">
                    <button class="house-button" onclick="window.location.href='#'">
                        <img src="./assets/logos/1.jpg" alt="Jal">
                    </button>
                    <div class="house-name">Jal</div>
                </div>

                <div class="house-item house-aakash">
                    <button class="house-button" onclick="window.location.href='#'">
                        <img src="./assets/logos/5.jpg" alt="Aakash">
                    </button>
                    <div class="house-name">Aakash</div>
                </div>

                <!-- Connecting lines -->
                <div class="connecting-lines">
                    <div class="line" style="width: 180px; transform: rotate(-90deg);"></div>
                    <div class="line" style="width: 180px; transform: rotate(-18deg);"></div>
                    <div class="line" style="width: 180px; transform: rotate(54deg);"></div>
                    <div class="line" style="width: 180px; transform: rotate(126deg);"></div>
                    <div class="line" style="width: 180px; transform: rotate(198deg);"></div>
                </div>
            </div>
        </section>
    </section>


    <!-- Clubs and Activities Section -->
    <section class="clubs-activities-section" style="padding: 80px 0; position: relative; overflow: hidden; margin-top: -220px;">
        <!-- Background Decorative Elements -->
        <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%; opacity: 0.6;"></div>
        <div style="position: absolute; bottom: -75px; right: -75px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>

        <div class="container" style="position: relative; z-index: 2;">
            <div class="section-title text-center" style="margin-bottom: 60px;">
                <p style=" font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;">Student Life</p>
                <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 15px; line-height: 1.1;">Clubs & <span style="color: #ffd700;">Activities</span></h2>
                <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.5;">Join our vibrant community through diverse student-led clubs and activities</p>
            </div>

            <!-- Clubs Grid -->
            <div class="clubs-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; max-width: 1400px; margin: 0 auto;">

                <!-- Startup Club -->
                <div class="club-card" style="border-radius: 20px; padding: 30px; text-align: center; border: #000 1px solid; transition: all 0.3s ease; backdrop-filter: blur(15px);">

                    <h3 style="color: #1e293b; font-size: 1.4rem; font-weight: 700; margin-bottom: 8px;">Startup Club</h3>
                    <h4 style="color: #19547b; font-size: 1rem; font-weight: 600; margin-bottom: 15px;">Entrepreneurship Hub</h4>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">Turn ideas into reality with mentors, investors, and entrepreneurs.</p>

                </div>

                <!-- SDC Club -->
                <div class="club-card" style="border-radius: 20px; padding: 30px; text-align: center; border: #000 1px solid; transition: all 0.3s ease; backdrop-filter: blur(15px);">

                    <h3 style="color: #1e293b; font-size: 1.4rem; font-weight: 700; margin-bottom: 8px;">SDC</h3>
                    <h4 style="color: #667eea; font-size: 1rem; font-weight: 600; margin-bottom: 15px;">Software Development</h4>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">Build innovative solutions with cutting-edge technologies and real-world projects.</p>

                </div>

                <!-- CDC Club -->
                <div class="club-card" style="border-radius: 20px; padding: 30px; text-align: center; border: #000 1px solid; transition: all 0.3s ease; backdrop-filter: blur(15px);">

                    <h3 style="color: #1e293b; font-size: 1.4rem; font-weight: 700; margin-bottom: 8px;">CDC</h3>
                    <h4 style="color: #f093fb; font-size: 1rem; font-weight: 600; margin-bottom: 15px;">Content Development</h4>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">Create compelling content, design graphics, and manage digital platforms.</p>

                </div>

                <!-- Swecha Club -->
                <div class="club-card" style="border-radius: 20px; padding: 30px; text-align: center; border: #000 1px solid; transition: all 0.3s ease; backdrop-filter: blur(15px);">

                    <h3 style="color: #1e293b; font-size: 1.4rem; font-weight: 700; margin-bottom: 8px;">Swecha</h3>
                    <h4 style="color: #4facfe; font-size: 1rem; font-weight: 600; margin-bottom: 15px;">Free Software Movement</h4>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">Promote open source technologies and contribute to digital freedom.</p>

                </div>

                <!-- IEI Club -->
                <div class="club-card" style="border-radius: 20px; padding: 30px; text-align: center; border: #000 1px solid; transition: all 0.3s ease; backdrop-filter: blur(15px);">

                    <h3 style="color: #1e293b; font-size: 1.4rem; font-weight: 700; margin-bottom: 8px;">IEI</h3>
                    <h4 style="color: #ff6b6b; font-size: 1rem; font-weight: 600; margin-bottom: 15px;">Institution of Engineers</h4>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">Professional engineering society fostering technical excellence.</p>

                </div>

            </div>

            <!-- Call to Action -->
            <div style="text-align: center; margin-top: 60px;">
                <p style="font-size: 1.1rem; margin-bottom: 25px; font-style: italic;">Ready to make your mark? Join a club that matches your passion!</p>
                <a href="#" style="display: inline-flex; align-items: center; background: rgba(0, 0, 0, 0.2); color: white; padding: 15px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: all 0.3s ease; border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">
                    <span>Explore All Clubs</span>
                    <i class="fas fa-arrow-right" style="margin-left: 8px; font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>

        <style>
            .club-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35) !important;
            }

            .club-card:hover .fas,
            .club-card:hover .fab {
                transform: scale(1.1);
            }

            @media (max-width: 1200px) {
                .clubs-grid {
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
                }
            }

            @media (max-width: 768px) {
                .clubs-grid {
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
                    gap: 15px !important;
                }

                .club-card {
                    padding: 25px !important;
                }
            }

            @media (max-width: 576px) {
                .clubs-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    </section>



    <!-- Recent News & Events -->
    <!-- <section class="news-events-section" style="padding: 80px 0; margin-top: -100px;">
        <div class="container">
            <div class="section-title">
                <h2>Latest News & Events</h2>
                <p>Stay updated with the latest happenings in our department</p>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <div class="news-card" style="background: white; border-radius: 8px; padding: 20px; border-left: 4px solid #dc2626; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%;"></div>
                            <span style="color: #dc2626; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Breaking</span>
                            <span style="color: #94a3b8; font-size: 0.7rem;">• 2 hours ago</span>
                        </div>
                        <h5 style="color: #1e293b; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">Students Win National Hackathon</h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; line-height: 1.4;">Team secured first place in AI Hackathon 2024, competing against 200+ teams nationwide.</p>
                    </div>
                </div>

                <div class="col mb-3">
                    <div class="news-card" style="background: white; border-radius: 8px; padding: 20px; border-left: 4px solid #059669; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                            <span style="color: #059669; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Event</span>
                            <span style="color: #94a3b8; font-size: 0.7rem;">• April 20, 2024</span>
                        </div>
                        <h5 style="color: #1e293b; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">Tech Symposium 2024</h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; line-height: 1.4;">Annual technical symposium featuring industry speakers and innovation showcase.</p>
                    </div>
                </div>

                <div class="col mb-3">
                    <div class="news-card" style="background: white; border-radius: 8px; padding: 20px; border-left: 4px solid #3b82f6; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                            <span style="color: #3b82f6; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Research</span>
                            <span style="color: #94a3b8; font-size: 0.7rem;">• March 28, 2024</span>
                        </div>
                        <h5 style="color: #1e293b; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">New Research Publication</h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; line-height: 1.4;">Faculty publishes breakthrough research in machine learning at top-tier conference.</p>
                    </div>
                </div>

                <div class="col mb-3">
                    <div class="news-card" style="background: white; border-radius: 8px; padding: 20px; border-left: 4px solid #f59e0b; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;"></div>
                            <span style="color: #f59e0b; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Industry</span>
                            <span style="color: #94a3b8; font-size: 0.7rem;">• March 25, 2024</span>
                        </div>
                        <h5 style="color: #1e293b; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">New Industry Partnership</h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; line-height: 1.4;">Strategic partnership with tech giant for internships and collaborative projects.</p>
                    </div>
                </div>

                <div class="col mb-3">
                    <div class="news-card" style="background: white; border-radius: 8px; padding: 20px; border-left: 4px solid #8b5cf6; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 8px; height: 8px; background: #8b5cf6; border-radius: 50%;"></div>
                            <span style="color: #8b5cf6; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Alumni</span>
                            <span style="color: #94a3b8; font-size: 0.7rem;">• March 20, 2024</span>
                        </div>
                        <h5 style="color: #1e293b; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">Alumni Success Story</h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0; line-height: 1.4;">Graduate lands prestigious role at Google, inspiring current students.</p>
                    </div>
                </div>
            </div>

            <style>
                .news-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
                }

                @media (max-width: 1200px) {
                    .row .col {
                        flex: 0 0 50%;
                        max-width: 50%;
                    }
                }

                @media (max-width: 768px) {
                    .row .col {
                        flex: 0 0 100%;
                        max-width: 100%;
                    }
                }
            </style>
        </div>
    </section> -->


    <!-- Photo Gallery Section -->
    <section class="photo-gallery-section" style="padding: 80px 0; margin-top: -80px;">
        <div class="container">
            <div class="section-title text-center" style="margin-bottom: 60px       ;">
                <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 15px; line-height: 1.1;">Moments & <span style="color: #3b82f6;">Memories</span></h2>
                <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.5;">A glimpse into the vibrant life of our department through photos</p>
            </div>
            <div class="gallery-carousel-wrapper" style="overflow: hidden; margin-bottom: 40px;">
                <!-- First Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-left 25s linear infinite; margin-bottom: 20px; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/1.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/2.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/3.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/4.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/5.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/6.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/9.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                </div>

                <!-- Second Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-right 25s linear infinite; margin-bottom: 20px; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/10.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/11.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/12.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/13.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/14.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/17.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/18.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                </div>

                <!-- Third Row -->
                <div class="gallery-carousel-row" style="display: flex; animation: gallery-scroll-left 25s linear infinite; gap: 15px;">
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/19.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/20.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/21.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/1.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/2.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <!-- Duplicate items for seamless loop -->
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/3.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="gallery-item" style="position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; min-width: 300px; height: 200px; transition: all 0.3s ease;">
                        <img src="./assets/memories/4.jpg" alt="Department Memory" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
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

    <!-- Scroll to Top Styles -->
    <style>
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: rgba(0,0,0,0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .progress-circle {
            position: absolute;
            top: 0;
            left: 0;
        }

        .progress-bar {
            transition: stroke-dashoffset 0.3s ease;
            transform-origin: center;
            transform: rotate(-90deg);
        }

        .scroll-to-top i {
            color: white;
            font-size: 20px;
            z-index: 1;
            position: relative;
        }

        .scroll-to-top:hover {
            background: rgba(0,0,0,0.9);
            transform: scale(1.1);
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .scroll-to-top.bounce {
            animation: bounce 0.6s ease;
        }
    </style>

    <!-- Call to Action -->
    <section class="cta-section" style="padding: 80px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Ready to Shape Your Future?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9;">Join our community of innovators, creators, and future technology leaders</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="students_overview.php" class="btn" style="background: white; color: #667eea; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: 600;">
                    Student Portal
                </a>
                <a href="faculty_dashboard.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: 600; border: 2px solid rgba(255,255,255,0.3);">
                    Faculty Portal
                </a>
            </div>
        </div>
    </section>


    <?php include "footer.php"; ?>

    <!-- Scroll to Top Button -->
    <div id="scroll-to-top" class="scroll-to-top">
        <svg class="progress-circle" width="60" height="60">
            <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="4" fill="none"></circle>
            <circle cx="30" cy="30" r="25" stroke="#007bff" stroke-width="4" fill="none" stroke-dasharray="157" stroke-dashoffset="157" class="progress-bar"></circle>
        </svg>
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Add hover effects for company cards
        document.querySelectorAll('.company-item').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add hover effects for startup cards
        document.querySelectorAll('.startup-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and panes
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = '#e2e8f0';
                    btn.style.color = '#64748b';
                    btn.style.borderBottom = 'none';
                });

                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                    pane.style.display = 'none';
                });

                // Add active class to clicked button
                this.classList.add('active');
                this.style.background = '#16a085';
                this.style.color = 'white';
                this.style.borderBottom = '3px solid #16a085';

                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab') + '-tab';
                const targetTab = document.getElementById(tabId);
                if (targetTab) {
                    targetTab.classList.add('active');
                    targetTab.style.display = 'block';
                }
            });
        });

        // Know More button hover effect
        document.querySelectorAll('.know-more-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.color = '#0d9488';
                const arrow = this.querySelector('i');
                if (arrow) arrow.style.transform = 'translateX(5px)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.color = '#16a085';
                const arrow = this.querySelector('i');
                if (arrow) arrow.style.transform = 'translateX(0)';
            });
        });

        // Highlight cards hover effects
        document.querySelectorAll('.highlight-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const arrows = this.querySelectorAll('.fas.fa-arrow-right');
                arrows.forEach(arrow => {
                    arrow.style.transform = 'translateX(5px)';
                });
            });
            card.addEventListener('mouseleave', function() {
                const arrows = this.querySelectorAll('.fas.fa-arrow-right');
                arrows.forEach(arrow => {
                    arrow.style.transform = 'translateX(0)';
                });
            });
        });

        // Bottom feature button hover
        document.querySelector('.bottom-feature [style*="background: #22c55e"]')?.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.background = '#16a34a';
        });

        document.querySelector('.bottom-feature [style*="background: #22c55e"]')?.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.background = '#22c55e';
        });

        // Add animation for statistics
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-item h3');
            statNumbers.forEach(stat => {
                const finalNumber = stat.textContent;
                let currentNumber = 0;
                const increment = parseInt(finalNumber) / 100;

                const timer = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= parseInt(finalNumber)) {
                        stat.textContent = finalNumber;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentNumber) + (finalNumber.includes('%') ? '%' : finalNumber.includes('+') ? '+' : finalNumber.includes('₹') ? 'L' : '');
                    }
                }, 20);
            });
        }

        // Trigger animation when stats section is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                    observer.unobserve(entry.target);
                }
            });
        });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        }

        // Scroll to Top Functionality
        const scrollButton = document.getElementById('scroll-to-top');
        const progressBar = document.querySelector('.progress-bar');

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            const offset = 157 - (157 * scrollPercent / 100);
            progressBar.style.strokeDashoffset = offset;

            if (scrollTop > 100) {
                scrollButton.classList.add('show');
            } else {
                scrollButton.classList.remove('show');
            }

            // Bottom animation - bounce when reaching bottom
            if (scrollTop + window.innerHeight >= document.documentElement.scrollHeight - 10) {
                scrollButton.classList.add('bounce');
                setTimeout(() => {
                    scrollButton.classList.remove('bounce');
                }, 600);
            }
        });

        scrollButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>