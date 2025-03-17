<?php
include '../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['Product_Name'];
    $product_type = $_POST['Product_Type'];
    $price = $_POST['Price'];
    $new_stock = $_POST['New_Stock'];
    $old_stock = $_POST['Old_Stock'];
    $threshold = $_POST['Threshold'];

    $sql_update = "
        UPDATE stocks s
        JOIN products p ON s.Product_ID = p.Product_ID
        SET
            p.Product_Type = ?,
            p.Price = ?,
            s.New_Stock = ?,
            s.Old_Stock = ?,
            s.Threshold = ?
        WHERE p.Product_Name = ?
    ";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("siiiss", $product_type, $price, $new_stock, $old_stock, $threshold, $product_name);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

$sql = "
    SELECT
        p.Product_ID,
        p.Product_Name, 
        p.Product_Type, 
        p.Price, 
        s.New_Stock, 
        s.Old_Stock, 
        s.Threshold 
    FROM 
        Stocks s
    JOIN 
        Products p ON s.Product_ID = p.Product_ID
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stocks</title>
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function editRow(button) {
            let row = button.parentElement.parentElement;
            let cells = row.getElementsByClassName("editable");
            for (let cell of cells) {
                cell.contentEditable = true;
                cell.style.backgroundColor = "#f0f0f0";
            }
            row.querySelector(".confirm-btn").style.display = "inline-block";
        }
        function confirmEdit(button) {
            let row = button.parentElement.parentElement;
            let form = document.createElement("form");
            form.method = "POST";
            form.action = "index.php";
            let inputs = ["Product_Name", "Product_Type", "Price", "New_Stock", "Old_Stock", "Threshold"];
            for (let input of inputs) {
                let cell = row.querySelector(`.${input}`);
                let hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = input;
                hiddenInput.value = cell.innerText.trim();
                form.appendChild(hiddenInput);
            }
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<div class="container-fluid " >
    <div class="row flex-nowrap">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 " style="background-color: #F2F4F0;">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="/" class="d-flex sgsd-redirect align-items-center pb-3 mb-md-0 me-md-auto text-dark text-decoration-none"><img src="../logo.png" class="p-1" alt="Logo" />San Gabriel Softdrinks Delivery</a>
                <header class="main-header">
    </header>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                    <li class="nav-item">
                        <a href="../" class="nav-link align-middle px-0">
                            <i class="fs-4 bi-house"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#submenu1" data-bs-toggle="collapse" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-speedometer2"></i> <span class="ms-1 d-none d-sm-inline">Management</span> </a>
                        <ul class="collapse nav flex-column ms-1" id="submenu1" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Stock</span> 1 </a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Product</span> 2 </a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Manage Orders</span> 3 </a>
                            </li>
                        
                            
                        </ul>
                    </li>

                    
                    <li>
                        <a href="#" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">Orders</span></a>
                    </li>
                    <li>
                        <a href="#submenu2" data-bs-toggle="collapse" class="nav-link px-0 align-middle ">
                            <i class="fs-4 bi-bootstrap"></i> <span class="ms-1 d-none d-sm-inline">Bootstrap</span></a>
                        <ul class="collapse nav flex-column ms-1" id="submenu2" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span> 1</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span> 2</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#submenu3" data-bs-toggle="collapse" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-grid"></i> <span class="ms-1 d-none d-sm-inline">Products</span> </a>
                            <ul class="collapse nav flex-column ms-1" id="submenu3" data-bs-parent="#menu">
                            <li class="w-100">
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 1</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 2</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 3</a>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Product</span> 4</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="nav-link px-0 align-middle">
                            <i class="fs-4 bi-people"></i> <span class="ms-1 d-none d-sm-inline">Customers</span> </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle">
                        <span class="d-none d-sm-inline mx-1">loser</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="#">New project...</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col py-3">
<body>
<div class="container">
    <h1 class="text-center my-4">Manage Stocks</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Product Type</th>
                <th>Price</th>
                <th>New Stock</th>
                <th>Old Stock</th>
                <th>Threshold</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
        while ($row = $result->fetch_assoc()) {
            echo '
            <tr>
                <td class="product-id">' . htmlspecialchars($row['Product_ID']) . '</td> <!-- Non-editable -->
                <td class="product-name editable">' . htmlspecialchars($row['Product_Name']) . '</td>
                <td class="product-type editable">' . htmlspecialchars($row['Product_Type']) . '</td>
                <td class="price editable">$' . htmlspecialchars($row['Price']) . '</td>
                <td class="new-stock editable">' . htmlspecialchars($row['New_Stock']) . '</td>
                <td class="old-stock editable">' . htmlspecialchars($row['Old_Stock']) . '</td>
                <td class="threshold editable">' . htmlspecialchars($row['Threshold']) . '</td>
                <td>
                    <button class="edit-btn btn btn-primary">Edit</button>
                    <button class="confirm-btn btn btn-success" style="display: none;">Confirm</button>
                </td>
            </tr>';
        }
        
?>
        </tbody>
    </table>
</div>
<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            let row = this.closest('tr');
            row.querySelectorAll('.editable').forEach(cell => {
                cell.contentEditable = true;
                cell.classList.add('editing');
            });
            row.querySelector('.confirm-btn').style.display = 'inline';
            this.style.display = 'none';
        });
    });

    document.querySelectorAll('.confirm-btn').forEach(button => {
        button.addEventListener('click', function () {
            let row = this.closest('tr');
            let priceField = row.querySelector('.price');

            // Get product ID (non-editable)
            let productID = row.querySelector('.product-id').innerText.trim();

            // Remove "$" from price before sending
            let cleanPrice = priceField.innerText.replace('$', '').trim();
            priceField.innerText = cleanPrice;

            let updatedData = {
                product_id: productID,
                product_name: row.querySelector('.product-name').innerText.trim(),
                product_type: row.querySelector('.product-type').innerText.trim(),
                price: cleanPrice,
                new_stock: row.querySelector('.new-stock').innerText.trim(),
                old_stock: row.querySelector('.old-stock').innerText.trim(),
                threshold: row.querySelector('.threshold').innerText.trim()
            };

            // Send AJAX request to update the database
            fetch('update_stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    priceField.innerText = `$` + cleanPrice;

                    row.querySelectorAll('.editable').forEach(cell => {
                        cell.contentEditable = false;
                        cell.classList.remove('editing');
                    });

                    row.querySelector('.edit-btn').style.display = 'inline';
                    row.querySelector('.confirm-btn').style.display = 'none';
                } else {
                    alert('Update failed: ' + data.error);
                }
            });
        });
    });
</script>



</body>
</html>
