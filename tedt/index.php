<?php
session_start();
include('../dbconnect.php');

// Ensure the user is logged in and check their session data
if (!isset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role'])) {
    // If session variables are not set, redirect to login page
    header('Location: ../');
    exit();
}

// Get the user role from session
$user_role = $_SESSION['user_role'];

// Role-based access control: Only admin and staff should access this page
if (!in_array($user_role, ['admin', 'staff'])) {
    // If the user is neither admin nor staff, redirect them to not allowed page
    header('Location: ../not_allowed.php');
    exit();
}

// Your admin/staff page content goes here
echo "<h1>Welcome, $user_role!</h1>";
echo "<p>This page is accessible only to admin and staff members.</p>";
?>
