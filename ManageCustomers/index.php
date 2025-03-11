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
$query = "SELECT First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name, $user_last_name);
$stmt->fetch();
$stmt->close();

// Handle adding customer
if (isset($_POST['add_customer'])) {
    $customer_id = $_POST['Customer_ID'];
    $product_id = $_POST['Product_ID'];
    $first_name = $_POST['First_Name'];
    $last_name = $_POST['Last_Name'];
    $contact_number = $_POST['Contact_Number'];
    

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

    // Proceed with inserting into Customer table
    $query = "INSERT INTO Customers (Customer_ID, Product_ID, First_Name, Last_Name, Contact_Number) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $customer_id, $product_id, $first_name, $last_name, $contact_number);

    if ($stmt->execute()) {
        $success_message = "Customer record added successfully.";
    } else {
        $error_message = "Error adding customer record: " . $stmt->error;
    }

    $stmt->close();
}


// Handle editing customer
if (isset($_POST['edit_customer'])) {
    $customer_id = $_POST['Customer_ID'];
    $new_fname = $_POST['New_FirstName'];
    $new_lname = $_POST['New_LastName'];
    $new_contactnum = $_POST['New_ContactNum'];

    $query = "UPDATE Customers SET First_Name = ?, Last_Name = ?, Contact_Number = ? WHERE Customer_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $new_fname, $new_lname, $new_contactnum, $customer_id);

    if ($stmt->execute()) {
        $success_message = "Customer record updated successfully.";
    } else {
        $error_message = "Error updating customer record: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch customers
$query = "SELECT * FROM Customers";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD | Manage Customers</title>
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
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR MANAGECUSTOMERS JS
------------------------------------------------------>

<script>
    $(document).ready(function () {
        // Populate edit modal with existing data
        $('#editCustomerModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var customerId = button.data('customer-id'); // Extract info from data-* attributes
            var firstName = button.data('first-name');
            var lastName = button.data('last-name');
            var contactNumber = button.data('contact-number');

            var modal = $(this);
            modal.find('#edit_customer_id').val(customerId);
            modal.find('#edit_first_name').val(firstName);
            modal.find('#edit_last_name').val(lastName);
            modal.find('#edit_contact_num').val(contactNumber);
        });
    });

        // Function to search table
        function searchTables() {
            const input = document.getElementById('searchInput');
            if (!input) return; // Ensure input exists
            const filter = input.value.trim().toLowerCase();

            // Search in Desktop Table
            const tableRows = document.querySelectorAll('#customersTable tbody tr');
            if (tableRows.length > 0) {
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
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

    function sortTable(n) {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("customersTable");
        switching = true;
        // Set the sorting direction to ascending:
        dir = "asc";
        // Make a loop that will continue until no switching has been done:
        while (switching) {
            // Start by saying: no switching is done:
            switching = false;
            rows = table.rows;
            // Loop through all table rows (except the table headers):
            for (i = 1; i < (rows.length - 1); i++) {
                // Start by saying there should be no switching:
                shouldSwitch = false;
                /* Get the two elements you want to compare,
                one from current row and one from the next: */
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                /* Check if the two rows should switch place,
                based on the direction, asc or desc: */
                if (dir == "asc") {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        // If so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir == "desc") {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        // If so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                /* If a switch has been marked, make the switch
                and mark that a switch has been done: */
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                // Each time a switch is done, increase this count by 1:
                switchcount++;
            } else {
                /* If no switching has been done AND the direction is "asc",
                set the direction to "desc" and run the while loop again. */
                if (switchcount == 0 && dir == "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
        
    }
</script>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR MANAGECUSTOMERS JS 2
------------------------------------------------------>

<script>
    $(document).ready(function () {
        // Populate edit modal with existing data
        $('#editCustomerModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var customerId = button.data('customer-id'); // Extract info from data-* attributes
            var firstName = button.data('first-name');
            var lastName = button.data('last-name');
            var contactNumber = button.data('contact-number');

            var modal = $(this);
            modal.find('#edit_customer_id').val(customerId);
            modal.find('#edit_first_name').val(firstName);
            modal.find('#edit_last_name').val(lastName);
            modal.find('#edit_contact_num').val(contactNumber);
        });
    });

    function sortTable(n) {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("customersTable");
        switching = true;
        // Set the sorting direction to ascending:
        dir = "asc";
        // Make a loop that will continue until no switching has been done:
        while (switching) {
            // Start by saying: no switching is done:
            switching = false;
            rows = table.rows;
            // Loop through all table rows (except the table headers):
            for (i = 1; i < (rows.length - 1); i++) {
                // Start by saying there should be no switching:
                shouldSwitch = false;
                /* Get the two elements you want to compare,
                one from current row and one from the next: */
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                /* Check if the two rows should switch place,
                based on the direction, asc or desc: */
                if (dir == "asc") {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        // If so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir == "desc") {
                    if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        // If so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                /* If a switch has been marked, make the switch
                and mark that a switch has been done: */
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                // Each time a switch is done, increase this count by 1:
                switchcount++;
            } else {
                /* If no switching has been done AND the direction is "asc",
                set the direction to "desc" and run the while loop again. */
                if (switchcount == 0 && dir == "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }
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
                <li>
                    <a href="../ManageProducts">
                        <i class="fa-solid fa-list" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Product</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($user_role === 'admin') : // Only Admins 
            ?>
                <li class="active">
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
                        <h1><?php echo htmlspecialchars($user_first_name); ?></h1>
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
                <i class="fa-solid fa-users" style="font-size:56px;"></i>
            </div>
            <div class="d-flex align-items-center">
                <h3 style="font-size: 40px; letter-spacing: -0.045em;">
                    <b>Manage Customers</b>
                </h3>
                <i class="bi bi-info-circle pl-2 pb-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Add and edit customer details, including their associated products and contact information."></i>
                <script>
                    $(document).ready(function(){
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                </script>
            </div>
            <h4 class="mb-2" style="color: gray; font-size: 16px;">Manage customer information and associated products.</h4>
            <div class="alert alert-light d-lg-none d-md-block" role="alert" style="color: gray; background-color: #e8ecef;">
                <i class="bi bi-info-circle mr-1"></i>
                Tap card to edit customer details.
            </div>
            <!-- Search Box -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Search Input Group -->
                <div class="input-group" style="width: 100%;">
                <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput" onkeyup="searchTables()">
                    <button class="btn btn-outline-secondary" type="button" id="search">
                        <i class="fa fa-search"></i>
                    </button>
                </div>

                <!-- Add Customer Button -->
                <button class="btn custom-btn m-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal" style="width: auto">Add Customer</button>
            </div>

            <!-- Table Layout (Visible on larger screens) -->    
            <div style="max-height: 500px; overflow-y: auto;">      
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="customersTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Customer ID <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(1)">Product ID <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(2)">First Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(3)">Last Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(4)">Contact Number <i class="bi bi-arrow-down-up"></i></th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Customer_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Product_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['First_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Last_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Contact_Number']); ?></td>
                                    <td class="text-center">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                                           data-customer-id="<?php echo htmlspecialchars($row['Customer_ID']); ?>"
                                           data-first-name="<?php echo htmlspecialchars($row['First_Name']); ?>"
                                           data-last-name="<?php echo htmlspecialchars($row['Last_Name']); ?>"
                                           data-contact-number="<?php echo htmlspecialchars($row['Contact_Number']); ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <!-- Responsive Card Layout (Visible on smaller screens) -->
            <div style="max-height: 750px; overflow-y: auto; overflow-x: hidden;">      
            <div class="row d-block d-md-none">
                <?php
                $result->data_seek(0); // Reset pointer to the beginning
                if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="col-12 mb-3">
                            <div class="card shadow-sm" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#editCustomerModal" 
                                 data-customer-id="<?php echo htmlspecialchars($row['Customer_ID']); ?>" 
                                 data-first-name="<?php echo htmlspecialchars($row['First_Name']); ?>" 
                                 data-last-name="<?php echo htmlspecialchars($row['Last_Name']); ?>"
                                 data-contact-number="<?php echo htmlspecialchars($row['Contact_Number']); ?>"
                                 style="cursor: pointer;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></h5>
                                    <p class="card-text">
                                        <strong>Customer ID:</strong> <?php echo htmlspecialchars($row['Customer_ID']); ?><br>
                                        <strong>Product ID:</strong> <?php echo htmlspecialchars($row['Product_ID']); ?><br>
                                        <strong>Contact:</strong> <?php echo htmlspecialchars($row['Contact_Number']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No customers found.</p>
                <?php endif; ?>
            </div>
            </div>
        </div>

        <!-- Add Customer Modal -->
        <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product ID</label>
                                <input type="number" class="form-control" id="Product_ID" name="Product_ID" required>
                            </div>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="First_Name" name="First_Name" placeholder="e.g., Jon" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="Last_Name" name="Last_Name" placeholder="e.g., Don" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="number" class="form-control" id="Contact_Number" name="Contact_Number" placeholder="e.g., 09913323242" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                                <button type="submit" name="add_customer" class="btn custom-btn">Add Customer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Customer Modal -->
        <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <input type="hidden" id="edit_customer_id" name="Customer_ID">
                            <div class="mb-3">
                                <label for="edit_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="edit_first_name" name="New_FirstName" placeholder="e.g., Jon" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="edit_last_name" name="New_LastName" placeholder="e.g., Don" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_contact_num" class="form-label">Contact Number</label>
                                <input type="number" class="form-control" id="edit_contact_num" name="New_ContactNum" placeholder="e.g., 09913323242" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;">Close</button>
                                <button type="submit" name="edit_customer" class="btn custom-btn">Save Changes</button>
                            </div>
                        </form>
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
    MANAGE CUSTOMERS STYLES
----------------------------------------------------- */

        /* ---------------------------------------------------
            MODAL STYLE
        ----------------------------------------------------- */

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .modal-header {
            background-color: #f2f4f0;
            border-bottom: 1px solid #dee2e6;
            padding: 1.2rem 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            letter-spacing: -0.045em;
            color: #333;
            font-size: 1.4rem;
        }

        .btn-close {
            background-color: #e8ecef;
            border-radius: 50%;
            opacity: 0.8;
            transition: transform 0.3s, opacity 0.3s;
        }

        .btn-close:hover {
            transform: rotate(90deg);
            opacity: 1;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* ---------------------------------------------------
            GENERAL STYLES
        ----------------------------------------------------- */

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.045em;
            color: #333;
            margin-bottom: 0.2rem;
        }

        h4 {
            font-size: 1.2rem;
            font-weight: 400;
            letter-spacing: -0.025em;
            margin-bottom: 1.5rem;
        }

        /* ---------------------------------------------------
            SEARCH & ACTION SECTION
        ----------------------------------------------------- */

        .search-actions {
            margin-bottom: 1.5rem;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .input-group .form-control {
            border-radius: 10px 0 0 10px;
            border: 1px solid #dee2e6;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }

        .input-group .form-control:focus {
            border-color: #6fa062;
            box-shadow: 0 0 0 0.2rem rgba(111, 160, 98, 0.25);
        }

        .input-group .btn-outline-secondary {
            border-radius: 0 10px 10px 0;
            border: 1px solid #dee2e6;
            border-left: none;
            background-color: #fff;
            color: #6c757d;
            transition: all 0.3s;
        }

        .input-group .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #5a6268;
        }

        /* ---------------------------------------------------
            CUSTOM BUTTON
        ----------------------------------------------------- */

        .custom-btn {
            background-color: #6fa062;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            letter-spacing: -0.025em;
            transition: transform 0.3s, background-color 0.3s;
        }

        .custom-btn:hover {
            background-color: #5e8853;
            transform: scale(1.05);
            color: white;
        }

        /* ---------------------------------------------------
            TABLE STYLES
        ----------------------------------------------------- */

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
            RESPONSIVE CARDS
        ----------------------------------------------------- */

        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            letter-spacing: -0.025em;
        }

        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* ---------------------------------------------------
            FORM STYLES
        ----------------------------------------------------- */

        .form-label {
            font-weight: 600;
            color: #444;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: #6fa062;
            box-shadow: 0 0 0 0.2rem rgba(111, 160, 98, 0.25);
        }

        /* ---------------------------------------------------
            RESPONSIVE ADJUSTMENTS
        ----------------------------------------------------- */

        @media (max-width: 767px) {
            .container {
                padding: 0.75rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            h4 {
                font-size: 1rem;
            }
            
            .search-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .custom-btn {
                width: 100%;
                margin-top: 10px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }

        /* ---------------------------------------------------
            UTILITY CLASSES
        ----------------------------------------------------- */

        .shadow-sm {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        }

        .text-success {
            color: #6fa062 !important;
        }

        .bg-light-success {
            background-color: rgba(111, 160, 98, 0.1);
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

