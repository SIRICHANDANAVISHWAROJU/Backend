<?php
  session_start();
  
  if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if($_POST['log_out_btn']){
      // destroy all session data
      session_unset();
      session_destroy();

      // Redirect to the login page
      header("Location: login.php");
      exit();
    }
  }
?>


<!DOCTYPE html>
<html>
<head>
  <title>CSCI 6040</title>
  <link rel="stylesheet" href="custom_style.css">
</head>
<body>
  <div id="content_div">
    <h1>Welcome to CSCI 6040</h1>
    <h2>Dashboard Under-contstruction</h2>
    <h3>User: <?php echo $_SESSION['user_name']; ?> 	| Email: <?php echo $_SESSION['user_email'];?></h3>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      No content present yet!
      <input type="submit" id="submit_btn" name="log_out_btn" value="Log Out">
    </form>
  </div>
</body>
</html>