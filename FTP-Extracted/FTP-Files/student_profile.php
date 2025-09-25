<?php
session_start();
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    header('Location: sections_overview.php');
    exit();
}

// Get student information
$student_query = "
    SELECT s.*, c.year, c.semester, c.academic_year, c.branch as class_branch, c.section as class_section,
           h.name as house_name,
           sp.parent_number, sp.address, sp.blood_group, sp.dob,
           spr.summary, spr.skills, spr.social_links, spr.projects, spr.experience, 
           spr.education, spr.certifications, spr.achievements, spr.cgpa
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN houses h ON s.hid = h.hid
    LEFT JOIN student_personal sp ON s.student_id = sp.student_id
    LEFT JOIN student_profile spr ON s.student_id = spr.student_id
    WHERE s.student_id = ?
";
$stmt = mysqli_prepare($conn, $student_query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);
$student_data = mysqli_fetch_assoc($student_result);

if (!$student_data) {
    header('Location: sections_overview.php');
    exit();
}

$student_name = $student_data['name'];
$student_branch = $student_data['class_branch'] ?? $student_data['branch'];
$student_section = $student_data['class_section'] ?? $student_data['section'];
$student_year = $student_data['year'];
$house_name = $student_data['house_name'];

// Get attendance summary for current month
$current_month = date('Y-m');
$attendance_query = "
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_sessions
    FROM student_attendance 
    WHERE student_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?
";
$attendance_stmt = mysqli_prepare($conn, $attendance_query);
mysqli_stmt_bind_param($attendance_stmt, "ss", $student_id, $current_month);
mysqli_stmt_execute($attendance_stmt);
$attendance_result = mysqli_stmt_get_result($attendance_stmt);
$attendance_data = mysqli_fetch_assoc($attendance_result);

$total_sessions = $attendance_data['total_sessions'] ?? 0;
$present_sessions = $attendance_data['present_sessions'] ?? 0;
$attendance_percentage = $total_sessions > 0 ? round(($present_sessions / $total_sessions) * 100, 2) : 0;

// Get points summary
$points_query = "
    SELECT 
        (SELECT COALESCE(SUM(points), 0) FROM appreciations WHERE student_id = ?) as appreciation_points,
        (SELECT COALESCE(SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END), 0) FROM student_attendance WHERE student_id = ?) as attendance_points
";
$points_stmt = mysqli_prepare($conn, $points_query);
mysqli_stmt_bind_param($points_stmt, "ss", $student_id, $student_id);
mysqli_stmt_execute($points_stmt);
$points_result = mysqli_stmt_get_result($points_stmt);
$points_data = mysqli_fetch_assoc($points_result);

$total_points = ($points_data['appreciation_points'] ?? 0) + ($points_data['attendance_points'] ?? 0);

// Parse JSON fields
$skills = [];
if (!empty($student_data['skills'])) {
    $skills = json_decode($student_data['skills'], true) ?: [];
}

$social_links = [];
if (!empty($student_data['social_links'])) {
    $social_links = json_decode($student_data['social_links'], true) ?: [];
}

