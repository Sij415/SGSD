<?php
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
                <a href="./Dashboard" class="sidebar-items-a">
                <i class="fa-solid fa-border-all"></i>
                <span>&nbsp;Dashboard</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./ManageStocks">
                    <i class="fa-solid fa-box"></i>
                    <span>&nbsp;Manage Stocks</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./ManageOrders">
                <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Orders</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./ManageProducts">
                <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Product</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./ManageCustomers">
                <i class="bi bi-people-fill" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Customer</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="./AdminSettings">
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



  <div class="dashboard">
    <!-- Dashboard-title -->
    <div class="dashboard-title">
        <h1><b>ANALYTICS</b> DASHBOARD</h1>
            <div class="btn-group" style="z-index: 999;" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio1">DAILY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="btnradio2">WEEKLY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio3">MONTHLY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio4" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio4">YEARLY</label>
            </div>
    </div>
    <div class="dashboard-summary">
    <div class="parent">
        <div class="div1">
            <div class='col-md-3'>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Revenue</h5>
                    <h2 class='fw-bold mb-2'>₱ 2,343</h2>
                    <span class='badge red'>-0.102%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='revenueBarChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div2">
            <div class='col-md-3'>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Orders</h5>
                    <h2 class='fw-bold mb-2'>45</h2>
                    <span class='badge green'>+1.2%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='ordersLineChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div3">
            <div class='col-md-3'>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Customers</h5>
                    <h2 class='fw-bold mb-2'>12</h2>
                    <span class='badge green'>+0.96%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='customersLineChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div4">
            <div class='col-md-3'>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Items Sold</h5>
                    <h2 class='fw-bold mb-2'>34</h2>
                    <span class='badge red'>-1.1%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='itemsSoldBarChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="dashboard-top">
        <h1><b>TOP</b> SELLING</h1>
    </div>
    <div class="dashboard-top-grid">
                <div class="div1">1</div>
                <div class="div2">2</div>
            </div>
    <hr>
</div> 
  
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






// Revenue Bar Chart
new Chart(document.getElementById('revenueBarChart'), {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    data: [12, 19, 3, 5, 2],
                    backgroundColor: ['#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8']
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });










  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
