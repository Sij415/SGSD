<?php
include('../dbconnect.php');

function logActivity($conn, $user_id, $activity, $order_id = NULL) {
    $date = date('Y-m-d');  // Get the current date
    $time = date('H:i:s');  // Get the current time

    // Fetch the full name of the user
    $userQuery = $conn->prepare("SELECT First_Name, Last_Name FROM Users WHERE User_ID = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
        $full_name = $userData['First_Name'] . " " . $userData['Last_Name'];
    } else {
        $full_name = "Unknown User"; // Fallback if no user is found
    }
    $userQuery->close();

    // Prepare SQL query to insert log
    $stmt = $conn->prepare("INSERT INTO Logs (User_ID, Order_ID, Date, Time, Activity, User_Full_Name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $user_id, $order_id, $date, $time, $activity, $full_name);

    // Execute query and check for errors
    if ($stmt->execute()) {
        return true; // Successfully logged
    } else {
        error_log("Log Error: " . $stmt->error);
        return false; // Logging failed
    }

    $stmt->close();
}
?>