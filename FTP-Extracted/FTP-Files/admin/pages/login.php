<?php
include "../utils/connect.php";
session_start();
session_regenerate_id(true); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $table = '';
    $redirect = '';
    $login_success = false;

    if ($role === 'user') {
        $table = 'students';
        $redirect = '../../student_dashboard.php';
    } elseif ($role === 'admin') {
        $table = 'house_admins';
        $redirect = './index.php';
    }

    if (!empty($table)) {
        if ($table === 'students') {
            $query = "SELECT s.*, c.year, c.semester, c.academic_year 
                     FROM $table s 
                     LEFT JOIN classes c ON s.class_id = c.class_id 
                     WHERE s.student_id='$username' AND s.password='$password'";
        } else {
            $query = "SELECT * FROM $table WHERE house_name='$username' AND password='$password'";
        }
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            if ($table === 'students') {
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
            } else {
                // Set admin session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_house_name'] = $row['house_name'];
                $_SESSION['admin_hid'] = $row['hid'];
            }
            
            $_SESSION['username'] = $username;
            $_SESSION['last_activity'] = time(); 
            $_SESSION['expire_time'] = 1200; 

            $login_success = true; // Flag successful login
            header("Location: $redirect");
            exit();
        } else {
            $error_message = "Incorrect username or password!";
        }
    } else {
        $error_message = "Please select a role!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .toast-container {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 9999;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <div class="form-container">
            <h2 class="text-center">CSD/IT Houses</h2>
            <?php if (!empty($error_message)) { ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php } ?>
            <form class="needs-validation" novalidate action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div>
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="invalid-feedback">Please enter your username.</div>
                </div>
                <div>
                    <label for="floatingPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="floatingPassword" name="password" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
                <div>
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    <div class="invalid-feedback">Please select your role.</div>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input custom-checkbox" type="checkbox" id="terms" name="termsAccepted" required style="width:10px">
                    <label class="form-check-label" for="terms">Agree to terms and conditions</label>
                    <div class="invalid-feedback"> You must agree before submitting.</div>
                </div>
                <div class="text-center mt-4">
                    <button class="btn btn-primary" type="submit" id="submitBtn">Submit</button>
                </div>
            </form>
        </div>
        <div class="image-container"></div>
    </div>
</div>

<div class="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="../js/login/script.js"></script>

</body>
</html>