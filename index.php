<?php
// Start the session
session_start();

// Check if the session token exists
if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
    // User is logged in, redirect to /Dashboard
    header("Location: /Dashboard");
    exit;
} else {
    // User is not logged in or session is invalid, redirect to /Login
    header("Location: /Login");
    exit;
}

?>