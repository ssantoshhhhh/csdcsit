<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';

$hod_email = $_SESSION['hod_username'] ?? ''; // The session stores email as hod_username

// Handle leave application actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['application_id'])) {
        $application_id = (int)$_POST['application_id'];
        $action = $_POST['action'];
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
        
        if ($action === 'approve') {
            $status = 'approved';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        } else {
            $status = 'pending';
        }

        // Get faculty_id for the HOD
        $faculty_query = "SELECT faculty_id FROM faculties WHERE email = ? AND is_active = 1";
        $stmt = mysqli_prepare($conn, $faculty_query);
        mysqli_stmt_bind_param($stmt, "s", $hod_email);
        mysqli_stmt_execute($stmt);
        $faculty_result = mysqli_stmt_get_result($stmt);
        $faculty_id = null;
        
        if ($faculty_row = mysqli_fetch_assoc($faculty_result)) {
            $faculty_id = $faculty_row['faculty_id'];
        }
        
        $update_query = "UPDATE leave_applications SET 
                        status = ?, 
                        hod_remarks = ?, 
                        processed_at = NOW(), 
                        processed_by = ? 
                        WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssi", $status, $remarks, $faculty_id, $application_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Leave application has been " . $status . " successfully.";
            
            // Send email notification to student
            try {
                include './mail_config.php';
                include './mail_helper.php';
                
                // Get student data for email
                $student_query = "SELECT la.*, s.name as student_name, s.email as student_email FROM leave_applications la JOIN students s ON la.student_id = s.student_id WHERE la.id = $application_id";
                $student_result = mysqli_query($conn, $student_query);
                if ($student_result && $row = mysqli_fetch_assoc($student_result)) {
                    $mailHelper = new MailHelper($mail_config);
                    // Notify student
                    if ($mailHelper->sendLeaveStatusNotification($row, $status, $remarks)) {
                        $success_message .= " Email notification has been sent to the student.";
                    } else {
                        $success_message .= " (Email notification failed, but status was updated.)";
                    }
                    // Notify all faculty assigned to this section
                    $section = $row['section'];
                    $faculty_query = "SELECT email FROM faculties WHERE FIND_IN_SET(?, assigned_sections) AND is_active = 1 AND email IS NOT NULL AND email != ''";
                    $faculty_stmt = mysqli_prepare($conn, $faculty_query);
                    mysqli_stmt_bind_param($faculty_stmt, "s", $section);
                    mysqli_stmt_execute($faculty_stmt);
                    $faculty_result = mysqli_stmt_get_result($faculty_stmt);
                    $faculty_emails = [];
                    while ($frow = mysqli_fetch_assoc($faculty_result)) {
                        $faculty_emails[] = $frow['email'];
                    }
                    if (!empty($faculty_emails)) {
                        $mailHelper->notifySectionFaculty($faculty_emails, $row, $status, $remarks);
                    }
                }
            } catch (Exception $e) {
                $success_message .= " (Email notification failed, but status was updated.)";
                error_log("Email error: " . $e->getMessage());
            }
        } else {
            $error_message = "Error updating application: " . mysqli_error($conn);
        }
    }
}

