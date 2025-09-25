<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';
$house_id = $_GET['house_id'] ?? 'all';
$type = $_GET['type'] ?? 'detailed';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// Validate type
if (!in_array($type, ['detailed', 'summary'])) {
    die('Invalid type specified');
}

// Get house information
$house_condition = '';
$house_name_for_file = 'All_Houses';
$house_display_name = 'All Houses';

if ($house_id !== 'all' && is_numeric($house_id)) {
    $house_result = mysqli_query($conn, "SELECT name FROM houses WHERE hid = " . intval($house_id));
    if ($house_row = mysqli_fetch_assoc($house_result)) {
        $house_condition = " AND s.hid = " . intval($house_id);
        $house_name_for_file = str_replace(' ', '_', $house_row['name']);
        $house_display_name = $house_row['name'];
    } else {
        die('Invalid house ID specified');
    }
}

if ($type === 'detailed') {
    // Detailed report with all transactions for specific house(s)
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
            DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position
        FROM appreciations a
        JOIN students s ON a.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON a.event_id = e.event_id
        WHERE 1=1 $house_condition
        
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
            CONCAT('Participated in event - Status: ', p.participation_status) as reason,
            e.title as event_title,
            DATE_FORMAT(p.registered_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position
        FROM participants p
        JOIN students s ON p.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON p.event_id = e.event_id
        WHERE p.points > 0 $house_condition
        
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
            DATE_FORMAT(o.assigned_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position
        FROM organizers o
        JOIN students s ON o.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON o.event_id = e.event_id
        WHERE o.points > 0 $house_condition
        
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
            CONCAT('Won event') as reason,
            e.title as event_title,
            DATE_FORMAT(w.announced_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            CONCAT('Position ', w.position) as position
        FROM winners w
        JOIN students s ON w.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON w.event_id = e.event_id
        WHERE 1=1 $house_condition
        
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
            DATE_FORMAT(pen.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded,
            'N/A' as position
        FROM penalties pen
        JOIN students s ON pen.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        LEFT JOIN events e ON pen.event_id = e.event_id
        WHERE 1=1 $house_condition
        
        ORDER BY date_awarded DESC, house_name, student_name
    ";
} else {
    // Summary report with totals per student for specific house(s)
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
            (SELECT COUNT(*) FROM penalties pen2 WHERE pen2.student_id = s.student_id) as total_penalties
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
        WHERE 1=1 $house_condition
        ORDER BY total_points DESC, s.name
    ";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = $house_name_for_file . '_House_Points_' . ucfirst($type) . '_Report_' . date('Y-m-d_H-i-s');

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
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position'
        ]);
    } else {
        fputcsv($output, [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
            'Total Appreciations', 'Total Participations', 'Total Organized', 'Total Wins', 'Total Penalties'
        ]);
    }
    
    // Write data
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} else {
    // Enhanced Excel Export with better formatting
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="ProgId" content="Excel.Sheet"><meta name="Generator" content="Microsoft Excel 11"></head>';
    echo '<body>';
    
    // Title
    echo '<h2>' . $house_display_name . ' - House Points ' . ucfirst($type) . ' Report</h2>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    if ($type === 'detailed') {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position'
        ];
    } else {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
            'Total Appreciations', 'Total Participations', 'Total Organized', 'Total Wins', 'Total Penalties'
        ];
    }
    
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data with alternating row colors and special formatting
    $row_count = 0;
    $current_house = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $row_count++;
        
        // Different background for different houses
        if ($house_id === 'all' && $row['house_name'] !== $current_house) {
            $current_house = $row['house_name'];
            $house_colors = [
                'PRUDHVI' => '#E8F5E8',
                'VAYU' => '#E8F0FF', 
                'AGNI' => '#FFE8E8',
                'AAKASH' => '#FFF8E8',
                'JAL' => '#E8F8FF'
            ];
            $bg_color = $house_colors[$current_house] ?? '#F2F2F2';
        } else {
            $bg_color = ($row_count % 2 == 0) ? '#F9F9F9' : '#FFFFFF';
        }
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        foreach ($row as $key => $cell) {
            $style = '';
            
            // Special formatting for different columns
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
                } elseif ($key === 'house_name') {
                    $style = 'font-weight: bold;';
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