<?php
// Include database connection
$required_role = 'admin,staff';
include('../check_session.php');
include '../dbconnect.php';
    //Start the session
ini_set('display_errors', 1);

// Fetch logged-in user details
$user_email = $_SESSION['email'];
$query = "SELECT User_ID, First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id, $user_first_name, $user_last_name);
$stmt->fetch();
$stmt->close();

// Handle adding stock
if (isset($_POST['add_stock'])) {
    $user_id = $_POST['User_ID'];
    $product_id = $_POST['Product_ID'];
    $new_stock = $_POST['New_Stock'];
    $threshold = $_POST['Threshold'];

    // Validate user and product existence
    $user_check_query = "SELECT User_ID FROM Users WHERE User_ID = ?";
    $user_stmt = $conn->prepare($user_check_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        $error_message = "Error: Selected user does not exist.";
        $user_stmt->close();
        return;
    }
    $user_stmt->close();

    $product_check_query = "SELECT Product_ID FROM Products WHERE Product_ID = ?";
    $product_stmt = $conn->prepare($product_check_query);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    
    if ($product_result->num_rows === 0) {
        $error_message = "Error: Selected product does not exist.";
        $product_stmt->close();
        return;
    }
    $product_stmt->close();

    // Fetch current stock
    $stock_check_query = "SELECT New_Stock FROM Stocks WHERE Product_ID = ? ORDER BY Stock_ID DESC LIMIT 1";
    $stock_stmt = $conn->prepare($stock_check_query);
    $stock_stmt->bind_param("i", $product_id);
    $stock_stmt->execute();
    $stock_stmt->bind_result($old_stock);
    $stock_stmt->fetch();
    $stock_stmt->close();

    // If no previous stock exists, set old_stock to 0
    if ($old_stock === null) {
        $old_stock = 0;
    }

    // Insert into Stocks table
    $insert_query = "INSERT INTO Stocks (User_ID, Product_ID, Old_Stock, New_Stock, Threshold) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiiii", $user_id, $product_id, $old_stock, $new_stock, $threshold);

    if ($stmt->execute()) {
        $success_message = "Stock added successfully.";
    } else {
        $error_message = "Error adding stock: " . $stmt->error;
    }
    $stmt->close();
}

// Check if stock_id is passed via AJAX
if(isset($_POST['fetch_stock']) && isset($_POST['stock_id'])) {
    $stock_id = $_POST['stock_id'];
    
    // Prepare and execute query to get stock details
    $fetch_query = "SELECT Stock_ID, New_Stock, Threshold FROM Stocks WHERE Stock_ID = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->bind_result($stock_id, $new_stock, $threshold);
    $stmt->fetch();
    $stmt->close();
    
    // Output JSON for AJAX response
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['stock_id' => $stock_id, 'new_stock' => $new_stock, 'threshold' => $threshold]);
        exit;
    }
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

// Fetch stock data for display
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
    <title>SGSD | Manage Stocks</title>
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
    <link rel="icon"  href="../logo.png">
    <link rel="stylesheet" href="../style/styles.css">

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

    // Search in Mobile Cards (if applicable)
    const cards = document.querySelectorAll('.card');
    if (cards.length > 0) {
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(filter) ? '' : 'none';
        });
    }
}

function updateRowColors() {
    document.querySelectorAll("#stocksTable tbody tr").forEach((row) => {
        let newStock = parseInt(row.getAttribute('data-new-stock') || "0");
        let threshold = parseInt(row.getAttribute('data-threshold') || "0");

        // Find the specific new stock column inside the row
        let newStockCell = row.querySelector("data-new-stock");

        if (newStockCell) {
            // Reset previous colors
            newStockCell.classList.remove('table-danger', 'table-orange', 'table-warning');

            // Apply color only to the threshold column
            if (newStock <= threshold) {
                newStockCell.classList.add('table-danger'); // Red for below threshold
            } else if (newStock <= threshold + 10) {
                newStockCell.classList.add('table-orange'); // Custom orange
            } else if (newStock <= threshold + 30) {
                newStockCell.classList.add('table-warning'); // Yellow
            }
        }
    });
}
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

