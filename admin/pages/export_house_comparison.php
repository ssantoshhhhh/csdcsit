<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// House comparison query with detailed breakdown
$query = "
    SELECT 
        h.name as house_name,
        COUNT(DISTINCT s.student_id) as total_students,
        
        -- Appreciation stats
        COALESCE(app_stats.total_appreciations, 0) as total_appreciations,
        COALESCE(app_stats.appreciation_points, 0) as appreciation_points,
        COALESCE(ROUND(app_stats.appreciation_points / NULLIF(COUNT(DISTINCT s.student_id), 0), 2), 0) as avg_appreciation_per_student,
        
        -- Participation stats
        COALESCE(part_stats.total_participations, 0) as total_participations,
        COALESCE(part_stats.participation_points, 0) as participation_points,
        COALESCE(ROUND(part_stats.participation_points / NULLIF(COUNT(DISTINCT s.student_id), 0), 2), 0) as avg_participation_per_student,
        
        -- Organizer stats
        COALESCE(org_stats.total_organized, 0) as total_organized,
        COALESCE(org_stats.organizer_points, 0) as organizer_points,
        
        -- Winner stats
        COALESCE(win_stats.total_wins, 0) as total_wins,
        COALESCE(win_stats.winner_points, 0) as winner_points,
        COALESCE(win_stats.first_positions, 0) as first_positions,
        COALESCE(win_stats.second_positions, 0) as second_positions,
        COALESCE(win_stats.third_positions, 0) as third_positions,
        
        -- Penalty stats
        COALESCE(pen_stats.total_penalties, 0) as total_penalties,
        COALESCE(pen_stats.penalty_points, 0) as penalty_points,
        
        -- Overall totals
        (COALESCE(app_stats.appreciation_points, 0) + COALESCE(part_stats.participation_points, 0) + 
         COALESCE(org_stats.organizer_points, 0) + COALESCE(win_stats.winner_points, 0) - 
         COALESCE(pen_stats.penalty_points, 0)) as total_points,
         
        COALESCE(ROUND((COALESCE(app_stats.appreciation_points, 0) + COALESCE(part_stats.participation_points, 0) + 
                       COALESCE(org_stats.organizer_points, 0) + COALESCE(win_stats.winner_points, 0) - 
                       COALESCE(pen_stats.penalty_points, 0)) / NULLIF(COUNT(DISTINCT s.student_id), 0), 2), 0) as avg_points_per_student,
        
        -- Activity rate (percentage of students who have any points)
        COALESCE(ROUND((active_students.active_count * 100.0 / NULLIF(COUNT(DISTINCT s.student_id), 0)), 2), 0) as activity_rate_percent
        
    FROM houses h
    LEFT JOIN students s ON h.hid = s.hid
    
    -- Appreciation statistics
    LEFT JOIN (
        SELECT s2.hid, 
               COUNT(*) as total_appreciations,
               SUM(a.points) as appreciation_points
        FROM appreciations a 
        JOIN students s2 ON a.student_id = s2.student_id 
        GROUP BY s2.hid
    ) app_stats ON h.hid = app_stats.hid
    
    -- Participation statistics
    LEFT JOIN (
        SELECT s3.hid, 
               COUNT(*) as total_participations,
               SUM(p.points) as participation_points
        FROM participants p 
        JOIN students s3 ON p.student_id = s3.student_id 
        GROUP BY s3.hid
    ) part_stats ON h.hid = part_stats.hid
    
    -- Organizer statistics
    LEFT JOIN (
        SELECT s4.hid, 
               COUNT(*) as total_organized,
               SUM(o.points) as organizer_points
        FROM organizers o 
        JOIN students s4 ON o.student_id = s4.student_id 
        GROUP BY s4.hid
    ) org_stats ON h.hid = org_stats.hid
    
    -- Winner statistics with position breakdown
    LEFT JOIN (
        SELECT s5.hid, 
               COUNT(*) as total_wins,
               SUM(w.points) as winner_points,
               SUM(CASE WHEN w.position = 1 THEN 1 ELSE 0 END) as first_positions,
               SUM(CASE WHEN w.position = 2 THEN 1 ELSE 0 END) as second_positions,
               SUM(CASE WHEN w.position = 3 THEN 1 ELSE 0 END) as third_positions
        FROM winners w 
        JOIN students s5 ON w.student_id = s5.student_id 
        GROUP BY s5.hid
    ) win_stats ON h.hid = win_stats.hid
    
    -- Penalty statistics
    LEFT JOIN (
        SELECT s6.hid, 
               COUNT(*) as total_penalties,
               SUM(pen.points) as penalty_points
        FROM penalties pen 
        JOIN students s6 ON pen.student_id = s6.student_id 
        GROUP BY s6.hid
    ) pen_stats ON h.hid = pen_stats.hid
    
    -- Active students count (students with any points)
    LEFT JOIN (
        SELECT s7.hid,
               COUNT(DISTINCT s7.student_id) as active_count
        FROM students s7
        WHERE s7.student_id IN (
            SELECT student_id FROM appreciations
            UNION
            SELECT student_id FROM participants
            UNION
            SELECT student_id FROM organizers
            UNION
            SELECT student_id FROM winners
            UNION
            SELECT student_id FROM penalties
        )
        GROUP BY s7.hid
    ) active_students ON h.hid = active_students.hid
    
    GROUP BY h.hid, h.name
    ORDER BY total_points DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'Houses_Detailed_Comparison_Analysis_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'House Name', 'Total Students', 'Total Appreciations', 'Appreciation Points', 'Avg Appreciation per Student',
        'Total Participations', 'Participation Points', 'Avg Participation per Student',
        'Total Organized', 'Organizer Points', 'Total Wins', 'Winner Points',
        'First Positions', 'Second Positions', 'Third Positions',
        'Total Penalties', 'Penalty Points', 'Total Points', 'Avg Points per Student', 'Activity Rate %'
    ]);
    
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
    
    // Title
    echo '<h2>Detailed House Comparison Report</h2>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<p>This report provides comprehensive statistics for all houses including participation rates, performance metrics, and detailed breakdowns.</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling and grouping
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    echo '<th rowspan="2">House Name</th>';
    echo '<th rowspan="2">Total Students</th>';
    echo '<th colspan="3" style="background-color: #28a745;">Appreciations</th>';
    echo '<th colspan="3" style="background-color: #17a2b8;">Participations</th>';
    echo '<th colspan="2" style="background-color: #ffc107;">Organizer</th>';
    echo '<th colspan="5" style="background-color: #fd7e14;">Winners</th>';
    echo '<th colspan="2" style="background-color: #dc3545;">Penalties</th>';
    echo '<th colspan="2" style="background-color: #6f42c1;">Overall</th>';
    echo '<th rowspan="2" style="background-color: #20c997;">Activity Rate %</th>';
    echo '</tr>';
    
    echo '<tr style="background-color: #6c757d; color: white; font-size: 11px;">';
    echo '<th>Total</th><th>Points</th><th>Avg/Student</th>';
    echo '<th>Total</th><th>Points</th><th>Avg/Student</th>';
    echo '<th>Total</th><th>Points</th>';
    echo '<th>Total</th><th>Points</th><th>1st</th><th>2nd</th><th>3rd</th>';
    echo '<th>Total</th><th>Points</th>';
    echo '<th>Total Points</th><th>Avg/Student</th>';
    echo '</tr>';
    
    // Write data with color coding
    $rank = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $row_color = '#FFFFFF';
        if ($rank == 1) $row_color = '#fff3cd'; // Gold tint for 1st
        elseif ($rank == 2) $row_color = '#f8f9fa'; // Silver tint for 2nd
        elseif ($rank == 3) $row_color = '#ffeaa7'; // Bronze tint for 3rd
        
        echo '<tr style="background-color: ' . $row_color . ';">';
        
        // House name with rank
        echo '<td style="font-weight: bold;">' . htmlspecialchars($row['house_name']) . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . $row['total_students'] . '</td>';
        
        // Appreciations (green theme)
        echo '<td style="color: #28a745; text-align: center;">' . $row['total_appreciations'] . '</td>';
        echo '<td style="color: #28a745; font-weight: bold; text-align: center;">' . $row['appreciation_points'] . '</td>';
        echo '<td style="color: #28a745; text-align: center;">' . $row['avg_appreciation_per_student'] . '</td>';
        
        // Participations (blue theme)
        echo '<td style="color: #17a2b8; text-align: center;">' . $row['total_participations'] . '</td>';
        echo '<td style="color: #17a2b8; font-weight: bold; text-align: center;">' . $row['participation_points'] . '</td>';
        echo '<td style="color: #17a2b8; text-align: center;">' . $row['avg_participation_per_student'] . '</td>';
        
        // Organizer (yellow theme)
        echo '<td style="color: #ffc107; text-align: center;">' . $row['total_organized'] . '</td>';
        echo '<td style="color: #ffc107; font-weight: bold; text-align: center;">' . $row['organizer_points'] . '</td>';
        
        // Winners (orange theme)
        echo '<td style="color: #fd7e14; text-align: center;">' . $row['total_wins'] . '</td>';
        echo '<td style="color: #fd7e14; font-weight: bold; text-align: center;">' . $row['winner_points'] . '</td>';
        echo '<td style="color: #fd7e14; text-align: center; font-weight: bold;">' . $row['first_positions'] . '</td>';
        echo '<td style="color: #fd7e14; text-align: center;">' . $row['second_positions'] . '</td>';
        echo '<td style="color: #fd7e14; text-align: center;">' . $row['third_positions'] . '</td>';
        
        // Penalties (red theme)
        echo '<td style="color: #dc3545; text-align: center;">' . $row['total_penalties'] . '</td>';
        echo '<td style="color: #dc3545; font-weight: bold; text-align: center;">' . ($row['penalty_points'] > 0 ? '-' . $row['penalty_points'] : '0') . '</td>';
        
        // Overall (purple theme)
        echo '<td style="color: #6f42c1; font-weight: bold; font-size: 14px; text-align: center;">' . $row['total_points'] . '</td>';
        echo '<td style="color: #6f42c1; text-align: center;">' . $row['avg_points_per_student'] . '</td>';
        
        // Activity rate
        echo '<td style="color: #20c997; font-weight: bold; text-align: center;">' . $row['activity_rate_percent'] . '%</td>';
        
        echo '</tr>';
        $rank++;
    }
    
    echo '</table>';
    echo '</body></html>';
}

mysqli_close($conn);
exit();
?>