<?php
include '../dbconnect.php';

// Fetch Revenue Data (Assuming completed orders generate revenue)
$query = "SELECT t.Date, SUM(p.Price) AS revenue 
          FROM Transactions t 
          JOIN Orders o ON t.Order_ID = o.Order_ID 
          JOIN Products p ON o.Product_ID = p.Product_ID 
          GROUP BY t.Date";
$result = $conn->query($query);
$revenue_data = [];
while ($row = $result->fetch_assoc()) {
    $revenue_data[] = $row;
}
$result->close();

// Fetch Orders Data
$query = "SELECT Date, COUNT(Order_ID) as order_count FROM Transactions GROUP BY Date";
$result = $conn->query($query);
$orders_data = [];
while ($row = $result->fetch_assoc()) {
    $orders_data[] = $row;
}
$result->close();

// Fetch Customers Data
$query = "SELECT Date, COUNT(Customer_ID) as customer_count FROM Transactions GROUP BY Date";
$result = $conn->query($query);
$customers_data = [];
while ($row = $result->fetch_assoc()) {
    $customers_data[] = $row;
}
$result->close();

// Fetch Items Sold Data
$query = "SELECT t.Date, COUNT(o.Product_ID) AS items_sold
          FROM Transactions t
          JOIN Orders o ON t.Order_ID = o.Order_ID
          GROUP BY t.Date";
$result = $conn->query($query);
$items_sold_data = [];
while ($row = $result->fetch_assoc()) {
    $items_sold_data[] = $row;
}
$result->close();

echo json_encode([
    "revenue_data" => $revenue_data,
    "orders_data" => $orders_data,
    "customers_data" => $customers_data,
    "items_sold_data" => $items_sold_data
]);
?>
