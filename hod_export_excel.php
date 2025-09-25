<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';
include './db_migration_helper.php';

// Get parameters
$class_id = $_POST['table'] ?? $_GET['table'] ?? '';
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? '';

// Validate class_id
$all_classes = $db_helper->getAllClasses();
if (!isset($all_classes[$class_id])) {
    die('Invalid section selected');
}

$section_name = $all_classes[$class_id];

// Build date conditions
$date_conditions = "";
if (!empty($start_date) && !empty($end_date)) {
    $date_conditions = " AND sa.attendance_date BETWEEN '" . mysqli_real_escape_string($conn, $start_date) . "' AND '" . mysqli_real_escape_string($conn, $end_date) . "'";
} elseif (!empty($start_date)) {
    $date_conditions = " AND sa.attendance_date >= '" . mysqli_real_escape_string($conn, $start_date) . "'";
} elseif (!empty($end_date)) {
    $date_conditions = " AND sa.attendance_date <= '" . mysqli_real_escape_string($conn, $end_date) . "'";
}

// Get all unique dates and sessions for the header
$header_query = "
    SELECT DISTINCT sa.attendance_date, sa.session
    FROM student_attendance sa
    JOIN students s ON sa.student_id = s.student_id
    WHERE s.class_id = ?
    $date_conditions
    ORDER BY sa.attendance_date, sa.session
";
$stmt = mysqli_prepare($conn, $header_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$header_result = mysqli_stmt_get_result($stmt);

if (!$header_result) {
    die('Error fetching header data: ' . mysqli_error($conn));
}

// Build header structure
$dates = [];
while ($row = mysqli_fetch_assoc($header_result)) {
    $date = $row['attendance_date'];
    $session = $row['session'];
    
    if (!isset($dates[$date])) {
        $dates[$date] = [];
    }
    $dates[$date][] = $session;
}

// Get all students in the class
$students = $db_helper->getStudentsByClass($class_id);

// Get attendance data for all students in the class
$attendance_query = "
    SELECT s.student_id, s.name as student_name, s.email, sa.attendance_date, sa.session, sa.status, f.name as faculty_name
    FROM student_attendance sa
    JOIN students s ON sa.student_id = s.student_id
    LEFT JOIN faculty f ON sa.faculty_id = f.faculty_id
    WHERE s.class_id = ?
    $date_conditions
    ORDER BY s.student_id, sa.attendance_date, sa.session
";
$stmt = mysqli_prepare($conn, $attendance_query);
mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);
$attendance_result = mysqli_stmt_get_result($stmt);

if (!$attendance_result) {
    die('Error fetching attendance data: ' . mysqli_error($conn));
}

// Build attendance matrix
$attendance_matrix = [];
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $key = $row['student_id'] . '_' . $row['attendance_date'] . '_' . $row['session'];
    $attendance_matrix[$key] = [
        'status' => $row['status'],
        'faculty_name' => $row['faculty_name']
    ];
}

// Create CSV file
$filename = str_replace('/', '-', $section_name) . '_Attendance_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Create header rows
$header_row1 = ['S.No', 'Regn No', 'Student Name'];
$header_row2 = ['', '', ''];

foreach ($dates as $date => $date_sessions) {
    $header_row1[] = date('d/m/Y', strtotime($date));
    if (count($date_sessions) > 1) {
        $header_row1[] = '';
    }
    $header_row2[] = in_array('Forenoon', $date_sessions) ? 'FN' : '';
    if (count($date_sessions) > 1) {
        $header_row2[] = in_array('Afternoon', $date_sessions) ? 'AN' : '';
    }
}
fputcsv($output, $header_row1);
fputcsv($output, $header_row2);

// Create data rows
$sno = 1;
foreach ($students as $student) {
    $reg_no = str_replace('@srkrec.edu.in', '', $student['email']);
    $data_row = [$sno, $reg_no, $student['name']];
    
    foreach ($dates as $date => $date_sessions) {
        foreach (['Forenoon', 'Afternoon'] as $session) {
            if (in_array($session, $date_sessions)) {
                $key = $student['student_id'] . '_' . $date . '_' . $session;
                if (isset($attendance_matrix[$key])) {
                    $status = $attendance_matrix[$key]['status'];
                    $data_row[] = ($status == 'Present') ? '1' : '0';
                } else {
                    $data_row[] = 'N/A';
                }
            }
        }
    }
    
    fputcsv($output, $data_row);
    $sno++;
}

fclose($output);

exit();
?>