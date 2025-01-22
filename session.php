<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./");
    exit();
}

// Check if the user is an admin
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ./");
    exit();
}
?>