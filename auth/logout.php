<?php

@include '../config/conn.php';

session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Unset all admin session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_email']);
    // Add any other admin session variables here if needed
    session_destroy();
    header('Location: admin_login.php');
    exit();
} else {
    // Unset all user session variables
    unset($_SESSION['email']);
    unset($_SESSION['user_name']);
    // Add any other user session variables here if needed
    session_destroy();
    header('Location: login.php');
    exit();
}

?>