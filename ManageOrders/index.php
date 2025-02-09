<?php
// Include database connection
$required_role = 'admin';
include('../check_session.php');
include '../dbconnect.php';
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's first name from the database
$query = "SELECT First_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_first_name);
$stmt->fetch();
$stmt->close();

// Fetch order data from the database
$query = "SELECT 
            Orders.Order_ID, 
            Users.User_ID,  
            Customers.First_Name AS Customer_Name, 
            Products.Product_Name, 
            Orders.Status, 
            Orders.Order_Type 
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
  <a href="#" class="sangabrielsoftdrinksdeliverytitledonotchangethisclassnamelol"><b>SGSD</b></a>
  <div class="sidebar-items">
    <hr style="width: 75%; margin: 0 auto; padding: 12px;">
    <div class="sidebar-item">
      <a href="../Dashboard" class="sidebar-items-a">
        <i class="fa-solid fa-border-all"></i>
        <span>&nbsp;Dashboard</span>
      </a>
    </div>
    <div class="sidebar-item">
      <a href="../ManageStocks">
        <i class="fa-solid fa-box"></i>
        <span>&nbsp;Manage Stocks</span>
      </a>
    </div>
    <div class="sidebar-item">
      <a href="../ManageOrders">
        <i class="bx bxs-objects-vertical-bottom" style="font-size:13.28px;"></i>
        <span>&nbsp;Manage Orders</span>
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
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="user_id" class="form-label">Customer Name</label>
              <input type="text" name="customer_name" id="customer_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="product_id" class="form-label">Product Name</label>
              <input type="text" name="product_name" id="product_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <input type="text" name="status" id="status" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="order_type" class="form-label">Order Type</label>
              <input type="text" name="order_type" id="order_type" class="form-control" required>
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
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
              <input type="text" class="form-control" id="edit_status" name="New_Status">
            </div>
            <div class="mb-3">
              <label for="edit_order_type" class="form-label">Order Type</label>
              <input type="text" class="form-control" id="edit_order_type" name="New_OrderType">
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
        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="example-search-input">
        <button class="btn btn-outline-secondary" type="button" id="search">
          <i class="fa fa-search"></i>
        </button>
      </div>

      <!-- Add Order Button -->
      <button class="add-btn ms-3" data-bs-toggle="modal" data-bs-target="#addOrderModal">Add Order</button>
    </div>

    <!-- Table Layout (Visible on larger screens) -->
    <div class="table-responsive d-none d-md-block">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Customer Name</th>
            <th>Product Name</th>
            <th>Status</th>
            <th>Order Type</th>
            <th>Edit</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['Order_ID']); ?></td>
                <td><?php echo htmlspecialchars($row['User_ID']); ?></td>
                <td><?php echo htmlspecialchars($row['Customer_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Product_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td><?php echo htmlspecialchars($row['Order_Type']); ?></td>
                <td> <a href="#" data-bs-toggle="modal" data-bs-target="#editOrderModal" data-order-id="<?php echo $row['Order_ID']; ?>" data-customer-name="<?php echo $row['Customer_Name']; ?>" data-product-name="<?php echo $row['Product_Name']; ?>" data-status="<?php echo $row['Status']; ?>" data-order-type="<?php echo $row['Order_Type']; ?>"><i class="bi bi-pencil-square"></i></a></td>
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
                    <p class="card-text"><strong>Order ID:</strong> <?php echo htmlspecialchars($row['Order_ID']); ?></p>
                  </div>
                  <div class="col-6">
                    <p class="card-text"><strong>User ID:</strong> <?php echo htmlspecialchars($row['User_ID']); ?></p>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>