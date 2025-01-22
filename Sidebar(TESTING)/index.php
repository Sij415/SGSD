<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashbard</title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<!-- Frontend notes: make this hidden or be linked in another file. -->
<script>
function openNav() {
  document.getElementById("Sidebar").style.width = "275px";
  document.getElementById("main").style.marginLeft = "275px";
}

function closeNav() {
  document.getElementById("Sidebar").style.width = "0";
  document.getElementById("main").style.marginLeft = "0";
}
</script>
<!-- Import to Dashboard, ManageOrders, ManageStocks. Because this is the Sidebar as well as the heading-->
<header class="app-header">
    <nav class="app-nav">
        <a href="#" class="sidebar-btn" id="menu-toggle" onclick="openNav()">≡</a>
        <a href="#" class="tooltip-btn">X</a>
    </nav>
    
    <div id="Sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
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
                <i class="fa-solid fa-list" style="font-size:13.28px;"></i>
                <span>&nbsp;Orders</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#">
                <i class="fa-solid fa-user-shield" style="font-size:13.28px;"></i>
                <span>&nbsp;Admin</span>
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
                <h2>sangabrielsoftdrinksdelivery@gmail.com</h2>
            </div>
        </div>
        <div class="sidebar-options">
            <div class="sidebar-item">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="#" class="sidebar-items-button">
                    <i class="fa-solid fa-file-alt"></i>
                    <span>Manual</span>
                </a>
            </div>
        </div>
    
</header>
 
</html>