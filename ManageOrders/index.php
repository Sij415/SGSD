<?php
// Include database connection
$required_role = 'admin,staff,driver';
include('../check_session.php');
include '../dbconnect.php';
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's details from the database
$query = "SELECT First_Name, Last_Name, User_ID, Role FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_first_name, $user_last_name, $user_id, $user_role);
$stmt->fetch();
$stmt->close();

// Fetch order data from the database
$query = "SELECT 
            Orders.Order_ID, 
            CONCAT(Users.First_Name, ' ', Users.Last_Name) AS Full_Name, 
            Customers.First_Name AS Customer_FName, 
            Customers.Last_Name AS Customer_LName,
            Products.Product_Name, 
            Orders.Status, 
            Orders.Order_Type,
            Orders.Quantity,
            Orders.Total_Price
          FROM Orders
          INNER JOIN Users ON Orders.User_ID = Users.User_ID
          INNER JOIN Products ON Orders.Product_ID = Products.Product_ID
          INNER JOIN Transactions ON Orders.Order_ID = Transactions.Order_ID
          LEFT JOIN Customers ON Transactions.Customer_ID = Customers.Customer_ID";


$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Check for query errors
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch product list for dropdown
$product_query = "SELECT Product_ID, Product_Name FROM Products";
$product_result = $conn->query($product_query);
$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}

// Fetch customer list for dropdown
$customer_query = "SELECT Customer_ID, First_Name, Last_Name FROM Customers";
$customer_result = $conn->query($customer_query);
$customers = [];
while ($row = $customer_result->fetch_assoc()) {
    $customers[] = $row;
}

// Handle adding a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_fname = $_POST['Customer_FName'];
    $customer_lname = $_POST['Customer_LName'];
    $product_name = $_POST['Product_Name']; // Get product name from form
    $status = $_POST['Status'];
    $order_type = $_POST['Order_Type'];
    $quantity = $_POST['Quantity'];

        // Check if customer exists
        $query = "SELECT Customer_ID FROM Customers WHERE First_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $customer_name);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        // If customer does not exist, insert into Customers table
        if (!$customer_id) {
            $query = "INSERT INTO Customers (First_Name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $customer_name);
            $stmt->execute();
            $customer_id = $stmt->insert_id; // Get the new Customer_ID
            $stmt->close();
        }

        if (!$customer_id) {
            echo "<div class='alert alert-danger'>Customer not found. Please register the customer first.</div>";
            exit();
        }

        // Fetch Product_ID from Products table
        $query = "SELECT Product_ID, Price FROM Products WHERE Product_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->bind_result($product_id, $price);
        $stmt->fetch();
        $stmt->close();

        if (!$product_id) {
            echo "<div class='alert alert-danger'>Product not found.</div>";
            exit();
        }

        $total_price = $price * $quantity;

        // Always create a new transaction for each order
        $query = "INSERT INTO Transactions (Customer_ID, Date, Time) VALUES (?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $transaction_id = $stmt->insert_id; // Get the new Transaction_ID
        $stmt->close();

        // Insert order with the retrieved Transaction_ID
        $query = "INSERT INTO Orders (User_ID, Product_ID, Status, Order_Type, Quantity, Total_Price, Transaction_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissidi", $user_id, $product_id, $status, $order_type, $quantity, $total_price, $transaction_id);
        $stmt->execute();
        $order_id = $stmt->insert_id; // Get the newly inserted Order_ID
        $stmt->close();

        // Update Transactions table with the Order_ID
        $query = "UPDATE Transactions SET Order_ID = ? WHERE Transaction_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $order_id, $transaction_id);
        $stmt->execute();
        $stmt->close();

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']); // Reload page to show new data
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error adding order: " . $conn->error . "</div>";
        }

        $stmt->close();

}

