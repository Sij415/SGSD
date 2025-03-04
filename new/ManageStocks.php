<?php
// Include database connection

$required_role = 'admin,staff';
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

    // Fetch current New_Stock before updating
    $query = "SELECT New_Stock FROM Stocks WHERE Stock_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->bind_result($current_stock);
    $stmt->fetch();
    $stmt->close();

    // Update stock: move New_Stock to Old_Stock, then update New_Stock
    $query = "UPDATE Stocks SET Old_Stock = ?, New_Stock = ?, Threshold = ? WHERE Stock_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $current_stock, $new_stock, $threshold, $stock_id);

    if ($stmt->execute()) {
        $success_message = "Stock updated successfully.";
    } else {
        $error_message = "Error updating stock: " . $stmt->error;
    }

    $stmt->close();
}

// UPDATED QUERY
$query = "SELECT Stocks.Stock_ID, 
                 Users.First_Name AS First_Name, 
                 Products.Product_Name, 
                 Stocks.Old_Stock, 
                 Stocks.New_Stock, 
                 Stocks.Threshold 
          FROM Stocks
          INNER JOIN Users ON Stocks.User_ID = Users.User_ID
          INNER JOIN Products ON Stocks.Product_ID = Products.Product_ID"; 

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Sidebar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
</head>
<body>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR SIDEBAR JS
------------------------------------------------------>

<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });

        $('#exitSidebar').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
        });
</script>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR MANAGESTOCKS JS
------------------------------------------------------>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('stocksTable');
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
    const table = document.getElementById('stocksTable');
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

function updateRowColors() {
    document.querySelectorAll("#stockTable tbody tr").forEach((row, index) => {
    let newStock = parseInt(row.getAttribute('data-new-stock') || "0");
    let threshold = parseInt(row.getAttribute('data-threshold') || "0");

    // Reset previous colors
    row.classList.remove('table-danger', 'table-orange', 'table-warning');

    if (newStock <= threshold) {
        row.classList.add('table-danger'); // Red for below threshold
    } else if (newStock <= threshold + 10) {
        row.classList.add('table-orange'); // Custom orange
    } else if (newStock <= threshold + 30) {
        row.classList.add('table-warning'); // Yellow
    }
});

    // Now apply the same logic to mobile cards
    document.querySelectorAll('.card.shadow-sm').forEach((card, index) => {
    let newStock = parseInt(card.getAttribute('data-new-stock') || "0");
    let threshold = parseInt(card.getAttribute('data-threshold') || "0");

    // Reset previous colors
    card.classList.remove('bg-danger', 'bg-orange', 'bg-warning', 'text-white', 'text-dark');

    if (newStock <= threshold) {
        card.classList.add('bg-danger', 'text-white'); // Red
    } else if (newStock <= threshold + 10) {
        card.classList.add('bg-orange', 'text-dark'); // Custom Orange
    } else if (newStock <= threshold + 30) {
        card.classList.add('bg-warning', 'text-dark'); // Yellow
    }
});
}

  /*  let firstCard = true;
    document.querySelectorAll('.card.shadow-sm').forEach(card => {
        if (firstCard) {
            firstCard = false; // Skip first card
            return;
        }

        let newStock = parseInt(card.getAttribute('data-new-stock') || "0");
        let threshold = parseInt(card.getAttribute('data-threshold') || "0");

        card.classList.remove('bg-danger', 'bg-warning', 'bg-opacity-75', 'text-white', 'text-dark'); // Reset colors

        if (newStock <= threshold) {
            card.classList.add('bg-danger', 'text-white'); // Red
        } else if (newStock <= threshold + 10) {
            card.classList.add('bg-warning', 'text-dark'); // Orange
        } else if (newStock <= threshold + 30) {
            card.classList.add('bg-warning', 'bg-opacity-75', 'text-dark'); // Yellow
        }
    }); */

// Run function on page load
document.addEventListener('DOMContentLoaded', updateRowColors);

// Run after adding or editing stock
document.getElementById('addStockForm')?.addEventListener('submit', function () {
    setTimeout(updateRowColors, 1000);
});

document.getElementById('editStockForm')?.addEventListener('submit', function () {
    setTimeout(updateRowColors, 1000);
});

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

