<?php
$host = "localhost"; // Hostname of the MariaDB server
$username = "root";  // Your database username
$password = "tifpis-4timbY-wubgoh";      // Your database password
$database = "SGSD";  // Your database name

// Create a connection to MariaDB
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to the database.";
?>
