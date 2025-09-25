<?php
session_start();
header('Content-Type: application/json');

// Check if faculty is logged in
if (!isset($_SESSION['faculty_logged_in']) || !$_SESSION['faculty_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login first.']);
    exit();
}

include './connect.php';

try {
    // Get form data
    $attendance_data = json_decode($_POST['attendance_data'] ?? '[]', true);
    $section_id = $_POST['attendance_section'] ?? '';
    $attendance_date = $_POST['attendance_date'] ?? '';
    $attendance_session = $_POST['attendance_session'] ?? '';
    
    if (empty($attendance_data) || empty($section_id) || empty($attendance_date) || empty($attendance_session)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit();
    }
    
    // Validate that faculty has access to this section
    $faculty_id = $_SESSION['faculty_id'] ?? null;
    $faculty_sections = $_SESSION['faculty_sections'] ?? '';
    $assigned_sections = array_filter(array_map('trim', explode(',', $faculty_sections)));
    
    if (!in_array($section_id, $assigned_sections)) {
        echo json_encode(['success' => false, 'message' => 'Access denied. You are not assigned to this section.']);
        exit();
    }
    
    $saved_count = 0;
    $errors = [];
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    foreach ($attendance_data as $attendance) {
        $student_id = $attendance['student_id'] ?? '';
        $status = $attendance['status'] ?? '';
        
        if (empty($student_id) || empty($status)) {
            continue;
        }
        
        // Check if attendance record exists
        $check_query = "SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ? AND session = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "sss", $student_id, $attendance_date, $attendance_session);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_query = "UPDATE student_attendance SET status = ?, updated_at = NOW() WHERE student_id = ? AND attendance_date = ? AND session = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ssss", $status, $student_id, $attendance_date, $attendance_session);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $saved_count++;
            } else {
                $errors[] = "Failed to update attendance for student $student_id";
            }
        } else {
            // Insert new record
            $insert_query = "INSERT INTO student_attendance (student_id, attendance_date, session, status, marked_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "sssss", $student_id, $attendance_date, $attendance_session, $status, $faculty_id);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $saved_count++;
            } else {
                $errors[] = "Failed to save attendance for student $student_id";
            }
        }
    }
    
    if (empty($errors)) {
        // Commit transaction
        mysqli_commit($conn);
        echo json_encode([
            'success' => true, 
            'message' => "Attendance saved successfully for $saved_count students.",
            'saved_count' => $saved_count
        ]);
    } else {
        // Rollback transaction
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false, 
            'message' => 'Some records failed to save: ' . implode(', ', $errors),
            'errors' => $errors
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    error_log("Attendance save error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while saving attendance. Please try again.'
    ]);
}
?>
