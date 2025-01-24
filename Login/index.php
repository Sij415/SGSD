<?php
// Include the database connection
include('../dbconnect.php');
session_start();

// Check if the admin has enabled sign-ups
$signup_enabled_query = "SELECT Value FROM Settings WHERE Setting_Key = 'SignUpEnabled'";
$signup_enabled_result = $conn->query($signup_enabled_query);
$signup_enabled = $signup_enabled_result->fetch_assoc()['Value'] ?? 0;

// Function to get the client's IP address
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Cool-down period (in seconds)
$cooldown_period = 600; // 10 minutes

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $ip_address = getClientIP();

    // Check if this IP is under cooldown
    $sql_cooldown = "SELECT * FROM IP_Cooldown WHERE IP_Address = ?";
    $stmt_cooldown = $conn->prepare($sql_cooldown);
    $stmt_cooldown->bind_param("s", $ip_address);
    $stmt_cooldown->execute();
    $cooldown_result = $stmt_cooldown->get_result();

    if ($cooldown_result->num_rows > 0) {
        $cooldown_data = $cooldown_result->fetch_assoc();
        $last_attempt = strtotime($cooldown_data['Last_Attempt']);
        $locked_until = strtotime($cooldown_data['Locked_Until']);
        $current_time = time();

        if ($current_time < $locked_until) {
            $remaining_time = $locked_until - $current_time;
            $error = "Too many failed attempts. Please try again in $remaining_time seconds.";
        } else {
            // Cool-down expired, reset attempts
            $reset_sql = "UPDATE IP_Cooldown SET Attempts = 0, Locked_Until = NULL WHERE IP_Address = ?";
            $reset_stmt = $conn->prepare($reset_sql);
            $reset_stmt->bind_param("s", $ip_address);
            $reset_stmt->execute();
        }
    }

    // If no cooldown or cooldown expired, proceed with login attempt
    if (!isset($error)) {
        // Prepare query to get user data based on email
        $sql = "SELECT * FROM Users WHERE Email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['Password_hash'])) {
                    // Login successful, reset cooldown
                    $delete_sql = "DELETE FROM IP_Cooldown WHERE IP_Address = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("s", $ip_address);
                    $delete_stmt->execute();

                    // Store user data in session
                    $_SESSION['user_id'] = $user['User_ID'];
                    $_SESSION['role'] = $user['Role'];
                    $_SESSION['first_name'] = $user['First_Name'];

                    // Redirect to dashboard based on the role
                    header("Location: ../Dashboard");
                    exit();
                } else {
                    // Invalid password, handle cooldown
                    $error = handleCooldown($ip_address, $email, $cooldown_result, $cooldown_data ?? null, $cooldown_period);
                }
            } else {
                // Email does not exist, handle cooldown
                $error = handleCooldown($ip_address, $email, $cooldown_result, $cooldown_data ?? null, $cooldown_period);
            }
            $stmt->close();
        } else {
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Function to handle cooldown logic
function handleCooldown($ip_address, $email, $cooldown_result, $cooldown_data, $cooldown_period) {
    global $conn;

    if ($cooldown_result->num_rows > 0) {
        $attempts = $cooldown_data['Attempts'] + 1;

        if ($attempts >= 5) {
            $locked_until = date("Y-m-d H:i:s", time() + $cooldown_period);
            $update_sql = "UPDATE IP_Cooldown SET Attempts = ?, Last_Attempt = NOW(), Locked_Until = ? WHERE IP_Address = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iss", $attempts, $locked_until, $ip_address);
            $update_stmt->execute();
            return "Too many failed attempts. Please try again in $cooldown_period seconds.";
        } else {
            $update_sql = "UPDATE IP_Cooldown SET Attempts = ?, Last_Attempt = NOW() WHERE IP_Address = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("is", $attempts, $ip_address);
            $update_stmt->execute();
            return "The username or password you entered is incorrect. You have " . (5 - $attempts) . " attempts remaining.";
        }
    } else {
        // First failed attempt for this IP
        $insert_sql = "INSERT INTO IP_Cooldown (Email, IP_Address, Attempts, Last_Attempt) VALUES (?, ?, 1, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $email, $ip_address);
        $insert_stmt->execute();
        return "The username or password you entered is incorrect. You have 4 attempts remaining.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="../style/style.css">
</head>

<header class="main-header">
    <nav class="main-nav">
        <a href="../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
    </nav>
</header>

<body>
    <div class="login-container">
        <h1 class="main-heading">Welcome Back</h1>
        <p class="sub-heading">Welcome back to SGSD! Please enter your details below to login.</p>

        

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" class="form-group" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-group" placeholder="Enter your password" required>
            </div>
            <!-- Display error message if any -->
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>


            <div class="button-group">
                <button type="submit" name="login" class="login-btn">Login to Dashboard</button>
                <a href="../ForgotPassword" class="forgot-btn">Forgot Password?</a>
                <?php if ($signup_enabled) { ?>
                    <a href="../SignUp" class="signup-btn">Don't have an Account? Sign up</a>
                <?php } ?>
            </div>
        </form>
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
