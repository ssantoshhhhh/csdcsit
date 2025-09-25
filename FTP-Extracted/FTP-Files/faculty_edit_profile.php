<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_logged_in']) || !$_SESSION['faculty_logged_in']) {
    header('Location: login.php');
    exit();
}

include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Get real faculty data from database
$faculty_id = $_SESSION['faculty_id'] ?? null;
if (!$faculty_id) {
    // Session data is missing, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

$faculty_query = "SELECT faculty_name, class_id, phone_number, email FROM faculties WHERE faculty_id = ?";
$stmt = mysqli_prepare($conn, $faculty_query);
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$faculty_result = mysqli_stmt_get_result($stmt);
$faculty_data = mysqli_fetch_assoc($faculty_result);

if ($faculty_data) {
    $faculty_name = $faculty_data['faculty_name'];
    $faculty_sections = (string)($faculty_data['class_id'] ?? '');
    $faculty_phone = $faculty_data['phone_number'];
    $faculty_email = $faculty_data['email'];
} else {
    // Fallback to session data if database query fails
    $faculty_name = $_SESSION['faculty_name'] ?? 'Unknown Faculty';
    $faculty_sections = $_SESSION['faculty_sections'] ?? '';
    $faculty_phone = $_SESSION['faculty_phone'] ?? '';
    $faculty_email = $_SESSION['faculty_email'] ?? '';
}

$profile_success = '';
$profile_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['faculty_name'] ?? '');
    $new_phone = trim($_POST['phone_number'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    
    if (!empty($new_name) && !empty($new_email)) {
        $update_query = "UPDATE faculties SET faculty_name = ?, phone_number = ?, email = ? WHERE faculty_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssi", $new_name, $new_phone, $new_email, $faculty_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $profile_success = "Profile updated successfully!";
            // Update session data
            $_SESSION['faculty_name'] = $new_name;
            $_SESSION['faculty_phone'] = $new_phone;
            $_SESSION['faculty_email'] = $new_email;
            // Refresh faculty data
            $faculty_name = $new_name;
            $faculty_phone = $new_phone;
            $faculty_email = $new_email;
        } else {
            $profile_error = "Failed to update profile. Please try again.";
        }
    } else {
        $profile_error = "Name and email are required fields.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Edit Faculty Profile - SRKR Engineering College</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-user-edit"></i> Edit Faculty Profile</h2>
            <p>Update your personal and contact information</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="text-end mb-4">
                <a href="faculty_dashboard.php" class="btn btn-primary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="faculty_logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-user-edit"></i> Faculty Profile
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($profile_error): ?><div class="alert alert-danger"><?php echo $profile_error; ?></div><?php endif; ?>
                    <?php if ($profile_success): ?><div class="alert alert-success"><?php echo $profile_success; ?></div><?php endif; ?>
                    
                    <form method="POST" action="faculty_edit_profile.php">
                        <div class="mb-3">
                            <label for="faculty_name" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-user"></i> Faculty Name
                            </label>
                            <input type="text" name="faculty_name" id="faculty_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($faculty_name); ?>" required
                                   style="border-radius: 10px; padding: 10px 15px;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($faculty_email); ?>" required
                                   style="border-radius: 10px; padding: 10px 15px;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone_number" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" name="phone_number" id="phone_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($faculty_phone); ?>"
                                   style="border-radius: 10px; padding: 10px 15px;">
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="update_profile" class="btn btn-primary" style="border-radius: 10px; padding: 10px 20px;">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
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