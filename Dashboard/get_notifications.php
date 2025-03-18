<?php

include '../dbconnect.php';
session_start();

// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's ID and role from the database
$query = "SELECT User_ID, Role FROM Users WHERE Email = ?";  
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id, $user_role);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Fetch notifications that haven't been cleared by this user
$sql = "SELECT Message, Created_At FROM Notifications WHERE Role = ? AND (Cleared IS NULL OR NOT FIND_IN_SET(?, Cleared)) ORDER BY Created_At DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $user_role, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
