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
                <img src="../logo.png" alt="SGSD Logo" width="30" height="30" class="mr-1"> SGSD
                </a>
                <button type="button" class="btn ml-auto d-md-none d-lg-none rounded-circle mr-1 shadow" id="exitSidebar">
                    <i class="fas fa-times" style="font-size: 13.37px;"></i>
                </button>
            </div>
        </div>

        <hr class="line">

        <ul class="list-unstyled components p-0">
            <li class="active">
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
                <li>
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
                        <h1><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></h1>
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
    <!-- This is the basis for the finale dashboard. It is a work in progress and will be updated as we go along. It will be distributed to other pages after the backend integration is implemented in the Notification -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
        <div class="container-fluid">
            <!-- Left-aligned toggle sidebar button (unchanged) -->
            <button type="button" id="sidebarCollapse" class="btn btn-info ml-1" data-toggle="tooltip" data-placement="bottom" title="Toggle Sidebar">
                <i class="fas fa-align-left"></i>
            </button>
            
            <!-- Right-aligned group for notification and manual buttons -->
            <div class="ml-auto d-flex align-items-center">
                <!-- Notification system -->
                <div class="dropdown d-inline-block">
                    <button class="btn position-relative" type="button" id="notificationButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-toggle="tooltip" data-placement="bottom" title="Notifications" style="background: #6fa062; color: #fff; border: none; border-radius: 24px; width: 40px; height: 40px; transition: transform 0.3s;">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="font-size: 0.6rem; top: -5px; right: -5px; background-color: #dc3545;">
                            3
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="notificationButton" style="width: 300px; max-height: 350px; overflow-y: auto; border-radius: 12px; border: none; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                        <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0" style="font-weight: 600; letter-spacing: -0.045em;">Notifications</h6>
                            <a href="#" class="text-muted small" style="color: #6fa062 !important;">Mark all as read</a>
                        </div>
                        <div class="notification-item p-2 border-bottom">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <i class="fas fa-box p-2 rounded-circle" style="background-color: #e8ecef; color: #6fa062;"></i>
                                </div>
                                <div>
                                    <p class="mb-0 font-weight-bold" style="font-size: 0.9rem; letter-spacing: -0.045em;">New stock arrival</p>
                                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Coca Cola stock has been updated</p>
                                    <small class="text-muted" style="font-size: 0.75rem;">2 minutes ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item p-2 border-bottom">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <i class="fas fa-shopping-cart p-2 rounded-circle" style="background-color: #e8ecef; color: #6fa062;"></i>
                                </div>
                                <div>
                                    <p class="mb-0 font-weight-bold" style="font-size: 0.9rem; letter-spacing: -0.045em;">New order received</p>
                                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Order #12345 is waiting for confirmation</p>
                                    <small class="text-muted" style="font-size: 0.75rem;">30 minutes ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item p-2 border-bottom">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <i class="fas fa-exclamation-triangle p-2 rounded-circle" style="background-color: #e8ecef; color: #6fa062;"></i>
                                </div>
                                <div>
                                    <p class="mb-0 font-weight-bold" style="font-size: 0.9rem; letter-spacing: -0.045em;">Low stock alert</p>
                                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Sprite is running low on inventory</p>
                                    <small class="text-muted" style="font-size: 0.75rem;">1 hour ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 text-center border-top">
                            <a href="#" style="color: #6fa062; font-weight: 600; font-size: 0.85rem;">View all notifications</a>
                        </div>
                    </div>
                </div>
                <!-- Manual button (now next to notification) -->
                <button class="btn btn-dark ml-2" type="button" id="manualButton" data-toggle="tooltip" data-placement="bottom" title="View Manual">
                    <i class="fas fa-file-alt"></i>
                </button>
            </div>
        </div>
    </nav>

        <!-- Dashboard -->
        <div class="p-3" style="max-height: 80vh; overflow-y: auto;">
        <div class="dashboard">
            <div class="dashboard-summary">
            <div class="pb-4">
                <i class="fa-solid fa-border-all" style="font-size:56px;"></i>
            </div>
            <div class="dashboard-title">
                <h1 style="font-size: 40px; font-weight: 800;">Dashboard
                    <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="This is the main dashboard, providing a summary of key metrics such as revenue, orders, customers, and items sold.  Use the navigation sidebar to access other management features."></i>
                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();   
                        });
                    </script>
                </h1>
            </div>
            <h4 class="pb-3 pt-1" style="color: gray; font-size: 18px; font-weight: 300;">A summary of key metrics such as revenue, orders, customers, and items sold.</h4>
            <div class="date-filter-group">
                <div class="btn-group" role="group" aria-label="Data Range">
                    <input type="radio" class="date-filter-input" name="data_range" id="daily" autocomplete="off" value="daily">
                    <label class="date-filter-btn" for="daily">Daily</label>

                    <input type="radio" class="date-filter-input" name="data_range" id="weekly" autocomplete="off" value="weekly">
                    <label class="date-filter-btn" for="weekly">Weekly</label>

                    <input type="radio" class="date-filter-input" name="data_range" id="monthly" autocomplete="off" value="monthly" checked>
                    <label class="date-filter-btn" for="monthly">Monthly</label>

                    <input type="radio" class="date-filter-input" name="data_range" id="yearly" autocomplete="off" value="yearly">
                    <label class="date-filter-btn" for="yearly">Yearly</label>
                </div>
            </div>
            <div class="parent">
                <div class="div1">
                <div class=''>
                    <div class='card p-3 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class='mb-2'>Revenue</h5>
                            <span class='badge red'>-0.102%</span>
                        </div>
                    <div class='chart-container mt-3'>
                        <canvas id='revenueBarChart'></canvas>
                    </div>
                    </div>
                </div>
                </div>
                <div class="div2">
                <div class=''>
                    <div class=''>
                        <div class='card p-3 text-center'>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class='mb-2'>Orders</h5>
                                <span class='badge green'>+1.2%</span>
                            </div>
                            <div class='chart-container mt-3'>
                                <canvas id='ordersLineChart'></canvas>
                            </div>
                        </div>
                </div>
                </div>
                </div>
                <div class="div3">
                <div class=''>
                    <div class='card p-3 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class='mb-2'>Customers</h5>
                            <span class='badge green'>+0.96%</span>
                        </div>
                    <div class='chart-container mt-3'>
                        <canvas id='customersLineChart'></canvas>
                    </div>
                    </div>
                </div>
                </div>
                <div class="div4">
                <div class=''>
                    <div class='card p-3 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class='mb-2'>Items Sold</h5>
                            <span class='badge red'>-1.1%</span>
                        </div>
                    <div class='chart-container mt-3'>
                        <canvas id='itemsSoldBarChart'></canvas>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <div class="dashboard-top">
            <h1 style="font-size: 40px; font-weight: 800;">Placeholder for two static
                    <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Placeholder"></i>
                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();   
                        });
                    </script>
                </h1>            
            </div>
            <h4 class="pb-3 pt-1" style="color: gray; font-size: 18px; font-weight: 300;">A summary of key metrics such as revenue, orders, customers, and items sold.</h4>
            <div class="dashboard-summary">
            <div class="parent">
                <div class="div1">
                <div class=''>
                    <div class='card p-3 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class='mb-2'>Customers</h5>
                            <span class='badge red'>-0.102%</span>
                        </div>
                    <div class='chart-container mt-3'>
                        <canvas id='revenueBarChart'></canvas>
                    </div>
                    </div>
                </div>
                </div>
                <div class="div2">
                <div class=''>
                    <div class=''>
                        <div class='card p-3 text-center'>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class='mb-2'>Orders</h5>
                                <span class='badge green'>+1.2%</span>
                            </div>
                            <div class='chart-container mt-3'>
                                <canvas id='ordersLineChart'></canvas>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            </div>

            <div class="dashboard-top-2">
            <h1 style="font-size: 40px; font-weight: 800;">Placeholder for Pie Graph
                    <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Placeholder"></i>
                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();   
                        });
                    </script>
                </h1>                        
            </div>
            <h4 class="pb-3 pt-1" style="color: gray; font-size: 18px; font-weight: 300;">A summary of key metrics such as revenue, orders, customers, and items sold.</h4>
            <div class="date-filter-group-2">
                <div class="btn-group" role="group" aria-label="Pie Data Range">
                    <input type="radio" class="date-filter-input" name="pie_data_range" id="daily-pie" autocomplete="off" value="daily">
                    <label class="date-filter-btn" for="daily-pie">Daily</label>

                    <input type="radio" class="date-filter-input" name="pie_data_range" id="weekly-pie" autocomplete="off" value="weekly">
                    <label class="date-filter-btn" for="weekly-pie">Weekly</label>

                    <input type="radio" class="date-filter-input" name="pie_data_range" id="monthly-pie" autocomplete="off" value="monthly" checked>
                    <label class="date-filter-btn" for="monthly-pie">Monthly</label>

                    <input type="radio" class="date-filter-input" name="pie_data_range" id="yearly-pie" autocomplete="off" value="yearly">
                    <label class="date-filter-btn" for="yearly-pie">Yearly</label>
                </div>
            </div>
            <div class="dashboard-top-grid">
                <div class="card p-3 text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class='mb-2'>Top Selling Products</h5>
                        <span class='badge green'>+2.4%</span>
                    </div>
                    <div class='chart-container mt-3'>
                        <canvas id="topSellingChart"></canvas>
                    </div>
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
    DASHBOARD STYLE