<script>
// Enhanced modal data fetching
$(document).ready(function() {
    $('#editStockModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const stockId = button.data('stock-id');

        // Set the initial values from data attributes for immediate display
        $('#edit_stock_id').val(stockId);
        $('#edit_new_stock').val(button.data('new-stock'));
        $('#edit_threshold').val(button.data('threshold'));

        // Then fetch the latest data from the database
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                fetch_stock: true,
                stock_id: stockId
            },
            dataType: 'json',
            success: function(response) {
                // Update form fields with latest data from database
                $('#edit_new_stock').val(response.new_stock);
                $('#edit_threshold').val(response.threshold);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching stock data:", error);
            }
        });
    });
});
</script>

<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header mt-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand m-0 p-1" href="#">
                <img src="../logo.png" alt="SGSD Logo" width="30" height="30" class="mr-1"> SGSD
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

        <div class="sidebar-spacer"></div>
        <hr class="line">
        <ul class="list-unstyled CTAs pt-0 mb-0 sidebar-bottom">
            <li class="sidebar-username pb-2">
                <div class="align-items-center">
                    <div class="profile-initials rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; border: 1px solid #ccc; background-color: #eee; font-size: 20px;">
                        <?php 
                            echo strtoupper(substr($user_first_name, 0, 1) . substr($user_last_name, 0, 1));
                        ?>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></h1>
                        <h2><?php echo htmlspecialchars($user_email); ?></h2>
                        <h5 style="font-size: 1em; background-color: #6fa062; color: #F2f2f2; font-weight: 700; padding: 8px; border-radius: 8px; width: fit-content;"><?php echo htmlspecialchars($user_role); ?></h5>
                    </div>
                </div>
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
                <button type="button" id="sidebarCollapse" class="btn btn-info ml-1" data-toggle="tooltip" data-placement="bottom" title="Toggle Sidebar">
                    <i class="fas fa-align-left"></i>
                </button>
                <button class="btn btn-dark d-inline-block ml-auto" type="button" id="manualButton" data-toggle="tooltip" data-placement="bottom" title="View Manual">
                    <i class="fas fa-file-alt"></i>
                </button>
            </div>
        </nav>

                <div class="container mt-4">
                <div class="pb-4">
                <i class="fa-solid fa-boxes-stacked" style="font-size:56px;"></i>
                </div>
                <div class="d-flex align-items-center">
                    <h3 style="font-size: 40px; letter-spacing: -0.045em;">
                        <b>Manage Stocks</b>
                    </h3>
                    <i class="bi bi-info-circle pl-2 pb-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Manage stock levels, add new stock, and edit existing stock entries."></i>
                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();
                        });
                    </script>
                </div>
                <h4 class="mb-2" style="color: gray; font-size: 16px;">Add, edit, and manage orders.</h4>
                <div class="alert alert-light d-lg-none d-md-block" role="alert" style="color: gray; background-color: #e8ecef;">
                    <i class="bi bi-info-circle mr-1"></i>
                    Tap card to edit order details.
                </div>
                <!-- Legend for Stock Colors -->
                <ul class="pl-0">
                    <li style="font-size: 1.2em; background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; list-style-type: none; margin-bottom: 10px; border: 2px solid #f5c6cb;">
                        <span>Red</span> = Stock is in threshold/below threshold
                    </li>
                    <li style="font-size: 1.2em; background-color: #ffe0b2; color: #8a6d3b; padding: 10px; border-radius: 5px; list-style-type: none; margin-bottom: 10px; border: 2px solid #ffcc80;">
                        <span>Orange</span> = Stock is +10 of the threshold
                    </li>
                    <li style="font-size: 1.2em; background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; list-style-type: none; margin-bottom: 10px; border: 2px solid #ffeeba;">
                        <span>Yellow</span> = Stock is +30 of the threshold
                    </li>
                </ul>
                <!-- Search Box -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Search Input Group -->
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput"  onkeyup="searchTable()">
                    <button class="btn btn-outline-secondary" type="button" id="search">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <!-- Add Customer Button -->
                <button class="add-btn m-2" data-bs-toggle="modal" data-bs-target="#addStockModal">Add Stock</button>
                </div>

                <!-- Table Layout (Visible on larger screens) -->
                <div style="max-height: 750px; overflow-y: auto; overflow-x: hidden;">      
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

                              // Apply color only to the Threshold column
                            if ($newStock <= $threshold) {
                                $newStockClass = "table-danger"; // Red
                            } elseif ($newStock <= $threshold + 10) {
                                $newStockClass = "bg-orange text-dark"; // Distinct orange
                            } elseif ($newStock <= $threshold + 30) {
                                $newStockClass = "table-warning"; // Yellow
                            }else {
                                $newStockClass = "";
                            }

                        ?>

                            <tr data-new-stock="<?php echo $newStock; ?>" data-threshold="<?php echo $threshold; ?>">
                                <td><?php echo $row['First_Name']; ?></td>
                                <td><?php echo $row['Product_Name']; ?></td>
                                <td><?php echo $row['Old_Stock']; ?></td>
                                <td class="<?php echo $newStockClass; ?>"><?php echo $row['New_Stock']; ?></td>
                                <td class="<?php echo $thresholdClass; ?>"><?php echo $row['Threshold']; ?></td>
                                <td class="text-dark text-center">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editStockModal" 
                                        data-stock-id="<?php echo $row['Stock_ID']; ?>" 
                                        data-new-stock="<?php echo $row['New_Stock']; ?>" 
                                        data-threshold="<?php echo $row['Threshold']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6">No stocks found.</td>
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
                                    <select class="form-control" id="user_id" name="User_ID" style="height: fit-content;" required>
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
                                    <select class="form-control" id="product_id" name="Product_ID" style="height: fit-content;" required>
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
                                    <input type="number" class="form-control" id="Old_Stock" name="Old_Stock" placeholder="Enter old stock quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_stock" class="form-label">New Stock</label>
                                    <input type="number" class="form-control" id="New_Stock" name="New_Stock" placeholder="Enter new stock quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="threshold" class="form-label">Threshold</label>
                                    <input type="number" class="form-control" id="Threshold" name="Threshold" placeholder="Enter threshold quantity" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                                    <button type="submit" name="add_stock" class="btn custom-btn">Add Stock</button>
                                </div>
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
                                <div class="modal-footer">
                                    <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                                    <button type="submit" name="edit_stock" class="btn custom-btn">Save Changes</button>
                                </div>
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

