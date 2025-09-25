<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

// Simple query to test if all students are included
$test_query = "
    SELECT 
        s.student_id,
        s.name as student_name,
        c.year,
        c.branch,
        c.section,
        c.academic_year,
        h.name as house_name,
        COALESCE(appreciation_points, 0) as appreciation_points,
        COALESCE(participation_points, 0) as participation_points,
        COALESCE(organizer_points, 0) as organizer_points,
        COALESCE(winner_points, 0) as winner_points,
        COALESCE(penalty_points, 0) as penalty_points,
        (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
         COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) as total_points
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN houses h ON s.hid = h.hid
    LEFT JOIN (
        SELECT student_id, SUM(points) as appreciation_points 
        FROM appreciations GROUP BY student_id
    ) a ON s.student_id = a.student_id
    LEFT JOIN (
        SELECT student_id, SUM(points) as participation_points 
        FROM participants GROUP BY student_id
    ) p ON s.student_id = p.student_id
    LEFT JOIN (
        SELECT student_id, SUM(points) as organizer_points 
        FROM organizers GROUP BY student_id
    ) o ON s.student_id = o.student_id
    LEFT JOIN (
        SELECT student_id, SUM(points) as winner_points 
        FROM winners GROUP BY student_id
    ) w ON s.student_id = w.student_id
    LEFT JOIN (
        SELECT student_id, SUM(points) as penalty_points 
        FROM penalties GROUP BY student_id
    ) pen ON s.student_id = pen.student_id
    WHERE s.class_id IS NOT NULL
    ORDER BY c.year DESC, c.branch, c.section, total_points DESC, s.name
";

$result = mysqli_query($conn, $test_query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

// CSV Export for testing
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="test_all_students_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
fputcsv($output, [
    'Student ID', 'Student Name', 'Year', 'Branch', 'Section', 'Academic Year', 'House',
    'Appreciation Points', 'Participation Points', 'Organizer Points', 
    'Winner Points', 'Penalty Points', 'Total Points'
]);

// Write data with section grouping
$current_section = '';
$section_count = 0;
$zero_points_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $section_key = 'Year ' . $row['year'] . ' - ' . $row['branch'] . ' - Section ' . $row['section'];
    
    if ($section_key !== $current_section) {
        if ($current_section !== '') {
            fputcsv($output, ['', '', '', '', '', '', 'SECTION TOTAL STUDENTS:', $section_count, 'ZERO POINTS:', $zero_points_count]);
            fputcsv($output, ['']); // Empty row
        }
        
        fputcsv($output, ['=== ' . $section_key . ' ===']);
        $current_section = $section_key;
        $section_count = 0;
        $zero_points_count = 0;
    }
    
    $section_count++;
    if ($row['total_points'] == 0) {
        $zero_points_count++;
    }
    
    fputcsv($output, $row);
}

// Add final section totals
if ($current_section !== '') {
    fputcsv($output, ['', '', '', '', '', '', 'SECTION TOTAL STUDENTS:', $section_count, 'ZERO POINTS:', $zero_points_count]);
}

fclose($output);
mysqli_close($conn);
exit();
?>