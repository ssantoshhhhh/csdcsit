<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';
$type = $_GET['type'] ?? 'detailed';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// Validate type
if (!in_array($type, ['detailed', 'summary'])) {
    die('Invalid type specified');
}

if ($type === 'detailed') {
    // Detailed report with all transactions
    $query = "
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            h.name as house_name,
            'Appreciation' as point_type,
            a.points,
            a.reason,
            e.title as event_title,
            a.created_at as date_awarded
        FROM appreciations a
        JOIN students s ON a.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON a.event_id = e.event_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            h.name as house_name,
            'Participation' as point_type,
            p.points,
            CONCAT('Participated in event') as reason,
            e.title as event_title,
            p.registered_at as date_awarded
        FROM participants p
        JOIN students s ON p.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON p.event_id = e.event_id
        WHERE p.points > 0
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            h.name as house_name,
            'Organizer' as point_type,
            o.points,
            CONCAT('Organized event as ', o.role) as reason,
            e.title as event_title,
            o.assigned_at as date_awarded
        FROM organizers o
        JOIN students s ON o.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON o.event_id = e.event_id
        WHERE o.points > 0
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            h.name as house_name,
            'Winner' as point_type,
            w.points,
            CONCAT('Position ', w.position, ' in event') as reason,
            e.title as event_title,
            w.announced_at as date_awarded
        FROM winners w
        JOIN students s ON w.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON w.event_id = e.event_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            h.name as house_name,
            'Penalty' as point_type,
            -pen.points as points,
            pen.reason,
            e.title as event_title,
            pen.created_at as date_awarded
        FROM penalties pen
        JOIN students s ON pen.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON pen.event_id = e.event_id
        
        ORDER BY date_awarded DESC
    ";
} else {
    // Summary report with totals per student
    $query = "
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
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
        ORDER BY c.year DESC, c.branch, c.section, c.academic_year DESC, total_points DESC, s.name
    ";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'All_Students_House_Points_' . ucfirst($type) . '_Report_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    if ($type === 'detailed') {
        fputcsv($output, [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded'
        ]);
    } else {
        fputcsv($output, [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points'
        ]);
    }
    
    // Write data
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} else {
    // Excel Export using simple HTML table method
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<table border="1">';
    
    // Write headers
    echo '<tr>';
    if ($type === 'detailed') {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded'
        ];
    } else {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points'
        ];
    }
    
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
}

mysqli_close($conn);
exit();
?>