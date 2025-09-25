<?php
session_start();

// Debug: Log form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST Request received");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Form buttons: " . print_r(array_keys($_POST), true));
    
    // Also write to a debug file
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - POST Request:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);
}

// Check if student is logged in
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    header('Location: login.php');
    exit();
}

include './connect.php';
include './db_migration_helper.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Get student data from database
$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    // Session data is missing, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Get student information first (needed for profile picture handling)
$student_query = "
    SELECT s.*, c.year, c.semester, c.academic_year, c.branch as class_branch, c.section as class_section,
           h.name as house_name,
           sper.personal_number, sper.address, sper.blood_group, sper.dob,
           sp.summary, sp.skills, sp.social_links, sp.cgpa
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN houses h ON s.hid = h.hid
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id
    LEFT JOIN student_personal sper ON s.student_id = sper.student_id
    WHERE s.student_id = ?
";
$stmt = mysqli_prepare($conn, $student_query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);
$student_data = mysqli_fetch_assoc($student_result);

if (!$student_data) {
    // Student not found, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle leave application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $_SESSION['error_message'] = 'Please fill all required fields.';
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['error_message'] = 'End date cannot be before start date.';
    } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error_message'] = 'Start date cannot be in the past.';
    } else {
        // Insert leave application
        $leave_query = "INSERT INTO leave_applications (student_id, leave_type, start_date, end_date, reason, status, applied_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $leave_stmt = mysqli_prepare($conn, $leave_query);
        mysqli_stmt_bind_param($leave_stmt, "sssss", $student_id, $leave_type, $start_date, $end_date, $reason);
        
        if (mysqli_stmt_execute($leave_stmt)) {
            $_SESSION['success_message'] = 'Leave application submitted successfully. You will be notified once it is processed.';
        } else {
            $_SESSION['error_message'] = 'Error submitting leave application. Please try again.';
        }
    }
    
    header('Location: student_dashboard.php');
    exit();
}

// Handle skills update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_skills'])) {
    error_log("Skills update form submitted");
    $skills = array_filter(array_map('trim', explode(',', $_POST['skills'] ?? '')));
    $skills_json = json_encode(array_values($skills));
    
    error_log("Skills to update: " . $skills_json);
    
    // Update or insert skills
    $query = "INSERT INTO student_profile (student_id, skills) 
              VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE skills = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Failed to prepare skills update query: " . mysqli_error($conn));
        $error = "Database error occurred. Please try again.";
    } else {
        mysqli_stmt_bind_param($stmt, "sss", $student_id, $skills_json, $skills_json);
        
        if (mysqli_stmt_execute($stmt)) {
            error_log("Skills updated successfully for student: " . $student_id);
            $success = "Skills updated successfully!";
            $student_data['skills'] = $skills_json;
        } else {
            error_log("Error updating skills: " . mysqli_stmt_error($stmt));
            $error = "Error updating skills: " . mysqli_error($conn);
        }
    }
}

// Handle summary update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_summary'])) {
    error_log("Summary update form handler triggered");
    $summary = trim($_POST['summary'] ?? '');
    
    error_log("Summary to update: " . $summary);
    
    // Update or insert summary
    $query = "INSERT INTO student_profile (student_id, summary) 
              VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE summary = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Failed to prepare summary update query: " . mysqli_error($conn));
        $error = "Database error occurred. Please try again.";
    } else {
        mysqli_stmt_bind_param($stmt, "sss", $student_id, $summary, $summary);
        
        if (mysqli_stmt_execute($stmt)) {
            error_log("Summary updated successfully for student: " . $student_id);
            $success = "Summary updated successfully!";
            $student_data['summary'] = $summary;
        } else {
            error_log("Error updating summary: " . mysqli_stmt_error($stmt));
            $error = "Error updating summary: " . mysqli_error($conn);
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST received but update_summary not set. POST keys: " . implode(', ', array_keys($_POST)));
}

// Handle social links update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_social_links'])) {
    $social_links_input = trim($_POST['social_links'] ?? '');
    $social_links = array_filter(array_map('trim', explode("\n", $social_links_input)));
    $social_links_json = json_encode(array_values($social_links));
    
    // Update or insert social links in student_profile table
    $query = "INSERT INTO student_profile (student_id, social_links) 
              VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE social_links = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $student_id, $social_links_json, $social_links_json);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Social links updated successfully!";
        $student_data['social_links'] = $social_links_json;
    } else {
        $error = "Error updating social links: " . mysqli_error($conn);
    }
}

// Handle personal information update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal'])) {
    $personal_number = $_POST['personal_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $dob = $_POST['dob'] ?? '';
    
    if (empty($dob)) {
        $error = 'Date of birth is required.';
    } else {
        // Check if personal record exists
        $check_query = "SELECT student_id FROM student_personal WHERE student_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $student_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_query = "UPDATE student_personal SET personal_number = ?, address = ?, blood_group = ?, dob = ?, updated_at = NOW() WHERE student_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "sssss", $personal_number, $address, $blood_group, $dob, $student_id);
        } else {
            // Insert new record
            $update_query = "INSERT INTO student_personal (student_id, personal_number, address, blood_group, dob, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "sssss", $student_id, $personal_number, $address, $blood_group, $dob);
        }
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = 'Personal information updated successfully.';
            // Update the student_data array
            $student_data['personal_number'] = $personal_number;
            $student_data['address'] = $address;
            $student_data['blood_group'] = $blood_group;
            $student_data['dob'] = $dob;
        } else {
            $error = 'Error updating personal information: ' . mysqli_error($conn);
        }
    }
}