#sidebar {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .sidebar-spacer {
        flex-grow: 1;
    }
    
    .sidebar-bottom {
        margin-top: auto;
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

.custom-btn {
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

.custom-btn:hover {
    transform: scale(1.05);
}

.tooltip-inner {
    color: #000 !important;
    background-color: #ebecec !important;
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
            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }

            .table {
                margin-bottom: 0;
            }

            .table thead th {
                background-color: #f2f4f0;
                color: #444;
                font-weight: 600;
                border-bottom: 2px solid #dee2e6;
                cursor: pointer;
                padding: 1rem;
                letter-spacing: -0.025em;
                position: relative;
                transition: background-color 0.3s;
            }

            .table thead th:hover {
                background-color: #e8ecef;
            }

            .table thead th i {
                font-size: 0.8rem;
                margin-left: 5px;
                opacity: 0.6;
            }

            .table tbody tr {
                transition: background-color 0.2s;
            }

            .table tbody tr:hover {
                background-color: #f8f9fa;
            }

            .table td {
                padding: 0.8rem 1rem;
                vertical-align: middle;
            }

            .table td a {
                color: #6fa062;
                transition: transform 0.3s, color 0.3s;
                display: inline-block;
            }

            .table td a:hover {
                color: #5e8853;
                transform: scale(1.2);
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
    MOBILE STYLES
----------------------------------------------------- */

/* Custom color styles */
.bg-orange {
    background-color: #ffe0b2 !important; /* Light Orange */
    color: #8a6d3b !important; /* Dark Orange Text */
    font-weight: 600;
}

.bg-danger {
    background-color: #f8d7da !important; /* Light Red */
    color: #721c24 !important; /* Dark Red Text */
}

.bg-warning {
    background-color: #fff3cd !important; /* Light Yellow */
    color: #856404 !important; /* Dark Yellow Text */
}

p.card-text {
    color:rgb(59, 59, 59) !important;
}

.table-danger {
    color: #721c24 !important;
    font-weight: 600;
}

.table-warning {
    color: #856404 !important;
    font-weight: 600;
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
