<?php
include './connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = 'Please enter both email and password.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = mysqli_prepare($conn, "UPDATE faculties SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $message = 'Password updated successfully for ' . htmlspecialchars($email);
            } else {
                $message = 'No user found with that email address.';
            }
        } else {
            $message = 'Error updating password: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Update Faculty Password</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-key"></i> Update Faculty Password</h2>
            <p>Use this tool to set a new, hashed password for a faculty member.</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-user-edit"></i> Update Password
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($message): ?>
                                <div class="alert alert-info" style="border-radius: 10px; margin-bottom: 20px;">
                                    <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-envelope"></i> Faculty Email Address
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control" required 
                                           placeholder="Enter the faculty's email address"
                                           style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e3e6f0;">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-lock"></i> New Password
                                    </label>
                                    <input type="password" name="password" id="password" class="form-control" required 
                                           placeholder="Enter the new password"
                                           style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e3e6f0;">
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; padding: 12px; font-weight: 500;">
                                        <i class="fas fa-save"></i> Update Password
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
</body>
</html>