<?php
  require "db_connection.php";
  $sql = "SELECT id, name, email FROM users";
  $result = $con->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Records</title>
</head>
<body>
  <h1>User Records</h1>  <?php
  if ($result->num_rows > 0) {
    // Start the table
    echo "<table border='1'>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
        </tr>";
    
    // Output data of each row
    while($row = $result->fetch_assoc()) {
      echo "<tr>
          <td>" . $row["id"] . "</td>
          <td>" . $row["name"] . "</td>
          <td>" . $row["email"] . "</td>
         </tr>";
    }
    echo "</table>";
  } else {
    echo "No users found.";
  }
  ?>
</body>
</html>