// Handle editing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    $order_id = $_POST['Order_ID'];
    $status = $_POST['New_Status'];

    if ($user_role === 'driver') {
        // Driver can only update status
        $query = "UPDATE Orders SET Status = ? WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
    } else {
        // Admin and Staff can update all fields
        $customer_fname = $_POST['New_CustomerFName'];
        $customer_lname = $_POST['New_CustomerLName'];
        $product_name = $_POST['New_ProductName'];
        $order_type = $_POST['New_OrderType'];
        $quantity = $_POST['New_Quantity'];

        // Get Customer_ID
        $query = "SELECT Customer_ID FROM Customers WHERE First_Name = ? AND Last_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $customer_fname, $customer_lname);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        if (!$customer_id) {
            echo "<div class='alert alert-danger'>Customer not found.</div>";
            exit();
        }

        // Get Product_ID and Price
        $query = "SELECT Product_ID, Price FROM Products WHERE Product_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->bind_result($product_id, $price);
        $stmt->fetch();
        $stmt->close();

        if (!$product_id) {
            echo "<div class='alert alert-danger'>Product not found.</div>";
            exit();
        }

        // Calculate updated Total Price
        $total_price = $price * $quantity;

        // Ensure Transaction_ID is fetched
        $query = "SELECT Transaction_ID FROM Orders WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($transaction_id);
        $stmt->fetch();
        $stmt->close();

        if (!$transaction_id) {
            echo "<div class='alert alert-danger'>Transaction not found.</div>";
            exit();
        }

        // Update Orders table for admin/staff
        $query = "UPDATE Orders SET Product_ID = ?, Status = ?, Order_Type = ?, Quantity = ?, Total_Price = ?, Transaction_ID = ? WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issidii", $product_id, $status, $order_type, $quantity, $total_price, $transaction_id, $order_id);
    }

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']); // Reload page to show updated data
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating order: " . $conn->error . "</div>";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD | Manage Orders</title>
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
    <link rel="icon" href="../logo.png">
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
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR MANAGEORDERS JS
------------------------------------------------------>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        // Sidebar toggle functionality (if needed here)

        // Function to sort table
        function sortTable(columnIndex) {
            const table = document.getElementById('OrdersTable');
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

        function searchTables() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('OrdersTable');
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


        // Edit order modal functionality
        $('#editOrderModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget); // Button that triggered the modal
            const orderId = button.data('order-id');
            const customerFName = button.data('customer-first-name');
            const customerLName = button.data('customer-last-name');
            const productName = button.data('product-name');
            const quantity = button.data('quantity');
            const status = button.data('status');
            const orderType = button.data('order-type');

            const modal = $(this);
            modal.find('#edit_order_id').val(orderId);
            modal.find('#edit_customer_fname').val(customerFName);
            modal.find('#edit_customer_lname').val(customerLName);
            modal.find('#edit_product_name').val(productName);
            modal.find('#edit_quantity').val(quantity);
            modal.find('#edit_status').val(status);
            modal.find('#edit_order_type').val(orderType);
        });

        // PDF generation functionality
        $('.PDFdata').click(function(e) {
            e.preventDefault(); // Prevent default link behavior

            // Collect data from the clicked link
            const managedBy = $(this).data('managed-by');
            const customerName = $(this).data('customer-name');
            const productName = $(this).data('product-name');
            const status = $(this).data('status');
            const orderType = $(this).data('order-type');
            const quantity = $(this).data('quantity');
            const totalPrice = $(this).data('total-price');

            // Populate the hidden form fields
            $('#managed_by').val(managedBy);
            $('#customer_name').val(customerName);
            $('#product_name').val(productName);
            $('#status').val(status);
            $('#order_type').val(orderType);
            $('#quantity').val(quantity);
            $('#total_price').val(totalPrice);

            // Submit the form
            $('#pdfForm').submit();
        });

        // Attach functions to the window so they can be called from HTML
        window.sortTable = sortTable;
        window.searchTables = searchTables;
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
        $("#OrdersTable thead tr").prepend('<th class="checkbox-column"><input type="checkbox" id="select-all"></th>');

        // Add checkboxes to all rows
        $("#OrdersTable tbody tr").prepend('<td class="checkbox-column"><input type="checkbox" class="row-checkbox"></td>');

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
            if (count > 0) {
                $("#selection-controls").fadeIn(300);
            } else {
                $("#selection-controls").fadeOut(300);
            }
        }

        // Handle delete confirmation
        $("#delete-confirmed").click(function() {
            console.log("Deleting items:", selectedItems);
            // Here you would normally send the selectedItems to the server for deletion

            // Clear selection and close modal
            $("#deleteConfirmModal").modal("hide");

            // For demo purposes, let's remove the selected rows from the table
            $(".row-checkbox:checked").closest("tr").fadeOut(400, function() {
                $(this).remove();
            });

            // Reset selection
            selectionMode = false;
            $("#toggle-selection-mode").removeClass("active");
            selectedItems = [];
            updateSelectedCount();
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

                <li class="active">
                    <a href="../ManageOrders">
                        <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Orders</span>
                    </a>
                </li>

            <?php if ($user_role === 'admin' || $user_role === 'staff') : // Admin and staff 
            ?>
                <li>
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

        <!-- Add Order Modal -->
        <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
                        </div>
                            <div class="modal-body">
                            <!-- Hidden Input for Customer ID -->
                            <input type="hidden" id="Selected_Customer_ID" name="Customer_ID">
                            <div class="mb-3">
                                <label for="Customer_FirstName" class="form-label">Customer First Name</label>
                                <select class="form-control" id="Customer_FirstName" name="Customer_FName" style = "height: fit-content" required>
                                    <option value="">Select First Name</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= htmlspecialchars($customer['Customer_ID']) ?>">
                                            <?= htmlspecialchars($customer['First_Name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="Customer_LastName" class="form-label">Customer Last Name</label>
                                <select class="form-control" id="Customer_LastName" name="Customer_LName" style="height: fit-content" required disabled>
                                    <option value="">Select Last Name</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= htmlspecialchars($customer['Customer_ID']) ?>" data-firstname="<?= htmlspecialchars($customer['First_Name']) ?>">
                                            <?= htmlspecialchars($customer['Last_Name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <script>
                                $(document).ready(function() {
                                    // Store all customer data for easy lookup
                                    const customers = <?php echo json_encode($customers); ?>;
                                    
                                    // When first name dropdown changes
                                    $('#Customer_FirstName').on('change', function() {
                                        const selectedCustomerId = $(this).val();
                                        
                                        // Find the corresponding customer
                                        const customer = customers.find(c => c.Customer_ID == selectedCustomerId);
                                        
                                        // Reset and update the last name dropdown
                                        $('#Customer_LastName').val('');
                                        
                                        if (customer) {
                                            // Find and select the matching option
                                            $('#Customer_LastName option').each(function() {
                                                if ($(this).val() == selectedCustomerId) {
                                                    $(this).prop('selected', true);
                                                    return false; // Break the loop
                                                }
                                            });
                                        }
                                    });
                                });
                                </script>
                            </div>
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product Name</label>
                                <select name="Product_ID" id="Product_ID" class="form-control" style = "height: fit-content" required>
                                    <option value="">Select Product</option>
                                    <?php foreach ($products as $product) { ?>
                                        <option value="<?php echo $product['Product_ID']; ?>">
                                            <?php echo htmlspecialchars($product['Product_Name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="add_status" class="form-label">Status</label>
                                <select class="form-control" id="Status" name="Status" style="height: fit-content; " required>
                                    <option value="">Select Status</option>
                                    <option value="To Pick Up">To Pick Up</option>
                                    <option value="In Transit">In Transit</option>
                                    <option value="Delivered">Delivered</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="order_type" class="form-label">Order Type</label>
                                <select name="Order_Type" id="Order_Type" class="form-control" style="height: fit-content;" required>
                                    <option value="">Select Order Type</option>
                                    <option value="Inbound">Inbound</option>
                                    <option value="Outbound">Outbound</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" name="Quantity" id="Quantity" class="form-control" required placeholder="Enter quantity">
                                <small class="form-text text-muted">Please enter the quantity of the product.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                            <button type="submit" name="add_order" class="btn custom-btn">Add Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Order Modal -->
        <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editOrderModalLabel">Edit Order</h5>
                        </div>
                        <div class="modal-body">
                            <?php if ($user_role === 'staff') : ?>
                                <p class="text-danger text-center fw-bold">You are not permitted to edit orders.</p>
                            <?php else: ?>
                                <input type="hidden" id="edit_order_id" name="Order_ID">
                                <?php if ($user_role !== 'driver') : ?>
                                    <div class="mb-3">
                                        <label for="edit_customer_fname" class="form-label">Customer First Name</label>
                                        <input type="text" class="form-control" id="edit_customer_fname" name="New_CustomerFName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_customer_lname" class="form-label">Customer Last Name</label>
                                        <input type="text" class="form-control" id="edit_customer_lname" name="New_CustomerLName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_product_name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="edit_product_name" name="New_ProductName">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="edit_quantity" name="New_Quantity">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_order_type" class="form-label">Order Type</label>
                                        <select class="form-control" id="edit_order_type" name="New_OrderType" style="height: fit-content;">
                                            <option value="">Select Order Type</option>
                                            <option value="Inbound">Inbound</option>
                                            <option value="Outbound">Outbound</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-control" id="edit_status" name="New_Status" style="height: fit-content;">
                                        <option value="">Select Status</option>
                                        <option value="To Pick Up">To Pick Up</option>
                                        <option value="In Transit">In Transit</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                            <?php if ($user_role !== 'driver' && $user_role !== 'staff') : ?>
                                <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none" 
                                style="background-color: #dc3545 !important; color: #fff !important;">Delete</button>
                            <?php endif; ?>
                            <?php if ($user_role !== 'staff') : ?>
                                <button type="submit" name="edit_order" class="btn custom-btn">Save Changes</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <div class="pb-4">
            <i class="fa-solid fa-chart-bar" style="font-size:56px;"></i>
            </div>
            <div class="d-flex align-items-center">
                <h3 style="font-size: 40px; letter-spacing: -0.045em;">
                    <b>Manage Orders</b>
                </h3>
                <i class="bi bi-info-circle pl-2 pb-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Manage orders including adding, editing, and viewing order details."></i>
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
            <!-- Search Box -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Search Input Group -->
                <div class="input-group m-0" style="width: 100%;">
                <div class="search-container">
                    <input type="search" class="form-control search-input-main" placeholder="Search" aria-label="Search" id="searchInput" onkeyup="searchTables()">
                    <button class="btn btn-outline-secondary search-btn-main" type="button" id="search">
                        <i class="fa fa-search"></i>
                    </button>
                </div>

                    <!-- Mobile search that will only show below 476px -->
                    <div class="mobile-search-container d-none">
                        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="mobileSearchInput" onkeyup="searchTables()">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <?php if ($user_role === 'admin') : ?>
                    <!-- Add Order Button -->
                    <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addOrderModal" style="width: auto;">Add Order</button>
                <?php endif; ?>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete <span id="delete-count">0</span> selected order(s)?
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
                    <?php if ($user_role === 'admin') : ?>
                    <button id="delete-selected-btn" class="btn btn-danger btn-sm" style="border-radius: 32px;">Delete Selected</button>
                    <?php endif; ?>
                    </div>
            </div>
            <script>
                // Connect delete buttons to delete modal
                $(document).ready(function() {
                    $("#delete-selected-btn, #delete-selected-btn-edit").click(function() {
                        $("#deleteConfirmModal").modal("show");
                    });
                });
            </script>

            <!-- Table Layout (Visible on larger screens) -->
            <div style="max-height: 750px; overflow-y: auto;">      
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="OrdersTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Managed by <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(1)">Customer's First Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(2)">Customer's Last Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(3)">Product Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(4)">Status <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(5)">Order Type <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(6)">Quantity <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(7)">Total Price <i class="bi bi-arrow-down-up"></i></th>
                            <th>Edit</th>
                            <th>Generate Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Customer_FName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Customer_LName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Order_Type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                                    <td>₱<?php echo number_format(htmlspecialchars($row['Total_Price']), 2); ?></td>
                                    <td class="text-center"> 
                                        <a href="" data-bs-toggle="modal" data-bs-target="#editOrderModal" 
                                        data-order-id="<?php echo $row['Order_ID']; ?>" 
                                        data-customer-first-name="<?php echo $row['Customer_FName']; ?>" 
                                        data-customer-last-name="<?php echo $row['Customer_LName']; ?>" 
                                        data-product-name="<?php echo $row['Product_Name']; ?>" 
                                        data-quantity="<?php echo $row['Quantity']; ?>"
                                        data-status="<?php echo $row['Status']; ?>" 
                                        data-order-type="<?php echo $row['Order_Type']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                    <td> 
                                    <a href="#" class="PDFdata"
                                            data-managed-by="<?php echo $row['Full_Name']; ?>" 
                                            data-customer-first-name="<?php echo $row['Customer_FName']; ?>" 
                                            data-customer-last-name="<?php echo $row['Customer_LName']; ?>" 
                                            data-product-name="<?php echo $row['Product_Name']; ?>" 
                                            data-status="<?php echo $row['Status']; ?>" 
                                            data-order-type="<?php echo $row['Order_Type']; ?>"
                                            data-quantity="<?php echo $row['Quantity']; ?>"
                                            data-total-price="<?php echo $row['Total_Price'] ?>">
                                            <i class="bi bi-envelope-paper"></i>
                                    </a>
                                </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Hidden Form -->
        <form id="pdfForm" action="../TransactionRecord/generate-pdf.php" method="POST" style="display:none;">
            <input type="hidden" name="managed_by" id="managed_by">
            <input type="hidden" name="customer_name" id="customer_name">
            <input type="hidden" name="product_name" id="product_name">
            <input type="hidden" name="status" id="status">
            <input type="hidden" name="order_type" id="order_type">
            <input type="hidden" name="quantity" id="quantity">
            <input type="hidden" name="total_price" id="total_price">
        </form>

            <div class="row d-block d-md-none">
                <?php
                $result->data_seek(0);
                if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#editOrderModal" 
                            data-order-id="<?php echo $row['Order_ID']; ?>" 
                            data-customer-first-name="<?php echo $row['Customer_FName']; ?>" 
                            data-customer-last-name="<?php echo $row['Customer_LName']; ?>"
                            data-product-name="<?php echo $row['Product_Name']; ?>" 
                            data-status="<?php echo $row['Status']; ?>" 
                            data-order-type="<?php echo $row['Order_Type']; ?>"
                            >
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['Product_Name']); ?></h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="card-text"><strong>Managed by:</strong> <?php echo htmlspecialchars($row['Full_Name']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Customer's First Name:</strong> <?php echo htmlspecialchars($row['Customer_FName']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Customer's Last Name:</strong> <?php echo htmlspecialchars($row['Customer_LName']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Status:</strong> <?php echo htmlspecialchars($row['Status']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Order Type:</strong> <?php echo htmlspecialchars($row['Order_Type']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
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
    MANAGE ORDERS STYLES
----------------------------------------------------- */

        /* ==========================================================================
            ManageOrders.css - Styling for the order management interface
            ========================================================================== */

        /* ==========================================================================
            Base Styles
            ========================================================================== */
        .manage-orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.045em;
            color: #333;
            margin-bottom: 0.2rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            font-weight: 400;
            color: #666;
            letter-spacing: -0.025em;
            margin-bottom: 1.5rem;
        }

        /* ==========================================================================
            Search and Action Bar
            ========================================================================== */
        .search-action-bar {
            background-color: #f8f9fa;
            padding: 1.25rem;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-group {
            flex-grow: 1;
            margin-right: 15px;
            position: relative;
        }

        .search-input {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem 0.75rem 3rem;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #6fa062;
            box-shadow: 0 0 0 0.2rem rgba(111, 160, 98, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .search-actions {
            margin-bottom: 1.5rem;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }


        /* ==========================================================================
            Buttons
            ========================================================================== */
        .add-btn {
            background-color: #6fa062;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            letter-spacing: -0.025em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .add-btn:hover {
            background-color: #5e8853;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .add-btn:active {
            transform: translateY(0);
        }

        .add-btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: #6fa062;
            border-color: #6fa062;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5e8853;
            border-color: #5e8853;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* ==========================================================================
            Table Styles
            ========================================================================== */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            overflow-x: auto;
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

        .action-icon {
            color: #6fa062;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 5px;
        }

        .action-icon:hover {
            color: #5e8853;
            transform: scale(1.2);
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50rem;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-pickup {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-transit {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        /* ==========================================================================
            Card View (Mobile)
            ========================================================================== */
        .order-card {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1rem;
            cursor: pointer;
            background-color: #fff;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .order-card-header {
            background-color: #f2f4f0;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0;
            color: #333;
        }

        .order-card-body {
            padding: 1.2rem;
        }

        .order-detail-row {
            display: flex;
            margin-bottom: 0.8rem;
        }

        .order-detail-label {
            font-weight: 600;
            color: #495057;
            width: 40%;
        }

        .order-detail-value {
            color: #333;
            width: 60%;
        }

        /* ==========================================================================
            Modal Styles
            ========================================================================== */
        .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #f2f4f0;
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-title {
            font-weight: 700;
            letter-spacing: -0.045em;
            color: #333;
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.2rem 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        /* Form Controls inside Modal */
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #6fa062;
            box-shadow: 0 0 0 0.2rem rgba(111, 160, 98, 0.25);
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            appearance: none;
            padding-right: 2.5rem;
        }

        /* ==========================================================================
            Responsive Adjustments
            ========================================================================== */
        @media (max-width: 992px) {
            .page-title {
                font-size: 2rem;
            }

            .page-subtitle {
                font-size: 1.1rem;
            }

            .search-action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-group {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .add-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .orders-table-container {
                border-radius: 12px;
            }

            .orders-table thead th,
            .orders-table td {
                padding: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.6rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }
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