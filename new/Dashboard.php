<?php
$required_role = 'admin,staff,driver';
include('../check_session.php');
include('../log_functions.php');
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];
// Get the user's first name and email from the database
$query = "SELECT First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name, $user_last_name);
$stmt->fetch();
$stmt->close();


// Handle logout when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../Login"); // Redirect to login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD | Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="icon"  href="../logo.png">
    <link rel="stylesheet" href="../style/styles.css">

</head>
<body>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR SIDEBAR JS
------------------------------------------------------>

<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });

        $('#exitSidebar').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
        });
</script>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR DASHBOARD JS
------------------------------------------------------>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    function closeNav() {
      sidebar.classList.remove('active');
    }


        // Doughnut Chart for Top Selling
        new Chart(document.getElementById('topSellingChart'), {
            type: 'doughnut',
            data: {
                labels: ['Coca Cola', 'Royal', 'Sprite', 'Pepsi', 'Mountain Dew'],
                datasets: [{
                    data: [43, 21, 13, 12, 8],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d']
                }]
            },
            options: { responsive: true }
        });
        </script>

<script>
$(document).ready(function () {
    $.ajax({
        url: 'fetch_data.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            // Revenue Chart (Bar)
            new Chart(document.getElementById('revenueBarChart'), {
                type: 'bar',
                data: {
                    labels: data.revenue_data.map(item => item.Date),
                    datasets: [{
                        data: data.revenue_data.map(item => item.revenue),
                        backgroundColor: ['#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8']
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            // Orders Chart (Line)
            new Chart(document.getElementById('ordersLineChart'), {
                type: 'line',
                data: {
                    labels: data.orders_data.map(item => item.Date),
                    datasets: [{
                        data: data.orders_data.map(item => item.order_count),
                        borderColor: '#9fb0a1',
                        tension: 0.4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            // Customers Chart (Line)
            new Chart(document.getElementById('customersLineChart'), {
                type: 'line',
                data: {
                    labels: data.customers_data.map(item => item.Date),
                    datasets: [{
                        data: data.customers_data.map(item => item.customer_count),
                        borderColor: '#9fb0a1',
                        tension: 0.4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            // Items Sold Chart (Bar)
            new Chart(document.getElementById('itemsSoldBarChart'), {
                type: 'bar',
                data: {
                    labels: data.items_sold_data.map(item => item.Date),
                    datasets: [{
                        data: data.items_sold_data.map(item => item.items_sold),
                        backgroundColor: ['#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9']
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        },
        error: function (xhr, status, error) {
            console.error("Error fetching data:", error);
        }
    });
});
</script>


  </script>

<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header mt-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand m-0 p-1" href="#">
                    <i class="fas fa-store mr-1"></i> SGSD
                </a>
                <button type="button" class="btn ml-auto d-md-none d-lg-none rounded-circle mr-1 shadow" id="exitSidebar">
                    <i class="fas fa-times" style="font-size: 13.37px;"></i>
                </button>
            </div>
        </div>

        <hr class="line">

        <ul class="list-unstyled components p-0">
            <li>
                <a href="../Dashboard" class="sidebar-items-a">
                    <i class="fa-solid fa-border-all" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Dashboard</span>
                </a>
            </li>

            <?php if ($user_role !== 'driver') : // Exclude for drivers 
            ?>
                <li>
                    <a href="../ManageOrders">
                        <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Orders</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($user_role === 'admin' || $user_role === 'staff') : // Admin and staff 
            ?>
                <li>
                    <a href="../ManageStocks">
                        <i class="fa-solid fa-box" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Stocks</span>
                    </a>
                </li>
                <li>
                    <a href="../ManageProducts">
                        <i class="fa-solid fa-list" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Product</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($user_role === 'admin') : // Only Admins 
            ?>
                <li>
                    <a href="../ManageCustomers">
                        <i class="bi bi-people-fill" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Customer</span>
                    </a>
                </li>
                <li class="active">
                    <a href="../AdminSettings">
                        <i class="bi bi-gear" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Admin Settings</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-spacer"></div>
        <hr class="line">
        <ul class="list-unstyled CTAs pt-0 mb-0 sidebar-bottom">
            <li class="sidebar-username pb-2">
                <div class="align-items-center">
                    <div class="profile-initials rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; border: 1px solid #ccc; background-color: #eee; font-size: 20px;">
                        <?php 
                            echo strtoupper(substr($user_first_name, 0, 1) . substr($user_last_name, 0, 1));
                        ?>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($user_first_name); ?></h1>
                        <h2><?php echo htmlspecialchars($user_email); ?></h2>
                        <h5 style="font-size: 1em; background-color: #6fa062; color: #F2f2f2; font-weight: 700; padding: 8px; border-radius: 8px; width: fit-content;"><?php echo htmlspecialchars($user_role); ?></h5>
                    </div>
                </div>
            </li>
            <li>
                <a href="#" class="logout">
                <i class="fa-solid fa-sign-out-alt"></i>
                <span>Log out</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content">
    <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info ml-1" data-toggle="tooltip" data-placement="bottom" title="Toggle Sidebar">
                    <i class="fas fa-align-left"></i>
                </button>
                <button class="btn btn-dark d-inline-block ml-auto" type="button" id="manualButton" data-toggle="tooltip" data-placement="bottom" title="View Manual">
                    <i class="fas fa-file-alt"></i>
                </button>
            </div>
        </nav>

        <!-- Dashboard -->
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
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Revenue</h5>
                    <!-- <h2 class='fw-bold mb-2'>₱ 2,343</h2> -->
                    <span class='badge red'>-0.102%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='revenueBarChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div2">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Orders</h5>
                    <!-- <h2 class='fw-bold mb-2'>45</h2> -->
                    <span class='badge green'>+1.2%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='ordersLineChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div3">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Customers</h5>
                    <!-- <h2 class='fw-bold mb-2'>12</h2> -->
                    <span class='badge green'>+0.96%</span>
                    <div class='chart-container mt-3'>
                        <canvas id='customersLineChart'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="div4">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2'>Items Sold</h5>
                    <!-- <h2 class='fw-bold mb-2'>34</h2> -->
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
                <div class="div1"><div class="doughnut-container">
                <canvas id="topSellingChart"></canvas></div>

            </div>
    <hr>
    </div> 


</div>

  </div>


    </div>
    </div>

<style>

/* ---------------------------------------------------
    NEW BODY STYLE
----------------------------------------------------- */

body {
    font-family: 'Inter', sans-serif !important;
    background: #fafafa;
    letter-spacing: -0.045em;
}

p {
    font-family: 'Inter', sans-serif !important;
    font-size: 1.1em;
    font-weight: 300;
    line-height: 1.7em;
    color: #999;
    letter-spacing: -0.045em;
}

a,
a:hover,
a:focus {
    color: inherit;
    text-decoration: none;
    transition: all 0.3s;
}

.navbar {
    padding: 15px 10px;
    background: #fff;
    border: none;
    border-radius: 0;
    margin-bottom: 40px;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
}

.navbar-btn {
    box-shadow: none;
    outline: none !important;
    border: none;
}

/* ---------------------------------------------------
    SIDEBAR STYLE
----------------------------------------------------- */

.wrapper {
    display: flex;
    width: 100%;
    align-items: stretch;
}

#sidebar {
    min-width: 250px;
    max-width: 250px;
    background: #f2f4f0;
    color: #252525;
    transition: all 0.3s;
    box-shadow: 2px 0 6px rgba(0, 0, 0, 0.25);
}

#sidebar.active {
    margin-left: -250px;
}

#sidebar .sidebar-header {
    padding: 0px 20px;
    background: #f2f4f0;
}

#sidebar ul.components {
    padding: 20px 0;
}

#sidebar ul p {
    color: #252525;
    padding: 10px;
}

#sidebar ul li a {
    padding: 15px 30px;
    font-size: 1.1em;
    display: block;
    transition: transform 0.3s;
}

#sidebar ul li a:hover {
    transform: scale(1.1);
}

#sidebar ul li.active>a,
a[aria-expanded="true"] {
    background: #e8e8e8;
    border-radius: 12px;
}

a[data-toggle="collapse"] {
    position: relative;
}

.dropdown-toggle::after {
    display: block;
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
}

ul ul a {
    font-size: 0.9em !important;
    padding-left: 30px !important;
    background: #6fa062;
}

ul.CTAs {
    padding: 20px;
}

ul.CTAs a {
    text-align: center;
    font-size: 0.9em !important;
    display: block;
    border-radius: 5px;
    margin-bottom: 5px;
}

a.logout {
    border-radius: 12px !important;
    padding: 16px !important;
    background: #6fa062;
    color: #fff;
}

a.logout:hover {
    color: #fff !important;
    transition: background 0.3s, transform 0.3s !important;
    transform: scale(1.02) !important;
}

#manualButton,
#sidebarCollapse {
    background: #6fa062;
    border: none;
    cursor: pointer;
    border-radius: 24px;
    width: 40px;
    height: 40px;
    transition: transform 0.3s;
}

#manualButton:hover,
#sidebarCollapse:hover {
    transform: scale(1.1);
}

#sidebarNavbar,
#mainNavbar {
    border-radius: 36px;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
}

hr.line {
    border-top: 1px solid #8a8d8b;    
    width: 75%;
}

.sidebar-header {
    font-weight: 700;
    letter-spacing: -0.045em;
}

.sidebar-username {
    padding: 12px;
    letter-spacing: -0.045em !important;
    white-space: normal;
    word-wrap: break-word; /* Allow long text to wrap */
    overflow-wrap: break-word; /* Ensure compatibility */
}

.sidebar-username h1 {
    width: 75%;
    font-size: 1.2em !important;
    font-weight: 700;
}

.sidebar-username h2 {
    font-size: 0.8em; 
    color: #797979;
}

/* ---------------------------------------------------
    CONTENT STYLE
----------------------------------------------------- */

#content {
    width: 100%;
    padding: 20px;
    min-height: 100vh;
    transition: all 0.3s;
}

/* .add-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: #ffffff !important;
    font-size: 12px;
    letter-spacing: -0.050em !important;
    border-radius: 10px;
    padding: 12px;
    border: none;
    white-space: nowrap;
    background-color: #6fa062;
    transition: transform 0.3s;
}

.add-btn:hover {
    transform: scale(1.05);
} */

.tooltip-inner {
    color: #000 !important;
    background-color: #ebecec !important;
}

/* Custom styles to match system colors */
.custom-btn {
    background-color: #6fa062 !important;
    color: #fff !important;
    border: none !important;
    transition: transform 0.3s !important;
}

.custom-btn:hover {
    background-color: #5e8a52 !important;
    color: #fff !important;
    transform: scale(1.05) !important;
}


/* ---------------------------------------------------
    MEDIAQUERIES
----------------------------------------------------- */

@media (max-width: 768px) {
    #sidebar {
        margin-left: -250px;
        position: fixed;
        z-index: 999;
        height: 100%;
    }
    #sidebar.active {
        margin-left: 0;
    }
    #sidebarCollapse span {
        display: none;
    }
}
</style>

