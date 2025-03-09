<?php
// Include database connection
$required_role = 'admin';
include('../check_session.php');
include('../log_functions.php');
include '../dbconnect.php';
ini_set('display_errors', 1);

// Fetch user details from session
$user_email = $_SESSION['email'];

// Get the user's first name and last name from the database
$query = "SELECT User_ID, First_Name, Last_Name FROM Users WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id, $first_name, $last_name);
$stmt->fetch();
$stmt->close();

// Concatenate first and last name
$user_full_name = $first_name . ' ' . $last_name;

// Fetch settings from the database
$query = "SELECT * FROM Settings";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['Setting_Key']] = $row['Value'];
}

// Handle settings form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Retrieve form values
    $sign_up_enabled = isset($_POST['sign_up_enabled']) ? 1 : 0;
    $admin_signup_enabled = isset($_POST['admin_signup_enabled']) ? 1 : 0;
    $sign_up_amount = isset($_POST['signup_amount']) ? $_POST['signup_amount'] : 0;

    // Update the settings in the database
    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'SignUpEnabled'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sign_up_enabled);
    $stmt->execute();
    
    // Log activity with full name
    logActivity($conn, $user_id, $user_full_name . " enabled sign-up restrictions");

    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'AdminSignUpEnabled'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_signup_enabled);
    $stmt->execute();

    // Log activity with full name
    logActivity($conn, $user_id, $user_full_name . " enabled admin sign-up restrictions");

    // Optionally, update the sign-up amount
    $query = "UPDATE Settings SET Value = ? WHERE Setting_Key = 'MaxSignUps'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sign_up_amount);
    $stmt->execute();

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Reload page to reflect changes
    exit();
}

// Handle IP removal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_ip'])) {
    $ip_id = $_POST['ip_id'];

    // Delete entry from the database
    $deleteQuery = "DELETE FROM IP_Cooldown WHERE ID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $ip_id);

    if ($stmt->execute()) {
        // Log activity with full name
        logActivity($conn, $user_id, $user_full_name . " removed an IP cooldown entry.");
        
        echo json_encode(["success" => true, "message" => "IP removed successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error removing IP"]);
    }
    $stmt->close();
    $conn->close();
    exit(); // Stop further execution after JSON response
}

// Fetch IP cooldown entries
$query = "SELECT ID, IP_Address, Attempts, Last_Attempt, Locked_Until FROM IP_Cooldown";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGSD | Admin Settings</title>
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
    DO NOT REMOVE THIS SNIPPET, THIS IS FOR ADMIN JS
------------------------------------------------------>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $(".remove-ip").click(function() {
            let ipId = $(this).data("ip-id");
            let rowElement = $("#row-" + ipId); // Table row
            let cardElement = $("#card-" + ipId); // Mobile card

            if (confirm("Are you sure you want to remove this IP?")) {
                $.ajax({
                    url: "", // Send request to same page
                    type: "POST",
                    data: {
                        remove_ip: 1,
                        ip_id: ipId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            rowElement.fadeOut(300, function() {
                                $(this).remove();
                            });
                            cardElement.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("An error occurred while processing your request.");
                    }
                });
            }
        });
    });
