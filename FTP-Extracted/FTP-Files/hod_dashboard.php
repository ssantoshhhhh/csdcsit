<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';

$hod_username = $_SESSION['hod_username'] ?? 'HOD';

// Include database helper
include './db_migration_helper.php';

// Handle student search
$search_results = [];
$search_query = '';
$search_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_students'])) {
    $search_query = trim($_POST['search_query'] ?? '');
    $search_type = $_POST['search_type'] ?? 'name';
    
    if (!empty($search_query)) {
        $search_sql = "
            SELECT s.*, c.year, c.branch, c.section, c.semester,
                   h.name as house_name,
                   sp.parent_number, sp.address, sp.blood_group, sp.dob,
                   spr.summary, spr.skills, spr.social_links, spr.projects, spr.cgpa
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN houses h ON s.hid = h.hid
            LEFT JOIN student_personal sp ON s.student_id = sp.student_id
            LEFT JOIN student_profile spr ON s.student_id = spr.student_id
            WHERE 1=1
        ";
        
        switch ($search_type) {
            case 'name':
                $search_sql .= " AND s.name LIKE ?";
                $search_param = "%$search_query%";
                break;
            case 'student_id':
                $search_sql .= " AND s.student_id LIKE ?";
                $search_param = "%$search_query%";
                break;
            case 'skills':
                $search_sql .= " AND spr.skills LIKE ?";
                $search_param = "%$search_query%";
                break;
            case 'blood_group':
                $search_sql .= " AND sp.blood_group = ?";
                $search_param = $search_query;
                break;
            case 'email':
                $search_sql .= " AND s.email LIKE ?";
                $search_param = "%$search_query%";
                break;
            default:
                $search_sql .= " AND (s.name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)";
                $search_param = "%$search_query%";
        }
        
        $search_sql .= " ORDER BY s.name ASC";
        
        $stmt = mysqli_prepare($conn, $search_sql);
        if ($search_type === 'name' || $search_type === 'student_id' || $search_type === 'skills' || $search_type === 'email') {
            mysqli_stmt_bind_param($stmt, "s", $search_param);
        } elseif ($search_type === 'blood_group') {
            mysqli_stmt_bind_param($stmt, "s", $search_param);
        } else {
            mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
        }
        
        mysqli_stmt_execute($stmt);
        $search_result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($search_result)) {
            $search_results[] = $row;
        }
    }
}

// Get students by section with attendance points and skills
$sections_query = "
    SELECT 
        c.class_id,
        c.year,
        c.branch,
        c.section,
        s.student_id,
        s.name,
        s.email,
        spr.skills,
        COUNT(DISTINCT sa.attendance_date) as total_days,
        SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as attendance_points
    FROM classes c
    LEFT JOIN students s ON s.class_id = c.class_id
    LEFT JOIN student_profile spr ON s.student_id = spr.student_id
    LEFT JOIN student_attendance sa ON s.student_id = sa.student_id
    GROUP BY c.class_id, s.student_id
    ORDER BY c.year DESC, c.branch, c.section, s.name
";

$sections_result = mysqli_query($conn, $sections_query);
$sections_data = [];

while ($row = mysqli_fetch_assoc($sections_result)) {
    $section_key = $row['year'] . '_' . $row['branch'] . '_' . $row['section'];
    if (!isset($sections_data[$section_key])) {
        $sections_data[$section_key] = [
            'year' => $row['year'],
            'branch' => $row['branch'],
            'section' => $row['section'],
            'students' => []
        ];
    }
    $reg_no = str_replace('@srkrec.edu.in', '', $row['email']);
    $sections_data[$section_key]['students'][] = [
        'student_id' => $row['student_id'],
        'name' => $row['name'],
        'reg_no' => $reg_no,
        'skills' => $row['skills'] ?? '',
        'attendance_points' => $row['attendance_points']
    ];
}

// Get total counts for statistics
$total_students = 0;
$total_sections = count($sections_data);
$total_faculty = count($db_helper->getFacultyMembers());
$today_attendance = 0;

foreach ($sections_data as $section) {
    $total_students += count($section['students']);
    // Calculate today's attendance
    $today = date('Y-m-d');
    foreach ($section['students'] as $student) {
        $today_query = "SELECT COUNT(*) as today_count FROM student_attendance 
                        WHERE student_id = ? AND attendance_date = ?";
        $stmt = mysqli_prepare($conn, $today_query);
        mysqli_stmt_bind_param($stmt, "is", $student['student_id'], $today);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $today_row = mysqli_fetch_assoc($result);
        $today_attendance += $today_row['today_count'];
    }
}

