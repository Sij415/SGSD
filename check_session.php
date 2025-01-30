<?php
session_start();
include('../dbconnect.php');
date_default_timezone_set("Asia/Manila");

// 1. Verify Session and User ID
if (!isset($_SESSION['user_id'])) {
    header('Location: ../');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];  // Get the email directly from session
$session_role = $_SESSION['role'];
$session_first_name = $_SESSION['first_name'];


// 3. Retrieve User Data (Based on User Email ONLY)
$sql = "SELECT User_ID, First_Name, Email, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();


$row = $result->fetch_assoc();
$user_first_name = $row['First_Name'];
$user_role = $row['Role'];
$user_id_from_db = $row['User_ID'];  // User ID from the database





// *** REMOVE ANY OLD AUTHENTICATION CHECK USING SESSION VARIABLES (role, first name, user_id) ***

// 5. Role Check (After retrieving user data based on user_email)
if (isset($required_role) && $user_role !== $required_role) {
    header('Location: ../NotAllowed');
    exit();
}

// 6. IP Cooldown (Consider Account Lockout as a stronger alternative)
$ip_address = $_SERVER['REMOTE_ADDR'];

$check_sql = "SELECT Attempts, Locked_Until FROM IP_Cooldown WHERE IP_Address = ?";
$stmt_check = $conn->prepare($check_sql);
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
    $stmt_update->bind_param("s", $ip_address);
    $stmt_update->execute();

} else {
    // First-time offender
    $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) VALUES (NULL, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
    $stmt_log = $conn->prepare($log_sql);
    $stmt_log->bind_param("s", $ip_address);
    $stmt_log->execute();
}









if ($result->num_rows !== 1) {
    session_destroy();
    header('Location: ../CoolDown');
    exit();
}


// 4. Compare Hashed User ID, Role, and First Name with Database Values
if ($user_id!== hash('sha256', $user_id_from_db) || 
    $session_role !== hash('sha256', $user_role) || 
    $session_first_name !== hash('sha256', $user_first_name)) {
    session_destroy();
    header('Location: ../CoolDown');
    
    exit();

}














$stmt_check->close();
// ... rest of your code ...

$stmt->close();
$conn->close();
?>
