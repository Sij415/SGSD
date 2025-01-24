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

<body>

<section class="admin">

<div class="admin-main">
    <div class="admin-title">
            <h1><b>Admin</b> Settings</h1>
            <h3>To view the product in detail, click the product.</h3>
        </div>
</div>

<div class="admin-ip">
    <div class="p-3">
        <div class="admin-ip-title d-flex flex-column mb-3">
            <h3 style="letter-spacing: -0.045em;">
                <b>Current</b> IP address
                <small class="text-muted">asdadas</small>
            </h3>
            <h5 class="text-muted mb-3" style="width: 90%;">
                Your IP address uniquely identifies your device on the internet and is essential for communication with other devices and accessing online services.
            </h5>
        </div>
        <div class="input-group mt-3">
            <input type="text" class="form-control" id="current-ip" value="asdadas" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">Copy</button>
        </div>
        <!-- <script>
            function copyToClipboard() {
                var copyText = document.getElementById("current-ip");
                copyText.select();
                copyText.setSelectionRange(0, 99999); // For mobile devices
                document.execCommand("copy");
                alert("Copied the text: " + copyText.value);
            }
        </script> -->
    </div>
</div>
<div style="padding: 16px;"><hr></div>
<div class="admin-restrictions">
    <h2 class="p-3" style="letter-spacing: -0.050em;"><b>This is Title</b></h2>
        <div class="d-flex justify-content-between align-items-start p-3">
            <div class="admin-restrictions-title d-flex flex-column mb-3">
                    <h3 style="letter-spacing: -0.045em;">
                        <b>Account</b> Restrictions
                    </h3>
                    <h5 class="text-muted mb-3" style="width: 90%;">
                        Toggle the restrictions on your account to control access and permissions.
                    </h5>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="account-restrictions" onchange="toggleRestrictions()">
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-start p-3">
            <div class="admin-restrictions-title d-flex flex-column mb-3">
                    <h3 style="letter-spacing: -0.045em;">
                        <b>Account</b> Restrictions
                    </h3>
                    <h5 class="text-muted mb-3" style="width: 90%;">
                        Toggle the restrictions on your account to control access and permissions.
                    </h5>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="account-restrictions" onchange="toggleRestrictions()">
                </div>
            </div>
        </div>
<div style="padding: 16px;"><hr></div>
</section>
</body>


</html>