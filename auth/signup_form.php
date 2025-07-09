<?php 
            
           
            include("../pages/courses_css.php");
    ?>
    
    <?php
            include("../includes/navbar1.php")
    ?>
    <?php

@include '../config/conn.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $email = strtolower($email);
   $pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);

   // Username must not start with a number
   if (preg_match('/^[0-9]/', $name)) {
       $error[] = 'Username cannot start with a number.';
   }

   // Username must be unique
   $username_check = "SELECT * FROM users WHERE name = '$name'";
   $username_result = mysqli_query($conn, $username_check);
   if (mysqli_num_rows($username_result) > 0) {
       $error[] = 'This username is already taken. Please choose another.';
   }

   // Only allow Gmail addresses
   if (!preg_match('/^[a-z0-9._%+-]+@gmail\.com$/', $email)) {
       $error[] = 'Only Gmail addresses are allowed for signup.';
   }

   // ✅ Email validation with regex
   if (!preg_match("/^[a-z0-9._%+-]+@[a-z.-]+\.[a-z]{2,}$/", $email)) {
       $error[] = 'Invalid email format.';
   }


 if (empty($error)) {
   $select = "SELECT * FROM users WHERE email = '$email'";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){

      $error[] = 'An account with this email already exists. Please use a different email or login.';

   }else{

      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         $insert = "INSERT INTO users(name, email, password) VALUES('$name','$email','$pass')";
         mysqli_query($conn, $insert);
         header('location:login.php');
         exit();
      }
   }
}

};

?>
    <div class="container-xxl py-2 mt-4">
            <div class="container">

                <div class="row g-4 wow fadeInUp" data-wow-delay="0.5s ">

                    <center>
                        <form class="shadow p-4" style="max-width: 550px;" method="post" action="">

                            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                                <h1 class="">Signup</h1>
                                    
                        <!-- Show Error Message Here -->
                          <!-- ✅ Show Error Messages -->
                        <?php
                        if (!empty($error)) {
                            foreach ($error as $err) {
                                echo '<div class="alert alert-danger">' . $err . '</div>';
                            }
                        }
                        ?>

                        <div class="alert alert-info" style="margin-bottom: 18px;">Only Gmail addresses are allowed for signup. Please use your Google Gmail account.</div>

                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input  name="name" type="text" class="form-control" required placeholder="Username">
                                        <label for="username">Username</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input name="email" type="email" class="form-control" id="email"placeholder="Email Address"
                                        pattern="^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$"required>

                                        <label for="email">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating position-relative">
                                        <input name="password" type="password" class="form-control" id="password" placeholder="Password" minlength="8" required>
                                        <label for="password">Password</label>
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" onclick="togglePassword('password')" style="z-index: 10; border: none; background: none;">
                                            <i class="fas fa-eye" id="password-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                 <div class="col-12">
                                    <div class="form-floating position-relative">
                                        <input name="cpassword" type="password" class="form-control" id="cpassword" placeholder="Confirm Password" minlength="8" required>
                                        <label for="cpassword">Confirm Password</label>
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" onclick="togglePassword('cpassword')" style="z-index: 10; border: none; background: none;">
                                            <i class="fas fa-eye" id="cpassword-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <input class="btn text-light w-100 py-3" type="submit" name="submit" value="Signup"/>
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
        </div>

<style>
.form-floating .btn-link {
    color: #6c757d;
    text-decoration: none;
    padding: 0;
    margin: 0;
}

.form-floating .btn-link:hover {
    color: #185a9d;
}

.form-floating .btn-link:focus {
    box-shadow: none;
    outline: none;
}

.form-floating .btn-link i {
    font-size: 1.1rem;
}
</style>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>
  




