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
$query = "SELECT First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name, $user_last_name);
$stmt->fetch();
$stmt->close();


// Handle adding product
if (isset($_POST['add_product'])) {
    $product_name = $_POST['Product_Name'];
    $product_type = $_POST['Product_Type'];
    $price = $_POST['Price'];
    $unit = $_POST['Unit'];

    // Proceed with inserting into Product table
    $query = "INSERT INTO Products (Product_Name, Product_Type, Price, Unit) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssds", $product_name, $product_type, $price, $unit);

    if ($stmt->execute()) {
        $success_message = "Product added successfully.";
    } else {
        $error_message = "Error adding product: " . $stmt->error;
    }

    $stmt->close();
}

// Handle editing product
if (isset($_POST['edit_product'])) {
    $new_productname = $_POST['New_ProductName'];
    $new_producttype = $_POST['New_ProductType'];
    $new_price = $_POST['New_Price'];
    $new_unit = $_POST['New_Unit'];
    $product_id = $_POST['Product_ID'];

    $query = "UPDATE Products SET Product_Name = ?, Product_Type = ?, Price = ?, Unit = ? WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsi", $new_productname, $new_producttype, $new_price, $new_unit, $product_id);

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../Login"); // Redirect to login page
    exit();
}


















// Handle deleting products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_products'])) {
    $product_ids = json_decode($_POST['product_ids']);

    foreach ($product_ids as $product_id) {
        // Delete the product
        $query = "DELETE FROM Products WHERE Product_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD | Manage Products</title>
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
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR SIDEBAR JS
------------------------------------------------------>

<script>
   $(document).ready(function () {
    // Populate edit modal with existing data
    $('#editProductModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var productId = button.data('product-id'); // Extract info from data-* attributes
        var productName = button.data('product-name');
        var productType = button.data('product-type');
        var price = button.data('price');
        var unit = button.data('unit'); // Add this line

        var modal = $(this);
        modal.find('#edit_product_id').val(productId);
        modal.find('#edit_product_name').val(productName);
        modal.find('#edit_product_type').val(productType);
        modal.find('#edit_price').val(price);
        modal.find('#edit_unit').val(unit); // Add this line
    });
});

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    function closeNav() {
        sidebar.classList.remove('active');
    }

    function sortTable(columnIndex) {
    var table = document.getElementById("ProductsTable");
    var rows = Array.from(table.rows).slice(1); // Exclude header
    var switching = true, dir = "asc", switchcount = 0;
    
    // Check current sorting direction
    var header = table.rows[0].cells[columnIndex];
    if (header.getAttribute("data-sort") === "asc") {
        dir = "desc";
        header.setAttribute("data-sort", "desc");
    } else {
        dir = "asc";
        header.setAttribute("data-sort", "asc");
    }
    
    // Sorting function
    rows.sort(function (rowA, rowB) {
        var x = rowA.cells[columnIndex].innerText.trim();
        var y = rowB.cells[columnIndex].innerText.trim();
        
        // Convert to numbers if applicable
        var xNum = parseFloat(x), yNum = parseFloat(y);
        if (!isNaN(xNum) && !isNaN(yNum)) {
            return dir === "asc" ? xNum - yNum : yNum - xNum;
        }
        return dir === "asc" ? x.localeCompare(y) : y.localeCompare(x);
    });
    
    // Append sorted rows back to table
    rows.forEach(row => table.appendChild(row));
    
    // Update sort icons
    document.querySelectorAll("th i").forEach(icon => icon.className = "bi bi-arrow-down-up");
    header.querySelector("i").className = dir === "asc" ? "bi bi-arrow-up" : "bi bi-arrow-down";
}

    function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('ProductsTable');
    const tr = table.getElementsByTagName('tr');
    let foundAny = false; // Track if any match is found

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < td.length; j++) {
            if (td[j] && td[j].innerText.toLowerCase().indexOf(filter) > -1) {
                found = true;
                foundAny = true;
                break;
            }
        }
        tr[i].style.display = found ? "" : "none"; // Hide non-matching rows
    }

    // Optional: Show a "No results found" message
    const noResults = document.getElementById('noResultsMessage');
    if (noResults) {
        noResults.style.display = foundAny ? "none" : "block";
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

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR DELETE ENTRY FUNCTION JS
------------------------------------------------------>

<script>
    $(document).ready(function() {
        // Initialize selection mode variables
        let selectionMode = false;
        let selectedItems = [];

        // Add checkbox column to table header
        $("#ProductsTable thead tr").prepend('<th class="checkbox-column"><input type="checkbox" id="select-all"></th>');

        // Add checkboxes to all rows
        $("#ProductsTable tbody tr").prepend(function() {
            var productId = $(this).data("product-id");
            return '<td class="checkbox-column"><input type="checkbox" class="row-checkbox" value="' + productId + '"></td>';
        });

        // Toggle selection mode
        $("#toggle-selection-mode").click(function() {
            if (selectedItems.length > 0) {
                // If items are selected, open delete modal directly
                $("#deleteConfirmModal").modal("show");
            } else {
                // Toggle selection mode as before
                selectionMode = !selectionMode;
                if (selectionMode) {
                    $(this).addClass("active");
                } else {
                    $(this).removeClass("active");
                    // Clear all checkboxes
                    $(".row-checkbox").prop("checked", false);
                    $("#select-all").prop("checked", false);
                    selectedItems = [];
                    updateSelectedCount();
                }
            }
        });

        // Individual card selection
        $(document).on("click", ".card", function() {
            const card = $(this)[0];

            if (!selectedItems.includes(card)) {
            // Add this card element to our selections if not already included
            selectedItems.push(card);
            $(this).addClass("selected"); // Optional: Add a class to indicate selection
            } else {
            // Remove this card from selections
            selectedItems = selectedItems.filter(item => item !== card);
            $(this).removeClass("selected"); // Optional: Remove the class indicating selection
            }

            updateSelectedCount();
        });

        // Select all checkboxes
        $("#select-all").change(function() {
            let isChecked = $(this).is(":checked");
            $(".row-checkbox").prop("checked", isChecked);

            // Update selected items
            selectedItems = [];
            if (isChecked) {
                // Simply gather all row elements that have checkboxes
                $(".row-checkbox").each(function() {
                    selectedItems.push($(this).closest("tr")[0]);
                });
            }
            updateSelectedCount();
        });

        // Individual checkbox selection
        $(document).on("change", ".row-checkbox", function() {
            const row = $(this).closest("tr")[0];

            if ($(this).is(":checked")) {
                // Add this row element to our selections if not already included
                if (!selectedItems.includes(row)) {
                    selectedItems.push(row);
                }
            } else {
                // Remove this row from selections
                selectedItems = selectedItems.filter(item => item !== row);
                $("#select-all").prop("checked", false);
            }

            updateSelectedCount();
        });

        // Update the selected count display
        function updateSelectedCount() {
            const count = selectedItems.length;
            $("#selected-count").text(count + " selected");
            $("#delete-count").text(count);
            
            // Show/hide floating dialog based on selection
            if (count > 0 && $(window).width() >= 768) {
                $("#selection-controls").fadeIn(300);
            } else {
                $("#selection-controls").fadeOut(300);
            }
        }

        // Handle delete confirmation
        $("#delete-confirmed").click(function() {
            const productIds = selectedItems.map(row => $(row).find(".row-checkbox").val());
            console.log("Selected Product IDs: ", productIds); // Debug log to check product IDs
            $("#product_ids").val(JSON.stringify(productIds));
            $("#deleteForm").submit();
        });

        // Connect delete button in floating dialog to delete modal
        $("#delete-selected-btn").click(function() {
            $("#deleteConfirmModal").modal("show");
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
                <li>
                    <a href="../ManageStocks">
                        <i class="fa-solid fa-box" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Stocks</span>
                    </a>
                </li>
                <li class="active">
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
            <a href="#" class="logout" onclick="document.getElementById('logoutForm').submit();">
    <i class="fa-solid fa-sign-out-alt"></i>
    <span>Log out</span>
</a>
<form id="logoutForm" method="POST" action="">
    <input type="hidden" name="logout" value="1">
</form>
            </li>
        </ul>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info ml-1" data-toggle="tooltip" data-placement="bottom" title="Toggle Sidebar">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <a href="../Manual/Manual-Placeholder.pdf" class="btn btn-dark ml-2 d-flex justify-content-center align-items-center" id="manualButton" data-toggle="tooltip" data-placement="bottom" target="_blank" title="View Manual">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
            </nav>


        <div class="container mt-4">
            <div class="pb-4">
            <i class="fa-solid fa-box-open" style="font-size:56px;"></i>
            </div>
            <div class="d-flex align-items-center">
                <h1 style="letter-spacing: -0.045em;">
                    <b>Manage Products</b>
                </h1>
                <i class="bi bi-info-circle pl-2 pb-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Add and edit products to manage your inventory effectively."></i>
                <script>
                    $(document).ready(function(){
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                </script>
            </div>
            <h4 class="mb-2" style="color: gray; font-size: 16px;">Add, edit, and manage products in your inventory.</h4>
            <div class="alert alert-light d-lg-none d-md-block" role="alert" style="color: gray; background-color: #e8ecef;">
                <i class="bi bi-info-circle mr-1"></i>
                Tap card to edit product.
            </div>
            <!-- Search Box -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Search Input Group -->
                <div class="input-group m-0" style="width: 100%;">
                    <div class="search-container">
                        <input type="search" class="form-control search-input-main" placeholder="Search" aria-label="Search" id="searchInput" onkeyup="searchTable()">
                        <button class="btn btn-outline-secondary search-btn-main" type="button" id="search">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>

                    <!-- Mobile search that will only show below 476px -->
                    <div class="mobile-search-container d-none">
                        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="mobileSearchInput" onkeyup="searchTable()">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <?php if ($user_role === 'admin' || $user_role === 'staff') : ?>
                    <!-- Add Product Button -->
                    <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addProductModal" style="width: auto;">Add Product</button>
                <?php endif; ?>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete <span id="delete-count">0</span> selected product(s)?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">No, Cancel</button>
                                <button type="button" class="btn custom-btn" id="delete-confirmed">Yes, Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="selection-controls" class="delete-selection-floating" style="display: none;">
                <div class="floating-dialog">
                    <span id="selected-count">0 selected</span>
                    <?php if ($user_role === 'admin' || $user_role === 'staff') : ?>
                    <button id="delete-selected-btn" class="btn btn-danger btn-sm" style="border-radius: 32px;">Delete Selected</button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Hidden Form for Deletion -->
<form id="deleteForm" method="POST" action="" style="display:none;">
    <input type="hidden" name="delete_products" value="1">
    <input type="hidden" name="product_ids" id="product_ids">
</form>
            <script>
                // Connect delete buttons to delete modal
                $(document).ready(function() {
                    $("#delete-selected-btn, #delete-selected-btn-edit").click(function() {
                        $("#deleteConfirmModal").modal("show");
                    });
                });
            </script>
            <!-- Table Layout (Visible on larger screens) -->
            <div style="max-height: 750px; overflow-y: auto; overflow-x: hidden;">      
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="ProductsTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(1)">Product Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(2)">Product Type <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(3)">Unit <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(4)">Price <i class="bi bi-arrow-down-up"></i></th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr data-product-id="<?php echo htmlspecialchars($row['Product_ID']); ?>">
                                    <td><?php echo $row['Product_Name']; ?></td>
                                    <td><?php echo $row['Product_Type']; ?></td>
                                    <td><?php echo $row['Unit']; ?></td>
                                    <td>₱<?php echo $row['Price']; ?></td>
                                    <td class="text-dark text-center">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                        data-product-id="<?php echo $row['Product_ID']; ?>" 
                                        data-product-name="<?php echo $row['Product_Name']; ?>" 
                                        data-product-type="<?php echo $row['Product_Type']; ?>" 
                                        data-price="<?php echo $row['Price']; ?>"
                                        data-unit="<?php echo $row['Unit']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Layout -->
            <div class="row d-block d-md-none rounded">
                <?php
                $result->data_seek(0);
                if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body rounded" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                data-product-id="<?php echo $row['Product_ID']; ?>" 
                                data-product-name="<?php echo $row['Product_Name']; ?>" 
                                data-product-type="<?php echo $row['Product_Type']; ?>" 
                                data-price="<?php echo $row['Price']; ?>" style="cursor: pointer;">
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
                <?php else : ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
            <p id="noResultsMessage" style="display: none; text-align: center; font-weight:bold; margin-top: 10px;">No Product found.</p>
        </div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="Product_Name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="Product_Name" name="Product_Name" placeholder="Enter Product Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="Product_Type" class="form-label">Product Type</label>
                        <input type="text" class="form-control" id="Product_Type" name="Product_Type" placeholder="Enter Product Type" required>
                    </div>
                    <div class="mb-3">
                        <label for="Price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="Price" name="Price" placeholder="Enter Price" required>
                    </div>
                    <div class="mb-3">
                        <label for="Unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="Unit" name="Unit" placeholder="Enter Unit" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                        <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none" style="background-color: #dc3545 !important; color: #fff !important;">Delete</button>
                        <button type="submit" name="add_product" class="btn custom-btn">Add Product</button>
                    </div>
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
                    <div class="mb-3">
                        <label for="edit_unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="edit_unit" name="New_Unit">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                        <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none" style="background-color: #dc3545 !important; color: #fff !important;">Delete</button>
                        <button type="submit" name="edit_product" class="btn custom-btn">Save Changes</button>
                    </div>
                </form>
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
    MODAL STYLE
----------------------------------------------------- */

   /* Modal styling */
   #addProductModal .modal-content,
    #editProductModal .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    #addProductModal .modal-header,
    #editProductModal .modal-header {
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa;
        border-radius: 15px 15px 0 0;
        padding: 15px 20px;
    }

    #addProductModal .modal-title,
    #editProductModal .modal-title {
        font-weight: 700;
        letter-spacing: -0.045em;
        color: #333;
    }

    #addProductModal .btn-close,
    #editProductModal .btn-close {
        background-color: #e8ecef;
        border-radius: 50%;
        transition: transform 0.3s;
    }

    #addProductModal .btn-close:hover,
    #editProductModal .btn-close:hover {
        transform: scale(1.1);
    }

    #addProductModal .modal-body,
    #editProductModal .modal-body {
        padding: 20px;
    }

    #addProductModal .form-label,
    #editProductModal .form-label {
        font-weight: 600;
        letter-spacing: -0.045em;
        color: #444;
    }

    #addProductModal .form-control,
    #editProductModal .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    #addProductModal .form-control:focus,
    #editProductModal .form-control:focus {
        border-color: #6fa062;
        box-shadow: 0 0 0 3px rgba(111, 160, 98, 0.25);
    }

    #addProductModal button[type="submit"],
    #editProductModal button[type="submit"] {
        background-color: #6fa062;
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
        letter-spacing: -0.050em;
        color: white;
        transition: transform 0.3s, background-color 0.3s;
    }

    #addProductModal button[type="submit"]:hover,
    #editProductModal button[type="submit"]:hover {
        background-color: #5e8853;
        transform: scale(1.05);
    }

    @media (max-width: 576px) {
        #addProductModal .modal-dialog,
        #editProductModal .modal-dialog {
        margin: 1rem;
        }
    }

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