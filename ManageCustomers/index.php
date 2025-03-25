<?php
// Include database connection

$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
include '../log_functions.php';
 // Start the session
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];
// Get the user's first name and email from the database
$query = "SELECT User_ID, First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($User_ID,$user_first_name, $user_last_name);
$stmt->fetch();
$stmt->close();

// Fetch product list for dropdown
$product_query = "SELECT Product_ID, Product_Name FROM Products";
$product_result = $conn->query($product_query);
$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}

// Handle adding customer
if (isset($_POST['add_customer'])) {
    $customer_id = $_POST['Customer_ID'];
    $first_name = $_POST['First_Name'];
    $last_name = $_POST['Last_Name'];
    $contact_number = $_POST['Contact_Number'];
    

    // Proceed with inserting into Customer table
    $query = "INSERT INTO Customers (Customer_ID, First_Name, Last_Name, Contact_Number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $customer_id, $first_name, $last_name, $contact_number);

    if ($stmt->execute()) {
        $success_message = "Customer record added successfully.";
    } else {
        $error_message = "Error adding customer record: " . $stmt->error;
    }

    $stmt->close();


    logActivity($conn, $User_ID, "Created a new Customer $first_name $last_name");

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

    logActivity($conn, $User_ID, "Edited a new customer $new_fname $new_lname");
}

// Fetch customers
$query = "SELECT * FROM Customers";
$result = $conn->query($query);
// Handle logout when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    logActivity($conn, $User_ID, "Logged out");
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../Login"); // Redirect to login page
    exit();
}

