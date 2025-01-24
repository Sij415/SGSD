<?php
// Enable error reporting for debugging
 error_reporting(E_ALL);
 ini_set('display_errors', 1);

$email = $_POST["email"];

try {
    // Generate reset token and its hash
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    // Include database connection
    $mysqli = require "../dbconnect.php";
    if (!$mysqli) {
        throw new Exception("Failed to connect to the database.");
    }

    // Update the user's reset token and expiry time
    $sql = "UPDATE Users
            SET reset_token_hash = ?,
                reset_token_expires_at = ?
            WHERE Email = ?";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: " . $mysqli->error);
    }

    $stmt->bind_param("sss", $token_hash, $expiry, $email);

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute SQL statement: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        // Include mailer configuration
        $mail = require "./mailer.php";
        if (!$mail) {
            throw new Exception("Failed to load mailer configuration.");
        }

        $mail->setFrom("noreply@example.com", "SGSD");
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $mail->isHTML(true);
        $mail->Body = <<<END

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashbard</title>
            <!-- <link rel="stylesheet" href="../style/style.css"> -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        </head>
        <style>
        body {
            padding: 1em;
            font-family: 'Inter', sans-serif !important;
            background-color: #f2f4f0;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding-top: 80px;
        }
        .sgsd-mail-success {
            text-align: left;
            background: #fff;
            font-family: 'Inter', sans-serif !important;
            letter-spacing: -0.050em;
        }
        .sgsd-mail-success .sgsd-success-inner {
            display: inline-block;
            padding: 32px;
        }
        .sgsd-mail-success .sgsd-success-inner h1 {
            font-size: 100px;
            text-shadow: 3px 5px 2px #3333;
            color: #82b370;
            font-weight: 700;
        }
        .sgsd-mail-success .sgsd-success-inner h1 span {
            display: block;
            font-size: 0.35em;
            color: #545454;
            font-weight: 600;
            text-shadow: none;
            margin-top: 20px;
            font-weight: 800;
        }
        .sgsd-success-inner i {
            font-size: 0.75em;
        }
        .sgsd-mail-success .sgsd-success-inner p {
            color: #7c8089;
        }
        .sgsd-mail-success .sgsd-success-inner .btn{
            color:#fff;
        }
        
        .container-mail {
            background-color: #f2f4f0; /* Light gray background */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            border-radius: 8px; /* Rounded corners */
            padding: 32px; /* Padding for inner content */
            margin: 20px auto; /* Center the container */
            max-width: 600px; /* Maximum width for the container */
        }
        
        /* Media query for medium devices */
        @media (max-width: 480px) {
            .sgsd-mail-success .sgsd-success-inner {
                padding: 16px; /* Adjust padding for smaller screens */
            }
            .sgsd-mail-success .sgsd-success-inner h1 {
                font-size: 60px; /* Reduce font size for smaller screens */
            }
            .sgsd-mail-success .sgsd-success-inner h1 span {
                font-size: 0.5em; /* Adjust span font size */
            }
        }
        </style>
        
        <body>
        <section class="sgsd-mail-success section" style="background-color: #f2f4f0;">
            <div class="container" style="background-color: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <div class="row">
                    <div class="col-lg-0 offset-lg-0 col-12">
                        <!-- Success Inner -->
                        <div class="sgsd-success-inner mb-4">
                            <h1><i class="fa fa-key"></i><span>You have requested to change your password.</span></h1>
                            <p>If you wish to proceed, please click the button below to initiate the password reset process.</p>
                            <a href="#" class="btn" style="background-color: #82b370; color: #fff;" class="btn btn-lg">Reset Password</a>
                        </div>
                    </div>
                </div>
                <footer class="footer text-center  mt-5" style="display: flex; justify-content: center; align-items: center; flex-direction: column;"></footer>
                <div class="container text-center">
                    <p>© SGSD 2025. All rights reserved.</p>
                    <a href="../privacy-policy" class="footer-link">Privacy Policy</a> | 
                    <a href="../terms-of-service" class="footer-link">Terms of Service</a>
                </div>
            </footer>
            </div>
        
        </section>
        
        </body>
        </html>
        END;

        try {
            $mail->send();
            header ("Location: ./EmailSent"); 
            exit;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
        }
    } else {
        header ("Location: ./NoUserFound"); 
    }

    $stmt->close();
    $mysqli->close();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>