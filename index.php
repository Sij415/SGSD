<?php
session_start();
require 'dbconnect.php'; // Ensure this file contains your database connection ($conn)

if (!isset($_SESSION['email'])) {
    header('Location: ./Login');
    exit();
} else {
    // Check if email exists in the database
    $email = $_SESSION['email'];
    $query = "SELECT Email FROM Users WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Email from session does not exist in the database
        session_destroy(); // Destroy the session for security
        header('Location: ./Login');
        exit();
    }

    // User exists, proceed to dashboard
    header('Location: ./Dashboard');
    exit();
}
?>