// Handle academic profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_academic'])) {
    $summary = $_POST['summary'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $cgpa = $_POST['cgpa'] ?? null;
    $social_links = $_POST['social_links'] ?? '';
    $projects = $_POST['projects'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $education = $_POST['education'] ?? '';
    $certifications = $_POST['certifications'] ?? '';
    $achievements = $_POST['achievements'] ?? '';
    
    // Validate CGPA
    if (!empty($cgpa) && ($cgpa < 0 || $cgpa > 10)) {
        $error = 'CGPA must be between 0 and 10.';
    } else {
        // Convert arrays to JSON for storage
        $skills_json = !empty($skills) ? json_encode(array_filter(array_map('trim', explode(',', $skills)))) : null;
        $social_links_json = !empty($social_links) ? json_encode(array_filter(array_map('trim', explode(',', $social_links)))) : null;
        $projects_json = !empty($projects) ? json_encode(array_filter(array_map('trim', explode(',', $projects)))) : null;
        $experience_json = !empty($experience) ? json_encode(array_filter(array_map('trim', explode(',', $experience)))) : null;
        $education_json = !empty($education) ? json_encode(array_filter(array_map('trim', explode(',', $education)))) : null;
        $certifications_json = !empty($certifications) ? json_encode(array_filter(array_map('trim', explode(',', $certifications)))) : null;
        $achievements_json = !empty($achievements) ? json_encode(array_filter(array_map('trim', explode(',', $achievements)))) : null;
        
        // Check if profile record exists
        $check_query = "SELECT student_id FROM student_profile WHERE student_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $student_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_query = "UPDATE student_profile SET summary = ?, skills = ?, social_links = ?, projects = ?, experience = ?, education = ?, certifications = ?, achievements = ?, cgpa = ?, updated_at = NOW() WHERE student_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ssssssssds", $summary, $skills_json, $social_links_json, $projects_json, $experience_json, $education_json, $certifications_json, $achievements_json, $cgpa, $student_id);
        } else {
            // Insert new record
            $update_query = "INSERT INTO student_profile (student_id, summary, skills, social_links, projects, experience, education, certifications, achievements, cgpa, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ssssssssds", $student_id, $summary, $skills_json, $social_links_json, $projects_json, $experience_json, $education_json, $certifications_json, $achievements_json, $cgpa);
        }
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = 'Academic profile updated successfully.';
            // Update the student_data array
            $student_data['summary'] = $summary;
            $student_data['skills'] = $skills_json;
            $student_data['social_links'] = $social_links_json;
            $student_data['projects'] = $projects_json;
            $student_data['experience'] = $experience_json;
            $student_data['education'] = $education_json;
            $student_data['certifications'] = $certifications_json;
            $student_data['achievements'] = $achievements_json;
            $student_data['cgpa'] = $cgpa;
        } else {
            $error = 'Error updating academic profile: ' . mysqli_error($conn);
        }
    }
}

// Handle basic information update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_basic'])) {
    error_log("Basic info update form submitted");
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    error_log("Basic info data - Name: $name, Email: $email");
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else {
        $update_query = "UPDATE students SET name = ?, email = ? WHERE student_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        
        if (!$update_stmt) {
            error_log("Failed to prepare basic info update query: " . mysqli_error($conn));
            $error = "Database error occurred. Please try again.";
        } else {
            mysqli_stmt_bind_param($update_stmt, "sss", $name, $email, $student_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                error_log("Basic info updated successfully for student: " . $student_id);
                $_SESSION['student_name'] = $name;
                $_SESSION['student_email'] = $email;
                $success = 'Basic information updated successfully.';
                // Update the student_data array
                $student_data['name'] = $name;
                $student_data['email'] = $email;
            } else {
                error_log("Error updating basic info: " . mysqli_stmt_error($update_stmt));
                $error = 'Error updating basic information: ' . mysqli_error($conn);
            }
        }
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_profile_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = $student_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if (!empty($student_data['profile_picture']) && file_exists($student_data['profile_picture'])) {
                    unlink($student_data['profile_picture']);
                }
                
                // Update database
                $update_query = "UPDATE students SET profile_picture = ? WHERE student_id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "ss", $upload_path, $student_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $student_data['profile_picture'] = $upload_path;
                    $success = "Profile picture updated successfully!";
                    
                    // Refresh the page to show the new image
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode($success));
                    exit();
                } else {
                    $error = "Error updating profile picture in database.";
                }
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file type. Please upload JPG, JPEG, PNG, or GIF files only.";
        }
    } else {
        $error = "Please select a valid image file.";
    }
}

// Student data already fetched above

$student_name = $student_data['name'];
$student_email = $student_data['email'];
$student_branch = $student_data['class_branch'] ?? $student_data['branch'];
$student_section = $student_data['class_section'] ?? $student_data['section'];
$student_year = $student_data['year'];
$student_semester = $student_data['semester'];
$house_name = $student_data['house_name'];

// Initialize social links for display
$social_links = [];
if (!empty($student_data['social_links'])) {
    $social_links = json_decode($student_data['social_links'], true);
    if (!is_array($social_links)) {
        $social_links = [];
    }
}

// Get student's leave applications
$leave_applications = [];
$leave_query = "SELECT * FROM leave_applications WHERE student_id = ? ORDER BY applied_at DESC";
$leave_stmt = mysqli_prepare($conn, $leave_query);
mysqli_stmt_bind_param($leave_stmt, "s", $student_id);
mysqli_stmt_execute($leave_stmt);
$leave_result = mysqli_stmt_get_result($leave_stmt);

while ($leave_row = mysqli_fetch_assoc($leave_result)) {
    // Calculate total days
    $start = new DateTime($leave_row['start_date']);
    $end = new DateTime($leave_row['end_date']);
    $leave_row['total_days'] = $start->diff($end)->days + 1;
    $leave_applications[] = $leave_row;
}

// Get pagination parameters for attendance
$attendance_page = isset($_GET['attendance_page']) ? max(1, intval($_GET['attendance_page'])) : 1;
$records_per_page = 10; // Show latest 10 days
$offset = ($attendance_page - 1) * $records_per_page;

// Get student's recent attendance with pagination
$attendance_summary = [];

// First, get total number of unique dates for this student
$date_count_query = "
    SELECT COUNT(DISTINCT attendance_date) as total_dates 
    FROM student_attendance 
    WHERE student_id = ?
";
$date_count_stmt = mysqli_prepare($conn, $date_count_query);
mysqli_stmt_bind_param($date_count_stmt, "s", $student_id);
mysqli_stmt_execute($date_count_stmt);
$date_count_result = mysqli_stmt_get_result($date_count_stmt);
$total_dates = mysqli_fetch_assoc($date_count_result)['total_dates'] ?? 0;
$total_pages = ceil($total_dates / $records_per_page);

// Get attendance data for the current page using a more efficient query
$attendance_query = "
    SELECT 
        sa.attendance_date,
        sa.session,
        sa.status,
        sa.created_at
    FROM student_attendance sa
    INNER JOIN (
        SELECT DISTINCT attendance_date 
        FROM student_attendance 
        WHERE student_id = ? 
        ORDER BY attendance_date DESC 
        LIMIT ? OFFSET ?
    ) dates ON sa.attendance_date = dates.attendance_date
    WHERE sa.student_id = ?
    ORDER BY sa.attendance_date DESC, sa.session ASC
";
$attendance_stmt = mysqli_prepare($conn, $attendance_query);
mysqli_stmt_bind_param($attendance_stmt, "siis", $student_id, $records_per_page, $offset, $student_id);
mysqli_stmt_execute($attendance_stmt);
$attendance_result = mysqli_stmt_get_result($attendance_stmt);

