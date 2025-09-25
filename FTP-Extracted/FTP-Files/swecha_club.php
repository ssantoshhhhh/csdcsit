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

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,107,107,0.2) 0%, rgba(78,205,196,0.2) 100%);
    z-index: 1;
}

.swecha-card {
    background: linear-gradient(to right bottom, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: all 0.3s ease;
    border: 1px solid rgba(78,205,196,0.2);
}

.swecha-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(78,205,196,0.2);
    border-color: rgba(78,205,196,0.4);
}

.swecha-card:hover {
    transform: translateY(-5px);
}

.activity-card {
    color: black;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 8px 20px rgba(108,99,255,0.2);
    position: relative;
    overflow: hidden;
}

.activity-card::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1));
    transform: skewX(-15deg);
    transition: transform 0.3s ease;
}

.mission-card {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.mission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">Swecha Club</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9;">Promoting Free Software, Open Source, and Digital Freedom</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-code-branch" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Club Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="swecha-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">About Swecha Club</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            Swecha Club at SRKREC is dedicated to promoting free software, open source culture, and digital freedom. 
                            We believe in the power of collaborative learning and sharing knowledge through open source contributions.
                        </p>
                        <p style="color: #64748b; line-height: 1.8;">
                            Our mission is to create awareness about free software alternatives, encourage students to contribute to 
                            open source projects, and build a community of tech enthusiasts who believe in digital freedom.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                        <h4>Club Statistics</h4>
                        <div style="margin: 25px 0;">
                            <h3>200+</h3>
                            <p>Active Members</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>30+</h3>
                            <p>Open Source Projects</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>40+</h3>
                            <p>Workshops Conducted</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Focus Areas -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Our Focus Areas</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border-left: 5px solid #2ecc71;">
                        <i class="fas fa-laptop-code" style="font-size: 40px; color: #2ecc71; margin-bottom: 20px;"></i>
                        <h5>Free Software</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Promoting free and open source alternatives</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Linux Operating Systems</li>
                            <li>Open Source Software</li>
                            <li>Free Software Philosophy</li>
                            <li>Software Freedom</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border-left: 5px solid #3498db;">
                        <i class="fas fa-users" style="font-size: 40px; color: #3498db; margin-bottom: 20px;"></i>
                        <h5>Community Building</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Creating a collaborative learning environment</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Knowledge Sharing</li>
                            <li>Peer Learning</li>
                            <li>Mentorship Programs</li>
                            <li>Community Events</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border-left: 5px solid #e74c3c;">
                        <i class="fas fa-code" style="font-size: 40px; color: #e74c3c; margin-bottom: 20px;"></i>
                        <h5>Open Source Development</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Contributing to open source projects</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Version Control (Git)</li>
                            <li>Project Contribution</li>
                            <li>Code Reviews</li>
                            <li>Documentation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Activities -->
    <section style="padding: 60px 0; background: #ffffffff;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Recent Activities</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="activity-card">
                        <div class="activity-header" style="margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 15px;">
                            <h5><i class="fas fa-calendar-alt"></i> Swecha Freedom Fest 2025</h5>
                            <p style="font-size: 0.9rem; margin-top: 10px;">March 15-17, 2025 | SRKREC Campus</p>
                        </div>
                        <p style="margin-bottom: 20px;">A three-day celebration of software freedom, digital rights, and open source innovation. Join us for an immersive experience of learning, collaboration, and technological advancement.</p>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <h6 style="color: #fff; margin-bottom: 10px;">Event Highlights</h6>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Open Source Exhibition & Project Showcase</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Tech Talks by Industry Experts</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Hands-on Workshops on Latest Technologies</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> 36-Hour Open Source Hackathon</li>
                                <li><i class="fas fa-check-circle"></i> Community Networking Sessions</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="activity-card">
                        <div class="activity-header" style="margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 15px;">
                            <h5><i class="fas fa-book"></i> Mission Kithab</h5>
                            <p style="font-size: 0.9rem; margin-top: 10px;">Ongoing Initiative | Digital Freedom in Education</p>
                        </div>
                        <p style="margin-bottom: 20px;">A revolutionary digital library initiative making educational resources freely accessible to all. Supporting the vision of knowledge freedom and open education.</p>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <h6 style="color: #fff; margin-bottom: 10px;">Key Features</h6>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Comprehensive E-book Collection (10,000+ titles)</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Open Educational Resources & Study Materials</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Interactive Learning Platforms</li>
                                <li style="margin-bottom: 8px;"><i class="fas fa-check-circle"></i> Collaborative Resource Development</li>
                                <li><i class="fas fa-check-circle"></i> Mobile-Friendly Access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Learning Center -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Swecha Learning Center</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border: 2px solid #FF6B6B;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <i class="fas fa-graduation-cap" style="font-size: 40px; color: #FF6B6B;"></i>
                            <h4 style="color: #FF6B6B; margin-top: 15px;">Training Programs</h4>
                        </div>
                        <ul style="color: #64748b; line-height: 1.8;">
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> Linux System Administration (Basic to Advanced)</li>
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> Open Source Development & Contribution</li>
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> Modern Web Technologies</li>
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> Python Programming & Applications</li>
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> DevOps Tools & Practices</li>
                            <li><i class="fas fa-check" style="color: #FF6B6B;"></i> Cloud Computing with Open Source</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border: 2px solid #4ECDC4;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <i class="fas fa-tools" style="font-size: 40px; color: #4ECDC4;"></i>
                            <h4 style="color: #4ECDC4; margin-top: 15px;">Resources & Tools</h4>
                        </div>
                        <ul style="color: #64748b; line-height: 1.8;">
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Comprehensive Learning Materials</li>
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Interactive Video Tutorials</li>
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Hands-on Practice Projects</li>
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Technical Documentation</li>
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Community Support Forums</li>
                            <li><i class="fas fa-check" style="color: #4ECDC4;"></i> Code Repositories & Examples</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="swecha-card" style="border: 2px solid #6C63FF;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <i class="fas fa-users-cog" style="font-size: 40px; color: #6C63FF;"></i>
                            <h4 style="color: #6C63FF; margin-top: 15px;">Special Programs</h4>
                        </div>
                        <ul style="color: #64748b; line-height: 1.8;">
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Open Source Contribution Workshop</li>
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Summer of Code Programs</li>
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Tech Mentorship Initiative</li>
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Industry Expert Sessions</li>
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Hackathons & Code Sprints</li>
                            <li><i class="fas fa-check" style="color: #6C63FF;"></i> Certification Programs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include "footer.php"; ?>
</body>
</html>
