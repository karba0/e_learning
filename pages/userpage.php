<?php

@include '../config/conn.php';

session_start();

if(!isset($_SESSION['email'])){
   header('location:../auth/login.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>user page</title>

   <!-- custom css file link  -->
   
   <?php
      include("./courses_css.php");
   ?>

</head>
<body>
   <?php
   
   include("../index1.php");
?>
   


</body>
</html>
<?php 

?>
