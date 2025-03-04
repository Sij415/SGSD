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
  <link rel="stylesheet" href="../style/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <title>Manage Product</title>
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
  <a class="closebtn d-md-none" onclick="closeNav()">&times;</a>
  <a href="#" class="sgsd-title"><b>SGSD</b></a>
  
  <div class="sidebar-items">
    <hr style="width: 75%; margin: 0 auto; padding: 12px;">

    <div class="sidebar-item">
      <a href="../Dashboard" class="sidebar-items-a">
        <i class="fa-solid fa-border-all"></i>
        <span>&nbsp;Dashboard</span>
      </a>
    </div>

    <?php if ($user_role !== 'driver'): // Exclude for drivers ?>
    <div class="sidebar-item">
      <a href="../ManageOrders">
        <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
        <span>&nbsp;Manage Orders</span>
      </a>
    </div>
    <?php endif; ?>

    <?php if ($user_role === 'admin'): // Only Admins ?>
    <div class="sidebar-item">
      <a href="../ManageStocks">
        <i class="fa-solid fa-box"></i>
        <span>&nbsp;Manage Stocks</span>
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
    <?php endif; ?>

    <?php if ($user_role === 'staff'): // Staff can access stocks and products ?>
    <div class="sidebar-item">
      <a href="../ManageStocks">
        <i class="fa-solid fa-box"></i>
        <span>&nbsp;Manage Stocks</span>
      </a>
    </div>
    <div class="sidebar-item">
      <a href="../ManageProducts">
        <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
        <span>&nbsp;Manage Product</span>
      </a>
    </div>
    <?php endif; ?>

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
        <h1><b>Manage Products</b></h1>
        <h3>Add and Edit Products</h3>
<h3 class="d-lg-none d-md-block">Click to edit Customer</h3>




        <!-- Search Box -->
        <div class="d-flex align-items-center justify-content-between mb-3">
    <!-- Search Input Group -->
    <div class="input-group">
    <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput" onkeyup="searchTable()">
        <button class="btn btn-outline-secondary" type="button" id="search">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <!-- Add Customer Button -->
    <button class="add-btn ms-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add Customer</button>
    
</div>



        <!-- Table Layout (Visible on larger screens) -->
        <div class="table-responsive  d-none d-md-block">
            <table class="table table-striped table-bordered" id="ProductsTable">
                <thead>
                <tr>
            <th onclick="sortTable(0)">Product Name <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(1)">Product Type <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(2)">Price <i class="bi bi-arrow-down-up"></i></th>
            <th>Edit</th>
            
        </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                <td><?php echo $row['Product_Name']; ?></td>
                <td><?php echo $row['Product_Type']; ?></td>
                <td><?php echo $row['Price']; ?></td>
        





                <td class="text-dark text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                    data-product-id="<?php echo $row['Product_ID']; ?>" 
                            data-product-name="<?php echo $row['Product_Name']; ?>" 
                            data-product-type="<?php echo $row['Product_Type']; ?>"
                            data-price="<?php echo $row['Price']; ?>">
                    
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
                     
                     

                      class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                            data-product-id="<?php echo $row['Product_ID']; ?>" 
                            data-product-name="<?php echo $row['Product_Name']; ?>" 
                            data-product-type="<?php echo $row['Product_Type']; ?>"
                            data-price="<?php echo $row['Price']; ?>"
                            style="cursor: pointer;">
                    

                     

                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['Product_Name']); ?></h5>
                        <div class="row">

                            <div class="col-6">
                                <p class="card-text"><strong>Product Type:</strong> <?php echo htmlspecialchars($row['Product_Type']); ?></p>
                            </div>

                            <div class="col-6">
                                <p class="card-text"><strong>Price:</strong> <?php echo htmlspecialchars($row['Price']); ?></p>
                            </div>

                        
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No Product found.</p>
    <?php endif; ?>
</div>

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




const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    function closeNav() {
      sidebar.classList.remove('active');
    }








    function sortTable(columnIndex) {
    const table = document.getElementById('ProductsTable');
    const rows = Array.from(table.rows).slice(1);
    const isNumeric = !isNaN(rows[0].cells[columnIndex].innerText);

    rows.sort((rowA, rowB) => {
        const cellA = rowA.cells[columnIndex].innerText.toLowerCase();
        const cellB = rowB.cells[columnIndex].innerText.toLowerCase();

        if (isNumeric) {
            return parseFloat(cellA) - parseFloat(cellB);
        } else {
            return cellA.localeCompare(cellB);
        }
    });

    // Re-append sorted rows to the table body
    const tbody = table.getElementsByTagName('tbody')[0];
    rows.forEach(row => tbody.appendChild(row));
}



function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('ProductsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                if (td[j].innerText.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        tr[i].style.display = found ? '' : 'none';
    }
}



































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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>