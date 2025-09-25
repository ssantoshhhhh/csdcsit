<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';
$branch = $_GET['branch'] ?? '';
$type = $_GET['type'] ?? 'summary';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// Validate branch
if (empty($branch)) {
    die('Invalid branch specified');
}

// Branch summary with section breakdown
$query = "
    SELECT 
        c.class_id,
        c.academic_year,
        c.year,
        c.branch,
        c.section,
        COUNT(DISTINCT s.student_id) as total_students,
        COUNT(DISTINCT active_s.student_id) as active_students,
        ROUND((COUNT(DISTINCT active_s.student_id) * 100.0 / NULLIF(COUNT(DISTINCT s.student_id), 0)), 2) as activity_rate,
        
        -- Points breakdown
        COALESCE(app_stats.total_appreciations, 0) as total_appreciations,
        COALESCE(app_stats.appreciation_points, 0) as appreciation_points,
        COALESCE(part_stats.total_participations, 0) as total_participations,
        COALESCE(part_stats.participation_points, 0) as participation_points,
        COALESCE(org_stats.total_organized, 0) as total_organized,
        COALESCE(org_stats.organizer_points, 0) as organizer_points,
        COALESCE(win_stats.total_wins, 0) as total_wins,
        COALESCE(win_stats.winner_points, 0) as winner_points,
        COALESCE(pen_stats.total_penalties, 0) as total_penalties,
        COALESCE(pen_stats.penalty_points, 0) as penalty_points,
        
        -- Calculated totals
        (COALESCE(app_stats.appreciation_points, 0) + COALESCE(part_stats.participation_points, 0) + 
         COALESCE(org_stats.organizer_points, 0) + COALESCE(win_stats.winner_points, 0) - 
         COALESCE(pen_stats.penalty_points, 0)) as total_points,
         
        ROUND((COALESCE(app_stats.appreciation_points, 0) + COALESCE(part_stats.participation_points, 0) + 
               COALESCE(org_stats.organizer_points, 0) + COALESCE(win_stats.winner_points, 0) - 
               COALESCE(pen_stats.penalty_points, 0)) / NULLIF(COUNT(DISTINCT s.student_id), 0), 2) as avg_points_per_student,
        
        -- Top performer info
        top_student.student_name as top_performer,
        top_student.top_points as top_performer_points
        
    FROM classes c
    LEFT JOIN students s ON c.class_id = s.class_id
    
    -- Active students
    LEFT JOIN (
        SELECT DISTINCT s7.student_id, s7.class_id
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
    ) active_s ON c.class_id = active_s.class_id
    
    -- Appreciation statistics
    LEFT JOIN (
        SELECT s2.class_id, 
               COUNT(*) as total_appreciations,
               SUM(a.points) as appreciation_points
        FROM appreciations a 
        JOIN students s2 ON a.student_id = s2.student_id 
        GROUP BY s2.class_id
    ) app_stats ON c.class_id = app_stats.class_id
    
    -- Participation statistics
    LEFT JOIN (
        SELECT s3.class_id, 
               COUNT(*) as total_participations,
               SUM(p.points) as participation_points
        FROM participants p 
        JOIN students s3 ON p.student_id = s3.student_id 
        GROUP BY s3.class_id
    ) part_stats ON c.class_id = part_stats.class_id
    
    -- Organizer statistics
    LEFT JOIN (
        SELECT s4.class_id, 
               COUNT(*) as total_organized,
               SUM(o.points) as organizer_points
        FROM organizers o 
        JOIN students s4 ON o.student_id = s4.student_id 
        GROUP BY s4.class_id
    ) org_stats ON c.class_id = org_stats.class_id
    
    -- Winner statistics
    LEFT JOIN (
        SELECT s5.class_id, 
               COUNT(*) as total_wins,
               SUM(w.points) as winner_points
        FROM winners w 
        JOIN students s5 ON w.student_id = s5.student_id 
        GROUP BY s5.class_id
    ) win_stats ON c.class_id = win_stats.class_id
    
    -- Penalty statistics
    LEFT JOIN (
        SELECT s6.class_id, 
               COUNT(*) as total_penalties,
               SUM(pen.points) as penalty_points
        FROM penalties pen 
        JOIN students s6 ON pen.student_id = s6.student_id 
        GROUP BY s6.class_id
    ) pen_stats ON c.class_id = pen_stats.class_id
    
    -- Top student per section
    LEFT JOIN (
        SELECT 
            s8.class_id,
            s8.name as student_name,
            (COALESCE(app_p.points, 0) + COALESCE(part_p.points, 0) + 
             COALESCE(org_p.points, 0) + COALESCE(win_p.points, 0) - COALESCE(pen_p.points, 0)) as top_points,
            ROW_NUMBER() OVER (PARTITION BY s8.class_id ORDER BY 
                (COALESCE(app_p.points, 0) + COALESCE(part_p.points, 0) + 
                 COALESCE(org_p.points, 0) + COALESCE(win_p.points, 0) - COALESCE(pen_p.points, 0)) DESC) as rn
        FROM students s8
        LEFT JOIN (SELECT student_id, SUM(points) as points FROM appreciations GROUP BY student_id) app_p ON s8.student_id = app_p.student_id
        LEFT JOIN (SELECT student_id, SUM(points) as points FROM participants GROUP BY student_id) part_p ON s8.student_id = part_p.student_id
        LEFT JOIN (SELECT student_id, SUM(points) as points FROM organizers GROUP BY student_id) org_p ON s8.student_id = org_p.student_id
        LEFT JOIN (SELECT student_id, SUM(points) as points FROM winners GROUP BY student_id) win_p ON s8.student_id = win_p.student_id
        LEFT JOIN (SELECT student_id, SUM(points) as points FROM penalties GROUP BY student_id) pen_p ON s8.student_id = pen_p.student_id
    ) top_student ON c.class_id = top_student.class_id AND top_student.rn = 1
    
    WHERE c.branch = '" . mysqli_real_escape_string($conn, $branch) . "'
    GROUP BY c.class_id, c.academic_year, c.year, c.branch, c.section
    ORDER BY c.academic_year DESC, c.year DESC, c.section
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'branch_' . strtolower($branch) . '_summary_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'Academic Year', 'Year', 'Branch', 'Section', 'Total Students', 'Active Students', 'Activity Rate %',
        'Total Appreciations', 'Appreciation Points', 'Total Participations', 'Participation Points',
        'Total Organized', 'Organizer Points', 'Total Wins', 'Winner Points',
        'Total Penalties', 'Penalty Points', 'Total Points', 'Avg Points per Student',
        'Top Performer', 'Top Performer Points'
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
    echo '<h2>' . strtoupper($branch) . ' Branch Performance Summary</h2>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<p>This report shows section-wise performance breakdown for the ' . $branch . ' branch.</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling and grouping
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    echo '<th rowspan="2">Academic Year</th>';
    echo '<th rowspan="2">Year</th>';
    echo '<th rowspan="2">Branch</th>';
    echo '<th rowspan="2">Section</th>';
    echo '<th colspan="3" style="background-color: #6c757d;">Students</th>';
    echo '<th colspan="2" style="background-color: #28a745;">Appreciations</th>';
    echo '<th colspan="2" style="background-color: #17a2b8;">Participations</th>';
    echo '<th colspan="2" style="background-color: #ffc107;">Organizer</th>';
    echo '<th colspan="2" style="background-color: #fd7e14;">Winners</th>';
    echo '<th colspan="2" style="background-color: #dc3545;">Penalties</th>';
    echo '<th colspan="2" style="background-color: #6f42c1;">Overall</th>';
    echo '<th colspan="2" style="background-color: #20c997;">Top Performer</th>';
    echo '</tr>';
    
    echo '<tr style="background-color: #6c757d; color: white; font-size: 11px;">';
    echo '<th>Total</th><th>Active</th><th>Rate %</th>';
    echo '<th>Count</th><th>Points</th>';
    echo '<th>Count</th><th>Points</th>';
    echo '<th>Count</th><th>Points</th>';
    echo '<th>Count</th><th>Points</th>';
    echo '<th>Count</th><th>Points</th>';
    echo '<th>Total</th><th>Avg/Student</th>';
    echo '<th>Name</th><th>Points</th>';
    echo '</tr>';
    
    // Write data with color coding
    $section_rank = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $row_color = '#FFFFFF';
        if ($section_rank == 1) $row_color = '#fff3cd'; // Gold tint for best section
        elseif ($row['activity_rate'] >= 80) $row_color = '#d4edda'; // Green for high activity
        elseif ($row['activity_rate'] < 50) $row_color = '#f8d7da'; // Red for low activity
        
        echo '<tr style="background-color: ' . $row_color . ';">';
        
        // Basic info
        echo '<td style="font-weight: bold;">' . htmlspecialchars($row['academic_year']) . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">Year ' . $row['year'] . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . htmlspecialchars($row['branch']) . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">Section ' . htmlspecialchars($row['section']) . '</td>';
        
        // Student stats
        echo '<td style="text-align: center; font-weight: bold;">' . $row['total_students'] . '</td>';
        echo '<td style="text-align: center;">' . $row['active_students'] . '</td>';
        $activity_color = $row['activity_rate'] >= 80 ? 'green' : ($row['activity_rate'] >= 60 ? 'orange' : 'red');
        echo '<td style="text-align: center; color: ' . $activity_color . '; font-weight: bold;">' . $row['activity_rate'] . '%</td>';
        
        // Appreciations
        echo '<td style="color: #28a745; text-align: center;">' . $row['total_appreciations'] . '</td>';
        echo '<td style="color: #28a745; font-weight: bold; text-align: center;">' . $row['appreciation_points'] . '</td>';
        
        // Participations
        echo '<td style="color: #17a2b8; text-align: center;">' . $row['total_participations'] . '</td>';
        echo '<td style="color: #17a2b8; font-weight: bold; text-align: center;">' . $row['participation_points'] . '</td>';
        
        // Organizer
        echo '<td style="color: #ffc107; text-align: center;">' . $row['total_organized'] . '</td>';
        echo '<td style="color: #ffc107; font-weight: bold; text-align: center;">' . $row['organizer_points'] . '</td>';
        
        // Winners
        echo '<td style="color: #fd7e14; text-align: center;">' . $row['total_wins'] . '</td>';
        echo '<td style="color: #fd7e14; font-weight: bold; text-align: center;">' . $row['winner_points'] . '</td>';
        
        // Penalties
        echo '<td style="color: #dc3545; text-align: center;">' . $row['total_penalties'] . '</td>';
        echo '<td style="color: #dc3545; font-weight: bold; text-align: center;">' . ($row['penalty_points'] > 0 ? '-' . $row['penalty_points'] : '0') . '</td>';
        
        // Overall
        echo '<td style="color: #6f42c1; font-weight: bold; font-size: 14px; text-align: center;">' . $row['total_points'] . '</td>';
        echo '<td style="color: #6f42c1; text-align: center;">' . $row['avg_points_per_student'] . '</td>';
        
        // Top performer
        echo '<td style="color: #20c997; font-weight: bold;">' . htmlspecialchars($row['top_performer'] ?? 'N/A') . '</td>';
        echo '<td style="color: #20c997; font-weight: bold; text-align: center;">' . ($row['top_performer_points'] ?? '0') . '</td>';
        
        echo '</tr>';
        $section_rank++;
    }
    
    echo '</table>';
    echo '</body></html>';
}

mysqli_close($conn);
exit();
?>