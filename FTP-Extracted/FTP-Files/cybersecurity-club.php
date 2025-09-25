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
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    padding: 80px 0;
}

.security-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: transform 0.3s ease;
}

.security-card:hover {
    transform: translateY(-5px);
}

.threat-alert {
    background: linear-gradient(135deg, #ff9ff3 0%, #f368e0 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
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
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">Cybersecurity Club</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9;">Defending the digital frontier through education, research, and hands-on security practices</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-shield-alt" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Club Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="security-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">About Cybersecurity Club</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            The Cybersecurity Club at SRKREC is dedicated to creating awareness about cybersecurity threats, 
                            teaching defensive and offensive security techniques, and building a community of ethical hackers 
                            and security professionals.
                        </p>
                        <p style="color: #64748b; line-height: 1.8;">
                            We focus on practical, hands-on learning through CTF competitions, security workshops, 
                            penetration testing exercises, and real-world security scenarios.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                        <h4>Security Stats</h4>
                        <div style="margin: 25px 0;">
                            <h3>300+</h3>
                            <p>Security Enthusiasts</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>25+</h3>
                            <p>CTF Competitions Won</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>50+</h3>
                            <p>Security Workshops</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Domains -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Security Domains</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="security-card" style="border-left: 5px solid #ff6b6b;">
                        <i class="fas fa-bug" style="font-size: 40px; color: #ff6b6b; margin-bottom: 20px;"></i>
                        <h5>Penetration Testing</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Ethical hacking and vulnerability assessment</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Web application testing</li>
                            <li>Network penetration testing</li>
                            <li>Mobile app security</li>
                            <li>Social engineering</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="security-card" style="border-left: 5px solid #74b9ff;">
                        <i class="fas fa-search" style="font-size: 40px; color: #74b9ff; margin-bottom: 20px;"></i>
                        <h5>Digital Forensics</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Investigation and analysis of digital evidence</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Computer forensics</li>
                            <li>Mobile forensics</li>
                            <li>Network forensics</li>
                            <li>Malware analysis</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="security-card" style="border-left: 5px solid #00b894;">
                        <i class="fas fa-lock" style="font-size: 40px; color: #00b894; margin-bottom: 20px;"></i>
                        <h5>Cryptography</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Encryption, decryption, and secure communication</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Symmetric encryption</li>
                            <li>Asymmetric encryption</li>
                            <li>Hash functions</li>
                            <li>Digital signatures</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTF Achievements -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">CTF Achievements</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="security-card" style="border-left: 5px solid #ffd700;">
                        <h5 style="color: #1e293b;">🥇 National Cyber Security CTF</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">1st Place - Team "CyberWarriors"</p>
                        <p style="color: #64748b; margin-bottom: 10px;">Solved 25/30 challenges in web security, cryptography, and forensics</p>
                        <small style="color: #94a3b8;">October 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="security-card" style="border-left: 5px solid #c0c0c0;">
                        <h5 style="color: #1e293b;">🥈 ISRO Cybersecurity Challenge</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">2nd Place - Team "SecureCoders"</p>
                        <p style="color: #64748b; margin-bottom: 10px;">Specialized in space systems security and satellite communication</p>
                        <small style="color: #94a3b8;">September 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="security-card" style="border-left: 5px solid #cd7f32;">
                        <h5 style="color: #1e293b;">🥉 PicoCTF International</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">3rd Place - Individual Category</p>
                        <p style="color: #64748b; margin-bottom: 10px;">Excellent performance in reverse engineering and binary exploitation</p>
                        <small style="color: #94a3b8;">August 2024</small>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="security-card" style="border-left: 5px solid #6c5ce7;">
                        <h5 style="color: #1e293b;">🏆 State Level Security Hackathon</h5>
                        <p style="color: #64748b; margin-bottom: 10px;">Winners - "Zero Day Hunters"</p>
                        <p style="color: #64748b; margin-bottom: 10px;">Developed innovative security tool for IoT device protection</p>
                        <small style="color: #94a3b8;">July 2024</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Weekly Activities -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Weekly Activities</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="threat-alert">
                        <h5><i class="fas fa-flag"></i> CTF Fridays</h5>
                        <p>Every Friday: 6:00 PM - 9:00 PM</p>
                        <p style="opacity: 0.9;">Practice CTF challenges, team formation, and strategy discussions</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="threat-alert" style="background: linear-gradient(135deg, #55a3ff 0%, #003d82 100%);">
                        <h5><i class="fas fa-hammer"></i> Exploit Wednesdays</h5>
                        <p>Every Wednesday: 4:00 PM - 6:00 PM</p>
                        <p style="opacity: 0.9;">Hands-on penetration testing and vulnerability exploitation workshops</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="threat-alert" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                        <h5><i class="fas fa-shield-virus"></i> Malware Mondays</h5>
                        <p>Every Monday: 5:00 PM - 7:00 PM</p>
                        <p style="opacity: 0.9;">Malware analysis, reverse engineering, and incident response training</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="threat-alert" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                        <h5><i class="fas fa-users-shield"></i> Security Saturdays</h5>
                        <p>Every Saturday: 10:00 AM - 12:00 PM</p>
                        <p style="opacity: 0.9;">Community outreach, cybersecurity awareness, and guest lectures</p>
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
                    <div class="security-card">
                        <div style="background: #ff6b6b; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">CyberSecCon 2024</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> December 18-20, 2024</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Annual cybersecurity conference with industry experts and live CTF</p>
                        <a href="#" class="btn btn-danger">Register</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="security-card">
                        <div style="background: #74b9ff; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Ethical Hacking Workshop</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> November 30, 2024</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Hands-on workshop on web application penetration testing</p>
                        <a href="#" class="btn btn-primary">Join</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="security-card">
                        <div style="background: #00b894; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Bug Bounty Bootcamp</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> January 15-17, 2025</p>
                        <p style="color: #64748b; margin-bottom: 20px;">3-day intensive training on bug bounty hunting techniques</p>
                        <a href="#" class="btn btn-success">Enroll</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Club -->
    <section style="padding: 60px 0; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white;">
        <div class="container text-center">
            <h2 style="margin-bottom: 30px;">Ready to Secure the Digital World?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 40px; opacity: 0.9;">Join our cybersecurity community and become an ethical hacker</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div style="background: rgba(255,255,255,0.1); padding: 40px; border-radius: 15px;">
                        <h4 style="margin-bottom: 20px;">Why Join Us?</h4>
                        <ul style="list-style: none; padding: 0; margin-bottom: 30px;">
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Industry-relevant skills</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Hands-on experience</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> CTF competitions</li>
                            <li><i class="fas fa-check-circle"></i> Career opportunities</li>
                        </ul>
                        <a href="#" class="btn" style="background: white; color: #ff6b6b; margin: 10px;">
                            <i class="fas fa-user-shield"></i> Join Club
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
