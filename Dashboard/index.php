<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashbard</title>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<header class="app-header">
    <nav class="app-nav">
        <a href="#" class="sidebar-btn">≡</a>
        <a href="#" class="tooltip-btn">X</a>
    </nav>
    
    <!-- Implement Sidebar -->

</header>

<body class="dashboard">
    <!-- Dashboard-title -->
    <div class="dashboard-title">
        <h1><b>ANALYTICS</b> DASHBOARD</h1>
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio1">DAILY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="btnradio2">WEEKLY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio3">MONTHLY</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio4" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnradio4">YEARLY</label>
            </div>
    </div>
    <!-- Grid Dashboard -->
    <div class="dashboard-summary">
        <div class="parent">
            <div class="div1">1</div>
            <div class="div2">2</div>
            <div class="div3">3</div>
            <div class="div4">4</div>
            <div class="div5">5</div>
        </div>
    </div>
    <div class="dashboard-top">
        <h1><b>TOP</b> SELLING</h1>
    </div>
    <div class="dashboard-top-grid">
                <div class="div1">1</div>
                <div class="div2">2</div>
            </div>
    <hr>
</body> 
</html>