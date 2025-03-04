<?php
// Include database connection
include('../check_session.php');
include '../dbconnect.php';

// Start the session
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's first name and email from the database
$query = "SELECT First_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email); // Bind the email as a string
$stmt->execute();
$stmt->bind_result($user_first_name);
$stmt->fetch();
$stmt->close();

// UPDATED QUERY to fetch logs
$query = "SELECT Logs.Log_ID, 
                 Users.First_Name AS First_Name, 
                 Logs.Order_ID, 
                 Logs.Date, 
                 Logs.Time, 
                 Logs.Activity 
          FROM Logs
          INNER JOIN Users ON Logs.User_ID = Users.User_ID"; 

$result = $conn->query($query);
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
  <title>Show Logs</title>
  <style>
    .bg-orange {
      background-color: #ff8800 !important; /* Ensure Orange */
      color: white !important;
    }

    tr.bg-orange td {
      background-color: #ff8800 !important;
      color: white !important;
    }

    .table-striped tbody tr.bg-orange td {
      background-color: #ff8800 !important;
      color: black !important;
    }

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
      left: -250px; /* Hidden by default */
      transition: left 0.3s ease;
      z-index: 1000;
      overflow-x: hidden;
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
        margin-left: 1rem; /* Push content for medium screens and above */
      }

      .toggle-btn {
        display: none;
      }

      .sidebar .close-btn {
        display: none; /* Hide close button on larger screens */
      }
    }

    @media (max-width: 767.98px) {
      .sidebar .close-btn {
        display: block; /* Show close button only on smaller screens */
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
      <a href="../ShowLogs">
        <i class="fa-solid fa-list"></i>
        <span>&nbsp;Show Logs</span>
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
  <div class="container mt-4">
    <h1><b>Show Logs</b></h1>
    <h3>System Logs</h3>

    <!-- Search Box -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="input-group">
        <input type="search" class="form-control" placeholder="Search" aria-label="Search" id="searchInput">
        <button class="btn btn-outline-secondary" type="button" id="search">
          <i class="fa fa-search"></i>
        </button>
      </div>
    </div>

    <!-- Table Layout (Visible on larger screens) -->
    <div class="table-responsive d-none d-md-block">
      <table class="table table-striped table-bordered" id="logsTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)">Log ID <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(1)">User Name <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(2)">Order ID <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(3)">Date <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(4)">Time <i class="bi bi-arrow-down-up"></i></th>
            <th onclick="sortTable(5)">Activity <i class="bi bi-arrow-down-up"></i></th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $row['Log_ID']; ?></td>
                <td><?php echo $row['First_Name']; ?></td>
                <td><?php echo $row['Order_ID']; ?></td>
                <td><?php echo $row['Date']; ?></td>
                <td><?php echo $row['Time']; ?></td>
                <td><?php echo $row['Activity']; ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">No logs found.</td>
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
            <div class="card shadow-sm">
              <div class="card-body">
                <h5 class="card-title">Log ID: <?php echo htmlspecialchars($row['Log_ID']); ?></h5>
                <div class="row">
                  <div class="col-6">
                    <p class="card-text"><strong>User Name:</strong> <?php echo htmlspecialchars($row['First_Name']); ?></p>
                  </div>
                  <div class="col-6">
                    <p class="card-text"><strong>Order ID:</strong> <?php echo htmlspecialchars($row['Order_ID']); ?></p>
                  </div>
                  <div class="col-6">
                    <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($row['Date']); ?></p>
                  </div>
                  <div class="col-6">
                    <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($row['Time']); ?></p>
                  </div>
                  <div class="col-12">
                    <p class="card-text"><strong>Activity:</strong> <?php echo htmlspecialchars($row['Activity']); ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No logs found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  function sortTable(columnIndex) {
    const table = document.getElementById('logsTable');
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

    const tbody = table.getElementsByTagName('tbody')[0];
    rows.forEach(row => tbody.appendChild(row));
  }

  function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('logsTable');
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

  document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    function closeNav() {
      sidebar.classList.remove('active');
    }
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>