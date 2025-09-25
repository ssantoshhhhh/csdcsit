<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffffff;
    color: #333333;
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    margin-bottom: 40px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.contribution-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border-left: 3px solid #4158D0;
}

.contribution-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.project-area {
    border: none;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    border-left: 3px solid #4158D0;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.workshop-area {
    border: none;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    border-left: 3px solid #C850C0;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.row {
    margin-right: -15px;
    margin-left: -15px;
    display: flex;
    flex-wrap: wrap;
}

.row > [class*='col-'] {
    display: flex;
    flex-direction: column;
}

@media (max-width: 768px) {
    .row {
        margin-right: -10px;
        margin-left: -10px;
    }
    .col-md-4 {
        padding-right: 10px;
        padding-left: 10px;
    }
}

.team-badge {
    display: none;
}

/* Additional styles for better typography and spacing */
h1 {
    background: linear-gradient(135deg, #FF0080, #7928CA);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0px 2px 4px rgba(0,0,0,0.1);
    font-size: 3.5rem;
}

h2 {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 2rem;
    text-align: center;
    font-size: 2rem;
    letter-spacing: 0.3px;
}

h3 {
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    letter-spacing: 0.2px;
}

h4 {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

p {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 1rem;
}

ul {
    padding-left: 20px;
    margin-bottom: 1rem;
}

ul li {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.section-padding {
    padding: 60px 0;
}

/* Container width adjustment for better readability */
.container {
    max-width: 1140px;
    margin: 0 auto;
}

/* Animation keyframes */
@keyframes gradientBG {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .contribution-card {
        padding: 20px;
    }
    
    h1 {
        font-size: 2rem;
    }
    
    .hero-section {
        padding: 40px 0;
    }
}

/* Hover effects */
.contribution-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
}

.project-area:hover, .workshop-area:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.06);
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="color: white; -webkit-text-fill-color: white;">Software Development Club</h1>
                    <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9);">Empowering students through practical software development experience and real-world projects</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-code" style="font-size: 64px; color: #495057;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Contributions -->
    <section style="padding: 50px 0;">
        <div class="container">
           
            <!-- Website Development Projects -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Website Development Projects</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>SVCET College Website</h4>
                            <p><strong>Website:</strong> svcet</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Magical DB Website</h4>
                            <p><strong>Website:</strong> Magical DB</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>SRKR College Website</h4>
                            <ul>
                                <li>Technical assistance in updating and maintaining the website</li>
                                <li>Enhanced UI/UX and added new features</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Houses Website</h4>
                            <ul>
                                <li>Designed to manage student house-based activities</li>
                                <li>Implemented a dynamic dashboard for easy navigation</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Panchayati Website</h4>
                            <p>Development of a Panchayati website for the BVRM locality, featuring WhatsApp integration for direct interaction.</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Smart Wash Website</h4>
                            <ul>
                                <li>Development with assistance of 2nd years</li>
                                <li>Platform for users to manage their laundry services</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>LICRS Website</h4>
                            <p>Website developed for the International Conference on Intra- and Inter-Cellular Regulatory Systems 2024 at the University of Hyderabad.</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Fest Website Development</h4>
                            <p>Designed a dedicated website for fest registrations and updates.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workshops and Training -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Workshops and Training Programs</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>Web Development Workshop</h4>
                            <p>Covered HTML, CSS, JavaScript, and responsive design techniques.</p>
                            <p><strong>Scope:</strong> Entire College</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>C Lab and Internal Evaluation</h4>
                            <ul>
                                <li>Regular Lab sessions for fundamental concepts</li>
                                <li>C Internal Assessment using Hacker Rank</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>Full Stack Workshop</h4>
                            <p><strong>Scope:</strong> Department Level (2 Weeks)</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>Summer Coding Classes</h4>
                            <p><strong>Location:</strong> Westberry School</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>GitHub Workshop</h4>
                            <ul>
                                <li>Training on version control and collaborative coding</li>
                                <li>Enhanced project management skills</li>
                            </ul>
                            <p><strong>Scope:</strong> Department Level</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>C Classes for Juniors</h4>
                            <p>Sessions covering fundamental programming concepts</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>Full Stack Academic Class for Juniors</h4>
                            <p>Core web development concepts, including frontend, backend, and database management</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>MERN Workshop</h4>
                            <p>In-depth training on MongoDB, Express.js, React.js, and Node.js</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="workshop-area h-100">
                            <h4>MERN Academic Classes (Parallels)</h4>
                            <p>Parallel sessions conducted to accommodate different student batches</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Organization and Technical Support -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Event Organization and Technical Support</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>SpellBee Organization</h4>
                            <p>Active role in planning, coordinating, and executing the event.</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Fest Organization - Technical Support</h4>
                            <h5>KBC Software</h5>
                            <p>Developed a custom software system for conducting a quiz event.</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Software for Free Fire</h4>
                            <p>Created a tracking and management system for a gaming event</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="project-area h-100">
                            <h4>Fest Organization (End-to-End)</h4>
                            <ul>
                                <li>Non-Tech Events organization</li>
                                <li>Finance management</li>
                                <li>Overall organization coordination</li>
                                <li>Artwork design</li>
                                <li>Technical aspects coordination</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hackathons and Events -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Hackathons and Events</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="project-area h-100">
                            <h4>WEBTECH Hackathon (Department Level)</h4>
                            <ul>
                                <li>Organized and mentored participants</li>
                                <li>Encouraged innovation and hands-on problem-solving</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="project-area h-100">
                            <h4>GDG Hackathon (College Level)</h4>
                            <ul>
                                <li>Active participation and contribution</li>
                                <li>Real-world problem-solving experience</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internship Guidance -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Internship Guidance</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="workshop-area h-100">
                            <h4>VB Alpha</h4>
                            <p>Focus on stock market domain</p>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="workshop-area h-100">
                            <h4>FalconX</h4>
                            <p>Guiding two interns from FalconX company</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekend Sessions -->
            <div class="contribution-card">
                <h3 style="color: #4a5568; margin-bottom: 20px;">Weekend Sessions</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="workshop-area h-100">
                            <h4>AI Fridays</h4>
                            <p>Brief introduction about AI's use in Corporate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
