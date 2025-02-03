<?php
// Include database connection

$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
 // Start the session
ini_set('display_errors', 1);



// Fetch user details from session
$user_email = $_SESSION['email'];
// Get the user's first name and email from the database
$query = "SELECT First_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name);
$stmt->fetch();
$stmt->close();


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
  <link rel="stylesheet" href="../style/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <title>Manage Stocks</title>
  <style>
    .table-striped>tbody>tr:nth-child(odd)>td, 
.table-striped>tbody>tr:nth-child(odd)>th {
   background-color: #f4f9f8;
 }
    /* Base styles */
    body {
      margin: 0;
      
      display: flex;
    }

    .sidebar {
  display: flex;
  width: 250px;
  height: 100vh; 
  position: fixed;
  top: 0;
  left: -250px; /* Hidden by default */
  transition: left 0.3s ease;
  z-index: 1000;

 

  overflow-x: hidden;

}


    .sidebar.active {
      left: 0;
    }

    .sidebar .close-btn {
      display: none;
    }

    .content {
      flex-grow: 1;
      margin-left: 0;
      padding-top: 2em;
      padding: 3.5em 1em;
      transition: margin-left 0.3s ease;
    }

    .toggle-btn {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 0.5em 1em;
      cursor: pointer;
    }
    /* Responsive styles */
    @media (min-width: 768px) {
      .sidebar {
        position: relative;
        left: 0;
      }

      .content {
        margin-left: 1rem;/* Push content for medium screens and above */
      }

      .toggle-btn {
        display: none;
      }

      .sidebar .close-btn {
        display: none; /* Hide close button on larger screens */
      }
    }

    @media (max-width: 767.98px) {
      .sidebar .close-btn {
        display: block; /* Show close button only on smaller screens */
        color: #fff;
        background: none;
        border: none;
        font-size: 1.5rem;
        position: absolute;
        top: 10px;
        right: 15px;
      }
    }
  </style>
</head>
<body class="p-0">
<header class="app-header">
  <nav class="app-nav d-flex justify-content-between">
    <!-- Sidebar button visible only on smaller screens -->
    <a class="sidebar-btn d-md-none" id="toggleBtn">☰</a>
    
    <!-- "X" button aligned to the right on larger screens -->
    <a href="#" class="d-md-flex d-md-none ms-auto tooltip-btn"><i class="bi bi-file-earmark-break-fill"></i></a>
    
    <!-- "X" button visible on smaller screens, aligned left -->
  </nav>
</header>

<div id="sidebar" class="sidebar d-flex flex-column">
        <a  class="closebtn d-md-none" onclick="closeNav()">&times;</a>
        <a href="#" class="sangabrielsoftdrinksdeliverytitledonotchangethisclassnamelol"><b>SGSD</b></a>
 
        <div class="sidebar-items">
            <hr style="width: 75%; margin: 0 auto; padding: 12px;">
            <div class="sidebar-item">
                <a href="./Dashboard" class="sidebar-items-a">
                <i class="fa-solid fa-border-all"></i>
                <span>&nbsp;Dashboard</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageStocks">
                    <i class="fa-solid fa-box"></i>
                    <span>&nbsp;Manage Stocks</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageOrders">
                <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Orders</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageProducts">
                <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Product</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../ManageCustomers">
                <i class="bi bi-people-fill" style="font-size:13.28px;"></i>
                <span>&nbsp;Manage Customer</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="../AdminSettings">
                <i class="bi bi-gear" style="font-size:13.28px;"></i>
                <span>&nbsp;Admin Settings</span>
                </a>
            </div>
        </div>
        
        <hr style="width: 75%; margin: 0 auto; padding: 12px ;">
        <div class="mt-auto p-2">
        <div class="sidebar-usr">
            <div class="sidebar-pfp">
                <img src="https://upload.wikimedia.org/wikipedia/en/b/b1/Portrait_placeholder.png" alt="Sample Profile Picture">
            </div>
            <div class="sidebar-usrname">
                <h1><?php
                










                echo htmlspecialchars($user_first_name);
                
                
                
                ?></h1>
                <h2><?php
                echo  htmlspecialchars($user_email)
                
                
                ?></h2>
            </div>
        </div>
        <div class="sidebar-options ">
            <div class="sidebar-item">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </div>
            <div class="sidebar-item d-none d-sm-block">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-file-alt"></i>
                    <span>Manual</span>
                </a>
            </div>
        </div></div>
 
        </div>
  <div class="content">




    <div class="container mt-4">
        <h1><b>Manage Stocks</b></h1>
        <h3>Add and Edit Stocks</h3>
<h3 class="d-lg-none d-md-block">Click to edit Customer</h3>




        <!-- Search Box -->
        <div class="d-flex align-items-center justify-content-between mb-3">
    <!-- Search Input Group -->
    <div class="input-group">
        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="example-search-input">
        <button class="btn btn-outline-secondary" type="button" id="search">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <!-- Add Customer Button -->
    <button class="add-btn ms-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add Customer</button>
    
</div>



        <!-- Table Layout (Visible on larger screens) -->
        <div class="table-responsive  d-none d-md-block">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                <th>Stock ID</th>
            <th>User ID</th>
            <th>Product ID</th>
            <th>Old Stock</th>
            <th>New Stock</th>
            <th>Threshold</th>
            <th>Edit</th>
            
        </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                            <td><?php echo $row['Stock_ID']; ?></td>
                <td><?php echo $row['User_ID']; ?></td>
                <td><?php echo $row['Product_ID']; ?></td>
                <td><?php echo $row['Old_Stock']; ?></td>
                <td><?php echo $row['New_Stock']; ?></td>
                <td><?php echo $row['Threshold']; ?></td>
        





                <td class="text-dark text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#editStocktModal" 
                    data-stock-id="<?php echo $row['Stock_ID']; ?>" 
                            data-new-stock="<?php echo $row['New_Stock']; ?>" 
                            data-threshold="<?php echo $row['Threshold']; ?>">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                </td>

            
                </td>
            </tr>
                                
                                
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
                <div class="card shadow-sm" 
                     
                     

                      class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStockModal" 
                      data-stock-id="<?php echo $row['Stock_ID']; ?>" 
                            data-new-stock="<?php echo $row['New_Stock']; ?>" 
                            data-threshold="<?php echo $row['Threshold']; ?>"
                            style="cursor: pointer;">
                
                    
            
                     

                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['Stock_ID']); ?></h5>
                        <div class="row">

                            <div class="col-6">
                                <p class="card-text"><strong>User ID:</strong> <?php echo htmlspecialchars($row['User_ID']); ?></p>
                            </div>

                            <div class="col-6">
                                <p class="card-text"><strong>Product ID:</strong> <?php echo htmlspecialchars($row['Product_ID']); ?></p>
                            </div>

                            <div class="col-6">
                                <p class="card-text"><strong>Old Stock:</strong> <?php echo htmlspecialchars($row['Old_Stock']); ?></p>
                            </div>
                            
                            <div class="col-6">
                                <p class="card-text"><strong>New Stock:</strong> <?php echo htmlspecialchars($row['New_Stock']); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="card-text"><strong>Threshold:</strong> <?php echo htmlspecialchars($row['Threshold']); ?></p>
                            </div>

                        
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No Stock found.</p>
    <?php endif; ?>
</div>

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




const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    function closeNav() {
      sidebar.classList.remove('active');
    }

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>