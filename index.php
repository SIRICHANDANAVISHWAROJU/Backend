<?php
    session_start();
    require 'db_connection.php';

    if(isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        
    }
    else {
        header("Location: login.php");
        
    }
    exit();
?>