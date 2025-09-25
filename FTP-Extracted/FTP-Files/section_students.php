<?php
session_start();
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    header('Location: sections_overview.php');
    exit();
}

// Get class information
$class_query = "SELECT * FROM classes WHERE class_id = ?";
$stmt = mysqli_prepare($conn, $class_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$class_result = mysqli_stmt_get_result($stmt);
$class_data = mysqli_fetch_assoc($class_result);

if (!$class_data) {
    header('Location: sections_overview.php');
    exit();
}

$section_name = $class_data['year'] . '/4 ' . strtoupper($class_data['branch']) . '-' . strtoupper($class_data['section']);

// Get students in this class
$students_query = "
    SELECT s.*, h.name as house_name,
           sp.parent_number, sp.address, sp.blood_group, sp.dob,
           spr.summary, spr.skills, spr.social_links, spr.projects, spr.cgpa
    FROM students s
    LEFT JOIN houses h ON s.hid = h.hid
    LEFT JOIN student_personal sp ON s.student_id = sp.student_id
    LEFT JOIN student_profile spr ON s.student_id = spr.student_id
    WHERE s.class_id = ?
    ORDER BY s.name ASC
";
$stmt = mysqli_prepare($conn, $students_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

$students = [];
while ($student = mysqli_fetch_assoc($students_result)) {
    $students[] = $student;
}

$student_count = count($students);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title><?php echo htmlspecialchars($section_name); ?> - Students</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-users"></i> <?php echo htmlspecialchars($section_name); ?></h2>
                    <p><?php echo $student_count; ?> students in this section</p>
                </div>
                <div>
                    <a href="sections_overview.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Sections
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <!-- Section Info Card -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-info-circle"></i> Section Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-graduation-cap" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 10px;"></i>
                                <h4 class="text-primary"><?php echo $class_data['year']; ?>/4</h4>
                                <p class="text-muted mb-0">Year</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-code-branch" style="font-size: 2rem; color: var(--success); margin-bottom: 10px;"></i>
                                <h4 class="text-success"><?php echo strtoupper($class_data['branch']); ?></h4>
                                <p class="text-muted mb-0">Branch</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-layer-group" style="font-size: 2rem; color: var(--info); margin-bottom: 10px;"></i>
                                <h4 class="text-info"><?php echo strtoupper($class_data['section']); ?></h4>
                                <p class="text-muted mb-0">Section</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt" style="font-size: 2rem; color: var(--warning); margin-bottom: 10px;"></i>
                                <h4 class="text-warning"><?php echo $class_data['semester']; ?></h4>
                                <p class="text-muted mb-0">Semester</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students Grid -->
            <?php if (!empty($students)): ?>
                <div class="row">
                    <?php foreach ($students as $student): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card student-card h-100" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px; transition: all 0.3s ease; cursor: pointer;" 
                                 onclick="window.location.href='student_profile.php?student_id=<?php echo urlencode($student['student_id']); ?>'">
                                <div class="card-body p-4 text-center">
                                    <!-- Profile Picture -->
                                    <div class="profile-picture-container mb-3">
                                        <?php if (!empty($student['profile_picture']) && file_exists($student['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" 
                                                 alt="Profile Picture" 
                                                 class="rounded-circle" 
                                                 style="width: 80px; height: 80px; object-fit: cover; border: 3px solid var(--primary-blue);">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                                 style="width: 80px; height: 80px; border: 3px solid var(--primary-blue);">
                                                <i class="fas fa-user" style="font-size: 2rem; color: var(--gray-medium);"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Student Info -->
                                    <h6 class="student-name" style="color: var(--primary-blue); font-weight: 600; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </h6>
                                    <p class="student-id text-muted mb-2" style="font-size: 0.9rem;">
                                        <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($student['student_id']); ?>
                                    </p>
                                    
                                    <!-- Additional Info -->
                                    <div class="student-details">
                                        <?php if ($student['house_name']): ?>
                                            <span class="badge bg-info mb-2" style="border-radius: 15px;">
                                                <i class="fas fa-home"></i> <?php echo htmlspecialchars($student['house_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($student['cgpa']): ?>
                                            <div class="cgpa-info">
                                                <small class="text-muted">
                                                    <i class="fas fa-chart-line"></i> CGPA: <?php echo htmlspecialchars($student['cgpa']); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $skills = [];
                                        if (!empty($student['skills'])) {
                                            $skills = json_decode($student['skills'], true);
                                        }
                                        if (!empty($skills) && is_array($skills) && count($skills) > 0): 
                                        ?>
                                            <div class="skills-preview mt-2">
                                                <?php foreach (array_slice($skills, 0, 2) as $skill): ?>
                                                    <span class="badge bg-light text-dark me-1 mb-1" style="font-size: 0.7rem;">
                                                        <?php echo htmlspecialchars(trim($skill)); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($skills) > 2): ?>
                                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                                        +<?php echo count($skills) - 2; ?> more
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- View Profile Button -->
                                    <div class="mt-3">
                                        <span class="badge bg-primary" style="border-radius: 20px; padding: 6px 12px;">
                                            <i class="fas fa-eye"></i> View Profile
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 20px;"></i>
                    <h4 class="text-muted">No students found</h4>
                    <p class="text-muted">This section doesn't have any students yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <style>
        .student-card {
            transition: all 0.3s ease;
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(7,101,147,0.2) !important;
        }
        
        .info-item {
            padding: 15px;
        }
        
        .info-item h4 {
            margin: 10px 0;
            font-weight: 600;
        }
        
        .student-name {
            font-size: 1.1rem;
            line-height: 1.3;
        }
        
        .student-details {
            min-height: 60px;
        }
        
        .skills-preview {
            max-height: 40px;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .student-card {
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 20px 15px;
            }
            
            .profile-picture-container img,
            .profile-picture-container div {
                width: 70px !important;
                height: 70px !important;
            }
            
            .profile-picture-container i {
                font-size: 1.5rem !important;
            }
            
            .student-name {
                font-size: 1rem;
            }
            
            .student-id {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 15px 10px;
            }
            
            .profile-picture-container img,
            .profile-picture-container div {
                width: 60px !important;
                height: 60px !important;
            }
            
            .profile-picture-container i {
                font-size: 1.3rem !important;
            }
        }
    </style>
</body>
</html>
