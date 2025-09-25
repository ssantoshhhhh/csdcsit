<?php
include './connect.php';

// Check attendance data for the first student that has data
$result = mysqli_query($conn, "SELECT student_id FROM student_attendance GROUP BY student_id LIMIT 1");
$student = mysqli_fetch_assoc($result);
$student_id = $student['student_id'];

echo "Checking attendance for student: $student_id\n";

// Check recent attendance data for this student
$result4 = mysqli_query($conn, "SELECT attendance_date, session, status FROM student_attendance WHERE student_id = '$student_id' ORDER BY attendance_date DESC LIMIT 30");
echo "\nRecent attendance for $student_id:\n";
while ($row = mysqli_fetch_assoc($result4)) {
    echo "Date: {$row['attendance_date']}, Session: {$row['session']}, Status: {$row['status']}\n";
}

// Check attendance by month for this student
$result5 = mysqli_query($conn, "SELECT DATE_FORMAT(attendance_date, '%Y-%m') as month, COUNT(*) as records FROM student_attendance WHERE student_id = '$student_id' GROUP BY DATE_FORMAT(attendance_date, '%Y-%m') ORDER BY month DESC");
echo "\nMonthly attendance for $student_id:\n";
while ($row = mysqli_fetch_assoc($result5)) {
    echo "Month: {$row['month']}, Records: {$row['records']}\n";
}
?>
