<?php
session_start();
include('../dbconnect.php');

// Check if user is logged in and if session variables are set
if (!isset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role'])) {
    // If session variables are not set, redirect to login
    header('Location: ../login.php');
    exit();
}

// Get session values
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// IP address of the user
$ip_address = $_SERVER['REMOTE_ADDR'];

// Check if the user is blocked due to failed login attempts
$sql = "SELECT * FROM IP_Cooldown WHERE Email = ? AND IP_Address = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_email, $ip_address);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has attempted login before, fetch data
    $row = $result->fetch_assoc();
    $attempts = $row['Attempts'];
    $locked_until = strtotime($row['Locked_Until']);
    $last_attempt = strtotime($row['Last_Attempt']);
    
    // Check if the user is locked out
    if ($locked_until > time()) {
        // User is still locked, redirect to not allowed page
        header('Location: ../not_allowed.php');
        exit();
    }

    // Check if the number of attempts exceeded limit (e.g., 5 attempts)
    if ($attempts >= 5 && (time() - $last_attempt) < 600) {
        // Lock the account for 10 minutes
        $locked_until = date('Y-m-d H:i:s', time() + 600);
        $update_sql = "UPDATE IP_Cooldown SET Locked_Until = ? WHERE Email = ? AND IP_Address = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sss", $locked_until, $user_email, $ip_address);
        $stmt->execute();

        // Redirect user to not allowed page
        header('Location: ../not_allowed.php');
        exit();
    }
}

// Validate user session in the database
$sql = "SELECT User_ID, Email, Role FROM Users WHERE User_ID = ? AND Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Invalid session, log the IP and add to IP_Cooldown table
    $log_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt) 
                VALUES (?, ?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE Attempts = Attempts + 1, Last_Attempt = NOW()";
    $stmt = $conn->prepare($log_sql);
    $stmt->bind_param("ss", $user_email, $ip_address);
    $stmt->execute();

    // Redirect user to not allowed page
    header('Location: ../not_allowed.php');
    exit();
}

// Role-based access control
switch ($user_role) {
    case 'admin':
        // Pages available only to admin
        break;

    case 'admin_and_staff':
        // Pages available only to admin and staff
        if (!in_array($user_role, ['admin', 'staff'])) {
            header('Location: ../not_allowed.php');
            exit();
        }
        break;

    case 'admin_and_driver':
        // Pages available only to admin and driver
        if (!in_array($user_role, ['admin', 'driver'])) {
            header('Location: ../not_allowed.php');
            exit();
        }
        break;

    default:
        // If role is invalid, redirect to not allowed
        header('Location: ../not_allowed.php');
        exit();
}

// Ensure no further code is executed if blocked
if (isset($_SESSION['login_attempts_blocked']) && (time() - $_SESSION['login_attempts_blocked']) < 600) {
    header('Location: ../not_allowed.php');
    exit();
}

$stmt->close();
$conn->close();
?>
