
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon"  href="../logo.png">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<?php
include('../dbconnect.php');
// Fetch the status of Sign-Up, Admin Sign-Up, and Max Sign-Ups from the database
$sign_up_enabled_query = "SELECT Value FROM Settings WHERE Setting_Key = 'SignUpEnabled'";
$admin_signup_enabled_query = "SELECT Value FROM Settings WHERE Setting_Key = 'AdminSignUpEnabled'";
$max_signups_query = "SELECT Value FROM Settings WHERE Setting_Key = 'MaxSignUps'";

$sign_up_result = $conn->query($sign_up_enabled_query);
$admin_signup_result = $conn->query($admin_signup_enabled_query);
$max_signups_result = $conn->query($max_signups_query);

$sign_up_enabled = $sign_up_result->fetch_assoc()['Value'] ?? 0;
$admin_signup_enabled = $admin_signup_result->fetch_assoc()['Value'] ?? 0;
$max_signups = $max_signups_result->fetch_assoc()['Value'] ?? 0;



if ($max_signups <= 0 & 0 != $sign_up_enabled) {
    header("Location: ../"); // Adjust this URL to match your actual login page
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the input values from the form
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = strtolower(trim($_POST['email'])); // Convert email to lowercase
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Get the role from the form

    // Check if any field is empty
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'All fields are required',
                        text: 'Please make sure all fields are filled out.',
                        confirmButtonText: 'OK'
                    });
                });
              </script>";
        exit();
    }

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
        // Prevent sign-up if it's disabled
        if (!$sign_up_enabled) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Sign-Up Disabled',
                            text: 'Sign-up is currently disabled. Please contact support for assistance.',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '../login'; // Redirect to login
                            }
                        });
                    });
                  </script>";
            exit();
        }

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
            
            // Generate activation token and hash
            $activation_token = bin2hex(random_bytes(16));
            $activation_token_hash = hash("sha256", $activation_token);

            // Insert the new user into the Users table
            $sql = "INSERT INTO Users (First_Name, Last_Name, Email, Password_hash, Role, account_activation_hash) 
                    VALUES (?, ?, ?, ?, ?, ?)";
          
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password_hash, $role, $activation_token_hash);

            // Execute the SQL query
            if ($stmt->execute()) {
                // Reduce MaxSignUps by 1
                $update_signups_sql = "UPDATE Settings SET Value = Value - 1 WHERE Setting_Key = 'MaxSignUps'";
                $conn->query($update_signups_sql);

                // Check if MaxSignUps is now zero, and disable sign-up if it is
                $max_signups_sql = "SELECT Value FROM Settings WHERE Setting_Key = 'MaxSignUps'";
                $max_signups_result = $conn->query($max_signups_sql);
                $max_signups_value = $max_signups_result->fetch_assoc()['Value'] ?? 0;

                if ($max_signups_value == 0) {
                    // Disable sign-up if MaxSignUps reaches zero
                    $disable_signup_sql = "UPDATE Settings SET Value = 0 WHERE Setting_Key = 'SignUpEnabled'";
                    $conn->query($disable_signup_sql);
                }

                // Send the activation email
                $mail = require "../mailer.php";
                $mail->SMTPDebug = 0;
                $mail->setFrom("sangabrielsoftdrinksdelivery@gmail.com");
                $mail->addAddress($email);
                $mail->Subject = "Account Activation";
                $mail->Body = <<<END
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Email Template</title>
                </head>
                <body style="padding: 1em; font-family: Arial, sans-serif; background-color: #f2f4f0; max-height: 600px; display: flex; align-items: center; justify-content: center; margin: 0; height: auto; padding-top: 80px;">
                    <table style="width: 100%; background-color: #f2f4f0; border-spacing: 0; border-collapse: collapse;">
                        <tr>
                            <td style="display: flex; justify-content: center; align-items: center;">
                                <table style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 12px; padding: 32px; margin: 20px auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-spacing: 0; max-height: 600px; overflow: hidden;">
                                    <tr>
                                        <td style="text-align: center;">
                                            <h1 style="font-size: 40px; color: #82b370; font-weight: bold; margin-bottom: 10px;">🔓</h1>
                                            <h2 style="font-size: 24px; color: #545454; font-weight: 600; margin: 0;">You have created an account</h2>
                                            <p style="font-size: 16px; color: #7c8089; line-height: 1.5; margin: 20px 0;">
                                                If you wish to proceed, please click the button below to initiate the account activation.
                                            </p>
                                            <a href="http://10.147.20.116/SignUp/AccountActivation?token=$activation_token" style="display: inline-block; background-color: #82b370; color: #ffffff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px; font-weight: bold;">Activate Account</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center; padding-top: 40px;">
                                            <p style="font-size: 14px; color: #7c8089; margin: 0;">© SGSD 2025. All rights reserved.</p>
                                            <p style="font-size: 14px; margin: 5px 0;">
                                                <a href="#" style="color: #82b370; text-decoration: none;">Privacy Policy</a> | 
                                                <a href="#" style="color: #82b370; text-decoration: none;">Terms of Service</a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                END;
        
                try {
                    $mail->send();
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                    exit;
                }

                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Account Created',
                                text: 'Your account has been successfully created! Please check your email to activate your account.',
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
    <div class="form-field-signup">
        <div class="terms-container" style="display: flex; align-items: flex-start; margin: 10px 0;">
            <input class="tocpp" type="checkbox" id="terms" name="terms" required style="width: 22px; margin-right: 10px; position: relative; !important;">
            <label for="terms" class="terms-label m-0">I agree to the <a href="../terms-conditions.php">Terms & Conditions</a> and <a href="../privacy-policy.php">Privacy Policy</a></label>
        </div> </div>
    <div class="button-group">
        <input class="signup-cont-btn" type="submit" value="Signup">
    </div>
</form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const form = document.querySelector('form');

        form.addEventListener('submit', function(event) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password.length < 8) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 8 characters long.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }

            if (!/[A-Z]/.test(password)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one uppercase letter.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }

            if (!/[a-z]/.test(password)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one lowercase letter.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }

            if (!/[0-9]/.test(password)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one number.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }

            if (!/[\W_]/.test(password)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must contain at least one special character.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure the password and confirm password fields match.',
                    confirmButtonText: 'OK'
                });
                event.preventDefault();
                return;
            }
        });
    });
</script>
</body>
<footer class="footer">
    © SGSD 2025
</footer>
</html>