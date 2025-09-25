<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check HOD credentials first (hardcoded)
        if ($username === 'hod@srkrec.edu.in' && $password === 'hod123') {
            $_SESSION['hod_logged_in'] = true;
            $_SESSION['hod_username'] = $username;
            header('Location: hod_dashboard.php');
            exit();
        }
        
        // Check Faculty credentials
        $stmt = mysqli_prepare($conn, "SELECT faculty_id, faculty_name, email, password, class_id, phone_number FROM faculties WHERE email = ? AND is_active = 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $password_match = false;
            if (password_verify($password, $row['password'])) {
                $password_match = true;
            } elseif ($password === $row['password']) {
                $password_match = true;
            }
            
            if ($password_match) {
                // Set faculty session variables
                $_SESSION['faculty_logged_in'] = true;
                $_SESSION['faculty_id'] = $row['faculty_id'];
                $_SESSION['faculty_name'] = $row['faculty_name'];
                $_SESSION['faculty_username'] = $row['email'];
                $_SESSION['faculty_class_id'] = $row['class_id'];
                $_SESSION['faculty_phone'] = $row['phone_number'];
                $_SESSION['faculty_email'] = $row['email'];
                $_SESSION['faculty_sections'] = $row['class_id'];
                $host  = $_SERVER['HTTP_HOST'];
                $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra = 'faculty_dashboard.php';
                header("Location: http://$host$uri/$extra");
                exit();
            }
        }
        
        // Check Student credentials
        $stmt = mysqli_prepare($conn, "
            SELECT s.student_id, s.name, s.email, s.password, s.class_id, s.branch, s.section, s.hid,
                   c.year, c.semester, c.academic_year
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE s.email = ?
        ");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $password_match = false;
            if (password_verify($password, $row['password'])) {
                $password_match = true;
            } elseif ($password === $row['password']) {
                $password_match = true;
            }
            
            if ($password_match) {
                // Set student session variables
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_id'] = $row['student_id'];
                $_SESSION['student_name'] = $row['name'];
                $_SESSION['student_email'] = $row['email'];
                $_SESSION['student_class_id'] = $row['class_id'];
                $_SESSION['student_branch'] = $row['branch'];
                $_SESSION['student_section'] = $row['section'];
                $_SESSION['student_hid'] = $row['hid'];
                $_SESSION['student_year'] = $row['year'];
                $_SESSION['student_semester'] = $row['semester'];
                $_SESSION['student_academic_year'] = $row['academic_year'];
                header('Location: student_dashboard.php');
                exit();
            }
        }
        
        // If no match found
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SRKR Engineering College</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --background-color: #f8fafc;
            --surface-color: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            
            pointer-events: none;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            text-align: center;
            padding: 48px 32px 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(255, 255, 255, 0.03) 2px,
                rgba(255, 255, 255, 0.03) 4px
            );
            animation: shimmer 20s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .college-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .college-logo i {
            font-size: 32px;
            color: white;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
            letter-spacing: -0.025em;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 2;
            font-weight: 400;
        }
        
        .login-body {
            padding: 40px 32px;
            background: var(--surface-color);
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 16px;
        }
        
        .input-wrapper {
            position: relative;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 16px 20px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--background-color);
            width: 100%;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: var(--surface-color);
            outline: none;
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
            font-weight: 400;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(37, 99, 235, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border: none;
            border-radius: 16px;
            padding: 18px 24px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            letter-spacing: 0.025em;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-link {
            text-align: center;
            margin-top: 32px;
        }
        
        .back-link a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 12px;
        }
        
        .back-link a:hover {
            color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
            transform: translateY(-1px);
        }
        
        .alert {
            border: none;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fde8e8 100%);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert i {
            font-size: 18px;
        }

        .user-type-badges {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .user-badge {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }
        
        /* Mobile responsiveness */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }
            
            .login-container {
                max-width: 100%;
                border-radius: 20px;
            }
            
            .login-header {
                padding: 40px 24px 24px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }

            .login-header p {
                font-size: 14px;
            }
            
            .login-body {
                padding: 32px 24px;
            }

            .college-logo {
                width: 70px;
                height: 70px;
            }

            .college-logo i {
                font-size: 28px;
            }

            .form-control {
                padding: 14px 16px;
                font-size: 16px; /* Prevent zoom on iOS */
            }

            .btn-login {
                padding: 16px 20px;
            }
        }
        
        /* Loading animation */
        .btn-login.loading {
            pointer-events: none;
            position: relative;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .btn-login.loading .btn-text {
            opacity: 0;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Focus visible for accessibility */
        .form-control:focus-visible,
        .btn-login:focus-visible,
        .back-link a:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Smooth transitions for all interactive elements */
        * {
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
                <p>Welcome back! Please sign in to continue</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <div class="input-wrapper">
                        <input type="email" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your email address" 
                               required 
                               autofocus
                               autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password" 
                               required
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </span>
                </button>
            </form>
            
            
            
            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
            
           
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        // Add loading state to login button
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                return; // Let browser validation handle this
            }
            
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        // Enhanced form interactions
        document.querySelectorAll('.form-control').forEach(input => {
            // Add floating label effect
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.classList.remove('focused');
                if (this.value.trim() !== '') {
                    this.parentElement.parentElement.classList.add('filled');
                } else {
                    this.parentElement.parentElement.classList.remove('filled');
                }
            });

            // Check if field is pre-filled
            if (input.value.trim() !== '') {
                input.parentElement.parentElement.classList.add('filled');
            }
        });

        // Add smooth entrance animation
        window.addEventListener('load', function() {
            document.querySelector('.login-container').style.opacity = '0';
            document.querySelector('.login-container').style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                document.querySelector('.login-container').style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                document.querySelector('.login-container').style.opacity = '1';
                document.querySelector('.login-container').style.transform = 'translateY(0)';
            }, 100);
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                const form = document.getElementById('loginForm');
                const inputs = form.querySelectorAll('input[required]');
                const currentIndex = Array.from(inputs).indexOf(e.target);
                
                if (currentIndex < inputs.length - 1) {
                    e.preventDefault();
                    inputs[currentIndex + 1].focus();
                }
            }
        });
    </script>
</body>
</html>