$projects = [];
if (!empty($student_data['projects'])) {
    $projects = json_decode($student_data['projects'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title><?php echo htmlspecialchars($student_name); ?> - Student Profile</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-user-graduate"></i> Student Profile</h2>
                    <p><?php echo htmlspecialchars($student_name); ?> - <?php echo htmlspecialchars($student_year . '/4 ' . $student_branch . '-' . $student_section); ?></p>
                </div>
                <div>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="row">
                <!-- Student Information Card -->
                <div class="col-md-4 mb-4">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-user-graduate"></i> Student Information
                            </h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <!-- Profile Picture -->
                            <div class="profile-picture-container mb-4">
                                <?php if (!empty($student_data['profile_picture']) && file_exists($student_data['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($student_data['profile_picture']); ?>" 
                                         alt="Profile Picture" 
                                         class="rounded-circle" 
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary-blue);">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 150px; height: 150px; border: 4px solid var(--primary-blue);">
                                        <i class="fas fa-user" style="font-size: 4rem; color: var(--gray-medium);"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h4 class="student-name" style="color: var(--primary-blue); font-weight: 600; margin-bottom: 10px;">
                                <?php echo htmlspecialchars($student_name); ?>
                            </h4>
                            <p class="student-id text-muted mb-3" style="font-size: 1rem;">
                                <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($student_id); ?>
                            </p>
                            
                            <div class="student-info">
                                <div class="info-item mb-3">
                                    <i class="fas fa-graduation-cap text-primary"></i>
                                    <span><strong>Class:</strong> <?php echo htmlspecialchars($student_year . '/4 ' . $student_branch . '-' . $student_section); ?></span>
                                </div>
                                
                                <?php if ($house_name): ?>
                                <div class="info-item mb-3">
                                    <i class="fas fa-home text-info"></i>
                                    <span><strong>House:</strong> <?php echo htmlspecialchars($house_name); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($student_data['cgpa']): ?>
                                <div class="info-item mb-3">
                                    <i class="fas fa-chart-line text-success"></i>
                                    <span><strong>CGPA:</strong> <?php echo htmlspecialchars($student_data['cgpa']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($student_data['blood_group']): ?>
                                <div class="info-item mb-3">
                                    <i class="fas fa-tint text-danger"></i>
                                    <span><strong>Blood Group:</strong> <?php echo htmlspecialchars($student_data['blood_group']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Summary -->
                <div class="col-md-4 mb-4">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-chart-pie"></i> Attendance Summary
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h2 class="text-primary"><?php echo $attendance_percentage; ?>%</h2>
                                <p class="text-muted">Current Month Attendance</p>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-success"><?php echo $present_sessions; ?></h5>
                                    <small class="text-muted">Present</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-danger"><?php echo $total_sessions - $present_sessions; ?></h5>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $attendance_percentage; ?>%" 
                                     aria-valuenow="<?php echo $attendance_percentage; ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Points Summary -->
                <div class="col-md-4 mb-4">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-star"></i> Points Summary
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h2 class="text-primary"><?php echo $total_points; ?></h2>
                                <p class="text-muted">Total Points</p>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h4 class="text-success"><?php echo $points_data['attendance_points'] ?? 0; ?></h4>
                                        <small class="text-muted">Attendance Points</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h4 class="text-info"><?php echo $points_data['appreciation_points'] ?? 0; ?></h4>
                                        <small class="text-muted">Appreciation Points</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <?php if (!empty($social_links)): ?>
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-link"></i> Social Links
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <?php foreach ($social_links as $link): ?>
                            <?php if (!empty(trim($link))): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <a href="<?php echo htmlspecialchars(trim($link)); ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary w-100"
                                       style="border-radius: 10px; padding: 12px;">
                                        <i class="fas fa-external-link-alt"></i> 
                                        <?php 
                                        $domain = parse_url(trim($link), PHP_URL_HOST);
                                        echo htmlspecialchars($domain ?: 'Link');
                                        ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Projects -->
            <?php if (!empty($projects)): ?>
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-project-diagram"></i> Projects
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <?php foreach ($projects as $project): ?>
                            <?php if (!empty(trim($project))): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="project-item p-3 bg-light rounded" style="border-radius: 10px;">
                                        <h6 class="mb-2" style="color: var(--primary-blue);">
                                            <i class="fas fa-code"></i> <?php echo htmlspecialchars(trim($project)); ?>
                                        </h6>
                                        <?php if (filter_var(trim($project), FILTER_VALIDATE_URL)): ?>
                                            <a href="<?php echo htmlspecialchars(trim($project)); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-external-link-alt"></i> View Project
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Skills -->
            <?php if (!empty($skills)): ?>
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-cogs"></i> Skills
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="skills-container">
                        <?php foreach ($skills as $skill): ?>
                            <?php if (!empty(trim($skill))): ?>
                                <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem; padding: 8px 12px; border-radius: 20px;">
                                    <i class="fas fa-code"></i> <?php echo htmlspecialchars(trim($skill)); ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Profile Summary -->
            <?php if (!empty($student_data['summary'])): ?>
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-file-alt"></i> About
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0" style="line-height: 1.6; font-size: 1rem;">
                        <?php echo nl2br(htmlspecialchars($student_data['summary'])); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <style>
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .info-item i {
            width: 25px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .project-item {
            transition: all 0.3s ease;
        }
        
        .project-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(7,101,147,0.15);
        }
        
        .skills-container {
            line-height: 2;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 20px 15px;
            }
            
            .profile-picture-container img,
            .profile-picture-container div {
                width: 120px !important;
                height: 120px !important;
            }
            
            .profile-picture-container i {
                font-size: 3rem !important;
            }
            
            .student-name {
                font-size: 1.3rem;
            }
            
            .info-item {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 15px 10px;
            }
            
            .profile-picture-container img,
            .profile-picture-container div {
                width: 100px !important;
                height: 100px !important;
            }
            
            .profile-picture-container i {
                font-size: 2.5rem !important;
            }
            
            .student-name {
                font-size: 1.2rem;
            }
        }
    </style>
</body>
</html>
