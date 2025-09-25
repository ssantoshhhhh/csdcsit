<?php
session_start();

// Clear all session variables for all user types
session_unset();

// Destroy the session
session_destroy();

// Redirect to unified login page
header('Location: login.php');
exit();
?>