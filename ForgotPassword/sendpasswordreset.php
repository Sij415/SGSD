<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$email = trim($_POST["email"]); // Trim to avoid spaces

try {
    // Generate reset token and its hash
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 mins expiry

    // Include database connection
    $mysqli = require "../dbconnect.php";
    if (!$mysqli || $mysqli->connect_error) {
        throw new Exception("Failed to connect to the database: " . $mysqli->connect_error);
    }

    // Prepare and execute the update query
    $sql = "UPDATE Users
            SET reset_token_hash = ?, reset_token_expires_at = ?
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
        $mail = require "../mailer.php";
        

        // Configure email
        $mail->setFrom("noreply@example.com", "SGSD");
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $mail->isHTML(true);

        // Corrected Reset Password Button
        $resetLink = "http://10.147.20.116/ForgotPassword/NewPassword?token=" . urlencode($token);

        $mail->Body = <<<END
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Email Template</title>
        </head>
        <body style="padding: 1em; font-family: Arial, sans-serif; background-color: #f2f4f0;">
            <table style="width: 100%; background-color: #f2f4f0; border-spacing: 0; border-collapse: collapse;">
                <tr>
                    <td style="display: flex; justify-content: center; align-items: center;">
                        <table style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 12px; padding: 32px; margin: 20px auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <tr>
                                <td style="text-align: center;">
                                    <h1 style="font-size: 40px; color: #82b370; font-weight: bold;">🔑</h1>
                                    <h2 style="font-size: 24px; color: #545454;">You have requested to change your password.</h2>
                                    <p style="font-size: 16px; color: #7c8089; line-height: 1.5;">
                                        If you wish to proceed, please click the button below to initiate the password reset process.
                                    </p>
                                    <a href="$resetLink" style="display: inline-block; background-color: #82b370; color: #ffffff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px; font-weight: bold;">Reset Password</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center; padding-top: 40px;">
                                    <p style="font-size: 14px; color: #7c8089;">© SGSD 2025. All rights reserved.</p>
                                    <p style="font-size: 14px;">
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

        // Send email
        try {
            $mail->send();
            header("Location: ./EmailSent");
            exit;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer error: " . $mail->ErrorInfo;
        }
    } else {
        header("Location: ./NoUserFound");
    }

    // Close resources
    $stmt->close();
    $mysqli->close();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
