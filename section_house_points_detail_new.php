<?php
session_start();
include './connect.php';
include './db_migration_helper.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$db_helper = new DatabaseMigrationHelper($conn);

$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    header('Location: section_house_points.php');
    exit();
}

// Get class information
$class_query = "SELECT * FROM classes WHERE class_id = ?";
$stmt = mysqli_prepare($conn, $class_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$class_result = mysqli_stmt_get_result($stmt);
$class_data = mysqli_fetch_assoc($class_result);

if (!$class_data) {
    header('Location: section_house_points.php');
    exit();
}

$section_name = $class_data['year'] . '/4 ' . strtoupper($class_data['branch']) . '-' . strtoupper($class_data['section']);

// Get students in this class with house points
$students_query = "
    SELECT s.student_id, s.name, h.name as house_name
    FROM students s
    LEFT JOIN houses h ON s.hid = h.hid
    WHERE s.class_id = ?
    ORDER BY s.student_id ASC
";
$stmt = mysqli_prepare($conn, $students_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

$students = [];
while ($student = mysqli_fetch_assoc($students_result)) {
    // Calculate total house points from all sources (same as houses_dashboard.php)
    $house_points = 0;

    // Points from participants
    $participants_query = "SELECT SUM(points) as points FROM participants WHERE student_id = ?";
    $participants_stmt = mysqli_prepare($conn, $participants_query);
    mysqli_stmt_bind_param($participants_stmt, "s", $student['student_id']);
    mysqli_stmt_execute($participants_stmt);
    $participants_result = mysqli_stmt_get_result($participants_stmt);
    $participants_data = mysqli_fetch_assoc($participants_result);
    $house_points += (int)($participants_data['points'] ?? 0);

    // Points from winners
    $winners_query = "SELECT SUM(points) as points FROM winners WHERE student_id = ?";
    $winners_stmt = mysqli_prepare($conn, $winners_query);
    mysqli_stmt_bind_param($winners_stmt, "s", $student['student_id']);
    mysqli_stmt_execute($winners_stmt);
    $winners_result = mysqli_stmt_get_result($winners_stmt);
    $winners_data = mysqli_fetch_assoc($winners_result);
    $house_points += (int)($winners_data['points'] ?? 0);

    // Points from organizers
    $organizers_query = "SELECT SUM(points) as points FROM organizers WHERE student_id = ?";
    $organizers_stmt = mysqli_prepare($conn, $organizers_query);
    mysqli_stmt_bind_param($organizers_stmt, "s", $student['student_id']);
    mysqli_stmt_execute($organizers_stmt);
    $organizers_result = mysqli_stmt_get_result($organizers_stmt);
    $organizers_data = mysqli_fetch_assoc($organizers_result);
    $house_points += (int)($organizers_data['points'] ?? 0);

    // Points from appreciations
    $appreciations_query = "SELECT SUM(points) as points FROM appreciations WHERE student_id = ?";
    $appreciations_stmt = mysqli_prepare($conn, $appreciations_query);
    mysqli_stmt_bind_param($appreciations_stmt, "s", $student['student_id']);
    mysqli_stmt_execute($appreciations_stmt);
    $appreciations_result = mysqli_stmt_get_result($appreciations_stmt);
    $appreciations_data = mysqli_fetch_assoc($appreciations_result);
    $house_points += (int)($appreciations_data['points'] ?? 0);

    // Subtract penalties
    $penalties_query = "SELECT SUM(points) as points FROM penalties WHERE student_id = ?";
    $penalties_stmt = mysqli_prepare($conn, $penalties_query);
    mysqli_stmt_bind_param($penalties_stmt, "s", $student['student_id']);
    mysqli_stmt_execute($penalties_stmt);
    $penalties_result = mysqli_stmt_get_result($penalties_stmt);
    $penalties_data = mysqli_fetch_assoc($penalties_result);
    $house_points -= (int)($penalties_data['points'] ?? 0);

    // Get attendance stats and convert percentage to points (round to whole number)
    $attendance_stats = $db_helper->getStudentAttendanceStats($student['student_id']);
    $attendance_percentage = $attendance_stats['attendance_percentage'] ?? 0;
    $attendance_points = round($attendance_percentage); // Round to whole number

    $student['house_points'] = $house_points;
    $student['attendance_points'] = $attendance_points;
    $students[] = $student;
}

$student_count = count($students);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title><?php echo htmlspecialchars($section_name); ?> - House Points</title>
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="page-title">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-trophy"></i> <?php echo htmlspecialchars($section_name); ?> - House Points</h2>
                    <p><?php echo $student_count; ?> students in this section</p>
                </div>
                <div>
                    <a href="section_house_points.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Sections
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Section Info Card -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-info-circle"></i> Section Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-graduation-cap" style="font-size: 2rem; color: var(--primary-blue); margin-bottom: 10px;"></i>
                                <h4 class="text-primary"><?php echo $class_data['year']; ?>/4</h4>
                                <p class="text-muted mb-0">Year</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-code-branch" style="font-size: 2rem; color: var(--success); margin-bottom: 10px;"></i>
                                <h4 class="text-success"><?php echo strtoupper($class_data['branch']); ?></h4>
                                <p class="text-muted mb-0">Branch</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-layer-group" style="font-size: 2rem; color: var(--info); margin-bottom: 10px;"></i>
                                <h4 class="text-info"><?php echo strtoupper($class_data['section']); ?></h4>
                                <p class="text-muted mb-0">Section</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt" style="font-size: 2rem; color: var(--warning); margin-bottom: 10px;"></i>
                                <h4 class="text-warning"><?php echo $class_data['semester']; ?></h4>
                                <p class="text-muted mb-0">Semester</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <?php if (!empty($students)): ?>
                <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                    <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Student House Points</h5>
                            <button onclick="downloadPDF()" class="btn btn-light btn-sm">
                                <i class="fas fa-download"></i> Download PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Toggle Attendance Points -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="toggleAttendance" checked onchange="toggleAttendancePoints()">
                                <label class="form-check-label" for="toggleAttendance">
                                    Show Attendance Points
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="housePointsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Registration Number</th>
                                        <th>Student Name</th>
                                        <th>House</th>
                                        <th>House Points</th>
                                        <th class="attendance-column">Attendance Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td>
                                                <?php if ($student['house_name']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($student['house_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo htmlspecialchars($student['house_points']); ?> pts
                                                </span>
                                            </td>
                                            <td class="attendance-column">
                                                <span class="badge bg-success">
                                                    <?php echo htmlspecialchars($student['attendance_points']); ?> pts
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 20px;"></i>
                    <h4 class="text-muted">No students found</h4>
                    <p class="text-muted">This section doesn't have any students yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <style>
        .info-item {
            padding: 15px;
        }

        .info-item h4 {
            margin: 10px 0;
            font-weight: 600;
        }

        .table th {
            font-weight: 600;
        }

        .badge {
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
        }

        @media print {
            /* Hide everything except the table */
            body > *:not(.main-content) {
                display: none !important;
            }

            .main-content > .container > .card:first-child {
                display: none !important;
            }

            .main-content > .container > .card:nth-child(2) {
                display: block !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-body > .form-check {
                display: none !important;
            }

            .card-header {
                display: none !important;
            }

            .table-responsive {
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .table {
                font-size: 12px !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .badge {
                border: 1px solid #000 !important;
                background: white !important;
                color: black !important;
            }

            /* Hide attendance column if checkbox is unchecked */
            .attendance-column[style*="display: none"] {
                display: none !important;
            }
        }
    </style>

    <script>
        function downloadPDF() {
            // Hide attendance points column if hidden before printing
            const attendanceVisible = document.getElementById('toggleAttendance').checked;
            const attendanceCols = document.querySelectorAll('.attendance-column');
            attendanceCols.forEach(col => {
                col.style.display = attendanceVisible ? '' : 'none';
            });
            window.print();
        }

        function toggleAttendancePoints() {
            const checkbox = document.getElementById('toggleAttendance');
            const attendanceCols = document.querySelectorAll('.attendance-column');
            attendanceCols.forEach(col => {
                col.style.display = checkbox.checked ? '' : 'none';
            });
        }
    </script>
</body>