// Handle deleting customers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customers'])) {
    $customer_ids = json_decode($_POST['customer_ids']);

    foreach ($customer_ids as $customer_id) {
        // Delete the customer


       
        $query = "SELECT First_Name, Last_Name FROM Customers WHERE Customer_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->bind_result($Customer_lname, $Customer_fname);
        $stmt->fetch();
        $stmt->close();





        $query = "DELETE FROM Customers WHERE Customer_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();


        logActivity($conn, $User_ID, "Deleted a Customer $Customer_fname $Customer_lname");
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
        function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('customersTable');
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


        function sortTable(columnIndex) {
    var table = document.getElementById("customersTable");
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
</script>

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR DELETE ENTRY FUNCTION JS
------------------------------------------------------>
<script>
$(document).ready(function() {
    let selectionMode = false;
    let selectedItems = [];

    // Check if there are any customers
    if ($("#customersTable tbody tr").length > 0 && $("#customersTable tbody tr td").length > 1) {
        // Add checkbox column to table header
        $("#customersTable thead tr").prepend("<th class='checkbox-column' style='width: 10%;'><button type='button' class='btn btn-sm custom-btn' id='select-all-btn' onclick='document.getElementById(\"select-all\").click()'>Select All <input type='checkbox' id='select-all' style='visibility:hidden; position:absolute;'></button></th>");
        // Add checkboxes to all rows
        $("#customersTable tbody tr").prepend(function() {
            var customerId = $(this).data("customer-id");
            return '<td class="checkbox-column"><input type="checkbox" class="row-checkbox" value="' + customerId + '"></td>';
        });
    }

    // **Toggle Selection Mode for Mobile**
    $("#toggle-selection-mode").click(function() {
        if (selectedItems.length > 0) {
            $("#deleteConfirmModal").modal("show");
        } else {
            selectionMode = !selectionMode;
            if (selectionMode) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
                $(".row-checkbox").prop("checked", false);
                $("#select-all").prop("checked", false);
                selectedItems = [];
                updateSelectedCount();
            }
        }
    });

        // **Deselect All Items**
        $("#deselect-all-btn").click(function() {
            $(".row-checkbox").prop("checked", false);
            $("#select-all").prop("checked", false);
            selectedItems = [];
            updateSelectedCount();
        });
        
        // Handle modal close via escape key or clicking outside
        $('#editCustomerModal').on('hidden.bs.modal', function() {
            $(".row-checkbox").prop("checked", false);
            $("#select-all").prop("checked", false);
            selectedItems = [];
            updateSelectedCount();
        });
        
    // **Mobile: Tap a Customer Card to Select for Deletion**
    $(document).on("click", ".card", function(event) {
        let customerId = $(this).data("customer-id");

        if (!customerId) {
            console.error("Error: Missing customer ID in card");
            return;
        }

        // Prevent modal from opening when selecting for deletion
        if ($(this).hasClass("selected")) {
            selectedItems = selectedItems.filter(id => id !== customerId);
            $(this).removeClass("selected");
        } else {
            selectedItems.push(customerId);
            $(this).addClass("selected");
        }

        updateSelectedCount();
        event.stopPropagation();
    });

    // **Desktop: Select All Checkboxes**
    $("#select-all").change(function() {
        let isChecked = $(this).is(":checked");
        $(".row-checkbox").prop("checked", isChecked);
        selectedItems = isChecked ? $(".row-checkbox").map(function() { return $(this).val(); }).get() : [];
        updateSelectedCount();
    });

    // **Desktop: Select Individual Checkbox**
    $(document).on("change", ".row-checkbox", function() {
        let customerId = $(this).val();
        if ($(this).is(":checked")) {
            if (!selectedItems.includes(customerId)) selectedItems.push(customerId);
        } else {
            selectedItems = selectedItems.filter(id => id !== customerId);
            $("#select-all").prop("checked", false);
        }
        updateSelectedCount();
    });

    // **Update Selected Count Display**
    function updateSelectedCount() {
        let count = selectedItems.length;
        $("#selected-count").text(count + " selected");
        $("#delete-count").text(count);

            // Show/hide floating dialog based on selection
            if (count > 0 && window.innerWidth >= 768) { // Only show on larger screens
                $("#selection-controls").fadeIn(300);
            } else {
                $("#selection-controls").fadeOut(300);
            }
    }

    // **Delete Button Click Event**
    $("#delete-selected-btn").click(function() {
        if (selectedItems.length > 0) {
            $("#deleteConfirmModal").modal("show");
        } else {
            alert("Please select at least one customer.");
        }
    });

    // **Confirm Deletion**
    $("#delete-confirmed").click(function() {
        if (selectedItems.length === 0) {
            alert("No customers selected.");
            return;
        }

        let customerIds = JSON.stringify(selectedItems);
        console.log("Selected Customer IDs for Deletion:", customerIds);

        $("#customer_ids").val(customerIds);
        $("#deleteForm").submit();
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
                        <h1 class="mb-2"><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></h1>
                        <h2><?php echo htmlspecialchars($user_email); ?></h2>
                        <h5 style="font-size: 1em; background-color: #6fa062; color: #F2f2f2; font-weight: 700; padding: 8px; border-radius: 8px; width: fit-content;"><?php echo htmlspecialchars($user_role); ?></h5>
                    </div>
                </div>
            </li>
            <li>
<!-- Logout Button -->
<a href="../Login" class="logout" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
    <i class="fa-solid fa-sign-out-alt"></i>
    <span>Log out</span>
</a>

<!-- Hidden Logout Form -->
<form id="logoutForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="logout" value="1">
</form>
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
                <a href="../Manual/Manual-Placeholder.pdf" class="btn btn-dark ml-2 d-flex justify-content-center align-items-center" id="manualButton" data-toggle="tooltip" data-placement="bottom" target="_blank" title="View Manual">
                        <i class="fas fa-file-alt"></i>
                </a>
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
                <?php if ($user_role === 'admin') : ?>
                    <!-- Add Customer Button -->
                    <!-- <button class="btn custom-btn m-0" id="select-all" style="width: 10%;">All</button> -->
                    <button class="btn custom-btn m-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal" style="width: auto;">Add Customer</button>
                <?php endif; ?>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete <span id="delete-count">0</span> selected customer(s)?
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
            <!-- Hidden Form for Deletion -->
<form id="deleteForm" method="POST" action="" style="display:none;">
    <input type="hidden" name="delete_customers" value="1">
    <input type="hidden" name="customer_ids" id="customer_ids">
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
            <div class="table-container" style="max-height: 550px; overflow-y: auto; overflow-x: hidden; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); position: relative;" onscroll="document.querySelector('.scroll-indicator').style.opacity = this.scrollTop > 20 ? '1' : '0';">
                <div class="scroll-indicator" style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: linear-gradient(transparent, rgba(111, 160, 98, 0.2)); opacity: 0; pointer-events: none; transition: opacity 0.3s ease;"></div>
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="customersTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(1)">First Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(2)">Last Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(3)">Contact Number <i class="bi bi-arrow-down-up"></i></th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr data-customer-id="<?php echo htmlspecialchars($row['Customer_ID']); ?>">
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
                                <td colspan="6" class="text-center">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <!-- Responsive Card Layout (Visible on smaller screens) -->
            <div class="row d-block d-md-none rounded">
            <?php
                $result->data_seek(0);
                if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="col-12 col-md-6 mb-3">
                            <div class="card shadow-sm customer-card" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editCustomerModal" 
                                data-customer-id="<?php echo $row['Customer_ID']; ?>" 
                                data-first-name="<?php echo $row['First_Name']; ?>" 
                                data-last-name="<?php echo $row['Last_Name']; ?>" 
                                data-contact-number="<?php echo $row['Contact_Number']; ?>"
                                style="cursor: pointer;">
                                <div class="card-body rounded">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="card-text"><strong>Customer ID:</strong> <?php echo htmlspecialchars($row['Customer_ID']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Contact:</strong> <?php echo htmlspecialchars($row['Contact_Number']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p>No customers found.</p>
                <?php endif; ?>
            </div>
            <p id="noResultsMessage" style="display: none; text-align: center; font-weight:bold; margin-top: 10px;">No Customer found.</p>
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
                            <!-- Hidden field for Customer_ID (auto-incremented, not user-inputted) -->
                            <input type="hidden" name="Customer_ID">

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
                                <input type="number" class="form-control" id="Contact_Number" name="Contact_Number" placeholder="e.g., 09913323242" min="0" required>
                            </div>
                            <div class="modal-footer d-flex justify-content-end">
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
                                <input type="number" class="form-control" id="edit_contact_num" name="New_ContactNum" placeholder="e.g., 09913323242" min="0" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;" id="deselect-all-btn">Close</button>
                                <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none" style="background-color: #dc3545 !important; color: #fff !important;">Delete</button>
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
/* 
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
        } */

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