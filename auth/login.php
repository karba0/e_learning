<?php
@include '../config/conn.php';
session_start();

if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   $select = " SELECT * FROM users WHERE email = '$email' ";
   $result = mysqli_query($conn, $select);
   if(mysqli_num_rows($result) > 0){
      $row = mysqli_fetch_array($result);
      $_SESSION['email'] = $row['email'];
      $_SESSION['user_name'] = $row['name'];
      header('location:../pages/userpage.php');
   }else{
      $error[] = 'Incorrect email or password!';
   }
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>E-Learning Platform : Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <?php include("../pages/courses_css.php"); ?>
</head>
<body>
    <?php include("../includes/navbar.php"); ?>
    <div class="container-xxl py-2 mt-4">
        <div class="container">
            <div class="row g-4 wow fadeInUp" data-wow-delay="0.5s ">
                <center>
                    <form class="shadow p-4" style="max-width: 550px;" action="" method="post">
                        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                            <h1 class="mb-5 bg-white text-center px-3">Login</h1>
                            <?php
                            if(isset($error)){
                                foreach($error as $err){
                                    echo '<span class="error-msg">'.$err.'</span>';
                                }
                            }
                            ?>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control" id="email" required placeholder="Email Address">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <a href="forgot_password.php" style="color: #388e8e; text-decoration: none; display: block; margin-bottom: 18px; text-align: center;">Forgot password?</a>
                            </div>
                            <div class="col-12">
                                <button class="btn text-light w-100 py-3"  type="submit" name="submit">Login</button>
                            </div>
                            <div class="col-12 text-center">
                                <p>Don't have an account? <a class="text-decoration-none" href="signup_form.php">Signup</a></p>
                            </div>
                        </div>
                    </form>
                </center>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/lib/wow/wow.min.js"></script>
    <script src="../assets/lib/easing/easing.min.js"></script>
    <script src="../assets/lib/waypoints/waypoints.min.js"></script>
    <script src="../assets/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>