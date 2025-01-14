<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="path/to/your/style.css">
    <title>San Gabriel Login</title>

    <style>
        /* Hide the spin buttons in WebKit browsers */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide spin buttons in Firefox */
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body>
<section class="vh-100" style="background-color: #82A69C;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card overflow-hidden" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <!-- Carousel -->
              <div id="photoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2000">
                <div class="carousel-inner">
                  <!-- First Image -->
                  <div class="carousel-item active">
                    <img src="img/img1.jpg" class="d-block w-100" alt="Image 1" style="object-fit: cover; height: 100%; width: 100%;" />
                  </div>
                  <!-- Second Image -->
                  <div class="carousel-item">
                    <img src="img/img2.jpg" class="d-block w-100" alt="Image 2" style="object-fit: cover; height: 100%; width: 100%;" />
                  </div>
                  <!-- Third Image -->
                  <div class="carousel-item">
                    <img src="img/img3.jpg" class="d-block w-100" alt="Image 3" style="object-fit: cover; height: 100%; width: 100%;" />
                  </div>
                  <!-- Fourth Image -->
                  <div class="carousel-item">
                    <img src="img/img4.jpg" class="d-block w-100" alt="Image 4" style="object-fit: cover; height: 100%; width: 100%;" />
                  </div>
                  <!-- Fifth Image -->
                  <div class="carousel-item">
                    <img src="img/img5.jpg" class="d-block w-100" alt="Image 5" style="object-fit: cover; height: 100%; width: 100%;" />
                  </div>
                </div>
              </div>
              <!-- End Carousel -->
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">

                <form>

                  <div class="d-flex align-items-center mb-3 pb-1">
                    <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                    <span class="h3 fw-bold mb-0">San Gabriel Softdrinks Delivery Inventory and Order Management System</span>
                  </div>

                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Sign into your account</h5>

                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="number" id="form2Example17" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example17">Employee ID</label>
                  </div>

                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="form2Example27" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example27">Password</label>
                  </div>

                  <div class="pt-1 mb-4">
                    <button data-mdb-button-init data-mdb-ripple-init class="btn btn-dark btn-lg btn-block" type="button">Login</button>
                  </div>

                  <a class="small text-muted" href="#!">Forgot password?</a>
                  <p class="mb-5 pb-lg-2" style="color: #393f81;">Don't have an account? <a href="#!"
                      style="color: #393f81;">Register here</a></p>
                  <a href="#!" class="small text-muted">Terms of use.</a>
                  <a href="#!" class="small text-muted">Privacy policy</a>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



    
</body>
</html>