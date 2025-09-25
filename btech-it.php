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

.it-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: transform 0.3s ease;
}

.it-card:hover {
    transform: translateY(-5px);
}

.semester-tab {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.semester-tab:hover {
    background: #e2e8f0;
}

.semester-tab.active {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.subject-list {
    display: none;
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-top: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.subject-list.active {
    display: block;
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">Computer Science & Information Technology</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9;">4-Year Undergraduate Program | AICTE Approved | Industry Focused</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-laptop-code" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="it-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">Program Overview</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            The B.Tech in Information Technology program is designed to prepare students for the rapidly evolving 
                            IT industry. The curriculum focuses on software development, system administration, network management, 
                            database technologies, and emerging fields like cloud computing and cybersecurity.
                        </p>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            Our program emphasizes practical learning through industry projects, internships, and hands-on laboratory 
                            sessions. Students gain expertise in modern technologies and frameworks used in the IT industry.
                        </p>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 10px; margin-top: 20px;">
                            <h5 style="color: #1e293b; margin-bottom: 15px;">Program Highlights</h5>
                            <ul style="color: #64748b; margin: 0;">
                                <li>Industry-oriented curriculum with latest IT trends</li>
                                <li>Emphasis on practical and project-based learning</li>
                                <li>Strong foundation in software and hardware technologies</li>
                                <li>Industry partnerships and guest lectures</li>
                                <li>Excellent placement record in IT companies</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; border-radius: 15px; text-align: center; margin-bottom: 30px;">
                        <h4>Program Details</h4>
                        <div style="margin: 25px 0;">
                            <h5>Duration</h5>
                            <p>4 Years (8 Semesters)</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h5>Total Credits</h5>
                            <p>160 Credits</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h5>Intake</h5>
                            <p>120 Students</p>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <h5 style="color: #1e293b; margin-bottom: 20px;">Admission Requirements</h5>
                        <ul style="color: #64748b; margin: 0;">
                            <li>12th grade with Physics, Chemistry & Mathematics</li>
                            <li>Minimum 75% in 12th grade</li>
                            <li>Valid JEE Main score</li>
                            <li>State entrance exam score (if applicable)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Curriculum -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Curriculum Structure</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="semester-tab" onclick="toggleSemesterIT('sem1')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 1 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem1">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Mathematics I</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Physics</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Chemistry</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Programming in C</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">English Communication</li>
                            <li style="padding: 10px;">Computer Fundamentals</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem2')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 2 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem2">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Mathematics II</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Environmental Science</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Programming in Java</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Digital Electronics</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Basic Electrical Engineering</li>
                            <li style="padding: 10px;">IT Workshop</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem3')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 3 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem3">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Data Structures</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Computer Organization</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Discrete Mathematics</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Object Oriented Programming</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Database Management Systems</li>
                            <li style="padding: 10px;">Web Technologies</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem4')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 4 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem4">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Software Engineering</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Operating Systems</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Computer Networks</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">System Programming</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Theory of Computation</li>
                            <li style="padding: 10px;">Network Programming</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="semester-tab" onclick="toggleSemesterIT('sem5')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 5 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem5">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Information Security</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Distributed Systems</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Data Mining</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Mobile Computing</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Elective I</li>
                            <li style="padding: 10px;">Mini Project</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem6')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 6 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem6">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Cloud Computing</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Software Project Management</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Enterprise Resource Planning</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Network Security</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Elective II</li>
                            <li style="padding: 10px;">Industry Training</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem7')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 7 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem7">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Artificial Intelligence</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">DevOps and Automation</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Internet of Things</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Elective III</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Elective IV</li>
                            <li style="padding: 10px;">Major Project I</li>
                        </ul>
                    </div>
                    
                    <div class="semester-tab" onclick="toggleSemesterIT('sem8')">
                        <h5 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                            Semester 8 
                            <i class="fas fa-chevron-down"></i>
                        </h5>
                    </div>
                    <div class="subject-list" id="sem8">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Industry Project</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Advanced Elective</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Technical Seminar</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Major Project II</li>
                            <li style="padding: 10px; border-bottom: 1px solid #f3f4f6;">Professional Skills</li>
                            <li style="padding: 10px;">Comprehensive Viva</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- IT Specializations -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">IT Specialization Areas</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="it-card" style="border-left: 5px solid #10b981;">
                        <i class="fas fa-cloud" style="font-size: 40px; color: #10b981; margin-bottom: 20px;"></i>
                        <h5>Cloud Computing & DevOps</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">AWS, Azure, Docker, Kubernetes, CI/CD</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Cloud Architecture</li>
                            <li>Container Technologies</li>
                            <li>Infrastructure as Code</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="it-card" style="border-left: 5px solid #3b82f6;">
                        <i class="fas fa-globe" style="font-size: 40px; color: #3b82f6; margin-bottom: 20px;"></i>
                        <h5>Web & Mobile Development</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Full Stack Development, React, Angular, Flutter</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Frontend Frameworks</li>
                            <li>Backend Development</li>
                            <li>Mobile App Development</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="it-card" style="border-left: 5px solid #f59e0b;">
                        <i class="fas fa-network-wired" style="font-size: 40px; color: #f59e0b; margin-bottom: 20px;"></i>
                        <h5>Network & System Administration</h5>
                        <p style="color: #64748b; margin-bottom: 15px;">Network Management, System Security, Infrastructure</p>
                        <ul style="color: #64748b; font-size: 0.9rem;">
                            <li>Network Design</li>
                            <li>System Administration</li>
                            <li>IT Infrastructure</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Laboratory Facilities -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Laboratory Facilities</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="it-card">
                        <h5 style="color: #1e293b; margin-bottom: 20px;">Software Development Labs</h5>
                        <ul style="color: #64748b; margin: 0;">
                            <li>Programming Languages Lab</li>
                            <li>Web Development Lab</li>
                            <li>Mobile App Development Lab</li>
                            <li>Database Management Lab</li>
                            <li>Software Testing Lab</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="it-card">
                        <h5 style="color: #1e293b; margin-bottom: 20px;">Infrastructure Labs</h5>
                        <ul style="color: #64748b; margin: 0;">
                            <li>Network Configuration Lab</li>
                            <li>Cloud Computing Lab</li>
                            <li>Cybersecurity Lab</li>
                            <li>System Administration Lab</li>
                            <li>IoT and Embedded Systems Lab</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Career Prospects -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Career Opportunities</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="it-card">
                        <h5 style="color: #1e293b; margin-bottom: 20px;">Job Roles</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul style="color: #64748b; margin: 0;">
                                    <li>Software Developer</li>
                                    <li>System Administrator</li>
                                    <li>Network Engineer</li>
                                    <li>Cloud Architect</li>
                                    <li>DevOps Engineer</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul style="color: #64748b; margin: 0;">
                                    <li>Database Administrator</li>
                                    <li>IT Consultant</li>
                                    <li>Project Manager</li>
                                    <li>Security Analyst</li>
                                    <li>Technical Support</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="it-card">
                        <h5 style="color: #1e293b; margin-bottom: 20px;">Industry Sectors</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul style="color: #64748b; margin: 0;">
                                    <li>IT Services</li>
                                    <li>Software Companies</li>
                                    <li>Telecommunications</li>
                                    <li>Banking & Finance</li>
                                    <li>E-commerce</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul style="color: #64748b; margin: 0;">
                                    <li>Healthcare IT</li>
                                    <li>Government Agencies</li>
                                    <li>Manufacturing</li>
                                    <li>Consulting Firms</li>
                                    <li>Startups</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include "footer.php"; ?>

    <script>
        function toggleSemesterIT(semId) {
            // Hide all semester content
            const allSemesters = document.querySelectorAll('.subject-list');
            const allTabs = document.querySelectorAll('.semester-tab');
            
            allSemesters.forEach(sem => sem.classList.remove('active'));
            allTabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected semester
            const selectedSem = document.getElementById(semId);
            const selectedTab = event.currentTarget;
            
            if (selectedSem.classList.contains('active')) {
                selectedSem.classList.remove('active');
                selectedTab.classList.remove('active');
            } else {
                selectedSem.classList.add('active');
                selectedTab.classList.add('active');
            }
        }
    </script>
</body>
</html>
