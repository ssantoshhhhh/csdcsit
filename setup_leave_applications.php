<?php
include './connect.php';

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Setting up Leave Applications Table...</h2>";

// Read SQL from file
$sql = file_get_contents('create_leave_applications_table.sql');

if ($sql === false) {
    die('Error reading SQL file');
}

// Execute SQL
if (mysqli_query($conn, $sql)) {
    echo "<p>✅ Leave applications table created/updated successfully</p>";
} else {
    echo "<p>❌ Error creating/updating table: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p><a href='student_leave_application.php'>Return to Leave Application</a></p>";
?>