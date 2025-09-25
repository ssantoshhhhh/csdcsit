<?php
?>
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Navigation specific styles with high specificity */
    .navbar.navbar-expand-lg {
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        padding: 1rem 0 !important;
        font-family: 'Inter', sans-serif !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    
    .navbar .navbar-brand {
        font-family: 'Inter', sans-serif !important;
        font-weight: 600 !important;
        color: #1a365d !important;
        text-decoration: none !important;
        font-size: 1.125rem !important;
    }
    
    .navbar .nav-link {
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
        color: #4a5568 !important;
        transition: color 0.2s ease !important;
        text-decoration: none !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.9rem !important;
    }
    
    .navbar .nav-link:hover {
        color: #1a365d !important;
    }
    
    .navbar .btn-outline-primary {
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
        border-radius: 6px !important;
        border-color: #3182ce !important;
        color: #3182ce !important;
        padding: 0.5rem 1rem !important;
        text-decoration: none !important;
        font-size: 0.875rem !important;
    }
    
    .navbar .btn-outline-primary:hover {
        background-color: #3182ce !important;
        border-color: #3182ce !important;
        color: #ffffff !important;
    }
    
    /* Ensure navbar toggler works properly */
    .navbar-toggler {
        border: none !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 4px !important;
    }
    
    .navbar-toggler:focus {
        box-shadow: none !important;
    }
    
    /* Remove any bullet points from navigation items */
    .navbar-nav li,
    .navbar-nav li::before,
    .nav-item,
    .nav-item::before {
        list-style: none !important;
        content: none !important;
        margin-left: 0 !important;
        padding-left: 0 !important;
    }
    
    .navbar-nav {
        list-style: none !important;
        padding-left: 0 !important;
    }
    
    /* Dropdown menu styles */
    .navbar .dropdown-menu {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07) !important;
        padding: 8px 0 !important;
        margin-top: 8px !important;
        min-width: 220px !important;
    }
    
    .navbar .dropdown-item {
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
        color: #4a5568 !important;
        padding: 8px 16px !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 0.875rem !important;
    }
    
    .navbar .dropdown-item:hover {
        background-color: #f7fafc !important;
        color: #1a365d !important;
    }
    
    .navbar .dropdown-item i {
        width: 16px !important;
        text-align: center !important;
        font-size: 14px !important;
        color: #718096 !important;
    }
    
    .navbar .dropdown-item:hover i {
        color: #3182ce !important;
    }
    
    .navbar .dropdown-divider {
        margin: 6px 0 !important;
        border-top: 1px solid #e2e8f0 !important;
    }
    
    .navbar .dropdown-toggle::after {
        margin-left: 6px !important;
        font-size: 12px !important;
    }
    
    /* Mobile dropdown improvements */
    @media (max-width: 991px) {
        .navbar .dropdown-menu {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin-top: 0 !important;
            padding-left: 16px !important;
        }
        
        .navbar .dropdown-item {
            padding: 8px 12px !important;
            border-radius: 4px !important;
            margin: 2px 0 !important;
        }
        
        .navbar .dropdown-item:hover {
            background-color: rgba(49, 130, 206, 0.1) !important;
        }
    }
</style>

<nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
        <!-- Logo + Title -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="logo.png" alt="SRKR Engineering College" 
                 onerror="this.style.display='none'" 
                 style="height:45px; margin-right:10px;">
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                
                <!-- Academics Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="academicsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-graduation-cap"></i> Academics
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="academicsDropdown">
                        <li><a class="dropdown-item" href="btech-cse.php"><i class="fas fa-book"></i> B.Tech CSD</a></li>
                        <li><a class="dropdown-item" href="btech-it.php"><i class="fas fa-laptop-code"></i> B.Tech CSIT</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="academic-calendar.php"><i class="fas fa-calendar-check"></i> Academic Calendar</a></li>
                        <li><a class="dropdown-item" href="syllabus.php"><i class="fas fa-clipboard-list"></i> Syllabus</a></li>
                    </ul>
                </li>

                <!-- Faculty -->
                <li class="nav-item">
                    <a class="nav-link" href="faculty.php">
                        <i class="fas fa-chalkboard-teacher"></i> Faculty
                    </a>
                </li>

                <!-- Placements -->
                <li class="nav-item">
                    <a class="nav-link" href="placements.php">
                        <i class="fas fa-briefcase"></i> Placements
                    </a>
                </li>

                <!-- Clubs & Activities Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="clubsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users"></i> Clubs
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="clubsDropdown">
                        <li><a class="dropdown-item" href="startup_club.php"><i class="fas fa-rocket"></i> Startup Club</a></li>
                        <li><a class="dropdown-item" href="sdc_club.php"><i class="fas fa-code"></i> SDC</a></li>
                        <li><a class="dropdown-item" href="swecha_club.php"><i class="fab fa-linux"></i> Swecha</a></li>
                    </ul>
                </li>

                <!-- Houses -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="housesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-trophy"></i> Houses
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="housesDropdown">
                        <li><a class="dropdown-item" href="houses_dashboard.php"><i class="fas fa-trophy"></i> House Activities</a></li>
                        <li><a class="dropdown-item" href="events_overview.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                        <li><a class="dropdown-item" href="students_overview.php"><i class="fas fa-calendar-alt"></i> Students</a></li>
                    </ul>
                </li>
                
                    

                <!-- Student Portal (when not logged in) or Dashboard (when logged in) -->
                <?php if (!empty($_SESSION['faculty_logged_in'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="faculty_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-power-off"></i> Logout
                        </a>
                    </li>
                <?php elseif (!empty($_SESSION['hod_logged_in'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="hod_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-power-off"></i> Logout
                        </a>
                    </li>
                <?php elseif (!empty($_SESSION['student_logged_in'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-power-off"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="students_overview.php">
                            <i class="fas fa-user-graduate"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary px-3" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                <?php endif; ?>

               
            </ul>
        </div>
    </div>
</nav>