<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header mt-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand m-0 p-1" href="#">
                    <i class="fas fa-store mr-1"></i> SGSD
                </a>
                <button type="button" class="btn ml-auto d-md-none d-lg-none rounded-circle mr-1 shadow" id="exitSidebar">
                    <i class="fas fa-times" style="font-size: 13.37px;"></i>
                </button>
            </div>
        </div>

        <hr class="line">

        <ul class="list-unstyled components p-0">
            <li>
                <a href="../Dashboard" class="sidebar-items-a">
                    <i class="fa-solid fa-border-all" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Dashboard</span>
                </a>
            </li>

            <?php if ($user_role !== 'driver') : // Exclude for drivers 
            ?>
                <li>
                    <a href="../ManageOrders">
                        <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Orders</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($user_role === 'admin' || $user_role === 'staff') : // Admin and staff 
            ?>
                <li class="active">
                    <a href="../ManageStocks">
                        <i class="fa-solid fa-box" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Stocks</span>
                    </a>
                </li>
                <li>
                    <a href="../ManageProducts">
                        <i class="fa-solid fa-list" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Product</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($user_role === 'admin') : // Only Admins 
            ?>
                <li>
                    <a href="../ManageCustomers">
                        <i class="bi bi-people-fill" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Customer</span>
                    </a>
                </li>
                <li>
                    <a href="../AdminSettings">
                        <i class="bi bi-gear" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Admin Settings</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <hr class="line">

        <ul class="list-unstyled CTAs">
            <li class="sidebar-username pb-2">
                <h1><?php echo htmlspecialchars($user_first_name); ?></h1>
                <h2><?php echo htmlspecialchars($user_email); ?></h2>
            </li>
            <li>
                <a href="#" class="logout">
                <i class="fa-solid fa-sign-out-alt"></i>
                <span>Log out</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info ml-1">
                    <i class="fas fa-align-left"></i>
                </button>
                <button class="btn btn-dark d-inline-block ml-auto" type="button" id="manualButton">
                    <i class="fas fa-file-alt"></i>
                </button>
            </div>
        </nav>

                <div class="container mt-4">
                <h1><b>Manage Stocks</b></h1>
                <h4 style="color: gray;">Add and Edit Stocks</h4>
                <h6 class="d-lg-none d-md-block" style="color: gray;">Click to edit Customer</h6>
                <!-- Search Box -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Search Input Group -->
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" id="search">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <!-- Add Customer Button -->
                <button class="add-btn m-2" data-bs-toggle="modal" data-bs-target="#addStockModal">Add Stock</button>
                </div>

                <!-- Table Layout (Visible on larger screens) -->
                <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="stocksTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Stocked By <i class="bi bi-arrow-down-up"></i></th>
                        <th onclick="sortTable(1)">Product Name <i class="bi bi-arrow-down-up"></i></th>
                        <th onclick="sortTable(2)">Old Stock <i class="bi bi-arrow-down-up"></i></th>
                        <th onclick="sortTable(3)">New Stock <i class="bi bi-arrow-down-up"></i></th>
                        <th onclick="sortTable(4)">Threshold <i class="bi bi-arrow-down-up"></i></th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                <?php 
                while ($row = mysqli_fetch_assoc($result)): 
                    $newStock = $row['New_Stock'];
                    $threshold = $row['Threshold'];
                
                    if ($newStock <= $threshold) {
                        $rowClass = "table-danger"; // Red
                    } elseif ($newStock <= $threshold + 10) {
                        $rowClass = "bg-orange text-dark"; // Distinct orange
                    } elseif ($newStock <= $threshold + 30) {
                        $rowClass = "table-warning"; // Yellow
                    } else {
                        $rowClass = "";
                    }
                ?>

                <tr class="<?php echo $rowClass; ?>">
                    <td><?php echo $row['First_Name']; ?></td>
                    <td><?php echo $row['Product_Name']; ?></td>
                    <td><?php echo $row['Old_Stock']; ?></td>
                    <td><?php echo $row['New_Stock']; ?></td>
                    <td><?php echo $row['Threshold']; ?></td>

                    <td class="text-dark text-center">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#editStockModal" 
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
                    </>
                </table>
                </div>

                <div class="row d-block d-md-none">
                <?php
                $result->data_seek(0);

                if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $newStock = $row['New_Stock'];
                        $threshold = $row['Threshold'];

                        if ($newStock <= $threshold) {
                            $cardClass = "bg-danger text-white"; // Red (below threshold)
                        } elseif ($newStock <= $threshold + 10) {
                            $cardClass = "bg-orange text-white"; // Custom Orange
                        } elseif ($newStock <= $threshold + 30) {
                            $cardClass = "bg-warning text-dark"; // Yellow
                        } else {
                            $cardClass = "";
                        }
                    ?>  

                    <div class="col-12 col-md-6 mb-3">
                        <div class="card shadow-sm <?php echo $cardClass; ?>" data-bs-toggle="modal" data-bs-target="#editStockModal"
                            data-stock-id="<?php echo $row['Stock_ID']; ?>"
                            data-new-stock="<?php echo $row['New_Stock']; ?>"
                            data-threshold="<?php echo $row['Threshold']; ?>"
                            style="cursor: pointer;">

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['Stock_ID']); ?></h5>
                                <div class="row">

                                    <div class="col-6">
                                        <p class="card-text"><strong>User Name:</strong> <?php echo htmlspecialchars($row['First_Name']); ?></p>
                                    </div>

                                    <div class="col-6">
                                        <p class="card-text"><strong>Product Name:</strong> <?php echo htmlspecialchars($row['Product_Name']); ?></p>
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
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <!-- Stocked By (User Selection) -->
                                    <div class="mb-3">
                                    <label for="user_id" class="form-label">Stocked By</label>
                                    <select class="form-control" id="user_id" name="user_id" required>
                                        <option value="">Select User</option>
                                        <?php
                                        // Fetch users from the Users table
                                        $query = "SELECT User_ID, First_Name FROM Users";
                                        $result = $conn->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['User_ID'] . "'>" . $row['First_Name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Product Name (Product Selection) -->
                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Product Name</label>
                                    <select class="form-control" id="product_id" name="product_id" required>
                                        <option value="">Select Product</option>
                                        <?php
                                        // Fetch products from the Products table
                                        $query = "SELECT Product_ID, Product_Name FROM Products";
                                        $result = $conn->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['Product_ID'] . "'>" . $row['Product_Name'] . "</option>";
                                        }
                                        ?>
                                    </select>
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
            
        </div>
    </div>