</script>
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
                <li>
                    <a href="../ManageCustomers">
                        <i class="bi bi-people-fill" style="font-size:13.28px; background-color: #e8ecef; padding: 6px; border-radius: 3px;"></i>
                        <span>&nbsp;Manage Customer</span>
                    </a>
                </li>
                <li class="active">
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
                            echo strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
                        ?>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h1>
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

    <div class="container-fluid px-md-4" style="max-height: 800px; overflow-y: auto;">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <section class="admin">
                    <div class="admin-main">
                        <div class="admin-title p-3 pt-5">
                        <div class="pb-4">
                        <i class="bi bi-gear" style="font-size:56px;"></i>
                        </div>
                        <div class="d-flex align-items-center">
                            <h1><b>Admin Settings</b>
                            <i class="bi bi-info-circle mb-5" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Manage key settings to control user access, sign-up restrictions, and IP cooldowns for enhanced security and customization."></i>
                            <script>
                                $(document).ready(function(){
                                    $('[data-toggle="tooltip"]').tooltip();   
                                });
                            </script>
                            </h1>
                        </div>

                            <h3>Customize and control system preferences</h3>
                            <p>Access tools to manage users, configure system settings, and oversee overall platform functionality.</p>
                        </div>

                        <div class="admin-ip">
                            <div class="p-3">
                                <div class="admin-ip-title d-flex flex-column mb-3">
                                    <h3 style="letter-spacing: -0.045em;">
                                        <b>Current IP address:</b>
                                        <small class="text-muted"><?php echo $_SERVER['REMOTE_ADDR']; ?></small>
                                    </h3>
                                    <h5 class="text-muted mb-3" style="width: 90%;">
                                        Your IP address uniquely identifies your device on the internet and is essential for communication with other devices and accessing online services.
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end p-3">
                            <button class="btn custom-btn" onclick="window.location.href='../Logs'">View Logs</button>
                        </div>
                    </div>

                    <div style="padding: 16px;">
                        <hr>
                    </div>


                    <div class="admin-restrictions">
                    <div class="d-flex justify-content-between align-items-start p-3">
                        <div class="d-flex align-items-center">
                            <h3 style="letter-spacing: -0.045em;">
                                <b>Restrictions</b>
                            </h3>
                            <i class="bi bi-info-circle pl-2 pb-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Manage key settings to control user access, sign-up restrictions, and IP cooldowns for enhanced security and customization."></i>
                            <script>
                                $(document).ready(function(){
                                    $('[data-toggle="tooltip"]').tooltip();
                                });
                            </script>
                        </div>
                    </div>
                    <div id="unsavedChangesAlert" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
                        You have unsaved changes made. Please click save settings to save.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                        <form action="" method="POST">
                            <div class="d-flex justify-content-between align-items-start p-3">
                                <div class="admin-restrictions-title d-flex flex-column mb-3">
                                    <h3 style="letter-spacing: -0.045em;">
                                        <b>Sign Up Restrictions</b>
                                    </h3>
                                    <h5 class="text-muted mb-3" style="width: 90%;">
                                        Toggle the restrictions to allow User account creation.
                                    </h5>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 5px;">
                                    <input type="number" id="signup_amount" name="signup_amount" placeholder="Set" value="<?php echo htmlspecialchars($settings['MaxSignUps'] ?? ''); ?>" style="width: 60px; padding: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-right: 10px;">
                                    <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 20px;">
                                        <input type="checkbox" name="sign_up_enabled" value="1" <?php echo ($settings['SignUpEnabled'] == 1) ? 'checked' : ''; ?> style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; -webkit-transition: .4s; transition: .4s; border-radius: 34px;"></span>
                                    </label>
                                    <style>

                                    </style>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-start p-3">
                                <div class="admin-restrictions-title d-flex flex-column mb-3">
                                    <h3 style="letter-spacing: -0.045em;">
                                        <b>Admin Sign Up</b> 
                                    </h3>
                                    <h5 class="text-muted mb-3" style="width: 90%;">
                                        Toggle the restrictions to allow admin sign up.
                                    </h5>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 5px;">
                                    <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 20px;">
                                        <input type="checkbox" name="admin_signup_enabled" value="1" <?php echo ($settings['AdminSignUpEnabled'] == 1) ? 'checked' : ''; ?> style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; -webkit-transition: .4s; transition: .4s; border-radius: 34px;"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end p-3">
                                <button type="submit" name="save_settings" class="btn custom-btn">Save Settings</button>
                            </div>
                        </form>
                    </div>
                    <script>
                        $(document).ready(function() {
                            // Function to show the unsaved changes alert
                            function showUnsavedChangesAlert() {
                                $("#unsavedChangesAlert").fadeIn();
                            }

                            // Attach event listeners to the input elements
                            $("input[name='sign_up_enabled'], input[name='admin_signup_enabled'], input[name='signup_amount']").change(function() {
                                showUnsavedChangesAlert();
                            });
                        });
                    </script>

                    <div class="p-3">
                        <hr>
                    </div>
                    <div class="p-3">
                        <h3 style="letter-spacing: -0.045em;">
                        <div class="d-flex align-items-center">
                            <b>IP Cooldown</b>
                            <i class="bi bi-info-circle pl-2" style="font-size: 20px; color:rgb(74, 109, 65); font-weight: bold;" data-toggle="tooltip" data-placement="top" title="Manage IP cooldown settings"></i>
                        </div>
                            <script>
                                $(document).ready(function(){
                                    $('[data-toggle="tooltip"]').tooltip();   
                                });
                            </script>
                        </h3>
                        <h5 class="text-muted mb-3" style="width: 90%;">
                            Manage banned IP addresses and cooldown periods.
                        </h5>
                        <div>
                            <div>

                                <!-- Table Layout (Visible on larger screens) -->
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>IP Address</th>
                                                <th>Attempts</th>
                                                <th>Last Attempt</th>
                                                <th>Locked Until</th>
                                                <th>Remove</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0) : ?>
                                                <?php while ($row = $result->fetch_assoc()) : ?>
                                                    <tr id="row-<?php echo $row['ID']; ?>">
                                                        <td><?php echo htmlspecialchars($row['IP_Address']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['Attempts']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['Last_Attempt']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['Locked_Until'] ?: 'Not Locked'); ?></td>
                                                        <td class="text-center">
                                                            <button class="btn btn-danger btn-sm remove-ip" data-ip-id="<?php echo $row['ID']; ?>">
                                                                <i class="bi bi-trash"></i> Remove
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No IP cooldown records found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Card Layout (Visible on smaller screens) -->
                                <div class="row d-block d-md-none">
                                    <?php
                                    $result->data_seek(0); // Reset pointer
                                    if ($result->num_rows > 0) : ?>
                                        <?php while ($row = $result->fetch_assoc()) : ?>
                                            <div class="col-12 mb-3" id="card-<?php echo $row['ID']; ?>">
                                                <div class="card shadow-sm">
                                                    <div class="card-body">
                                                        <h5 class="card-title">IP: <?php echo htmlspecialchars($row['IP_Address']); ?></h5>
                                                        <p><strong>Attempts:</strong> <?php echo htmlspecialchars($row['Attempts']); ?></p>
                                                        <p><strong>Last Attempt:</strong> <?php echo htmlspecialchars($row['Last_Attempt']); ?></p>
                                                        <p><strong>Locked Until:</strong> <?php echo htmlspecialchars($row['Locked_Until'] ?: 'Not Locked'); ?></p>
                                                        <button class="btn btn-danger btn-sm remove-ip" data-ip-id="<?php echo $row['ID']; ?>">
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <p>No IP cooldown records found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
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

/* .add-btn {
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
} */

.tooltip-inner {
    color: #000 !important;
    background-color: #ebecec !important;
}

/* Custom styles to match system colors */
.custom-btn {
    background-color: #6fa062 !important;
    color: #fff !important;
    border: none !important;
    transition: transform 0.3s !important;
}

.custom-btn:hover {
    background-color: #5e8a52 !important;
    color: #fff !important;
    transform: scale(1.05) !important;
}

/* Override Bootstrap's default switch appearance */
.form-switch .form-check-input {
    height: 1.5rem;
    width: 3rem;
}

/* Style for the table header */
.table thead th {
    background-color: #f2f4f0;
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

/*-----------------------------------------------------
    ADMIN SETTINGS
------------------------------------------------------*/

/* Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc !important; /* Ensure initial state */
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #6fa062 !important; /* When checked */
}

input:checked + .slider:before {
    -webkit-transform: translateX(20px);
    -ms-transform: translateX(20px);
    transform: translateX(20px);
}

/* Add higher specificity for the checked state */
label.switch input[type="checkbox"]:checked + .slider {
    background-color: #6fa062 !important;
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
