<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
</head>

<header class="app-header">
    <nav class="app-nav">
        <a href="#" class="sidebar-btn">≡</a>
        <a href="#" class="tooltip-btn">X</a>
    </nav>
</header>
<body class="mng-stck">
    <div class="container">
        <div class="mng-stck-title text-center my-4">
            <h1><b>Manage</b> Stocks</h1>
            <h3>To view the product in detail, click the product.</h3>
        </div>
        <div class="mng-stck-search text-center mb-4">
            <input type="text" id="search" name="search" class="form-control w-50 mx-auto" placeholder="Search...">
        </div>
        <div class="mng-stck-inv">
            <div class="mng-stck-list">
                <?php
                // Include dbconnect.php to connect to the database
                include '../dbconnect.php';

                // Fetch stocks and product details
                $sql = "
                    SELECT 
                        p.Product_Name, 
                        p.Product_Type, 
                        p.Price, 
                        s.New_Stock, 
                        s.Old_Stock, 
                        s.Threshold 
                    FROM 
                        Stocks s
                    JOIN 
                        Products p ON s.Product_ID = p.Product_ID
                ";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Table layout for larger screens
                    echo '<table class="table table-striped d-none d-md-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Product Type</th>
                                    <th>Price</th>
                                    <th>New Stock</th>
                                    <th>Old Stock</th>
                                    <th>Threshold</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    while ($row = $result->fetch_assoc()) {
                        echo '
                        <tr>
                            <td>' . htmlspecialchars($row['Product_Name']) . '</td>
                            <td>' . htmlspecialchars($row['Product_Type']) . '</td>
                            <td>$' . htmlspecialchars($row['Price']) . '</td>
                            <td>' . htmlspecialchars($row['New_Stock']) . '</td>
                            <td>' . htmlspecialchars($row['Old_Stock']) . '</td>
                            <td>' . htmlspecialchars($row['Threshold']) . '</td>
                        </tr>';
                    }
                    echo '</tbody></table>';

                    // Card layout for mobile and smaller screens
                    echo '<div class="row d-block d-md-none">';
                    
                    // Reset pointer to the result for cards view
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) {
                        echo '
    <div class="col-12 col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($row['Product_Name']) . '</h5>
                <div class="row">
                    <div class="col-6">
                        <p class="card-text">Type: ' . htmlspecialchars($row['Product_Type']) . '</p>
                    </div>
                    <div class="col-6">
                        <p class="card-text">Price: $' . htmlspecialchars($row['Price']) . '</p>
                    </div>
                    <div class="col-6">
                        <p class="card-text">New Stock: ' . htmlspecialchars($row['New_Stock']) . '</p>
                    </div>
                    <div class="col-6">
                        <p class="card-text">Old Stock: ' . htmlspecialchars($row['Old_Stock']) . '</p>
                    </div>
                    <div class="col-6">
                        <p class="card-text">Threshold: ' . htmlspecialchars($row['Threshold']) . '</p>
                    </div>
                </div>
            </div>
        </div>
    </div>';

                    }
                    echo '</div>';
                } else {
                    echo '<p class="text-center">No stocks available.</p>';
                }

                // Close the database connection
                $conn->close();
                ?>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/AddStock" class="btn btn-primary">Add Stock</a>
        </div>
    </div>
</body> 
</html>
