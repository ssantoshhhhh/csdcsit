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
            DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded
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
            DATE_FORMAT(p.registered_at, '%Y-%m-%d %H:%i:%s') as date_awarded
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
            DATE_FORMAT(o.assigned_at, '%Y-%m-%d %H:%i:%s') as date_awarded
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
            DATE_FORMAT(w.announced_at, '%Y-%m-%d %H:%i:%s') as date_awarded
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
            DATE_FORMAT(pen.created_at, '%Y-%m-%d %H:%i:%s') as date_awarded
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
             COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) as total_points,
            CASE 
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 50 THEN 'Excellent'
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 30 THEN 'Good'
                WHEN (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                      COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) >= 10 THEN 'Average'
                ELSE 'Needs Improvement'
            END as performance_grade
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
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
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
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade'
        ]);
    }
    
    // Write data with section grouping for summary reports
    $current_section = '';
    $section_totals = [];
    
    // Reset result pointer
    mysqli_data_seek($result, 0);
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Add year and section headers for summary reports
        if ($type === 'summary') {
            $year_key = 'Year ' . $row['year'];
            $section_key = $row['branch'] . ' - Section ' . $row['section'] . ' (' . $row['academic_year'] . ')';
            $full_section_key = $year_key . ' - ' . $section_key;
            
            // Check if we need a new year header
            if (strpos($current_section, $year_key) !== 0) {
                // Add previous section total if exists
                if ($current_section !== '' && !empty($section_totals)) {
                    fputcsv($output, ['', '', '', '', '', '', 'SECTION TOTAL:', 
                        $section_totals['appreciation'], $section_totals['participation'], 
                        $section_totals['organizer'], $section_totals['winner'], 
                        $section_totals['penalty'], $section_totals['total'], 
                        'Avg: ' . round($section_totals['total'] / $section_totals['count'], 2)]);
                }
                
                // Add year header
                fputcsv($output, ['']);
                fputcsv($output, ['=== ACADEMIC ' . strtoupper($year_key) . ' STUDENTS ===']);
                fputcsv($output, ['']);
            }
            
            if ($full_section_key !== $current_section) {
                // Add section summary if not first section and same year
                if ($current_section !== '' && !empty($section_totals) && strpos($current_section, $year_key) === 0) {
                    fputcsv($output, ['', '', '', '', '', '', 'SECTION TOTAL:', 
                        $section_totals['appreciation'], $section_totals['participation'], 
                        $section_totals['organizer'], $section_totals['winner'], 
                        $section_totals['penalty'], $section_totals['total'], 
                        'Avg: ' . round($section_totals['total'] / $section_totals['count'], 2)]);
                    fputcsv($output, ['']); // Empty row for spacing
                }
                
                // Add new section header
                fputcsv($output, ['--- ' . $section_key . ' ---']);
                
                $current_section = $full_section_key;
                $section_totals = [
                    'appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                    'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0
                ];
            }
            
            // Update section totals
            $section_totals['appreciation'] += $row['appreciation_points'];
            $section_totals['participation'] += $row['participation_points'];
            $section_totals['organizer'] += $row['organizer_points'];
            $section_totals['winner'] += $row['winner_points'];
            $section_totals['penalty'] += $row['penalty_points'];
            $section_totals['total'] += $row['total_points'];
            $section_totals['count']++;
        }
        
        fputcsv($output, $row);
    }
    
    // Add final section totals for summary reports
    if ($type === 'summary' && !empty($section_totals)) {
        fputcsv($output, ['', '', '', '', '', '', 'SECTION TOTAL:', 
            $section_totals['appreciation'], $section_totals['participation'], 
            $section_totals['organizer'], $section_totals['winner'], 
            $section_totals['penalty'], $section_totals['total'], 
            'Avg: ' . round($section_totals['total'] / $section_totals['count'], 2)]);
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
    echo '<h2>House Points ' . ucfirst($type) . ' Report</h2>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers with styling
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    if ($type === 'detailed') {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded'
        ];
    } else {
        $headers = [
            'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
            'House', 'Appreciation Points', 'Participation Points', 'Organizer Points', 
            'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade'
        ];
    }
    
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data with section grouping for summary reports
    $row_count = 0;
    $current_section = '';
    $section_totals = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row_count++;
        
        // Add year and section headers for summary reports
        if ($type === 'summary') {
            $year_key = 'Year ' . $row['year'];
            $section_key = $row['branch'] . ' - Section ' . $row['section'] . ' (' . $row['academic_year'] . ')';
            $full_section_key = $year_key . ' - ' . $section_key;
            
            // Check if we need a new year header
            if (strpos($current_section, $year_key) !== 0) {
                // Add previous section total if exists
                if ($current_section !== '' && !empty($section_totals)) {
                    echo '<tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #6c757d;">';
                    echo '<td colspan="7" style="text-align: right; padding: 8px;">Section Total:</td>';
                    echo '<td style="text-align: center; color: green;">' . $section_totals['appreciation'] . '</td>';
                    echo '<td style="text-align: center; color: blue;">' . $section_totals['participation'] . '</td>';
                    echo '<td style="text-align: center; color: orange;">' . $section_totals['organizer'] . '</td>';
                    echo '<td style="text-align: center; color: purple;">' . $section_totals['winner'] . '</td>';
                    echo '<td style="text-align: center; color: red;">' . $section_totals['penalty'] . '</td>';
                    echo '<td style="text-align: center; color: blue; font-size: 14px;">' . $section_totals['total'] . '</td>';
                    echo '<td style="text-align: center;">Avg: ' . round($section_totals['total'] / $section_totals['count'], 2) . '</td>';
                    echo '</tr>';
                }
                
                // Add year header
                echo '<tr><td colspan="14" style="height: 15px; background-color: #ffffff;"></td></tr>'; // Spacer
                echo '<tr style="background-color: #dc3545; color: white; font-weight: bold; font-size: 16px;">';
                echo '<td colspan="14" style="text-align: center; padding: 15px;">';
                echo '<i class="fas fa-graduation-cap"></i> ACADEMIC ' . strtoupper($year_key) . ' STUDENTS';
                echo '</td></tr>';
            }
            
            if ($full_section_key !== $current_section) {
                // Add section summary if not first section and same year
                if ($current_section !== '' && !empty($section_totals) && strpos($current_section, $year_key) === 0) {
                    echo '<tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #6c757d;">';
                    echo '<td colspan="7" style="text-align: right; padding: 8px;">Section Total:</td>';
                    echo '<td style="text-align: center; color: green;">' . $section_totals['appreciation'] . '</td>';
                    echo '<td style="text-align: center; color: blue;">' . $section_totals['participation'] . '</td>';
                    echo '<td style="text-align: center; color: orange;">' . $section_totals['organizer'] . '</td>';
                    echo '<td style="text-align: center; color: purple;">' . $section_totals['winner'] . '</td>';
                    echo '<td style="text-align: center; color: red;">' . $section_totals['penalty'] . '</td>';
                    echo '<td style="text-align: center; color: blue; font-size: 14px;">' . $section_totals['total'] . '</td>';
                    echo '<td style="text-align: center;">Avg: ' . round($section_totals['total'] / $section_totals['count'], 2) . '</td>';
                    echo '</tr>';
                }
                
                // Add new section header
                echo '<tr style="background-color: #4472C4; color: white; font-weight: bold; font-size: 14px;">';
                echo '<td colspan="14" style="text-align: center; padding: 12px;">';
                echo '<i class="fas fa-users"></i> ' . htmlspecialchars($section_key);
                echo '</td></tr>';
                
                $current_section = $full_section_key;
                $section_totals = [
                    'appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                    'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0
                ];
            }
            
            // Update section totals
            $section_totals['appreciation'] += $row['appreciation_points'];
            $section_totals['participation'] += $row['participation_points'];
            $section_totals['organizer'] += $row['organizer_points'];
            $section_totals['winner'] += $row['winner_points'];
            $section_totals['penalty'] += $row['penalty_points'];
            $section_totals['total'] += $row['total_points'];
            $section_totals['count']++;
        }
        
        $bg_color = ($row_count % 2 == 0) ? '#F9F9F9' : '#FFFFFF';
        
        // Special highlighting for top performers in each section
        if ($type === 'summary' && $section_totals['count'] == 1) {
            $bg_color = '#fff3cd'; // Gold for section topper
        }
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        foreach ($row as $key => $cell) {
            $style = '';
            
            // Special formatting for different columns
            if ($type === 'summary') {
                if (strpos($key, 'points') !== false && $key !== 'penalty_points') {
                    $style = 'color: green; font-weight: bold;';
                } elseif ($key === 'penalty_points' && $cell > 0) {
                    $style = 'color: red; font-weight: bold;';
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
                }
            }
            
            echo '<td style="' . $style . '">' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    
    // Add final section totals for summary reports
    if ($type === 'summary' && !empty($section_totals)) {
        echo '<tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #6c757d;">';
        echo '<td colspan="7" style="text-align: right; padding: 8px;">Section Total:</td>';
        echo '<td style="text-align: center; color: green;">' . $section_totals['appreciation'] . '</td>';
        echo '<td style="text-align: center; color: blue;">' . $section_totals['participation'] . '</td>';
        echo '<td style="text-align: center; color: orange;">' . $section_totals['organizer'] . '</td>';
        echo '<td style="text-align: center; color: purple;">' . $section_totals['winner'] . '</td>';
        echo '<td style="text-align: center; color: red;">' . $section_totals['penalty'] . '</td>';
        echo '<td style="text-align: center; color: blue; font-size: 14px;">' . $section_totals['total'] . '</td>';
        echo '<td style="text-align: center;">Avg: ' . round($section_totals['total'] / $section_totals['count'], 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}

mysqli_close($conn);
exit();
?>