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
    <link rel="icon"  href="../../logo.png">
</head>

<body>
    <header class="main-header">
        <nav class="main-nav">
            <a href="../../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
        </nav>
    </header>

    <div class="login-container">
        <div class="logo-container">
            <div class="icon-container">
                <i class="fa-solid fa-key icon"></i>
            </div>
            
            <style>
                .icon-container {
                    text-align: center;
                    margin-bottom: 20px;
                }
                
                .icon {
                    font-size: 40px;
                    color: #6fa062;
                    background-color: #f8f9fa;
                    border-radius: 50%;
                    padding: 20px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    width: 90px;  /* Set a fixed width */
                    height: 90px; /* Set a fixed height */
                    display: inline-flex;
                    justify-content: center;
                    align-items: center;
                }
            </style>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const form = document.querySelector('form');

            if (form && passwordInput && confirmPasswordInput) {
                form.addEventListener('submit', function(event) {
                    const password = passwordInput.value;
                    const confirmPassword = confirmPasswordInput.value;

                    // Check password length
                    if (password.length < 8) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Weak Password',
                            text: 'Password must be at least 8 characters long.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }

                    // Check for uppercase letter
                    if (!/[A-Z]/.test(password)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Weak Password',
                            text: 'Password must contain at least one uppercase letter.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }

                    // Check for lowercase letter
                    if (!/[a-z]/.test(password)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Weak Password',
                            text: 'Password must contain at least one lowercase letter.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }

                    // Check for number
                    if (!/[0-9]/.test(password)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Weak Password',
                            text: 'Password must contain at least one number.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }

                    // Check for special character
                    if (!/[\W_]/.test(password)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Weak Password',
                            text: 'Password must contain at least one special character.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }

                    // Check if passwords match
                    if (password !== confirmPassword) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Passwords Do Not Match',
                            text: 'Please make sure the password and confirm password fields match.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success',
                                title: 'text-danger',
                                popup: 'swal2-popup'
                            },
                            buttonsStyling: false
                        });
                        event.preventDefault();
                        return;
                    }
                });
            } else {
                console.error('Form or input fields not found.');
            }
        });

        // Add style for SweetAlert popup border-radius
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .swal2-popup {
                    border-radius: 12px !important;
                }
            </style>
        `);
    </script>
</body>

<footer class="footer">
    © SGSD 2025
</footer>
</html>

<?php
include('../log_functions.php');
$token = $_GET["token"];
$token_hash = hash("sha256", $token);

$mysqli = require "../../dbconnect.php";

$sql = "SELECT * FROM Users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);

// if (!$stmt) {
//     die("<script>console.error('SQL Prepare Error: " . $mysqli->error . "');</script>");
// }

$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// // Debugging
// echo "<script>console.log('Token Hash: " . $token_hash . "');</script>";
// echo "<script>console.log('Query Result: " . json_encode($user) . "');</script>";

if (!$user || empty($user)) {
    die("<script>
        console.error('User not found or empty array.');
        Swal.fire({
            icon: 'error',
            title: 'Invalid Token',
            text: 'The provided reset token is invalid or has already been used.',
            showConfirmButton: true
            if (result.isConfirmed) {
                    window.location.href = '../../'; // Replace with your target page
                }
        });
    </script>");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("<script>
        console.error('Token expired.');
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
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $update_sql = "UPDATE Users SET Password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE reset_token_hash = ?";
    $update_stmt = $mysqli->prepare($update_sql);
    $update_stmt->bind_param("ss", $password_hash, $token_hash);

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