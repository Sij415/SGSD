<?php
// Include the database connection file
include 'dbconnect.php';

// Handle adding stock
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
        $error_message = "Error adding stock: " . $stmt->error;
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
    <title>Customer Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Customer Records</h1>

    <!-- Display success or error message -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"> <?php echo $success_message; ?> </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
    <?php endif; ?>

    <!-- Add Customer Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add Customer</button>

    <!-- Customer Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Customer ID</th>
            <th>Product ID</th>
            <th>First_Name</th>
            <th>Last_Name</th>
            <th>Contact Number</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['Customer_ID']; ?></td>
                <td><?php echo $row['Product_ID']; ?></td>
                <td><?php echo $row['First_Name']; ?></td>
                <td><?php echo $row['Last_Name']; ?></td>
                <td><?php echo $row['Contact_Number']; ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCustomerModal" 
                            data-customer-id="<?php echo $row['Customer_ID']; ?>" 
                            data-first-name="<?php echo $row['First_Name']; ?>" 
                            data-last-name="<?php echo $row['Last_Name']; ?>"
                            data-contact-number="<?php echo $row['Contact_Number']; ?>">
                        Edit
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer ID</label>
                        <input type="number" class="form-control" id="Customer_ID" name="Customer_ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product ID</label>
                        <input type="number" class="form-control" id="Product_ID" name="Product_ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="First_Name" name="First_Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="Last_Name" name="Last_Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="Contact_Number" name="Contact_Number" required>
                    </div>
                    <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="edit_customer_id" name="Customer_ID">
                    <div class="mb-3">
                        <label for="edit_new_stock" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="New_FirstName">
                    </div>
                    <div class="mb-3">
                        <label for="edit_threshold" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="New_LastName">
                    </div>
                    <div class="mb-3">
                        <label for="edit_contactnum" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="edit_contact_num" name="New_ContactNum">
                    </div>
                    <button type="submit" name="edit_customer" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate edit modal with existing data
    const editStockModal = document.getElementById('editCustomerModal');
    editStockModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const customerId = button.getAttribute('data-customer-id');
        const firstName = button.getAttribute('data-first-name');
        const lastName = button.getAttribute('data-last-name');
        const contactNumber = button.getAttribute('data-contact-number');

        document.getElementById('edit_customer_id').value = customerId;
        document.getElementById('edit_first_name').value = firstName;
        document.getElementById('edit_last_name').value = lastName;
        document.getElementById('edit_contact_num').value = contactNumber;
    });

    // Handle adding a customer
    document.getElementById('addCustomerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Customer record added successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to add customer record: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Handle editing a customer record
    document.getElementById('editCustomerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('your_php_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Customer record updated successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Failed to update customer record: ' + data);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
