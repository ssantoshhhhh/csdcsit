<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php";
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.6;
        color: #1a1a1a;
    }

    /* Hero Section */
    .hero-section {
        background: #ffffff;
        padding: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .hero-content {
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
        letter-spacing: -0.025em;
    }

    .hero-subtitle {
        font-size: 1.125rem;
        color: #ffffffff;
        font-weight: 400;
        line-height: 1.5;
    }

    /* Faculty Grid */
    .faculty-section {
        padding: 3rem 0;
    }

    .section-title {
        font-size: 1.875rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2rem;
        text-align: center;
        letter-spacing: -0.025em;
    }

    .faculty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .faculty-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .faculty-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .faculty-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        overflow: hidden;
        border: 4px solid #f3f4f6;
        transition: all 0.3s ease;
        position: relative;
    }

    .faculty-card:hover .faculty-photo {
        border-color: #3b82f6;
        transform: scale(1.05);
    }

    .faculty-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .faculty-name {
        font-size: 1.375rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }

    .faculty-designation {
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .faculty-details {
        margin-top: 1.5rem;
        text-align: left;
    }

    .faculty-details p {
        margin-bottom: 1rem;
        color: #4b5563;
        font-size: 0.95rem;
    }

    .faculty-details strong {
        font-weight: 600;
        color: #111827;
    }

    .faculty-links {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .faculty-links a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #3b82f6;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .faculty-links a:hover {
        background-color: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
        text-decoration: none;
    }

    /* HOD Section */
    .hod-section {
        background: #ffffff;
        padding: 4rem 0;
        margin: 0;
    }

    .hod-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 3rem;
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .hod-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .hod-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .hod-card:hover::before {
        transform: scaleX(1);
    }

    .hod-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .hod-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        overflow: hidden;
        border: 4px solid #f3f4f6;
        transition: all 0.3s ease;
        position: relative;
    }

    .hod-card:hover .hod-photo {
        border-color: #3b82f6;
        transform: scale(1.05);
    }

    .hod-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .hod-name {
        font-size: 1.375rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }

    .hod-title {
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .hod-email {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        color: #ffffff;
        background: #3b82f6;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 2px solid #3b82f6;
    }

    .hod-email:hover {
        background: #ffffff;
        color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        text-decoration: none;
    }

    /* Department Sections */
    .department-section {
        padding: 3rem 0;
    }

    .department-title {
        font-size: 1.875rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2rem;
        padding-left: 2rem;
        text-align: center;
        letter-spacing: -0.025em;
    }

    .csd-badge {
        color: #3b82f6;
    }

    .csit-badge {
        color: #10b981;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .faculty-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .hod-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .department-title {
            padding-left: 1rem;
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {

        .hero-content,
        .faculty-grid,
        .hod-grid {
            padding: 0 1rem;
        }

        .hod-grid {
            gap: 1.5rem;
        }
    }
</style>

<body>
    <?php include "nav.php"; ?>

    <!-- Hero Section -->
    <section class="hero-section" style="background-color: #0870A4;">
        <div class="hero-content">
            <h1 class="hero-title">Faculty</h1>
            <p class="hero-subtitle">Meet our distinguished educators and researchers who are shaping the future of computer science and technology</p>
        </div>
    </section>

    <!-- HOD Section -->
    <section class="hod-section">
        <h2 class="section-title">Heads of Department</h2>
        <div class="hod-grid">
            <!-- CSD HOD -->
            <div class="hod-card">
                <div class="hod-photo">
                    <img src="assets/logos/sureshsir.png" alt="Dr. M. Suresh Babu">
                </div>
                <h3 class="hod-name">Dr. M. Suresh Babu</h3>
                <p class="hod-title">Head of Department - CSD</p>
                <a href="mailto:sureshbabu.k@srkrec.edu.in" class="hod-email">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </div>

            <!-- CSIT HOD -->
            <div class="hod-card">
                <div class="hod-photo">
                    <img src="assets/faculty_imgs/4.jpg" alt="Dr. N. Gopala Krishna Murthy">
                </div>
                <h3 class="hod-name">Dr. N. Gopala Krishna Murthy</h3>
                <p class="hod-title">Head of Department - CSIT</p>
                <a href="mailto:gopinukala@srkrec.edu.in" class="hod-email">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </div>
        </div>
    </section>

    <?php
    $csd_faculty = [
        [
            'name' => 'Dr. K. Srinivasa Rao',
            'designation' => 'Assistant Professor',
            'email' => 'kasaganasriniva@gma',
            'image' => 'assets/faculty_imgs/19.jpg',
            'profile' => 'assets/faculty_profiles/Resume-Kasagana_Srinivasa_Rao.docx',
            'linkedin' => '',
            'about' => 'More than 23 years of experience in teaching and research.',
            'experience' => '24 Years'
        ],
        [
            'name' => 'Angara Satyam',
            'designation' => 'Assistant Professor',
            'email' => 'asatyam@srkrec.ac.in',
            'image' => 'assets/faculty_imgs/1.png',
            'profile' => 'assets/faculty_profiles/A.Satyam_Profile.docx',
            'linkedin' => 'https://www.linkedin.com/pub',
            'about' => 'Research areas are Q-learning and AI.',
            'experience' => '10 Years'
        ],
        [
            'name' => 'Areti Aswani Priyanka',
            'designation' => 'Assistant Professor',
            'email' => 'aswini.areti@gmail.cor',
            'image' => 'assets/faculty_imgs/6.jpg',
            'profile' => 'assets/faculty_profiles/Faculty_Profile_Aswini.doc',
            'linkedin' => 'https://www.linkedin.com/feec',
            'about' => 'I am an Assistant professor.',
            'experience' => '9.5 Years'
        ],
        [
            'name' => 'Kamparapu Bhanu Raj',
            'designation' => 'Assistant Professor',
            'email' => 'bhanurajeshnaidu@srk',
            'image' => 'assets/faculty_imgs/16.png',
            'profile' => 'assets/faculty_profiles/Bhanu_Rajesh_Naidu_SRKR Resume.docx',
            'linkedin' => '',
            'about' => 'Cloud Computing (AWS).',
            'experience' => '9 Years'
        ],
        [
            'name' => 'Poduru Sri Venka',
            'designation' => 'Assistant Professor',
            'email' => 'suryakumar.poduru@s',
            'image' => 'assets/faculty_imgs/7.png',
            'profile' => 'assets/faculty_profiles/Surya_Kumar_Poduru.docx',
            'linkedin' => '',
            'about' => 'I am an Assistant Professor.',
            'experience' => '1.7 Years'
        ],
        [
            'name' => 'Nadimpilli Aneela',
            'designation' => 'Assistant Professor',
            'email' => 'aneela@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/9.png',
            'profile' => 'assets/faculty_profiles/ANEELA_RESUME_Aneela_Nadimpilli.docx',
            'linkedin' => 'https://www.linkedin.com/in/a',
            'about' => 'Faculty professional with experience in software industry.',
            'experience' => '2 Years'
        ],
        [
            'name' => 'S Mohan Krishna',
            'designation' => 'Assistant Professor',
            'email' => 'mohankrishna.seerla@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/8.jpg',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
    ];

    $csit_faculty = [
        [
            'name' => 'Nallaparaju Navy',
            'designation' => 'Assistant Professor',
            'email' => 'navyanallaparaju65@c',
            'image' => 'assets/faculty_imgs/11.jpg',
            'profile' => 'assets/faculty_profiles/Navya_resume.docx',
            'linkedin' => 'www.linkedin.com/in/navya-n',
            'about' => 'I AM WORKING AN AS.',
            'experience' => '4 Years'
        ],
        [
            'name' => 'Anusuri Krishna Veni',
            'designation' => 'Assistant Professor',
            'email' => 'krishnavenianusuri35@',
            'image' => 'assets/faculty_imgs/2.png',
            'profile' => 'assets/faculty_profiles/krishnaveni_anusuri.docx',
            'linkedin' => '',
            'about' => 'I am a self-learner and passionate about teaching.',
            'experience' => '7 Years'
        ],
        [
            'name' => 'Kamparapu V V Satya',
            'designation' => 'Assistant Professor',
            'email' => 'kvvstrinadhnaidu@srkr',
            'image' => 'assets/faculty_imgs/15.png',
            'profile' => 'assets/faculty_profiles/Trinadh_Kamaparapu.pdf',
            'linkedin' => '',
            'about' => 'C Programming, Java.',
            'experience' => '6 Years'
        ],
        [
            'name' => 'N Praveen',
            'designation' => 'Assistant Professor',
            'email' => 'neti.praveen@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/12.jpg',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
        [
            'name' => 'K V Sunil Varma',
            'designation' => 'Assistant Professor',
            'email' => 'sunil.kunuku@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/10.jpg',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
        [
            'name' => 'Jonnapalli Tulasi Rajesh',
            'designation' => 'Assistant Professor',
            'email' => 'jtulasirajesh@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/5.jpg',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
        [
            'name' => 'Penmetsa Mouna',
            'designation' => 'Assistant Professor',
            'email' => 'mouna.nandyala@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/17.jpeg',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
        [
            'name' => 'Pericherla Manoj',
            'designation' => 'Assistant Professor',
            'email' => 'manoj.p@srkrec.edu.in',
            'image' => 'assets/faculty_imgs/13.png',
            'profile' => '',
            'linkedin' => '',
            'about' => '',
            'experience' => ''
        ],
    ];
    ?>

    <!-- CSD Faculty -->
    <section class="department-section">
        <h2 class="department-title"><span class="csd-badge">CSD</span> Faculty</h2>
        <div class="faculty-grid">
            <?php foreach ($csd_faculty as $faculty) : ?>
                <div class="faculty-card">
                    <div class="faculty-photo">
                        <img src="<?php echo $faculty['image']; ?>" alt="<?php echo $faculty['name']; ?>">
                    </div>
                    <h3 class="faculty-name"><?php echo $faculty['name']; ?></h3>
                    <p class="faculty-designation"><?php echo $faculty['designation']; ?></p>
                    
                    <div class="faculty-details">
                        <?php if (!empty($faculty['about'])) : ?>
                            <p><strong>About:</strong> <?php echo $faculty['about']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($faculty['experience'])) : ?>
                            <p><strong>Experience:</strong> <?php echo $faculty['experience']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="faculty-links">
                        <a href="mailto:<?php echo $faculty['email']; ?>" class="faculty-email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </a>
                        <?php if (!empty($faculty['profile'])) : ?>
                            <a href="<?php echo $faculty['profile']; ?>" class="faculty-email" target="_blank">
                                <i class="fas fa-user"></i>
                                Profile
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($faculty['linkedin'])) : ?>
                            <a href="<?php echo $faculty['linkedin']; ?>" class="faculty-email" target="_blank">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CSIT Faculty -->
    <section class="department-section">
        <h2 class="department-title"><span class="csit-badge">CSIT</span> Faculty</h2>
        <div class="faculty-grid">
            <?php foreach ($csit_faculty as $faculty) : ?>
                <div class="faculty-card">
                    <div class="faculty-photo">
                        <img src="<?php echo $faculty['image']; ?>" alt="<?php echo $faculty['name']; ?>">
                    </div>
                    <h3 class="faculty-name"><?php echo $faculty['name']; ?></h3>
                    <p class="faculty-designation"><?php echo $faculty['designation']; ?></p>
                    
                    <div class="faculty-details">
                        <?php if (!empty($faculty['about'])) : ?>
                            <p><strong>About:</strong> <?php echo $faculty['about']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($faculty['experience'])) : ?>
                            <p><strong>Experience:</strong> <?php echo $faculty['experience']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="faculty-links">
                        <a href="mailto:<?php echo $faculty['email']; ?>" class="faculty-email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </a>
                        <?php if (!empty($faculty['profile'])) : ?>
                            <a href="<?php echo $faculty['profile']; ?>" class="faculty-email" target="_blank">
                                <i class="fas fa-user"></i>
                                Profile
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($faculty['linkedin'])) : ?>
                            <a href="<?php echo $faculty['linkedin']; ?>" class="faculty-email" target="_blank">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>

</html>
</body>

</html>