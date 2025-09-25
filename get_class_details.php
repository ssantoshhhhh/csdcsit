<?php
session_start();
include "connect.php";

header('Content-Type: application/json');

if (!isset($_GET['class_id'])) {
    echo json_encode(['success' => false, 'message' => 'Class ID is required']);
    exit;
}

$class_id = mysqli_real_escape_string($conn, $_GET['class_id']);

try {
    // Get students in the class
    $students_query = "SELECT 
        s.student_id,
        s.name,
        s.email,
        s.branch,
        s.section,
        s.is_alumni,
        sp.cgpa,
        sp.skills
    FROM students s 
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
    WHERE s.class_id = '$class_id'
    ORDER BY s.name ASC";
    
    $students_result = mysqli_query($conn, $students_query);
    $students = [];
    
    if ($students_result) {
        while ($row = mysqli_fetch_assoc($students_result)) {
            $students[] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'branch' => $row['branch'],
                'section' => $row['section'],
                'is_alumni' => $row['is_alumni'],
                'cgpa' => $row['cgpa'],
                'skills' => $row['skills']
            ];
        }
    }
    
    // Get faculty assigned to the class
    $faculty_query = "SELECT 
        f.faculty_id,
        f.faculty_name,
        f.email,
        f.phone_number
    FROM faculties f 
    WHERE f.class_id = '$class_id' AND f.is_active = 1
    ORDER BY f.faculty_name ASC";
    
    $faculty_result = mysqli_query($conn, $faculty_query);
    $faculties = [];
    
    if ($faculty_result) {
        while ($row = mysqli_fetch_assoc($faculty_result)) {
            $faculties[] = [
                'faculty_id' => $row['faculty_id'],
                'faculty_name' => $row['faculty_name'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number']
            ];
        }
    }
    
    // Get class information
    $class_query = "SELECT 
        c.class_id,
        c.academic_year,
        c.year,
        c.semester,
        c.branch,
        c.section,
        COUNT(s.student_id) as student_count
    FROM classes c
    LEFT JOIN students s ON c.class_id = s.class_id
    WHERE c.class_id = '$class_id'
    GROUP BY c.class_id";
    
    $class_result = mysqli_query($conn, $class_query);
    $class_info = null;
    
    if ($class_result && mysqli_num_rows($class_result) > 0) {
        $class_info = mysqli_fetch_assoc($class_result);
    }
    
    // Get additional statistics
    $stats = [
        'total_students' => count($students),
        'active_students' => count(array_filter($students, function($s) { return $s['is_alumni'] == 0; })),
        'alumni_count' => count(array_filter($students, function($s) { return $s['is_alumni'] == 1; })),
        'students_with_cgpa' => count(array_filter($students, function($s) { return !empty($s['cgpa']); })),
        'avg_cgpa' => 0
    ];
    
    // Calculate average CGPA
    $cgpa_values = array_filter(array_map(function($s) { return $s['cgpa']; }, $students));
    if (!empty($cgpa_values)) {
        $stats['avg_cgpa'] = round(array_sum($cgpa_values) / count($cgpa_values), 2);
    }
    
    echo json_encode([
        'success' => true,
        'students' => $students,
        'faculties' => $faculties,
        'class_info' => $class_info,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>