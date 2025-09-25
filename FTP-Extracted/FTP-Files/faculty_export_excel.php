<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['faculty_logged_in']) || !$_SESSION['faculty_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Get faculty ID and assigned sections
$faculty_id = $_SESSION['faculty_id'] ?? null;
if (!$faculty_id) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get faculty assigned sections
$faculty_query = "SELECT assigned_sections FROM faculty_credentials WHERE id = ?";
$stmt = mysqli_prepare($conn, $faculty_query);
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$faculty_result = mysqli_stmt_get_result($stmt);
$faculty_data = mysqli_fetch_assoc($faculty_result);

if (!$faculty_data || empty($faculty_data['assigned_sections'])) {
    die('No sections assigned to this faculty member');
}

$assigned_sections = explode(',', $faculty_data['assigned_sections']);
$assigned_sections = array_map('trim', $assigned_sections);
$assigned_sections = array_filter($assigned_sections);

// Get parameters
$table = $_POST['table'] ?? $_GET['table'] ?? '';
$start_date = $_POST['start_date'] ?? $_GET['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? $_GET['end_date'] ?? '';
$debug = isset($_GET['debug']) && $_GET['debug'] == 1;

// Debug output
if ($debug) {
    echo "Debug Mode Enabled<br>";
    echo "Table: $table<br>";
    echo "Start Date: $start_date<br>";
    echo "End Date: $end_date<br>";
    echo "Assigned Sections: " . implode(', ', $assigned_sections) . "<br><br>";
}

// Validate table - only allow faculty's assigned sections
if (!in_array($table, $assigned_sections)) {
    die('You are not authorized to access this section');
}

// Validate table exists
$valid_tables = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];

if (!array_key_exists($table, $valid_tables)) {
    die('Invalid table selected');
}

// Check if table exists and has data
$table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
if (mysqli_num_rows($table_check) == 0) {
    die("Table '$table' does not exist");
}

$table_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM `$table`");
$total_records = mysqli_fetch_assoc($table_count)['total'];

if ($debug) {
    echo "Table exists with $total_records total records<br><br>";
}

// First, get ALL students from the section (regardless of attendance)
$all_students_query = "SELECT DISTINCT register_no FROM `$table` ORDER BY register_no";
$all_students_result = mysqli_query($conn, $all_students_query);

if (!$all_students_result) {
    die('Error fetching students: ' . mysqli_error($conn));
}

$all_students = [];
while ($row = mysqli_fetch_assoc($all_students_result)) {
    $all_students[] = $row['register_no'];
}

if ($debug) {
    echo "Total Students Found: " . count($all_students) . "<br>";
    echo "Students: " . implode(', ', array_slice($all_students, 0, 10)) . (count($all_students) > 10 ? '...' : '') . "<br><br>";
}

if (empty($all_students)) {
    die('No students found in this section');
}

// Get ALL dates from the table (not just from attendance records)
$all_dates_query = "SELECT DISTINCT attendance_date FROM `$table` ORDER BY attendance_date";
$all_dates_result = mysqli_query($conn, $all_dates_query);

if (!$all_dates_result) {
    die('Error fetching dates: ' . mysqli_error($conn));
}

$all_dates = [];
while ($row = mysqli_fetch_assoc($all_dates_result)) {
    $all_dates[] = $row['attendance_date'];
}

if ($debug) {
    echo "Total Dates Found: " . count($all_dates) . "<br>";
    echo "Dates: " . implode(', ', array_slice($all_dates, 0, 10)) . (count($all_dates) > 10 ? '...' : '') . "<br><br>";
}

if (empty($all_dates)) {
    die('No dates found in this section');
}

// Get all sessions available
$sessions_query = "SELECT DISTINCT session FROM `$table` ORDER BY session";
$sessions_result = mysqli_query($conn, $sessions_query);

if (!$sessions_result) {
    die('Error fetching sessions: ' . mysqli_error($conn));
}

$available_sessions = [];
while ($row = mysqli_fetch_assoc($sessions_result)) {
    $available_sessions[] = $row['session'];
}

if ($debug) {
    echo "Available Sessions: " . implode(', ', $available_sessions) . "<br><br>";
}

// Get faculty information for students
$faculty_info_query = "
    SELECT DISTINCT register_no, faculty_name
    FROM `$table` 
    ORDER BY register_no
";
$faculty_info_result = mysqli_query($conn, $faculty_info_query);

$student_faculty = [];
if ($faculty_info_result) {
    while ($row = mysqli_fetch_assoc($faculty_info_result)) {
        $student_faculty[$row['register_no']] = $row['faculty_name'];
    }
}

if ($debug) {
    echo "Faculty info for " . count($student_faculty) . " students<br><br>";
}

// Get ALL attendance data
$attendance_query = "
    SELECT register_no, attendance_date, session, status
    FROM `$table` 
    ORDER BY register_no, attendance_date, session
";
$attendance_result = mysqli_query($conn, $attendance_query);

if (!$attendance_result) {
    die('Error fetching attendance data: ' . mysqli_error($conn));
}

// Build attendance matrix
$attendance_matrix = [];
$attendance_count = 0;
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $key = $row['register_no'] . '_' . $row['attendance_date'] . '_' . $row['session'];
    $attendance_matrix[$key] = $row['status'];
    $attendance_count++;
}

