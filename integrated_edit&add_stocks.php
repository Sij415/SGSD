<?php
// Include the database connection file
include 'dbconnect.php';

// Handle adding stock
if (isset($_POST['add_stock'])) {
    $user_id = $_POST['User_ID'];
    $product_id = $_POST['Product_ID'];
    $old_stock = $_POST['Old_Stock'];
    $new_stock = $_POST['New_Stock'];
    $threshold = $_POST['Threshold'];

    // Insert User_ID if it doesn't exist
    $user_check_query = "SELECT User_ID FROM Users WHERE User_ID = ?";
    $user_stmt = $conn->prepare($user_check_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows === 0) {
        $insert_user_query = "INSERT INTO Users (User_ID) VALUES (?)";
        $insert_user_stmt = $conn->prepare($insert_user_query);
        $insert_user_stmt->bind_param("i", $user_id);
        $insert_user_stmt->execute();
        $insert_user_stmt->close();
    }

    $user_stmt->close();

    // Insert Product_ID if it doesn't exist
    $product_check_query = "SELECT Product_ID FROM Products WHERE Product_ID = ?";
    $product_stmt = $conn->prepare($product_check_query);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();

    if ($product_result->num_rows === 0) {
        $insert_product_query = "INSERT INTO Products (Product_ID) VALUES (?)";
        $insert_product_stmt = $conn->prepare($insert_product_query);
        $insert_product_stmt->bind_param("i", $product_id);
        $insert_product_stmt->execute();
        $insert_product_stmt->close();
    }

    $product_stmt->close();

    // Proceed with inserting into Stocks table
    $query = "INSERT INTO Stocks (User_ID, Product_ID, Old_Stock, New_Stock, Threshold) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $user_id, $product_id, $old_stock, $new_stock, $threshold);

    if ($stmt->execute()) {
        $success_message = "Stock added successfully.";
    } else {
        $error_message = "Error adding stock: " . $stmt->error;
    }

    $stmt->close();
}

// Handle editing stock
if (isset($_POST['edit_stock'])) {
    $stock_id = $_POST['Stock_ID'];
    $new_stock = $_POST['New_Stock'];
    $threshold = $_POST['Threshold'];

    $query = "UPDATE Stocks SET New_Stock = ?, Threshold = ? WHERE Stock_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $new_stock, $threshold, $stock_id);

    if ($stmt->execute()) {
        $success_message = "Stock updated successfully.";
    } else {
        $error_message = "Error updating stock: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch stocks
$query = "SELECT * FROM Stocks";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Manage Stocks</h1>

    <!-- Display success or error message -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"> <?php echo $success_message; ?> </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
    <?php endif; ?>

    <!-- Add Stock Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStockModal">Add Stock</button>

    <!-- Stock Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Stock ID</th>
            <th>User ID</th>
            <th>Product ID</th>
            <th>Old Stock</th>
            <th>New Stock</th>
            <th>Threshold</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['Stock_ID']; ?></td>
                <td><?php echo $row['User_ID']; ?></td>
                <td><?php echo $row['Product_ID']; ?></td>
                <td><?php echo $row['Old_Stock']; ?></td>
                <td><?php echo $row['New_Stock']; ?></td>
                <td><?php echo $row['Threshold']; ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStockModal" 
                            data-stock-id="<?php echo $row['Stock_ID']; ?>" 
                            data-new-stock="<?php echo $row['New_Stock']; ?>" 
                            data-threshold="<?php echo $row['Threshold']; ?>">
                        Edit
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User ID</label>
                        <input type="number" class="form-control" id="User_ID" name="User_ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product ID</label>
                        <input type="number" class="form-control" id="Product_ID" name="Product_ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="old_stock" class="form-label">Old Stock</label>
                        <input type="number" class="form-control" id="Old_Stock" name="Old_Stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_stock" class="form-label">New Stock</label>
                        <input type="number" class="form-control" id="New_Stock" name="New_Stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="threshold" class="form-label">Threshold</label>
                        <input type="number" class="form-control" id="Threshold" name="Threshold" required>
                    </div>
                    <button type="submit" name="add_stock" class="btn btn-primary">Add Stock</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Stock Modal -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStockModalLabel">Edit Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="edit_stock_id" name="Stock_ID">
                    <div class="mb-3">
                        <label for="edit_new_stock" class="form-label">New Stock</label>
                        <input type="number" class="form-control" id="edit_new_stock" name="New_Stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_threshold" class="form-label">Threshold</label>
                        <input type="number" class="form-control" id="edit_threshold" name="Threshold" required>
                    </div>
                    <button type="submit" name="edit_stock" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate edit modal with existing data
    const editStockModal = document.getElementById('editStockModal');
    editStockModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const stockId = button.getAttribute('data-stock-id');
        const newStock = button.getAttribute('data-new-stock');
        const threshold = button.getAttribute('data-threshold');

        document.getElementById('edit_stock_id').value = stockId;
        document.getElementById('edit_new_stock').value = newStock;
        document.getElementById('edit_threshold').value = threshold;
    });

    // Handle adding a stock
    document.getElementById('addStockForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Stock added successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to add stock: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Handle editing a stock
    document.getElementById('editStockForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Stock updated successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to update stock: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
