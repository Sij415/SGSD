<?php
// Include the database connection
include('dbconnect.php');
session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare query to get user data based on email
    $sql = "SELECT * FROM Users WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password_hash'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['first_name'] = $user['First_Name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
    $stmt->close();
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $role = $_POST['role'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password before saving it
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into Users table
    $sql = "INSERT INTO Users (Role, First_Name, Last_Name, Email, Password_hash) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $role, $first_name, $last_name, $email, $password_hash);
    if ($stmt->execute()) {
        $success = "Account created successfully! Please log in.";
    } else {
        $error = "Error creating account.";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup Page</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    <div class="container mt-5">
        <!-- Error or Success messages -->
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>

        <!-- Tab navigation for Login and Signup -->
        <ul class="nav nav-tabs" id="login-signup-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="signup-tab" data-toggle="tab" href="#signup" role="tab">Signup</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="login-signup-tabs-content">
            <!-- Login Form -->
            <div class="tab-pane fade show active" id="login" role="tabpanel">
                <h3>Login to your account</h3>
                <form action="index.php" method="POST">
                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </div>
                    <div class="form-group">
                        <a href="../ForgotPassword" class="forgot-btn">Forgot Password?</a>
                    </div>
                    <div class="form-group">
                        <a href="#signup" class="btn btn-link" data-toggle="tab">Don't have an Account? Sign up</a>
                    </div>
                </form>
            </div>

            <!-- Signup Form -->
            <div class="tab-pane fade" id="signup" role="tabpanel">
                <h3>Create a new account</h3>
                <form action="index.php" method="POST">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="driver">Driver</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter your last name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="signup" class="btn btn-primary">Signup</button>
                    </div>
                    <div class="form-group">
                        <a href="#login" class="btn btn-link" data-toggle="tab">Already have an Account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer mt-5">
        © SGSD 2025
    </footer>
</body>
</html>