----------------------------------------------------- */

        /* dashboard.css */

        /* Main Dashboard Container */
        .dashboard {
            background-color: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        /* Dashboard Title Section */
        .dashboard-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .dashboard-title h1 {
            font-size: 1.8rem;
            margin: 0;
            color: #343a40;
            letter-spacing: -0.045em;
        }

        .dashboard-title h1 b {
            color: #6fa062;
            font-weight: 700;
        }

        .dashboard-title .btn-group .btn {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.3s ease;
        }

        .dashboard-title .btn-outline-primary {
            color: #6fa062;
            border-color: #6fa062;
        }

        .dashboard-title .btn-outline-primary:hover,
        .dashboard-title .btn-check:checked + .btn-outline-primary {
            background-color: #6fa062;
            border-color: #6fa062;
            color: white;
        }

        /* Dashboard Summary Section */
        .dashboard-summary {
            margin-bottom: 30px;
        }

        .dashboard-summary .parent {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* Optimize for small screens (456px and below) */
        @media (max-width: 456px) {
            .dashboard-summary .parent {
            grid-template-columns: 1fr !important; /* Stack the cards */
            gap: 12px !important; /* Slightly reduce gap to save space */
            }
            
            .dashboard-summary .card {
            padding: 15px !important; /* More touch-friendly padding */
            margin-bottom: 10px !important; /* Add space between cards */
            width: 90% !important; /* Reduce card width */
            margin-left: auto !important;
            margin-right: auto !important;
            }
            
            .dashboard-summary h5 {
            font-size: 0.9rem !important; /* Smaller headings */
            }
            
            .badge {
            padding: 4px 8px !important; /* Smaller badges */
            font-size: 0.7rem !important;
            }

            .chart-container {
            height: 150px !important; /* Make charts smaller */
            }
        }

        .dashboard-summary .card {
            height: 100%;
            border-radius: 12px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-summary .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .dashboard-summary h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #495057;
        }

        .dashboard-summary h2 {
            font-size: 1.8rem;
            color: #212529;
        }

        /* Chart containers */
        .chart-container {
            height: 180px;
            position: relative;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.03em;
        }

        .badge.green {
            background-color: #e3f5e1;
            color: #2a9134;
        }

        .badge.red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        /* Top Selling Section */

        .dashboard-top h1 {
            font-size: 1.6rem;
            color: #343a40;
            letter-spacing: -0.045em;
        }

        .dashboard-top-2 h1 {
            font-size: 1.6rem;
            color: #343a40;
            letter-spacing: -0.045em;
            margin-top: 24px;
        }

        .dashboard-top h1 b {
            color: #6fa062;
            font-weight: 700;
        }

        .dashboard-top-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 15px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .dashboard-title {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-title .btn-group {
                width: 100%;
                margin-top: 10px;
            }
            
            .dashboard-summary .parent {
                grid-template-columns: 1fr;
            }

        }

        /* Card animations */
        .card {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        .date-filter-group {
                margin-bottom: 20px;
            }

            .date-filter-input {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }

            .date-filter-btn {
                display: inline-block;
                padding: 8px 16px;
                background-color: #f8f9fa;
                border: 1px solid #dae3d8;
                color: #666;
                cursor: pointer;
                font-size: 0.85rem;
                font-weight: 600;
                transition: all 0.2s ease;
                margin: 0;
            }

            .date-filter-btn:first-of-type {
                border-radius: 8px 0 0 8px;
            }

            .date-filter-btn:last-of-type {
                border-radius: 0 8px 8px 0;
            }

            .date-filter-input:checked + .date-filter-btn {
                background-color: #6fa062;
                color: white;
                border-color: #6fa062;
                box-shadow: 0 2px 5px rgba(111, 160, 98, 0.3);
                transform: translateY(-1px);
            }

            .date-filter-btn:hover {
                background-color: #eaefea;
                z-index: 1;
                transform: translateY(-1px);
            }

            .date-filter-input:focus + .date-filter-btn {
                outline: 2px solid rgba(111, 160, 98, 0.5);
                z-index: 2;
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