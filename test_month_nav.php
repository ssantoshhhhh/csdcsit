<?php
include './connect.php';

// Test the new month functionality
$student_id = '22B91A6201'; // Using a student ID we know has data

echo "Testing month navigation for student: $student_id\n\n";

// Get available months for this student
$available_months_query = "
    SELECT DISTINCT DATE_FORMAT(attendance_date, '%Y-%m') as month,
           DATE_FORMAT(attendance_date, '%M %Y') as month_name,
           COUNT(*) as records
    FROM student_attendance 
    WHERE student_id = ?
    GROUP BY DATE_FORMAT(attendance_date, '%Y-%m')
    ORDER BY month DESC
";
$stmt = mysqli_prepare($conn, $available_months_query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "Available months:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "- {$row['month_name']} ({$row['month']}): {$row['records']} records\n";
}

// Test attendance for a specific month
$test_month = '2025-08';
echo "\nTesting attendance for $test_month:\n";

$monthly_attendance_query = "
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_sessions
    FROM student_attendance 
    WHERE student_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?
";
$stmt2 = mysqli_prepare($conn, $monthly_attendance_query);
mysqli_stmt_bind_param($stmt2, "ss", $student_id, $test_month);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$monthly_data = mysqli_fetch_assoc($result2);

$total_sessions = $monthly_data['total_sessions'] ?? 0;
$present_sessions = $monthly_data['present_sessions'] ?? 0;
$attendance_percentage = $total_sessions > 0 ? round(($present_sessions / $total_sessions) * 100, 2) : 0;

echo "Total sessions: $total_sessions\n";
echo "Present sessions: $present_sessions\n";
echo "Attendance percentage: $attendance_percentage%\n";
?>
