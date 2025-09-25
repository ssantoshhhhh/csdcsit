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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
}

.club-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    transition: transform 0.3s ease;
}

.club-card:hover {
    transform: translateY(-5px);
}

.activity-item {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 4px solid #3b82f6;
}

.achievement-badge {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">Coding Club</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9;">Where passion meets programming - Join us in the journey of coding excellence</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-code" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Club Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="club-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">About Coding Club</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            The Coding Club at SRKREC is a vibrant community of passionate programmers, problem solvers, and technology enthusiasts. 
                            We believe in learning by doing and aim to enhance the programming skills of students through various activities, 
                            competitions, and collaborative projects.
                        </p>
                        <p style="color: #64748b; line-height: 1.8;">
                            Our mission is to create a platform where students can explore different programming languages, participate in 
                            competitive programming, contribute to open-source projects, and prepare for technical interviews at top companies.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="achievement-badge">
                        <h4>Club Stats</h4>
                        <div style="margin: 20px 0;">
                            <h3>500+</h3>
                            <p>Active Members</p>
                        </div>
                        <div style="margin: 20px 0;">
                            <h3>50+</h3>
                            <p>Events Organized</p>
                        </div>
                        <div style="margin: 20px 0;">
                            <h3>25+</h3>
                            <p>Hackathons Won</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Activities -->
    <section style="padding: 40px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Our Activities</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="activity-item">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-trophy" style="font-size: 24px; color: #3b82f6; margin-right: 15px;"></i>
                            <h5 style="margin: 0;">Competitive Programming</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">Regular contests on platforms like CodeChef, Codeforces, and HackerRank</p>
                        <ul style="color: #64748b; margin: 0;">
                            <li>Weekly programming contests</li>
                            <li>ACM ICPC training sessions</li>
                            <li>Algorithm workshops</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="activity-item" style="border-left-color: #10b981;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-laptop-code" style="font-size: 24px; color: #10b981; margin-right: 15px;"></i>
                            <h5 style="margin: 0;">Hackathons</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">24-48 hour coding marathons to solve real-world problems</p>
                        <ul style="color: #64748b; margin: 0;">
                            <li>Internal hackathons</li>
                            <li>National level competitions</li>
                            <li>Innovation challenges</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="activity-item" style="border-left-color: #f59e0b;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fab fa-github" style="font-size: 24px; color: #f59e0b; margin-right: 15px;"></i>
                            <h5 style="margin: 0;">Open Source Projects</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">Contributing to open source and building community projects</p>
                        <ul style="color: #64748b; margin: 0;">
                            <li>GitHub collaboration</li>
                            <li>Community contributions</li>
                            <li>Project showcases</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="activity-item" style="border-left-color: #ef4444;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 24px; color: #ef4444; margin-right: 15px;"></i>
                            <h5 style="margin: 0;">Tech Talks & Workshops</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">Learning sessions on latest technologies and frameworks</p>
                        <ul style="color: #64748b; margin: 0;">
                            <li>Technology seminars</li>
                            <li>Industry expert sessions</li>
                            <li>Hands-on workshops</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Upcoming Events</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="club-card">
                        <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">CodeFest 2024</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> December 15-16, 2024</p>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-clock"></i> 48-hour Hackathon</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Annual flagship event with prizes worth ₹2 lakhs</p>
                        <a href="#" class="btn btn-primary">Register Now</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="club-card">
                        <div style="background: #10b981; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Algorithm Workshop</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> November 25, 2024</p>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-clock"></i> 2:00 PM - 5:00 PM</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Advanced algorithms and data structures</p>
                        <a href="#" class="btn btn-success">Join Workshop</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="club-card">
                        <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Open Source Day</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> December 5, 2024</p>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-clock"></i> All Day Event</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Contribute to open source projects</p>
                        <a href="#" class="btn btn-warning">Participate</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Recent Achievements</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="club-card" style="border-left: 5px solid #ffd700;">
                        <h5 style="color: #1e293b;">🏆 National Hackathon Winners</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">Our team won 1st place at TechnoHack 2024</p>
                        <small style="color: #94a3b8;">October 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="club-card" style="border-left: 5px solid #c0c0c0;">
                        <h5 style="color: #1e293b;">🥈 ACM ICPC Regionals</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">Secured 2nd position in regional programming contest</p>
                        <small style="color: #94a3b8;">September 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="club-card" style="border-left: 5px solid #cd7f32;">
                        <h5 style="color: #1e293b;">🥉 Google Code Jam</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">3rd place in university category</p>
                        <small style="color: #94a3b8;">August 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="club-card" style="border-left: 5px solid #3b82f6;">
                        <h5 style="color: #1e293b;">💡 Innovation Award</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">Best innovation project at State Tech Fest</p>
                        <small style="color: #94a3b8;">July 2024</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Us -->
    <section style="padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container text-center">
            <h2 style="margin-bottom: 30px;">Ready to Code with Us?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 40px; opacity: 0.9;">Join our community of passionate programmers and take your coding skills to the next level</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div style="background: rgba(255,255,255,0.1); padding: 40px; border-radius: 15px;">
                        <h4 style="margin-bottom: 20px;">How to Join</h4>
                        <p style="margin-bottom: 30px;">Fill out our membership form and attend our next meeting</p>
                        <a href="#" class="btn" style="background: white; color: #667eea; margin: 10px;">
                            <i class="fas fa-user-plus"></i> Join Club
                        </a>
                        <a href="#" class="btn" style="background: rgba(255,255,255,0.2); color: white; margin: 10px;">
                            <i class="fab fa-discord"></i> Join Discord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
