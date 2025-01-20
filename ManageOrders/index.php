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
          INNER JOIN Customers ON Users.User_ID = Customers.Customer_ID
          INNER JOIN Products ON Orders.Product_ID = Products.Product_ID";

$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_name = $_POST['customer_name'];
    $product_name = $_POST['product_name'];
    $status = $_POST['status'];
    $order_type = $_POST['order_type'];

    // Validate input
    if (!empty($customer_name) && !empty($product_name) && !empty($status) && !empty($order_type)) {
        $query = "INSERT INTO Orders (Customer_Name, Product_Name, Status, Order_Type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $customer_name, $product_name, $status, $order_type);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']); // Reload page to show updated data
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error adding order: " . $conn->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
    }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<header class="main-header">
        <nav class="main-nav">
            <a href="../../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
        </nav>
    </header>
<body>
    <!-- Add Order Button -->
<div class="mb-4">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">Add Order</button>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product Name</label>
                        <input type="text" name="product_name" id="product_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" id="status" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="order_type" class="form-label">Order Type</label>
                        <input type="text" name="order_type" id="order_type" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_order" class="btn btn-primary">Add Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="container mt-4">
        <h1><b>Manage Orders</b></h1>
        <h3>To view the orders in detail, click the product.</h3>

        <!-- Search Box -->
        <div class="mb-3">
            <input type="text" id="search" name="search" class="form-control" placeholder="Search...">
        </div>

        <!-- Table Layout (Visible on larger screens) -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Status</th>
                        <th>Order Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Order_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['User_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['Customer_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                <td><?php echo htmlspecialchars($row['Order_Type']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="row d-block d-md-none">
            <?php
            $result->data_seek(0);
            
            if (mysqli_num_rows($result) > 0): ?>
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
</body>
</html>