// Get recent attendance modifications
$recent_modifications = [];
if (mysqli_query($conn, "SHOW TABLES LIKE 'attendance_modifications'") && mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'attendance_modifications'")) > 0) {
    $modifications_query = "SELECT * FROM attendance_modifications ORDER BY modified_at DESC LIMIT 10";
    $modifications_result = mysqli_query($conn, $modifications_query);
    if ($modifications_result) {
        while ($row = mysqli_fetch_assoc($modifications_result)) {
            $recent_modifications[] = $row;
        }
    }
}

// Get total modifications count
$total_modifications = 0;
if (mysqli_query($conn, "SHOW TABLES LIKE 'attendance_modifications'") && mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'attendance_modifications'")) > 0) {
    $total_modifications_query = "SELECT COUNT(*) as count FROM attendance_modifications";
    $total_modifications_result = mysqli_query($conn, $total_modifications_query);
    if ($total_modifications_result) {
        $total_modifications = mysqli_fetch_assoc($total_modifications_result)['count'];
    }
}

// Get leave applications statistics
$leave_stats_query = "SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
FROM leave_applications";
$leave_stats_result = mysqli_query($conn, $leave_stats_query);
$leave_stats = ['total_applications' => 0, 'pending_count' => 0];
if ($leave_stats_result) {
    $leave_stats = mysqli_fetch_assoc($leave_stats_result);
}

// Handle assign faculty to class
$success = '';
$error = '';

if (isset($_POST['assign_faculty'])) {
    $faculty_id = (int)$_POST['faculty_id'];
    $class_id = $_POST['class_id'] === 'NULL' ? NULL : (int)$_POST['class_id'];

    // Check if faculty exists
    $check_faculty_query = "SELECT * FROM faculties WHERE faculty_id = ?";
    $stmt = mysqli_prepare($conn, $check_faculty_query);
    mysqli_stmt_bind_param($stmt, "i", $faculty_id);
    mysqli_stmt_execute($stmt);
    $check_faculty_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_faculty_result) > 0) {
        $update_query = "UPDATE faculties SET class_id = ? WHERE faculty_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $class_id, $faculty_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Faculty assigned to class successfully!';
        } else {
            $error = 'Error assigning faculty: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Faculty not found.';
    }
}

// Handle update faculty email
if (isset($_POST['update_email'])) {
    $faculty_id = (int)$_POST['faculty_id'];
    $email = mysqli_real_escape_string($conn, $_POST['faculty_email']);

    // Check if faculty exists
    $check_faculty_query = "SELECT * FROM faculties WHERE faculty_id = ?";
    $stmt = mysqli_prepare($conn, $check_faculty_query);
    mysqli_stmt_bind_param($stmt, "i", $faculty_id);
    mysqli_stmt_execute($stmt);
    $check_faculty_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_faculty_result) > 0) {
        $update_query = "UPDATE faculties SET email = ? WHERE faculty_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $email, $faculty_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Faculty email updated successfully!';
        } else {
            $error = 'Error updating faculty email: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Faculty not found.';
    }
}

// Fetch all classes for dropdown
$classes_query = "SELECT class_id, academic_year, year, branch, section FROM classes ORDER BY academic_year DESC, year ASC, branch ASC, section ASC";
$classes_result = mysqli_query($conn, $classes_query);
$classes = [];
while ($row = mysqli_fetch_assoc($classes_result)) {
    $classes[] = $row;
}

// Fetch all faculty members with their assigned classes
$all_faculty_query = "
    SELECT 
        f.faculty_id, 
        f.faculty_name, 
        f.email, 
        c.academic_year, 
        c.year, 
        c.branch, 
        c.section
    FROM faculties f
    LEFT JOIN classes c ON f.class_id = c.class_id
    ORDER BY f.faculty_name ASC
