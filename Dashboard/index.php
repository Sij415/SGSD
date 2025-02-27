<?php
// $required_role = 'admin';
// include('../check_session.php');
session_start();
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);



// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's first name and role from the database
$query = "SELECT First_Name, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name, $user_role);
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
  <script>
        var app = angular.module('notificationApp', []);
app.controller('NotificationController', function ($scope, $http) {
    $scope.dropdownVisible = false;
    $scope.notifications = [];

    // Fetch notifications from the server
    $scope.getNotifications = function () {
        $http.get('get_notifications.php')
            .then(function (response) {
                console.log("Response Data:", response.data); // Debugging
                $scope.notifications = response.data;
            })
            .catch(function (error) {
                console.error("Error fetching notifications:", error);
            });
    };

    // Toggle dropdown
    $scope.toggleDropdown = function (event) {
        event.preventDefault();
        $scope.dropdownVisible = !$scope.dropdownVisible;
        if ($scope.dropdownVisible) {
            $scope.getNotifications();
        }
    };

    // Fetch notifications when the page loads
    $scope.getNotifications();
});

    </script>
  <title>Dashboard</title>





    <style
.table-striped > tbody > tr:nth-child(odd) > td, 
.table-striped > tbody > tr:nth-child(odd) > th {
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
    margin-left: 1rem; /* Push content for medium screens and above */
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
 /* Positioning container for notifications */


 .notification-icon {
    font-size: 24px;
    color: #333;
    cursor: pointer;
    position: relative;
}

.notification-icon:hover {
    color: #007bff;
}

/* Notification container */
.notification-container {
    position: absolute;
    top: 50px;
    right: 20px;
    width: 300px;
    z-index: 1000000; /* Ensures it appears above other elements */
}

/* Dropdown styling */
.dropdown-menu {
    display: none;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    border-radius: 10px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    word-wrap: break-word; /* Ensures long words break */
    white-space: normal; /* Allows text to wrap */
    background-color: white; /* Prevents transparency issues */
    z-index: 1100; /* Higher than the container to be on top */
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    padding: 10px;
    font-size: 14px;
    border-bottom: 1px solid #ddd;
    word-wrap: break-word; /* Ensures long words break */
    white-space: normal; /* Allows text to wrap */
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}
canvas {
    z-index: 1 !important; /* Send charts to the back */
    position: relative; /* Ensure z-index applies */
}

</style>
</head>
<body>
<header class="app-header" ng-app="notificationApp" ng-controller="NotificationController">
  <nav class="app-nav d-flex justify-content-between">
    <!-- Sidebar button visible only on smaller screens -->
    <a class="sidebar-btn d-md-none" id="toggleBtn">☰</a>
    
    <!-- "X" button aligned to the right on larger screens -->
    <a href="#" class="d-md-flex d-md-none ms-auto tooltip-btn"><i class="bi bi-file-earmark-break-fill"></i></a>
   






    <a href="#" class="d-flex ms-auto tooltip-btn position-relative" ng-click="toggleDropdown($event)">
    <i class="bi bi-bell-fill"></i>
    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" 
          ng-if="notifications.length > 0">
        {{ notifications.length }}
    </span>
</a>


    <div class="notification-container">
        <div class="dropdown-menu p-2" ng-class="{'show': dropdownVisible}">
            <h6 class="dropdown-header">Notifications</h6>
            <ul class="list-unstyled mb-0">
                <li class="dropdown-item" ng-repeat="notification in notifications">
                    {{ notification.Message }}
                </li>
                <li class="dropdown-item text-muted text-center" ng-if="notifications.length === 0">
                    No notifications
                </li>
            </ul>
        </div>
    </div>

<!-- <div class="notification-container">
   
    <div class="dropdown" ng-class="{'show': dropdownVisible}">
        <ul>
            <li ng-repeat="notification in notifications">{{ notification.Message }}</li>
            <li ng-if="notifications.length === 0">No notifications</li>
        </ul>
    </div>
</div> -->



    
    <!-- "X" button visible on smaller screens, aligned left -->
  </nav>
</header>

<div id="sidebar" class="sidebar d-flex flex-column">
        <a  class="closebtn d-md-none" onclick="closeNav()">&times;</a>
        <a href="#" class="sgsd-title mt-5"><b>SGSD</b></a>
 
        <div class="sidebar-items">
            <hr style="width: 75%; margin: 0 auto; padding: 12px;">
            <div class="sidebar-item">
                <a href="./Dashboard" class="sidebar-items-a">
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
                <a href="../logout.php?logout=true" class="sidebar-items-button">
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
        <div class="div1 p-0">
                <div class='card p-3 text-center'>
                    <h5 class='mb-2 text-start'>Revenue</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge red'>-0.102%</span>
                        <canvas id='revenueBarChart' style="max-width:100%; height: auto;"></canvas>
                </div>
        </div>
        <div class="div2">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2 text-start'>Orders</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge green'>+1.2%</span>
                        <canvas id='ordersLineChart' style="max-width:100%; height: auto;"></canvas>
                </div>
            </div>
        </div>
        <div class="div3">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2 text-start'>Customers</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge green'>+0.96%</span>
                        <canvas id='customersLineChart' style="max-width:100%; height: auto;"></canvas>
                </div>
            </div>
        </div>
        <div class="div4">
            <div class=''>
                <div class='card p-3 text-center'>
                    <h5 class='mb-2 text-start'>Items Sold</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge red'>-1.1%</span>
                        <canvas id='itemsSoldBarChart' style="max-width:100%; height: auto;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="dashboard-summary-static">
    <div class="parent-static">
        <div class="div1-static p-0">
                <div class='card-static p-3 text-center'>
                    <h5 class='mb-2 text-start'>Revenue</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge-static red'>-0.102%</span>
                        <canvas id='revenueBarChart' style="max-width:100%; height: auto;"></canvas>
                </div>
        </div>
        <div class="div2-static">
            <div class=''>
                <div class='card-static p-3 text-center'>
                    <h5 class='mb-2 text-start'>Orders</h5>
                    <h4 class='fw-bold mb-2 text-start'>₱ 2,343</h4>
                    <span class='badge-static green'>+1.2%</span>
                        <canvas id='ordersLineChart' style="max-width:100%; height: auto;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="dashboard-top">
        <h1><b>TOP</b> SELLING</h1>
    </div>
    <div class="dashboard-top-grid">
        <div class="div1">
            <div class="doughnut-container">
                <canvas id="topSellingChart"></canvas>
            </div>
        </div>
    </div>
    <hr>
    </div> 





</div>


  
  </div>

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




    

// Revenue Bar Chart
// Revenue Bar Chart
/**new Chart(document.getElementById('revenueBarChart'), {
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

        // Orders Line Chart
        new Chart(document.getElementById('ordersLineChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{ data: [10, 15, 5, 2, 20], borderColor: '#9fb0a1', tension: 0.4 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Customers Line Chart
        new Chart(document.getElementById('customersLineChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{ data: [5, 10, 15, 10, 20], borderColor: '#9fb0a1', tension: 0.4 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Items Sold Bar Chart
        new Chart(document.getElementById('itemsSoldBarChart'), {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    data: [8, 15, 10, 5, 7],
                    backgroundColor: ['#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9']
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        }); **/

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
        url: './fetch_data.php',
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>