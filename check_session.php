<?php
session_start();
require_once('../dbconnect.php');
date_default_timezone_set("Asia/Manila");

// Enable error reporting (For debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session variables exist
if (!isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role'], $_SESSION['first_name'])) {
    header('Location: ../Login');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$session_role = $_SESSION['role'];
$session_first_name = $_SESSION['first_name'];

// Get IP Address
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Retrieve user details from database
$sql = "SELECT User_ID, First_Name, Email, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Log unauthorized access attempt (IP Cooldown)
    $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) 
                VALUES (NULL, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
    $stmt_log = $conn->prepare($log_sql);
    $stmt_log->bind_param("s", $ip_address);
    $stmt_log->execute();

    session_destroy();
    header('Location: ../Login');
    exit();
}

$row = $result->fetch_assoc();
$user_first_name = $row['First_Name'];
$user_role = $row['Role'];
$user_id_from_db = $row['User_ID'];

// Verify session integrity
if ($user_id !== hash('sha256', $user_id_from_db) || 
    $session_role !== hash('sha256', $user_role) || 
    $session_first_name !== hash('sha256', $user_first_name)) {
    
    applyIPCooldown($conn, $ip_address);
    session_destroy();
    header('Location: ../CoolDown');
    exit();
}

// Role-based access control
if (isset($required_role)) {
    $required_roles = explode(',', $required_role);

    if (!in_array($user_role, $required_roles)) {
        applyIPCooldown($conn, $ip_address);
        header('Location: ../NotAllowed');
        exit();
    }
}

// Close connections
$stmt->close();
$conn->close();

// Function to apply IP cooldown for failed attempts
function applyIPCooldown($conn, $ip_address) {
    $check_sql = "SELECT Attempts, Locked_Until FROM IP_Cooldown WHERE IP_Address = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $ip_address);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($row_check = $result_check->fetch_assoc()) {
        $attempts = $row_check['Attempts'];
        $locked_until = new DateTime($row_check['Locked_Until']);
        $now = new DateTime();

        if ($attempts >= 10 && $locked_until > $now) {
            $update_sql = "UPDATE IP_Cooldown SET Locked_Until = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE IP_Address = ?";
        } else {
            $update_sql = "UPDATE IP_Cooldown SET Attempts = Attempts + 1, Last_Attempt = NOW(), 
                           Locked_Until = DATE_ADD(Locked_Until, INTERVAL 1 DAY) WHERE IP_Address = ?";
        }
    } else {
        $update_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt, Locked_Until) 
                       VALUES (NULL, ?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
    }

    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("s", $ip_address);
    $stmt_update->execute();
}
?>
