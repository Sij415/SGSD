<?php
include '../dbconnect.php';

$period = $_GET['period'] ?? 'daily';

switch ($period) {
    case 'daily':
        $interval = 'DAY';
        $dateFormat = '%Y-%m-%d';
        break;
    case 'weekly':
        $interval = 'WEEK';
        $dateFormat = '%Y-%u'; // Year and week number
        break;
    case 'monthly':
        $interval = 'MONTH';
        $dateFormat = '%Y-%m';
        break;
    case 'yearly':
        $interval = 'YEAR';
        $dateFormat = '%Y';
        break;
    default:
        $interval = 'DAY';
        $dateFormat = '%Y-%m-%d';
        break;
}

// Function to execute query and capture errors if any
function executeQuery($query, $conn) {
    $result = $conn->query($query);
    if (!$result) {
        error_log("Error executing query: " . $conn->error . "\nQuery: " . $query);
        return ["error" => $conn->error, "query" => $query];
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->close();
    return $data;
}

// Fetch Revenue Data (Assuming completed orders generate revenue)
$query = "SELECT DATE_FORMAT(t.Date, '$dateFormat') AS Date, SUM(p.Price) AS revenue 
          FROM Transactions t 
          JOIN Orders o ON t.Order_ID = o.Order_ID 
          JOIN Products p ON o.Product_ID = p.Product_ID 
          WHERE t.Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)
          GROUP BY DATE_FORMAT(t.Date, '$dateFormat')";
$revenue_data = executeQuery($query, $conn);

// Fetch Orders Data
$query = "SELECT DATE_FORMAT(Date, '$dateFormat') AS Date, COUNT(Order_ID) as order_count 
          FROM Transactions 
          WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)
          GROUP BY DATE_FORMAT(Date, '$dateFormat')";
$orders_data = executeQuery($query, $conn);

// Fetch Customers Data
$query = "SELECT DATE_FORMAT(Date, '$dateFormat') AS Date, COUNT(Customer_ID) as customer_count 
          FROM Transactions 
          WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)
          GROUP BY DATE_FORMAT(Date, '$dateFormat')";
$customers_data = executeQuery($query, $conn);

// Fetch Items Sold Data
$query = "SELECT DATE_FORMAT(t.Date, '$dateFormat') AS Date, COUNT(o.Product_ID) AS items_sold
          FROM Transactions t
          JOIN Orders o ON t.Order_ID = o.Order_ID
          WHERE t.Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)
          GROUP BY DATE_FORMAT(t.Date, '$dateFormat')";
$items_sold_data = executeQuery($query, $conn);

$response = [
    "revenue_data" => $revenue_data,
    "orders_data" => $orders_data,
    "customers_data" => $customers_data,
    "items_sold_data" => $items_sold_data
];

// Output JSON response
header('Content-Type: application/json'); // Ensure correct content type
echo json_encode($response);

// Log the response for debugging
error_log("Response: " . json_encode($response));
?>