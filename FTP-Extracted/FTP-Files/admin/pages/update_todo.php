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

if ($data && isset($data['event_id']) && isset($data['title']) && isset($data['description'])) {
    $event_id = (int)$data['event_id'];
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description']);
    
    // Update the todo, ensuring it belongs to the current house (using events table for now)
    $query = "UPDATE events SET title='$title', description='$description' WHERE event_id=$event_id AND hid=$hid";
    
    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}

$conn->close();
?>