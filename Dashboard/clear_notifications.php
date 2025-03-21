<?php

include '../dbconnect.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_email = $_SESSION['email'];

// Get User ID
$query = "SELECT User_ID FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Update notifications: Ensure NULL is handled properly
$sql = "UPDATE Notifications 
        SET Cleared = 
            CASE 
                WHEN Cleared IS NULL OR Cleared = '' THEN ?
                ELSE CONCAT(Cleared, ',', ?) 
            END
        WHERE (Cleared IS NULL OR Cleared NOT LIKE CONCAT('%', ?, '%'))";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user_id, $user_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Notifications cleared"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to clear notifications"]);
}

$stmt->close();
$conn->close();

?>
