<?php
include '../dbconnect.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'month'; // Default to month

// Modify the queries to handle different periods
if ($period === 'daily') {
    $date_format = '%Y-%m-%d';
    $interval_clause = "DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $pie_interval_clause = "DATE(t.Date) = CURDATE()";
} elseif ($period === 'weekly') {
    $date_format = '%Y-%u';
    $interval_clause = "DATE_SUB(NOW(), INTERVAL 5 WEEK)";
    $pie_interval_clause = "YEARWEEK(t.Date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($period === 'monthly') {
    $date_format = '%Y-%m';
    $interval_clause = "DATE_SUB(NOW(), INTERVAL 6 MONTH)";
    $pie_interval_clause = "DATE_FORMAT(t.Date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
} elseif ($period === 'yearly') {
    $date_format = '%Y';
    $interval_clause = "DATE_SUB(NOW(), INTERVAL 3 YEAR)";
    $pie_interval_clause = "YEAR(t.Date) = YEAR(CURDATE())";
} else {
    $date_format = '%Y-%m-%d';
    $interval_clause = "DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $pie_interval_clause = "DATE(t.Date) = CURDATE()";
}

// Fetch Revenue Data (Including only completed orders)
$query = "SELECT DATE_FORMAT(t.Date, '$date_format') AS Date, SUM(p.Price) AS revenue 
          FROM Transactions t 
          JOIN Orders o ON t.Order_ID = o.Order_ID 
          JOIN Products p ON o.Product_ID = p.Product_ID 
          WHERE t.Date >= $interval_clause AND o.Status = 'Completed'
          GROUP BY DATE_FORMAT(t.Date, '$date_format')";
$result = $conn->query($query);
$revenue_data = [];
while ($row = $result->fetch_assoc()) {
    $revenue_data[] = $row;
}
$result->close();

// Fetch Orders Data
$query = "SELECT DATE_FORMAT(Date, '$date_format') AS Date, COUNT(Order_ID) as order_count 
          FROM Transactions 
          WHERE Date >= $interval_clause
          GROUP BY DATE_FORMAT(Date, '$date_format')";
$result = $conn->query($query);
$orders_data = [];
while ($row = $result->fetch_assoc()) {
    $orders_data[] = $row;
}
$result->close();

// Fetch Transactions Data
$query = "SELECT DATE_FORMAT(Date, '$date_format') AS Date, COUNT(Transaction_ID) as transaction_count 
          FROM Transactions 
          WHERE Date >= $interval_clause
          GROUP BY DATE_FORMAT(Date, '$date_format')";
$result = $conn->query($query);
$transactions_data = [];
while ($row = $result->fetch_assoc()) {
    $transactions_data[] = $row;
}
$result->close();

// Fetch Items Sold Data (Including only completed orders and order_type 'Sale')
$query = "SELECT DATE_FORMAT(t.Date, '$date_format') AS Date, COUNT(o.Product_ID) AS items_sold
          FROM Transactions t
          JOIN Orders o ON t.Order_ID = o.Order_ID
          WHERE t.Date >= $interval_clause AND o.Status = 'Completed' AND o.Order_Type = 'Sale'
          GROUP BY DATE_FORMAT(t.Date, '$date_format')";
$result = $conn->query($query);
$items_sold_data = [];
while ($row = $result->fetch_assoc()) {
    $items_sold_data[] = $row;
}
$result->close();

// Fetch total customers
$query = "SELECT COUNT(Customer_ID) as total_customers FROM customers";
$result = $conn->query($query);
$total_customers = $result->fetch_assoc();
$result->close();

// Fetch total products
$query = "SELECT COUNT(Product_ID) as total_products FROM products";
$result = $conn->query($query);
$total_products = $result->fetch_assoc();
$result->close();

// Fetch product quantity data for pie chart
$query = "SELECT p.Product_Name, SUM(o.quantity) AS quantity_sold
          FROM Orders o
          JOIN Products p ON o.Product_ID = p.Product_ID
          JOIN Transactions t ON o.Order_ID = t.Order_ID
          WHERE o.Status = 'Completed' AND o.Order_Type = 'Sale'
          AND $pie_interval_clause
          GROUP BY p.Product_Name";
$result = $conn->query($query);
$product_quantity_data = [];
$total_quantity_sold = 0; // Initialize total quantity sold

while ($row = $result->fetch_assoc()) {
    $total_quantity_sold += $row['quantity_sold']; // Sum total quantity sold
    $product_quantity_data[] = $row;
}
$result->close();

// Calculate percentages
foreach ($product_quantity_data as &$product) {
    $product['percentage'] = $total_quantity_sold > 0 ? ($product['quantity_sold'] / $total_quantity_sold) * 100 : 0;
}

echo json_encode([
    "revenue_data" => $revenue_data,
    "orders_data" => $orders_data,
    "transactions_data" => $transactions_data,
    "items_sold_data" => $items_sold_data,
    "total_customers" => $total_customers,
    "total_products" => $total_products,
    "product_quantity_data" => $product_quantity_data
]);

error_log(json_encode($product_quantity_data)); // Log the fetched data
?>

