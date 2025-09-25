<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "./connect.php";

// Handle table selection via POST and store in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
    $_SESSION['table'] = $_POST['table'];
    // If this is from HOD dashboard, also store date range
    if (isset($_POST['hod_view']) && $_POST['hod_view'] == '1') {
        $_SESSION['start_date'] = $_POST['start_date'] ?? '';
        $_SESSION['end_date'] = $_POST['end_date'] ?? '';
        $_SESSION['hod_view'] = true;
    }
    header('Location: student_attendance.php');
    exit();
}

// Include database helper
include './db_migration_helper.php';

// Use session table or default to first available class
$selected_class_id = isset($_SESSION['table']) ? $_SESSION['table'] : null;
if (!$selected_class_id) {
    $classes = $db_helper->getAllClasses();
    $selected_class_id = array_key_first($classes);
}

// Get date range parameters
$start_date = isset($_SESSION['start_date']) ? $_SESSION['start_date'] : (isset($_GET['start_date']) ? $_GET['start_date'] : '');
$end_date = isset($_SESSION['end_date']) ? $_SESSION['end_date'] : (isset($_GET['end_date']) ? $_GET['end_date'] : '');
$is_hod_view = isset($_SESSION['hod_view']) ? $_SESSION['hod_view'] : (isset($_GET['hod_view']) ? $_GET['hod_view'] : false);

// Clear session data after using it
if ($is_hod_view && isset($_SESSION['hod_view'])) {
    unset($_SESSION['start_date']);
    unset($_SESSION['end_date']);
    unset($_SESSION['hod_view']);
}

// Get search and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc' ? 'asc' : 'desc';
$next_sort = $sort === 'desc' ? 'asc' : 'desc';

// Build date range conditions
$date_conditions = "";
if (!empty($start_date) && !empty($end_date)) {
    $date_conditions = " AND attendance_date BETWEEN '" . mysqli_real_escape_string($conn, $start_date) . "' AND '" . mysqli_real_escape_string($conn, $end_date) . "'";
} elseif (!empty($start_date)) {
    $date_conditions = " AND attendance_date >= '" . mysqli_real_escape_string($conn, $start_date) . "'";
} elseif (!empty($end_date)) {
    $date_conditions = " AND attendance_date <= '" . mysqli_real_escape_string($conn, $end_date) . "'";
}

// Get students for the selected class
$students = $db_helper->getStudentsByClass($selected_class_id);

// Calculate attendance points for each student
$student_stats = [];
foreach ($students as $student) {
    $student_id = $student['student_id'];
    $reg_no = str_replace('@srkrec.edu.in', '', $student['email']); // Extract reg_no from email
    
    // Get attendance statistics for this student
    $stats = $db_helper->getStudentAttendanceStats($student_id, $start_date, $end_date);
    
    if ($stats) {
        $student_stats[] = [
            'register_no' => $reg_no,
            'total_sessions' => $stats['total_sessions'],
            'present_sessions' => $stats['present_sessions'],
            'attendance_percentage' => $stats['attendance_percentage']
        ];
    }
}

// Filter by search if provided
if (!empty($search)) {
    $student_stats = array_filter($student_stats, function($student) use ($search) {
        return stripos($student['register_no'], $search) !== false;
    });
}

// Sort the results
usort($student_stats, function($a, $b) use ($sort) {
    if ($sort === 'asc') {
        return $a['present_sessions'] - $b['present_sessions'];
    } else {
        return $b['present_sessions'] - $a['present_sessions'];
    }
});

