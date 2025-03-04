<?php
// Include database connection
$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];
//echo 'User ID: ' . $_SESSION['user_id'];

// Get the user's first name from the database
$query = "SELECT First_Name, User_ID FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_first_name, $user_id);
$stmt->fetch();
$stmt->close();


// Fetch order data from the database
$query = "SELECT 
            Orders.Order_ID, 
            CONCAT(Users.First_Name, ' ', Users.Last_Name) AS Full_Name, 
            Customers.First_Name AS Customer_Name, 
            Products.Product_Name, 
            Orders.Status, 
            Orders.Order_Type,
            Orders.Quantity,
            Orders.Total_Price
          FROM Orders
          INNER JOIN Users ON Orders.User_ID = Users.User_ID
          INNER JOIN Products ON Orders.Product_ID = Products.Product_ID
          INNER JOIN Transactions ON Orders.Order_ID = Transactions.Order_ID
          INNER JOIN Customers ON Transactions.Customer_ID = Customers.Customer_ID";


$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Check for query errors
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle adding a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
  $customer_name = $_POST['Customer_Name'];
  $product_name = $_POST['Product_Name'];
  $status = $_POST['Status'];
  $order_type = $_POST['Order_Type'];
  $quantity = $_POST['Quantity'];
  $totalprice = $_POST['Total_Price'];

  // Validate input
  if (!empty($customer_name) && !empty($product_name) && !empty($quantity) && !empty($order_type)) {
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

      // Insert into Orders table
      $query = "INSERT INTO Orders (User_ID, Product_ID, Status, Order_Type, Quantity, Total_Price) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("iissid", $user_id, $product_id, $status, $order_type, $quantity, $total_price);

      if ($stmt->execute()) {
          header("Location: " . $_SERVER['PHP_SELF']); // Reload page to show new data
          exit();
      } else {
          echo "<div class='alert alert-danger'>Error adding order: " . $conn->error . "</div>";
      }

      $stmt->close();
  } else {
      echo "<div class='alert alert-warning'>All fields are required.</div>";
  }
}

