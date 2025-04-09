<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../dbconnect.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'monthly'; // Default to monthly

// Helper function to fill missing dates with 0 values
function fill_missing_dates($data, $date_format, $interval_clause, $conn, $value_key) {
    $filled_data = [];
    $date_range_query = "SELECT DATE_FORMAT(date_range.Date, '$date_format') AS Date FROM 
                         (SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS Date 
                          FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a 
                          CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b 
                          CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c) AS date_range 
                         WHERE date_range.Date >= $interval_clause 
                         GROUP BY DATE_FORMAT(date_range.Date, '$date_format')";

    $date_range_result = $conn->query($date_range_query);

    while ($date_row = $date_range_result->fetch_assoc()) {
        $date = $date_row['Date'];
        $found = false;

        foreach ($data as $row) {
            if ($row['Date'] === $date) {
                $filled_data[] = $row;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $filled_data[] = ['Date' => $date, $value_key => 0];
        }
    }

    return $filled_data;
}

// Define date format and interval clause based on the period
if ($period === 'daily') {
    $date_format = '%Y-%m-%d';
    $interval_clause = "DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
    $pie_interval_clause = "DATE(t.Date) = CURDATE()";
} elseif ($period === 'weekly') {
    $date_format = '%Y-%u';
    $interval_clause = "DATE_SUB(CURDATE(), INTERVAL 5 WEEK)";
    $pie_interval_clause = "YEARWEEK(t.Date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($period === 'monthly') {
    $date_format = '%Y-%m';
    $interval_clause = "DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
    $pie_interval_clause = "DATE_FORMAT(t.Date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
} elseif ($period === 'yearly') {
    $date_format = '%Y';
    $interval_clause = "DATE_SUB(CURDATE(), INTERVAL 3 YEAR)";
    $pie_interval_clause = "YEAR(t.Date) = YEAR(CURDATE())";
} else {
    $date_format = '%Y-%m-%d';
    $interval_clause = "DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
    $pie_interval_clause = "DATE(t.Date) = CURDATE()";
}

// Fetch Revenue Data (Including only Delivered orders)
$query = "SELECT DATE_FORMAT(t.Date, '$date_format') AS Date, SUM(o.Total_Price) AS revenue 
          FROM Transactions t 
          JOIN Orders o ON t.Order_ID = o.Order_ID 
          WHERE t.Date >= $interval_clause
          GROUP BY DATE_FORMAT(t.Date, '$date_format')";
$result = $conn->query($query);
$revenue_data = [];
while ($row = $result->fetch_assoc()) {
    $revenue_data[] = $row;
}
$result->close();
$revenue_data = fill_missing_dates($revenue_data, $date_format, $interval_clause, $conn, 'revenue');

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
$orders_data = fill_missing_dates($orders_data, $date_format, $interval_clause, $conn, 'order_count');

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
$transactions_data = fill_missing_dates($transactions_data, $date_format, $interval_clause, $conn, 'transaction_count');

// Fetch Items Sold Data (Including only Delivered orders and order_type 'Outbound')
$query = "SELECT DATE_FORMAT(t.Date, '$date_format') AS Date, SUM(o.Quantity) AS items_sold
          FROM Transactions t
          JOIN Orders o ON t.Order_ID = o.Order_ID
          WHERE t.Date >= $interval_clause AND o.Order_Type = 'Outbound'
          GROUP BY DATE_FORMAT(t.Date, '$date_format')";
$result = $conn->query($query);
$items_sold_data = [];
while ($row = $result->fetch_assoc()) {
    $items_sold_data[] = $row;
}
$result->close();
$items_sold_data = fill_missing_dates($items_sold_data, $date_format, $interval_clause, $conn, 'items_sold');

// Fetch total customers
$query = "SELECT COUNT(Customer_ID) as total_customers FROM Customers";
$result = $conn->query($query);
$total_customers = $result->fetch_assoc();
$result->close();

// Fetch total products
$query = "SELECT COUNT(Product_ID) as total_products FROM Products";
$result = $conn->query($query);
$total_products = $result->fetch_assoc();
$result->close();

// Fetch product quantity data for pie chart
$query = "SELECT p.Product_Name, SUM(o.quantity) AS quantity_sold
          FROM Orders o
          JOIN Products p ON o.Product_ID = p.Product_ID
          JOIN Transactions t ON o.Order_ID = t.Order_ID
          WHERE o.Order_Type = 'Outbound'
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