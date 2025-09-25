<?php
include './connect.php';

if (!isset($conn)) {
    die('Database connection failed.');
}

$errors = [];

// Alter appreciations table
$query1 = "ALTER TABLE `appreciations` CHANGE `event_id` `event_id` INT(11) NULL;";
if (mysqli_query($conn, $query1)) {
    echo "<p><b>Appreciations table:</b> Schema updated successfully. The 'event_id' is now optional.</p>";
} else {
    $errors[] = "Error updating appreciations table: " . mysqli_error($conn);
}

// Alter penalties table
$query2 = "ALTER TABLE `penalties` CHANGE `event_id` `event_id` INT(11) NULL;";
if (mysqli_query($conn, $query2)) {
    echo "<p><b>Penalties table:</b> Schema updated successfully. The 'event_id' is now optional.</p>";
} else {
    $errors[] = "Error updating penalties table: " . mysqli_error($conn);
}

if (empty($errors)) {
    echo "<h1>Database schema updated successfully!</h1>";
    echo "<p style='color: red; font-weight: bold;'>For security, please delete this file (update_appreciations_schema.php) from your server now.</p>";
} else {
    echo "<h1>Errors occurred:</h1>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>" . $error . "</p>";
    }
}

mysqli_close($conn);
?>