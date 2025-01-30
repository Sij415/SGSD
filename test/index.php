<?php
session_start();  // Start the session to access session variables

// Check if the form is submitted and update session variables accordingly
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['user_id'])) {
        $_SESSION['user_id'] = $_POST['user_id'];
    }
    if (!empty($_POST['email'])) {
        $_SESSION['email'] = $_POST['email'];
    }
    if (!empty($_POST['role'])) {
        $_SESSION['role'] = $_POST['role'];
    }
    if (!empty($_POST['first_name'])) {
        $_SESSION['first_name'] = $_POST['first_name'];
    }

    // Redirect to refresh session data (avoiding form resubmission issues)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set User Information</title>
</head>
<body>

    <h2>Set User Information</h2>

    <form method="POST" id="user_info_form">
        <input type="text" name="user_id" id="user_id_input" placeholder="Enter User ID">
        <input type="email" name="email" id="email_input" placeholder="Enter Email">
        <input type="text" name="role" id="role_input" placeholder="Enter Role">
        <input type="text" name="first_name" id="first_name_input" placeholder="Enter First Name">
        <button type="submit" id="submit_button">Set User Information</button>
    </form>

    <h3>Session Data:</h3>
    <p>User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></p>
    <p>Email: <?php echo $_SESSION['email'] ?? 'Not set'; ?></p>
    <p>Role: <?php echo $_SESSION['role'] ?? 'Not set'; ?></p>
    <p>First Name: <?php echo $_SESSION['first_name'] ?? 'Not set'; ?></p>

    <script>
        document.getElementById('submit_button').onclick = function(event) {
            event.preventDefault(); // Prevent default form submission
            let user_id = document.getElementById('user_id_input').value;
            let email = document.getElementById('email_input').value;
            let role = document.getElementById('role_input').value;
            let first_name = document.getElementById('first_name_input').value;

            // Check if at least one field is filled
            if (user_id || email || role || first_name) {
                let formData = new FormData();
                if (user_id) formData.append('user_id', user_id);
                if (email) formData.append('email', email);
                if (role) formData.append('role', role);
                if (first_name) formData.append('first_name', first_name);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload(); // Reload the page to update the session
                    }
                };
                xhr.send(formData);
            } else {
                alert('Please enter at least one field.');
            }
        };
    </script>

</body>
</html>
