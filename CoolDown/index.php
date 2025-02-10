<?php
session_start();
include('../dbconnect.php');
date_default_timezone_set("Asia/Manila");

$ip_address = $_SERVER['REMOTE_ADDR'];

// Check cooldown status
$sql = "SELECT Locked_Until FROM IP_Cooldown WHERE IP_Address = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();
$cooldown_message = "You are temporarily banned from accessing the site.";

// Check if the IP is in the cooldown table
if ($row = $result->fetch_assoc()) {
    $locked_until = new DateTime($row['Locked_Until']);
    $now = new DateTime();

    if ($locked_until > $now) {
        // If the cooldown is still active
        $remaining_time = $locked_until->getTimestamp() - $now->getTimestamp();
        $cooldown_message = "Your IP is on cooldown. You can try again in <span id='countdown'>$remaining_time</span> seconds.";
    } else {
        // If cooldown has expired, redirect to the login page
        header('Location: ../login.php');
        exit();
    }
} else {
    // If no matching row (i.e., IP is not in the cooldown table), redirect to the login page
    header('Location: ../');
    exit();
}

// Close connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cooldown</title>

    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
        <?php
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Cooldown Active',
                html: '$cooldown_message',
                showConfirmButton: true
            });
        </script>";
        ?>

        <!-- Display a custom message on the page -->
        <h1 class="main-heading">Access Restricted</h1>
        <p class="sub-heading"><?php echo $cooldown_message; ?></p>
        <div class="button-group">
            <a href="../" class="request-btn">Return to Home</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        © SGSD 2025
    </footer>

    <script>
        // Function to format the time into years, months, days, hours, minutes, seconds
        function formatTime(seconds) {
            let timeLeft = seconds;

            let years = Math.floor(timeLeft / (365 * 24 * 60 * 60));
            timeLeft %= (365 * 24 * 60 * 60);

            let months = Math.floor(timeLeft / (30 * 24 * 60 * 60));
            timeLeft %= (30 * 24 * 60 * 60);

            let days = Math.floor(timeLeft / (24 * 60 * 60));
            timeLeft %= (24 * 60 * 60);

            let hours = Math.floor(timeLeft / (60 * 60));
            timeLeft %= (60 * 60);

            let minutes = Math.floor(timeLeft / 60);
            timeLeft %= 60;

            let secondsLeft = timeLeft;

            let timeString = "";

            if (years > 0) timeString += `${years} year${years > 1 ? "s" : ""} `;
            if (months > 0) timeString += `${months} month${months > 1 ? "s" : ""} `;
            if (days > 0) timeString += `${days} day${days > 1 ? "s" : ""} `;
            if (hours > 0) timeString += `${hours} hour${hours > 1 ? "s" : ""} `;
            if (minutes > 0) timeString += `${minutes} minute${minutes > 1 ? "s" : ""} `;
            if (secondsLeft > 0) timeString += `${secondsLeft} second${secondsLeft > 1 ? "s" : ""}`;

            return timeString.trim();
        }

        let countdownElement = document.getElementById("countdown");
        if (countdownElement) {
            let timeLeft = parseInt(countdownElement.innerText);

            function updateCountdown() {
                if (timeLeft > 0) {
                    countdownElement.innerText = formatTime(timeLeft);
                    timeLeft--;
                    setTimeout(updateCountdown, 1000);
                } else {
                    location.reload();
                }
            }

            updateCountdown();
        }
    </script>
</body>
</html>
