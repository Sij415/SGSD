<?php
// Include database connection
include '../dbconnect.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch order data from the database
$query = "SELECT 
            Orders.Order_ID, 
            Users.User_ID, 
            Customers.First_Name AS Customer_Name, 
            Products.Product_Name, 
            Orders.Status, 
            Orders.Order_Type 
          FROM Orders
          INNER JOIN Users ON Orders.User_ID = Users.User_ID
          INNER JOIN Customers ON Users.User_ID = Customers.User_ID
          INNER JOIN Products ON Orders.Product_ID = Products.Product_ID";

$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container mt-4">
        <h1><b>Manage Orders</b></h1>
        <h3>To view the orders in detail, click the product.</h3>

        <!-- Search Box -->
        <div class="mb-3">
            <input type="text" id="search" name="search" class="form-control" placeholder="Search...">
        </div>

        <!-- Card Layout (Visible on smaller screens) -->
        <div class="row d-block d-md-none">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-12 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['Product_Name']); ?></h5>
                                <div class="row">
                                    <!-- Order ID -->
                                    <div class="col-6">
                                        <p class="card-text"><strong>Order ID:</strong> <?php echo htmlspecialchars($row['Order_ID']); ?></p>
                                    </div>
                                    <!-- User ID -->
                                    <div class="col-6">
                                        <p class="card-text"><strong>User ID:</strong> <?php echo htmlspecialchars($row['User_ID']); ?></p>
                                    </div>
                                    <!-- Customer Name -->
                                    <div class="col-6">
                                        <p class="card-text"><strong>Customer Name:</strong> <?php echo htmlspecialchars($row['Customer_Name']); ?></p>
                                    </div>
                                    <!-- Status -->
                                    <div class="col-6">
                                        <p class="card-text"><strong>Status:</strong> <?php echo htmlspecialchars($row['Status']); ?></p>
                                    </div>
                                    <!-- Order Type -->
                                    <div class="col-6">
                                        <p class="card-text"><strong>Order Type:</strong> <?php echo htmlspecialchars($row['Order_Type']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
