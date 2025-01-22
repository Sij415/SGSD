<?php


// Include the database connection
include('../dbconnect.php');
session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare query to get user data based on email
    $sql = "SELECT * FROM Users WHERE Email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password_hash'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['User_ID'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['first_name'] = $user['First_Name'];

                // Redirect to dashboard based on the role
                header("Location: ../Dashboard");
                exit();
            } else {
                $error = "Invalid password or email.";
            }
        } else {
            $error = "Invalid password or email.";
        }
        $stmt->close();
    } else {
        $error = "An error occurred. Please try again later.";
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

        <!-- Display error message if any -->
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-Mail</label>
<<<<<<< HEAD
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
=======
                <input type="email" id="email" name="email" class="form-group" placeholder="Enter your email" required>
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
            </div>

            <div class="form-group">
                <label for="password">Password</label>
<<<<<<< HEAD
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
=======
                <input type="password" id="password" name="password" class="form-group" placeholder="Enter your password" required>
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
            </div>

            <div class="button-group">
                <button type="submit" name="login" class="login-btn">Login to Dashboard</button>
                <a href="../ForgotPassword" class="forgot-btn">Forgot Password?</a>
<<<<<<< HEAD
                <a href="../asdasdas" class="signup-btn">Don't have an Account? Sign up</a>
=======
                <a href="../SignUp" class="signup-btn">Don't have an Account? Sign up</a>
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
            </div>
        </form>
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
