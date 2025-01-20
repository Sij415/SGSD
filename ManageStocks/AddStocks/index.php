<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<header class="app-header">
    <nav class="app-nav">
        <a href="#" class="back-btn"><</a>
        <a href="#" class="tooltip-btn">X</a>
    </nav>
    
    <!-- Implement Sidebar -->

</header>
<body class="add-stck">
    <div class="add-stck-title">
        <h1><b>Create</b> Products</h1>
    </div>
    <div class="add-stck-input">
        <label for="product-name">Product Name</label>
        <select id="product-name" name="product-name" required>
            <option value="">Select Product Name</option>
            <option value="product1">Product 1</option>
            <option value="product2">Product 2</option>
            <option value="product3">Product 3</option>
        </select>

        <label for="product-unit">Product Unit</label>
        <select id="product-unit" name="product-unit" required>
            <option value="">Select Product Unit</option>
            <option value="unit1">Unit 1</option>
            <option value="unit2">Unit 2</option>
            <option value="unit3">Unit 3</option>
        </select>

        <label for="product-quantity">Price</label>
        <input type="number" id="product-quantity" name="product-quantity" required>
    <div class="incr-dcrn">
        <div class="quantity-input">
        <label for="product-quantity">Quantity</label>
            <input type="number" id="product-quantity" name="product-quantity" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>
        <div class="ceiling-input">
        <label for="product-ceiling">Ceiling</label>
            <input type="number" id="product-ceiling" name="product-ceiling" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>
    </div>
    <div class="product-date">
        <label for="product-date">Date</label>
        <input type="date" id="product-date" name="product-date" required>
    </div>
    <div class="button-group">
        <input class="add-stck-add-btn" type="submit" value="Confirm Products">
    </div>
</body> 
</html>