<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../dbconnect.php';

$period = $_GET['period'] ?? 'daily';

switch ($period) {
    case 'daily':
        $interval = 'DAY';
        break;
    case 'weekly':
        $interval = 'WEEK';
        break;
    case 'monthly':
        $interval = 'MONTH';
        break;
    case 'yearly':
        $interval = 'YEAR';
        break;
    default:
        $interval = 'DAY';
        break;
}

try {
    // Fetch the current number of users
    $userCountResult = $conn->query("SELECT COUNT(*) as count FROM Users");
    if (!$userCountResult) {
        throw new Exception("Error fetching user count: " . $conn->error);
    }
    $userCount = $userCountResult->fetch_assoc()['count'];

    // Fetch the total revenue
    $revenueResult = $conn->query("SELECT SUM(Products.Price) as revenue FROM Transactions JOIN Orders ON Transactions.Order_ID = Orders.Order_ID JOIN Products ON Orders.Product_ID = Products.Product_ID WHERE Transactions.Transaction_Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)");
    if (!$revenueResult) {
        throw new Exception("Error fetching revenue: " . $conn->error);
    }
    $revenue = $revenueResult->fetch_assoc()['revenue'] ?? 0;

    // Fetch the total number of orders
    $orderCountResult = $conn->query("SELECT COUNT(*) as count FROM Transactions WHERE Transactions.Transaction_Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)");
    if (!$orderCountResult) {
        throw new Exception("Error fetching order count: " . $conn->error);
    }
    $orderCount = $orderCountResult->fetch_assoc()['count'];

    // Fetch the total items sold
    $itemsSoldResult = $conn->query("
        SELECT SUM(Orders.Quantity) as items_sold 
        FROM Orders 
        JOIN Transactions ON Transactions.Order_ID = Orders.Order_ID 
        WHERE Transactions.Transaction_Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval)
    ");
    if (!$itemsSoldResult) {
        throw new Exception("Error fetching items sold: " . $conn->error);
    }
    $itemsSold = $itemsSoldResult->fetch_assoc()['items_sold'] ?? 0;

    // Fetch the top-selling product
    $topSellingResult = $conn->query("
        SELECT Products.Product_Name, SUM(Orders.Quantity) as count 
        FROM Transactions 
        JOIN Orders ON Transactions.Order_ID = Orders.Order_ID 
        JOIN Products ON Orders.Product_ID = Products.Product_ID 
        WHERE Transactions.Transaction_Date >= DATE_SUB(CURDATE(), INTERVAL 1 $interval) 
        GROUP BY Products.Product_Name 
        ORDER BY count DESC 
        LIMIT 1
    ");
    if (!$topSellingResult) {
        throw new Exception("Error fetching top-selling product: " . $conn->error);
    }
    $topSelling = $topSellingResult->fetch_assoc();

    $response = [
        'userCount' => $userCount,
        'revenue' => $revenue,
        'orderCount' => $orderCount,
        'itemsSold' => $itemsSold,
        'topSelling' => $topSelling['Product_Name'] ?? 'N/A',
    ];

    // Output the response as JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Output the error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>