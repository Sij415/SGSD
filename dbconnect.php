<?php
// Database connection configuration
$host = "localhost";
$username = "connectdatabase";
$password = "ibw0LhO00N36";
$database = "SGSD";

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected successfully to the database.";
?>
