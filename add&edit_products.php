<?php
// Include the database connection file
include 'dbconnect.php';

// Handle adding product
if (isset($_POST['add_product'])) {
    $product_id = $_POST['Product_ID'];
    $product_name = $_POST['Product_Name'];
    $product_type= $_POST['Product_Type'];
    $price = $_POST['Price'];

    // Proceed with inserting into Product table
    $query = "INSERT INTO Products (Product_ID, Product_Name, Product_Type, Price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $product_id, $product_name, $product_type, $price);

    if ($stmt->execute()) {
        $success_message = "Product added successfully.";
    } else {
        $error_message = "Error adding product: " . $stmt->error;
    }

    $stmt->close();
}

// Handle editing product
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['Product_ID'];
    $new_productname = $_POST['New_ProductName'];
    $new_producttype = $_POST['New_ProductType'];
    $new_price = $_POST['New_Price'];

    $query = "UPDATE Products SET Product_Name = ?, Product_Type = ?, Price = ? WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $new_productname, $new_producttype, $new_price, $product_id);

    if ($stmt->execute()) {
        $success_message = "Product updated successfully.";
    } else {
        $error_message = "Error updating product: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch products
$query = "SELECT * FROM Products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Products</h1>

    <!-- Display success or error message -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"> <?php echo $success_message; ?> </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
    <?php endif; ?>

    <!-- Add Product Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

    <!-- Product Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Product Type</th>
            <th>Price</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['Product_ID']; ?></td>
                <td><?php echo $row['Product_Name']; ?></td>
                <td><?php echo $row['Product_Type']; ?></td>
                <td><?php echo $row['Price']; ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                            data-product-id="<?php echo $row['Product_ID']; ?>" 
                            data-product-name="<?php echo $row['Product_Name']; ?>" 
                            data-product-type="<?php echo $row['Product_Type']; ?>"
                            data-price="<?php echo $row['Price']; ?>">
                        Edit
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product ID</label>
                        <input type="number" class="form-control" id="Product_ID" name="Product_ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="Product_Name" name="Product_Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_type" class="form-label">Product Type</label>
                        <input type="text" class="form-control" id="Product_Type" name="Product_Type" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="Price" name="Price" required>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="edit_product_id" name="Product_ID">
                    <div class="mb-3">
                        <label for="edit_product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit_product_name" name="New_ProductName">
                    </div>
                    <div class="mb-3">
                        <label for="edit_product_type" class="form-label">Product Type</label>
                        <input type="text" class="form-control" id="edit_product_type" name="New_ProductType">
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="edit_price" name="New_Price">
                    </div>
                    <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate edit modal with existing data
    const editStockModal = document.getElementById('editProductModal');
    editStockModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        const productType = button.getAttribute('data-product-type');
        const price = button.getAttribute('data-price');

        document.getElementById('edit_product_id').value = productId;
        document.getElementById('edit_product_name').value = productName;
        document.getElementById('edit_product_type').value = productType;
        document.getElementById('edit_price').value = price;
    });

    // Handle adding a product
    document.getElementById('addProductForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Product added successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to add product: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Handle editing a product
    document.getElementById('editProductForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Product updated successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to update product: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
