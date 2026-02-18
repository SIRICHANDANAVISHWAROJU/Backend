<?php
  if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $cookie_name = "user";

    if ($action == "update" && isset($_GET['value'])) {
        $cookie_value = $_GET['value'];
        setcookie($cookie_name, $cookie_value, time() + 3600, "/");
    }

    if ($action == "remove") {
        setcookie($cookie_name, "", time() - 3600, "/");
    }

    header("Location: cookie.php");
    exit();
}
  
?>

<html>
  <head>
    <link rel="stylesheet" href="custom_style.css">
  </head>
  <body>
  <div id="cookie_div">
  <?php
    $cookie_name = "user";
    if(!isset($_COOKIE[$cookie_name])) {
      echo "Cookie is not set for this site!";
    } else {
      echo "Cookie '" . $cookie_name . "' is set!<br>";
      echo "Value is: " . $_COOKIE[$cookie_name];
    }
    include "cookie_input.html";
  ?>
  </div>
  </body>
</html>