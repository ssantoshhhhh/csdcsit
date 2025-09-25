<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_logged_in']) || !$_SESSION['faculty_logged_in']) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

include './connect.php';

// Get parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$faculty_id = $_SESSION['faculty_id'] ?? null;

if (!$faculty_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faculty ID not found']);
    exit();
}

// Get faculty's assigned sections
$faculty_query = "SELECT class_id FROM faculties WHERE faculty_id = ?";
$stmt = mysqli_prepare($conn, $faculty_query);
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$faculty_result = mysqli_stmt_get_result($stmt);
$faculty_data = mysqli_fetch_assoc($faculty_result);

$assigned_sections = [];
if ($faculty_data && !empty($faculty_data['class_id'])) {
    $assigned_sections = array_filter(explode(',', $faculty_data['class_id']), function($section) {
        return !empty(trim($section));
    });
}

$attendance_data = [];

if (!empty($assigned_sections)) {
    $class_ids_in = implode(',', array_map('intval', $assigned_sections));
    
    // Calculate date range for the requested month
    $start_date = sprintf('%04d-%02d-01', $year, $month);
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $calendar_query = "SELECT DISTINCT sa.attendance_date, sa.session 
                      FROM student_attendance sa 
                      JOIN students s ON sa.student_id = s.student_id 
                      WHERE s.class_id IN ($class_ids_in) 
                      AND sa.attendance_date BETWEEN '$start_date' AND '$end_date'
                      AND sa.faculty_id = $faculty_id
                      ORDER BY sa.attendance_date, sa.session";
    
    $calendar_result = mysqli_query($conn, $calendar_query);
    if ($calendar_result) {
        while ($row = mysqli_fetch_assoc($calendar_result)) {
            $date = $row['attendance_date'];
            $session = $row['session'];
            
            if (!isset($attendance_data[$date])) {
                $attendance_data[$date] = [];
            }
            $attendance_data[$date][] = $session;
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'attendance_data' => $attendance_data,
    'month' => $month,
    'year' => $year,
    'total_days' => count($attendance_data)
]);
?>
