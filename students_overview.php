<?php
session_start();
include './connect.php';

// Get overall statistics
$total_students = 0;
$active_students = 0;
$alumni_count = 0;
$avg_cgpa = 0;

$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_alumni = 0 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_alumni = 1 THEN 1 ELSE 0 END) as alumni,
    AVG(CASE WHEN sp.cgpa IS NOT NULL THEN sp.cgpa END) as avg_cgpa
FROM students s 
LEFT JOIN student_profile sp ON s.student_id = sp.student_id";

$stats_result = mysqli_query($conn, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
    $total_students = $stats['total'];
    $active_students = $stats['active'];
    $alumni_count = $stats['alumni'];
    $avg_cgpa = round($stats['avg_cgpa'], 2);
}

// Get branch distribution
$branch_stats = [];
$branch_query = "SELECT branch, 
    COUNT(*) as count,
    SUM(CASE WHEN is_alumni = 0 THEN 1 ELSE 0 END) as active_count
FROM students 
GROUP BY branch 
ORDER BY count DESC";

$branch_result = mysqli_query($conn, $branch_query);
if ($branch_result) {
    while ($row = mysqli_fetch_assoc($branch_result)) {
        $branch_stats[] = $row;
    }
}

// Get year distribution
$year_stats = [];
$year_query = "SELECT c.year, 
    COUNT(s.student_id) as count,
    SUM(CASE WHEN s.is_alumni = 0 THEN 1 ELSE 0 END) as active_count
FROM students s 
JOIN classes c ON s.class_id = c.class_id 
GROUP BY c.year 
ORDER BY c.year";

$year_result = mysqli_query($conn, $year_query);
if ($year_result) {
    while ($row = mysqli_fetch_assoc($year_result)) {
        $year_stats[] = $row;
    }
}

// Get CGPA distribution
$cgpa_ranges = [
    '9.0-10.0' => ['min' => 9.0, 'max' => 10.0, 'count' => 0],
    '8.0-8.9' => ['min' => 8.0, 'max' => 8.9, 'count' => 0],
    '7.0-7.9' => ['min' => 7.0, 'max' => 7.9, 'count' => 0],
    '6.0-6.9' => ['min' => 6.0, 'max' => 6.9, 'count' => 0],
    'Below 6.0' => ['min' => 0, 'max' => 5.9, 'count' => 0],
    'Not Available' => ['min' => null, 'max' => null, 'count' => 0]
];

$cgpa_query = "SELECT cgpa FROM student_profile WHERE cgpa IS NOT NULL";
$cgpa_result = mysqli_query($conn, $cgpa_query);
$total_cgpa_records = 0;

if ($cgpa_result) {
    while ($row = mysqli_fetch_assoc($cgpa_result)) {
        $cgpa = $row['cgpa'];
        $total_cgpa_records++;
        
        if ($cgpa >= 9.0) $cgpa_ranges['9.0-10.0']['count']++;
        elseif ($cgpa >= 8.0) $cgpa_ranges['8.0-8.9']['count']++;
        elseif ($cgpa >= 7.0) $cgpa_ranges['7.0-7.9']['count']++;
        elseif ($cgpa >= 6.0) $cgpa_ranges['6.0-6.9']['count']++;
        else $cgpa_ranges['Below 6.0']['count']++;
    }
}

// Count students without CGPA data
$no_cgpa_query = "SELECT COUNT(*) as count FROM students s 
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
    WHERE sp.cgpa IS NULL";
$no_cgpa_result = mysqli_query($conn, $no_cgpa_query);
if ($no_cgpa_result) {
    $no_cgpa_data = mysqli_fetch_assoc($no_cgpa_result);
    $cgpa_ranges['Not Available']['count'] = $no_cgpa_data['count'];
}

// Get top skills
$skills_data = [];
$skills_query = "SELECT skills FROM student_profile WHERE skills IS NOT NULL AND skills != '[]'";
$skills_result = mysqli_query($conn, $skills_query);

