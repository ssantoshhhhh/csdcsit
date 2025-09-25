<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    header('Location: login.php');
    exit();
}

include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_personal'])) {
        // Update personal information
        $parent_number = $_POST['parent_number'] ?? '';
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
                $update_query = "UPDATE student_personal SET parent_number = ?, address = ?, blood_group = ?, dob = ?, updated_at = NOW() WHERE student_id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "sssss", $parent_number, $address, $blood_group, $dob, $student_id);
            } else {
                // Insert new record
                $update_query = "INSERT INTO student_personal (student_id, parent_number, address, blood_group, dob, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "sssss", $student_id, $parent_number, $address, $blood_group, $dob);
            }
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = 'Personal information updated successfully.';
            } else {
                $error = 'Error updating personal information: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['update_profile'])) {
        // Update profile information
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
                $success = 'Profile information updated successfully.';
            } else {
                $error = 'Error updating profile information: ' . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['update_basic'])) {
        // Update basic student information
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } else {
            $update_query = "UPDATE students SET name = ?, email = ? WHERE student_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "sss", $name, $email, $student_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['student_name'] = $name;
                $_SESSION['student_email'] = $email;
                $success = 'Basic information updated successfully.';
            } else {
                $error = 'Error updating basic information: ' . mysqli_error($conn);
            }
        }
    }
}

// Get current student data
$student_query = "
    SELECT s.*, 
           sp.parent_number, sp.address, sp.blood_group, sp.dob,
           spr.summary, spr.skills, spr.social_links, spr.projects, spr.experience, 
           spr.education, spr.certifications, spr.achievements, spr.cgpa, spr.profile_picture
    FROM students s
    LEFT JOIN student_personal sp ON s.student_id = sp.student_id
    LEFT JOIN student_profile spr ON s.student_id = spr.student_id
    WHERE s.student_id = ?
