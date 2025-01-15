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
            <p>Click <a href="http://10.147.20.116/ForgotPassword/NewPassword?token=$token">here</a> 
            to reset your password.</p>
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
