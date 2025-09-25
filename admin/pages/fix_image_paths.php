<?php
require '../utils/connect.php';

echo "<h2>Fixing Event Image Paths</h2>";

// Get all events with image paths that don't start with 'admin/pages/'
$query = "SELECT event_id, image_path FROM events WHERE image_path IS NOT NULL AND image_path != '' AND image_path NOT LIKE 'admin/pages/%'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " events with incorrect image paths:</p>";
    echo "<ul>";
    
    while ($row = $result->fetch_assoc()) {
        $old_path = $row['image_path'];
        $new_path = "admin/pages/" . $old_path;
        
        // Update the image path in the database
        $update_query = "UPDATE events SET image_path = ? WHERE event_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_path, $row['event_id']);
        
        if ($stmt->execute()) {
            echo "<li>✅ Event ID {$row['event_id']}: Updated '{$old_path}' to '{$new_path}'</li>";
        } else {
            echo "<li>❌ Event ID {$row['event_id']}: Failed to update - " . $stmt->error . "</li>";
        }
    }
    
    echo "</ul>";
    echo "<p><strong>Image path fix completed!</strong></p>";
} else {
    echo "<p>✅ No events found with incorrect image paths. All image paths are already correct.</p>";
}

$conn->close();
?>
