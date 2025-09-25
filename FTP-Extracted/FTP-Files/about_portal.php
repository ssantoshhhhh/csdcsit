<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
?>

<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-header" style="background: linear-gradient(135deg, #076593 0%, #0089E4 100%); color: white; padding: 60px 0; text-align: center;">
        <div class="container">
            <h1 class="section-title-large" style="color: white; margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> About This <span style="color: #ffd700;">Portal</span>
            </h1>
            <p class="section-description" style="color: rgba(255,255,255,0.9);">Learn about the SRKR CSD & CSIT Attendance Management System</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Introduction Section -->
            <div class="card mb-4" style="background: var(--light-blue); border: none; border-radius: 15px;">
                <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                    <h3 class="card-header-title" style="color: white; margin-bottom: 0;"><i class="fas fa-graduation-cap"></i> Welcome to SRKR Attendance Portal</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title text-primary mb-3">What is this Portal?</h4>
                            <p class="text-body">
                                This is the official attendance management portal for SRKR Engineering College's Computer Science & Design (CSD) and Computer Science & Information Technology (CSIT) departments. 
                                Our portal provides a comprehensive system for tracking student attendance, managing academic performance, and fostering healthy competition through house points.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-clipboard-check fa-4x text-primary mb-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="row mb-4">
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-users fa-3x text-primary"></i>
                            </div>
                            <h4 class="card-title text-primary mb-3">Attendance Tracking</h4>
                            <p class="text-body">
                                Faculty can easily mark attendance for different sections and sessions. The system tracks daily attendance, 
                                calculates attendance percentages, and maintains detailed records for all students.
                            </p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i> Daily attendance marking</li>
                                <li><i class="fas fa-check text-success me-2"></i> Session-wise tracking</li>
                                <li><i class="fas fa-check text-success me-2"></i> Attendance percentage calculation</li>
                                <li><i class="fas fa-check text-success me-2"></i> Historical data access</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-trophy fa-3x text-warning"></i>
                            </div>
                            <h5 class="text-warning mb-3">Leaderboard System</h5>
                            <p class="text-muted">
                                Students can view attendance leaderboards to see their performance compared to peers. 
                                This encourages healthy competition and motivates students to maintain good attendance.
                            </p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i> Real-time rankings</li>
                                <li><i class="fas fa-check text-success me-2"></i> Section-wise leaderboards</li>
                                <li><i class="fas fa-check text-success me-2"></i> Performance indicators</li>
                                <li><i class="fas fa-check text-success me-2"></i> Achievement tracking</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-home fa-3x text-success"></i>
                            </div>
                            <h5 class="text-success mb-3">House Points System</h5>
                            <p class="text-muted">
                                Our unique house system divides students into five houses: Aakash, Jal, Vayu, Pruthvi, and Agni. 
                                Students earn points for various achievements, creating a competitive and engaging environment.
                            </p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i> Five competitive houses</li>
                                <li><i class="fas fa-check text-success me-2"></i> Point-based achievements</li>
                                <li><i class="fas fa-check text-success me-2"></i> House rankings</li>
                                <li><i class="fas fa-check text-success me-2"></i> Performance tracking</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-chart-bar fa-3x text-info"></i>
                            </div>
                            <h5 class="text-info mb-3">Analytics & Reports</h5>
                            <p class="text-muted">
                                Faculty and HODs can access detailed analytics and reports to monitor attendance trends, 
                                identify patterns, and make data-driven decisions for academic improvement.
                            </p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i> Detailed analytics</li>
                                <li><i class="fas fa-check text-success me-2"></i> Trend analysis</li>
                                <li><i class="fas fa-check text-success me-2"></i> Export capabilities</li>
                                <li><i class="fas fa-check text-success me-2"></i> Performance insights</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Departments Section -->
            <div class="card mb-4" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                    <h3 class="card-header-title" style="color: white; margin-bottom: 0;"><i class="fas fa-building"></i> Supported Departments</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="department-item mb-3">
                                <h6 class="text-primary"><i class="fas fa-laptop-code"></i> Computer Science & Design (CSD)</h6>
                                <p class="text-muted mb-2">A comprehensive program focusing on computer science fundamentals with design thinking and creative problem-solving skills.</p>
                                <div class="sections">
                                    <span class="badge bg-info me-2">2/4 CSD</span>
                                    <span class="badge bg-info me-2">3/4 CSD</span>
                                    <span class="badge bg-info">4/4 CSD</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="department-item mb-3">
                                <h6 class="text-primary"><i class="fas fa-microchip"></i> Computer Science & Information Technology (CSIT)</h6>
                                <p class="text-muted mb-2">A specialized program combining computer science with information technology applications and modern computing practices.</p>
                                <div class="sections">
                                    <span class="badge bg-success me-2">2/4 CSIT-A</span>
                                    <span class="badge bg-success me-2">2/4 CSIT-B</span>
                                    <span class="badge bg-success">3/4 CSIT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- How to Use Section -->
            <div class="card mb-4" style="background: var(--light-blue); border: none; border-radius: 15px;">
                <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-question-circle"></i> How to Use This Portal</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary" style="font-size: 1.2rem; padding: 10px 15px;">1</span>
                                </div>
                                <h6 class="text-primary">For Students</h6>
                                <p class="text-muted small">
                                    Browse attendance records, view leaderboards, and check your house points. 
                                    Use the navigation menu to access different sections.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary" style="font-size: 1.2rem; padding: 10px 15px;">2</span>
                                </div>
                                <h6 class="text-primary">For Faculty</h6>
                                <p class="text-muted small">
                                    Login with your credentials to mark attendance, view reports, and manage student data. 
                                    Access is restricted to authorized personnel only.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary" style="font-size: 1.2rem; padding: 10px 15px;">3</span>
                                </div>
                                <h6 class="text-primary">For HODs</h6>
                                <p class="text-muted small">
                                    Access comprehensive analytics, export reports, and monitor overall department performance. 
                                    Full administrative access available.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Section -->
            <div class="card mb-5" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-headset"></i> Need Help?</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Technical Support</h6>
                            <p class="text-muted">
                                If you encounter any technical issues or have questions about using the portal, 
                                please contact the IT department or your faculty coordinator.
                            </p>
                            <ul class="list-unstyled">
                                <!-- <li><i class="fas fa-envelope text-primary me-2"></i> it@srkrec.ac.in</li>
                                <li><i class="fas fa-phone text-primary me-2"></i> +91 (8816) 223332</li> -->
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-home"></i> Back to Home
                                </a>
                                <!-- <a href="student_attendance.php" class="btn btn-outline-success">
                                    <i class="fas fa-users"></i> View Attendance
                                </a> -->
                                <a href="houses_display.php" class="btn btn-outline-warning">
                                    <i class="fas fa-home"></i> House System
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <style>
        .feature-icon {
            transition: all 0.3s ease;
        }
        
        .card:hover .feature-icon {
            transform: scale(1.1);
        }
        
        .step-number {
            transition: all 0.3s ease;
        }
        
        .card:hover .step-number {
            transform: scale(1.1);
        }
        
        .department-item {
            padding: 15px;
            border-radius: 10px;
            background: var(--light-blue);
            transition: all 0.3s ease;
        }
        
        .department-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .sections .badge {
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .feature-icon i {
                font-size: 2.5rem !important;
            }
        }
    </style>
</body>
</html> 