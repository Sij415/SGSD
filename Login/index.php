<?php
// Include the database connection
include('dbconnect.php');
session_start();

$error = ''; // Initialize error variable

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Sanitize user input
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

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

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No user found with the provided email.";
        }
        $stmt->close();
    } else {
        $error = "Database error: Unable to prepare statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="button-group">
                <button type="submit" name="login" class="login-btn">Login to Dashboard</button>
                <a href="../ForgotPassword" class="forgot-btn">Forgot Password?</a>
                <a href="../signup.php" class="signup-btn">Don't have an Account? Sign up</a>
            </div>
        </form>
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