while ($attendance_row = mysqli_fetch_assoc($attendance_result)) {
    $date = $attendance_row['attendance_date'];
    if (!isset($attendance_summary[$date])) {
        $attendance_summary[$date] = [];
    }
    $attendance_summary[$date][$attendance_row['session']] = $attendance_row['status'];
}

// Get pagination parameter for monthly attendance view
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Validate the selected month format
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}

// Calculate attendance percentage for selected month
$monthly_attendance_query = "
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_sessions
    FROM student_attendance 
    WHERE student_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?
";
$monthly_stmt = mysqli_prepare($conn, $monthly_attendance_query);
mysqli_stmt_bind_param($monthly_stmt, "ss", $student_id, $selected_month);
mysqli_stmt_execute($monthly_stmt);
$monthly_result = mysqli_stmt_get_result($monthly_stmt);
$monthly_data = mysqli_fetch_assoc($monthly_result);

$total_sessions = $monthly_data['total_sessions'] ?? 0;
$present_sessions = $monthly_data['present_sessions'] ?? 0;
$attendance_percentage = $total_sessions > 0 ? round(($present_sessions / $total_sessions) * 100, 2) : 0;

// Calculate overall attendance percentage for all time
$overall_attendance_query = "
    SELECT
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_sessions
    FROM student_attendance
    WHERE student_id = ?
";
$overall_stmt = mysqli_prepare($conn, $overall_attendance_query);
mysqli_stmt_bind_param($overall_stmt, "s", $student_id);
mysqli_stmt_execute($overall_stmt);
$overall_result = mysqli_stmt_get_result($overall_stmt);
$overall_data = mysqli_fetch_assoc($overall_result);

$overall_total_sessions = $overall_data['total_sessions'] ?? 0;
$overall_present_sessions = $overall_data['present_sessions'] ?? 0;
$overall_attendance_percentage = $overall_total_sessions > 0 ? round(($overall_present_sessions / $overall_total_sessions) * 100, 2) : 0;

// Get available months for this student
$available_months_query = "
    SELECT DISTINCT DATE_FORMAT(attendance_date, '%Y-%m') as month,
           DATE_FORMAT(attendance_date, '%M %Y') as month_name
    FROM student_attendance 
    WHERE student_id = ?
    ORDER BY month DESC
";
$available_months_stmt = mysqli_prepare($conn, $available_months_query);
mysqli_stmt_bind_param($available_months_stmt, "s", $student_id);
mysqli_stmt_execute($available_months_stmt);
$available_months_result = mysqli_stmt_get_result($available_months_stmt);

$available_months = [];
while ($row = mysqli_fetch_assoc($available_months_result)) {
    $available_months[] = $row;
}

// Get attendance data for last 3 months
$current_month = date('Y-m');
$three_months_ago = date('Y-m', strtotime('-2 months'));

$monthly_attendance_query = "
    SELECT 
        DATE_FORMAT(attendance_date, '%Y-%m') as month,
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as attendance_points
    FROM student_attendance 
    WHERE student_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') >= ?
    GROUP BY DATE_FORMAT(attendance_date, '%Y-%m')
    ORDER BY month DESC
";

$monthly_stmt = mysqli_prepare($conn, $monthly_attendance_query);
mysqli_stmt_bind_param($monthly_stmt, "ss", $student_id, $three_months_ago);
mysqli_stmt_execute($monthly_stmt);
$monthly_result = mysqli_stmt_get_result($monthly_stmt);

$monthly_stats = [];
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_stats[] = $row;
}

// Calculate total attendance points
$total_attendance_points = 0;
foreach ($monthly_stats as $stat) {
    $total_attendance_points += $stat['attendance_points'];
}

// Get student's event data
$participated_events = [];
$organized_events = [];
$won_events = [];

// Get events the student participated in (using participants table)
$participated_events = [];
$participated_query = "
    SELECT e.event_id, e.title, e.event_date, e.venue, p.registered_at, 'confirmed' as registration_status
    FROM events e
    JOIN participants p ON e.event_id = p.event_id
    WHERE p.student_id = ?
    ORDER BY e.event_date DESC
    LIMIT 10
";
$participated_stmt = mysqli_prepare($conn, $participated_query);
mysqli_stmt_bind_param($participated_stmt, "s", $student_id);
mysqli_stmt_execute($participated_stmt);
$participated_result = mysqli_stmt_get_result($participated_stmt);
while ($row = mysqli_fetch_assoc($participated_result)) {
    $participated_events[] = $row;
}

// Get events the student organized (using organizers table)
$organized_events = [];
$organized_query = "
    SELECT e.event_id, e.title, e.event_date, e.venue, e.event_date as created_at
    FROM events e
    JOIN organizers o ON e.event_id = o.event_id
    WHERE o.student_id = ?
    ORDER BY e.event_date DESC
    LIMIT 10
";
$organized_stmt = mysqli_prepare($conn, $organized_query);
mysqli_stmt_bind_param($organized_stmt, "s", $student_id);
mysqli_stmt_execute($organized_stmt);
$organized_result = mysqli_stmt_get_result($organized_stmt);
while ($row = mysqli_fetch_assoc($organized_result)) {
    $organized_events[] = $row;
}

// Get events the student won (using winners table)
$won_events = [];
$won_query = "
    SELECT e.event_id, e.title, e.event_date, e.venue, w.position, w.announced_at
    FROM events e
    JOIN winners w ON e.event_id = w.event_id
    WHERE w.student_id = ?
    ORDER BY e.event_date DESC
    LIMIT 10
