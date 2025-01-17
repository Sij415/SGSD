<?php

session_start();

// Check if the session variable for the user is set
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not set
    header("Location: ../../");
    exit();
}
// Include the database connection
include '../../dbconnect.php';

// Process form data when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['product-name'];
    $productUnit = $_POST['product-unit'];
    $price = $_POST['product-price'];
    $quantity = $_POST['product-quantity'];
    $threshold = $_POST['product-ceiling'];


    // Insert the data into the database (adjust to your table structure)
    $sql = "INSERT INTO Stocks (Product_ID, Product_Unit, Price, Stock_Quantity, Threshold)
            VALUES ('$productName', '$productUnit', '$price', '$quantity', '$threshold')";

    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success' role='alert'>New stock added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Stock</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
</head>

<header class="app-header">
    <nav class="app-nav">
        <a href="#" class="sidebar-btn">≡</a>
        <a href="#" class="tooltip-btn">X</a>
    </nav>
</header>

<body class="add-stck">
    <div class="container">
        <div class="add-stck-title text-center my-4">
            <h1><b>Create</b> Product Stock</h1>
            <h3>Fill in the form to add a new stock.</h3>
        </div>

        <!-- Form for adding stock -->
        <form action="add_stock.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="product-name">Product Name</label>
                <select id="product-name" name="product-name" class="form-control mb-3" required>
                    <option value="">Select Product Name</option>
                    <?php
                    // Fetch available products from the Products table
                    $sql = "SELECT Product_ID, Product_Name FROM Products";
                    $result = $conn->query($sql);

                    // Populate product names dropdown
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['Product_ID']) . '">' . htmlspecialchars($row['Product_Name']) . '</option>';
                        }
                    } else {
                        echo '<option value="">No products available</option>';
                    }
                    ?>
                </select>

                <label for="product-unit">Product Unit</label>
                <select id="product-unit" name="product-unit" class="form-control mb-3" required>
                    <option value="">Select Product Unit</option>
                    <option value="pcs">Per Pcs</option>
                    <option value="case">Per Case</option>
                </select>

                <label for="product-price">Price</label>
                <input type="number" id="product-price" name="product-price" class="form-control mb-3" required min="0">

                <div class="quantity-section d-flex justify-content-between">
                    <div class="quantity-input">
                        <label for="product-quantity">Quantity</label>
                        <div class="input-group mb-3">
                            <button class="btn btn-outline-secondary" type="button" id="minus-qty" onclick="decrementQuantity()">-</button>
                            <input type="number" id="product-quantity" name="product-quantity" class="form-control" value="0" required min="0">
                            <button class="btn btn-outline-secondary" type="button" id="plus-qty" onclick="incrementQuantity()">+</button>
                        </div>
                    </div>

                    <div class="ceiling-input">
                        <label for="product-threshold">Threshold</label>
                        <div class="input-group mb-3">
                            <button class="btn btn-outline-secondary" type="button" id="minus-ceiling" onclick="decrementCeiling()">-</button>
                            <input type="number" id="product-threshold" name="product-threshold" class="form-control" value="0" required min="0">
                            <button class="btn btn-outline-secondary" type="button" id="plus-ceiling" onclick="incrementCeiling()">+</button>
                        </div>
                    </div>
                </div>



                <div class="text-center mt-4">
                    <input type="submit" value="Confirm Product Stock" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript for quantity and ceiling increment/decrement -->
    <script>
        function incrementQuantity() {
            var quantityInput = document.getElementById('product-quantity');
            var currentValue = parseInt(quantityInput.value) || 0;
            quantityInput.value = currentValue + 1;
        }

        function decrementQuantity() {
            var quantityInput = document.getElementById('product-quantity');
            var currentValue = parseInt(quantityInput.value) || 0;
            if (currentValue > 0) {
                quantityInput.value = currentValue - 1;
            }
        }

        function incrementCeiling() {
            var ceilingInput = document.getElementById('product-threshold');
            var currentValue = parseInt(ceilingInput.value) || 0;
            ceilingInput.value = currentValue + 1;
        }

        function decrementCeiling() {
            var ceilingInput = document.getElementById('product-threshold');
            var currentValue = parseInt(ceilingInput.value) || 0;
            if (currentValue > 0) {
                ceilingInput.value = currentValue - 1;
            }
        }

        function validateForm() {
            var price = document.getElementById('product-price').value;
            var quantity = document.getElementById('product-quantity').value;
            var ceiling = document.getElementById('product-threshold').value;

            if (price < 0 || quantity < 0 || ceiling < 0) {
                alert("Values for Price, Quantity, and Ceiling cannot be negative.");
                return false;
            }

            return true;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body> 
</html>
