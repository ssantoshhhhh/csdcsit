<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include './connect.php';

$applications = [];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $register_no = mysqli_real_escape_string($conn, $_POST['register_no']);
    
    if (!empty($register_no)) {
        $query = "SELECT * FROM leave_applications WHERE register_no = '$register_no' ORDER BY applied_at DESC";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $applications[] = $row;
            }
        } else {
            $error_message = 'Error fetching applications: ' . mysqli_error($conn);
        }
    } else {
        $error_message = 'Please enter your register number.';
    }
}

$sections = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Leave Application Status - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-search"></i> Leave Application Status</h2>
            <p>Check the status of your leave applications</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                            <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                <i class="fas fa-search"></i> Check Your Leave Application Status
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger" style="border-radius: 10px;">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="register_no" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-id-card"></i> Register Number *
                                        </label>
                                        <input type="text" name="register_no" id="register_no" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" 
                                               placeholder="Enter your register number" 
                                               value="<?php echo isset($_POST['register_no']) ? htmlspecialchars($_POST['register_no']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; padding: 10px 15px;">
                                            <i class="fas fa-search"></i> Check Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if (!empty($applications)): ?>
                                <hr>
                                <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 20px;">
                                    <i class="fas fa-list"></i> Your Leave Applications (<?php echo count($applications); ?> found)
                                </h6>
                                
                                <?php foreach ($applications as $app): ?>
                                    <div class="card mb-3" style="border: 1px solid #e3e6f0; border-radius: 10px;">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6 style="color: var(--primary-blue); font-weight: 600; margin-bottom: 10px;">
                                                        Application #<?php echo $app['id']; ?>
                                                    </h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <strong>Student:</strong> <?php echo htmlspecialchars($app['student_name']); ?><br>
                                                                <strong>Section:</strong> <?php echo $sections[$app['section']] ?? $app['section']; ?><br>
                                                                <strong>Leave Type:</strong> <?php echo ucfirst($app['leave_type']); ?>
                                                            </small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <strong>From:</strong> <?php echo date('d M Y', strtotime($app['start_date'])); ?><br>
                                                                <strong>To:</strong> <?php echo date('d M Y', strtotime($app['end_date'])); ?><br>
                                                                <strong>Days:</strong> <?php echo $app['total_days']; ?> day(s)
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <strong>Reason:</strong> <?php echo htmlspecialchars($app['reason']); ?>
                                                        </small>
                                                    </div>
                                                    <?php if ($app['hod_remarks']): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>HOD Remarks:</strong> <?php echo htmlspecialchars($app['hod_remarks']); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <?php
                                                    $status_class = '';
                                                    $status_icon = '';
                                                    switch ($app['status']) {
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            $status_icon = 'fas fa-clock';
                                                            break;
                                                        case 'approved':
                                                            $status_class = 'bg-success';
                                                            $status_icon = 'fas fa-check-circle';
                                                            break;
                                                        case 'rejected':
                                                            $status_class = 'bg-danger';
                                                            $status_icon = 'fas fa-times-circle';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?> mb-2" style="border-radius: 8px; font-size: 0.9rem;">
                                                        <i class="<?php echo $status_icon; ?>"></i> <?php echo ucfirst($app['status']); ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> Applied: <?php echo date('d M Y', strtotime($app['applied_at'])); ?><br>
                                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($app['applied_at'])); ?>
                                                    </small>
                                                    <?php if ($app['processed_at']): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-tie"></i> Processed by: <?php echo htmlspecialchars($app['processed_by']); ?><br>
                                                            <i class="fas fa-calendar"></i> <?php echo date('d M Y H:i', strtotime($app['processed_at'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)): ?>
                                <hr>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                                    <h6 style="color: var(--gray-medium);">No leave applications found</h6>
                                    <p class="text-muted">No applications found for the provided register number.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mt-4">
                                <a href="student_leave_application.php" class="btn btn-primary" style="border-radius: 10px; padding: 12px 30px;">
                                    <i class="fas fa-plus"></i> Apply for Leave
                                </a>
                                <a href="index.php" class="btn btn-secondary ms-2" style="border-radius: 10px; padding: 12px 30px;">
                                    <i class="fas fa-home"></i> Back to Home
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
</body>
</html> 