";
$stmt = mysqli_prepare($conn, $student_query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student_data = mysqli_fetch_assoc($result);

if (!$student_data) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Convert JSON fields back to comma-separated strings for display
$skills_display = '';
if (!empty($student_data['skills'])) {
    $skills_array = json_decode($student_data['skills'], true);
    if (is_array($skills_array)) {
        $skills_display = implode(', ', $skills_array);
    }
}

$social_links_display = '';
if (!empty($student_data['social_links'])) {
    $social_links_array = json_decode($student_data['social_links'], true);
    if (is_array($social_links_array)) {
        $social_links_display = implode(', ', $social_links_array);
    }
}

$projects_display = '';
if (!empty($student_data['projects'])) {
    $projects_array = json_decode($student_data['projects'], true);
    if (is_array($projects_array)) {
        $projects_display = implode(', ', $projects_array);
    }
}

$experience_display = '';
if (!empty($student_data['experience'])) {
    $experience_array = json_decode($student_data['experience'], true);
    if (is_array($experience_array)) {
        $experience_display = implode(', ', $experience_array);
    }
}

$education_display = '';
if (!empty($student_data['education'])) {
    $education_array = json_decode($student_data['education'], true);
    if (is_array($education_array)) {
        $education_display = implode(', ', $education_array);
    }
}

$certifications_display = '';
if (!empty($student_data['certifications'])) {
    $certifications_array = json_decode($student_data['certifications'], true);
    if (is_array($certifications_array)) {
        $certifications_display = implode(', ', $certifications_array);
    }
}

$achievements_display = '';
if (!empty($student_data['achievements'])) {
    $achievements_array = json_decode($student_data['achievements'], true);
    if (is_array($achievements_array)) {
        $achievements_display = implode(', ', $achievements_array);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Edit Profile - Student Dashboard</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-edit"></i> Edit Profile</h2>
            <p>Update your personal and academic information</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="text-end mb-4">
                <a href="student_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                        <i class="fas fa-user"></i> Basic Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                        <i class="fas fa-home"></i> Personal Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-graduation-cap"></i> Academic Profile
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="profileTabsContent">
                <!-- Basic Information Tab -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="card mt-3" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-user"></i> Basic Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-user"></i> Full Name
                                        </label>
                                        <input type="text" name="name" id="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($student_data['name']); ?>" required
                                               style="border-radius: 10px; padding: 10px 15px;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-envelope"></i> Email Address
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($student_data['email']); ?>" required
                                               style="border-radius: 10px; padding: 10px 15px;">
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="update_basic" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Basic Information
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Details Tab -->
                <div class="tab-pane fade" id="personal" role="tabpanel">
                    <div class="card mt-3" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-home"></i> Personal Details
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="parent_number" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-phone"></i> Parent/Guardian Phone Number
                                        </label>
                                        <input type="tel" name="parent_number" id="parent_number" class="form-control" 
                                               value="<?php echo htmlspecialchars($student_data['parent_number'] ?? ''); ?>"
                                               style="border-radius: 10px; padding: 10px 15px;">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="blood_group" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-tint"></i> Blood Group
                                        </label>
                                        <select name="blood_group" id="blood_group" class="form-control" style="border-radius: 10px; padding: 10px 15px;">
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
                                        <label for="dob" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-birthday-cake"></i> Date of Birth
                                        </label>
                                        <input type="date" name="dob" id="dob" class="form-control" 
                                               value="<?php echo htmlspecialchars($student_data['dob'] ?? ''); ?>" required
                                               style="border-radius: 10px; padding: 10px 15px;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-map-marker-alt"></i> Address
                                    </label>
                                    <textarea name="address" id="address" class="form-control" rows="3"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($student_data['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="update_personal" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update Personal Details
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Profile Tab -->
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    <div class="card mt-3" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-graduation-cap"></i> Academic Profile
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cgpa" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-chart-line"></i> CGPA
                                        </label>
                                        <input type="number" name="cgpa" id="cgpa" class="form-control" 
                                               value="<?php echo htmlspecialchars($student_data['cgpa'] ?? ''); ?>"
                                               min="0" max="10" step="0.01"
                                               style="border-radius: 10px; padding: 10px 15px;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="summary" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-file-alt"></i> Profile Summary
                                    </label>
                                    <textarea name="summary" id="summary" class="form-control" rows="4"
                                              placeholder="Write a brief summary about yourself, your interests, and career goals"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($student_data['summary'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="skills" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-cogs"></i> Skills
                                    </label>
                                    <input type="text" name="skills" id="skills" class="form-control" 
                                           value="<?php echo htmlspecialchars($skills_display); ?>"
                                           placeholder="e.g., Java, Python, React, Machine Learning (comma separated)"
                                           style="border-radius: 10px; padding: 10px 15px;">
                                    <small class="form-text text-muted">Separate multiple skills with commas</small>
                                </div>
                                <div class="mb-3">
                                    <label for="projects" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-project-diagram"></i> Projects
                                    </label>
                                    <textarea name="projects" id="projects" class="form-control" rows="3"
                                              placeholder="List your projects (comma separated)"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($projects_display); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="certifications" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-certificate"></i> Certifications
                                    </label>
                                    <textarea name="certifications" id="certifications" class="form-control" rows="3"
                                              placeholder="List your certifications (comma separated)"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($certifications_display); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="achievements" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-trophy"></i> Achievements
                                    </label>
                                    <textarea name="achievements" id="achievements" class="form-control" rows="3"
                                              placeholder="List your achievements and awards (comma separated)"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($achievements_display); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="experience" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-briefcase"></i> Experience
                                    </label>
                                    <textarea name="experience" id="experience" class="form-control" rows="3"
                                              placeholder="List your work experience, internships (comma separated)"
                                              style="border-radius: 10px; padding: 10px 15px;"><?php echo htmlspecialchars($experience_display); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="social_links" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-link"></i> Social Links
                                    </label>
                                    <input type="text" name="social_links" id="social_links" class="form-control" 
                                           value="<?php echo htmlspecialchars($social_links_display); ?>"
                                           placeholder="e.g., https://linkedin.com/in/yourprofile, https://github.com/yourusername (comma separated)"
                                           style="border-radius: 10px; padding: 10px 15px;">
                                    <small class="form-text text-muted">Separate multiple links with commas</small>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="update_profile" class="btn btn-info">
                                        <i class="fas fa-save"></i> Update Academic Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <style>
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(7,101,147,0.25);
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--gray-medium);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--light-blue);
            color: var(--primary-blue);
            border: none;
            border-bottom: 3px solid var(--primary-blue);
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-blue);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 20px 15px;
            }
            
            .form-control {
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</body>
</html>
