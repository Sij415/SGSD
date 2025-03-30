<?php
$required_role = 'admin,staff,driver';
include('../check_session.php');
include('../log_functions.php');
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="icon"  href="../logo.png">
    <link rel="stylesheet" href="../style/styles.css">
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

        // Clear all notifications
        $scope.clearAllNotifications = function () {
            $http.post('clear_notifications.php')
                .then(function (response) {
                    if (response.data.success) {
                        $scope.notifications = []; // Clear UI notifications
                    } else {
                        console.error("Failed to clear notifications:", response.data.message);
                    }
                })
                .catch(function (error) {
                    console.error("Error clearing notifications:", error);
                });
        };

        // Fetch notifications when the page loads
        $scope.getNotifications();
    });
</script>


</head>

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
        </script>


<script>
    $(document).ready(function () {
        let revenueBarChart, ordersLineChart, transactionsLineChart, itemsSoldBarChart;

        function formatDate(dateString, period) {
            let options;
            if (period === 'monthly') {
                options = { year: 'numeric', month: 'long' };
            } else if (period === 'yearly') {
                options = { year: 'numeric' };
            } else if (period === 'weekly') {
                const [year, week] = dateString.split('-').map(Number);
                return `Week ${week}`;
            } else {
                options = { year: 'numeric', month: 'long', day: 'numeric' };
            }
            return new Date(dateString).toLocaleDateString(undefined, options);
        }

        function calculatePercentageChange(current, previous) {
    if (previous === 0) return current === 0 ? '0.00%' : '100.00%';
    return ((current - previous) / previous * 100).toFixed(2) + '%';
}

        function updatePercentageSpan(spanId, percentageChange) {
            const spanElement = document.getElementById(spanId);
            if (percentageChange === '0.00%') {
                spanElement.textContent = percentageChange;
                spanElement.className = 'badge grey';
            } else if (parseFloat(percentageChange) > 0) {
                spanElement.textContent = `+${percentageChange}`;
                spanElement.className = 'badge green';
            } else {
                spanElement.textContent = `${percentageChange}`;
                spanElement.className = 'badge red';
            }
        }

        function fetchData(period) {
            $.ajax({
                url: 'fetch_data.php',
                method: 'GET',
                data: { period: period },
                dataType: 'json',
                success: function (data) {
                    // Update Revenue Chart (Bar)
                    const revenueData = data.revenue_data;
                    if (revenueBarChart) {
                        revenueBarChart.data.labels = revenueData.map(item => formatDate(item.Date, period));
                        revenueBarChart.data.datasets[0].data = revenueData.map(item => item.revenue);
                        revenueBarChart.update();
                    } else {
                        revenueBarChart = new Chart(document.getElementById('revenueBarChart'), {
                            type: 'bar',
                            data: {
                                labels: revenueData.map(item => formatDate(item.Date, period)),
                                datasets: [{
                                    data: revenueData.map(item => item.revenue),
                                    backgroundColor: ['#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8']
                                }]
                            },
                            options: { responsive: true, plugins: { legend: { display: false } } }
                        });
                    }
                    const revenueChange = revenueData.length > 1 ? calculatePercentageChange(revenueData[revenueData.length - 1].revenue, revenueData[revenueData.length - 2].revenue) : '0.00%';
                    updatePercentageSpan('revenueChangeSpan', revenueChange);

                    // Update Orders Chart (Line)
                    const ordersData = data.orders_data;
                    if (ordersLineChart) {
                        ordersLineChart.data.labels = ordersData.map(item => formatDate(item.Date, period));
                        ordersLineChart.data.datasets[0].data = ordersData.map(item => item.order_count);
                        ordersLineChart.update();
                    } else {
                        ordersLineChart = new Chart(document.getElementById('ordersLineChart'), {
                            type: 'line',
                            data: {
                                labels: ordersData.map(item => formatDate(item.Date, period)),
                                datasets: [{
                                    data: ordersData.map(item => item.order_count),
                                    borderColor: '#9fb0a1',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: function(value) {
                                                return Number.isInteger(value) ? value : null;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    const ordersChange = ordersData.length > 1 ? calculatePercentageChange(ordersData[ordersData.length - 1].order_count, ordersData[ordersData.length - 2].order_count) : '0.00%';
                    updatePercentageSpan('ordersChangeSpan', ordersChange);

                    // Update Transactions Chart (Line)
                    const transactionsData = data.transactions_data;
                    if (transactionsLineChart) {
                        transactionsLineChart.data.labels = transactionsData.map(item => formatDate(item.Date, period));
                        transactionsLineChart.data.datasets[0].data = transactionsData.map(item => item.transaction_count);
                        transactionsLineChart.update();
                    } else {
                        transactionsLineChart = new Chart(document.getElementById('transactionsLineChart'), {
                            type: 'line',
                            data: {
                                labels: transactionsData.map(item => formatDate(item.Date, period)),
                                datasets: [{
                                    data: transactionsData.map(item => item.transaction_count),
                                    borderColor: '#9fb0a1',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: function(value) {
                                                return Number.isInteger(value) ? value : null;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    const transactionsChange = transactionsData.length > 1 ? calculatePercentageChange(transactionsData[transactionsData.length - 1].transaction_count, transactionsData[transactionsData.length - 2].transaction_count) : '0.00%';
                    updatePercentageSpan('transactionsChangeSpan', transactionsChange);

                    // Update Items Sold Chart (Bar)
                    const itemsSoldData = data.items_sold_data;
                    if (itemsSoldBarChart) {
                        itemsSoldBarChart.data.labels = itemsSoldData.map(item => formatDate(item.Date, period));
                        itemsSoldBarChart.data.datasets[0].data = itemsSoldData.map(item => item.items_sold);
                        itemsSoldBarChart.update();
                    } else {
                        itemsSoldBarChart = new Chart(document.getElementById('itemsSoldBarChart'), {
                            type: 'bar',
                            data: {
                                labels: itemsSoldData.map(item => formatDate(item.Date, period)),
                                datasets: [{
                                    data: itemsSoldData.map(item => item.items_sold),
                                    backgroundColor: ['#abbaa9', '#dae3d8', '#abbaa9', '#dae3d8', '#abbaa9']
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: function(value) {
                                                return Number.isInteger(value) ? value : null;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    const itemsSoldChange = itemsSoldData.length > 1 ? calculatePercentageChange(itemsSoldData[itemsSoldData.length - 1].items_sold, itemsSoldData[itemsSoldData.length - 2].items_sold) : '0.00%';
                    updatePercentageSpan('itemsSoldChangeSpan', itemsSoldChange);

                    // Display total customers
                    document.getElementById('totalCustomers').textContent = data.total_customers.total_customers;

                    // Display total products
                    document.getElementById('totalProducts').textContent = data.total_products.total_products;

                    const filteredProductQuantityData = data.product_quantity_data.filter(item => item.period === period);
                },
                    error: function (xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        });
    }


    // Event listener for date range buttons
    $('input[name="data_range"]').change(function () {
        const period = $(this).val();
        fetchData(period);
    });

    // Initial fetch for the default period
    fetchData('monthly');
});
</script>


<script>
$(document).ready(function () {
    let productPieChart;

    function stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const hue = hash % 360;
        return `hsl(${hue}, 70%, 50%)`;
    }

    function fetchPieChartData(period) {
        console.log("Fetching Pie Chart Data for:", period);

        $.ajax({
            url: 'fetch_data.php',
            method: 'GET',
            data: { period: period, chart: 'pie' },
            dataType: 'json',
            success: function (data) {
                console.log("Full API Response:", data);

                if (!data || !data.product_quantity_data || data.product_quantity_data.length === 0) {
                    console.warn("⚠️ No product quantity data found!");
                    
                    if (productPieChart) {
                        productPieChart.destroy();
                        productPieChart = null;
                    }

                    return;
                }

                // ✅ Filter out products with 0 sales
                let filteredData = data.product_quantity_data.filter(item => item.quantity_sold > 0);
                if (filteredData.length === 0) {
                    console.warn("⚠️ No valid products to display.");

                    if (productPieChart) {
                        productPieChart.destroy();
                        productPieChart = null;
                    }

                    return;
                }

                let labels = filteredData.map(item => item.Product_Name);
                let values = filteredData.map(item => item.percentage);
                let colors = labels.map(label => stringToColor(label));

                let ctx = document.getElementById('productPieChart');
                if (!ctx) {
                    console.error("❌ Missing #productPieChart canvas element.");
                    return;
                }

                ctx = ctx.getContext('2d');

                if (productPieChart) {
                    productPieChart.destroy();
                }

                // Create a varied color palette with contrasting colors
                const colorPalette = [
                    '#4e79a7', '#f28e2c', '#e15759', '#76b7b2', '#59a14f',
                    '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab',
                    '#6b6ecf', '#d37295', '#b07aa1', '#9d7660', '#5d8ca8',
                    '#ff8c00', '#8549ba', '#008080', '#d62728', '#ffbb78'
                ];

                // Use custom colors instead of randomly generated ones
                const chartColors = labels.map((_, i) => colorPalette[i % colorPalette.length]);

                // Plugin to draw percentages inside the pie chart with enhanced readability
                const doughnutLabelsPlugin = {
                    id: 'doughnutLabels',
                    afterDraw(chart) {
                        const { ctx, width, height, _metasets } = chart;
                        
                        // Only proceed if we have data
                        if (!chart.data.datasets[0].data.length) return;
                        
                        // Get the metadata for the doughnut chart
                        const meta = _metasets[0];
                        
                        ctx.save();
                        
                        // For each data point, draw the percentage
                        meta.data.forEach((element, index) => {
                            // Get percentage value
                            const value = values[index];
                            const percentage = value.toFixed(1) + '%';
                            
                            // Only show label if segment is large enough (more than 3%)
                            if (value < 3) return;
                            
                            // Calculate the middle point of the segment
                            const { x, y } = element.getCenterPoint();
                            
                            // Get the angle in the middle of the segment
                            const angle = element.startAngle + (element.endAngle - element.startAngle) / 2;
                            
                            // Calculate segment size ratio (0-1) to adjust text size and position
                            const segmentRatio = value / 100;
                            
                            // Reduced radiusRatio to bring percentages closer to center
                            const radiusRatio = -0.001 + (segmentRatio * 0.1); // between 25% and 35% of radius
                            const radius = Math.min(chart.chartArea.width, chart.chartArea.height) / 2 * radiusRatio;
                            
                            // Position text
                            const newX = x + Math.cos(angle) * radius;
                            const newY = y + Math.sin(angle) * radius;
                            
                            // Calculate font size based on segment size (with min/max limits)
                            const baseFontSize = 12;
                            const minFontSize = 10;
                            const maxFontSize = 16;
                            const fontSize = Math.max(minFontSize, Math.min(maxFontSize, 
                                               baseFontSize + (segmentRatio * 10)));
                            
                            // Get segment color and determine if we need dark or light text
                            const bgColor = chartColors[index];
                            // Simple luminance formula to determine if background is dark or light
                            const r = parseInt(bgColor.slice(1, 3), 16);
                            const g = parseInt(bgColor.slice(3, 5), 16);
                            const b = parseInt(bgColor.slice(5, 7), 16);
                            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                            const textColor = luminance > 0.5 ? '#000000' : '#FFFFFF';
                            
                            // Draw text background/container for better readability
                            const textWidth = ctx.measureText(percentage).width;
                            const padding = 4;
                            const backgroundRadius = 10;
                            
                            // Draw rounded rectangle background with semi-transparency
                            ctx.beginPath();
                            ctx.roundRect(
                                newX - (textWidth/2) - padding, 
                                newY - (fontSize/2) - padding,
                                textWidth + (padding * 2),
                                fontSize + (padding * 2),
                                backgroundRadius
                            );
                            ctx.fillStyle = 'rgba(0, 0, 0, 0)';
                            ctx.fill();
                            
                            // Set text styling
                            ctx.font = `bold ${fontSize}px Inter`;
                            ctx.fillStyle = '#FFFFFF';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            
                            // Add text shadow for better visibility
                            ctx.shadowColor = 'rgba(0, 0, 0, 0.7)';
                            ctx.shadowBlur = 3;
                            
                            // Draw the text
                            ctx.fillText(percentage, newX, newY);
                            
                            // Reset shadow
                            ctx.shadowBlur = 0;
                        });
                        
                        ctx.restore();
                    }
                };

                productPieChart = new Chart(ctx, {
                    type: 'doughnut', // Use doughnut for more modern look
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: chartColors,
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverBorderWidth: 4,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%', // Creates a nice doughnut shape
                        plugins: {
                            legend: {
                                position: window.innerWidth < 768 ? 'bottom' : 'right', // Change legend position based on screen width
                                align: 'center',
                                labels: {
                                    padding: 15,
                                    pointStyle: 'circle',
                                    font: {
                                        family: 'Inter',
                                        size: 12,
                                        weight: '600'
                                    },
                                    generateLabels: function (chart) {
                                        let dataset = chart.data.datasets[0]; 
                                        if (!dataset || !dataset.data || dataset.data.length === 0) {
                                            return [];
                                        }
                                        return filteredData.map((product, index) => ({
                                            text: `${product.Product_Name}: ${product.percentage.toFixed(1)}%`,
                                            fillStyle: dataset.backgroundColor[index],
                                            hidden: !chart.getDataVisibility(index),
                                            index: index
                                        }));
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#333',
                                bodyColor: '#333',
                                borderColor: '#ccc',
                                borderWidth: 1,
                                cornerRadius: 8,
                                padding: 12,
                                boxPadding: 6,
                                callbacks: {
                                    label: function (tooltipItem) {
                                        let index = tooltipItem.dataIndex;
                                        let productData = filteredData[index];
                                        if (!productData) {
                                            return "No data available";
                                        }
                                        return [
                                            `Product: ${productData.Product_Name}`,
                                            `Units sold: ${productData.quantity_sold}`,
                                            `Share: ${productData.percentage.toFixed(2)}%`
                                        ];
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 1000,
                            easing: 'easeOutCirc'
                        },
                        layout: {
                            padding: 20
                        }
                    },
                    plugins: [doughnutLabelsPlugin]
                });

            },
            error: function (xhr, status, error) {
                console.error("Error fetching pie chart data:", error);
            }
        });
    }

    $('input[name="pie_data_range"]').change(function () {
        const period = $(this).val();
        console.log("Filter changed to:", period);
        fetchPieChartData(period);
    });

    fetchPieChartData('monthly');
});
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

            <!-- Revision 1 -->
            <li>
                <a href="../InboundInvoices">
                    <i class="fa-solid fa-file-import" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Inbound Invoices</span>
                </a>
            </li>

            <li>
                <a href="../OutboundInvoices">
                    <i class="fa-solid fa-file-export" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Outbound Invoices</span>
                </a>
            </li>
            <!-- Revision 1 CODE ENDS HERE -->

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
            <a href="#" class="logout" onclick="document.getElementById('logoutForm').submit();">
    <i class="fa-solid fa-sign-out-alt"></i>
    <span>Log out</span>
</a>
<form id="logoutForm" method="POST" action="">
    <input type="hidden" name="logout" value="1">
</form>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content" style="max-height: 750px; overflow-y: auto;">
    <div ng-app="notificationApp" ng-controller="NotificationController">
    <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button type="button" id="sidebarCollapse" class="btn btn-info ml-1">
                <i class="fas fa-align-left"></i>
            </button>
            <!-- Right-aligned group for notification and manual buttons -->
            <div class="ml-auto d-flex align-items-center">
                <!-- Notification system -->
                <div class="dropdown d-inline-block">
                    <button class="btn position-relative" type="button" id="notificationButton" 
                            ng-click="toggleDropdown($event)" aria-haspopup="true" aria-expanded="false" 
                            data-toggle="tooltip" data-placement="bottom" title="Notifications" 
                            style="background: #6fa062; color: #fff; border: none; border-radius: 24px; width: 40px; height: 40px; transition: transform 0.3s;">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" 
                              style="font-size: 0.6rem; top: -5px; right: -5px; background-color: #dc3545;"
                              ng-if="notifications.length > 0">
                            {{ notifications.length }}
                        </span>
                    </button>
                        <div class="dropdown-menu dropdown-menu-right p-0" ng-class="{'show': dropdownVisible}"
                             aria-labelledby="notificationButton" 
                             style="width: 300px; border-radius: 12px; border: none; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                            <div class="p-2 border-bottom d-flex justify-content-between align-items-center position-sticky top-0 bg-white" 
                                 style="border-radius: 12px; z-index: 1030; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h6 class="m-0" style="font-weight: 600; letter-spacing: -0.045em;">Notifications</h6>
                                <a href="#" style="color: #6fa062; font-weight: 600; font-size: 0.85rem;"
                                   ng-click="clearAllNotifications()">Clear all notifications</a>
                            </div>
                        <div class="p-2 h-100" 
                        style="overflow-y: auto; width: 300px; max-height: 350px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; border: none; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                            <div ng-if="notifications.length === 0" class="notification-item p-3 text-center">
                                <i class="fas fa-bell-slash mb-2" style="font-size: 1.5rem; color: #adb5bd;"></i>
                                <p class="mb-0" style="color: #6c757d;">No notifications</p>
                            </div>
                            <div ng-repeat="notification in notifications" class="notification-item p-2 border-bottom">
                                <div class="d-flex">
                                    <div class="mr-3">
                                        <i class="fas fa-bell p-2 rounded-circle" style="background-color: #e8ecef; color: #6fa062;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 font-weight-bold" style="font-size: 0.9rem; letter-spacing: -0.045em;">
                                            {{ notification.Title || 'Notification' }}
                                        </p>
                                        <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ notification.Message }}</p>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            {{ notification.Created_At || 'Just now' }}
                                        </small>
                                    </div>
                        </div>
                            </div>  
                            </div>
                        </div>
                    </div>
                    <!-- Manual button (now next to notification) -->
                    <a href="../Manual/Manual-Placeholder.pdf" class="btn btn-dark ml-2 d-flex justify-content-center align-items-center" id="manualButton" data-toggle="tooltip" data-placement="bottom" target="_blank" title="View Manual">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    
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
                            <span id="revenueChangeSpan" class='badge red'></span>
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
                                <span id="ordersChangeSpan" class='badge green'></span>
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
                            <h5 class='mb-2'>Transactions</h5>
                            <span id="transactionsChangeSpan" class='badge green'></span> <!-- Corrected span ID -->
                        </div>
                        <div class='chart-container mt-3'>
                            <canvas id='transactionsLineChart'></canvas> <!-- Corrected canvas ID -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="div4">
                <div class=''>
                    <div class='card p-3 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class='mb-2'>Items Sold</h5>
                            <span id="itemsSoldChangeSpan" class='badge red'></span>
                        </div>
                        <div class='chart-container mt-3'>
                            <canvas id='itemsSoldBarChart'></canvas> <!-- Corrected canvas ID -->
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>

            <div class="dashboard-top">
            <h1 style="font-size: 40px; font-weight: 800;">Core Assets
                    <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="This section provides a summary of the core assets of the business, including the total number of customers and products."></i>
                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();   
                        });
                    </script>
                </h1>            
            </div>
            <h4 class="pb-3 pt-1" style="color: gray; font-size: 18px; font-weight: 300;">A summary of our total customers and products.</h4>
            <div class="dashboard-summary">
            <div class="parent">
            <div class="div1">
                <div class=''>
                    <div class='card p-4 text-center'>
                    <div class="d-flex justify-content-between align-items-center">
                    <div class="text-left">
                    <span id="totalCustomers" class='display-4 ml-4' style='font-weight: 500;'></span>
                            <h5 class='mb-2 mt-2'>Total Customers</h5>
                        </div>
                        <div class='mr-2'>
                            <i class="fas fa-user-circle" style="font-size: 13rem; color: #6fa062; position: absolute; opacity: 0.125; top: 50%; left: 90%; transform: translate(-50%, -50%);"></i>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div2">
                <div class=''>
                <div class='card p-4 text-center'>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-left">
                                <span id="totalProducts" class='display-4 ml-4' style='font-weight: 500;'></span>
                                <h5 class='mb-2 mt-2'>Total Products</h5>
                            </div>
                            <div class='mr-2'>
                                <i class="fas fa-cubes" style="font-size: 10rem; color: #6fa062; position: absolute; opacity: 0.125; top: 50%; left: 90%; transform: translate(-50%, -50%);"></i>
                            </div>
                    </div>
                </div>
            </div>
            </div>
            </div>
            
<!-- Placeholder for Pie Graph -->
<div class="dashboard-top-2">
    <h1 style="font-size: 40px; font-weight: 800;">Top Selling Products
        <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="This is the top selling products pie chart."></i>
        <script>
            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip();   
            });
        </script>
    </h1>                        
</div>
<h4 class="pb-3 pt-1" style="color: gray; font-size: 18px; font-weight: 300;">A summary of key metrics such as revenue, orders, customers, and items sold.</h4>
<div class="date-filter-group-2 mb-3">
    <div class="btn-group" role="group" aria-label="Pie Data Range">
        <input type="radio" class="date-filter-input" name="pie_data_range" id="day-pie" autocomplete="off" value="daily">
        <label class="date-filter-btn" for="day-pie">Day</label>

        <input type="radio" class="date-filter-input" name="pie_data_range" id="week-pie" autocomplete="off" value="weekly">
        <label class="date-filter-btn" for="week-pie">Week</label>

        <input type="radio" class="date-filter-input" name="pie_data_range" id="month-pie" autocomplete="off" value="monthly" checked>
        <label class="date-filter-btn" for="month-pie">Month</label>

        <input type="radio" class="date-filter-input" name="pie_data_range" id="year-pie" autocomplete="off" value="yearly">
        <label class="date-filter-btn" for="year-pie">Year</label>
    </div>
</div>
<div class="dashboard-top-grid">
    <div class="card p-3 text-center" style="width: 100%; border: none; background: #FFFFFF;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class='mb-2' style="text-align: left;">Top Selling Products</h5>
        </div>
        <div class='pie-chart-container mt-3'>
        <canvas id="productPieChart" width="500" height="500"></canvas>
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

        .badge.grey {
            background-color:rgb(209, 214, 207);
            color:rgb(122, 115, 115);
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