// Handle editing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    $order_id = $_POST['Order_ID'];
    $customer_name = $_POST['New_CustomerName'];
    $product_name = $_POST['New_ProductName'];
    $status = $_POST['New_Status'];
    $order_type = $_POST['New_OrderType'];

    // Validate input
    if (!empty($order_id) && !empty($customer_name) && !empty($product_name) && !empty($status) && !empty($order_type)) {
        // Get Customer_ID from Customers table
        $query = "SELECT Customer_ID FROM Customers WHERE First_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $customer_name);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        if (!$customer_id) {
            echo "<div class='alert alert-danger'>Customer not found.</div>";
            exit();
        }

        // Get Product_ID from Products table
        $query = "SELECT Product_ID FROM Products WHERE Product_Name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->bind_result($product_id);
        $stmt->fetch();
        $stmt->close();

        if (!$product_id) {
            echo "<div class='alert alert-danger'>Product not found.</div>";
            exit();
        }

        // Update Orders table
        $query = "UPDATE Orders SET User_ID = ?, Product_ID = ?, Status = ?, Order_Type = ? WHERE Order_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissi", $customer_id, $product_id, $status, $order_type, $order_id);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']); // Reload page to show updated data
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error updating order: " . $conn->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
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
  <title>Manage Orders</title>
  <style>
    .table-striped>tbody>tr:nth-child(odd)>td, 
    .table-striped>tbody>tr:nth-child(odd)>th {
      background-color: #f4f9f8;
    }
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
      left: -250px;
      transition: left 0.3s ease;
      z-index: 1000;
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
    @media (min-width: 768px) {
      .sidebar {
        position: relative;
        left: 0;
      }
      .content {
        margin-left: 1rem;
      }
      .toggle-btn {
        display: none;
      }
      .sidebar .close-btn {
        display: none;
      }
    }
    @media (max-width: 767.98px) {
      .sidebar .close-btn {
        display: block;
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
    <a class="sidebar-btn d-md-none" id="toggleBtn">☰</a>
    <a href="#" class="d-md-flex d-md-none ms-auto tooltip-btn"><i class="bi bi-file-earmark-break-fill"></i></a>
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
  <hr style="width: 75%; margin: 0 auto; padding: 12px;">
  <div class="mt-auto p-2">
    <div class="sidebar-usr">
      <div class="sidebar-pfp">
        <img src="https://upload.wikimedia.org/wikipedia/en/b/b1/Portrait_placeholder.png" alt="Sample Profile Picture">
      </div>
      <div class="sidebar-usrname">
        <h1><?php echo htmlspecialchars($user_first_name); ?></h1>
        <h2><?php echo htmlspecialchars($user_email); ?></h2>
      </div>
    </div>
    <div class="sidebar-options">
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
    </div>
  </div>
</div>

<div class="content">
  <!-- Add Order Modal -->
  <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="">
          <div class="modal-header">
            <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="customer_name" class="form-label">Customer Name</label>
              <input type="text" name="Customer_Name" id="Customer_Name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="product_name" class="form-label">Product Name</label>
              <input type="text" name="Product_Name" id="Product_Name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="add_status" class="form-label">Status</label>
              <select class="form-control" id="Status" name="Status">
                <option value="">Select Status</option>
                <option value="To Pick Up">To Pick Up</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="order_type" class="form-label">Order Type</label>
              <select name="Order_Type" id="Order_Type" class="form-control" required>
                <option value="">Select Order Type</option>
                <option value="Inbound">Inbound</option>
                <option value="Outbound">Outbound</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="quantity" class="form-label">Quantity</label>
              <input type="number" name="Quantity" id="Quantity" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_order" class="btn btn-primary">Add Order</button>
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
            <input type="hidden" id="edit_order_id" name="Order_ID">
            <div class="mb-3">
              <label for="edit_customer_name" class="form-label">Customer Name</label>
              <input type="text" class="form-control" id="edit_customer_name" name="New_CustomerName">
            </div>
            <div class="mb-3">
              <label for="edit_product_name" class="form-label">Product Name</label>
              <input type="text" class="form-control" id="edit_product_name" name="New_ProductName">
            </div>
            <div class="mb-3">
              <label for="edit_status" class="form-label">Status</label>
              <select class="form-control" id="edit_status" name="New_Status">
                <option value="">Select Status</option>
                <option value="To Pick Up">To Pick Up</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="edit_order_type" class="form-label">Order Type</label>
              <select class="form-control" id="edit_order_type" name="New_OrderType">
                 <option value="Inbound">Inbound</option>
                 <option value="Outbound">Outbound</option>
              </select>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="edit_order" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container mt-4">
    <h1><b>Manage Orders</b></h1>
    <h3>To view the orders in detail, click the product.</h3>

    <!-- Search Box -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <!-- Search Input Group -->
      <div class="input-group">
      <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput" onkeyup="searchTable()">
        <button class="btn btn-outline-secondary" type="button" id="search">
          <i class="fa fa-search"></i>
        </button>
      </div>

      <!-- Add Order Button -->
      <button class="add-btn ms-3" data-bs-toggle="modal" data-bs-target="#addOrderModal">Add Order</button>
    </div>

    <!-- Table Layout (Visible on larger screens) -->
    <div class="table-responsive d-none d-md-block">
      <table class="table table-striped table-bordered" id="OrdersTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)">Managed by <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(1)">Customer Name <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(2)">Product Name <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(3)">Status <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(4)">Order Type <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(5)">Quantity <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(6)">Total Price <i class="bi bi-arrow-down-up"></i></th>
            <th>Edit</th>
            <th>Generate Record</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Customer_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td><?php echo htmlspecialchars($row['Order_Type']); ?></td>
                <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['Total_Price']); ?></td>
                <td> <a href="" data-bs-toggle="modal" data-bs-target="#editOrderModal" data-order-id="<?php echo $row['Order_ID']; ?>" data-customer-name="<?php echo $row['Customer_Name']; ?>" data-product-name="<?php echo $row['Product_Name']; ?>" data-status="<?php echo $row['Status']; ?>" data-order-type="<?php echo $row['Order_Type']; ?>"><i class="bi bi-pencil-square"></i></a></td>
                <td> 
    <a href="#" class="PDFdata"
        data-managed-by="<?php echo $row['Full_Name']; ?>" 
        data-customer-name="<?php echo $row['Customer_Name']; ?>" 
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
             data-bs-toggle="modal" data-bs-target="#editOrderModal" data-order-id="<?php echo $row['Order_ID']; ?>" data-customer-name="<?php echo $row['Customer_Name']; ?>" data-product-name="<?php echo $row['Product_Name']; ?>" data-status="<?php echo $row['Status']; ?>" data-order-type="<?php echo $row['Order_Type']; ?>"
            
            
            
            >

              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($row['Product_Name']); ?></h5>
                <div class="row">
                  <div class="col-6">
                    <p class="card-text"><strong>Managed by:</strong> <?php echo htmlspecialchars($row['Full_Name']); ?></p>
                  </div>
                  <div class="col-6">
                    <p class="card-text"><strong>Customer Name:</strong> <?php echo htmlspecialchars($row['Customer_Name']); ?></p>
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



function searchTable() {
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
}













  const editOrderModal = document.getElementById('editOrderModal');
  editOrderModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const orderId = button.getAttribute('data-order-id');
    const customerName = button.getAttribute('data-customer-name');
    const productName = button.getAttribute('data-product-name');
    const status = button.getAttribute('data-status');
    const orderType = button.getAttribute('data-order-type');

    const modalOrderId = editOrderModal.querySelector('#edit_order_id');
    const modalCustomerName = editOrderModal.querySelector('#edit_customer_name');
    const modalProductName = editOrderModal.querySelector('#edit_product_name');
    const modalStatus = editOrderModal.querySelector('#edit_status');
    const modalOrderType = editOrderModal.querySelector('#edit_order_type');

    modalOrderId.value = orderId;
    modalCustomerName.value = customerName;
    modalProductName.value = productName;
    modalStatus.value = status;
    modalOrderType.value = orderType;
  });
</script>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.querySelectorAll('.PDFdata').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior

            // Collect data from the clicked link
            const managedBy = link.getAttribute('data-managed-by');
            const customerName = link.getAttribute('data-customer-name');
            const productName = link.getAttribute('data-product-name');
            const status = link.getAttribute('data-status');
            const orderType = link.getAttribute('data-order-type');
            const quantity = link.getAttribute('data-quantity');
            const totalPrice = link.getAttribute('data-total-price');

            // Populate the hidden form fields
            document.getElementById('managed_by').value = managedBy;
            document.getElementById('customer_name').value = customerName;
            document.getElementById('product_name').value = productName;
            document.getElementById('status').value = status;
            document.getElementById('order_type').value = orderType;
            document.getElementById('quantity').value = quantity;
            document.getElementById('total_price').value = totalPrice;

            // Submit the form
            document.getElementById('pdfForm').submit();
        });
    });
</script>




  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>