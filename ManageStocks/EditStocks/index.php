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

<header class="main-header">
        <nav class="main-nav">
            <a href="../../" class="sgsd-redirect">San Gabriel Softdrinks Delivery</a>
        </nav>
    </header>
    
    <!-- Implement Sidebar -->

</header>
<body class="add-stck">
    <div class="add-stck-title">
        <h1><b>Edit STock</b></h1>
    </div>
    <hr style="margin: 0 auto; width: 85%; padding: 10px;">
    <div class="add-stck-input">
        <h1><b>Product</b> Type</h1>
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

        <h1><b>Product</b> Price</h1>
        <label for="product-quantity">Price</label>
        <input type="number" id="product-quantity" name="product-quantity" required>
        
        <h1><b>Product</b> Stock</h1>
        <div class="incr-dcrn-edit">
        <div class="ceiling-input">
        <label for="product-ceiling">Ceiling</label>
            <input type="number" id="product-ceiling" name="product-ceiling" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>

        <div class="quantity-input">
        <label for="product-quantity">New Stock</label>
            <input type="number" id="product-quantity" name="product-quantity" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>



        <div class="in-stock-input">
        <label for="product-quantity">In Stock</label>
            <input type="number" id="product-quantity" name="product-quantity" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>

        <div class="ceiling-input">
        <label for="product-ceiling">Old Stock</label>
            <input type="number" id="product-ceiling" name="product-ceiling" required>
            <div>
                <button class="quantity-btn decrement-btn" onclick="decrement()">-</button>
                <button class="quantity-btn increment-btn" onclick="increment()">+</button>
            </div>
        </div>
    </div>

    <h1 style="margin-top: 24px;"><b>Product</b> Condition</h1>
    <div class="product-condition">
        <div class="product-date">
            <label for="product-date">Date</label>
            <input type="date" id="product-date" name="product-date" required>
        </div>
        <div class="product-condition-input">
        <label for="product-condition-input">Condition</label>
        <select id="product-condition-input" name="product-condition-input" required>
            <option value="">Select Condition</option>
            <option value="new">New</option>
            <option value="used">Used</option>
            <option value="refurbished">Refurbished</option>
        </select>
        </div>
    </div>
    <div class="button-group">
        <input class="add-stck-cancel-btn" type="submit" value="Cancel">
        <input class="add-stck-add-btn" type="submit" value="Confirm Changes">
    </div>
</body> 
</html>