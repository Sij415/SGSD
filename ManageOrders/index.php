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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php
// Include database connection
$required_role = 'admin,staff,driver';
include('../check_session.php');
include '../dbconnect.php';
include '../log_functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch user details from session
$user_email = $_SESSION['email'];

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
            Products.Product_Type, 
            Products.Unit,
            Orders.Status, 
            Orders.Order_Type,
            Orders.Quantity,
            Orders.Total_Price,
            Orders.Notes
          FROM Orders
          INNER JOIN Users ON Orders.User_ID = Users.User_ID
          INNER JOIN Products ON Orders.Product_ID = Products.Product_ID
          INNER JOIN Transactions ON Orders.Transaction_ID = Transactions.Transaction_ID
          LEFT JOIN Customers ON Transactions.Customer_ID = Customers.Customer_ID";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Fetch customer list for dropdown
$customer_query = "SELECT Customer_ID, First_Name, Last_Name FROM Customers";
$customer_result = $conn->query($customer_query);
$customers = $customer_result->fetch_all(MYSQLI_ASSOC);

// Handle adding a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_id = $_POST['Customer_ID'];
    $product_id = $_POST['Product_ID'];
    $status = $_POST['Status'];
    $order_type = $_POST['Order_Type'];
    $quantity = $_POST['Quantity'];
    $notes = $_POST['Notes'];

    // Fetch Product Price
    $query = "SELECT Price FROM Products WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();
    
    $total_price = $price * $quantity;

    // Insert Transaction
    $query = "INSERT INTO Transactions (Customer_ID, Date, Time) VALUES (?, NOW(), NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $transaction_id = $stmt->insert_id;
    $stmt->close();

    // Fetch stock details
    $query = "SELECT Old_Stock, New_Stock FROM Stocks WHERE Product_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($old_stock, $new_stock);
    $stmt->fetch();
    $stmt->close();

    // Check if stock entry exists for the product
    if ($old_stock === null && $new_stock === null) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No stock entry found for the selected product.'
                });
            });
        </script>";
    } else if ($quantity > ($old_stock + $new_stock) && $order_type !== "Inbound") {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Insufficient stock available for this product.'
                });
            });
        </script>";
    } else {
        // Move New_Stock to Old_Stock if Old_Stock is 0
        if ($old_stock == 0) {
            $query = "UPDATE Stocks SET Old_Stock = New_Stock, New_Stock = 0 WHERE Product_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
            $old_stock = $new_stock;
            $new_stock = 0;
        }

        // Update stock based on order type and status
        if ($order_type === "Inbound" && $status === "Delivered") {
            // Move New_Stock to Old_Stock and add quantity to New_Stock
            $query = "UPDATE Stocks SET Old_Stock = Old_Stock + New_Stock, New_Stock = ? WHERE Product_ID = ?";
            $stmt = $conn->prepare($query);
            $new_stock_after_update = $quantity;
            $stmt->bind_param("ii", $new_stock_after_update, $product_id);
        } elseif ($order_type === "Inbound") {
            // Inbound Order: Add to New_Stock
            $query = "UPDATE Stocks SET New_Stock = New_Stock + ? WHERE Product_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $quantity, $product_id);
        } else {
            // Outbound Order: Deduct from Old_Stock first, then New_Stock if needed
            if ($quantity <= $old_stock) {
                $query = "UPDATE Stocks SET Old_Stock = Old_Stock - ? WHERE Product_ID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $quantity, $product_id);
            } else {
                $remaining_qty = $quantity - $old_stock;
                $query = "UPDATE Stocks SET Old_Stock = 0, New_Stock = New_Stock - ? WHERE Product_ID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $remaining_qty, $product_id);
            }
        }
        
        $stmt->execute();
        $stmt->close();

        // Insert Order
        $query = "INSERT INTO Orders (User_ID, Product_ID, Status, Order_Type, Quantity, Total_Price, Notes, Transaction_ID) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissidsi", $user_id, $product_id, $status, $order_type, $quantity, $total_price, $notes, $transaction_id);
        if (!$stmt->execute()) {
            error_log("Error inserting order: " . $stmt->error);
        }
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Update Transactions with Order_ID
        $query = "UPDATE Transactions SET Order_ID = ? WHERE Transaction_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $order_id, $transaction_id);
        if (!$stmt->execute()) {
            error_log("Error updating transaction with order ID: " . $stmt->error);
        }
        $stmt->close();

        $query = "SELECT Product_Name FROM Products WHERE Product_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();


      // Insert Notification Logic for New Order
      if ($order_type === "Inbound") {
        $notification_message = "New Inbound Order: $product_name, Quantity: $quantity, Status: $status.";

        // Notify Driver
        $notif_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES ('driver', ?, NOW(), 0)";
        $stmt = $conn->prepare($notif_query);
        $stmt->bind_param("s", $notification_message);
        $stmt->execute();
        $stmt->close();
    }

    
        logActivity($conn, $user_id, "Created a new Order Product: $product_name, Quantity: $quantity, Order Type: $order_type, Status: $status, Notes: $notes");
    

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();


        
    }

}

