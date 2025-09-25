<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'csv';

// House-wise summary query
$query = "
    SELECT 
        h.name as house_name,
        COUNT(DISTINCT s.student_id) as total_students,
        COALESCE(appreciation_points, 0) as appreciation_points,
        COALESCE(participation_points, 0) as participation_points,
        COALESCE(organizer_points, 0) as organizer_points,
        COALESCE(winner_points, 0) as winner_points,
        COALESCE(penalty_points, 0) as penalty_points,
        (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
         COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) as total_points,
        CASE 
            WHEN COUNT(DISTINCT s.student_id) > 0 THEN 
                ROUND((COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                       COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) / COUNT(DISTINCT s.student_id), 2)
            ELSE 0 
        END as avg_points_per_student
    FROM houses h
    LEFT JOIN students s ON h.hid = s.hid
    LEFT JOIN (
        SELECT s2.hid, SUM(a.points) as appreciation_points 
        FROM appreciations a 
        JOIN students s2 ON a.student_id = s2.student_id 
        GROUP BY s2.hid
    ) ap ON h.hid = ap.hid
    LEFT JOIN (
        SELECT s3.hid, SUM(p.points) as participation_points 
        FROM participants p 
        JOIN students s3 ON p.student_id = s3.student_id 
        GROUP BY s3.hid
    ) pp ON h.hid = pp.hid
    LEFT JOIN (
        SELECT s4.hid, SUM(o.points) as organizer_points 
        FROM organizers o 
        JOIN students s4 ON o.student_id = s4.student_id 
        GROUP BY s4.hid
    ) op ON h.hid = op.hid
    LEFT JOIN (
        SELECT s5.hid, SUM(w.points) as winner_points 
        FROM winners w 
        JOIN students s5 ON w.student_id = s5.student_id 
        GROUP BY s5.hid
    ) wp ON h.hid = wp.hid
    LEFT JOIN (
        SELECT s6.hid, SUM(pen.points) as penalty_points 
        FROM penalties pen 
        JOIN students s6 ON pen.student_id = s6.student_id 
        GROUP BY s6.hid
    ) penp ON h.hid = penp.hid
    GROUP BY h.hid, h.name
    ORDER BY total_points DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'Houses_Summary_Comparison_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'House Name', 'Total Students', 'Appreciation Points', 'Participation Points', 
        'Organizer Points', 'Winner Points', 'Penalty Points', 'Total Points', 'Average Points per Student'
    ]);
    
    // Write data
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} else {
    // Excel Export
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="ProgId" content="Excel.Sheet"><meta name="Generator" content="Microsoft Excel 11"></head>';
    echo '<body>';
    
    // Title
    echo '<h2>House-wise Points Summary Report</h2>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    $headers = [
        'House Name', 'Total Students', 'Appreciation Points', 'Participation Points', 
        'Organizer Points', 'Winner Points', 'Penalty Points', 'Total Points', 'Average Points per Student'
    ];
    
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data with alternating row colors
    $row_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row_count++;
        $bg_color = ($row_count % 2 == 0) ? '#F2F2F2' : '#FFFFFF';
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        foreach ($row as $key => $cell) {
            $style = '';
            
            // Special formatting for different columns
            if (strpos($key, 'points') !== false && $key !== 'penalty_points') {
                $style = 'color: green; font-weight: bold;';
            } elseif ($key === 'penalty_points' && $cell > 0) {
                $style = 'color: red; font-weight: bold;';
            } elseif ($key === 'total_points') {
                $style = 'color: blue; font-weight: bold; font-size: 14px;';
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