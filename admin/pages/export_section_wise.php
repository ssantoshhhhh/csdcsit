<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';
$class_id = $_GET['class_id'] ?? '';
$type = $_GET['type'] ?? 'detailed';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// Validate class_id
if (empty($class_id) || !is_numeric($class_id)) {
    die('Invalid class ID specified');
}

// Get class information
$class_query = "SELECT * FROM classes WHERE class_id = " . intval($class_id);
$class_result = mysqli_query($conn, $class_query);
$class_info = mysqli_fetch_assoc($class_result);

if (!$class_info) {
    die('Class not found');
}

$section_name = $class_info['branch'] . '_Year' . $class_info['year'] . '_Section' . $class_info['section'] . '_' . $class_info['academic_year'];

if ($type === 'detailed') {
    // Detailed report with all transactions for specific section
    $query = "
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            'Appreciation' as point_type,
            a.points,
            a.reason,
            e.title as event_title,
            DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position,
            f.faculty_name as created_by
        FROM appreciations a
        JOIN students s ON a.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON a.event_id = e.event_id
        LEFT JOIN faculties f ON a.created_by = f.faculty_id
        WHERE s.class_id = $class_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            'Participation' as point_type,
            p.points,
            CONCAT('Participated in event - Status: ', p.participation_status) as reason,
            e.title as event_title,
            DATE_FORMAT(p.registered_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position,
            'System' as created_by
        FROM participants p
        JOIN students s ON p.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON p.event_id = e.event_id
        WHERE p.points > 0 AND s.class_id = $class_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            'Organizer' as point_type,
            o.points,
            CONCAT('Organized event as ', o.role) as reason,
            e.title as event_title,
            DATE_FORMAT(o.assigned_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position,
            'System' as created_by
        FROM organizers o
        JOIN students s ON o.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON o.event_id = e.event_id
        WHERE o.points > 0 AND s.class_id = $class_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            'Winner' as point_type,
            w.points,
            CONCAT('Won event') as reason,
            e.title as event_title,
            DATE_FORMAT(w.announced_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            CONCAT('Position ', w.position) as position,
            'System' as created_by
        FROM winners w
        JOIN students s ON w.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON w.event_id = e.event_id
        WHERE s.class_id = $class_id
        
        UNION ALL
        
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            'Penalty' as point_type,
            -pen.points as points,
            pen.reason,
            e.title as event_title,
            DATE_FORMAT(pen.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position,
            f.faculty_name as created_by
        FROM penalties pen
        JOIN students s ON pen.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON pen.event_id = e.event_id
        LEFT JOIN faculties f ON pen.created_by = f.faculty_id
        WHERE s.class_id = $class_id
        
        ORDER BY date_awarded DESC, student_name
    ";
} else {
    // Summary report with totals per student for specific section
    $query = "
        SELECT 
            s.student_id,
            s.name as student_name,
            s.branch,
            s.section,
            c.year,
            c.semester,
            c.academic_year,
            h.name as house_name,
            COALESCE(appreciation_points, 0) as appreciation_points,
            COALESCE(participation_points, 0) as participation_points,
            COALESCE(organizer_points, 0) as organizer_points,
            COALESCE(winner_points, 0) as winner_points,
            COALESCE(penalty_points, 0) as penalty_points,
            (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
             COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) as total_points,
            CASE 
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 50 THEN 'Excellent'
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 30 THEN 'Good'
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 10 THEN 'Average'
                ELSE 'Needs Improvement'
            END as performance_grade,
            (SELECT COUNT(*) FROM appreciations a2 WHERE a2.student_id = s.student_id) as total_appreciations,
            (SELECT COUNT(*) FROM participants p2 WHERE p2.student_id = s.student_id) as total_participations,
            (SELECT COUNT(*) FROM organizers o2 WHERE o2.student_id = s.student_id) as total_organized,
            (SELECT COUNT(*) FROM winners w2 WHERE w2.student_id = s.student_id) as total_wins,
            (SELECT COUNT(*) FROM penalties pen2 WHERE pen2.student_id = s.student_id) as total_penalties,
            RANK() OVER (ORDER BY (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                                  COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) DESC) as class_rank
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
        WHERE s.class_id = $class_id
        ORDER BY total_points DESC, s.name
    ";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'section_' . strtolower($section_name) . '_' . $type . '_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    if ($type === 'detailed') {
        fputcsv($output, [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 'Academic Year',
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position', 'Created By'
        ]);
    } else {
        fputcsv($output, [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 'Academic Year',
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
            'Total Appreciations', 'Total Participations', 'Total Organized', 'Total Wins', 'Total Penalties', 'Class Rank'
        ]);
    }
    
    // Write data
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} else {
    // Enhanced Excel Export
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="ProgId" content="Excel.Sheet"><meta name="Generator" content="Microsoft Excel 11"></head>';
    echo '<body>';
    
    // Title and section info
    echo '<h2>Section ' . ucfirst($type) . ' Report</h2>';
    echo '<h3>' . $class_info['branch'] . ' - Year ' . $class_info['year'] . ' - Section ' . $class_info['section'] . '</h3>';
    echo '<p>Academic Year: ' . $class_info['academic_year'] . ' | Semester: ' . $class_info['semester'] . '</p>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    if ($type === 'detailed') {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 'Academic Year',
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position', 'Created By'
        ];
    } else {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 'Academic Year',
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
            'Total Appreciations', 'Total Participations', 'Total Organized', 'Total Wins', 'Total Penalties', 'Class Rank'
        ];
    }
    
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data with formatting
    $row_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row_count++;
        $bg_color = ($row_count % 2 == 0) ? '#F2F2F2' : '#FFFFFF';
        
        // Special highlighting for top performers in summary
        if ($type === 'summary' && isset($row['class_rank'])) {
            if ($row['class_rank'] == 1) $bg_color = '#fff3cd'; // Gold for 1st
            elseif ($row['class_rank'] == 2) $bg_color = '#f8f9fa'; // Silver for 2nd
            elseif ($row['class_rank'] == 3) $bg_color = '#ffeaa7'; // Bronze for 3rd
        }
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        foreach ($row as $key => $cell) {
            $style = '';
            
            // Special formatting
            if ($type === 'summary') {
                if (strpos($key, 'points') !== false && $key !== 'penalty_points' && $key !== 'total_points') {
                    $style = 'color: green; font-weight: bold;';
                } elseif ($key === 'penalty_points' && $cell > 0) {
                    $style = 'color: red; font-weight: bold;';
                } elseif ($key === 'total_points') {
                    $style = 'color: blue; font-weight: bold; font-size: 14px;';
                } elseif ($key === 'performance_grade') {
                    switch ($cell) {
                        case 'Excellent':
                            $style = 'color: green; font-weight: bold;';
                            break;
                        case 'Good':
                            $style = 'color: blue; font-weight: bold;';
                            break;
                        case 'Average':
                            $style = 'color: orange; font-weight: bold;';
                            break;
                        case 'Needs Improvement':
                            $style = 'color: red; font-weight: bold;';
                            break;
                    }
                } elseif ($key === 'class_rank') {
                    $style = 'font-weight: bold; text-align: center;';
                }
            } elseif ($type === 'detailed') {
                if ($key === 'points') {
                    $style = $cell < 0 ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;';
                } elseif ($key === 'point_type') {
                    switch ($cell) {
                        case 'Penalty':
                            $style = 'color: red; font-weight: bold;';
                            break;
                        case 'Winner':
                            $style = 'color: gold; font-weight: bold;';
                            break;
                        case 'Appreciation':
                            $style = 'color: green; font-weight: bold;';
                            break;
                    }
                }
            }
            
            echo '<td style="' . $style . '">' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}

mysqli_close($conn);
exit();
?>