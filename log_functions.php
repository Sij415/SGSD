<?php
include('../dbconnect.php');
function logActivity($conn, $user_id, $activity, $order_id = NULL) {
    $date = date('Y-m-d');  // Get the current date
    $time = date('H:i:s');  // Get the current time

    // Prepare SQL query
    $stmt = $conn->prepare("INSERT INTO Logs (User_ID, Order_ID, Date, Time, Activity) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $order_id, $date, $time, $activity);

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