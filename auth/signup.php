<?php

    echo "I am triggered.";
    include("../config/conn.php");
   
    // $signupForm =

    $msg='';

    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password =$_POST['password'];


        


        $select1="SELECT * FROM `users` WHERE email='$email' AND password ='$password'";
        $select_user= mysqli_query($conn,$select1);
        if (mysqli_num_rows($select_user) >0) {
            $msg ="user already exist!";
        }
        else{
            $insert1="INSERT INTO `users`(`name`, `email`, `password`) VALUES ('$name','$email','$password')";
        mysqli_query($conn, $select1);
        header('Location: login.php');

        }
    }
?>