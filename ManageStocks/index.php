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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
// Include database connection and session check
$required_role = 'admin,staff';
include('../check_session.php');
include '../dbconnect.php';
include '../log_functions.php';
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

// Function to check stock levels and send notifications
function checkStockNotifications($conn, $product_id, $old_stock, $threshold) {
    // Fetch product name
    $product_name = "";
    $query = "SELECT Product_Name FROM Products WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($product_name);
    $stmt->fetch();
    $stmt->close();

    if (empty($product_name)) return;

    // Critical stock: Notify both admin and staff
    if ($old_stock < $threshold) {
        $message = "⚠️ Stock for $product_name is critically low (Current: $old_stock, Threshold: $threshold). Reorder soon!";
        foreach (['admin', 'staff'] as $role) {
            $insert_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES (?, ?, NOW(), 0)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ss", $role, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Stock update notification for staff
    elseif ($old_stock == $threshold || $old_stock == $threshold + 10 || $old_stock == $threshold + 30) {
        $message = "ℹ️ Stock for $product_name has reached $old_stock.";
        $insert_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES ('staff', ?, NOW(), 0)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("s", $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Check all stock entries and move New_Stock to Old_Stock if needed
$check_query = "SELECT Stock_ID, Product_ID, Old_Stock, New_Stock, Threshold FROM Stocks";
$check_stmt = $conn->prepare($check_query);
$check_stmt->execute();
$check_stmt->store_result();
$check_stmt->bind_result($stock_id, $product_id, $old_stock, $new_stock, $threshold);

while ($check_stmt->fetch()) {
    if ($old_stock == 0 && $new_stock > 0) {
        $update_query = "UPDATE Stocks SET Old_Stock = New_Stock, New_Stock = 0 WHERE Stock_ID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $stock_id);
        $update_stmt->execute();
        $update_stmt->close();
        $old_stock = $new_stock;
        $new_stock = 0;
    }
    if (!empty($product_id)) checkStockNotifications($conn, $product_id, $old_stock, $threshold);
}
$check_stmt->close();

// Handle adding stock
if (isset($_POST['add_stock'])) {
    $user_id = $_POST['User_ID'];
    $product_id = $_POST['Product_ID'];
    $old_stock = $_POST['Old_Stock'];
    $new_stock = $_POST['New_Stock'];
    $threshold = $_POST['Threshold'];

    // Prevent duplicate product stock entry
    $query = "SELECT COUNT(*) FROM Stocks WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script>
                Swal.fire({ icon: 'error', title: 'Duplicate Product', text: 'This product already exists in stock!' })
                .then(() => window.history.back());
              </script>";
        exit;
    }

    // Insert into Stocks table
    $insert_query = "INSERT INTO Stocks (User_ID, Product_ID, Old_Stock, New_Stock, Threshold) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiiii", $user_id, $product_id, $old_stock, $new_stock, $threshold);

    if ($stmt->execute()) {
        $success_message = "Stock added successfully.";
        logActivity($conn, $user_id, "Created a new Stock entry for Product_ID: $product_id");
    } else {
        $error_message = "Error adding stock: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch stock details via AJAX
if (isset($_POST['fetch_stock']) && isset($_POST['stock_id'])) {
    $stock_id = $_POST['stock_id'];
    $fetch_query = "SELECT Stock_ID, New_Stock, Threshold FROM Stocks WHERE Stock_ID = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->bind_result($stock_id, $new_stock, $threshold);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(['stock_id' => $stock_id, 'new_stock' => $new_stock, 'threshold' => $threshold]);
    exit;
}

// Handle editing stock
if (isset($_POST['edit_stock'])) {
    $stock_id = $_POST['Stock_ID'];
    $new_stock = $_POST['New_Stock'];
    $threshold = $_POST['Threshold'];

    // Fetch current Old_Stock and New_Stock before updating
    $query = "SELECT Old_Stock, New_Stock FROM Stocks WHERE Stock_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    $stmt->bind_result($old_stock, $current_new_stock);
    $stmt->fetch();
    $stmt->close();

    // Treat null as 0 for old_stock
    if (is_null($old_stock)) {
        $old_stock = 0;
    }

    // Update stock: move New_Stock to Old_Stock only if Old_Stock is zero, then update New_Stock
    if ($old_stock == 0) {
        $query = "UPDATE Stocks SET Old_Stock = ?, New_Stock = 0, Threshold = ? WHERE Stock_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $current_new_stock, $threshold, $stock_id);
    } else {
        $query = "UPDATE Stocks SET New_Stock = ?, Threshold = ? WHERE Stock_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $new_stock, $threshold, $stock_id);
    }

    if ($stmt->execute()) {
        $success_message = "Stock updated successfully.";
    } else {
        $error_message = "Error updating stock: " . $stmt->error;
    }

    $stmt->close();



//WALA KUKUHAHAN NG ID PARA PROD NAME


    logActivity($conn, $user_id, "Edited a Stock");

}

// Fetch stock data for display
$query = "SELECT Stocks.Stock_ID, 
                 Users.First_Name AS First_Name, 
                 CONCAT(Products.Product_Name, ' (', Products.Unit, ')') AS Product_Name, 
                 Products.Product_Type,
                 Stocks.Old_Stock, 
                 Stocks.New_Stock, 
                 Stocks.Threshold 
          FROM Stocks
          INNER JOIN Users ON Stocks.User_ID = Users.User_ID
          INNER JOIN Products ON Stocks.Product_ID = Products.Product_ID"; 

$result = $conn->query($query);

// Handle deleting stocks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_stocks'])) {
    $stock_ids = json_decode($_POST['stock_ids']);

    foreach ($stock_ids as $stock_id) {
        // Delete the stock
        $query = "DELETE FROM Stocks WHERE Stock_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $stock_id);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $user_id, "Deleted a Stock $stock_id");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle logout when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    logActivity($conn, $user_id, "Logged out");
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../Login"); // Redirect to login page
    exit();
}
?>
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
    var table = document.getElementById("stocksTable");
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
    const table = document.getElementById('stocksTable');
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
}

    // Search in Mobile Cards (if applicable)
    const cards = document.querySelectorAll('.card');
    if (cards.length > 0) {
        let cardFoundAny = false; // Track if any card is found
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const cardFound = text.includes(filter);
            card.style.display = cardFound ? '' : 'none';
            if (cardFound) cardFoundAny = true; // Set to true if at least one card is found
        });
        foundAny = foundAny || cardFoundAny; // Update foundAny based on card results
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

<!-----------------------------------------------------
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR DELETE ENTRY FUNCTION JS
------------------------------------------------------>

<script>
    $(document).ready(function() {
        // Initialize selection mode variables
        let selectionMode = false;
        let selectedItems = [];

        // Check if there are any stocks
        if ($("#stocksTable tbody tr").length > 0 && $("#stocksTable tbody tr td").length > 1) {
            // Add checkbox column to table header
            $("#stocksTable thead tr").prepend("<th class='checkbox-column' style='width: 10%;'><button type='button' class='btn btn-sm custom-btn' id='select-all-btn' onclick='document.getElementById(\"select-all\").click()'>Select All <input type='checkbox' id='select-all' style='visibility:hidden; position:absolute;'></button></th>");

            // Add checkboxes to all rows
            $("#stocksTable tbody tr").prepend(function() {
            var stockId = $(this).data("stock-id");
            return '<td class="checkbox-column"><input type="checkbox" class="row-checkbox" value="' + stockId + '"></td>';
            });
        }

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

        // **Deselect All Items**
        $("#deselect-all-btn").click(function() {
            $(".row-checkbox").prop("checked", false);
            $("#select-all").prop("checked", false);
            selectedItems = [];
            updateSelectedCount();
        });
        
        // Handle modal close via escape key or clicking outside
        $('#editStockModal').on('hidden.bs.modal', function() {
            $(".row-checkbox").prop("checked", false);
            $("#select-all").prop("checked", false);
            selectedItems = [];
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

        // Update the selected count display
        function updateSelectedCount() {
            const count = selectedItems.length;
            $("#selected-count").text(count + " selected");
            $("#delete-count").text(count);
            
            // Show/hide floating dialog based on selection
            if (count > 0 && window.innerWidth >= 768) { // Only show on larger screens
                $("#selection-controls").fadeIn(300);
            } else {
                $("#selection-controls").fadeOut(300);
            }
        }

        // Handle delete confirmation
        $("#delete-confirmed").click(function() {
            // Initialize an array to store all stock IDs
            let stockIds = [];
            
            // Go through all selected items
            selectedItems.forEach(item => {
            // Check if item is a table row (has checkbox)
            const checkbox = $(item).find(".row-checkbox");
            if (checkbox.length > 0) {
                stockIds.push(checkbox.val());
            } 
            // Check if item is a card
            else if ($(item).hasClass("card")) {
                const cardStockId = $(item).data("stock-id");
                if (cardStockId) {
                stockIds.push(cardStockId);
                }
            }
            });
            
            // Remove any duplicates
            stockIds = [...new Set(stockIds)];
            
            console.log("Selected Stock IDs: ", stockIds); // Debug log to check stock IDs
            $("#stock_ids").val(JSON.stringify(stockIds));
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
            <!-- Revision 1 -->
            <li>
                <a href="../InboundInvoices">
                    <i class="fa-solid fa-file-import" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Inbound Invoices</span>
                </a>
            </li>

            <li>
                <a href="../OutboundInvoices">
                    <i class="fa-solid fa-file-export" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                    <span>&nbsp;Outbound Invoices</span>
                </a>
            </li>
            <!-- Revision 1 CODE ENDS HERE -->
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
<!-- Logout Button -->
<a href="../Login" class="logout" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
    <i class="fa-solid fa-sign-out-alt"></i>
    <span>Log out</span>
</a>

<!-- Hidden Logout Form -->
<form id="logoutForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="logout" value="1">
</form>    </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content" style="max-height: 750px; overflow-y: auto; overflow-x: hidden;">
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
                    <li style="font-size: 1em; background-color: #f8d7da; color: #721c24; padding: 5px; border-radius: 5px; list-style-type: none; margin-bottom: 5px; border: 1px solid #f5c6cb;">
                        <i class="fas fa-exclamation-circle"></i> <span>Red</span> = Stock is in threshold/below threshold
                    </li>
                    <li style="font-size: 1em; background-color: #ffe0b2; color: #8a6d3b; padding: 5px; border-radius: 5px; list-style-type: none; margin-bottom: 5px; border: 1px solid #ffcc80;">
                        <i class="fas fa-exclamation-triangle"></i> <span>Orange</span> = Stock is +10 of the threshold
                    </li>
                    <li style="font-size: 1em; background-color: #fff3cd; color: #856404; padding: 5px; border-radius: 5px; list-style-type: none; margin-bottom: 5px; border: 1px solid #ffeeba;">
                        <i class="fas fa-chart-line"></i> <span>Yellow</span> = Stock is +30 of the threshold
                    </li>
                </ul>
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
                    <!-- Add Stock Button -->
                    <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addStockModal" style="width: auto;">Add Stock</button>
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
                    <?php if ($user_role === 'admin' || $user_role === 'staff') : ?>
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
            <!-- Hidden Form for Deletion -->
            <form id="deleteForm" method="POST" action="" style="display:none;">
                <input type="hidden" name="delete_stocks" value="1">
                <input type="hidden" name="stock_ids" id="stock_ids">
            </form>

                <!-- Table Layout (Visible on larger screens) -->
                <div class="table-container" style="max-height: 400px; overflow-y: auto; overflow-x: hidden; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); position: relative;" onscroll="document.querySelector('.scroll-indicator').style.opacity = this.scrollTop > 20 ? '1' : '0';">
                    <div class="scroll-indicator" style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: linear-gradient(transparent, rgba(111, 160, 98, 0.2)); opacity: 0; pointer-events: none; transition: opacity 0.3s ease;"></div>
                <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-bordered" id="stocksTable">
                    
                <thead>
    <tr>
        <th onclick="sortTable(1)">Stocked By <i class="bi bi-arrow-down-up"></i></th>
        <th onclick="sortTable(2)">Product Name <i class="bi bi-arrow-down-up"></i></th>
        <th onclick="sortTable(3)">Product Type <i class="bi bi-arrow-down-up"></i></th>
        <th onclick="sortTable(4)">Old Stock <i class="bi bi-arrow-down-up"></i></th>
        <th onclick="sortTable(5)">New Stock <i class="bi bi-arrow-down-up"></i></th>
        <th onclick="sortTable(6)">Threshold <i class="bi bi-arrow-down-up"></i></th>
        <th>Edit</th>
    </tr>
</thead>

                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php 
                        while ($row = mysqli_fetch_assoc($result)): 
                            $oldStock = $row['Old_Stock'];
                            $threshold = $row['Threshold'];

                              // Apply color only to the Threshold column
                            if ($oldStock <= $threshold) {
                                $oldStockClass = "table-danger"; // Red
                            } elseif ($oldStock <= $threshold + 10) {
                                $oldStockClass = "bg-orange text-dark"; // Distinct orange
                            } elseif ($oldStock <= $threshold + 30) {
                                $oldStockClass = "table-warning"; // Yellow
                            }else {
                                $oldStockClass = "";
                            }

                        ?>

                <tr data-stock-id="<?php echo $row['Stock_ID']; ?>" data-new-stock="<?php echo $row['New_Stock']; ?>" data-threshold="<?php echo $row['Threshold']; ?>">
                <td><?php echo $row['First_Name']; ?></td>
                <td><?php echo $row['Product_Name']; ?></td>
                <td><?php echo $row['Product_Type']; ?></td>
                <td class="<?php echo $oldStockClass; ?>"><?php echo $row['Old_Stock']; ?></td>
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
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">No stocks found.</td>
        </tr>
    <?php endif; ?>
</tbody>

                </table>
                </div>

                <div class="row d-block d-md-none">
                <?php
                $result->data_seek(0);

                if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $oldStock = $row['Old_Stock'];
                        $threshold = $row['Threshold'];

                        if ($oldStock <= $threshold) {
                            $cardClass = "bg-danger text-white"; // Red (below threshold)
                        } elseif ($oldStock <= $threshold + 10) {
                            $cardClass = "bg-orange text-white"; // Custom Orange
                        } elseif ($oldStock <= $threshold + 30) {
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
            <p id="noResultsMessage" style="display: none; text-align: center; font-weight:bold; margin-top: 10px;">No Stock found.</p>

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
                            $query = "SELECT Product_ID, CONCAT(Product_Name, ' (', Unit, ')') AS Product_Name, Product_Type FROM Products";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['Product_ID'] . "'>" . $row['Product_Name'] . " - " . $row['Product_Type'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="old_stock" class="form-label">Old Stock</label>
                            <input type="number" class="form-control" id="Old_Stock" name="Old_Stock" placeholder="Enter old stock quantity" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_stock" class="form-label">New Stock</label>
                            <input type="number" class="form-control" id="New_Stock" name="New_Stock" placeholder="Enter new stock quantity" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="threshold" class="form-label">Threshold</label>
                        <input type="number" class="form-control" id="Threshold" name="Threshold" placeholder="Enter threshold quantity" min="0" required>
                    </div>
                    <!-- <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="Notes" rows="3" placeholder="Enter notes"></textarea>
                    </div> -->
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
                                    <input type="number" class="form-control" id="edit_new_stock" name="New_Stock" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_threshold" class="form-label">Threshold</label>
                                    <input type="number" class="form-control" id="edit_threshold" name="Threshold" min="0" required>
                                </div>
                                <!-- <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="Notes" rows="3" placeholder="Enter notes"></textarea>
                                </div> -->
                                <div class="modal-footer">
                                    <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef !important; color: #495057 !important;" id="deselect-all-btn">Close</button>
                                    <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none" style="background-color: #dc3545 !important; color: #fff !important;">Delete</button>
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

/* Enhanced Stock Status Styling */
.bg-orange, .table-orange {
    background-color: #ffe0b2 !important; /* Light Orange */
    color: #8a6d3b !important; /* Dark Orange Text */
    font-weight: 600;
    padding: 4px 8px !important;
    border-left: 3px solid #f39c12 !important;
    transition: all 0.2s ease !important;
    letter-spacing: -0.02em !important;
}

.bg-danger, .table-danger {
    background-color: #f8d7da !important; /* Light Red */
    color: #721c24 !important; /* Dark Red Text */
    font-weight: 600;
    padding: 4px 8px !important;
    border-left: 3px solid #dc3545 !important;
    transition: all 0.2s ease !important;
    letter-spacing: -0.02em !important;
}

.bg-warning, .table-warning {
    background-color: #fff3cd !important; /* Light Yellow */
    color: #856404 !important; /* Dark Yellow Text */
    font-weight: 600;
    padding: 4px 8px !important;
    border-left: 3px solid #ffc107 !important;
    transition: all 0.2s ease !important;
    letter-spacing: -0.02em !important;
}

/* Icons for status indicators */
.table-danger::before {
    content: "\f06a"; /* Exclamation icon */
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    margin-right: 5px !important;
    font-size: 0.8rem !important;
}

td.bg-orange::before {
    content: "\f071"; /* Warning icon */
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    margin-right: 5px !important;
    font-size: 0.8rem !important;
}

.table-warning::before {
    content: "\f201"; /* Lightning icon */
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    margin-right: 5px !important;
    font-size: 0.8rem !important;
}

p.card-text {
    color:rgb(59, 59, 59) !important;
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