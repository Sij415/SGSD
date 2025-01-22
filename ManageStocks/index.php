<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
</head>


    <div class="container-fluid " >
    <div class="row flex-nowrap">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 " style="background-color: #F2F4F0;">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="/" class="d-flex sgsd-redirect align-items-center pb-3 mb-md-0 me-md-auto text-dark text-decoration-none"><img src="../logo.png" class="p-1" alt="Logo" />San Gabriel Softdrinks Delivery</a>
                <header class="main-header">
    </header>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                    <li class="nav-item">
                        <a href="../" class="nav-link align-middle px-0">
                            <i class="fs-4 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#submenu1" data-bs-toggle="collapse" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-speedometer2"></i> <span class="ms-1 d-none d-sm-inline">Management</span> </a>
                        <ul class="collapse nav flex-column ms-1" id="submenu1" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Stock</span> 1 </a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Product</span> 2 </a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Orders</span> 3 </a>
                            </li>
                        
                            
                        </ul>
                    </li>

                    
                    <li>
                        <a href="#" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">Orders</span></a>
                    </li>
                    <li>
                        <a href="#submenu2" data-bs-toggle="collapse" class="nav-link px-0 align-middle ">
                            <i class="fs-4 bi-bootstrap"></i> <span class="ms-1 d-none d-sm-inline">Bootstrap</span></a>
                        <ul class="collapse nav flex-column ms-1" id="submenu2" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span> 1</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span> 2</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#submenu3" data-bs-toggle="collapse" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-grid"></i> <span class="ms-1 d-none d-sm-inline">Products</span> </a>
                            <ul class="collapse nav flex-column ms-1" id="submenu3" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 1</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 2</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 3</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 4</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-people"></i> <span class="ms-1 d-none d-sm-inline">Customers</span> </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle">
                        <span class="d-none d-sm-inline mx-1">loser</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="#">New project...</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col py-3">
        <body class="pt-0">
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
            <a href="./AddStocks" class="login-btn">Add Stock</a>
        </div>
    </div>
</body> 
        </div>
    </div>
</div>

</html>