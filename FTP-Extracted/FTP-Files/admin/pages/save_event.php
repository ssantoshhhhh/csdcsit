<?php
include '../utils/connect.php'; // Ensure correct database connection

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Check if event_id exists
    if (!isset($_POST['event_id'])) {
        die("Error: Event ID is missing from the request.");
    }

    $event_id = intval($_POST['event_id']);
    $title = $_POST['event_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $start_time = $_POST['timings'] ?? '';
    $participate_points = $_POST['participate_points'] ?? 0;
    $winner_points = $_POST['winner_points'] ?? 0;
    $organiser_points = $_POST['organiser_points'] ?? 0;

    // Handle organizers (ensure valid JSON)
    $selected_students = isset($_POST['selected_students']) ? json_decode($_POST['selected_students'], true) : [];

    // Debugging: Check if form values are set correctly
    if (empty($title) || empty($description) || empty($venue) || empty($event_date) || empty($start_time)) {
        die("Error: Required fields are missing.");
    }

    // Handle file upload
    $image_path = null;
    if (isset($_FILES['house_photo']) && $_FILES['house_photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "files/events/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create directory if missing
        }

        $fileName = basename($_FILES['house_photo']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['house_photo']['tmp_name'], $targetFilePath)) {
                $image_path = "admin/pages/" . $targetFilePath; // Store the full path for events_overview.php
            }
        }
    }
    $accept_registrations = ($_POST['accept_registrations'] === 'true') ? 1 : 0;

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update the event record
        if ($image_path) {
            $sql = "UPDATE events SET title=?, description=?, venue=?, event_date=?, start_time=?, image_path=?, 
                    participate_points=?, winner_points=?, accept_registrations=?, organiser_points=? 
                    WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing update query: " . $conn->error);
            }
            $stmt->bind_param("ssssssiisii", $title, $description, $venue, $event_date, $start_time, 
                $image_path, $participate_points, $winner_points, $accept_registrations, $organiser_points, $event_id);
        } else {
            $sql = "UPDATE events SET title=?, description=?, venue=?, event_date=?, start_time=?, 
                    participate_points=?, winner_points=?, accept_registrations=?, organiser_points=? 
                    WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing update query without image: " . $conn->error);
            }
            $stmt->bind_param("sssssiisii", $title, $description, $venue, $event_date, $start_time, 
                $participate_points, $winner_points, $accept_registrations, $organiser_points, $event_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error executing event update query: " . $stmt->error);
        }
        
        // Delete existing organizers for this event
        $delete_sql = "DELETE FROM organizers WHERE event_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            throw new Exception("Error preparing delete organizers query: " . $conn->error);
        }
        $delete_stmt->bind_param("i", $event_id);
        if (!$delete_stmt->execute()) {
            throw new Exception("Error executing delete organizers query: " . $delete_stmt->error);
        }
        
        // Insert new organizers
        if (!empty($selected_students)) {
            $insert_sql = "INSERT INTO organizers (student_id, event_id, points) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("Error preparing insert organizers query: " . $conn->error);
            }
            
            foreach ($selected_students as $student_id) {
                $insert_stmt->bind_param("sii", $student_id, $event_id, $organiser_points);
                if (!$insert_stmt->execute()) {
                    throw new Exception("Error executing insert organizers query: " . $insert_stmt->error);
                }
            }
        }
        
        $conn->commit();
        
        header("Location: events.php");
        exit();
    } 
    catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>