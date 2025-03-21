<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <link rel="icon"  href="../logo.png">
</head>

<header class="main-header">
    <nav class="main-nav">
        <a href="../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
    </nav>
</header>

<body>

    <div class="login-container">
        
        <div class="icon-container">
             <i class="fas fa-lock icon"></i>
         </div>
         
         <style>            
             .icon-container {
             text-align: left;
             margin-bottom: 20px;
             }
             
             .icon {
             font-size: 50px;
             color: #6fa062;
             background-color: #f8f9fa;
             border-radius: 50%;
             padding: 20px;
             box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
             }
         </style>

        <h1 class="main-heading">Forgot your Password?</h1>
        <p class="sub-heading">Please enter your email address, and we'll send you a link to reset your password.</p>


         <!-- Form for requesting password reset -->
         <form action="sendpasswordreset.php" method="POST">
             <div class="form-group">
                 <label for="email">E-Mail</label>
                 <input type="email" id="email" name="email" class="form-group" placeholder="Enter your email" required>
             <div class="button-group">
                 <button type="submit" class="request-btn">Request Reset Password</button>
                 <a href="../" class="back-btn">Go Back</a>
             </div>
         </form>
</body>

<footer class="footer">
    © SGSD 2025
</footer>

</html>
