<?php
include './connect.php';

if ($conn) {
    $sql = "ALTER TABLE `penalties` CHANGE `event_id` `event_id` INT(11) NULL DEFAULT NULL;";
    if (mysqli_query($conn, $sql)) {
        echo "Table 'penalties' altered successfully to allow NULL in 'event_id'.";
    } else {
        echo "Error altering table: " . mysqli_error($conn);
    }
    mysqli_close($conn);
} else {
    echo "Database connection failed.";
}
?>