if ($skills_result) {
    while ($row = mysqli_fetch_assoc($skills_result)) {
        $skills = json_decode($row['skills'], true);
        if (is_array($skills)) {
            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (!empty($skill)) {
                    if (!isset($skills_data[$skill])) {
                        $skills_data[$skill] = 0;
                    }
                    $skills_data[$skill]++;
                }
            }
        }
    }
}

// Sort skills by popularity
arsort($skills_data);
$top_skills = array_slice($skills_data, 0, 10, true);

// Get class assignments with teachers
$class_assignments = [];
$class_query = "SELECT 
    c.class_id,
    c.academic_year,
    c.year,
    c.semester,
    c.branch,
    c.section,
    COUNT(s.student_id) as student_count,
    f.faculty_name,
    f.email as faculty_email
FROM classes c
LEFT JOIN students s ON c.class_id = s.class_id AND s.is_alumni = 0
LEFT JOIN faculties f ON c.class_id = f.class_id AND f.is_active = 1
GROUP BY c.class_id, f.faculty_id
ORDER BY c.year, c.branch, c.section";

$class_result = mysqli_query($conn, $class_query);
if ($class_result) {
    while ($row = mysqli_fetch_assoc($class_result)) {
        $class_assignments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Students Overview - SRKR Engineering College</title>
    <style>
        /* Ensure consistent font family */
        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        
        /* Ensure navbar styling consistency */
        .navbar {
            background-color: #ffffff !important;
            
        }

        .page-header {
            background: #f8f9fa;
            padding: 3rem 0 2rem 0;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 1rem;
            margin-top: 0.5rem;
            color: #5a67d8;
            font-weight: 400;
        }

        .back-nav {
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .back-btn {
            background: white;
            border: 1px solid #e9ecef;
            color: #6c757d;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .back-btn:hover::before {
            left: 100%;
        }

        .back-btn:hover {
            color: #667eea;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
        }

        .back-btn i {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-2px);
        }

        .main-content {
            padding: 0 0 3rem 0;
        }

        /* Stats Cards Styles */
        .stats-container {
            padding: 0 15px;
        }
        
        .stats-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            min-width: 200px;
            flex: 1;
            max-width: 240px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border-color: #e0e0e0;
        }
        
        .stat-icon-container {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .stat-icon-container i {
            font-size: 20px;
        }
        
        .stat-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #9e9e9e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.1;
        }

        /* Distribution Cards */
        .distribution-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .distribution-card:hover {
            transform: translateY(-2px);
            border-color: #e0e0e0;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafbfc;
        }

        .card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: #667eea;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 24px;
        }

        .progress-item {
            margin-bottom: 20px;
        }

        .progress-item:last-child {
            margin-bottom: 0;
        }

        .progress-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 8px;
        }

        .progress-label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .progress-value {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .progress-bar-container {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        /* Skills Section */
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .skill-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .skill-tag:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .skill-count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Class Cards */
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .class-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border-color: #667eea;
        }

        .class-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .class-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #667eea20;
        }

        .class-icon i {
            color: #667eea;
            font-size: 1.1rem;
        }

        .class-info h6 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
        }

        .class-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .class-stats {
            margin-bottom: 16px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .stat-item i {
            color: #6c757d;
            font-size: 0.8rem;
            width: 14px;
        }

        .stat-item span {
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .faculty-section h6 {
            color: #2c3e50;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .faculty-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .faculty-avatar {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #43e97b20;
        }

        .faculty-avatar i {
            color: #43e97b;
            font-size: 0.7rem;
        }

        .faculty-details {
            flex: 1;
        }

        .faculty-name {
            color: #2c3e50;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0;
        }

        .faculty-email {
            color: #6c757d;
            font-size: 0.7rem;
            margin: 0;
        }

        .no-faculty {
            text-align: center;
            padding: 20px;
        }

        .no-faculty i {
            color: #dee2e6;
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .no-faculty div {
            color: #6c757d;
            font-size: 0.8rem;
        }

        /* Top Performers Section */
        .performers-table {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafbfc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filters-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.85rem;
            background: white;
            min-width: 120px;
        }

        .filter-select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .performers-table table {
            margin: 0;
        }

        .performers-table th {
            background: #fafbfc;
            border: none;
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            padding: 16px 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .performers-table td {
            border: none;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.85rem;
        }

        .performers-table tr:hover {
            background: #f8f9fa;
        }

        .rank-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .rank-gold {
            background: #ffd700;
            color: #b8860b;
        }

        .rank-silver {
            background: #c0c0c0;
            color: #696969;
        }

        .rank-bronze {
            background: #cd7f32;
            color: #8b4513;
        }

        .rank-default {
            background: #e9ecef;
            color: #6c757d;
        }

        .student-name {
            font-weight: 500;
            color: #2c3e50;
        }

        .cgpa-score {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-alumni {
            background: #e3f2fd;
            color: #1565c0;
        }

        .skill-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .skill-badge {
            background: #667eea;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 500;
        }

        .show-more-btn {
            background: white;
            border: 1px solid #667eea;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 20px auto;
            display: block;
        }

        .show-more-btn:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .stats-grid {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-card {
                min-width: unset;
                max-width: unset;
                padding: 20px;
                gap: 14px;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .stat-icon-container {
                width: 40px;
                height: 40px;
            }
            
            .stat-icon-container i {
                font-size: 18px;
            }

            .class-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-row {
                justify-content: stretch;
            }

            .filter-select {
                flex: 1;
                min-width: unset;
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                padding: 0 10px;
            }
            
            .stat-card {
                padding: 16px;
                gap: 12px;
            }
            
            .stat-value {
                font-size: 1.3rem;
            }
            
            .stat-icon-container {
                width: 36px;
                height: 36px;
            }
            
            .stat-icon-container i {
                font-size: 16px;
            }

            .card-body {
                padding: 20px;
            }

            .class-card {
                padding: 16px;
            }
        }

        /* Modal Styles */
        .class-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .class-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            position: relative;
        }

        .modal-header {
            padding: 24px 30px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-subtitle {
            margin: 8px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 24px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        .modal-body {
            padding: 30px;
        }

        .class-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .detail-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.2rem;
        }

        .detail-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .students-section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .student-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
        }

        .student-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .student-info h6 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .student-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .student-stats {
            display: flex;
            gap: 16px;
            margin-top: 8px;
        }

        .student-stat {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #6c757d;
        }

        .student-stat i {
            font-size: 0.7rem;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20px;
            }

            .modal-header {
                padding: 20px 24px;
            }

            .modal-body {
                padding: 24px;
            }

            .class-details-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 16px;
            }

            .students-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>

</head>
<body>
    <?php include "nav.php"; ?>
        
    <div class="main-content">
        <div class="container">
            <div class="back-nav">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="houses_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Houses Dashboard
                    </a>
                    
                </div>
            </div>

            <!-- Student Statistics Section -->
            <div class="stats-container mb-5">
                <?php
                // Define stats with clean design
                $stats = [
                    [
                        'title' => 'Total Students',
                        'value' => number_format($total_students),
                        'icon' => 'fas fa-users',
                        'icon_bg' => '#e3f2fd',
                        'icon_color' => '#1976d2'
                    ],
                    [
                        'title' => 'Active Students', 
                        'value' => number_format($active_students),
                        'icon' => 'fas fa-user-graduate',
                        'icon_bg' => '#e8f5e9',
                        'icon_color' => '#388e3c'
                    ],
                    [
                        'title' => 'Alumni',
                        'value' => number_format($alumni_count),
                        'icon' => 'fas fa-graduation-cap',
                        'icon_bg' => '#fff3e0',
                        'icon_color' => '#f57c00'
                    ],
                    [
                        'title' => 'Average CGPA',
                        'value' => $avg_cgpa ?: 'N/A',
                        'icon' => 'fas fa-star',
                        'icon_bg' => '#fce4ec',
                        'icon_color' => '#c2185b'
                    ]
                ];
                ?>
                
                <div class="stats-grid">
                    <?php foreach ($stats as $stat): ?>
                        <div class="stat-card">
                            <div class="stat-icon-container" style="background-color: <?php echo $stat['icon_bg']; ?>;">
                                <i class="<?php echo $stat['icon']; ?>" style="color: <?php echo $stat['icon_color']; ?>;"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label"><?php echo $stat['title']; ?></div>
                                <div class="stat-value"><?php echo $stat['value']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Distribution Charts -->
            <div class="row mb-5">
                <!-- Branch Distribution -->
                <div class="col-md-6">
                    <div class="distribution-card h-100">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-code-branch"></i>Branch Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($branch_stats as $branch): ?>
                                <?php 
                                $percentage = $total_students > 0 ? ($branch['count'] / $total_students) * 100 : 0;
                                $colors = ['CSD' => '#667eea', 'CSIT' => '#f093fb'];
                                $color = $colors[$branch['branch']] ?? '#6c757d';
                                ?>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label"><?php echo $branch['branch']; ?></span>
                                        <span class="progress-value">
                                            <?php echo $branch['count']; ?> students (<?php echo round($percentage, 1); ?>%)
                                        </span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="background: <?php echo $color; ?>; width: <?php echo $percentage; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Year Distribution -->
                <div class="col-md-6">
                    <div class="distribution-card h-100">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-layer-group"></i>Year Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($year_stats as $year): ?>
                                <?php 
                                $percentage = $total_students > 0 ? ($year['count'] / $total_students) * 100 : 0;
                                $colors = ['#4facfe', '#43e97b', '#f093fb', '#ffa726'];
                                $color = $colors[$year['year'] - 1] ?? '#6c757d';
                                ?>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label"><?php echo $year['year']; ?><?php echo ($year['year'] == 1) ? 'st' : (($year['year'] == 2) ? 'nd' : (($year['year'] == 3) ? 'rd' : 'th')); ?> Year</span>
                                        <span class="progress-value">
                                            <?php echo $year['count']; ?> students (<?php echo round($percentage, 1); ?>%)
                                        </span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="background: <?php echo $color; ?>; width: <?php echo $percentage; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CGPA Distribution -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="distribution-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar"></i>CGPA Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $cgpa_colors = [
                                    '9.0-10.0' => '#43e97b',
                                    '8.0-8.9' => '#4facfe', 
                                    '7.0-7.9' => '#667eea',
                                    '6.0-6.9' => '#ffa726',
                                    'Below 6.0' => '#f093fb',
                                    'Not Available' => '#6c757d'
                                ];
                                foreach ($cgpa_ranges as $range => $data): 
                                    $percentage = $total_students > 0 ? ($data['count'] / $total_students) * 100 : 0;
                                ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="progress-item">
                                            <div class="progress-header">
                                                <span class="progress-label"><?php echo $range; ?></span>
                                                <span class="progress-value">
                                                    <?php echo $data['count']; ?> (<?php echo round($percentage, 1); ?>%)
                                                </span>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar" style="background: <?php echo $cgpa_colors[$range]; ?>; width: <?php echo $percentage; ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

     
            <!-- Class Assignments -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="distribution-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chalkboard-teacher"></i>Class Assignments & Faculty
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="class-grid">
                                <?php 
                                $grouped_classes = [];
                                foreach ($class_assignments as $class) {
                                    $key = $class['class_id'];
                                    if (!isset($grouped_classes[$key])) {
                                        $grouped_classes[$key] = [
                                            'class_info' => $class,
                                            'faculties' => []
                                        ];
                                    }
                                    if ($class['faculty_name']) {
                                        $grouped_classes[$key]['faculties'][] = [
                                            'name' => $class['faculty_name'],
                                            'email' => $class['faculty_email']
                                        ];
                                    }
                                }
                                
                                foreach ($grouped_classes as $class_data): 
                                    $class = $class_data['class_info'];
                                    $faculties = $class_data['faculties'];
                                ?>
                                    <div class="class-card" onclick="openClassModal(<?php echo $class['class_id']; ?>, '<?php echo addslashes($class['branch']); ?>', '<?php echo $class['section']; ?>', <?php echo $class['year']; ?>, '<?php echo addslashes($class['academic_year']); ?>', <?php echo $class['student_count']; ?>)">
                                        <div class="class-header">
                                            <div class="class-icon">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div class="class-info">
                                                <h6><?php echo $class['branch']; ?> - Section <?php echo $class['section']; ?></h6>
                                                <small>Year <?php echo $class['year']; ?> | <?php echo $class['academic_year']; ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="class-stats">
                                            <div class="stat-item">
                                                <i class="fas fa-users"></i>
                                                <span><?php echo $class['student_count']; ?> Students</span>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($faculties)): ?>
                                            <div class="faculty-section">
                                                <h6>
                                                    <i class="fas fa-chalkboard-teacher"></i>Faculty
                                                </h6>
                                                <?php foreach ($faculties as $faculty): ?>
                                                    <div class="faculty-item">
                                                        <div class="faculty-avatar">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="faculty-details">
                                                            <div class="faculty-name"><?php echo htmlspecialchars($faculty['name']); ?></div>
                                                            <div class="faculty-email"><?php echo htmlspecialchars($faculty['email']); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-faculty">
                                                <i class="fas fa-user-slash"></i>
                                                <div>No faculty assigned</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="performers-table">
                        <div class="table-header">
                            <h5 class="card-title">
                                <i class="fas fa-trophy"></i>Top Academic Performers
                            </h5>
                            
                            <div class="filters-row">
                                <select class="filter-select" id="branchFilter" onchange="filterTopPerformers()">
                                    <option value="">All Branches</option>
                                    <option value="CSD">CSD</option>
                                    <option value="CSIT">CSIT</option>
                                </select>
                                <select class="filter-select" id="yearFilter" onchange="filterTopPerformers()">
                                    <option value="">All Years</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                                <select class="filter-select" id="statusFilter" onchange="filterTopPerformers()">
                                    <option value="active">Active Students</option>
                                    <option value="alumni">Alumni</option>
                                    <option value="all">All Students</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Year</th>
                                        <th>CGPA</th>
                                        <th>Status</th>
                                        <th>Skills</th>
                                    </tr>
                                </thead>
                                <tbody id="performersTable">
                                    <!-- Top performers will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Show More Button -->
                        <div class="text-center">
                            <button class="show-more-btn" id="showMoreBtn" onclick="loadMorePerformers()" style="display: none;">
                                Show More Students (<span id="remainingCount">0</span> more)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Class Details Modal -->
    <div id="classModal" class="class-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">
                        <i class="fas fa-graduation-cap"></i>
                        <span id="modalClassTitle">Class Details</span>
                    </h2>
                    <p class="modal-subtitle" id="modalClassSubtitle">Loading class information...</p>
                </div>
                <button class="modal-close" onclick="closeClassModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
        let currentOffset = 0;
        let currentLimit = 10;
        let totalPerformers = 0;
        let isLoading = false;

        // Load top performers on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTopPerformers();
        });

        function filterTopPerformers() {
            currentOffset = 0;
            loadTopPerformers();
        }

        function loadTopPerformers() {
            if (isLoading) return;
            isLoading = true;

            const branch = document.getElementById('branchFilter').value;
            const year = document.getElementById('yearFilter').value;
            const status = document.getElementById('statusFilter').value;

            const params = new URLSearchParams({
                action: 'get_top_performers',
                offset: currentOffset,
                limit: currentLimit,
                branch: branch,
                year: year,
                status: status
            });

            fetch('get_students.php?' + params)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('performersTable');
                        
                        if (currentOffset === 0) {
                            tbody.innerHTML = '';
                        }
                        
                        data.students.forEach((student, index) => {
                            const row = createPerformerRow(student, currentOffset + index + 1);
                            tbody.appendChild(row);
                        });
                        
                        totalPerformers = data.total;
                        updateShowMoreButton();
                    } else {
                        console.error('Error loading top performers:', data.message);
                    }
                    isLoading = false;
                })
                .catch(error => {
                    console.error('Error loading top performers:', error);
                    isLoading = false;
                });
        }

        function loadMorePerformers() {
            currentOffset += currentLimit;
            loadTopPerformers();
        }

        function createPerformerRow(student, rank) {
            const row = document.createElement('tr');
            
            // Parse skills
            let skillsHtml = '<span class="text-muted" style="font-size: 0.8rem;">No skills listed</span>';
            if (student.skills) {
                try {
                    const skills = JSON.parse(student.skills);
                    if (Array.isArray(skills) && skills.length > 0) {
                        const displaySkills = skills.slice(0, 3);
                        skillsHtml = '<div class="skill-badges">' + displaySkills.map(skill => 
                            `<span class="skill-badge">${skill}</span>`
                        ).join('') + '</div>';
                        if (skills.length > 3) {
                            skillsHtml += `<div class="text-muted" style="font-size: 0.7rem; margin-top: 4px;">+${skills.length - 3} more</div>`;
                        }
                    }
                } catch (e) {
                    skillsHtml = '<span class="text-muted" style="font-size: 0.8rem;">Invalid skills data</span>';
                }
            }

            // Determine rank badge class
            let rankClass = 'rank-default';
            if (rank === 1) rankClass = 'rank-gold';
            else if (rank === 2) rankClass = 'rank-silver';
            else if (rank === 3) rankClass = 'rank-bronze';
            
            row.innerHTML = `
                <td>
                    <span class="rank-badge ${rankClass}">
                        ${rank}
                    </span>
                </td>
                <td>
                    <div class="student-name">${student.name}</div>
                </td>
                <td>
                    <span style="color: #6c757d;">${student.branch}</span>
                </td>
                <td>
                    <span style="color: #6c757d;">${student.year}</span>
                </td>
                <td>
                    <span class="cgpa-score">
                        ${student.cgpa || 'N/A'}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${student.is_alumni == 1 ? 'status-alumni' : 'status-active'}">
                        ${student.is_alumni == 1 ? 'Alumni' : 'Active'}
                    </span>
                </td>
                <td>
                    ${skillsHtml}
                </td>
            `;
            
            return row;
        }

        function updateShowMoreButton() {
            const showMoreBtn = document.getElementById('showMoreBtn');
            const remainingCount = document.getElementById('remainingCount');
            const currentlyShown = currentOffset + currentLimit;
            
            if (currentlyShown < totalPerformers) {
                const remaining = totalPerformers - currentlyShown;
                remainingCount.textContent = remaining;
                showMoreBtn.style.display = 'inline-block';
            } else {
                showMoreBtn.style.display = 'none';
            }
        }

        // Add some animation to the stats cards
        document.addEventListener('DOMContentLoaded', function() {
            const statsCards = document.querySelectorAll('.stat-card');
            statsCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });

            // Animate progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach((bar, index) => {
                setTimeout(() => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 200);
                }, index * 100);
            });

            // Animate class cards
            const classCards = document.querySelectorAll('.class-card');
            classCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 50);
            });
        });

        // Modal functionality
        function openClassModal(classId, branch, section, year, academicYear, studentCount) {
            const modal = document.getElementById('classModal');
            const modalTitle = document.getElementById('modalClassTitle');
            const modalSubtitle = document.getElementById('modalClassSubtitle');
            const modalContent = document.getElementById('modalContent');
            
            // Set modal title and subtitle
            modalTitle.textContent = `${branch} - Section ${section}`;
            modalSubtitle.textContent = `Year ${year} | ${academicYear} | ${studentCount} Students`;
            
            // Show modal
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Show loading spinner
            modalContent.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
            `;
            
            // Fetch class details
            fetchClassDetails(classId, branch, section, year, academicYear, studentCount);
        }

        function closeClassModal() {
            const modal = document.getElementById('classModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function fetchClassDetails(classId, branch, section, year, academicYear, studentCount) {
            console.log('Fetching class details for class ID:', classId);
            
            fetch(`get_class_details.php?class_id=${classId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        renderClassDetails(data, branch, section, year, academicYear, studentCount);
                    } else {
                        document.getElementById('modalContent').innerHTML = `
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                                <h4>Error Loading Class Details</h4>
                                <p class="text-muted">${data.message || 'Unable to load class information'}</p>
                                <button class="btn btn-primary mt-3" onclick="fetchClassDetails(${classId}, '${branch}', '${section}', ${year}, '${academicYear}', ${studentCount})">
                                    <i class="fas fa-redo"></i> Try Again
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching class details:', error);
                    document.getElementById('modalContent').innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-wifi" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                            <h4>Connection Error</h4>
                            <p class="text-muted">Unable to connect to the server. Please try again.</p>
                            <button class="btn btn-primary mt-3" onclick="fetchClassDetails(${classId}, '${branch}', '${section}', ${year}, '${academicYear}', ${studentCount})">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                        </div>
                    `;
                });
        }

        function renderClassDetails(data, branch, section, year, academicYear, studentCount) {
            const students = data.students || [];
            const faculties = data.faculties || [];
            
            // Calculate statistics
            const activeStudents = students.filter(s => s.is_alumni == 0).length;
            const alumniCount = students.filter(s => s.is_alumni == 1).length;
            const avgCgpa = students.filter(s => s.cgpa).length > 0 
                ? (students.filter(s => s.cgpa).reduce((sum, s) => sum + parseFloat(s.cgpa), 0) / students.filter(s => s.cgpa).length).toFixed(2)
                : 'N/A';

            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <!-- Class Statistics -->
                <div class="class-details-grid">
                    <div class="detail-card">
                        <div class="detail-icon" style="background: #e3f2fd; color: #1976d2;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="detail-value">${studentCount}</div>
                        <div class="detail-label">Total Students</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon" style="background: #e8f5e9; color: #388e3c;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="detail-value">${activeStudents}</div>
                        <div class="detail-label">Active Students</div>
                    </div>
                
                    <div class="detail-card">
                        <div class="detail-icon" style="background: #fce4ec; color: #c2185b;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="detail-value">${avgCgpa}</div>
                        <div class="detail-label">Average CGPA</div>
                    </div>
                </div>

                <!-- Faculty Information -->
                ${faculties.length > 0 ? `
                <div class="students-section">
                    <h3 class="section-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Faculty Members
                    </h3>
                    <div class="students-grid">
                        ${faculties.map(faculty => `
                            <div class="student-card">
                                <div class="student-header">
                                    <div class="student-avatar" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                        ${faculty.faculty_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="student-info">
                                        <h6>${faculty.faculty_name}</h6>
                                        <small>${faculty.email}</small>
                                    </div>
                                </div>
                                <div class="student-stats">
                                    <div class="student-stat">
                                        <i class="fas fa-envelope"></i>
                                        <span>Faculty</span>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                <!-- Students List -->
                <div class="students-section">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i>
                        Students (${students.length})
                    </h3>
                    ${students.length > 0 ? `
                        <div class="students-grid">
                            ${students.map(student => `
                                <div class="student-card">
                                    <div class="student-header">
                                        <div class="student-avatar">
                                            ${student.name.split(' ').map(n => n.charAt(0)).join('').substring(0, 2).toUpperCase()}
                                        </div>
                                        <div class="student-info">
                                            <h6>${student.name}</h6>
                                            <small>${student.email || 'No email provided'}</small>
                                        </div>
                                    </div>
                                    <div class="student-stats">
                                        ${student.cgpa ? `
                                            <div class="student-stat">
                                                <i class="fas fa-star"></i>
                                                <span>CGPA: ${student.cgpa}</span>
                                            </div>
                                        ` : ''}
                                        <div class="student-stat">
                                            <i class="fas fa-${student.is_alumni == 1 ? 'graduation-cap' : 'user-graduate'}"></i>
                                            <span>${student.is_alumni == 1 ? 'Alumni' : 'Active'}</span>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div class="text-center py-4">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: #e9ecef; margin-bottom: 1rem;"></i>
                            <h4>No Students Found</h4>
                            <p class="text-muted">This class currently has no enrolled students.</p>
                        </div>
                    `}
                </div>
            `;
        }

        // Close modal when clicking outside
        document.getElementById('classModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeClassModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeClassModal();
            }
        });
    </script>
    
</body>
</html>