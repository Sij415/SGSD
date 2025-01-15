<?php
// Include the database connection
include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the input values from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the Users table
    $sql = "INSERT INTO Users (First_Name, Last_Name, Email, Password_hash) 
            VALUES ('$first_name', '$last_name', '$email', '$password_hash')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
</head>
<body>
    <h2>Create Account</h2>
    <form action="signup.php" method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required><br><br>
        
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required><br><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="Signup">
    </form>
</body>
</html>
