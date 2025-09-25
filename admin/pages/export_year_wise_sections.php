<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";

$format = $_GET['format'] ?? 'excel';

// Validate format
if (!in_array($format, ['csv', 'excel'])) {
    die('Invalid format specified');
}

// Query to get all students organized by year, then sections within each year
$query = "
    SELECT 
        c.year,
        c.branch,
        c.section,
        c.academic_year,
        c.semester,
        s.student_id,
        s.name as student_name,
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
        RANK() OVER (PARTITION BY c.year ORDER BY (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                                                   COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) DESC) as year_rank,
        RANK() OVER (PARTITION BY s.class_id ORDER BY (COALESCE(appreciation_points, 0) + COALESCE(participation_points, 0) + 
                                                       COALESCE(organizer_points, 0) + COALESCE(winner_points, 0) - COALESCE(penalty_points, 0)) DESC) as section_rank
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

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$filename = 'Year_Wise_Sections_Complete_Report_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'Year', 'Branch', 'Section', 'Academic Year', 'Semester', 'Student ID', 'Student Name', 'House',
        'Appreciation Points', 'Participation Points', 'Organizer Points', 
        'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
        'Year Rank', 'Section Rank'
    ]);
    
    // Write data with year and section grouping
    $current_year = '';
    $current_section = '';
    $year_totals = [];
    $section_totals = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Add year header
        if ($row['year'] !== $current_year) {
            if ($current_year !== '') {
                // Add year totals
                fputcsv($output, ['', '', '', '', '', '', '', 'YEAR TOTAL:', 
                    $year_totals['appreciation'], $year_totals['participation'], 
                    $year_totals['organizer'], $year_totals['winner'], 
                    $year_totals['penalty'], $year_totals['total'], 
                    'Avg: ' . round($year_totals['total'] / $year_totals['count'], 2), '', '']);
                fputcsv($output, ['']); // Empty row
            }
            
            fputcsv($output, ['=== ACADEMIC YEAR ' . $row['year'] . ' STUDENTS ===']);
            $current_year = $row['year'];
            $year_totals = ['appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                          'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0];
        }
        
        // Add section header
        $section_key = $row['branch'] . ' - Section ' . $row['section'] . ' (' . $row['academic_year'] . ')';
        if ($section_key !== $current_section) {
            if ($current_section !== '' && !empty($section_totals)) {
                // Add section totals
                fputcsv($output, ['', '', '', '', '', '', '', 'SECTION TOTAL:', 
                    $section_totals['appreciation'], $section_totals['participation'], 
                    $section_totals['organizer'], $section_totals['winner'], 
                    $section_totals['penalty'], $section_totals['total'], 
                    'Avg: ' . round($section_totals['total'] / $section_totals['count'], 2), '', '']);
            }
            
            fputcsv($output, ['--- ' . $section_key . ' ---']);
            $current_section = $section_key;
            $section_totals = ['appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                             'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0];
        }
        
        // Update totals
        $year_totals['appreciation'] += $row['appreciation_points'];
        $year_totals['participation'] += $row['participation_points'];
        $year_totals['organizer'] += $row['organizer_points'];
        $year_totals['winner'] += $row['winner_points'];
        $year_totals['penalty'] += $row['penalty_points'];
        $year_totals['total'] += $row['total_points'];
        $year_totals['count']++;
        
        $section_totals['appreciation'] += $row['appreciation_points'];
        $section_totals['participation'] += $row['participation_points'];
        $section_totals['organizer'] += $row['organizer_points'];
        $section_totals['winner'] += $row['winner_points'];
        $section_totals['penalty'] += $row['penalty_points'];
        $section_totals['total'] += $row['total_points'];
        $section_totals['count']++;
        
        fputcsv($output, $row);
    }
    
    // Add final totals
    if (!empty($section_totals)) {
        fputcsv($output, ['', '', '', '', '', '', '', 'SECTION TOTAL:', 
            $section_totals['appreciation'], $section_totals['participation'], 
            $section_totals['organizer'], $section_totals['winner'], 
            $section_totals['penalty'], $section_totals['total'], 
            'Avg: ' . round($section_totals['total'] / $section_totals['count'], 2), '', '']);
    }
    if (!empty($year_totals)) {
        fputcsv($output, ['', '', '', '', '', '', '', 'YEAR TOTAL:', 
            $year_totals['appreciation'], $year_totals['participation'], 
            $year_totals['organizer'], $year_totals['winner'], 
            $year_totals['penalty'], $year_totals['total'], 
            'Avg: ' . round($year_totals['total'] / $year_totals['count'], 2), '', '']);
    }
    
    fclose($output);
    
} else {
    // Enhanced Excel Export with year and section grouping
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="ProgId" content="Excel.Sheet"><meta name="Generator" content="Microsoft Excel 11"></head>';
    echo '<body>';
    
    // Title
    echo '<h1>Complete Year-wise Sections Report</h1>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    echo '<p>This report shows all students organized by academic year, then by sections within each year.</p>';
    echo '<br>';
    
    echo '<table border="1" cellpadding="3" cellspacing="0">';
    
    // Write headers
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    $headers = [
        'Year', 'Branch', 'Section', 'Academic Year', 'Semester', 'Student ID', 'Student Name', 'House',
        'Appreciation Points', 'Participation Points', 'Organizer Points', 
        'Winner Points', 'Penalty Points', 'Total Points', 'Performance Grade',
        'Year Rank', 'Section Rank'
    ];
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Write data with year and section grouping
    $current_year = '';
    $current_section = '';
    $year_totals = [];
    $section_totals = [];
    $row_count = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Add year header
        if ($row['year'] !== $current_year) {
            if ($current_year !== '') {
                // Add year totals
                echo '<tr style="background-color: #dc3545; color: white; font-weight: bold; font-size: 16px;">';
                echo '<td colspan="8" style="text-align: right; padding: 12px;">YEAR ' . $current_year . ' TOTAL:</td>';
                echo '<td style="text-align: center;">' . $year_totals['appreciation'] . '</td>';
                echo '<td style="text-align: center;">' . $year_totals['participation'] . '</td>';
                echo '<td style="text-align: center;">' . $year_totals['organizer'] . '</td>';
                echo '<td style="text-align: center;">' . $year_totals['winner'] . '</td>';
                echo '<td style="text-align: center;">' . $year_totals['penalty'] . '</td>';
                echo '<td style="text-align: center; font-size: 18px;">' . $year_totals['total'] . '</td>';
                echo '<td style="text-align: center;">Avg: ' . round($year_totals['total'] / $year_totals['count'], 2) . '</td>';
                echo '<td colspan="2"></td>';
                echo '</tr>';
                echo '<tr><td colspan="17" style="height: 20px; background-color: #ffffff;"></td></tr>'; // Spacer
            }
            
            // Year colors
            $year_colors = [
                '1' => '#e74c3c',
                '2' => '#f39c12', 
                '3' => '#2ecc71',
                '4' => '#3498db'
            ];
            $year_color = $year_colors[$row['year']] ?? '#6c757d';
            
            echo '<tr style="background-color: ' . $year_color . '; color: white; font-weight: bold; font-size: 18px;">';
            echo '<td colspan="17" style="text-align: center; padding: 20px;">';
            echo '<i class="fas fa-graduation-cap"></i> ACADEMIC YEAR ' . $row['year'] . ' STUDENTS';
            echo '</td></tr>';
            
            $current_year = $row['year'];
            $year_totals = ['appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                          'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0];
        }
        
        // Add section header
        $section_key = $row['branch'] . ' - Section ' . $row['section'] . ' (' . $row['academic_year'] . ')';
        if ($section_key !== $current_section) {
            if ($current_section !== '' && !empty($section_totals)) {
                // Add section totals
                echo '<tr style="background-color: #6c757d; color: white; font-weight: bold;">';
                echo '<td colspan="8" style="text-align: right; padding: 8px;">Section Total:</td>';
                echo '<td style="text-align: center;">' . $section_totals['appreciation'] . '</td>';
                echo '<td style="text-align: center;">' . $section_totals['participation'] . '</td>';
                echo '<td style="text-align: center;">' . $section_totals['organizer'] . '</td>';
                echo '<td style="text-align: center;">' . $section_totals['winner'] . '</td>';
                echo '<td style="text-align: center;">' . $section_totals['penalty'] . '</td>';
                echo '<td style="text-align: center; font-size: 14px;">' . $section_totals['total'] . '</td>';
                echo '<td style="text-align: center;">Avg: ' . round($section_totals['total'] / $section_totals['count'], 2) . '</td>';
                echo '<td colspan="2"></td>';
                echo '</tr>';
            }
            
            echo '<tr style="background-color: #495057; color: white; font-weight: bold;">';
            echo '<td colspan="17" style="text-align: center; padding: 12px;">';
            echo '<i class="fas fa-users"></i> ' . htmlspecialchars($section_key);
            echo '</td></tr>';
            
            $current_section = $section_key;
            $section_totals = ['appreciation' => 0, 'participation' => 0, 'organizer' => 0, 
                             'winner' => 0, 'penalty' => 0, 'total' => 0, 'count' => 0];
        }
        
        // Update totals
        $year_totals['appreciation'] += $row['appreciation_points'];
        $year_totals['participation'] += $row['participation_points'];
        $year_totals['organizer'] += $row['organizer_points'];
        $year_totals['winner'] += $row['winner_points'];
        $year_totals['penalty'] += $row['penalty_points'];
        $year_totals['total'] += $row['total_points'];
        $year_totals['count']++;
        
        $section_totals['appreciation'] += $row['appreciation_points'];
        $section_totals['participation'] += $row['participation_points'];
        $section_totals['organizer'] += $row['organizer_points'];
        $section_totals['winner'] += $row['winner_points'];
        $section_totals['penalty'] += $row['penalty_points'];
        $section_totals['total'] += $row['total_points'];
        $section_totals['count']++;
        
        $row_count++;
        $bg_color = '#FFFFFF';
        
        // Special highlighting for top performers
        if ($row['year_rank'] == 1) $bg_color = '#ffd700'; // Gold for year topper
        elseif ($row['section_rank'] == 1) $bg_color = '#d1ecf1'; // Blue for section topper
        elseif ($row_count % 2 == 0) $bg_color = '#f8f9fa'; // Alternate rows
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        
        foreach ($row as $key => $cell) {
            $style = '';
            
            // Special formatting
            if (strpos($key, 'points') !== false && $key !== 'penalty_points' && $key !== 'total_points') {
                $style = 'color: green; font-weight: bold; text-align: center;';
            } elseif ($key === 'penalty_points' && $cell > 0) {
                $style = 'color: red; font-weight: bold; text-align: center;';
            } elseif ($key === 'total_points') {
                $style = 'color: blue; font-weight: bold; font-size: 14px; text-align: center;';
            } elseif ($key === 'performance_grade') {
                switch ($cell) {
                    case 'Excellent':
                        $style = 'color: green; font-weight: bold; text-align: center;';
                        break;
                    case 'Good':
                        $style = 'color: blue; font-weight: bold; text-align: center;';
                        break;
                    case 'Average':
                        $style = 'color: orange; font-weight: bold; text-align: center;';
                        break;
                    case 'Needs Improvement':
                        $style = 'color: red; font-weight: bold; text-align: center;';
                        break;
                }
            } elseif ($key === 'year_rank' || $key === 'section_rank') {
                $style = 'font-weight: bold; text-align: center;';
                if ($cell == 1) $style .= ' color: gold;';
                elseif ($cell == 2) $style .= ' color: silver;';
                elseif ($cell == 3) $style .= ' color: #cd7f32;';
            } elseif ($key === 'year') {
                $style = 'font-weight: bold; text-align: center; font-size: 14px;';
            }
            
            echo '<td style="' . $style . '">' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    
    // Add final totals
    if (!empty($section_totals)) {
        echo '<tr style="background-color: #6c757d; color: white; font-weight: bold;">';
        echo '<td colspan="8" style="text-align: right; padding: 8px;">Section Total:</td>';
        echo '<td style="text-align: center;">' . $section_totals['appreciation'] . '</td>';
        echo '<td style="text-align: center;">' . $section_totals['participation'] . '</td>';
        echo '<td style="text-align: center;">' . $section_totals['organizer'] . '</td>';
        echo '<td style="text-align: center;">' . $section_totals['winner'] . '</td>';
        echo '<td style="text-align: center;">' . $section_totals['penalty'] . '</td>';
        echo '<td style="text-align: center; font-size: 14px;">' . $section_totals['total'] . '</td>';
        echo '<td style="text-align: center;">Avg: ' . round($section_totals['total'] / $section_totals['count'], 2) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';
    }
    if (!empty($year_totals)) {
        echo '<tr style="background-color: #dc3545; color: white; font-weight: bold; font-size: 16px;">';
        echo '<td colspan="8" style="text-align: right; padding: 12px;">YEAR ' . $current_year . ' TOTAL:</td>';
        echo '<td style="text-align: center;">' . $year_totals['appreciation'] . '</td>';
        echo '<td style="text-align: center;">' . $year_totals['participation'] . '</td>';
        echo '<td style="text-align: center;">' . $year_totals['organizer'] . '</td>';
        echo '<td style="text-align: center;">' . $year_totals['winner'] . '</td>';
        echo '<td style="text-align: center;">' . $year_totals['penalty'] . '</td>';
        echo '<td style="text-align: center; font-size: 18px;">' . $year_totals['total'] . '</td>';
        echo '<td style="text-align: center;">Avg: ' . round($year_totals['total'] / $year_totals['count'], 2) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}

mysqli_close($conn);
exit();
?>