if ($debug) {
    echo "Total attendance records: $attendance_count<br>";
    echo "Attendance matrix keys: " . count($attendance_matrix) . "<br>";
    if (!empty($attendance_matrix)) {
        $sample_keys = array_slice(array_keys($attendance_matrix), 0, 5);
        echo "Sample keys: " . implode(', ', $sample_keys) . (count($attendance_matrix) > 5 ? '...' : '') . "<br>";
    }
    echo "<br>";
}

// If not in debug mode, start CSV output
if (!$debug) {
    // Create CSV file
    $filename = $valid_tables[$table] . '_Attendance_Faculty_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Create header rows
    $header_row1 = ['S.No', 'Regn No', 'Faculty Name'];
    $header_row2 = ['', '', ''];

    foreach ($all_dates as $date) {
        $header_row1[] = date('d/m/Y', strtotime($date));
        $header_row1[] = '';
        
        // Add session sub-headers for each date
        if (in_array('Forenoon', $available_sessions)) {
            $header_row2[] = 'FN';
        }
        if (in_array('Afternoon', $available_sessions)) {
            $header_row2[] = 'AN';
        }
    }

    fputcsv($output, $header_row1);
    fputcsv($output, $header_row2);

    // Create data rows for ALL students
    $sno = 1;
    foreach ($all_students as $student) {
        $faculty_name = isset($student_faculty[$student]) ? $student_faculty[$student] : 'N/A';
        $data_row = [$sno, $student, $faculty_name];
        
        foreach ($all_dates as $date) {
            // Add Forenoon session data
            if (in_array('Forenoon', $available_sessions)) {
                $key = $student . '_' . $date . '_Forenoon';
                $status = isset($attendance_matrix[$key]) ? $attendance_matrix[$key] : '';
                
                if ($status === '') {
                    $data_row[] = 'N/A';
                } elseif ($status == 1) {
                    $data_row[] = '1';
                } else {
                    $data_row[] = '0';
                }
            }
            
            // Add Afternoon session data
            if (in_array('Afternoon', $available_sessions)) {
                $key = $student . '_' . $date . '_Afternoon';
                $status = isset($attendance_matrix[$key]) ? $attendance_matrix[$key] : '';
                
                if ($status === '') {
                    $data_row[] = 'N/A';
                } elseif ($status == 1) {
                    $data_row[] = '1';
                } else {
                    $data_row[] = '0';
                }
            }
        }
        
        fputcsv($output, $data_row);
        $sno++;
    }

    fclose($output);
    exit();
} else {
    // Debug mode - show summary
    echo "<h3>Export Summary</h3>";
    echo "<p><strong>Section:</strong> " . $valid_tables[$table] . "</p>";
    echo "<p><strong>Total Students:</strong> " . count($all_students) . "</p>";
    echo "<p><strong>Total Dates:</strong> " . count($all_dates) . "</p>";
    echo "<p><strong>Available Sessions:</strong> " . implode(', ', $available_sessions) . "</p>";
    echo "<p><strong>Total Attendance Records:</strong> " . $attendance_count . "</p>";
    echo "<p><strong>CSV Columns:</strong> " . (3 + (count($all_dates) * count($available_sessions))) . "</p>";
    echo "<p><strong>CSV Rows:</strong> " . (2 + count($all_students)) . "</p>";
    
    echo "<h4>Sample Data:</h4>";
    echo "<p><strong>First 5 Students:</strong> " . implode(', ', array_slice($all_students, 0, 5)) . "</p>";
    echo "<p><strong>First 5 Dates:</strong> " . implode(', ', array_slice($all_dates, 0, 5)) . "</p>";
    
    echo "<p><a href='faculty_export_excel.php?table=$table'>Download CSV (without debug)</a></p>";
}
?> 