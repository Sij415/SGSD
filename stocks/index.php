<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">

        <div class="header">
            <h1>Manage Stocks</h1>
        </div>
        <p class="text-secondary">To view the product in detail, press the product.</p>

        
        <div class="search-bar mb-4">
            <input type="text" placeholder="Search..." />
            <i class="bi bi-filter"></i>
        </div>

        <div class="container py-4 productbg">
        <?php
        $products = [
            ["Coca Cola", 35, 23, 12, "Single Bottle", "C"],
            ["Sprite", 12, 12, 0, "Kasalo", "S"],
            ["Royal", 0, 0, 0, "Case", "R"],
        ];
        foreach ($products as $product) {
            echo "
            <div class='stock-card'>
                <div class='stock-icon'>{$product[5]}</div>
                <div class='stock-details'>
                    <h5>{$product[0]}</h5>
                    <span>{$product[4]}</span>
                    <div class='stock-stats'>
                        <span class='badge total'>Total Stock: {$product[1]}</span>
                        <span class='badge old'>Old Stock: {$product[2]}</span>
                        <span class='badge new'>New Stock: {$product[3]}</span>
                    </div>
                </div>
            </div>
            ";
        }
        ?>

        </div>
        <div class="text-center mt-4">
            <button class="btn btn-add">Add Products</button>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
