<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "SnapCafe";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";






//Login
if(isset($_POST['login'])){
  session_start();

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $usertype = $_POST['usertype'];

  switch($usertype) {
      case 'customer':
          $table = 'customer';
          break;
      case 'admin':
          $table = 'admin';
          break;
      case 'employee':
          $table = 'employee';
          break;
      default:
          echo "Invalid user type";
          exit();
  }

  $query = "SELECT * FROM $table WHERE Name='$name' AND Password='$password'";
  $result = mysqli_query($conn, $query);

  if(mysqli_num_rows($result) > 0){
      $_SESSION['name'] = $name;
      $_SESSION['usertype'] = $usertype;
  } else {
      echo "Invalid name or password";
  }
} 
if (isset($_SESSION['name'])) {
        header('Location: index.php');
}

// Logout
if(isset($_POST['logout'])){
  unset($_SESSION['name']);
  unset($_SESSION['usertype']);
  session_destroy();
  header('Location: index.php');
  exit();
}


//registration
if(isset($_POST['register'])){

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $usertype = mysqli_real_escape_string($conn, $_POST['usertype']);

    if($usertype == 'customer'){
        $table = 'customer';
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $result = mysqli_query($conn, "SELECT CustomerID FROM $table ORDER BY CustomerID DESC LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        $lastId = substr($row['CustomerID'], 1);
        $nextId = 'C' . sprintf('%03d', $lastId + 1);
        $query = "INSERT INTO $table (CustomerID, Name, Email, Password, Address, Phone) VALUES ('$nextId', '$name', '$email', '$password', '$address', '$phone')";
    } elseif($usertype == 'admin'){
        $table = 'admin';
        $result = mysqli_query($conn, "SELECT AdminID FROM $table ORDER BY AdminID DESC LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        $lastId = substr($row['AdminID'], 1);
        $nextId = 'A' . sprintf('%03d', $lastId + 1);
        $query = "INSERT INTO $table (AdminID, Name, Email, Password) VALUES ('$nextId', '$name', '$email', '$password')";
    } elseif($usertype == 'employee'){
        $table = 'employee';
        $designation = mysqli_real_escape_string($conn, $_POST['designation']);
        $payroll = mysqli_real_escape_string($conn, $_POST['payroll']);
        $result = mysqli_query($conn, "SELECT EmployeeID FROM $table ORDER BY EmployeeID DESC LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        $lastId = substr($row['EmployeeID'], 1);
        $nextId = 'E' . sprintf('%03d', $lastId + 1);
        $query = "INSERT INTO $table (EmployeeID, Name, Email, Password, Designation, Payroll) VALUES ('$nextId', '$name', '$email', '$password', '$designation', '$payroll')";
    } else {
        // Invalid user type, handle the error
        echo "<script>alert('Invalid user type');</script>";
        exit;
    }

    if(mysqli_query($conn, $query)){
        // User registered, set a session variable and redirect to login page
        session_start();
        $_SESSION['message'] = 'Registration successful';
        header('Location: index.php');
    } else {
        // Registration failed, show an error message
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}



if ($_POST['register']) {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $phone = mysqli_real_escape_string($conn, $_POST['phone']);
  $orderType = $_POST['Ordertype'];
  $menu = $_POST['Menu'];
  $cost = $_POST['Cost'];
  $customization = mysqli_real_escape_string($conn, $_POST['Customization']);

  // Get the last OrderID
  $result = mysqli_query($conn, "SELECT OrderID FROM orderdetails ORDER BY OrderID DESC LIMIT 1");
  $row = mysqli_fetch_assoc($result);
  $lastId = substr($row['OrderID'], 1);
  $nextId = 'O' . sprintf('%03d', $lastId + 1);

  // Get current date and time
  $orderDate = date('Y-m-d');
  $orderTime = date('H:i:s');

  // Insert order details into the database
  $query = "INSERT INTO orderdetails (OrderID, Order_Date, Order_Time, Item, Customization, Cost, OrderType) VALUES ('$nextId', '$orderDate', '$orderTime', '$menu', '$customization', '$cost', '$orderType')";
  if (mysqli_query($conn, $query)) {
      echo "Order placed successfully";
  } else {
      echo "Error: " . $query . "<br>" . mysqli_error($conn);
  }
}






// Email Subscription
if(isset($_POST['email_address'])){
  $email = $_POST['email_address'];
  $conn = mysqli_connect($servername, $username, $password, $dbname);

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  $query = "SELECT CustomerID FROM customer WHERE Email = '$email'";
  $result = mysqli_query($conn, $query);

  if(mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_assoc($result);
    $customer_id = $row['CustomerID'];

    $query = "INSERT INTO emailwishlist (CustomerID, Email) VALUES ('$customer_id', '$email')";
    $result = mysqli_query($conn, $query);

    if($result){
      session_start(); 
      $_SESSION['message'] = 'Subscription Successful';
      header('Location: index.php');
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  } else {
    session_start(); 
    $_SESSION['message'] = 'No User exists with this email try registering'; 
  }


  mysqli_close($conn);
}


?>
