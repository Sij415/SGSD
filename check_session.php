<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>


<?php

session_start();
require_once('../dbconnect.php');
date_default_timezone_set("Asia/Manila");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Verify Session and User ID
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];  // Get the email directly from session
$session_role = $_SESSION['role'];
$session_first_name = $_SESSION['first_name'];

// 3. Retrieve User Data (Based on User Email ONLY)
$sql = "SELECT User_ID, First_Name, Email, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();






if ($result->num_rows !== 1) {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) VALUES (NULL, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";



}





$row = $result->fetch_assoc();
$user_first_name = $row['First_Name'];
$user_role = $row['Role'];
$user_id_from_db = $row['User_ID'];  // User ID from the database
// 5. Role Check (After retrieving user data based on user_email)
// 5. Role Check (After retrieving user data based on user_email)
// 5. Role Check (After retrieving user data based on user_email)
if (isset($required_role)) {
    $required_role = explode(',', $required_role);

    // Get IP Address
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Retrieve user email from database
    $email_sql = "SELECT Email FROM Users WHERE User_ID = ?";
    $stmt_email = $conn->prepare($email_sql);
    $stmt_email->bind_param("i", $user_id_from_db);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    
    if ($row_email = $result_email->fetch_assoc()) {
        $email = $row_email['Email']; // Get email from database
    }
    
    $stmt_email->close(); // Close statement

    // Check if user role is allowed
    if (!in_array($user_role, $required_role)) {
        // Check if IP already exists in IP_Cooldown table
        $check_sql = "SELECT Attempts FROM IP_Cooldown WHERE IP_Address = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("s", $ip_address);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            // If IP exists, update the Attempts count and Last_Attempt time
            $update_sql = "UPDATE IP_Cooldown SET Attempts = Attempts + 1, Last_Attempt = NOW(), Locked_Until = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE IP_Address = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("s", $ip_address);
            $stmt_update->execute();
        } else {
            // If IP does not exist, insert a new record with the user's email
            $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) VALUES (?, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
            $stmt_log = $conn->prepare($log_sql);
            $stmt_log->bind_param("ss", $email, $ip_address);
            $stmt_log->execute();
        }

        // Redirect user to NotAllowed page
        header('Location: ../NotAllowed');
        exit();
    }
}


// 4. Compare Hashed User ID, Role, and First Name with Database Values
if ($user_id !== hash('sha256', $user_id_from_db) || 
    $session_role !== hash('sha256', $user_role) || 
    $session_first_name !== hash('sha256', $user_first_name)) {
    
    // 6. IP Cooldown (Consider Account Lockout as a stronger alternative)
    $ip_address = $_SERVER['REMOTE_ADDR'];

    $check_sql = "SELECT Attempts, Locked_Until FROM IP_Cooldown WHERE IP_Address = ?";
    $stmt_check = $conn->prepare($check_sql);
    if (!$stmt_check) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_check->bind_param("s", $ip_address);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($row_check = $result_check->fetch_assoc()) {
        $attempts = $row_check['Attempts'];
        $locked_until = new DateTime($row_check['Locked_Until']);
        $now = new DateTime();

        if ($attempts >= 10) {
            if ($locked_until > $now) {
                // Extend ban to 1 year
                $update_sql = "UPDATE IP_Cooldown SET Locked_Until = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE IP_Address = ?";
            } else {
                // Reset attempts if the lock time has passed
                $update_sql = "UPDATE IP_Cooldown SET Attempts = 1, Last_Attempt = NOW(), Locked_Until = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE IP_Address = ?";
            }
        } else {
            // Increment attempts and extend ban by 1 day
            $update_sql = "UPDATE IP_Cooldown SET Attempts = Attempts + 1, Last_Attempt = NOW(), Locked_Until = DATE_ADD(Locked_Until, INTERVAL 1 DAY) WHERE IP_Address = ?";
        }

        $stmt_update = $conn->prepare($update_sql);
        if (!$stmt_update) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_update->bind_param("s", $ip_address);
        $stmt_update->execute();
    } else {
        // First-time offender
        $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) VALUES (NULL, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
        $stmt_log = $conn->prepare($log_sql);
        if (!$stmt_log) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_log->bind_param("s", $ip_address);
        $stmt_log->execute();
    }

    session_destroy();
    header('Location: ../CoolDown');
 
    exit();
}



$stmt->close();
$conn->close();


