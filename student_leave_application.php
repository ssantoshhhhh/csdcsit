<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include './connect.php';

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only use Google reCAPTCHA validation
    $recaptcha_secret = '6LfnX6wrAAAAAGzAzaJgKMIPNkpV6pwlDEBtSP4U'; // Your new secret key
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    if (!$recaptcha || !$recaptcha->success) {
        $error_message = 'Captcha verification failed. Please try again.';
    }else {
        $register_no = mysqli_real_escape_string($conn, $_POST['register_no']);
        $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        $student_email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
        $parent_contact = mysqli_real_escape_string($conn, $_POST['parent_contact']);
        
        // Calculate total days
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $total_days = $start->diff($end)->days + 1;
        
        // Validate dates
        if ($start_date > $end_date) {
            $error_message = 'Start date cannot be after end date.';
        } elseif ($start_date < date('Y-m-d')) {
            $error_message = 'Start date cannot be in the past.';
        } else {
            // Use prepared statements for SQL injection protection
            $stmt = $conn->prepare("INSERT INTO leave_applications (register_no, student_name, section, leave_type, start_date, end_date, total_days, reason, student_email, contact_number, parent_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssissss", $register_no, $student_name, $section, $leave_type, $start_date, $end_date, $total_days, $reason, $student_email, $contact_number, $parent_contact);
            if ($stmt->execute()) {
                $application_id = $stmt->insert_id;
                $success_message = 'Leave application submitted successfully! Your application ID is: ' . $application_id;
                
                // Send email notification to HOD
                try {
                    include './mail_config.php';
                    include './mail_helper.php';
                    
                    $mailHelper = new MailHelper($mail_config);
                    
                    $student_data = [
                        'id' => $application_id,
                        'student_name' => $student_name,
                        'register_no' => $register_no,
                        'section' => $section,
                        'leave_type' => $leave_type,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'total_days' => $total_days,
                        'reason' => $reason,
                        'email' => $student_email,
                        'contact_number' => $contact_number,
                        'applied_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($mailHelper->sendLeaveApplicationNotification($student_data, $hod_email)) {
                        $success_message .= ' An email notification has been sent to the HOD.';
                    } else {
                        $success_message .= ' (Email notification failed, but application was saved.)';
                    }
                } catch (Exception $e) {
                    $success_message .= ' (Email notification failed, but application was saved.)';
                    error_log("Email error: " . $e->getMessage());
                }
            } else {
                $error_message = 'Error submitting application: ' . $stmt->error;
            }
            $stmt->close();
        }
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
    <title>Leave Application - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-file-alt"></i> Leave Application</h2>
            <p>Submit your leave application for approval</p>
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
                                <i class="fas fa-file-alt"></i> Student Leave Application Form
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($success_message): ?>
                                <div class="alert alert-success" style="border-radius: 10px;">
                                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger" style="border-radius: 10px;">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="register_no" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-id-card"></i> Register Number *
                                        </label>
                                        <input type="text" name="register_no" id="register_no" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" placeholder="Enter your register number">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="student_name" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-user"></i> Student Name *
                                        </label>
                                        <input type="text" name="student_name" id="student_name" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" placeholder="Enter your full name">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="section" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-graduation-cap"></i> Section *
                                        </label>
                                        <select name="section" id="section" class="form-control" required 
                                                style="border-radius: 10px; padding: 10px 15px;">
                                            <option value="">Select your section</option>
                                            <?php foreach ($sections as $table => $section_name): ?>
                                                <option value="<?php echo $table; ?>"><?php echo $section_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="leave_type" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-tag"></i> Leave Type *
                                        </label>
                                        <select name="leave_type" id="leave_type" class="form-control" required 
                                                style="border-radius: 10px; padding: 10px 15px;">
                                            <option value="">Select leave type</option>
                                            <option value="sick">Sick Leave</option>
                                            <option value="personal">Personal Leave</option>
                                            <option value="emergency">Emergency Leave</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> Start Date *
                                        </label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-calendar-day"></i> End Date *
                                        </label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-envelope"></i> Email Address *
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" placeholder="Enter your email address">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_number" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-phone"></i> Contact Number *
                                        </label>
                                        <input type="tel" name="contact_number" id="contact_number" class="form-control" required 
                                               style="border-radius: 10px; padding: 10px 15px;" placeholder="Enter your contact number">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="parent_contact" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                            <i class="fas fa-phone"></i> Parent Contact Number
                                        </label>
                                        <input type="tel" name="parent_contact" id="parent_contact" class="form-control" 
                                               style="border-radius: 10px; padding: 10px 15px;" placeholder="Enter parent contact number (optional)">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reason" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                        <i class="fas fa-comment"></i> Reason for Leave *
                                    </label>
                                    <textarea name="reason" id="reason" class="form-control" rows="4" required 
                                              style="border-radius: 10px; padding: 10px 15px;" 
                                              placeholder="Please provide a detailed reason for your leave application"></textarea>
                                </div>
                                
                                <div class="mb-3 text-center">
    <div class="g-recaptcha" data-sitekey="6LfnX6wrAAAAAHrzhe8ZRy3Z_qj8W-M2KwhLMkjo"></div>
</div>
                                
                                <div class="alert alert-info" style="border-radius: 10px;">
                                    <i class="fas fa-info-circle"></i> <strong>Important Notes:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>All fields marked with * are mandatory</li>
                                        <li>Leave applications will be reviewed by HOD</li>
                                        <li>You will receive email notifications about your application status</li>
                                        <li>Please ensure all information is accurate</li>
                                        <li>HOD will be notified immediately when you submit your application</li>
                                    </ul>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; padding: 12px 30px;">
                                        <i class="fas fa-paper-plane"></i> Submit Application
                                    </button>
                                    <a href="index.php" class="btn btn-secondary ms-2" style="border-radius: 10px; padding: 12px 30px;">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <script>
        // Auto-calculate total days when dates change
        document.getElementById('start_date').addEventListener('change', calculateDays);
        document.getElementById('end_date').addEventListener('change', calculateDays);
        
        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // You can display this somewhere if needed
                console.log('Total days:', diffDays);
            }
        }
        
        // Set minimum end date based on start date
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>