<?php
// Include database connection

$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);



// Fetch user details from session
$user_email = $_SESSION['email'];
// Get the user's first name and email from the database
$query = "SELECT First_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name);
$stmt->fetch();
$stmt->close();




echo $user_email;




// Fetch settings data from the database
$query = "SELECT * FROM Settings WHERE Setting_Key = 'AdminSignUpEnabled' OR Setting_Key = 'AdminSignUpEnabled'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['Setting_Key']] = $row['Value'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Retrieve form values
    $sign_up_enabled = isset($_POST['AdminSignUpEnabled']) ? 1 : 0;
    $admin_signup_enabled = isset($_POST['AdminSignUpEnabled']) ? 1 : 0;
    $sign_up_amount = isset($_POST['signup_amount']) ? $_POST['signup_amount'] : 0;

    // Update the settings in the database
    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'AdminSignUpEnabled'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sign_up_enabled);
    $stmt->execute();

    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'AdminSignUpEnabled'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_signup_enabled);
    $stmt->execute();

    // Optionally, update the sign up amount
    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'MaxSignUps'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sign_up_amount);
    $stmt->execute();

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Reload page to reflect changes
    exit();
}














?>







<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <title>Responsive Sidebar</title>
  <style>
    .table-striped>tbody>tr:nth-child(odd)>td, 
.table-striped>tbody>tr:nth-child(odd)>th {
   background-color: #f4f9f8;
 }
    /* Base styles */
    body {
      margin: 0;
      
      display: flex;
    }

    .sidebar {
  display: flex;
  width: 250px;
  height: 100vh; 
  position: fixed;
  top: 0;
  left: -250px; /* Hidden by default */
  transition: left 0.3s ease;
  z-index: 1000;

 

  overflow-x: hidden;

}


    .sidebar.active {
      left: 0;
    }

    .sidebar .close-btn {
      display: none;
    }

    .content {
      flex-grow: 1;
      margin-left: 0;
      padding-top: 2em;
      padding: 3.5em 1em;
      transition: margin-left 0.3s ease;
    }

    .toggle-btn {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 0.5em 1em;
      cursor: pointer;
    }
    /* Responsive styles */
    @media (min-width: 768px) {
      .sidebar {
        position: relative;
        left: 0;
      }

      .content {
        margin-left: 1rem;/* Push content for medium screens and above */
      }

      .toggle-btn {
        display: none;
      }

      .sidebar .close-btn {
        display: none; /* Hide close button on larger screens */
      }
    }

    @media (max-width: 767.98px) {
      .sidebar .close-btn {
        display: block; /* Show close button only on smaller screens */
        color: #fff;
        background: none;
        border: none;
        font-size: 1.5rem;
        position: absolute;
        top: 10px;
        right: 15px;
      }
    }
  </style>
</head>
<body class="p-0">
<header class="app-header">
  <nav class="app-nav d-flex justify-content-between">
    <!-- Sidebar button visible only on smaller screens -->
    <a class="sidebar-btn d-md-none" id="toggleBtn">☰</a>
    
    <!-- "X" button aligned to the right on larger screens -->
    <a href="#" class="d-md-flex d-md-none ms-auto tooltip-btn"><i class="bi bi-file-earmark-break-fill"></i></a>
    
    <!-- "X" button visible on smaller screens, aligned left -->
  </nav>
</header>

