<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>E-Learning Platform : Signup</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">


</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Navbar Start -->
       <?php 
            
             include("../includes/navbar.php");
             include("../index.php");
        ?>
    
        <div class="container">

            <div class="row g-4 wow fadeInUp" data-wow-delay="0.5s ">

                <center>
                    <form class="shadow p-4" style="max-width: 550px;" method="POST" action="signup.php">
                        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                            <h1 class="mb-5 bg-white text-center px-3">Signup</h1>

                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="username" placeholder="Username">
                                    <label for="Username">Username</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" placeholder="Email Address">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" placeholder="Password" required>
                                    <label for="password">Password</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <input class="btn text-light w-100 py-3" type="submit" value="Signup"/>
                            </div>

                            <div class="col-12 text-center">
                                <p>Already have an account? <a class="text-decoration-none" href="login.php">Login</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </center>

            </div>
        </div>
    </div> -->
        <?php 
             include("../auth/signup_form.php");
        ?>


    <!-- Template Javascript -->
    <script src="../assets/js/main.js"></script>
</body>

</html>