";
$won_stmt = mysqli_prepare($conn, $won_query);
mysqli_stmt_bind_param($won_stmt, "s", $student_id);
mysqli_stmt_execute($won_stmt);
$won_result = mysqli_stmt_get_result($won_stmt);
while ($row = mysqli_fetch_assoc($won_result)) {
    $won_events[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Student Dashboard - SRKR Engineering College</title>
    <link rel="stylesheet" href="student_dashboard.css">   
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="back-nav">
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert-modern alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert-modern alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
           
            
            <div class="row">
                <!-- Student Profile Card -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-user-graduate"></i> Student Profile
                        </div>
                        <div class="card-body">
                            <div class="profile-section">
                                <div class="profile-picture-container">
                                    <?php if (!empty($student_data['profile_picture']) && file_exists($student_data['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($student_data['profile_picture']); ?>" 
                                             alt="Profile Picture">
                                    <?php else: ?>
                                        <div class="profile-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <button type="button" class="camera-btn" 
                                            data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($student_name); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($student_id); ?></small>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($student_email); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Class</span>
                                <span class="info-value"><?php echo htmlspecialchars($student_year . '/4 ' . $student_branch . '-' . $student_section); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Semester</span>
                                <span class="info-value"><?php echo htmlspecialchars($student_semester); ?></span>
                            </div>
                            <?php if ($house_name): ?>
                            <div class="info-item">
                                <span class="info-label">House</span>
                                <span class="info-value"><?php echo htmlspecialchars($house_name); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($student_data['cgpa']): ?>
                            <div class="info-item">
                                <span class="info-label">CGPA</span>
                                <span class="info-value"><?php echo htmlspecialchars($student_data['cgpa']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editBasicModal">
                                    <i class="fas fa-user-edit"></i> Edit Basic Info
                                </button>
                                <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editPersonalModal">
                                    <i class="fas fa-home"></i> Edit Personal Details
                                </button>
                                <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAcademicModal">
                                    <i class="fas fa-graduation-cap"></i> Edit Academic Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance & Points Summary -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-chart-pie"></i> Attendance & Points Overview
                        </div>
                        <div class="card-body">
                            <!-- Overall Attendance Section -->
                            <div class="mb-4 pb-3" style="border-bottom: 1px solid #f0f0f0;">
                                <h6 class="mb-3" style="font-size: 13px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-chart-pie me-1"></i> Overall Attendance
                                </h6>

                                <div class="text-center mb-3">
                                    <div class="stat-number" style="font-size: 24px; color: <?php echo $overall_attendance_percentage >= 75 ? '#28a745' : ($overall_attendance_percentage >= 60 ? '#ffc107' : '#dc3545'); ?>;">
                                        <?php echo $overall_attendance_percentage; ?>%
                                    </div>
                                    <div class="stat-label">Overall Attendance</div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <?php echo $overall_present_sessions; ?> of <?php echo $overall_total_sessions; ?> sessions attended
                                        </small>
                                    </div>
                                </div>

                                <div class="progress-bar-custom mb-4">
                                    <div class="progress-fill" style="width: <?php echo $overall_attendance_percentage; ?>%; background: <?php echo $overall_attendance_percentage >= 75 ? '#28a745' : ($overall_attendance_percentage >= 60 ? '#ffc107' : '#dc3545'); ?>;"></div>
                                </div>
                            </div>

                            <!-- Monthly Attendance Section -->
                            <div class="mb-4 pb-3" style="border-bottom: 1px solid #f0f0f0;">
                                <h6 class="mb-3" style="font-size: 13px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-calendar-check me-1"></i> Monthly Attendance
                                </h6>

                                <!-- Month Navigation -->
                                <?php if (!empty($available_months)): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="month-nav d-flex align-items-center">
                                        <?php
                                        $current_index = -1;
                                        foreach ($available_months as $index => $month) {
                                            if ($month['month'] === $selected_month) {
                                                $current_index = $index;
                                                break;
                                            }
                                        }
                                        
                                        $prev_month = ($current_index < count($available_months) - 1) ? $available_months[$current_index + 1]['month'] : null;
                                        $next_month = ($current_index > 0) ? $available_months[$current_index - 1]['month'] : null;
                                        ?>
                                        
                                        <?php if ($prev_month): ?>
                                            <a href="?month=<?php echo $prev_month; ?><?php echo isset($_GET['attendance_page']) ? '&attendance_page=' . $_GET['attendance_page'] : ''; ?>" 
                                               class="btn btn-sm btn-outline-secondary me-2" style="min-width: 35px;">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary me-2" disabled style="min-width: 35px;">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <span class="fw-bold text-center" style="min-width: 120px;">
                                            <?php echo date('F Y', strtotime($selected_month . '-01')); ?>
                                        </span>
                                        
                                        <?php if ($next_month): ?>
                                            <a href="?month=<?php echo $next_month; ?><?php echo isset($_GET['attendance_page']) ? '&attendance_page=' . $_GET['attendance_page'] : ''; ?>" 
                                               class="btn btn-sm btn-outline-secondary ms-2" style="min-width: 35px;">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" disabled style="min-width: 35px;">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                                                        
                                </div>
                                <?php endif; ?>
                                
                                <div class="text-center mb-3">
                                    <div class="stat-number"><?php echo $attendance_percentage; ?>%</div>
                                    <div class="stat-label">
                                        <?php echo $selected_month === date('Y-m') ? 'Current Month' : date('F Y', strtotime($selected_month . '-01')); ?>
                                    </div>
                                </div>
                                
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-number text-success"><?php echo $present_sessions; ?></div>
                                        <div class="stat-label">Present</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-danger"><?php echo $total_sessions - $present_sessions; ?></div>
                                        <div class="stat-label">Absent</div>
                                    </div>
                                </div>
                                
                                <div class="progress-bar-custom">
                                    <div class="progress-fill" style="width: <?php echo $attendance_percentage; ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Points Section -->
                            <div class="mb-3">
                                <h6 class="mb-3" style="font-size: 13px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-star me-1"></i> Points Summary
                                </h6>
                                <?php
                                // Get appreciation points
                                $appreciation_query = "SELECT SUM(points) as total_appreciation 
                                                     FROM appreciations 
                                                     WHERE student_id = ?";
                                $stmt = mysqli_prepare($conn, $appreciation_query);
                                mysqli_stmt_bind_param($stmt, "s", $student_id);
                                mysqli_stmt_execute($stmt);
                                $appreciation_result = mysqli_stmt_get_result($stmt);
                                $appreciation_points = mysqli_fetch_assoc($appreciation_result)['total_appreciation'] ?? 0;
                                
                                // Calculate total points (attendance + appreciation)
                                $total_points = $total_attendance_points + $appreciation_points;
                                ?>
                                
                                <div class="text-center mb-3">
                                    <div class="stat-number"><?php echo $total_points; ?></div>
                                    <div class="stat-label">Total Points</div>
                                </div>
                                
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-number text-success"><?php echo $total_attendance_points; ?></div>
                                        <div class="stat-label">attendace points</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-info"><?php echo $appreciation_points; ?></div>
                                        <div class="stat-label">Activities Points</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-3">
                                <button class="action-btn primary" data-bs-toggle="modal" data-bs-target="#leaveModal">
                                    <i class="fas fa-file-alt"></i> Apply for Leave
                                </button>
                               
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Information -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <i class="fas fa-graduation-cap"></i> Academic Profile
                        </div>
                        <div class="card-body">

                        
                            <!-- Education Section -->
                            <div class="resume-section mb-4">
                                <h6 class="resume-section-title">
                                    <i class="fas fa-university"></i> Education
                                </h6>
                                <div class="resume-item">
                                    <div class="resume-item-header">
                                        <strong>B.Tech in <?php echo htmlspecialchars($student_branch); ?></strong>
                                        <span class="resume-date"><?php echo htmlspecialchars($student_data['academic_year'] ?? date('Y')); ?></span>
                                    </div>
                                    <div class="resume-item-subtitle">SRKR Engineering College</div>
                                    <div class="resume-item-details">
                                        <div class="d-flex justify-content-between">
                                            <span>Year: <?php echo htmlspecialchars($student_year); ?>/4</span>
                                            <span>Section: <?php echo htmlspecialchars($student_section); ?></span>
                                        </div>
                                        <?php if ($student_data['cgpa']): ?>
                                        <div class="mt-1">
                                            <span class="badge-modern badge-success">CGPA: <?php echo htmlspecialchars($student_data['cgpa']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                                <!-- Summary Section -->
                                <div class="resume-section mb-4">
                                <h6 class="resume-section-title">
                                    <i class="fas fa-user"></i> Summary
                                    <button class="btn-edit-skills" data-bs-toggle="modal" data-bs-target="#summaryModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </h6>
                                <?php if (!empty($student_data['summary'])): ?>
                                    <div class="summary-content">
                                        <p><?php echo nl2br(htmlspecialchars($student_data['summary'])); ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-plus-circle"></i>
                                        <small>Add your professional summary</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Skills Section -->
                            <?php 
                            $skills = [];
                            if (!empty($student_data['skills'])) {
                                $skills = json_decode($student_data['skills'], true);
                            }
                            ?>
                            <div class="resume-section mb-4">
                                <h6 class="resume-section-title">
                                    <i class="fas fa-code"></i> Skills
                                    <button class="btn-edit-skills" data-bs-toggle="modal" data-bs-target="#skillsModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </h6>
                                <?php if (!empty($skills) && is_array($skills)): ?>
                                    <div class="skills-container">
                                        <?php foreach ($skills as $skill): ?>
                                            <?php if (!empty(trim($skill))): ?>
                                                <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-plus-circle"></i>
                                        <small>Add your skills</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        
                            
                            <!-- Projects Section -->
                            <?php
                            // Get student projects (check if projects table exists)
                            $projects = [];
                            $check_projects_table = mysqli_query($conn, "SHOW TABLES LIKE 'student_projects'");
                            if (mysqli_num_rows($check_projects_table) > 0) {
                                $projects_query = "SELECT * FROM student_projects WHERE student_id = ? ORDER BY created_at DESC LIMIT 3";
                                $projects_stmt = mysqli_prepare($conn, $projects_query);
                                mysqli_stmt_bind_param($projects_stmt, "s", $student_id);
                                mysqli_stmt_execute($projects_stmt);
                                $projects_result = mysqli_stmt_get_result($projects_stmt);
                                while ($project = mysqli_fetch_assoc($projects_result)) {
                                    $projects[] = $project;
                                }
                            }
                            ?>
                            <div class="resume-section mb-4">
                                <h6 class="resume-section-title">
                                    <i class="fas fa-project-diagram"></i> Projects
                                    <button class="btn-edit-skills" data-bs-toggle="modal" data-bs-target="#projectsModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </h6>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                    <div class="resume-item">
                                        <div class="resume-item-header">
                                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                            <?php if (!empty($project['completion_date'])): ?>
                                            <span class="resume-date"><?php echo date('M Y', strtotime($project['completion_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($project['description'])): ?>
                                        <div class="resume-item-subtitle"><?php echo htmlspecialchars($project['description']); ?></div>
                                        <?php endif; ?>
                                        <div class="resume-item-details">
                                            <?php if (!empty($project['technologies'])): ?>
                                            <div class="project-technologies">
                                                <?php 
                                                $technologies = json_decode($project['technologies'], true);
                                                if (is_array($technologies)):
                                                    foreach ($technologies as $tech): ?>
                                                        <span class="tech-tag"><?php echo htmlspecialchars($tech); ?></span>
                                                    <?php endforeach;
                                                endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($project['project_url'])): ?>
                                            <div class="mt-2">
                                                <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="project-link">
                                                    <i class="fas fa-external-link-alt"></i> View Project
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-plus-circle"></i>
                                        <small>Add your projects</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                         
                            <!-- Contact Information -->
                            <div class="resume-section">
                                <h6 class="resume-section-title">
                                    <i class="fas fa-address-card"></i> Contact
                                </h6>
                                <div class="contact-info">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($student_email); ?></span>
                                    </div>
                                    <?php if ($student_data['personal_number']): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($student_data['personal_number']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($student_data['address']): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($student_data['address']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($student_data['dob']): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-birthday-cake"></i>
                                        <span><?php echo date('d M Y', strtotime($student_data['dob'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                        </div>
                    </div>
                </div>
                
            

            </div>
            
            <!-- Student Activity Tabs -->
            <div class="dashboard-card mb-4" id="leave-status">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Student Activity & Records
                </div>
                <div class="card-body p-0">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-tabs-modern" id="attendanceLeaveTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab" aria-controls="attendance" aria-selected="true">
                                <i class="fas fa-calendar-check"></i> Attendance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab" aria-controls="leave" aria-selected="false">
                                <i class="fas fa-file-alt"></i> Leave Applications
                                <?php if (!empty($leave_applications)): ?>
                                    <span class="badge-modern badge-info ms-1"><?php echo count($leave_applications); ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
                                <i class="fas fa-calendar-alt"></i> Events
                                <?php 
                                $total_events = count($participated_events) + count($organized_events) + count($won_events);
                                if ($total_events > 0): ?>
                                    <span class="badge-modern badge-success ms-1"><?php echo $total_events; ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="attendanceLeaveTabContent">
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade show active" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                            <?php if (!empty($attendance_summary)): ?>
                                <div class="table-responsive">
                                    <table class="table table-modern">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Forenoon</th>
                                                <th>Afternoon</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_summary as $date => $sessions): ?>
                                            <tr>
                                                <td><strong><?php echo date('d M Y', strtotime($date)); ?></strong></td>
                                                <td>
                                                    <?php if (isset($sessions['Forenoon'])): ?>
                                                        <span class="badge-modern <?php echo $sessions['Forenoon'] === 'Present' ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo $sessions['Forenoon']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-modern badge-info">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($sessions['Afternoon'])): ?>
                                                        <span class="badge-modern <?php echo $sessions['Afternoon'] === 'Present' ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo $sessions['Afternoon']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-modern badge-info">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Attendance Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center p-3" style="border-top: 1px solid #f0f0f0;">
                                    <div class="pagination-info">
                                        <small class="text-muted">
                                            Showing page <?php echo $attendance_page; ?> of <?php echo $total_pages; ?> 
                                            (<?php echo $total_dates; ?> total days)
                                        </small>
                                    </div>
                                    <nav aria-label="Attendance pagination">
                                        <ul class="pagination pagination-sm mb-0">
                                            <!-- Previous Button -->
                                            <?php if ($attendance_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?attendance_page=<?php echo $attendance_page - 1; ?><?php echo isset($_GET['month']) ? '&month=' . $_GET['month'] : ''; ?>#attendance" 
                                                       onclick="switchToAttendanceTab()">
                                                        <i class="fas fa-chevron-left"></i> Previous
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <!-- Page Numbers -->
                                            <?php
                                            $start_page = max(1, $attendance_page - 2);
                                            $end_page = min($total_pages, $attendance_page + 2);
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <?php if ($i == $attendance_page): ?>
                                                    <li class="page-item active">
                                                        <span class="page-link"><?php echo $i; ?></span>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?attendance_page=<?php echo $i; ?><?php echo isset($_GET['month']) ? '&month=' . $_GET['month'] : ''; ?>#attendance" 
                                                           onclick="switchToAttendanceTab()"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            
                                            <!-- Next Button -->
                                            <?php if ($attendance_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?attendance_page=<?php echo $attendance_page + 1; ?><?php echo isset($_GET['month']) ? '&month=' . $_GET['month'] : ''; ?>#attendance" 
                                                       onclick="switchToAttendanceTab()">
                                                        Next <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-calendar-times"></i>
                                    <h6>No Attendance Records</h6>
                                    <p class="text-muted mb-0">No recent attendance data available.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Leave Applications Tab -->
                        <div class="tab-pane fade" id="leave" role="tabpanel" aria-labelledby="leave-tab">
                            <?php if (!empty($leave_applications)): ?>
                                <div class="table-responsive">
                                    <table class="table table-modern">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Dates</th>
                                                <th>Days</th>
                                                <th>Status</th>
                                                <th>Applied On</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($leave_applications as $application): ?>
                                            <tr>
                                                <td><strong>#<?php echo $application['id']; ?></strong></td>
                                                <td>
                                                    <span class="badge-modern badge-info">
                                                        <?php echo ucfirst($application['leave_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($application['start_date'])); ?> - 
                                                    <?php echo date('d M Y', strtotime($application['end_date'])); ?>
                                                </td>
                                                <td><?php echo $application['total_days']; ?> day(s)</td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($application['status']) {
                                                        case 'pending':
                                                            $status_class = 'badge-warning';
                                                            break;
                                                        case 'approved':
                                                            $status_class = 'badge-success';
                                                            break;
                                                        case 'rejected':
                                                            $status_class = 'badge-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge-modern <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($application['status']); ?>
                                                    </span>
                                                    <?php if ($application['status_description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($application['status_description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('d M Y H:i', strtotime($application['applied_at'])); ?></small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-file-alt"></i>
                                    <h6>No Leave Applications</h6>
                                    <p class="text-muted mb-0">You haven't applied for any leave yet.</p>
                                    <button class="action-btn primary mt-3" data-bs-toggle="modal" data-bs-target="#leaveModal">
                                        <i class="fas fa-plus"></i> Apply for Leave
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Events Tab -->
                        <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                            <?php if (!empty($participated_events) || !empty($organized_events) || !empty($won_events)): ?>
                                <!-- Events Sub-tabs -->
                                <ul class="nav nav-pills nav-fill mb-3" id="eventsSubTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="participated-subtab" data-bs-toggle="pill" data-bs-target="#participated" type="button" role="tab">
                                            <i class="fas fa-users"></i> Participated
                                            <?php if (!empty($participated_events)): ?>
                                                <span class="badge bg-primary ms-1"><?php echo count($participated_events); ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="organized-subtab" data-bs-toggle="pill" data-bs-target="#organized" type="button" role="tab">
                                            <i class="fas fa-cog"></i> Organized
                                            <?php if (!empty($organized_events)): ?>
                                                <span class="badge bg-info ms-1"><?php echo count($organized_events); ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="won-subtab" data-bs-toggle="pill" data-bs-target="#won" type="button" role="tab">
                                            <i class="fas fa-trophy"></i> Won
                                            <?php if (!empty($won_events)): ?>
                                                <span class="badge bg-warning ms-1"><?php echo count($won_events); ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="eventsSubTabContent">
                                    <!-- Participated Events -->
                                    <div class="tab-pane fade show active" id="participated" role="tabpanel">
                                        <?php if (!empty($participated_events)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-modern">
                                                    <thead>
                                                        <tr>
                                                            <th>Event</th>
                                                            <th>Date</th>
                                                            <th>Venue</th>
                                                            <th>Registered</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($participated_events as $event): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                                            <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($event['venue'] ?? 'TBA'); ?></td>
                                                            <td><small><?php echo date('d M Y', strtotime($event['registered_at'])); ?></small></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-data">
                                                <i class="fas fa-users"></i>
                                                <h6>No Participated Events</h6>
                                                <p class="text-muted mb-0">You haven't participated in any events yet.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Organized Events -->
                                    <div class="tab-pane fade" id="organized" role="tabpanel">
                                        <?php if (!empty($organized_events)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-modern">
                                                    <thead>
                                                        <tr>
                                                            <th>Event</th>
                                                            <th>Date</th>
                                                            <th>Venue</th>
                                                            <th>Created</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($organized_events as $event): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                                            <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($event['venue'] ?? 'TBA'); ?></td>
                                                            <td><small><?php echo date('d M Y', strtotime($event['created_at'])); ?></small></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-data">
                                                <i class="fas fa-cog"></i>
                                                <h6>No Organized Events</h6>
                                                <p class="text-muted mb-0">You haven't organized any events yet.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Won Events -->
                                    <div class="tab-pane fade" id="won" role="tabpanel">
                                        <?php if (!empty($won_events)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-modern">
                                                    <thead>
                                                        <tr>
                                                            <th>Event</th>
                                                            <th>Date</th>
                                                            <th>Position</th>
                                                            <th>Prize</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($won_events as $event): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                                            <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                                                            <td>
                                                                <span class="badge-modern badge-warning">
                                                                    <?php echo htmlspecialchars($event['position']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($event['prize'] ?? 'Certificate'); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-data">
                                                <i class="fas fa-trophy"></i>
                                                <h6>No Won Events</h6>
                                                <p class="text-muted mb-0">You haven't won any events yet.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-calendar-alt"></i>
                                    <h6>No Event Activity</h6>
                                    <p class="text-muted mb-0">You haven't participated in, organized, or won any events yet.</p>
                                    <a href="events_overview.php" class="action-btn primary mt-3">
                                        <i class="fas fa-search"></i> Browse Events
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Picture Upload Modal -->
    <div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profilePictureModalLabel">
                        <i class="fas fa-camera"></i> Upload Profile Picture
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">
                                <i class="fas fa-image"></i> Select Image
                            </label>
                            <input type="file" name="profile_picture" id="profile_picture" class="form-control" 
                                   accept="image/*" required>
                            <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, GIF (Max size: 5MB)</small>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="upload_profile_picture" class="btn-modern btn-primary">
                                <i class="fas fa-upload"></i> Upload Picture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skills Update Modal -->
    <div class="modal fade" id="skillsModal" tabindex="-1" aria-labelledby="skillsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="skillsModalLabel">
                        <i class="fas fa-code"></i> Update Skills
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="update_skills" value="1">
                        <div class="mb-3">
                            <label for="skills" class="form-label">
                                <i class="fas fa-tags"></i> Skills (comma-separated)
                            </label>
                            <textarea name="skills" id="skills" class="form-control" rows="4" 
                                      placeholder="e.g., JavaScript, Python, React, Node.js, MySQL"><?php 
                                if (!empty($skills) && is_array($skills)) {
                                    echo htmlspecialchars(implode(', ', $skills));
                                }
                            ?></textarea>
                            <small class="form-text text-muted">Separate each skill with a comma. Example: JavaScript, Python, React</small>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Skills
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Update Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="summaryModalLabel">
                        <i class="fas fa-user"></i> Update Summary
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="update_summary" value="1">
                        <div class="mb-3">
                            <label for="summary" class="form-label">
                                <i class="fas fa-align-left"></i> Professional Summary
                            </label>
                            <textarea name="summary" id="summary" class="form-control" rows="6" 
                                      placeholder="Write a brief professional summary about yourself, your goals, and what makes you unique..."><?php echo htmlspecialchars($student_data['summary'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">Describe your professional background, skills, and career objectives</small>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Modal -->
    <div class="modal fade" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectsModalLabel">
                        <i class="fas fa-project-diagram"></i> Manage Projects
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Project management functionality will be available soon. For now, you can update your projects through the profile edit page.
                    </div>
                
                </div>
            </div>
        </div>
    </div>

    <!-- Social Links Update Modal -->
    <div class="modal fade" id="socialLinksModal" tabindex="-1" aria-labelledby="socialLinksModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="socialLinksModalLabel">
                        <i class="fas fa-link"></i> Update Social Links
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="update_social_links" value="1">
                        <div class="mb-3">
                            <label for="social_links" class="form-label">
                                <i class="fas fa-globe"></i> Social Links (one per line)
                            </label>
                            <textarea name="social_links" id="social_links" class="form-control" rows="6" 
                                      placeholder="https://linkedin.com/in/yourprofile&#10;https://github.com/yourusername&#10;https://yourportfolio.com&#10;https://twitter.com/yourusername"><?php 
                                if (!empty($social_links) && is_array($social_links)) {
                                    echo htmlspecialchars(implode("\n", $social_links));
                                }
                            ?></textarea>
                            <small class="form-text text-muted">Enter each social media or portfolio link on a new line. Include full URLs (starting with http:// or https://)</small>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Social Links
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Application Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveModalLabel">
                        <i class="fas fa-file-alt"></i> Apply for Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="leave_type" class="form-label">
                                    <i class="fas fa-list"></i> Leave Type
                                </label>
                                <select name="leave_type" id="leave_type" class="form-control" required>
                                    <option value="">Select Leave Type</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="personal">Personal Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">
                                    <i class="fas fa-calendar"></i> Start Date
                                </label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">
                                    <i class="fas fa-calendar"></i> End Date
                                </label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">
                                <i class="fas fa-comment"></i> Reason for Leave
                            </label>
                            <textarea name="reason" id="reason" class="form-control" rows="4" required 
                                      placeholder="Please provide a detailed reason for your leave application"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="submit_leave" class="btn-modern btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Basic Information Modal -->
    <div class="modal fade" id="editBasicModal" tabindex="-1" aria-labelledby="editBasicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBasicModalLabel">
                        <i class="fas fa-user-edit"></i> Edit Basic Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="update_basic" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="basic_name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <input type="text" name="name" id="basic_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student_data['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="basic_email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" name="email" id="basic_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($student_data['email']); ?>" required>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Basic Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Personal Details Modal -->
    <div class="modal fade" id="editPersonalModal" tabindex="-1" aria-labelledby="editPersonalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPersonalModalLabel">
                        <i class="fas fa-home"></i> Edit Personal Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="update_personal" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="personal_personal_number" class="form-label">
                                    <i class="fas fa-phone"></i> Parent/Guardian Phone Number
                                </label>
                                <input type="tel" name="personal_number" id="personal_personal_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($student_data['personal_number'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="personal_blood_group" class="form-label">
                                    <i class="fas fa-tint"></i> Blood Group
                                </label>
                                <select name="blood_group" id="personal_blood_group" class="form-control">
                                    <option value="">Select Blood Group</option>
                                    <?php
                                    $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    foreach ($blood_groups as $bg) {
                                        $selected = ($student_data['blood_group'] === $bg) ? 'selected' : '';
                                        echo "<option value=\"$bg\" $selected>$bg</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="personal_dob" class="form-label">
                                    <i class="fas fa-birthday-cake"></i> Date of Birth
                                </label>
                                <input type="date" name="dob" id="personal_dob" class="form-control" 
                                       value="<?php echo htmlspecialchars($student_data['dob'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="personal_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Address
                            </label>
                            <textarea name="address" id="personal_address" class="form-control" rows="3"><?php echo htmlspecialchars($student_data['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="update_personal" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Personal Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Academic Profile Modal -->
    <div class="modal fade" id="editAcademicModal" tabindex="-1" aria-labelledby="editAcademicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAcademicModalLabel">
                        <i class="fas fa-graduation-cap"></i> Edit Academic Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form method="POST">
                        <input type="hidden" name="update_academic" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="academic_cgpa" class="form-label">
                                    <i class="fas fa-chart-line"></i> CGPA
                                </label>
                                <input type="number" name="cgpa" id="academic_cgpa" class="form-control" 
                                       value="<?php echo htmlspecialchars($student_data['cgpa'] ?? ''); ?>"
                                       min="0" max="10" step="0.01">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_summary" class="form-label">
                                <i class="fas fa-file-alt"></i> Profile Summary
                            </label>
                            <textarea name="summary" id="academic_summary" class="form-control" rows="4"
                                      placeholder="Write a brief summary about yourself, your interests, and career goals"><?php echo htmlspecialchars($student_data['summary'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_skills" class="form-label">
                                <i class="fas fa-cogs"></i> Skills
                            </label>
                            <input type="text" name="skills" id="academic_skills" class="form-control" 
                                   value="<?php 
                                   if (!empty($student_data['skills'])) {
                                       $skills_array = json_decode($student_data['skills'], true);
                                       if (is_array($skills_array)) {
                                           echo htmlspecialchars(implode(', ', $skills_array));
                                       }
                                   }
                                   ?>"
                                   placeholder="e.g., Java, Python, React, Machine Learning (comma separated)">
                            <small class="form-text text-muted">Separate multiple skills with commas</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_projects" class="form-label">
                                <i class="fas fa-project-diagram"></i> Projects
                            </label>
                            <textarea name="projects" id="academic_projects" class="form-control" rows="3"
                                      placeholder="List your projects (comma separated)"><?php 
                                if (!empty($student_data['projects'])) {
                                    $projects_array = json_decode($student_data['projects'], true);
                                    if (is_array($projects_array)) {
                                        echo htmlspecialchars(implode(', ', $projects_array));
                                    }
                                }
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_certifications" class="form-label">
                                <i class="fas fa-certificate"></i> Certifications
                            </label>
                            <textarea name="certifications" id="academic_certifications" class="form-control" rows="3"
                                      placeholder="List your certifications (comma separated)"><?php 
                                if (!empty($student_data['certifications'])) {
                                    $certifications_array = json_decode($student_data['certifications'], true);
                                    if (is_array($certifications_array)) {
                                        echo htmlspecialchars(implode(', ', $certifications_array));
                                    }
                                }
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_achievements" class="form-label">
                                <i class="fas fa-trophy"></i> Achievements
                            </label>
                            <textarea name="achievements" id="academic_achievements" class="form-control" rows="3"
                                      placeholder="List your achievements and awards (comma separated)"><?php 
                                if (!empty($student_data['achievements'])) {
                                    $achievements_array = json_decode($student_data['achievements'], true);
                                    if (is_array($achievements_array)) {
                                        echo htmlspecialchars(implode(', ', $achievements_array));
                                    }
                                }
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_experience" class="form-label">
                                <i class="fas fa-briefcase"></i> Experience
                            </label>
                            <textarea name="experience" id="academic_experience" class="form-control" rows="3"
                                      placeholder="List your work experience, internships (comma separated)"><?php 
                                if (!empty($student_data['experience'])) {
                                    $experience_array = json_decode($student_data['experience'], true);
                                    if (is_array($experience_array)) {
                                        echo htmlspecialchars(implode(', ', $experience_array));
                                    }
                                }
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_social_links" class="form-label">
                                <i class="fas fa-link"></i> Social Links
                            </label>
                            <input type="text" name="social_links" id="academic_social_links" class="form-control" 
                                   value="<?php 
                                   if (!empty($student_data['social_links'])) {
                                       $social_links_array = json_decode($student_data['social_links'], true);
                                       if (is_array($social_links_array)) {
                                           echo htmlspecialchars(implode(', ', $social_links_array));
                                       }
                                   }
                                   ?>"
                                   placeholder="e.g., https://linkedin.com/in/yourprofile, https://github.com/yourusername (comma separated)">
                            <small class="form-text text-muted">Separate multiple links with commas</small>
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn-modern btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="update_academic" class="btn-modern btn-primary">
                                <i class="fas fa-save"></i> Update Academic Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
        // Set minimum end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });

        // Enhanced UX for profile editing modals
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus first input when modal opens
            const modals = ['editBasicModal', 'editPersonalModal', 'editAcademicModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.addEventListener('shown.bs.modal', function() {
                        const firstInput = modal.querySelector('input, textarea, select');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    });
                }
            });

            // Form validation feedback
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitBtn.disabled = true;

                    // Add a hidden input to carry the button's name and value
                    if (submitBtn.name) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = submitBtn.name;
                        hiddenInput.value = submitBtn.value || '1';
                        form.appendChild(hiddenInput);
                    }
                }
            });
        });

            // Skills input enhancement
            const skillsInput = document.getElementById('academic_skills');
            if (skillsInput) {
                skillsInput.addEventListener('input', function() {
                    // Auto-format skills as user types
                    let value = this.value;
                    // Remove extra spaces and format
                    value = value.replace(/\s*,\s*/g, ', ');
                    if (value !== this.value) {
                        this.value = value;
                    }
                });
            }

            // CGPA validation
            const cgpaInput = document.getElementById('academic_cgpa');
            if (cgpaInput) {
                cgpaInput.addEventListener('input', function() {
                    const value = parseFloat(this.value);
                    if (value > 10) {
                        this.value = '10.00';
                    } else if (value < 0) {
                        this.value = '0.00';
                    }
                });
            }

            // Auto-resize textareas
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });

            // Show success/error messages with better UX
            <?php if ($success): ?>
                // Auto-hide success message after 5 seconds
                setTimeout(function() {
                    const alert = document.querySelector('.alert-success');
                    if (alert) {
                        alert.style.transition = 'opacity 0.5s ease';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 5000);
            <?php endif; ?>

            // Enhanced modal transitions
            const profileModals = document.querySelectorAll('.modal');
            profileModals.forEach(modal => {
                modal.addEventListener('show.bs.modal', function() {
                    this.style.display = 'block';
                    this.style.opacity = '0';
                    setTimeout(() => {
                        this.style.transition = 'opacity 0.3s ease';
                        this.style.opacity = '1';
                    }, 10);
                });
            });
        });

        // Utility function to show loading state
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;
            return originalText;
        }

        // Utility function to hide loading state
        function hideLoading(button, originalText) {
            button.innerHTML = originalText;
            button.disabled = false;
        }

        // Function to switch to attendance tab when pagination is clicked
        function switchToAttendanceTab() {
            // Activate attendance tab
            const attendanceTab = document.getElementById('attendance-tab');
            const attendancePane = document.getElementById('attendance');
            
            // Remove active class from all tabs and panes
            document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Activate attendance tab
            attendanceTab.classList.add('active');
            attendancePane.classList.add('show', 'active');
        }
    </script>
</body>
</html>