// Handle editing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    $order_id = $_POST['Order_ID'];
    $status = $_POST['New_Status'];

    if ($user_role === 'driver') {
        $query = "UPDATE Orders SET Status = ? WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Fetch updated order details
        $customer_id = $_POST['New_CustomerID'];  // Directly from form
        $product_id = $_POST['New_ProductID'];    // Directly from form
        $order_type = $_POST['New_OrderType'];
        $quantity = $_POST['New_Quantity'];
        $notes = $_POST['New_Notes'];

        // Get Product_ID and Price
        $query = "SELECT Price FROM Products WHERE Product_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($price);
        $stmt->fetch();
        $stmt->close();

        // Get Current Quantity from Orders
        $query = "SELECT Quantity FROM Orders WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($current_quantity);
        $stmt->fetch();
        $stmt->close();

        // Get Stock Levels
        $query = "SELECT Old_Stock, New_Stock FROM Stocks WHERE Product_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($old_stock, $new_stock);
        $stmt->fetch();
        $stmt->close();

        // Check if stock entry exists for the product
        if ($old_stock === null && $new_stock === null) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No stock entry found for the selected product.'
                    }).then(function() {
                        window.location.href = window.location.href.split('?')[0] + '?reload=true';
                    });
                });
            </script>";
            exit();
        }

        // Move New_Stock to Old_Stock if Old_Stock is 0
        if ($old_stock == 0) {
            $query = "UPDATE Stocks SET Old_Stock = New_Stock, New_Stock = 0 WHERE Product_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
            $old_stock = $new_stock;
            $new_stock = 0;
        }

        // Check if the combined stock is sufficient
        $total_stock = $old_stock + $new_stock;
        $quantity_difference = $quantity - $current_quantity;

        if ($order_type !== "Inbound" && $quantity_difference > $total_stock) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Quantity exceeds available stock.'
                    }).then(function() {
                        window.location.href = window.location.href.split('?')[0] + '?reload=true';
                    });
                });
            </script>";
            exit();
        }

        if ($quantity_difference != 0) {
            // If quantity is changed, validate and update stock
            if ($quantity_difference > 0) {
                // If new quantity is greater, validate and update stock
                if ($order_type === "Inbound") {
                    $query = "UPDATE Stocks SET New_Stock = New_Stock + ? WHERE Product_ID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $quantity_difference, $product_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    if ($quantity_difference <= $old_stock) {
                        $query = "UPDATE Stocks SET Old_Stock = Old_Stock - ? WHERE Product_ID = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("ii", $quantity_difference, $product_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $remaining_qty = $quantity_difference - $old_stock;
                        $query = "UPDATE Stocks SET Old_Stock = 0, New_Stock = New_Stock - ? WHERE Product_ID = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("ii", $remaining_qty, $product_id);
                        $stmt->execute();
                        $stmt->close();

                        // Move remaining New_Stock to Old_Stock if Old_Stock is depleted
                        $query = "UPDATE Stocks SET Old_Stock = New_Stock, New_Stock = 0 WHERE Product_ID = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            } else {
                // If new quantity is less, add back to Old_Stock
                $quantity_difference = abs($quantity_difference);
                $query = "UPDATE Stocks SET Old_Stock = Old_Stock + ? WHERE Product_ID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $quantity_difference, $product_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Update Order
        $total_price = $price * $quantity;
        $query = "UPDATE Orders 
                SET Product_ID = ?, Status = ?, Order_Type = ?, Quantity = ?, Total_Price = ?, Notes = ? 
                WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issidsi", $product_id, $status, $order_type, $quantity, $total_price, $notes, $order_id);
        $stmt->execute();
        $stmt->close();

        $query = "UPDATE Transactions
        SET Customer_ID = ?
        WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $customer_id, $order_id);
        $stmt->execute();
        $stmt->close();
        

        $query = "SELECT Product_Name FROM Products WHERE Product_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();



        if ($status === "Delivered") {
            $notification_message = "Order Delivered: $product_name, Quantity: $quantity.";

            // Notify Admin
            $notif_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES ('admin', ?, NOW(), 0)";
            $stmt = $conn->prepare($notif_query);
            $stmt->bind_param("s", $notification_message);
            $stmt->execute();
            $stmt->close();

            // Notify Staff
            $notif_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES ('staff', ?, NOW(), 0)";
            $stmt = $conn->prepare($notif_query);
            $stmt->bind_param("s", $notification_message);
            $stmt->execute();
            $stmt->close();

            // Notify Driver
            $notif_query = "INSERT INTO Notifications (Role, Message, Created_At, cleared) VALUES ('driver', ?, NOW(), 0)";
            $stmt = $conn->prepare($notif_query);
            $stmt->bind_param("s", $notification_message);
            $stmt->execute();
            $stmt->close();
        }











    
        logActivity($conn, $user_id, "Edited a Order Product: $product_name, Quantity: $quantity, Order Type: $order_type, Status: $status, Notes: $notes");
    





















        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Avoid infinite loop by checking the 'reload' parameter
if (isset($_GET['reload']) && $_GET['reload'] == 'true') {
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Handle deleting orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_orders'])) {
    $order_ids = json_decode($_POST['order_ids']);

    foreach ($order_ids as $order_id) {
        // Fetch the transaction details (Transaction_ID)
        $query = "SELECT Transaction_ID FROM Transactions WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($transaction_id);
        $stmt->fetch();
        $stmt->close();

        if ($transaction_id) {
            // Fetch order details from the Orders table before deletion
            $query = "SELECT Product_ID, Quantity, Order_Type FROM Orders WHERE Order_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->bind_result($product_id, $quantity, $order_type);
            $stmt->fetch();
            $stmt->close();

            // Update stock based on order type
            if ($order_type === "Inbound") {
                // Inbound Order: Deduct from New_Stock
                $query = "UPDATE Stocks SET New_Stock = New_Stock - ? WHERE Product_ID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $quantity, $product_id);
            } else {
                // Outbound Order: Add back to Old_Stock
                $query = "UPDATE Stocks SET Old_Stock = Old_Stock + ? WHERE Product_ID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $quantity, $product_id);
            }
            $stmt->execute();
            $stmt->close();



            $query = "SELECT Product_Name FROM Products WHERE Product_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->bind_result($product_name);
            $stmt->fetch();
            $stmt->close();
        
            logActivity($conn, $user_id, "Deleted a Order Product: $product_name, Quantity: $quantity, Order Type: $order_type");
        

            

            // Delete transaction (this will cascade and delete the order)
            $query = "DELETE FROM Transactions WHERE Transaction_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();
            $stmt->close();
        }
        
    }



    









    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle logout when the form is submitted
// Handle logout when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    logActivity($conn, $user_id, "Logged out");
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../Login"); // Redirect to login page
    exit();
}
// Fetch Product Names for dropdown
$product_query = "SELECT Product_ID, Product_Name, Product_Type, Unit FROM Products";
$product_result = $conn->query($product_query);
$products = $product_result->fetch_all(MYSQLI_ASSOC);
?>

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
    var table = document.getElementById("OrdersTable");
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
    const table = document.getElementById('OrdersTable');
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

    document.addEventListener("DOMContentLoaded", function () {
    // Get the first name and last name input fields
        const firstNameInput = document.getElementById("Customer_FName");
        const lastNameInput = document.getElementById("Customer_LName");

        // Ensure the last name field is disabled
        if (lastNameInput) {
            lastNameInput.disabled = true;
        }

        // Add event listener for first name input
        if (firstNameInput) {
            firstNameInput.addEventListener("input", function () {
                let firstName = firstNameInput.value.trim();

                if (firstName !== "") {
                    // Make AJAX request to fetch last name
                    fetch(`manageorders.php?first_name=${encodeURIComponent(firstName)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lastNameInput.value = data.last_name;
                            } else {
                                lastNameInput.value = ""; // Clear if no match found
                            }
                        })
                        .catch(error => console.error("Error fetching last name:", error));
                } else {
                    lastNameInput.value = ""; // Clear if input is empty
                }
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // Get the product name and product type input fields
        const productNameInput = document.getElementById("Product_Name");
        const productTypeInput = document.getElementById("Product_Type");
        //const productIDInput = document.getElementById("Product_ID"); // Hidden field for Product ID

        // Ensure the product type field is disabled
        if (productTypeInput) {
            productTypeInput.disabled = true;
        }

        // Add event listener for product name input
        if (productNameInput) {
            productNameInput.addEventListener("input", function () {
                let productName = productNameInput.value.trim();

                if (productName !== "") {
                    // Make AJAX request to fetch product type and ID
                    fetch(`manageorders.php?product_name=${encodeURIComponent(productName)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                productTypeInput.value = data.product_type;
                                productIDInput.value = data.product_id; // Store product ID
                            } else {
                                productTypeInput.value = ""; // Clear if no match found
                                productIDInput.value = "";
                            }
                        })
                        .catch(error => console.error("Error fetching product type:", error));
                } else {
                    productTypeInput.value = ""; // Clear if input is empty
                    productIDInput.value = "";
                }
            });
        }
    });

    // Edit order modal functionality
$(document).ready(function () {
    $("a[data-bs-target='#editOrderModal']").click(function () {
        var orderID = $(this).data("order-id");
        var customerFName = $(this).data("customer-first-name");
        var customerLName = $(this).data("customer-last-name");
        var productName = $(this).data("product-name");
        var productType = $(this).data("product-type");
        var productUnit = $(this).data("product-unit");
        var quantity = $(this).data("quantity");
        var orderType = $(this).data("order-type");
        var status = $(this).data("status");
        var notes = $(this).data("notes");

        // Construct the exact product text format used in the dropdown
        var formattedProductText = productName + " (" + productUnit + ") - " + productType;

        // Populate the modal fields
        $("#edit_order_id").val(orderID);
        $("#edit_quantity").val(quantity);
        $("#edit_order_type").val(orderType);
        $("#edit_status").val(status);
        $("#edit_notes").val(notes);

        // Match the formatted text with the correct option in the dropdown
        $("#editProduct option").each(function () {
            if ($(this).text().trim() === formattedProductText.trim()) {
                $(this).prop("selected", true);
                return false; // Stop looping once a match is found
            }
        });

        // Handle customer fields based on order type
        if (orderType === "INBOUND ORDER") {
            $("#edit_customer_fname, #edit_customer_lname").val("N/A").prop("disabled", true);
        } else {
            $("#edit_customer_fname").val(customerFName).prop("disabled", false);
            $("#edit_customer_lname").val(customerLName).prop("disabled", false);
        }
    });
});


        // PDF generation functionality
        $('.PDFdata').click(function(e) {
            e.preventDefault(); // Prevent default link behavior

            // Collect data from the clicked link
            const managedBy = $(this).data('managed-by');
            const customerName = $(this).data('customer-name');
            const productName = $(this).data('product');
            const status = $(this).data('status');
            const orderType = $(this).data('order-type');
            const quantity = $(this).data('quantity');
            const totalPrice = $(this).data('total-price');
            const notes = $(this).data('notes');

            // Populate the hidden form fields
            $('#managed_by').val(managedBy);
            $('#customer_name').val(customerName);
            $('#product_name').val(productName);
            $('#status').val(status);
            $('#order_type').val(orderType);
            $('#quantity').val(quantity);
            $('#total_price').val(totalPrice);
            $('#notes').val(notes);

            // Submit the form
            $('#pdfForm').submit();
        });

        // Attach functions to the window so they can be called from HTML
        window.sortTable = sortTable;
        window.searchTable = searchTable;
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

        // Check if there are any orders
        if ($("#OrdersTable tbody tr").length > 0 && $("#OrdersTable tbody tr td").length > 1) {
            // Add checkbox column to table header
            $("#OrdersTable thead tr").prepend('<th class="checkbox-column"><button type="button" class="btn btn-sm custom-btn" id="select-all-btn" onclick="document.getElementById(\'select-all\').click()">Select All <input type="checkbox" id="select-all" style="visibility:hidden; position:absolute;"></button></th>');

            // Add checkboxes to all rows
            $("#OrdersTable tbody tr").prepend(function() {
                var orderId = $(this).data("order-id");
                return '<td class="checkbox-column"><input type="checkbox" class="row-checkbox" value="' + orderId + '"></td>';
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
        $('#editOrderModal').on('hidden.bs.modal', function() {
            $(".row-checkbox").prop("checked", false);
            $("#select-all").prop("checked", false);
            selectedItems = [];
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

        // USE THIS FUNC TO REPLACE DELETEION FUNCTION IN MANAGE PRODUCTS AND STOCKS

        // Handle delete confirmation
        $("#delete-confirmed").click(function() {
            // Initialize an array to store all order IDs
            let orderIds = [];
            
            // Go through all selected items
            selectedItems.forEach(item => {
                // Check if item is a table row (has checkbox)
                const checkbox = $(item).find(".row-checkbox");
                if (checkbox.length > 0) {
                    orderIds.push(checkbox.val());
                } 
                // Check if item is a card
                else if ($(item).hasClass("card")) {
                    const cardOrderId = $(item).data("order-id");
                    if (cardOrderId) {
                        orderIds.push(cardOrderId);
                    }
                }
            });
            
            // Remove any duplicates
            orderIds = [...new Set(orderIds)];
            
            console.log("Selected Order IDs: ", orderIds); // Debug log to check order IDs
            $("#order_ids").val(JSON.stringify(orderIds));
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
<!-- Logout Button -->
<a href="#" class="logout" onclick="document.getElementById('logoutForm').submit();">
<i class="fa-solid fa-sign-out-alt"></i>
    <span>Log out</span>
</a>

<!-- Hidden Logout Form -->
<form id="logoutForm" method="POST" action="">
    <input type="hidden" name="logout" value="1">
</form>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <!-- PLEASE PULL THIS INLINE, THIS IS THE THIRD TIME THAT THIS IS IMPLEMENTED -->
    <div id="content" style="max-height: 750px; overflow-y: auto;">
    
        <nav class="navbar navbar-expand-lg navbar-light bg-light" id="mainNavbar">
            <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-info ml-1" data-toggle="tooltip" data-placement="bottom" title="Toggle Sidebar">
            <i class="fas fa-align-left"></i>
            </button>
            <a href="../Manual/Manual-Placeholder.pdf" class="btn btn-dark ml-2 d-flex justify-content-center align-items-center" id="manualButton" data-toggle="tooltip" data-placement="bottom" target="_blank" title="View Manual">
                <i class="fas fa-file-alt"></i>
            </a>
            <!-- <button class="btn btn-primary ml-auto" type="button" data-toggle="modal" data-target="#editOrderModal">
                Test Edit Modal
            </button> -->
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
                    <!-- Display Error Message -->
                    <?php if (!empty($add_error_message)): ?>
                        <div class="alert alert-danger text-center"><?php echo $add_error_message; ?></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="Customer" class="form-label">Customer Name</label>
                        <select class="form-control" id="Customer" name="Customer_ID" style="height: fit-content;" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= htmlspecialchars($customer['Customer_ID']) ?>">
                                    <?= htmlspecialchars($customer['First_Name'] . ' ' . $customer['Last_Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Product" class="form-label">Product</label>
                        <select class="form-control" id="product_id" name="Product_ID" style="height: fit-content;" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= htmlspecialchars($product['Product_ID']) ?>">
                                    <?= htmlspecialchars($product['Product_Name'] . ' (' . $product['Unit'] . ') - ' . $product['Product_Type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Status" class="form-label">Status</label>
                        <select class="form-control" id="Status" name="Status" style="height: fit-content;" required>
                            <option value="">Select Status</option>
                            <option value="To Pick Up">To Pick Up</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Order_Type" class="form-label">Order Type</label>
                        <select name="Order_Type" id="Order_Type" class="form-control" style="height: fit-content;" required>
                            <option value="">Select Order Type</option>
                            <option value="Inbound">Inbound</option>
                            <option value="Outbound">Outbound</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Quantity" class="form-label">Quantity</label>
                        <input type="number" name="Quantity" id="Quantity" class="form-control" required placeholder="Enter quantity" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="Notes" class="form-label">Notes</label>
                        <textarea maxlength="250" class="form-control" id="Notes" name="Notes" rows="3" placeholder="Enter notes" oninput="updateCharacterCount()"></textarea>
                        <script>
                            function updateCharacterCount() {
                                const textarea = document.getElementById('Notes');
                                const charCount = document.getElementById('charCount');
                                charCount.textContent = `${textarea.value.length}/250`;
                            }

                            // Disable Customer Name when Inbound is selected
                            document.addEventListener("DOMContentLoaded", function () {
                                const orderType = document.getElementById("Order_Type");
                                const customerField = document.getElementById("Customer");

                                orderType.addEventListener("change", function () {
                                    if (this.value === "Inbound") {
                                        customerField.disabled = true;
                                        customerField.value = ""; // Clear selected customer
                                    } else {
                                        customerField.disabled = false;
                                    }
                                });
                            });
                        </script>
                        <div class="d-flex justify-content-between">
                            <small class="form-text text-muted">Maximum 250 characters. Special characters will be escaped.</small>
                            <div id="charCount" class="form-text text-muted" style="font-size: 12.6px;">0/250</div>
                        </div>
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
                    <?php if (!empty($edit_error_message)): ?>
                        <div class="alert alert-danger text-center"><?php echo $edit_error_message; ?></div>
                    <?php endif; ?>
                    <?php if ($user_role === 'staff') : ?>
                        <p class="text-danger text-center fw-bold">You are not permitted to edit orders.</p>
                    <?php else: ?>
                        <input type="hidden" id="edit_order_id" name="Order_ID">
                        
                        <!-- Customer Name -->
                        <div class="mb-3">
                            <label for="editCustomer" class="form-label">Customer Name</label>




                            <select class="form-control" id="editCustomer" name="New_CustomerID" style="height: fit-content;" required>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= htmlspecialchars($customer['Customer_ID']) ?>">
                                        <?= htmlspecialchars($customer['First_Name'] . ' ' . $customer['Last_Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>

                        <!-- Product -->
                        <div class="mb-3">
                            <label for="editProduct" class="form-label">Product</label>
                            <select class="form-control" id="editProduct" name="New_ProductID" style="height: fit-content;" required>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= htmlspecialchars($product['Product_ID']) ?>">
                                        <?= htmlspecialchars($product['Product_Name'] . ' (' . $product['Unit'] . ') - ' . $product['Product_Type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="New_Quantity" style="height: fit-content;" required placeholder="Enter quantity" min="0">
                        </div>

                        <!-- Order Type -->
                        <div class="mb-3">
                            <label for="edit_order_type" class="form-label">Order Type</label>
                            <select class="form-control" id="edit_order_type" name="New_OrderType" style="height: fit-content;" required>
                                <option value="Inbound">Inbound</option>
                                <option value="Outbound">Outbound</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_status" name="New_Status" style="height: fit-content;" required>
                                <option value="To Pick Up">To Pick Up</option>
                                <option value="In Transit">In Transit</option>
                                <option value="Delivered">Delivered</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea maxlength="250" class="form-control" id="edit_notes" name="New_Notes" rows="3" placeholder="Enter notes" oninput="updateCharacterCountEdit()"></textarea>
                            
                            <div class="d-flex justify-content-between">
                                <small class="form-text text-muted">Maximum 250 characters. Special characters will be escaped.</small>
                                <div class="form-text text-muted" id="editCharCount">
                                    <?php echo isset($row['Notes']) ? strlen($row['Notes']) : 0; ?>/250
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn custom-btn" data-bs-dismiss="modal" style="background-color: #e8ecef; color: #495057;">Close</button>
                    <?php if ($user_role !== 'staff') : ?>
                        <button id="delete-selected-btn-edit" type="button" class="btn custom-btn btn-danger d-md-none">Delete</button>
                        <button type="submit" name="edit_order" class="btn custom-btn">Save Changes</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const orderType = document.getElementById("edit_order_type");
    const customerField = document.getElementById("editCustomer");

    function updateCustomerField() {
        customerField.disabled = orderType.value === "Inbound";
        if (customerField.disabled) customerField.value = ""; // Clear selection if disabled
    }

    // Run the function when modal opens
    $(document).on('shown.bs.modal', '#editOrderModal', function () {
        updateCustomerField();
    });

    // Update when user changes order type
    orderType.addEventListener("change", updateCustomerField);
});

// Character Counter for Notes
function updateCharacterCountEdit() {
    const textarea = document.getElementById('edit_notes');
    document.getElementById('editCharCount').textContent = `${textarea.value.length}/250`;
}
</script>


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
            <ul class="pl-0">
                    <li style="font-size: 1em; background-color: #5dade2; color: #ffffff; padding: 5px; border-radius: 5px; list-style-type: none; margin-bottom: 5px; border: 1px solid #3498db;">
                        <i class="fas fa-arrow-right"></i> <span>Blue</span> = Outbound
                    </li>
                    <li style="font-size: 1em; background-color: #58d68d; color: #ffffff; padding: 5px; border-radius: 5px; list-style-type: none; margin-bottom: 5px; border: 1px solid #2ecc71;">
                        <i class="fas fa-arrow-left"></i> <span>Green</span> = Inbound
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
<!-- Hidden Form for Deletion -->
 <!-- Hidden Form for Deletion -->
<form id="deleteForm" method="POST" action="" style="display:none;">
    <input type="hidden" name="delete_orders" value="1">
    <input type="hidden" name="order_ids" id="order_ids">
</form>

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
            <div class="table-container" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); position: relative;">
                <div class="scroll-indicator" style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: linear-gradient(transparent, rgba(111, 160, 98, 0.2)); opacity: 0; pointer-events: none; transition: opacity 0.3s ease;"></div>
            <div class="table-responsive d-none d-md-block">
                
                <table class="table table-striped table-bordered" id="OrdersTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(1)">Managed by <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(2)">Customer's First Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(3)">Customer's Last Name <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(4)">Product<i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(5)">Status <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(6)">Order Type <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(7)">Quantity <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(8)">Total Price <i class="bi bi-arrow-down-up"></i></th>
                            <th onclick="sortTable(9)">Notes <i class="bi bi-arrow-down-up"></i></th>
                            <th>Edit</th>
                            <th>Generate Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                $orderType = $row['Order_Type']; // Fetch Order Type
                                $orderClass = ($orderType == 'Outbound') ? 'outbound' : 'inbound';
                        ?>
                                <tr data-order-id="<?php echo htmlspecialchars($row['Order_ID']); ?>">
                                    <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Customer_FName'] ?? "N/A", ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Customer_LName'] ?? "N/A", ENT_QUOTES, 'UTF-8'); ?></td>


                                    <td><?php echo htmlspecialchars($row['Product_Name'] . ' (' . $row['Unit'] . ') - ' . $row['Product_Type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                    <td class="order-type <?php echo $orderClass; ?>"><?php echo $orderType; ?></td>
                                    <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                                    <td>₱<?php echo number_format(htmlspecialchars($row['Total_Price']), 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['Notes']); ?></td>
                                    <td class="text-center"> 
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editOrderModal"
                                        data-order-id="<?php echo htmlspecialchars($row['Order_ID']); ?>" 
                                        data-customer-first-name="<?php echo htmlspecialchars($row['Customer_FName']); ?>" 
                                        data-customer-last-name="<?php echo htmlspecialchars($row['Customer_LName']); ?>" 
                                        data-product-name="<?php echo htmlspecialchars($row['Product_Name']); ?>" 
                                        data-product-type="<?php echo htmlspecialchars($row['Product_Type']); ?>" 
                                        data-product-unit="<?php echo htmlspecialchars($row['Unit']); ?>"
                                        data-quantity="<?php echo htmlspecialchars($row['Quantity']); ?>"
                                        data-status="<?php echo htmlspecialchars($row['Status']); ?>" 
                                        data-order-type="<?php echo htmlspecialchars($row['Order_Type']); ?>"
                                        data-notes="<?php echo htmlspecialchars($row['Notes']); ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    </td>
                                    <td> 
                                    <a href="#" class="PDFdata"
                                            data-managed-by="<?php echo $row['Full_Name']; ?>" 
                                            data-customer-name="<?php echo $row['Customer_FName'] . ' ' . $row['Customer_LName']; ?>"
                                            data-product="<?php echo $row['Product_Name'].' ('.$row['Product_Type'].')'; ?>"
                                            data-status="<?php echo $row['Status']; ?>" 
                                            data-order-type="<?php echo $row['Order_Type']; ?>"
                                            data-quantity="<?php echo $row['Quantity']; ?>"
                                            data-total-price="<?php echo $row['Total_Price'] ?>"
                                            data-notes="<?php echo $row['Notes'] ?>">
                                            <i class="bi bi-envelope-paper"></i>
                                    </a>
                                </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
            <td colspan="11" class="text-center">No orders found.</td>
        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p id="noResultsMessage" style="display: none; text-align: center; font-weight:bold; margin-top: 10px;">No order found.</p>
            </div>
            <!-- Hidden Form -->
        <form id="pdfForm" action="../TransactionRecord/generate-pdf.php" method="POST" target="_blank" style="display:none;">
            <input type="hidden" name="managed_by" id="managed_by">
            <input type="hidden" name="customer_name" id="customer_name">
            <input type="hidden" name="product_name" id="product_name">
            <input type="hidden" name="status" id="status">
            <input type="hidden" name="order_type" id="order_type">
            <input type="hidden" name="quantity" id="quantity">
            <input type="hidden" name="total_price" id="total_price">
            <input type="hidden" name="notes" id="notes">
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
                            data-product-type="<?php echo $row['Product_Type']; ?>"
                            data-product-unit="<?php echo $row['Unit']; ?>"
                            data-status="<?php echo $row['Status']; ?>" 
                            data-order-type="<?php echo $row['Order_Type']; ?>"
                            data-quantity="<?php echo $row['Quantity']; ?>"
                            data-total-price="<?php echo $row['Total_Price']; ?>"
                            data-notes="<?php echo $row['Notes']; ?>"
                            >
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['Product_Name'] . ' (' . $row['Unit'] . ') - ' . $row['Product_Type']); ?></h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="card-text"><strong>Managed by:</strong> <?php echo htmlspecialchars($row['Full_Name']); ?></p>
                                        </div>
                                        <div class="col-6">
    <p class="card-text"><strong>Customer's First Name:</strong> <?php echo htmlspecialchars($row['Customer_FName'] ?? "N/A", ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<div class="col-6">
    <p class="card-text"><strong>Customer's Last Name:</strong> <?php echo htmlspecialchars($row['Customer_LName'] ?? "N/A", ENT_QUOTES, 'UTF-8'); ?></p>
</div>

                                        <div class="col-6">
                                            <p class="card-text"><strong>Status:</strong> <?php echo htmlspecialchars($row['Status']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Order Type:</strong> <?php echo htmlspecialchars($row['Order_Type']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Quantity:</strong> <?php echo htmlspecialchars($row['Quantity']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Total Price:</strong> <?php echo htmlspecialchars($row['Total_Price']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="card-text"><strong>Notes:</strong> <?php echo htmlspecialchars($row['Notes']); ?></p>
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

/* Order Type Styling */
td.order-type {
    margin-top: 32px !important;
    margin-left: 8px !important;
    margin-right: 8px !important;
    padding: 2px 4px !important; /* Decreased padding */
    border-radius: 6px !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
    display: block !important;
    align-items: center !important;
    justify-content: center !important; /* Center content horizontally */
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    text-align: center !important;
    letter-spacing: -0.02em !important;
}

.order-type.outbound {
    background-color: #3498db !important;
    color: white !important;
    border-left: 3px solid #2980b9 !important;
}

.order-type.outbound::before {
    content: "\f061" !important; /* Arrow right icon */
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    margin-right: 5px !important;
    font-size: 0.8rem !important;
}

.order-type.inbound {
    background-color: #2ecc71 !important;
    color: white !important;
    border-left: 3px solid #27ae60 !important;
}

.order-type.inbound::before {
    content: "\f060" !important; /* Arrow left icon */
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    margin-right: 5px !important;
    font-size: 0.8rem !important;
}

.order-type:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.15) !important;
}
/* Style for status badges */
td:nth-child(5) {
    font-weight: 500;
    text-align: center; /* Center the text */
}

td:nth-child(5):contains("To Pick Up") {
    color: #f39c12;
}

td:nth-child(5):contains("In Transit") {
    color: #3498db;
}

td:nth-child(5):contains("Delivered") {
    color: #2ecc71;
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