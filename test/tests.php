<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f4f0;
            margin: 0;
            padding: 0;
        }
        .container-mail {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn {
            background-color: #82b370;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-mail">
        <h1>Password Reset Request</h1>
        <p>You have requested to reset your password. If this was you, click the button below:</p>
        <a href="$resetLink" class="btn">Reset Password</a>
        <p>If you did not request this, you can safely ignore this email.</p>
    </div>
    <footer>
        <p>© SGSD 2025. All rights reserved.</p>
    </footer>
</body>
</html>