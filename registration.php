<?php
  session_start();
  require "db_connection.php";

  if(isset($_SESSION['user_id'])) 
  {
    header("Location: dashboard.php");
    exit();
  }


  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];

    $password = $_POST['password'];
    $hashed_password = md5($password);

    $check_stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "
        <script>
            alert('Email already registered! Please login.');
            document.location = 'login.php';
        </script>";
    } else {

    $stmt = $con->prepare("INSERT INTO users (name, email, password) 	VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

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

    $check_stmt->close();
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