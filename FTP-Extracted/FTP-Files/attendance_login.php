<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in - redirect to attendance entry
if (isset($_SESSION['faculty_logged_in']) && $_SESSION['faculty_logged_in']) {
    header('Location: attendance_entry.php');
    exit();
}

// Debug session info
if (isset($_GET['debug'])) {
    echo "<pre>Session ID: " . session_id() . "\n";
    echo "Session Data: " . print_r($_SESSION, true) . "</pre>";
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === 'srkreccsdcsit') {
        $_SESSION['faculty_logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Debug: Check if session was set
        if (isset($_GET['debug'])) {
            echo "<pre>Session after login: " . print_r($_SESSION, true) . "</pre>";
        }
        
        header('Location: attendance_entry.php');
        exit();
    } else {
        $login_error = 'Invalid password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Faculty Attendance Login - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <!-- <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-sign-in-alt"></i> Faculty Attendance Login</h2>
            <p>Enter your attendance code to access the faculty portal</p>
        </div>
    </div> -->
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-tie" style="font-size: 3rem; color: var(--primary-blue); margin-bottom: 20px;"></i>
                                <h4 style="color: var(--primary-blue); font-weight: 600;">Faculty Portal</h4>
                                <p class="text-muted">Enter your attendance code to continue</p>
                            </div>
                            
                            <?php if ($login_error): ?>
                                <div class="alert alert-danger" style="border-radius: 10px;">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-4">
                                    <label for="password" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-key"></i> Attendance Code
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your attendance code" 
                                           required 
                                           autofocus
                                           style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e3e6f0; transition: border-color 0.3s ease;">
                                </div>
                                
                                <button type="submit" 
                                        class="btn btn-primary w-100" 
                                        style="border-radius: 10px; padding: 12px; font-size: 16px; font-weight: 500; background: var(--primary-blue); border: none;">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
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
    </style>
</body>
</html> 