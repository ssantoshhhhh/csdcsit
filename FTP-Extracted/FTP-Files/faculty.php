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
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .faculty-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .faculty-card:hover {
        border-color: #3b82f6;
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .faculty-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        overflow: hidden;
        border: 3px solid #f3f4f6;
        transition: all 0.2s ease;
    }

    .faculty-card:hover .faculty-photo {
        border-color: #3b82f6;
    }

    .faculty-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.2s ease;
    }

    .faculty-photo img:hover {
        transform: scale(1.05);
    }

    .faculty-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .faculty-designation {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    .faculty-email {
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

    .faculty-email:hover {
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

    <!-- CSD Faculty -->
    <section class="department-section">
        <h2 class="department-title"><span class="csd-badge">CSD</span> Faculty</h2>
        <div class="faculty-grid">
            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/19.jpg" alt="Dr. K. Srinivasa Rao">
                </div>
                <h4 class="faculty-name">Dr. K. Srinivasa Rao</h4>
                <p class="faculty-designation">Professor</p>
                <a href="mailto:ksinivasarao@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/8.jpg" alt="S Mohan Krishna">
                </div>
                <h4 class="faculty-name">S Mohan Krishna</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:mohankrishna.seerla@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/16.png" alt="Mr. K. Bhanu Rajesh Naidu">
                </div>
                <h4 class="faculty-name">Mr. K. Bhanu Rajesh Naidu</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:bhanurajeshnaidu@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/6.jpg" alt="A Aswini Priyanka">
                </div>
                <h4 class="faculty-name">A Aswini Priyanka</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:aswini.areti@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/1.png" alt="Angara Satyam">
                </div>
                <h4 class="faculty-name">Angara Satyam</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:satyama@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>



            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/7.png" alt="P S V Surya Kumar">
                </div>
                <h4 class="faculty-name">P S V Surya Kumar</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:suryakumar.poduru@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>
        </div>
    </section>

    <!-- CSIT Faculty -->
    <section class="department-section">
        <h2 class="department-title"><span class="csit-badge">CSIT</span> Faculty</h2>
        <div class="faculty-grid">

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/12.jpg" alt="N Praveen">
                </div>
                <h4 class="faculty-name">N Praveen</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:neti.praveen@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

             <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/10.jpg" alt="K V Sunil Varma">
                </div>
                <h4 class="faculty-name">K V Sunil Varma</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:sunil.kunuku@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/5.jpg" alt="Jonnapalli Tulasi Rajesh">
                </div>
                <h4 class="faculty-name">Jonnapalli Tulasi Rajesh</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:jtulasirajesh@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/11.jpg" alt="Navya Nallaparaju">
                </div>
                <h4 class="faculty-name">Navya Nallaparaju</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:navyanallaparaju@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>



            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/2.png" alt="Mr. A. Krishna Veni">
                </div>
                <h4 class="faculty-name">Mr. A. Krishna Veni</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:krishnaveni@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/15.png" alt="Mr. K.V.V.S. Trinadh Naidu">
                </div>
                <h4 class="faculty-name">Mr. K.V.V.S. Trinadh Naidu</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:kvvstrinadhnaidu@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/17.jpeg" alt="Penmetsa Mouna">
                </div>
                <h4 class="faculty-name">Penmetsa Mouna</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:mouna.nandyala@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

            <div class="faculty-card">
                <div class="faculty-photo">
                    <img src="assets/faculty_imgs/13.png" alt="Pericherla Manoj">
                </div>
                <h4 class="faculty-name">Pericherla Manoj</h4>
                <p class="faculty-designation">Assistant Professor</p>
                <a href="mailto:manoj.p@srkrec.edu.in" class="faculty-email">
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
            </div>

           
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>

</html>
</body>

</html>