<style>

/* ---------------------------------------------------
    NEW BODY STYLE
----------------------------------------------------- */

body {
    font-family: 'Inter', sans-serif !important;
    background: #fafafa;
    letter-spacing: -0.045em;
}

p {
    font-family: 'Inter', sans-serif !important;
    font-size: 1.1em;
    font-weight: 300;
    line-height: 1.7em;
    color: #999;
    letter-spacing: -0.045em;
}

a,
a:hover,
a:focus {
    color: inherit;
    text-decoration: none;
    transition: all 0.3s;
}

.navbar {
    padding: 15px 10px;
    background: #fff;
    border: none;
    border-radius: 0;
    margin-bottom: 40px;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
}

.navbar-btn {
    box-shadow: none;
    outline: none !important;
    border: none;
}

/* ---------------------------------------------------
    SIDEBAR STYLE
----------------------------------------------------- */

.wrapper {
    display: flex;
    width: 100%;
    align-items: stretch;
}

#sidebar {
    min-width: 250px;
    max-width: 250px;
    background: #f2f4f0;
    color: #252525;
    transition: all 0.3s;
    box-shadow: 2px 0 6px rgba(0, 0, 0, 0.25);
}

#sidebar.active {
    margin-left: -250px;
}

#sidebar .sidebar-header {
    padding: 0px 20px;
    background: #f2f4f0;
}

#sidebar ul.components {
    padding: 20px 0;
}

#sidebar ul p {
    color: #252525;
    padding: 10px;
}

#sidebar ul li a {
    padding: 15px 30px;
    font-size: 1.1em;
    display: block;
    transition: transform 0.3s;
}

#sidebar ul li a:hover {
    transform: scale(1.1);
}

#sidebar ul li.active>a,
a[aria-expanded="true"] {
    background: #e8e8e8;
    border-radius: 12px;
}

a[data-toggle="collapse"] {
    position: relative;
}

.dropdown-toggle::after {
    display: block;
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
}

ul ul a {
    font-size: 0.9em !important;
    padding-left: 30px !important;
    background: #6fa062;
}

ul.CTAs {
    padding: 20px;
}

ul.CTAs a {
    text-align: center;
    font-size: 0.9em !important;
    display: block;
    border-radius: 5px;
    margin-bottom: 5px;
}

a.logout {
    border-radius: 12px !important;
    padding: 16px !important;
    background: #6fa062;
    color: #fff;
}