// Get filters
$filter_status = $_GET['status'] ?? '';
$filter_section = $_GET['section'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

// Build query with filters
$where_conditions = [];
if ($filter_status) {
    $where_conditions[] = "la.status = '$filter_status'";
}
if ($filter_section) {
    $where_conditions[] = "la.section = '$filter_section'";
}
if ($filter_date_from) {
    $where_conditions[] = "la.applied_at >= '$filter_date_from'";
}
if ($filter_date_to) {
    $where_conditions[] = "la.applied_at <= '$filter_date_to 23:59:59'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get leave applications with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM leave_applications la $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT la.*, s.name as student_name, s.email as student_email FROM leave_applications la JOIN students s ON la.student_id = s.student_id $where_clause ORDER BY la.applied_at DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($conn, $query);

$sections = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
FROM leave_applications";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Leave Management - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-file-alt"></i> Leave Applications Management</h2>
            <p>Review and manage student leave applications</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Back to Dashboard -->
            <div class="text-end mb-4">
                <a href="hod_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" style="border-radius: 10px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-file-alt" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h4 style="color: var(--primary-blue); font-weight: 600;"><?php echo $stats['total_applications']; ?></h4>
                            <p class="text-muted mb-0">Total Applications</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(255,193,7,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-clock" style="font-size: 2.5rem; color: #ffc107; margin-bottom: 15px;"></i>
                            <h4 style="color: #ffc107; font-weight: 600;"><?php echo $stats['pending_count']; ?></h4>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(40,167,69,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-check-circle" style="font-size: 2.5rem; color: #28a745; margin-bottom: 15px;"></i>
                            <h4 style="color: #28a745; font-weight: 600;"><?php echo $stats['approved_count']; ?></h4>
                            <p class="text-muted mb-0">Approved</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(220,53,69,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-times-circle" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                            <h4 style="color: #dc3545; font-weight: 600;"><?php echo $stats['rejected_count']; ?></h4>
                            <p class="text-muted mb-0">Rejected</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-filter"></i> Filters
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label" style="color: var(--primary-blue); font-weight: 500;">Status</label>
                                <select name="status" id="status" class="form-control" style="border-radius: 10px;">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="section" class="form-label" style="color: var(--primary-blue); font-weight: 500;">Section</label>
                                <select name="section" id="section" class="form-control" style="border-radius: 10px;">
                                    <option value="">All Sections</option>
                                    <?php foreach ($sections as $table => $section_name): ?>
                                        <option value="<?php echo $table; ?>" <?php echo $filter_section === $table ? 'selected' : ''; ?>>
                                            <?php echo $section_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="date_from" class="form-label" style="color: var(--primary-blue); font-weight: 500;">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $filter_date_from; ?>" style="border-radius: 10px;">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="date_to" class="form-label" style="color: var(--primary-blue); font-weight: 500;">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $filter_date_to; ?>" style="border-radius: 10px;">
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" style="border-radius: 10px; padding: 10px 25px;">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="hod_leave_management.php" class="btn btn-secondary ms-2" style="border-radius: 10px; padding: 10px 25px;">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Leave Applications Table -->
            <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-list"></i> Leave Applications (<?php echo $total_records; ?> total)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background: var(--light-blue);">
                                    <tr>
                                        <th style="color: var(--primary-blue); font-weight: 600;">ID</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Student Details</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Leave Details</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Contact</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Status</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Applied On</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $row['id']; ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($row['student_name'] ?? ''); ?></strong><br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-id-card"></i> <?php echo htmlspecialchars(str_replace('@srkrec.edu.in', '', $row['student_email'] ?? '')); ?><br>
                                                        <i class="fas fa-graduation-cap"></i> <?php echo $sections[$row['section']] ?? ($row['section'] ?? ''); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-info" style="border-radius: 8px;">
                                                        <?php echo ucfirst($row['leave_type'] ?? ''); ?> Leave
                                                    </span><br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> <?php echo isset($row['start_date']) ? date('d M Y', strtotime($row['start_date'])) : ''; ?> - <?php echo isset($row['end_date']) ? date('d M Y', strtotime($row['end_date'])) : ''; ?><br>
                                                        <i class="fas fa-clock"></i> <?php echo $row['total_days'] ?? ''; ?> day(s)
                                                    </small><br>
                                                    <small class="text-muted">
                                                        <strong>Reason:</strong> <?php echo htmlspecialchars(substr($row['reason'] ?? '', 0, 50)) . (isset($row['reason']) && strlen($row['reason']) > 50 ? '...' : ''); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['contact_number'] ?? ''); ?><br>
                                                        <?php if (!empty($row['parent_contact'])): ?>
                                                            <i class="fas fa-phone"></i> Parent: <?php echo htmlspecialchars($row['parent_contact']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_icon = '';
                                                switch ($row['status'] ?? '') {
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
                                                <span class="badge <?php echo $status_class; ?>" style="border-radius: 8px;">
                                                    <i class="<?php echo $status_icon; ?>"></i> <?php echo ucfirst($row['status'] ?? ''); ?>
                                                </span>
                                                <?php if (!empty($row['hod_remarks'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($row['hod_remarks']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($row['applied_at'])); ?><br>
                                                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($row['applied_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-success btn-sm mb-1" style="border-radius: 8px;" 
                                                            onclick="showActionModal(<?php echo $row['id']; ?>, 'approve', '<?php echo addslashes($row['student_name'] ?? ''); ?>')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" style="border-radius: 8px;" 
                                                            onclick="showActionModal(<?php echo $row['id']; ?>, 'reject', '<?php echo addslashes($row['student_name'] ?? ''); ?>')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php else: ?>
                                                    <small class="text-muted">
                                                        Processed by: <?php echo htmlspecialchars($row['processed_by']); ?><br>
                                                        On: <?php echo date('d M Y H:i', strtotime($row['processed_at'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Leave applications pagination">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&section=<?php echo $filter_section; ?>&date_from=<?php echo $filter_date_from; ?>&date_to=<?php echo $filter_date_to; ?>" style="border-radius: 8px;">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                            <h5 style="color: var(--gray-medium);">No leave applications found</h5>
                            <p class="text-muted">No applications match the current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 8px 32px rgba(7,101,147,0.15);">
                <div class="modal-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="actionModalLabel" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-cog"></i> Process Leave Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body p-4">
                        <input type="hidden" name="application_id" id="application_id">
                        <input type="hidden" name="action" id="action_type">
                        
                        <div class="mb-3">
                            <label for="student_name_display" class="form-label" style="color: var(--primary-blue); font-weight: 500;">Student Name</label>
                            <input type="text" id="student_name_display" class="form-control" readonly style="border-radius: 10px; background-color: #f8f9fa;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label" style="color: var(--primary-blue); font-weight: 500;">Remarks (Optional)</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3" style="border-radius: 10px;" 
                                      placeholder="Add any remarks or comments about this application"></textarea>
                        </div>
                        
                        <div class="alert alert-info" style="border-radius: 10px;">
                            <i class="fas fa-info-circle"></i> <strong>Note:</strong> This action cannot be undone. Please review the application carefully before proceeding.
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e3e6f0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="confirmBtn" style="border-radius: 10px;">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <style>
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                display: block;
                width: 100%;
            }
            .table {
                min-width: 600px;
            }
        }
    </style>
    <script>
        function showActionModal(applicationId, action, studentName) {
            document.getElementById('application_id').value = applicationId;
            document.getElementById('action_type').value = action;
            document.getElementById('student_name_display').value = studentName;
            const modal = new bootstrap.Modal(document.getElementById('actionModal'));
            const confirmBtn = document.getElementById('confirmBtn');
            if (action === 'approve') {
                confirmBtn.className = 'btn btn-success';
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Approve Application';
            } else {
                confirmBtn.className = 'btn btn-danger';
                confirmBtn.innerHTML = '<i class="fas fa-times"></i> Reject Application';
            }
            modal.show();
        }
        // Submit the modal form when confirm button is clicked
        document.addEventListener('DOMContentLoaded', function() {
            var confirmBtn = document.getElementById('confirmBtn');
            var modalForm = document.querySelector('#actionModal form');
            if (confirmBtn && modalForm) {
                confirmBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modalForm.submit();
                });
            }
        });
    </script>
</body>
</html>