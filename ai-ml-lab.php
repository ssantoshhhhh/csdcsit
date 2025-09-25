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

.project-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: transform 0.3s ease;
}

.project-card:hover {
    transform: translateY(-5px);
}

.research-area {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 25px;
    text-align: center;
}
</style>

<body>
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">AI & Machine Learning Research Lab</h1>
                    <p style="font-size: 1.2rem; opacity: 0.9;">Exploring the frontiers of Artificial Intelligence and shaping the future with intelligent systems</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-brain" style="font-size: 120px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Lab Overview -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="project-card">
                        <h2 style="color: #1e293b; margin-bottom: 20px;">About AI/ML Research Lab</h2>
                        <p style="color: #64748b; line-height: 1.8; margin-bottom: 20px;">
                            Our AI & Machine Learning Research Lab is at the forefront of cutting-edge research in artificial intelligence, 
                            machine learning, deep learning, and related technologies. We focus on both theoretical foundations and 
                            practical applications that can make a real impact in various domains.
                        </p>
                        <p style="color: #64748b; line-height: 1.8;">
                            The lab provides students with hands-on experience in developing AI solutions, conducting research, 
                            and collaborating with industry partners on innovative projects.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                        <h4>Lab Statistics</h4>
                        <div style="margin: 25px 0;">
                            <h3>150+</h3>
                            <p>Research Students</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>25+</h3>
                            <p>Ongoing Projects</p>
                        </div>
                        <div style="margin: 25px 0;">
                            <h3>80+</h3>
                            <p>Publications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Research Areas -->
    <section style="padding: 40px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Research Areas</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="research-area">
                        <i class="fas fa-eye" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h4>Computer Vision</h4>
                        <p>Image recognition, object detection, facial recognition, medical imaging analysis</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="research-area" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h4>Natural Language Processing</h4>
                        <p>Text analysis, sentiment analysis, chatbots, language translation, text generation</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="research-area" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h4>Predictive Analytics</h4>
                        <p>Time series forecasting, recommendation systems, business intelligence, data mining</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Current Projects -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Current Projects</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="project-card">
                        <div style="display: flex; align-items: center; margin-bottom: 20px;">
                            <div style="width: 60px; height: 60px; background: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                                <i class="fas fa-hospital" style="color: white; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="margin: 0; color: #1e293b;">Medical Image Analysis</h5>
                                <p style="margin: 0; color: #64748b;">Healthcare AI Project</p>
                            </div>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Developing AI models for early detection of diseases through medical imaging. 
                            Collaborating with local hospitals for data collection and validation.
                        </p>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <span class="badge bg-primary">Computer Vision</span>
                            <span class="badge bg-success">Healthcare</span>
                            <span class="badge bg-info">Deep Learning</span>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.9rem;">Status: In Progress | Team: 8 students</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="project-card">
                        <div style="display: flex; align-items: center; margin-bottom: 20px;">
                            <div style="width: 60px; height: 60px; background: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                                <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="margin: 0; color: #1e293b;">Intelligent Chatbot</h5>
                                <p style="margin: 0; color: #64748b;">NLP Research Project</p>
                            </div>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Building an intelligent chatbot for student queries using advanced NLP techniques. 
                            Integrating with university systems for personalized responses.
                        </p>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <span class="badge bg-success">NLP</span>
                            <span class="badge bg-warning">Chatbot</span>
                            <span class="badge bg-info">BERT</span>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.9rem;">Status: Testing Phase | Team: 6 students</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="project-card">
                        <div style="display: flex; align-items: center; margin-bottom: 20px;">
                            <div style="width: 60px; height: 60px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                                <i class="fas fa-chart-bar" style="color: white; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="margin: 0; color: #1e293b;">Student Performance Predictor</h5>
                                <p style="margin: 0; color: #64748b;">Educational Analytics</p>
                            </div>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Predicting student academic performance using machine learning algorithms. 
                            Helping identify at-risk students for early intervention.
                        </p>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <span class="badge bg-warning">Machine Learning</span>
                            <span class="badge bg-primary">Education</span>
                            <span class="badge bg-success">Analytics</span>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.9rem;">Status: Data Collection | Team: 5 students</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="project-card">
                        <div style="display: flex; align-items: center; margin-bottom: 20px;">
                            <div style="width: 60px; height: 60px; background: #ef4444; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                                <i class="fas fa-car" style="color: white; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="margin: 0; color: #1e293b;">Autonomous Vehicle Vision</h5>
                                <p style="margin: 0; color: #64748b;">Computer Vision Project</p>
                            </div>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;">
                            Developing vision systems for autonomous vehicles using deep learning. 
                            Focus on object detection and path planning algorithms.
                        </p>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <span class="badge bg-danger">Computer Vision</span>
                            <span class="badge bg-dark">Autonomous Systems</span>
                            <span class="badge bg-info">CNN</span>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.9rem;">Status: Prototype Development | Team: 10 students</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities & Resources -->
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Lab Facilities & Resources</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="project-card text-center">
                        <i class="fas fa-server" style="font-size: 48px; color: #3b82f6; margin-bottom: 20px;"></i>
                        <h5>High-Performance Computing</h5>
                        <p style="color: #64748b;">GPU clusters with NVIDIA Tesla V100 for deep learning model training</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="project-card text-center">
                        <i class="fas fa-database" style="font-size: 48px; color: #10b981; margin-bottom: 20px;"></i>
                        <h5>Datasets & Libraries</h5>
                        <p style="color: #64748b;">Access to premium datasets and state-of-the-art ML libraries and frameworks</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="project-card text-center">
                        <i class="fas fa-users" style="font-size: 48px; color: #f59e0b; margin-bottom: 20px;"></i>
                        <h5>Expert Mentorship</h5>
                        <p style="color: #64748b;">Guidance from PhD faculty and industry experts in AI/ML domain</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events & Workshops -->
    <section style="padding: 60px 0; background: #f8fafc;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #1e293b;">Upcoming Events</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="project-card">
                        <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">AI Research Symposium</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> January 15-16, 2025</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Annual symposium featuring latest research presentations and industry talks</p>
                        <a href="#" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="project-card">
                        <div style="background: #10b981; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Deep Learning Workshop</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> December 20, 2024</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Hands-on workshop on advanced deep learning techniques and frameworks</p>
                        <a href="#" class="btn btn-success">Register</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="project-card">
                        <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                            <h5 style="margin: 0;">Industry Collaboration Meet</h5>
                        </div>
                        <p style="color: #64748b; margin-bottom: 15px;"><i class="fas fa-calendar"></i> February 10, 2025</p>
                        <p style="color: #64748b; margin-bottom: 20px;">Networking event with AI/ML professionals and potential collaborators</p>
                        <a href="#" class="btn btn-warning">Attend</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Research -->
    <section style="padding: 60px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container text-center">
            <h2 style="margin-bottom: 30px;">Join Our Research Community</h2>
            <p style="font-size: 1.1rem; margin-bottom: 40px; opacity: 0.9;">Be part of cutting-edge AI research and innovation</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div style="background: rgba(255,255,255,0.1); padding: 40px; border-radius: 15px;">
                        <h4 style="margin-bottom: 20px;">Research Opportunities</h4>
                        <ul style="list-style: none; padding: 0; margin-bottom: 30px;">
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Undergraduate Research Projects</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Publication Opportunities</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Industry Collaboration</li>
                            <li><i class="fas fa-check-circle"></i> Conference Presentations</li>
                        </ul>
                        <a href="#" class="btn" style="background: white; color: #667eea; margin: 10px;">
                            <i class="fas fa-flask"></i> Apply for Research
                        </a>
                        <a href="#" class="btn" style="background: rgba(255,255,255,0.2); color: white; margin: 10px;">
                            <i class="fas fa-info-circle"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
