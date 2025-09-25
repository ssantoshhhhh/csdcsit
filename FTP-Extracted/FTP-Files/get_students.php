<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "connect.php";

header('Content-Type: application/json');

if (!isset($_GET['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

$action = $_GET['action'];

if ($action === 'get_top_performers') {
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $branch = isset($_GET['branch']) ? mysqli_real_escape_string($conn, $_GET['branch']) : '';
    $year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : 'active';
    
    // Build WHERE conditions
    $where_conditions = [];
    
    if (!empty($branch)) {
        $where_conditions[] = "s.branch = '$branch'";
    }
    
    if ($year > 0) {
        $where_conditions[] = "c.year = $year";
    }
    
    if ($status === 'active') {
        $where_conditions[] = "s.is_alumni = 0";
    } elseif ($status === 'alumni') {
        $where_conditions[] = "s.is_alumni = 1";
    }
    // For 'all', we don't add any condition
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total 
        FROM students s 
        LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        $where_clause";
    
    $count_result = mysqli_query($conn, $count_query);
    $total = 0;
    if ($count_result) {
        $count_data = mysqli_fetch_assoc($count_result);
        $total = $count_data['total'];
    }
    
    // Get students data
    $query = "SELECT 
        s.student_id,
        s.name,
        s.branch,
        s.is_alumni,
        c.year,
        sp.cgpa,
        sp.skills
    FROM students s 
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
    LEFT JOIN classes c ON s.class_id = c.class_id 
    $where_clause
    ORDER BY 
        CASE WHEN sp.cgpa IS NULL THEN 1 ELSE 0 END,
        sp.cgpa DESC,
        s.name ASC
    LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $students = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'branch' => $row['branch'],
                'year' => $row['year'],
                'cgpa' => $row['cgpa'],
                'is_alumni' => $row['is_alumni'],
                'skills' => $row['skills']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'students' => $students,
        'total' => $total
    ]);
    
} elseif ($action === 'get_student_details') {
    $student_id = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : '';
    
    if (empty($student_id)) {
        echo json_encode(['success' => false, 'message' => 'Student ID required']);
        exit;
    }
    
    // Get detailed student information
    $query = "SELECT 
        s.student_id,
        s.name,
        s.email,
        s.branch,
        s.section,
        s.is_alumni,
        c.year,
        c.semester,
        c.academic_year,
        sp.summary,
        sp.skills,
        sp.cgpa,
        sp.projects,
        sp.certifications,
        sp.achievements,
        sp.social_links,
        h.name as house_name
    FROM students s 
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
    LEFT JOIN classes c ON s.class_id = c.class_id 
    LEFT JOIN houses h ON s.hid = h.hid
    WHERE s.student_id = '$student_id'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // Get event participation stats
        $participation_stats = [
            'participated' => 0,
            'won' => 0,
            'organized' => 0,
            'total_points' => 0
        ];
        
        // Count participations
        $participation_query = "SELECT COUNT(*) as count, SUM(points) as points FROM participants WHERE student_id = '$student_id'";
        $participation_result = mysqli_query($conn, $participation_query);
        if ($participation_result) {
            $data = mysqli_fetch_assoc($participation_result);
            $participation_stats['participated'] = $data['count'];
            $participation_stats['total_points'] += (int)$data['points'];
        }
        
        // Count wins
        $wins_query = "SELECT COUNT(*) as count, SUM(points) as points FROM winners WHERE student_id = '$student_id'";
        $wins_result = mysqli_query($conn, $wins_query);
        if ($wins_result) {
            $data = mysqli_fetch_assoc($wins_result);
            $participation_stats['won'] = $data['count'];
            $participation_stats['total_points'] += (int)$data['points'];
        }
        
        // Count organized events
        $organized_query = "SELECT COUNT(*) as count, SUM(points) as points FROM organizers WHERE student_id = '$student_id'";
        $organized_result = mysqli_query($conn, $organized_query);
        if ($organized_result) {
            $data = mysqli_fetch_assoc($organized_result);
            $participation_stats['organized'] = $data['count'];
            $participation_stats['total_points'] += (int)$data['points'];
        }
        
        // Add participation stats to student data
        $student['participation_stats'] = $participation_stats;
        
        echo json_encode([
            'success' => true,
            'student' => $student
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
    
} elseif ($action === 'get_skills_analysis') {
    // Get skills analysis data
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
    
    echo json_encode([
        'success' => true,
        'skills' => $skills_data
    ]);
    
} elseif ($action === 'get_branch_stats') {
    // Get detailed branch statistics
    $branch_stats = [];
    
    $query = "SELECT 
        s.branch,
        COUNT(*) as total_students,
        SUM(CASE WHEN s.is_alumni = 0 THEN 1 ELSE 0 END) as active_students,
        SUM(CASE WHEN s.is_alumni = 1 THEN 1 ELSE 0 END) as alumni_count,
        AVG(CASE WHEN sp.cgpa IS NOT NULL THEN sp.cgpa END) as avg_cgpa,
        MAX(sp.cgpa) as max_cgpa,
        MIN(sp.cgpa) as min_cgpa
    FROM students s 
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id 
    GROUP BY s.branch 
    ORDER BY total_students DESC";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $branch_stats[] = [
                'branch' => $row['branch'],
                'total_students' => (int)$row['total_students'],
                'active_students' => (int)$row['active_students'],
                'alumni_count' => (int)$row['alumni_count'],
                'avg_cgpa' => round($row['avg_cgpa'], 2),
                'max_cgpa' => $row['max_cgpa'],
                'min_cgpa' => $row['min_cgpa']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'branch_stats' => $branch_stats
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>