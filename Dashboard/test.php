<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Analytics Dashboard</h1>

    <h2>Users by Role</h2>
    <canvas id="usersByRoleChart"></canvas>

    <h2>Products by Type</h2>
    <canvas id="productsByTypeChart"></canvas>

    <h2>Orders by Status</h2>
    <canvas id="ordersByStatusChart"></canvas>

    <?php
    include '../dbconnect.php';

    try {
        // Fetch number of users by role
        $stmt = $pdo->query("SELECT Role, COUNT(*) as count FROM Users GROUP BY Role");
        $usersByRole = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch product types and their counts
        $stmt = $pdo->query("SELECT Product_Type, COUNT(*) as count FROM Products GROUP BY Product_Type");
        $productsByType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch order status
        $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM Orders GROUP BY Status");
        $ordersByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Encode data to JSON
        $usersByRoleJson = json_encode($usersByRole);
        $productsByTypeJson = json_encode($productsByType);
        $ordersByStatusJson = json_encode($ordersByStatus);

    } catch (Exception $e) {
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        exit;
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                // Parse JSON data from PHP
                const usersByRoleData = JSON.parse('<?php echo $usersByRoleJson; ?>');
                const productsByTypeData = JSON.parse('<?php echo $productsByTypeJson; ?>');
                const ordersByStatusData = JSON.parse('<?php echo $ordersByStatusJson; ?>');

                // Check if data is fetched correctly
                console.log('Users by Role:', usersByRoleData);
                console.log('Products by Type:', productsByTypeData);
                console.log('Orders by Status:', ordersByStatusData);

                const usersByRoleCtx = document.getElementById('usersByRoleChart').getContext('2d');
                const productsByTypeCtx = document.getElementById('productsByTypeChart').getContext('2d');
                const ordersByStatusCtx = document.getElementById('ordersByStatusChart').getContext('2d');

                new Chart(usersByRoleCtx, {
                    type: 'bar',
                    data: {
                        labels: usersByRoleData.map(d => d.Role),
                        datasets: [{
                            label: 'Number of Users',
                            data: usersByRoleData.map(d => d.count),
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    }
                });

                new Chart(productsByTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: productsByTypeData.map(d => d.Product_Type),
                        datasets: [{
                            label: 'Product Types',
                            data: productsByTypeData.map(d => d.count),
                            backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                            borderWidth: 1
                        }]
                    }
                });

                new Chart(ordersByStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ordersByStatusData.map(d => d.Status),
                        datasets: [{
                            label: 'Order Status',
                            data: ordersByStatusData.map(d => d.count),
                            backgroundColor: ['rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                            borderColor: ['rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)', 'rgba(75, 192, 192, 1)'],
                            borderWidth: 1
                        }]
                    }
                });
            } catch (error) {
                console.error('Error parsing JSON data or creating charts:', error);
            }
        });
    </script>
</body>
</html>