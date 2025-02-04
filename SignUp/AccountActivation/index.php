<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    <link rel="stylesheet" href="../../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="main-nav">
            <a href="../../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="login-container">
        <!-- Logo -->
        <!-- <div class="logo-container">
            <img src="assets/logo.svg" alt="SGSD Logo" class="logo">
        </div> -->

        <?php
        // Fetch token from URL
        $token = $_GET["token"] ?? null;

        if ($token) {
            $token_hash = hash("sha256", $token);

            // Database connection
            $mysqli = require "../../dbconnect.php";

            // Verify token in the database
            $sql = "SELECT * FROM Users WHERE account_activation_hash = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $token_hash);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user === null) {
                // Invalid or expired token
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Token',
                        text: 'The token you provided is either invalid or has expired.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                </script>";
                exit;
            } else {
                // Update user record to nullify the activation hash
                $sql = "UPDATE Users SET account_activation_hash = NULL WHERE id = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("s", $user["id"]);

                if ($stmt->execute()) {
                    // Success message
                    echo '<h1 class="main-heading">Your Account Has Been Successfully Activated</h1>
                          <p class="sub-heading">You can now log in and start using your account.</p>
                          <div class="button-group">
                              <a href="../../" class="request-btn">Login</a>
                          </div>';
                } else {
                    // Handle update error
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while activating your account. Please try again later.',
                            showConfirmButton: true
                        });
                    </script>";
                    exit;
                }
            }
        } else {
            // No token provided
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Token',
                    text: 'No activation token was provided.',
                    showConfirmButton: true
                });
            </script>";
            exit;
        }
        ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        © SGSD 2025
    </footer>
</body>
</html>
