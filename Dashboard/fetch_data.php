<?php
include 'dbconnect.php';

// Fetch data for Customers Chart
$query = "SELECT Date, COUNT(Customer_ID) as customer_count FROM Transactions GROUP BY Date";
$result = $conn->query($query);
$customers_data = array();
while ($row = $result->fetch_assoc()) {
    $customers_data[] = $row;
}
$result->close();

// Fetch data for Orders Chart
$query = "SELECT Date, COUNT(Order_ID) as order_count FROM Transactions GROUP BY Date";
$result = $conn->query($query);
$orders_data = array();
while ($row = $result->fetch_assoc()) {
    $orders_data[] = $row;
}
$result->close();

echo json_encode(array(
    "customers_data" => $customers_data,
    "orders_data" => $orders_data
));
?>
