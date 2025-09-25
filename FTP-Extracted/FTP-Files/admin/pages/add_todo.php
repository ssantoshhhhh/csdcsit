<?php
session_start();
include '../utils/connect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the house ID for the logged-in admin
$sql = "SELECT hid FROM admins WHERE username='" . $_SESSION['username'] . "'";
$result = $conn->query($sql);
$hid = $result->fetch_assoc()['hid'];

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['title']) && isset($data['description'])) {
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description']);
    
    // Insert the new todo (using events table for now)
    $query = "INSERT INTO events (title, description, hid, admin_id, event_date, start_time, accept_registrations) VALUES ('$title', '$description', $hid, 1, CURDATE(), '00:00:00', 0)";
    
    if ($conn->query($query) === TRUE) {
        // Return success with the new ID
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}

$conn->close();
?>