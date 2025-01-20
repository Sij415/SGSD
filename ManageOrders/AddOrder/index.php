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
<body class="add-ordr">
    <div class="add-ordr-title">
        <h1><b>Create</b> Order</h1>
    </div>

    <div class="add-ordr-input">
        <label for="customer-name">Customer Name</label>
        <input for="customer-name" name="Juan Dela Cruz" required>
    </div>

    <div class="add-ordr-product">
        <div class="add-ordr-input">
            <label for="customer-name">Product 1</label>
            <input for="customer-name" name="Juan Dela Cruz" required>
        </div>
        <div class="add-more-ordr-product">
            <i class="fas fa-plus"></i>
        </div>
    </div>
    <div class="add-ordr-input">
        <div class="incr-dcrn">
            <div class="quantity-input">
            <label for="product-quantity">Quantity</label>
                <input type="number" id="product-quantity" name="product-quantity" required>
            </div>
        <div class="ceiling-input">
        <label for="product-ceiling">Unit</label>
            <input type="number" id="product-ceiling" name="product-ceiling" required>
        </div>
    </div>
    <hr style="padding: 12px;">
    <div class="product-ordr">
        <div class="product-date">
            <label for="product-date">Date</label>
            <input type="date" id="product-date" name="product-date" required>
        </div>
        <div class="product-status-input">
        <label for="product-status-input">Condition</label>
        <select id="product-status-input" name="product-status-input" required placeholder="Select product status">
            <option value="new">Pending</option>
            <option value="used">iforgotlmaotinatamadakomagsearchsalogistics</option>
            <option value="refurbished">eto din haha</option>
        </select>
        </div>
    </div>
    <hr style="padding: 12px;">
    <div class="product-notes">
        <div class="add-ordr-input-notes">
            <label for="additional-notes">Additional Notes</label>
            <textarea id="additional-notes" name="additional-notes"></textarea>
        </div>
        <div class="button-group">
        <input class="add-stck-add-btn" type="submit" value="Confirm Order">
        </div>
    </div>

</body> 
</html>