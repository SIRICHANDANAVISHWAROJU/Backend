<?php
  $host = "localhost";
  $username = "root";
  $password = "";
  $database = "csci6040_study";
  $con = mysqli_connect($host, $username, $password, $database);
  if ($con->connect_error) {
    die("Can not connect DB: " . $con->connect_error);
  }
  mysqli_select_db($con, $database);
?>