<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../style/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <title>Responsive Sidebar</title>
  <style>
    /* Base styles */
    body {
      padding: 0;
      font-family: Arial, sans-serif;
      display: flex;
    }

    .sidebar {
      background-color: #333;
      color: #fff;
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: -250px; /* Hidden by default */
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
      padding: 1em;
      transition: margin-left 0.3s ease;
    }

    .toggle-btn {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 0.5em 1em;
      cursor: pointer;
    }

    /* Responsive styles */
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
<body>
  <header>
  <div class="sidebar" id="sidebar">
    <button class="close-btn d-md-none" onclick="closeNav()">&times;</button>
    <div class="">
      <nav class="app-nav">
        <a href="#" class="tooltip-btn">Hello X</a>
      </nav>
      <a href="#" class="sangabrielsoftdrinksdeliverytitledonotchangethisclassnamelol"><b>SGSD</b></a>

      <div class="sidebar-items">
        <hr style="width: 75%; margin: 0 auto; padding: 12px;">
        <div class="sidebar-item">
          <a href="#" class="sidebar-items-a">
            <i class="fa-solid fa-border-all"></i>
            <span>&nbsp;Dashboard</span>
          </a>
        </div>
        <div class="sidebar-item">
          <a href="#">
            <i class="fa-solid fa-box"></i>
            <span>&nbsp;Stocks</span>
          </a>
        </div>
        <div class="sidebar-item">
          <a href="#">
            <i class="fa-solid fa-list" style="font-size:19.25px;"></i>
            <span>&nbsp;Orders</span>
          </a>
        </div>
      </div>
      <hr style="width: 75%; margin: 0 auto; padding: 12px ;">
      <div class="sidebar-usr">
        <div class="sidebar-pfp">
          <img src="https://upload.wikimedia.org/wikipedia/en/b/b1/Portrait_placeholder.png" alt="Sample Profile Picture">
        </div>
        <div class="sidebar-usrname">
          <h1>pogi ako</h1>
          <h2>pogiako@gmail.io</h2>
        </div>
      </div>
    </div>
  </div>
  </header>
  <div class="content">
    <button class="toggle-btn" id="toggleBtn">☰</button>
    <h1>Responsive Sidebar</h1>
    <p>Resize the window to see the sidebar behavior.</p>
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
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
