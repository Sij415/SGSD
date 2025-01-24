<?php

include('../dbconnect.php');

// Fetch the status of Admin sign-up from the database
$admin_signup_enabled_query = "SELECT Value FROM Settings WHERE Setting_Key = 'AdminSignUpEnabled'";
$admin_signup_result = $conn->query($admin_signup_enabled_query);
$admin_signup_enabled = $admin_signup_result->fetch_assoc()['Value'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the input values from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = strtolower($_POST['email']); // Convert email to lowercase
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Get the role from the form

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure the password and confirm password fields match.',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        // Prevent admin sign-up if disabled
        if ($role === 'admin' && !$admin_signup_enabled) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Admin Sign-Up Disabled',
                        text: 'Admin sign-up is currently disabled. Please contact support for assistance.',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            exit();
        }

        // Check if the email already exists
        $check_email_sql = "SELECT Email FROM Users WHERE Email = ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email already exists
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Already Registered',
                        text: 'Please use a different email address to sign up.',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the Users table
            $sql = "INSERT INTO Users (First_Name, Last_Name, Email, Password_hash, Role) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $role);

            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Account Created',
                            text: 'Your account has been successfully created.',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '../';
                            }
                        });
                    });
                </script>";
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>










<body>
<header class="main-header">
    <nav class="main-nav">
        <a href="../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
    </nav>
</header>
<div class="login-container">
    <h1 class="main-heading">Create Account</h1>
    <hr>
    <form action="" method="POST" class="form-group-signup">
    <div class="form-group-signup-name">
        <div class="form-field-signup">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        <div class="form-field-signup">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
    </div>
    <div class="form-field-signup">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-field-signup">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div class="form-field-signup">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <div class="form-field-signup">
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="driver">Driver</option>
            <option value="staff">Staff</option>
            <!-- Render the Admin option only if admin sign-up is enabled -->
            <?php if ($admin_signup_enabled): ?>
                <option value="admin">Admin</option>
            <?php endif; ?>
        </select>
    </div>
    <div class="button-group">
        <input class="signup-cont-btn" type="submit" value="Signup">
    </div>
</form>


</div>
</body>
<footer class="footer">
    © SGSD 2025
</footer>
</html>