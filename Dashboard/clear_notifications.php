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
$stmt->bind_result($user_id_from_db, $user_role);
$stmt->fetch();
$stmt->close();

if (!$user_id_from_db) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Update notifications to mark them as cleared for this user
$sql = "UPDATE Notifications 
        SET Cleared = CASE 
            WHEN Cleared IS NULL OR Cleared = '' THEN ? 
            ELSE CONCAT_WS(',', Cleared, ?) 
        END
        WHERE Role = ? AND (Cleared IS NULL OR NOT FIND_IN_SET(?, Cleared))";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $user_id_from_db, $user_id_from_db, $user_role, $user_id_from_db);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Notifications cleared"]);
} else {
    echo json_encode(["success" => false, "message" => "No notifications to clear"]);
}

$stmt->close();
?>