<div id="sidebar" class="sidebar d-flex flex-column">
        <a  class="closebtn d-md-none" onclick="closeNav()">&times;</a>
        <a href="#" class="sangabrielsoftdrinksdeliverytitledonotchangethisclassnamelol"><b>SGSD</b></a>
 
        <div class="sidebar-items">
            <hr style="width: 75%; margin: 0 auto; padding: 12px;">
            <div class="sidebar-item">
                <a href="#" class="sidebar-items-a">
                <i class="fa-solid fa-border-all"></i>
                <span>&nbsp;Dashboard</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./">
                    <i class="fa-solid fa-box"></i>
                    <span>&nbsp;Manage Stocks</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#">
                <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Orders</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#">
                <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Product</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#">
                <i class="bi bi-people-fill" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Customer</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#">
                <i class="bi bi-gear" style="font-size:13.28px;"></i>
                <span>&nbsp;Admin Settings</span>
                </a>
            </div>
        </div>
        
        <hr style="width: 75%; margin: 0 auto; padding: 12px ;">
        <div class="mt-auto p-2">
        <div class="sidebar-usr">
            <div class="sidebar-pfp">
                <img src="https://upload.wikimedia.org/wikipedia/en/b/b1/Portrait_placeholder.png" alt="Sample Profile Picture">
            </div>
            <div class="sidebar-usrname">
                <h1><?php
                echo htmlspecialchars($user_first_name);
                
                
                
                ?></h1>
                <h2><?php
                echo  htmlspecialchars($user_email)
                
                
                ?></h2>
            </div>
        </div>
        <div class="sidebar-options ">
            <div class="sidebar-item">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </div>
            <div class="sidebar-item d-none d-sm-block">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-file-alt"></i>
                    <span>Manual</span>
                </a>
            </div>
        </div></div>
 
        </div>
  <div class="content">
  <section class="admin">

<div class="admin-main">
    <div class="admin-title">
    <h1><b>Admin</b> Settings</h1>
    <h3>Customize and control system preferences</h3>
    <p>Access tools to manage users, configure system settings, and oversee overall platform functionality.</p>
    </div>


<div class="admin-ip">
    <div class="p-3">
        <div class="admin-ip-title d-flex flex-column mb-3">
            <h3 style="letter-spacing: -0.045em;">
                <b>Current</b> IP address:
                <small class="text-muted">PLACEHOLDER</small>
            </h3>
            <h5 class="text-muted mb-3" style="width: 90%;">
                Your IP address uniquely identifies your device on the internet and is essential for communication with other devices and accessing online services.
            </h5>
        </div>

    </div>
</div>
<div style="padding: 16px;"><hr></div>
<div class="admin-restrictions">
    <h2 class="p-3" style="letter-spacing: -0.050em;"><b>This is Title</b></h2>
    <form action="" method="POST">
        <div class="d-flex justify-content-between align-items-start p-3">
            <div class="admin-restrictions-title d-flex flex-column mb-3">
                    <h3 style="letter-spacing: -0.045em;">
                        <b>Sign Up</b> Restrictions
                    </h3>
                    <h5 class="text-muted mb-3" style="width: 90%;">
                        Toggle the restrictions to allow User account creation.
                    </h5>
                </div>
                <div class="d-flex align-items-center">
        
        <input type="number" class="form-control mx-5" id="signup-amount" name="signup_amount" placeholder="Set" value="<?php echo htmlspecialchars($settings['MaxSignUps'] ?? ''); ?>" style="width: 150px;">
        <div class="form-check form-switch me-3">
        <input class="form-check-input" type="checkbox" id="account-restrictions" name="sign_up_enabled" <?php echo ($settings['AdminSignUpEnabled'] == 1) ? 'checked' : ''; ?>>
        </div>
    </div>
        </div>
        <div class="d-flex justify-content-between align-items-start p-3">
            <div class="admin-restrictions-title d-flex flex-column mb-3">
                    <h3 style="letter-spacing: -0.045em;">
                        <b>Admin</b> Sign Up
                    </h3>
                    <h5 class="text-muted mb-3" style="width: 90%;">
                        Toggle the restrictions to allow admin sign up.
                    </h5>
                </div>
                <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="admin_signup_enabled" <?php echo ($settings['AdminSignUpEnabled'] == 1) ? 'checked' : ''; ?>>
                </div>
            </div>
            <div class="d-flex justify-content-end p-3">
                <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
           
            </div>
            </form>
        </div>
<div style="padding: 16px;"><hr></div>
</section>
  </div>


  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    function closeNav() {
      sidebar.classList.remove('active');
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
