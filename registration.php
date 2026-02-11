<?php
  session_start();
  require "db_connection.php";

  if(!isset($_SESSION['user_id'])) 
  {
    header("Location: dashboard.php");
    exit();
  }


  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $con->prepare("INSERT INTO users (name, email, password) 	VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
      echo "
      <script>
        alert('New user added successfully! Please Log In');
        document.location = 'login.php';
      </script>";
    } else {
      echo "Error: " . $stmt->error;
    }
    $stmt->close();
  }
?>


<!DOCTYPE html>
<html>
<head>
  <title>CSCI 4060</title>
  <link rel="stylesheet" href="custom_style.css">
</head>
<body>
  <div id="content_div">
    <h1>Insert New User</h1>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <input type="text" name="name" placeholder="Enter your name" required><br><br>
      <input type="email" name="email" placeholder="Enter your email" required><br><br>
      <input type="password" name="password" placeholder="Enter preferred password" required><br><br>
      <input type="submit" id="submit_btn" name="register_in_btn" value="Register">
    </form>
    <h3>Already a user? <a href='login.php'> Log In Here!</a></h3>
  </div>
</body>
</html>