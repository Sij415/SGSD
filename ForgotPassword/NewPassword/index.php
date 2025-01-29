<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>



<header class="main-header">
    <nav class="main-nav">
        <a href="../../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
    </nav>
</header>

<body>

    <div class="login-container">
        
        <div class="logo-container">
            <img src="assets/logo.svg" alt="SGSD Logo" class="logo">
        </div>

        <h1 class="main-heading">Enter your new password</h1>
        <p class="sub-heading">Your new password must be different from your previous password.</p>

        <form action="" method="POST">
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password" required>
            </div>

            <div class="button-group">
                <button type="submit" class="request-btn">Reset Password</button>
            </div>
        </form>
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>

<?php

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

$mysqli = require "../../dbconnect.php";

$sql = "SELECT * FROM Users WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();


if ($user === null) {
    die("<script>
        Swal.fire({
            icon: 'error',
            title: 'Invalid Token',
            showConfirmButton: false
       
            
        });
    </script>");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("<script>
        Swal.fire({
            icon: 'error',
            title: 'Expired Token',
            showConfirmButton: false
       
            
        });
    </script>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle password reset
    $password = $_POST["password"];
    $password_confirmation = $_POST["password_confirmation"];

    // Check if both passwords match
    if ($password !== $password_confirmation) {
        die("Passwords do not match.");
    }

    // Hash the new password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Update the user's password in the database
    $update_sql = "UPDATE Users SET Password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE User_ID = ?";
    $update_stmt = $mysqli->prepare($update_sql);
    $update_stmt->bind_param("si", $password_hash, $user["User_ID"]);

    if ($update_stmt->execute()) {
        echo("<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Password has been changed successfully'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../'; // Replace with your target page
        }
    });
</script>");

    } else {
        die("Failed to reset password.");
    }
}
?>


