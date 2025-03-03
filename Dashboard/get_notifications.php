<?php
include '../dbconnect.php';

session_start();
// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's first name and role from the database
$query = "SELECT First_Name, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name, $user_role);
$stmt->fetch();
$stmt->close();

$sql = "SELECT Message FROM Notifications WHERE Role = ? ORDER BY Created_At DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_role);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
