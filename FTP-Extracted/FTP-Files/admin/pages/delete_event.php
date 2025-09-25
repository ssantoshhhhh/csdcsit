<?php
require '../utils/connect.php'; // Ensure this is the correct path to your database connection

header('Content-Type: application/json');

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Prepare delete query
    $query = "DELETE FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Event deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete event."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Event ID missing."]);
}
?>
