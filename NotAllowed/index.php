<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="icon"  href="../logo.png">
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
        // // Show "Not Allowed" message
        // echo "<script>
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Access Denied',
        //         text: 'You do not have permission to access this page.',
        //         showConfirmButton: true
        //     });
        // </script>";
        // ?>

        <!-- Display a custom message on the page -->
        <h1 class="main-heading">Access Denied</h1>
        <p class="sub-heading">You do not have permission to access this page. Please contact the administrator if you believe this is an error.</p>
        <div class="button-group">
            <a href="../" class="request-btn">Return to Home</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        © SGSD 2025
    </footer>
</body>
</html>
