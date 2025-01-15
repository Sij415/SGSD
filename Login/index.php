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

        <!-- Display error message if login fails -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Login to Dashboard</button>
                <a href="../ForgotPassword" class="btn btn-link">Forgot Password?</a>
            </div>
            <?php
    session_start();

    // Include the database connection file
    include_once '../dbconnect.php'; // Adjust the path as necessary
    $error = "";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            // Prepare and execute SQL query
            if (isset($conn)) { // Ensure $conn is defined
                $stmt = $conn->prepare("SELECT User_ID, Password_hash FROM users WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $hashed_password);
                    $stmt->fetch();

                    // Verify password
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['user_id'] = $id; // Store user ID in session
                        header("Location: ../dashboard.php"); // Redirect to dashboard
                        exit();
                    } else {
                        $error = "Invalid email or password. Please try again.";
                    }
                } else {
                    $error = "Invalid email or password. Please try again.";
                }

                $stmt->close();
            } else {
                $error = "Database connection not initialized. Please check your setup.";
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }
    ?>
        </form>
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
