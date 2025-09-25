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
} else {
    // Fallback to session data if database query fails
    $faculty_name = $_SESSION['faculty_name'] ?? 'Unknown Faculty';
    $faculty_sections = $_SESSION['faculty_sections'] ?? '';
}

// Get assigned sections - handle empty sections properly
$assigned_sections = [];
if (!empty($faculty_sections)) {
    $assigned_sections = explode(',', $faculty_sections);
    // Clean up any empty entries
    $assigned_sections = array_filter($assigned_sections, function($section) {
        return !empty(trim($section));
    });
}

$leave_error = '';
$leave_success = '';

// Handle faculty leave approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faculty_leave_action'])) {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['faculty_leave_action'];
    $remarks = mysqli_real_escape_string($conn, $_POST['faculty_remarks'] ?? '');
    $faculty_name = $_SESSION['faculty_name'] ?? '';
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        $status = 'pending';
    }
    $update_query = "UPDATE leave_applications SET status='$status', hod_remarks=CONCAT(IFNULL(hod_remarks,''), '\nFaculty: $remarks'), processed_at=NOW(), processed_by='$faculty_id' WHERE id=$application_id";
    if (mysqli_query($conn, $update_query)) {
        // Send notification to student and HOD
        include_once './mail_config.php';
        include_once './mail_helper.php';
        $student_query = "SELECT * FROM leave_applications WHERE id=$application_id";
        $student_result = mysqli_query($conn, $student_query);
        if ($student_result && $row = mysqli_fetch_assoc($student_result)) {
            $mailHelper = new MailHelper($mail_config);
            $mailHelper->sendLeaveStatusNotification($row, $status, $remarks);
        }
        $leave_success = "Leave application has been $status.";
    } else {
        $leave_error = 'Error updating leave application.';
    }
}

// Fetch all leave applications for assigned section (not just pending)
$all_leaves = [];
if (!empty($assigned_sections)) {
    // $assigned_sections contains class_ids for the faculty
    $class_ids_in = implode(',', array_map('intval', $assigned_sections));
    if (!empty($class_ids_in)) {
        $students_query = "SELECT student_id FROM students WHERE class_id IN ($class_ids_in)";
        $students_result = mysqli_query($conn, $students_query);
        $student_ids = [];
        if ($students_result) {
            while ($student_row = mysqli_fetch_assoc($students_result)) {
                $student_ids[] = "'" . mysqli_real_escape_string($conn, $student_row['student_id']) . "'";
            }
        }

        if (!empty($student_ids)) {
            $student_ids_in = implode(',', $student_ids);
            $query = "SELECT la.*, s.name as student_name, s.student_id as register_no, c.year, c.branch, c.section, sp.parent_number as parent_contact
                      FROM leave_applications la 
                      JOIN students s ON la.student_id = s.student_id 
                      JOIN classes c ON s.class_id = c.class_id
                      LEFT JOIN student_personal sp ON s.student_id = sp.student_id
                      WHERE la.student_id IN ($student_ids_in) 
                      ORDER BY la.applied_at DESC";
            $result = mysqli_query($conn, $query);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Calculate total_days
                    $start = new DateTime($row['start_date']);
                    $end = new DateTime($row['end_date']);
                    $row['total_days'] = $start->diff($end)->days + 1;
                    
                    $all_leaves[] = $row;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Leave Management - Faculty Dashboard</title>
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-calendar-check"></i> Leave Application Management</h2>
            <p>Review and process leave applications from your students.</p>
        </div>
    </div>

    <div class="main-content">
        <div class="container">

            <?php if ($leave_error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($leave_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($leave_success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($leave_success); ?>
                </div>
            <?php endif; ?>

            <div class="text-end mb-4">
                <a href="faculty_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>All Leave Applications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Register No</th>
                                    <th>Class</th>
                                    <th>Leave Dates</th>
                                    <th>Total Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_leaves)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No leave applications found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_leaves as $leave): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($leave['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($leave['register_no']); ?></td>
                                            <td><?php echo htmlspecialchars($leave['year'] . '/' . $leave['branch'] . '-' . $leave['section']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['start_date']))) . ' - ' . htmlspecialchars(date('d M Y', strtotime($leave['end_date']))); ?></td>
                                            <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                            <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $leave['status'] === 'approved' ? 'success' : ($leave['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($leave['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($leave['status'] === 'pending'): ?>
                                                    <form method="POST" action="faculty_leave_management.php" class="d-inline">
                                                        <input type="hidden" name="application_id" value="<?php echo $leave['id']; ?>">
                                                        <input type="hidden" name="faculty_remarks" value="Approved by faculty.">
                                                        <button type="submit" name="faculty_leave_action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                                    </form>
                                                    <form method="POST" action="faculty_leave_management.php" class="d-inline">
                                                        <input type="hidden" name="application_id" value="<?php echo $leave['id']; ?>">
                                                        <input type="text" name="faculty_remarks" placeholder="Reason for rejection" class="form-control-sm d-inline" style="width: 120px;">
                                                        <button type="submit" name="faculty_leave_action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span>Processed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>
