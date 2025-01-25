<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the homepage if no valid session
    header("Location: ../");
    exit();
}

// Include the database connection
include('../dbconnect.php');

// Fetch the user's role from the database
$user_id = $_SESSION['user_id'];
$role = "";

$sql = "SELECT Role, First_Name FROM users WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = htmlspecialchars($user['Role']);
    $_SESSION['First_Name'] = htmlspecialchars($user['First_Name']); // Store first name in the session
} else {
    $role = "Unknown";
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <h1 class="fw-bold">ANALYTICS DASHBOARD</h1>
        </div>

        <!-- Welcome Message with Role -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="alert alert-success">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['First_Name']); ?></strong>!<br>
                <span class="text-muted">Role: <strong><?php echo $role; ?></strong></span>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#">Daily</a>
            </li>
            <li class="nav-item">
                <a class="nav-link inactive" href="#">Weekly</a>
            </li>
            <li class="nav-item">
                <a class="nav-link inactive" href="#">Monthly</a>
            </li>
            <li class="nav-item">
                <a class="nav-link inactive" href="#">Yearly</a>
            </li>
        </ul>

        <!-- Dashboard Cards -->
        <div class="row g-3">
            <?php
            $cards = [
                ["Revenue", "₱ 2,343", "-0.102%", "red", "revenueBarChart"],
                ["Orders", "45", "+1.2%", "green", "ordersLineChart"],
                ["Customers", "12", "+0.96%", "green", "customersLineChart"],
                ["Items Sold", "34", "-1.1%", "red", "itemsSoldBarChart"],
            ];
            foreach ($cards as $card) {
                echo "
                <div class='col-md-3'>
                    <div class='card p-3 text-center'>
                        <h5 class='mb-2'>{$card[0]}</h5>
                        <h2 class='fw-bold mb-2'>{$card[1]}</h2>
                        <span class='badge {$card[3]}'>{$card[2]}</span>
                        <div class='chart-container mt-3'>
                            <canvas id='{$card[4]}'></canvas>
                        </div>
                    </div>
                </div>
                ";
            }
            ?>
        </div>

        <!-- Top Selling Section -->
        <div class="mt-5">
            <h5 class="mb-3">Top Selling</h5>
            <div class="doughnut-container">
                <canvas id="topSellingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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
        });

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
</body>

<footer class="footer">
    © SGSD 2025
</footer>
</html>
