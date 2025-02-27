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

// Fetch IP cooldown entries
$query = "SELECT ID, Email, IP_Address, Attempts, Last_Attempt, Locked_Until FROM IP_Cooldown";
$result = $conn->query($query);

// Handle IP removal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_ip'])) {
    $ip_id = $_POST['ip_id'];

    // Delete entry from the database
    $deleteQuery = "DELETE FROM IP_Cooldown WHERE ID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $ip_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "IP removed successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error removing IP"]);
    }
    $stmt->close();
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
  <title>IP Cooldown</title>
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
                <a href="../Dashboard" class="sidebar-items-a">
                <i class="fa-solid fa-border-all"></i>
                <span>&nbsp;Dashboard</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageStocks">
                    <i class="fa-solid fa-box"></i>
                    <span>&nbsp;Manage Stocks</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageOrders">
                <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Orders</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageProducts">
                <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Product</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageCustomers">
                <i class="bi bi-people-fill" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Customer</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../AdminSettings">
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




    <div class="container mt-4">
        <h1><b>IP Cooldown</b></h1>
<h3 class="d-lg-none d-md-block">Click to edit Customer</h3>




        <!-- Search Box -->
        <div class="d-flex align-items-center justify-content-between mb-3">
    <!-- Search Input Group -->
    <div class="input-group">
        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="example-search-input">
        <button class="btn btn-outline-secondary" type="button" id="search">
            <i class="fa fa-search"></i>
        </button>
    </div>
    <div class="table-responsive  d-none d-md-block">
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Attempts</th>
                <th>Last Attempt</th>
                <th>Locked Until</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="ipTable">
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?php echo $row['ID']; ?>">
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['Email'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['IP_Address']; ?></td>
                    <td><?php echo $row['Attempts']; ?></td>
                    <td><?php echo $row['Last_Attempt']; ?></td>
                    <td><?php echo $row['Locked_Until'] ?? 'N/A'; ?></td>
                    <td>
                        <button class="remove-btn" data-id="<?php echo $row['ID']; ?>">Remove</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    <script>
        $(document).ready(function() {
            $(".remove-btn").click(function() {
                var ipId = $(this).data("id");
                var row = $("#row-" + ipId);

                $.post("", { remove_ip: true, ip_id: ipId }, function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        row.remove();
                    } else {
                        alert("Failed to remove IP.");
                    }
                });
            });
        });
    </script>
</body>
</html>