<?php
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "SGSD";

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected successfully to the database.";
?>
