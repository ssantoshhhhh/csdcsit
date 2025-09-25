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

// Get all houses
$houses_query = "SELECT hid, name FROM houses ORDER BY name";
$houses_result = mysqli_query($conn, $houses_query);

if (!$houses_result || mysqli_num_rows($houses_result) == 0) {
    die('No houses found');
}

// Create a temporary directory for files
$temp_dir = sys_get_temp_dir() . '/house_exports_' . uniqid();
if (!mkdir($temp_dir, 0777, true)) {
    die('Could not create temporary directory');
}

$files_created = [];

// Generate file for each house
while ($house = mysqli_fetch_assoc($houses_result)) {
    $house_id = $house['hid'];
    $house_name = $house['name'];
    $safe_house_name = str_replace(' ', '_', $house_name);
    
    // Get detailed data for this house
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
        WHERE s.hid = $house_id
        
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
        WHERE p.points > 0 AND s.hid = $house_id
        
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
        WHERE o.points > 0 AND s.hid = $house_id
        
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
        WHERE s.hid = $house_id
        
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
        WHERE s.hid = $house_id
        
        ORDER BY date_awarded DESC, student_name
    ";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $filename = $temp_dir . '/' . $safe_house_name . '_House_Detailed_Report.' . $format;
        
        if ($format === 'csv') {
            // Create CSV file
            $file = fopen($filename, 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            fputcsv($file, [
                'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
                'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position'
            ]);
            
            // Write data
            while ($row = mysqli_fetch_assoc($result)) {
                fputcsv($file, $row);
            }
            
            fclose($file);
            
        } else {
            // Create Excel file (HTML format)
            $file = fopen($filename, 'w');
            
            fwrite($file, '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">');
            fwrite($file, '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="ProgId" content="Excel.Sheet"><meta name="Generator" content="Microsoft Excel 11"></head>');
            fwrite($file, '<body>');
            
            fwrite($file, '<h2>House Points Detailed Report - ' . htmlspecialchars($house_name) . '</h2>');
            fwrite($file, '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p><br>');
            
            fwrite($file, '<table border="1" cellpadding="3" cellspacing="0">');
            
            // Headers
            fwrite($file, '<tr style="background-color: #4472C4; color: white; font-weight: bold;">');
            $headers = [
                'Student ID', 'Student Name', 'Branch', 'Section', 'Year', 'Semester', 
                'House', 'Point Type', 'Points', 'Reason', 'Event Title', 'Date Awarded', 'Position'
            ];
            foreach ($headers as $header) {
                fwrite($file, '<th>' . htmlspecialchars($header) . '</th>');
            }
            fwrite($file, '</tr>');
            
            // Data rows
            $row_count = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $row_count++;
                $bg_color = ($row_count % 2 == 0) ? '#F2F2F2' : '#FFFFFF';
                
                fwrite($file, '<tr style="background-color: ' . $bg_color . ';">');
                foreach ($row as $key => $cell) {
                    $style = '';
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
                    fwrite($file, '<td style="' . $style . '">' . htmlspecialchars($cell ?? '') . '</td>');
                }
                fwrite($file, '</tr>');
            }
            
            fwrite($file, '</table></body></html>');
            fclose($file);
        }
        
        $files_created[] = $filename;
    }
}

// Create ZIP file
$zip_filename = 'All_Houses_Individual_Reports_' . date('Y-m-d_H-i-s') . '.zip';
$zip_path = $temp_dir . '/' . $zip_filename;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
    die('Could not create ZIP file');
}

// Add all created files to ZIP
foreach ($files_created as $file) {
    $zip->addFile($file, basename($file));
}

$zip->close();

// Send ZIP file to browser
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($zip_path));

readfile($zip_path);

// Clean up temporary files
foreach ($files_created as $file) {
    unlink($file);
}
unlink($zip_path);
rmdir($temp_dir);

mysqli_close($conn);
exit();
?>