// For leaderboard: get all students sorted by points desc
$leaderboard = $student_stats;
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php include "./head.php"; ?>
    <title>Student Attendance Points - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-users"></i> Student Attendance Points</h2>
            <p>View and search student attendance records</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <?php if ($is_hod_view): ?>
                        <li class="breadcrumb-item"><a href="hod_dashboard.php" style="color: var(--primary-blue);">HOD Dashboard</a></li>
                    <?php else: ?>
                    <li class="breadcrumb-item"><a href="index.php" style="color: var(--primary-blue);">Home</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">Student Attendance Points</li>
                </ol>
            </nav>
            
            <?php if ($is_hod_view): ?>
                <!-- HOD Navigation -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                            <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                                <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                                    <i class="fas fa-chart-line"></i> HOD View - Date Range Filter
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 style="color: var(--primary-blue); font-weight: 600;">
                                            <i class="fas fa-calendar"></i> Selected Date Range:
                                        </h6>
                                        <p class="mb-2">
                                            <?php if (!empty($start_date) && !empty($end_date)): ?>
                                                <span class="badge bg-info" style="border-radius: 8px;">
                                                    <i class="fas fa-calendar-day"></i> 
                                                    <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>
                                                </span>
                                            <?php elseif (!empty($start_date)): ?>
                                                <span class="badge bg-info" style="border-radius: 8px;">
                                                    <i class="fas fa-calendar-day"></i> 
                                                    From: <?php echo date('d M Y', strtotime($start_date)); ?>
                                                </span>
                                            <?php elseif (!empty($end_date)): ?>
                                                <span class="badge bg-info" style="border-radius: 8px;">
                                                    <i class="fas fa-calendar-day"></i> 
                                                    Until: <?php echo date('d M Y', strtotime($end_date)); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary" style="border-radius: 8px;">
                                                    <i class="fas fa-calendar"></i> All Records
                                                </span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="hod_dashboard.php" class="btn btn-primary">
                                            <i class="fas fa-arrow-left"></i> Back to HOD Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="post" action="attendance_leaderboard.php" style="display:inline;">
                        <button type="submit" name="table" value="<?php echo htmlspecialchars($table); ?>" class="btn btn-warning">
                            <i class="fas fa-trophy"></i> Leaderboard
                        </button>
                        <?php if ($is_hod_view && (!empty($start_date) || !empty($end_date))): ?>
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            <input type="hidden" name="hod_view" value="1">
                        <?php endif; ?>
                    </form>
                    <form method="GET" action="" style="display:inline-block; margin-left: 10px;">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_class_id); ?>">
                        <?php if ($is_hod_view && (!empty($start_date) || !empty($end_date))): ?>
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            <input type="hidden" name="hod_view" value="1">
                        <?php endif; ?>
                        <button type="submit" name="sort" value="<?php echo $next_sort; ?>" class="btn btn-primary">
                            Sort by Points: <?php echo $sort === 'desc' ? 'Descending' : 'Ascending'; ?>
                            <i class="fas fa-sort-<?php echo $sort === 'desc' ? 'down' : 'up'; ?>"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Search Section -->
            <div class="card mb-4" style="border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 15px;">
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_class_id); ?>">
                        <?php if ($is_hod_view && (!empty($start_date) || !empty($end_date))): ?>
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            <input type="hidden" name="hod_view" value="1">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" placeholder="Search by Student Registration Number..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 25px; padding: 12px 20px;">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" style="border-radius: 25px; padding: 12px 20px;">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="card" style="border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-table"></i> Attendance Records
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: var(--light-blue);">
                                <tr>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600;">Registration No</th>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600; text-align: center;">Attendance Points</th>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600; text-align: center;">Attendance %</th>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600; text-align: center;">Total Sessions</th>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600; text-align: center;">Present Sessions</th>
                                    <th style="border: none; padding: 15px; color: var(--primary-blue); font-weight: 600; text-align: center;">Absent Sessions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($student_stats)): ?>
                                    <?php foreach ($student_stats as $row): ?>
                                        <tr style="border-bottom: 1px solid #f0f0f0;">
                                            <td style="padding: 15px;">
                                                <form method="post" action="student_attendance_detail.php" style="display:inline;">
                                                    <input type="hidden" name="reg_no" value="<?php echo htmlspecialchars($row['register_no']); ?>">
                                                    <button type="submit" class="btn btn-link p-0" style="color: var(--primary-blue); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($row['register_no']); ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td style="padding: 15px; text-align: center; font-weight: 600;"><?php echo $row['present_sessions']; ?></td>
                                            <td style="padding: 15px; text-align: center;">
                                                <span class="badge <?php echo $row['attendance_percentage'] >= 75 ? 'bg-success' : ($row['attendance_percentage'] >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <?php echo $row['attendance_percentage']; ?>%
                                                </span>
                                            </td>
                                            <td style="padding: 15px; text-align: center;"><?php echo $row['total_sessions']; ?></td>
                                            <td style="padding: 15px; text-align: center;"><?php echo $row['present_sessions']; ?></td>
                                            <td style="padding: 15px; text-align: center;"><?php echo $row['total_sessions'] - $row['present_sessions']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                                            <p class="text-muted">No students found matching your search criteria.</p>
                                            <?php if ($search): ?>
                                                <p class="text-muted">Try searching with a different registration number.</p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <style>
        /* Mobile Responsive Improvements for Student Attendance Page */
        @media (max-width: 768px) {
            .btn {
                padding: 10px 15px;
                font-size: 13px;
                margin-bottom: 10px;
            }
            
            .btn + .btn {
                margin-left: 0;
            }
            
            .form-control {
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
            
            .card-body {
                padding: 20px 15px;
            }
            
            .card-header {
                padding: 15px 20px;
            }
            
            .row {
                margin-left: -10px;
                margin-right: -10px;
            }
            
            .col-md-8,
            .col-md-4 {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            /* Stack buttons on mobile */
            .d-flex {
                flex-direction: column;
            }
            
            .d-flex .btn {
                width: 100%;
                margin-left: 0 !important;
            }
        }
        
        @media (max-width: 576px) {
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            .form-control {
                padding: 10px 12px;
                font-size: 16px;
            }
            
            .table th,
            .table td {
                padding: 8px 6px;
                font-size: 12px;
            }
            
            .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
            }
            
            .card-body {
                padding: 15px 10px;
            }
            
            .card-header {
                padding: 12px 15px;
            }
            
            .page-title h2 {
                font-size: 20px;
            }
            
            .page-title p {
                font-size: 14px;
            }
            
            /* Hide less important columns on very small screens */
            .table-responsive {
                font-size: 11px;
            }
        }
        
        /* Landscape orientation fixes */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                padding: 20px 0;
            }
        }
        
        /* Touch target improvements */
        .btn-link {
            min-height: 44px;
            display: flex;
            align-items: center;
        }
        
        /* Accessibility improvements */
        .btn-link:focus {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .table th {
                background: var(--primary-blue) !important;
                color: white !important;
            }
            
            .btn-link {
                color: var(--primary-blue) !important;
                text-decoration: underline !important;
            }
        }
    </style>
</body>
</html> 