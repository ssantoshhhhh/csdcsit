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

// Fetch todos for this house (using events table for now)
$query = "SELECT event_id, title, description FROM events WHERE hid=$hid ORDER BY event_id DESC";
$result = $conn->query($query);

$todos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $todos[] = $row;
    }
    echo json_encode(['success' => true, 'todos' => $todos]);
} else {
    echo json_encode(['success' => true, 'todos' => []]);
}

$conn->close();
?>