";
$all_faculty_result = mysqli_query($conn, $all_faculty_query);
$all_faculty = [];
while ($row = mysqli_fetch_assoc($all_faculty_result)) {
    $all_faculty[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>HOD Dashboard - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-tachometer-alt"></i> HOD Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($hod_username); ?>! Monitor attendance across all sections</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-search"></i> Search Students
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="search_type" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                    <i class="fas fa-filter"></i> Search By
                                </label>
                                <select name="search_type" id="search_type" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                    <option value="name" <?php echo $search_type === 'name' ? 'selected' : ''; ?>>Name</option>
                                    <option value="student_id" <?php echo $search_type === 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                                    <option value="skills" <?php echo $search_type === 'skills' ? 'selected' : ''; ?>>Skills</option>
                                    <option value="blood_group" <?php echo $search_type === 'blood_group' ? 'selected' : ''; ?>>Blood Group</option>
                                    <option value="email" <?php echo $search_type === 'email' ? 'selected' : ''; ?>>Email</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="search_query" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                    <i class="fas fa-search"></i> Search Query
                                </label>
                                <input type="text" name="search_query" id="search_query" class="form-control" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Enter search term..." 
                                       style="border-radius: 10px; padding: 10px 15px;">
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" name="search_students" class="btn btn-primary w-100" style="border-radius: 10px; padding: 10px;">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (!empty($search_results)): ?>
                        <div class="mt-4">
                            <h6 class="mb-3" style="color: var(--primary-blue);">
                                <i class="fas fa-list"></i> Search Results (<?php echo count($search_results); ?> found)
                            </h6>
                            <div class="row">
                                <?php foreach ($search_results as $student): ?>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                                        <div class="card student-search-card" style="border: none; box-shadow: 0 2px 8px rgba(7,101,147,0.1); border-radius: 10px; transition: all 0.3s ease; cursor: pointer;" 
                                             onclick="window.location.href='student_profile.php?student_id=<?php echo urlencode($student['student_id']); ?>'">
                                            <div class="card-body p-3 text-center">
                                                <!-- Profile Picture -->
                                                <div class="profile-picture-container mb-2">
                                                    <?php if (!empty($student['profile_picture']) && file_exists($student['profile_picture'])): ?>
                                                        <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" 
                                                             alt="Profile Picture" 
                                                             class="rounded-circle" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border: 2px solid var(--primary-blue);">
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                                             style="width: 60px; height: 60px; border: 2px solid var(--primary-blue);">
                                                            <i class="fas fa-user" style="font-size: 1.5rem; color: var(--gray-medium);"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <h6 class="student-name mb-1" style="color: var(--primary-blue); font-weight: 600; font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                </h6>
                                                <p class="student-id text-muted mb-2" style="font-size: 0.8rem;">
                                                    <?php echo htmlspecialchars($student['student_id']); ?>
                                                </p>
                                                
                                                <div class="student-details">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-graduation-cap"></i> 
                                                        <?php echo htmlspecialchars($student['year'] . '/4 ' . $student['branch'] . '-' . $student['section']); ?>
                                                    </small>
                                                    
                                                    <?php if ($student['blood_group']): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-tint"></i> <?php echo htmlspecialchars($student['blood_group']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($student['cgpa']): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-chart-line"></i> CGPA: <?php echo htmlspecialchars($student['cgpa']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <span class="badge bg-primary" style="font-size: 0.7rem; border-radius: 15px;">
                                                        <i class="fas fa-eye"></i> View Profile
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_students']) && !empty($search_query)): ?>
                        <div class="mt-4 text-center">
                            <i class="fas fa-search" style="font-size: 3rem; color: var(--gray-medium); margin-bottom: 15px;"></i>
                            <h5 class="text-muted">No students found</h5>
                            <p class="text-muted">Try adjusting your search criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    .main-content {
        padding: 20px 0;
    }
    
    .student-search-card {
        transition: all 0.3s ease;
    }
    
    .student-search-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(7,101,147,0.2) !important;
    }
    
    .form-control:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(7,101,147,0.25);
    }
    
    .btn-primary {
        background-color: var(--primary-blue);
        border-color: var(--primary-blue);
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(7,101,147,0.3);
    }

    .section-nav {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .section-btn {
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 10px;
    }

    .section-btn i {
        font-size: 24px;
    }

    .section-btn.active {
        background-color: var(--primary-blue);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(7,101,147,0.2);
    }

    .skill-filters {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #e3e6f0;
    }

    .skill-filter-btn {
        margin: 5px;
    }

    .controls-section {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .student-table {
        margin-top: 20px;
    }

    .student-row td {
        vertical-align: middle;
    }
    </style>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Logout Button -->
            <div class="text-end mb-4">
                <a href="hod_logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-users" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $total_students; ?></h4>
                            <p class="text-muted mb-0">Total Students</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-graduation-cap" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $total_sections; ?></h4>
                            <p class="text-muted mb-0">Total Sections</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-user-tie" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $total_faculty; ?></h4>
                            <p class="text-muted mb-0">Faculty Members</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-edit" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $total_modifications; ?></h4>
                            <p class="text-muted mb-0">Attendance Modifications</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-file-alt" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $leave_stats['total_applications']; ?></h4>
                            <p class="text-muted mb-0">Leave Applications</p>
                            <?php if ($leave_stats['pending_count'] > 0): ?>
                                <small class="text-warning">
                                    <i class="fas fa-clock"></i> <?php echo $leave_stats['pending_count']; ?> pending
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculty Management Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-user-plus"></i> Assign Faculty to Class
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="faculty_id_assign" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-user-tie"></i> Select Faculty
                                    </label>
                                    <select name="faculty_id" id="faculty_id_assign" class="form-control" required style="border-radius: 8px;">
                                        <option value="">Select Faculty</option>
                                        <?php foreach ($all_faculty as $f): ?>
                                            <option value="<?php echo $f['faculty_id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="class_id_assign" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-graduation-cap"></i> Assign Class
                                    </label>
                                    <select name="class_id" id="class_id_assign" class="form-control" style="border-radius: 8px;">
                                        <option value="NULL">Unassign Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['class_id']; ?>">
                                                <?php echo htmlspecialchars($class['academic_year'] . ' ' . $class['year'] . '/4 ' . $class['branch'] . '-' . $class['section']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_faculty" class="btn btn-primary" style="border-radius: 8px;">
                                    <i class="fas fa-check"></i> Assign Class
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-envelope"></i> Update Faculty Email
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="faculty_id_email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-user-tie"></i> Select Faculty
                                    </label>
                                    <select name="faculty_id" id="faculty_id_email" class="form-control" required style="border-radius: 8px;">
                                        <option value="">Select Faculty</option>
                                        <?php foreach ($all_faculty as $f): ?>
                                            <option value="<?php echo $f['faculty_id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="faculty_email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-envelope"></i> New Email
                                    </label>
                                    <input type="email" name="faculty_email" id="faculty_email" class="form-control" placeholder="Faculty Email" required style="border-radius: 8px;">
                                </div>
                                <button type="submit" name="update_email" class="btn btn-warning" style="border-radius: 8px;">
                                    <i class="fas fa-edit"></i> Update Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Faculty Members with Assigned Classes -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-users-cog"></i> All Faculty Members and Their Assignments
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background: var(--light-blue);">
                                <tr>
                                    <th style="color: var(--primary-blue); font-weight: 600;">ID</th>
                                    <th style="color: var(--primary-blue); font-weight: 600;">Name</th>
                                    <th style="color: var(--primary-blue); font-weight: 600;">Email</th>
                                    <th style="color: var(--primary-blue); font-weight: 600;">Assigned Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($all_faculty)): ?>
                                    <?php foreach ($all_faculty as $f): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($f['faculty_id']); ?></td>
                                            <td><?php echo htmlspecialchars($f['faculty_name']); ?></td>
                                            <td><?php echo htmlspecialchars($f['email']); ?></td>
                                            <td>
                                                <?php 
                                                if ($f['academic_year']) {
                                                    echo '<span class="badge bg-success">' . htmlspecialchars($f['academic_year'] . ' ' . $f['year'] . '/4 ' . $f['branch'] . '-' . $f['section']) . '</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">Not Assigned</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No faculty members found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Section-wise Students -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-users"></i> Section-wise Students Performance
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Section Navigation -->
                            <div class="section-nav mb-4">
                                <div class="row">
                                    <?php foreach ($sections_data as $section_key => $section): ?>
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <button class="btn btn-outline-primary w-100 section-btn" 
                                                    data-section="<?php echo $section_key; ?>"
                                                    style="border-radius: 10px; padding: 15px;">
                                                <i class="fas fa-graduation-cap"></i>
                                                <?php echo "{$section['year']}/4 {$section['branch']}-{$section['section']}"; ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Search and Filter Controls -->
                            <div class="controls-section mb-4" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text" style="background: var(--light-blue); border: none;">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" id="studentSearch" class="form-control" placeholder="Search by name or registration number..." 
                                                   style="border-radius: 0 10px 10px 0; padding: 12px;">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="skill-filters" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <?php
                                            // Extract unique skills across all students
                                            $all_skills = [];
                                            foreach ($sections_data as $section) {
                                                foreach ($section['students'] as $student) {
                                                    $skills_arr = json_decode($student['skills'], true);
                                                    if (is_array($skills_arr)) {
                                                        foreach ($skills_arr as $skill) {
                                                            $skill = trim($skill);
                                                            if (!empty($skill) && !in_array($skill, $all_skills)) {
                                                                $all_skills[] = $skill;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            sort($all_skills); // Sort skills alphabetically
                                            ?>
                                            <?php foreach ($all_skills as $skill): ?>
                                                <button class="btn btn-outline-info skill-filter-btn" data-skill="<?php echo htmlspecialchars($skill); ?>">
                                                    <?php echo htmlspecialchars($skill); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Students Table Container -->
                            <?php foreach ($sections_data as $section_key => $section): ?>
                                <div class="section-content" id="section_<?php echo $section_key; ?>" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-hover student-table">
                                            <thead style="background: var(--light-blue);">
                                                <tr>
                                                    <th style="color: var(--primary-blue);">Reg No</th>
                                                    <th style="color: var(--primary-blue);">Name</th>
                                                    <th style="color: var(--primary-blue);">
                                                        Attendance Points
                                                        <button class="btn btn-sm btn-link sort-btn" data-sort="points">
                                                            <i class="fas fa-sort"></i>
                                                        </button>
                                                    </th>
                                                    <th style="color: var(--primary-blue);">Skills</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($section['students'] as $student): ?>
                                                    <tr class="student-row" 
                                                        data-name="<?php echo strtolower(htmlspecialchars($student['name'])); ?>"
                                                        data-regno="<?php echo strtolower(htmlspecialchars($student['reg_no'])); ?>"
                                                        data-skills="<?php $skills_arr = json_decode($student['skills'], true); if (is_array($skills_arr)) { echo strtolower(htmlspecialchars(implode(',', $skills_arr))); } ?>">
                                                        <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                        <td data-points="<?php echo $student['attendance_points']; ?>">
                                                            <span class="badge bg-primary">
                                                                <?php echo $student['attendance_points']; ?> pts
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $skills_arr = json_decode($student['skills'], true);
                                                            if (is_array($skills_arr)) {
                                                                foreach ($skills_arr as $skill) {
                                                                    echo '<span class="badge bg-info me-1">' . htmlspecialchars(trim($skill)) . '</span>';
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-cogs"></i> Quick Actions
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-primary w-100" style="border-radius: 10px; padding: 15px;" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                                        <i class="fas fa-users"></i><br>
                                        View Attendance
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-warning w-100" style="border-radius: 10px; padding: 15px;" data-bs-toggle="modal" data-bs-target="#leaderboardModal">
                                        <i class="fas fa-trophy"></i><br>
                                        Leaderboard
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-success w-100" style="border-radius: 10px; padding: 15px;" data-bs-toggle="modal" data-bs-target="#exportModal">
                                        <i class="fas fa-file-excel"></i><br>
                                        Export Excel
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="hod_leave_management.php" class="btn btn-info w-100" style="border-radius: 10px; padding: 15px;">
                                        <i class="fas fa-file-alt"></i><br>
                                        Leave Applications
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="attendance_modifications.php" class="btn btn-warning w-100" style="border-radius: 10px; padding: 15px;">
                                        <i class="fas fa-history"></i><br>
                                        View Modifications
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="index.php" class="btn btn-info w-100" style="border-radius: 10px; padding: 15px;">
                                        <i class="fas fa-home"></i><br>
                                        Home Page
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Modifications -->
            <?php if (!empty($recent_modifications)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-history"></i> Recent Attendance Modifications
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover">
                                    <thead style="background: var(--light-blue);">
                                        <tr>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Section</th>
                                            <th style="color: var (--primary-blue); font-weight: 600;">Date</th>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Session</th>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Faculty</th>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Reason</th>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Changes Made</th>
                                            <th style="color: var(--primary-blue); font-weight: 600;">Modified At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_modifications as $mod): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary" style="border-radius: 8px;">
                                                        <?php echo $sections[$mod['table_name']] ?? $mod['table_name']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($mod['attendance_date'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $mod['session'] == 'Forenoon' ? 'bg-warning' : 'bg-info'; ?>" style="border-radius: 8px;">
                                                        <?php echo htmlspecialchars($mod['session']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($mod['faculty_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="text-muted" style="font-size: 0.9rem;">
                                                        <?php echo htmlspecialchars($mod['modification_reason']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($mod['changes_made'])): ?>
                                                        <span class="badge bg-info" style="border-radius: 8px; font-size: 0.7rem;">
                                                            <i class="fas fa-exchange-alt"></i> Changes
                                                        </span>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                                            <?php echo htmlspecialchars($mod['changes_made']); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted" style="font-size: 0.8rem;">
                                                            <i class="fas fa-info-circle"></i> No changes tracked
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> <?php echo date('d M Y H:i', strtotime($mod['modified_at'])); ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Mobile Card View -->
                            <div class="d-md-none">
                                <?php foreach ($recent_modifications as $mod): ?>
                                    <div class="card mb-3" style="border: 1px solid #e3e6f0; border-radius: 10px;">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <span class="badge bg-primary mb-2" style="border-radius: 8px; font-size: 0.7rem;">
                                                        <?php echo $sections[$mod['table_name']] ?? $mod['table_name']; ?>
                                                    </span>
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($mod['attendance_date'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="mb-2">
                                                        <span class="badge <?php echo $mod['session'] == 'Forenoon' ? 'bg-warning' : 'bg-info'; ?>" style="border-radius: 8px; font-size: 0.7rem;">
                                                            <?php echo htmlspecialchars($mod['session']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> <?php echo date('d M Y H:i', strtotime($mod['modified_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <strong style="font-size: 0.8rem; color: var(--primary-blue);">
                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($mod['faculty_name']); ?>
                                                </strong>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    <strong>Reason:</strong> <?php echo htmlspecialchars($mod['modification_reason']); ?>
                                                </small>
                                            </div>
                                            <?php if (!empty($mod['changes_made'])): ?>
                                                <div>
                                                    <span class="badge bg-info" style="border-radius: 8px; font-size: 0.65rem;">
                                                        <i class="fas fa-exchange-alt"></i> Changes
                                                    </span>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                        <?php echo htmlspecialchars($mod['changes_made']); ?>
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        <i class="fas fa-info-circle"></i> No changes tracked
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            
        </div>
    </div>
    
    <!-- Attendance Selection Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 8px 32px rgba(7,101,147,0.15);">
                <div class="modal-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="attendanceModalLabel" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-users"></i> Select Section & Date Range
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="student_attendance.php" id="attendanceForm">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-graduation-cap"></i> Select Section
                                </h6>
                                <div class="row">
                                    <?php
                                    // Use the same sections from the database helper
                                    $modal_sections = $db_helper->getAllClasses();
                                    foreach ($modal_sections as $class_id => $section_name): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="section-option" style="border: 2px solid #e3e6f0; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa;" onclick="selectSection('<?php echo $class_id; ?>', this)">
                                                <div class="text-center">
                                                    <i class="fas fa-users" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 10px;"></i>
                                                    <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 5px;">
                                                        <?php echo htmlspecialchars($section_name); ?>
                                                    </h6>
                                                    <small class="text-muted">Class Section</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-calendar"></i> Select Date Range (Optional)
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> Start Date
                                        </label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to view all records</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> End Date
                                        </label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to view all records</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="table" id="selected_table" value="">
                        <input type="hidden" name="hod_view" value="1">
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="viewAttendanceBtn" disabled>
                                <i class="fas fa-eye"></i> View Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Selection Modal -->
    <div class="modal fade" id="leaderboardModal" tabindex="-1" aria-labelledby="leaderboardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 8px 32px rgba(7,101,147,0.15);">
                <div class="modal-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="leaderboardModalLabel" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-trophy"></i> Select Section & Date Range for Leaderboard
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="attendance_leaderboard.php" id="leaderboardForm">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-graduation-cap"></i> Select Section
                                </h6>
                                <div class="row">
                                    <?php
                                    // Use the same sections from the database helper
                                    $modal_sections = $db_helper->getAllClasses();
                                    foreach ($modal_sections as $class_id => $section_name): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="section-option-leaderboard" style="border: 2px solid #e3e6f0; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa;" onclick="selectSectionLeaderboard('<?php echo $class_id; ?>', this)">
                                                <div class="text-center">
                                                    <i class="fas fa-trophy" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 10px;"></i>
                                                    <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 5px;">
                                                        <?php echo htmlspecialchars($section_name); ?>
                                                    </h6>
                                                    <small class="text-muted">Class Section</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-calendar"></i> Select Date Range (Optional)
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="leaderboard_start_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> Start Date
                                        </label>
                                        <input type="date" name="start_date" id="leaderboard_start_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to view all records</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="leaderboard_end_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> End Date
                                        </label>
                                        <input type="date" name="end_date" id="leaderboard_end_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to view all records</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="table" id="selected_table_leaderboard" value="">
                        <input type="hidden" name="hod_view" value="1">
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-warning" id="viewLeaderboardBtn" disabled>
                                <i class="fas fa-trophy"></i> View Leaderboard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Excel Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 8px 32px rgba(7,101,147,0.15);">
                <div class="modal-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="exportModalLabel" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-file-excel"></i> Export Attendance Data to Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="hod_export_excel.php" id="exportForm">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var (--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-graduation-cap"></i> Select Section
                                </h6>
                                <div class="row">
                                    <?php
                                    // Use the same sections from the database helper
                                    $modal_sections = $db_helper->getAllClasses();
                                    foreach ($modal_sections as $class_id => $section_name): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="section-option-export" style="border: 2px solid #e3e6f0; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa;" onclick="selectSectionExport('<?php echo $class_id; ?>', this)">
                                                <div class="text-center">
                                                    <i class="fas fa-file-excel" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 10px;"></i>
                                                    <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 5px;">
                                                        <?php echo htmlspecialchars($section_name); ?>
                                                    </h6>
                                                    <small class="text-muted">Class Section</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 15px;">
                                    <i class="fas fa-calendar"></i> Select Date Range (Optional)
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="export_start_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> Start Date
                                        </label>
                                        <input type="date" name="start_date" id="export_start_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to export all records</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="export_end_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> End Date
                                        </label>
                                        <input type="date" name="end_date" id="export_end_date" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
                                        <small class="form-text text-muted">Leave empty to export all records</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info" style="border-radius: 10px;">
                            <i class="fas fa-info-circle"></i> <strong>Export Information:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Format:</strong> S.No | Regn No | Faculty Name | Date (with FN/AN sub-headers)</li>
                                <li><strong>Header Structure:</strong> Two rows - dates in row 1, FN/AN in row 2</li>
                                <li><strong>Status Codes:</strong> 1 = Present, 0 = Absent, N/A = No data</li>
                                <li><strong>Session Codes:</strong> FN = Forenoon, AN = Afternoon</li>
                                <li><strong>File Format:</strong> CSV that opens perfectly in Excel</li>
                            </ul>
                        </div>
                        
                        <input type="hidden" name="table" id="selected_table_export" value="">
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success" id="exportExcelBtn" disabled>
                                <i class="fas fa-file-excel"></i> Export to CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <script>
        // Function to select a section in a modal
        function selectSection(modalType, tableName, element) {
            // Remove active class from all sections in the same modal
            document.querySelectorAll(`.section-option-${modalType}`).forEach(option => {
                option.style.borderColor = '#e3e6f0';
                option.style.backgroundColor = '#f8f9fa';
            });

            // Add active class to selected section
            element.style.borderColor = 'var(--primary-blue)';
            element.style.backgroundColor = '#e8f4fd';

            // Set the selected table
            document.getElementById(`selected_table_${modalType}`).value = tableName;

            // Enable the corresponding button
            document.getElementById(`view${modalType.charAt(0).toUpperCase() + modalType.slice(1)}Btn`).disabled = false;
        }

        // Simplified selection functions for each modal
        function selectSectionAttendance(tableName, element) {
            selectSection('attendance', tableName, element);
        }

        function selectSectionLeaderboard(tableName, element) {
            selectSection('leaderboard', tableName, element);
        }

        function selectSectionExport(tableName, element) {
            selectSection('export', tableName, element);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            const formattedToday = today.toISOString().split('T')[0];
            const formattedThirtyDaysAgo = thirtyDaysAgo.toISOString().split('T')[0];

            // Set dates for all modals
            ['', 'leaderboard_', 'export_'].forEach(prefix => {
                const startDateEl = document.getElementById(`${prefix}start_date`);
                const endDateEl = document.getElementById(`${prefix}end_date`);
                if (startDateEl && endDateEl) {
                    startDateEl.value = formattedThirtyDaysAgo;
                    endDateEl.value = formattedToday;
                }
            });

            // Section button click handling
            const sectionButtons = document.querySelectorAll('.section-btn');
            const sectionContents = document.querySelectorAll('.section-content');
            const controlsSection = document.querySelector('.controls-section');

            sectionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const sectionKey = this.getAttribute('data-section');
                    
                    sectionButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    sectionContents.forEach(content => {
                        content.style.display = content.id === `section_${sectionKey}` ? 'block' : 'none';
                    });

                    controlsSection.style.display = 'block';
                    filterStudents();
                });
            });

            // Skill filter handling
            const skillButtons = document.querySelectorAll('.skill-filter-btn');
            skillButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('active');
                    filterStudents();
                });
            });

            // Search handling
            const searchInput = document.getElementById('studentSearch');
            if(searchInput) {
                searchInput.addEventListener('input', filterStudents);
            }

            // Combined filter function
            function filterStudents() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeSkills = Array.from(document.querySelectorAll('.skill-filter-btn.active'))
                    .map(btn => btn.getAttribute('data-skill').toLowerCase());

                const visibleSection = document.querySelector('.section-content[style*="display: block"]');
                if (!visibleSection) return;

                const rows = visibleSection.querySelectorAll('.student-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const regno = row.getAttribute('data-regno') || '';
                    const skills = (row.getAttribute('data-skills') || '').split(',').map(s => s.trim().toLowerCase());

                    const matchesSearch = searchTerm === '' || name.includes(searchTerm) || regno.includes(searchTerm);
                    const matchesSkills = activeSkills.length === 0 || activeSkills.every(skill => skills.includes(skill));

                    row.style.display = (matchesSearch && matchesSkills) ? '' : 'none';
                });
            }

            // Sorting functionality
            document.querySelectorAll('.sort-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const sortType = this.getAttribute('data-sort');
                    const visibleSection = document.querySelector('.section-content[style*="display: block"]');
                    if (!visibleSection) return;

                    const tbody = visibleSection.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    rows.sort((a, b) => {
                        if (sortType === 'points') {
                            const aPoints = parseInt(a.querySelector('td[data-points]').getAttribute('data-points')) || 0;
                            const bPoints = parseInt(b.querySelector('td[data-points]').getAttribute('data-points')) || 0;
                            return this.classList.contains('asc') ? aPoints - bPoints : bPoints - aPoints;
                        }
                        return 0;
                    });

                    this.classList.toggle('asc');
                    const icon = this.querySelector('i');
                    icon.className = this.classList.contains('asc') ? 'fas fa-sort-up' : 'fas fa-sort-down';

                    rows.forEach(row => tbody.appendChild(row));
                });
            });
            
            
        });
    </script>
    <script>
    // Handle form submissions for modals
    document.addEventListener('DOMContentLoaded', function() {
        const attendanceForm = document.getElementById('attendanceForm');
        if(attendanceForm) {
            attendanceForm.addEventListener('submit', function(e) {
                const selectedTable = document.getElementById('selected_table').value;
                if (!selectedTable) {
                    e.preventDefault();
                    alert('Please select a section.');
                }
            });
        }

        const leaderboardForm = document.getElementById('leaderboardForm');
        if(leaderboardForm) {
            leaderboardForm.addEventListener('submit', function(e) {
                const selectedTable = document.getElementById('selected_table_leaderboard').value;
                if (!selectedTable) {
                    e.preventDefault();
                    alert('Please select a section.');
                }
            });
        }

        const exportForm = document.getElementById('exportForm');
        if(exportForm) {
            exportForm.addEventListener('submit', function(e) {
                const selectedTable = document.getElementById('selected_table_export').value;
                if (!selectedTable) {
                    e.preventDefault();
                    alert('Please select a section.');
                }
            });
        }
    });
    </script>
    
    <style>
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(7,101,147,0.15) !important;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        /* Mobile responsive improvements for modification table */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table th,
            .table td {
                padding: 8px 6px;
                font-size: 0.75rem;
                white-space: nowrap;
                min-width: 80px;
            }
            
            .table th:nth-child(4),
            .table td:nth-child(4) {
                min-width: 120px;
            }
            
            .table th:nth-child(5),
            .table td:nth-child(5) {
                min-width: 150px;
                white-space: normal;
                word-wrap: break-word;
            }
            
            .table th:nth-child(6),
            .table td:nth-child(6) {
                min-width: 140px;
                white-space: normal;
                word-wrap: break-word;
            }
            
            .table th:nth-child(7),
            .table td:nth-child(7) {
                min-width: 100px;
            }
            
            .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
            }
            
            .text-muted {
                font-size: 0.7rem;
            }
            
            /* Improve modal responsiveness */
            .modal-dialog {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
            
            .modal-body {
                padding: 15px;
            }
            
            .section-option,
            .section-option-leaderboard,
            .section-option-export {
                padding: 10px !important;
            }
            
            .section-option i,
            .section-option-leaderboard i,
            .section-option-export i {
                font-size: 1.5rem !important;
            }
            
            .section-option h6,
            .section-option-leaderboard h6,
            .section-option-export h6 {
                font-size: 0.9rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.7rem;
            }
            
            .table th,
            .table td {
                padding: 6px 4px;
                font-size: 0.65rem;
                min-width: 70px;
            }
            
            .table th:nth-child(4),
            .table td:nth-child(4) {
                min-width: 100px;
            }
            
            .table th:nth-child(5),
            .table td:nth-child(5) {
                min-width: 120px;
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .table th:nth-child(6),
            .table td:nth-child(6) {
                min-width: 110px;
                max-width: 110px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .table th:nth-child(7),
            .table td:nth-child(7) {
                min-width: 80px;
            }
            
            .badge {
                font-size: 0.6rem;
                padding: 2px 4px;
            }
            
            /* Stack cards in single column on very small screens */
            .col-md-6,
            .col-lg-4 {
                width: 100%;
            }
            
            /* Improve modal on very small screens */
            .modal-dialog {
                margin: 5px;
                max-width: calc(100% - 10px);
            }
            
            .modal-body {
                padding: 10px;
            }
            
            .section-option,
            .section-option-leaderboard,
            .section-option-export {
                padding: 8px !important;
            }
            
            .section-option i,
            .section-option-leaderboard i,
            .section-option-export i {
                font-size: 1.2rem !important;
            }
            
            .section-option h6,
            .section-option-leaderboard h6,
            .section-option-export h6 {
                font-size: 0.8rem !important;
            }
        }
        
        /* Landscape orientation fixes */
        @media (max-width: 768px) and (orientation: landscape) {
            .table-responsive {
                max-height: 60vh;
                overflow-y: auto;
            }
            
            .modal-dialog {
                max-height: 90vh;
                overflow-y: auto;
            }
        }
        
        /* Touch target improvements */
        .table th,
        .table td {
            min-height: 44px;
        }
        
        @media (max-width: 768px) {
            .table th,
            .table td {
                min-height: 40px;
            }
        }
        
        @media (max-width: 576px) {
            .table th,
            .table td {
                min-height: 36px;
            }
        }

        .section-title {
            color: var(--primary-blue);
            font-weight: 600;
            padding: 10px 0;
            border-bottom: 2px solid var(--light-blue);
            margin-bottom: 20px;
        }

        .sort-btn {
            padding: 0;
            color: var(--primary-blue);
        }

        .sort-btn:hover {
            color: var(--secondary-blue);
        }

        .student-table th {
            white-space: nowrap;
        }

        .badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        .section-btn.active {
            background-color: var(--primary-blue);
            color: white;
        }

        .skill-filter-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 15px;
        }

        .skill-filter-btn.active {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .student-row.hidden {
            display: none;
        }
    </style>
</body>
</html>