a.logout:hover {
    color: #fff !important;
    transition: background 0.3s, transform 0.3s !important;
    transform: scale(1.02) !important;
}

#manualButton,
#sidebarCollapse {
    background: #6fa062;
    border: none;
    cursor: pointer;
    border-radius: 24px;
    width: 40px;
    height: 40px;
    transition: transform 0.3s;
}

#manualButton:hover,
#sidebarCollapse:hover {
    transform: scale(1.1);
}

#sidebarNavbar,
#mainNavbar {
    border-radius: 36px;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
}

hr.line {
    border-top: 1px solid #8a8d8b;    
    width: 75%;
}

.sidebar-header {
    font-weight: 700;
    letter-spacing: -0.045em;
}

.sidebar-username {
    padding: 12px;
    letter-spacing: -0.045em !important;
    white-space: normal;
    word-wrap: break-word; /* Allow long text to wrap */
    overflow-wrap: break-word; /* Ensure compatibility */
}

.sidebar-username h1 {
    width: 75%;
    font-size: 1.2em !important;
    font-weight: 700;
}

.sidebar-username h2 {
    font-size: 0.8em; 
    color: #797979;
}

/* ---------------------------------------------------
    CONTENT STYLE
----------------------------------------------------- */

#content {
    width: 100%;
    padding: 20px;
    min-height: 100vh;
    transition: all 0.3s;
}

.add-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: #ffffff !important;
    font-size: 12px;
    letter-spacing: -0.050em !important;
    border-radius: 10px;
    padding: 12px;
    border: none;
    white-space: nowrap;
    background-color: #6fa062;
    transition: transform 0.3s;
}

.add-btn:hover {
    transform: scale(1.05);
}


/* ---------------------------------------------------
    MANAGE STOCKS STYLES
----------------------------------------------------- */

            /* main.css - External CSS for SGSD Application */

            /* Modal Styling */
            .modal-content {
                border-radius: 15px;
                border: none;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .modal-header {
                background-color: #f8f9fa;
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
                padding: 20px 25px;
                border-bottom: 1px solid #eaeaea;
            }

            .modal-title {
                font-weight: 600;
                color: #333;
                letter-spacing: -0.045em;
            }

            .modal-body {
                padding: 25px;
            }

            .modal-footer {
                border-top: 1px solid #eaeaea;
                padding: 15px 25px;
            }

            /* Form Controls */
            .form-control {
                border-radius: 10px;
                border: 1px solid #dde0e3;
                padding: 12px 15px;
                font-size: 0.95rem;
                transition: all 0.3s ease;
                box-shadow: none;
            }

            .form-control:focus {
                border-color: #6fa062;
                box-shadow: 0 0 0 0.2rem rgba(111, 160, 98, 0.25);
            }

            .form-label {
                font-weight: 500;
                color: #444;
                margin-bottom: 8px;
            }

            select.form-control {
                background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 0.75rem center;
                background-size: 8px 10px;
                padding-right: 30px;
            }

            /* Table custom styling */
            .table {
                border-radius: 10px;
                overflow: hidden;
                border-collapse: separate;
                border-spacing: 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }

            .table thead th {
                background-color: #f8f9fa;
                border-bottom: 1px solid #eaeaea;
                font-weight: 600;
                color: #444;
                padding: 15px;
            }

            .table-striped tbody tr:nth-of-type(odd) {
                background-color: rgba(0,0,0,0.02);
            }

            /* Custom color styles */
            .bg-orange {
                background-color: #FF9800 !important;
            }

            .text-dark {
                color: #212529 !important;
            }

            /* Card styling */
            .card {
                border-radius: 12px;
                transition: all 0.3s ease;
                overflow: hidden;
            }

            .card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            }

            .card-body {
                padding: 20px;
            }

            .card-title {
                font-weight: 600;
                margin-bottom: 15px;
            }

            /* Additional utility classes */
            .shadow-sm {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            }

            .rounded-pill {
                border-radius: 50rem !important;
            }

            /* Make input fields more distinct on hover */
            input:hover, select:hover {
                border-color: #6fa062;
            }


/* ---------------------------------------------------
    MEDIAQUERIES
----------------------------------------------------- */

@media (max-width: 768px) {
    #sidebar {
        margin-left: -250px;
        position: fixed;
        z-index: 999;
        height: 100%;
    }
    #sidebar.active {
        margin-left: 0;
    }
    #sidebarCollapse span {
        display: none;
    }
}
</style>

