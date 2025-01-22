<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Login Page</title>
=======
    <title>Forgot Password</title>
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<<<<<<< HEAD

=======
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
</head>

<header class="main-header">
    <nav class="main-nav">
        <a href="../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
    </nav>
</header>

<body>
<<<<<<< HEAD

    <div class="login-container">
        
=======
    <div class="login-container">
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
        <div class="logo-container">
            <img src="assets/logo.svg" alt="SGSD Logo" class="logo">
        </div>

        <h1 class="main-heading">Forgot your Password?</h1>
        <p class="sub-heading">Please enter your email address, and we'll send you a link to reset your password.</p>

<<<<<<< HEAD
        <div class="form-group">
            <label for="email">E-Mail</label>
            <input type="email" id="email" placeholder="Enter your email">
        </div>

        <div class="button-group">
        <a href="../NewPassword" class="request-btn">Request Reset Password</a>
    
            <button class="back-btn">Go Back</button>
        </div>
=======
        <!-- Form for requesting password reset -->
        <form action="sendpasswordreset.php" method="POST">
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" class="form-group" placeholder="Enter your email" required>
            </div>

            <div class="button-group">
                <button type="submit" class="request-btn">Request Reset Password</button>
                <a href="../" class="back-btn">Go Back</a>
            </div>
        </form>
>>>>>>> 51fda992c421cf24f7a2cdd7830c9f5f6e6a0250
    </div>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
