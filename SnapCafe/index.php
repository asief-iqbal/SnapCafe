
<?php

include 'connect.php';

$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if(isset($_POST['login'])){
  session_start();

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





  if(isset($_POST['order'])){
    session_start(); 
    // Get the form data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $item = $_POST['Item'];
    $customization = $_POST['Customization'];
    $orderType = $_POST['OrderType'];

    // Generate the OrderID
    $result = mysqli_query($conn, "SELECT OrderID FROM orderdetails ORDER BY OrderID DESC LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    $lastId = substr($row['OrderID'], 1);
    $nextId = 'O' . sprintf('%03d', $lastId + 1);

    // Get the CustomerID from the customer table using the username stored in the session
    $username = $_SESSION['name'];
    $result = mysqli_query($conn, "SELECT CustomerID FROM customer WHERE Name = '$username'");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
      $customerId = $row['CustomerID'];

      // Insert  into the orderdetails table
      $sql = "INSERT INTO orderdetails (OrderID, OrderDate, Item, Customization, OrderType) VALUES ('$nextId', NOW(), '$item', '$customization', '$orderType')";
      if (mysqli_query($conn, $sql)) {
          echo "New record created successfully in orderdetails table";
      } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }

      // Insert into the orders table
      $sql = "INSERT INTO orders (CustomerID, OrderID) VALUES ('$customerId', '$nextId')";
      if (mysqli_query($conn, $sql)) {
          echo "New record created successfully in orders table";
      } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }

      // Update the discount table
      $startDate = date('Y-m-d');
      $endDate = date('Y-m-d', strtotime('+1 month'));
      $sql = "INSERT INTO discount (CustomerID, MonthlyStreak, StartDate, EndDate, DiscountRate) VALUES ('$customerId', 'Yes', '$startDate', '$endDate', '20%') ON DUPLICATE KEY UPDATE MonthlyStreak='Yes', StartDate='$startDate', EndDate='$endDate', DiscountRate='20%'";
      if (mysqli_query($conn, $sql)) {
          echo "Discount table updated successfully";
      } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }
    } else {
      echo "Error: No customer found with the name $username";
    }
  }





  if (isset($_SESSION['name'])) {
      $username = $_SESSION['name'];
      $result = mysqli_query($conn, "SELECT * FROM customer WHERE Name = '$username'");
      $row = mysqli_fetch_assoc($result);
      if ($row) {
          $customerId = $row['CustomerID'];
          $result = mysqli_query($conn, "SELECT * FROM orders JOIN orderdetails ON orders.OrderID = orderdetails.OrderID WHERE CustomerID = '$customerId'");
          $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
      } else {
          echo "Error: No customer found with the name $username";
      }
  }
 

  // Handle the form submission for updating an order
  if(isset($_POST['update_order'])){
    // Get the form data
    $orderId = $_POST['OrderID'];
    $item = $_POST['Item'];
    $customization = $_POST['Customization'];
    $orderType = $_POST['OrderType'];

    // Update the order in the orderdetails table
    $sql = "UPDATE orderdetails SET Item = '$item', Customization = '$customization', OrderType = '$orderType' WHERE OrderID = '$orderId'";
    if (mysqli_query($conn, $sql)) {
        echo "Order updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  }


  // Deleting an order
  if(isset($_POST['delete_order'])){
      $orderId = $_POST['OrderID'];
  
      // Delete the order from the orders table
      $sql = "DELETE FROM orders WHERE OrderID = '$orderId'";
      if (mysqli_query($conn, $sql)) {
          // Delete the order from the orderdetails table
          $sql = "DELETE FROM orderdetails WHERE OrderID = '$orderId'";
          if (mysqli_query($conn, $sql)) {
              echo "Order deleted successfully";
          } else {
              echo "Error: " . $sql . "<br>" . mysqli_error($conn);
          }
      } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
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

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- 
    - primary meta tags
  -->
  <title>Snapcafe: Streak on for your price slice!</title>
  <meta name="title" content="Snapcafe: Streak on for your price slice!">
  <meta name="description" content="This is a Restaurant html template made by codewithsadee">

  <!-- 
    
  
    - favicon
  -->
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">

  <!-- 
    - google font link
  -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;700&family=Forum&display=swap" rel="stylesheet">

  <!-- 
    - custom css link
  -->
  <link rel="stylesheet" href="./assets/css/style.css">

  <!-- 
    - preload images
  -->
  <link rel="preload" as="image" href="./assets/images/hero-slider-1.jpg">
  <link rel="preload" as="image" href="./assets/images/hero-slider-2.jpg">
  <link rel="preload" as="image" href="./assets/images/hero-slider-3.jpg">

</head>

<body id="top">

  <!-- 
    - #PRELOADER
  -->

  <div class="preload" data-preaload>
    <div class="circle"></div>
    <p class="text">SnapCafe</p>
  </div>





  <!-- 
    - #TOP BAR
  -->

  <div class="topbar">
    <div class="container">

      <address class="topbar-item">
        <div class="icon">
          <ion-icon name="location-outline" aria-hidden="true"></ion-icon>
        </div>

        <span class="span">
          Restaurant St, Delicious City, London 9578, UK
        </span>
      </address>

      <div class="separator"></div>

      <div class="topbar-item item-2">
        <div class="icon">
          <ion-icon name="time-outline" aria-hidden="true"></ion-icon>
        </div>

        <span class="span">Daily : 8.00 am to 10.00 pm</span>
      </div>

      <a href="tel:+11234567890" class="topbar-item link">
        <div class="icon">
          <ion-icon name="call-outline" aria-hidden="true"></ion-icon>
        </div>

        <span class="span">+1 123 456 7890</span>
      </a>

      <div class="separator"></div>

      <a href="mailto:booking@restaurant.com" class="topbar-item link">
        <div class="icon">
          <ion-icon name="mail-outline" aria-hidden="true"></ion-icon>
        </div>

        <span class="span">booking@restaurant.com</span>
      </a>

    </div>
  </div>





  <!-- 
    - #HEADER
  -->

  <header class="header" data-header>
    <div class="container">

      <a href="#" class="logo">
        <img src="./assets/images/logo.png" width="160" height="50" alt="SnapCafe - Home">
      </a>

      <nav class="navbar" data-navbar>

        <button class="close-btn" aria-label="close menu" data-nav-toggler>
          <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
        </button>

        <a href="#" class="logo">
          <img src="./assets/images/logo.png" width="160" height="50" alt="SnapCafe - Home">
        </a>

        <ul class="navbar-list">

          <li class="navbar-item">
            <a href="#home" class="navbar-link hover-underline active">
              <div class="separator"></div>

              <span class="span">Home</span>
            </a>
          </li>

          <li class="navbar-item">
            <a href="#menu" class="navbar-link hover-underline">
              <div class="separator"></div>

              <span class="span">Menus</span>
            </a>
          </li>

          <li class="navbar-item">
            <a href="#about" class="navbar-link hover-underline">
              <div class="separator"></div>

              <span class="span">About Us</span>
            </a>
          </li>

          <li class="navbar-item">
            <a href="#" class="navbar-link hover-underline">
              <div class="separator"></div>

              <span class="span">Our Chefs</span>
            </a>
          </li>

          <li class="navbar-item">
            <a href="#" class="navbar-link hover-underline">
              <div class="separator"></div>

              <span class="span">Contact</span>
            </a>
          </li>

        </ul>

        <div class="text-center">
          <p class="headline-1 navbar-title">Visit Us</p>

          <address class="body-4">
            Restaurant St, Delicious City, <br>
            London 9578, UK
          </address>

          <p class="body-4 navbar-text">Open: 9.30 am - 2.30pm</p>

          <a href="mailto:booking@SnapCafe.com" class="body-4 sidebar-link">booking@SnapCafe.com</a>

          <div class="separator"></div>

          <p class="contact-label">Booking Request</p>

          <a href="tel:+88123123456" class="body-1 contact-number hover-underline">
            +88-123-123456
          </a>
        </div>

      </nav>

      <a href="#order" class="btn btn-secondary">
        <span class="text text-1">Find A Table</span>

        <span class="text text-2" aria-hidden="true">Find A Table</span>
      </a>

      <button class="nav-open-btn" aria-label="open menu" data-nav-toggler>
        <span class="line line-1"></span>
        <span class="line line-2"></span>
        <span class="line line-3"></span>
      </button>

      <div class="overlay" data-nav-toggler data-overlay></div>

    </div>
  </header>





  <main>
    <article>

      <!-- 
        - #HERO
      -->

      <section class="hero text-center" aria-label="home" id="home">

        <ul class="hero-slider" data-hero-slider>

          <li class="slider-item active" data-hero-slider-item>

            <div class="slider-bg">
              <img src="./assets/images/hero-slider-1.jpg" width="1880" height="950" alt="" class="img-cover">
            </div>

            <p class="label-2 section-subtitle slider-reveal">Tradational & Hygine</p>

            <h1 class="display-1 hero-title slider-reveal">
              For the love of <br>
              coffee
            </h1>

            <p class="body-2 hero-text slider-reveal">
              Come with family & feel the joy of mouthwatering coffee
            </p>

            <a href="#menu" class="btn btn-primary slider-reveal">
              <span class="text text-1">View Our Menu</span>

              <span class="text text-2" aria-hidden="true">View Our Menu</span>
            </a>

          </li>

          <li class="slider-item" data-hero-slider-item>

            <div class="slider-bg">
              <img src="./assets/images/hero-slider-2.jpg" width="1880" height="950" alt="" class="img-cover">
            </div>

            <p class="label-2 section-subtitle slider-reveal">delightful experience</p>

            <h1 class="display-1 hero-title slider-reveal">
              Flavors Inspired by <br>
              the Seasons
            </h1>

            <p class="body-2 hero-text slider-reveal">
              Come with family & feel the joy of mouthwatering coffee
            </p>

            <a href="#" class="btn btn-primary slider-reveal">
              <span class="text text-1">View Our Menu</span>

              <span class="text text-2" aria-hidden="true">View Our Menu</span>
            </a>

          </li>

          <li class="slider-item" data-hero-slider-item>

            <div class="slider-bg">
              <img src="./assets/images/hero-slider-3.jpg" width="1880" height="950" alt="" class="img-cover">
            </div>

            <p class="label-2 section-subtitle slider-reveal">amazing & delicious</p>

            <h1 class="display-1 hero-title slider-reveal">
              Where every flavor <br>
              tells a story
            </h1>

            <p class="body-2 hero-text slider-reveal">
              Come with family & feel the joy of mouthwatering coffee
            </p>

            <a href="#" class="btn btn-primary slider-reveal">
              <span class="text text-1">View Our Menu</span>

              <span class="text text-2" aria-hidden="true">View Our Menu</span>
            </a>

          </li>

        </ul>

        <button class="slider-btn prev" aria-label="slide to previous" data-prev-btn>
          <ion-icon name="chevron-back"></ion-icon>
        </button>

        <button class="slider-btn next" aria-label="slide to next" data-next-btn>
          <ion-icon name="chevron-forward"></ion-icon>
        </button>

        <a href="#reservation" class="hero-btn has-after">
          <img src="./assets/images/hero-icon.png" width="48" height="48" alt="booking icon">

          <span class="label-2 text-center span">Book A Table</span>
        </a>

      </section>





      <!-- 
        - #SERVICE
      -->

      <section class="section service bg-black-10 text-center" aria-label="service">
        <div class="container">

          <p class="section-subtitle label-2">Flavors For Royalty</p>

          <h2 class="headline-1 section-title">We Offer Top Notch</h2>

          <p class="section-text">
            Lorem Ipsum is simply dummy text of the printing and typesetting industry lorem Ipsum has been the industrys
            standard dummy text ever.
          </p>

          <ul class="grid-list">

            <li>
              <div class="service-card">

                <a href="#" class="has-before hover:shine">
                  <figure class="card-banner img-holder" style="--width: 285; --height: 336;">
                    <img src="./assets/images/service-1.jpg" width="285" height="336" loading="lazy" alt="Breakfast"
                      class="img-cover">
                  </figure>
                </a>

                <div class="card-content">

                  <h3 class="title-4 card-title">
                    <a href="#">Breakfast</a>
                  </h3>

                  <a href="#" class="btn-text hover-underline label-2">View Menu</a>

                </div>

              </div>
            </li>

            <li>
              <div class="service-card">

                <a href="#" class="has-before hover:shine">
                  <figure class="card-banner img-holder" style="--width: 285; --height: 336;">
                    <img src="./assets/images/service-2.jpg" width="285" height="336" loading="lazy" alt="Appetizers"
                      class="img-cover">
                  </figure>
                </a>

                <div class="card-content">

                  <h3 class="title-4 card-title">
                    <a href="#">Appetizers</a>
                  </h3>

                  <a href="#" class="btn-text hover-underline label-2">View Menu</a>

                </div>

              </div>
            </li>

            <li>
              <div class="service-card">

                <a href="#" class="has-before hover:shine">
                  <figure class="card-banner img-holder" style="--width: 285; --height: 336;">
                    <img src="./assets/images/service-3.jpg" width="285" height="336" loading="lazy" alt="Drinks"
                      class="img-cover">
                  </figure>
                </a>

                <div class="card-content">

                  <h3 class="title-4 card-title">
                    <a href="#">Drinks</a>
                  </h3>

                  <a href="#" class="btn-text hover-underline label-2">View Menu</a>

                </div>

              </div>
            </li>

          </ul>

          <img src="./assets/images/shape-1.png" width="246" height="412" loading="lazy" alt="shape"
            class="shape shape-1 move-anim">
          <img src="./assets/images/shape-2.png" width="343" height="345" loading="lazy" alt="shape"
            class="shape shape-2 move-anim">

        </div>
      </section>





      <!-- 
        - #ABOUT
      -->

      <section class="section about text-center" aria-labelledby="about-label" id="about">
        <div class="container">

          <div class="about-content">

            <p class="label-2 section-subtitle" id="about-label">Our Story</p>

            <h2 class="headline-1 section-title">Every Flavor Tells a Story</h2>

            <p class="section-text">
              Lorem Ipsum is simply dummy text of the printingand typesetting industry lorem Ipsum has been the
              industrys standard dummy text ever since the when an unknown printer took a galley of type and scrambled
              it to make a type specimen book It has survived not only five centuries, but also the leap into.
            </p>

            <div class="contact-label">Book Through Call</div>

            <a href="tel:+804001234567" class="body-1 contact-number hover-underline">+80 (400) 123 4567</a>

            <a href="#" class="btn btn-primary">
              <span class="text text-1">Read More</span>

              <span class="text text-2" aria-hidden="true">Read More</span>
            </a>

          </div>

          <figure class="about-banner">

            <img src="./assets/images/about-banner.jpg" width="570" height="570" loading="lazy" alt="about banner"
              class="w-100" data-parallax-item data-parallax-speed="1">

            <div class="abs-img abs-img-1 has-before" data-parallax-item data-parallax-speed="1.75">
              <img src="./assets/images/about-abs-image.jpg" width="285" height="285" loading="lazy" alt=""
                class="w-100">
            </div>

            <div class="abs-img abs-img-2 has-before">
              <img src="./assets/images/badge-2.png" width="133" height="134" loading="lazy" alt="">
            </div>

          </figure>

          <img src="./assets/images/shape-3.png" width="197" height="194" loading="lazy" alt="" class="shape">

        </div>
      </section>





      <!-- 
        - #SPECIAL DISH
      -->

      <section class="special-dish text-center" aria-labelledby="dish-label">

        <div class="special-dish-banner">
          <img src="./assets/images/special-dish-banner.jpg" width="940" height="900" loading="lazy" alt="special dish"
            class="img-cover">
        </div>

        <div class="special-dish-content bg-black-10">
          <div class="container">

            <img src="./assets/images/badge-1.png" width="28" height="41" loading="lazy" alt="badge" class="abs-img">

            <p class="section-subtitle label-2">Special Dish</p>

            <h2 class="headline-1 section-title">Butter Cookie Cake Toppings With Fruits
            </h2>

            <p class="section-text">
              Lorem Ipsum is simply dummy text of the printingand typesetting industry lorem Ipsum has been the
              industrys standard dummy text ever since the when an unknown printer took a galley of type.
            </p>

            <div class="wrapper">
              <del class="del body-3">$40.00</del>

              <span class="span body-1">$20.00</span>
            </div>

            <a href="#" class="btn btn-primary">
              <span class="text text-1">View All Menu</span>

              <span class="text text-2" aria-hidden="true">View All Menu</span>
            </a>

          </div>
        </div>

        <img src="./assets/images/shape-4.png" width="179" height="359" loading="lazy" alt="" class="shape shape-1">

        <img src="./assets/images/shape-9.png" width="351" height="462" loading="lazy" alt="" class="shape shape-2">

      </section>





      <!-- 
        - #MENU
      -->

      <section id ="menu" class="section menu" aria-label="menu-label" id="menu">
        <div class="container">

          <p class="section-subtitle text-center label-2">Special Selection</p>

          <h2 class="headline-1 section-title text-center">Delicious Menu</h2>

          <ul class="grid-list">

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-1.png" width="100" height="100" loading="lazy" alt="Mocha"
                    class="img-cover">
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Mocha</a>
                    </h3>

                    <span class="badge label-1">Most Favourite</span>

                    <span class="span title-2">$7.50</span>
                  </div>

                  <p class="card-text label-1">
                    Espresso, chocolate powder, syrup, milk and cream  
                  </p>

                </div>

              </div>
            </li>

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-2.png" width="100" height="100" loading="lazy" alt="Cappuccino"
                    class="img-cover" >
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Cappuccino</a>
                    </h3>

                    <span class="span title-2">$5.50</span>
                  </div>

                  <p class="card-text label-1">
                    Espresso, steamed milk and foam
                  </p>

                </div>

              </div>
            </li>

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-3.png" width="100" height="100" loading="lazy" alt="Latte"
                    class="img-cover">
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Latte</a>
                    </h3>

                    <span class="span title-2">$5.50</span>
                  </div>

                  <p class="card-text label-1">
                    Espresso, steamed milk and a thin layer of milk foam
                  </p>

                </div>

              </div>
            </li>

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-4.png" width="100" height="100" loading="lazy" alt="Tokusen Wagyu"
                    class="img-cover">
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Frappe</a>
                    </h3>

                    <span class="badge label-1">New</span>

                    <span class="span title-2">$8.50</span>
                  </div>

                  <p class="card-text label-1">
                    Cream, skim milk, coffee extract, milk, cocoa
                  </p>

                </div>

              </div>
            </li>

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-5.png" width="100" height="100" loading="lazy" alt="Hot Chocolate "
                    class="img-cover">
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Hot Chocolate</a>
                    </h3>

                    <span class="span title-2">$7.00</span>
                  </div>

                  <p class="card-text label-1">
                    Cocoa powder, Marshmallows, chocolate chips  
                  </p>

                </div>

              </div>
            </li>

            <li>
              <div class="menu-card hover:card">

                <figure class="card-banner img-holder" style="--width: 100; --height: 100;">
                  <img src="./assets/images/menu-6.png" width="100" height="100" loading="lazy" alt="Opu Fish"
                    class="img-cover">
                </figure>

                <div>

                  <div class="title-wrapper">
                    <h3 class="title-3">
                      <a href="#" class="card-title">Iced Coffee</a>
                    </h3>

                    <span class="span title-2">$6.50
                    </span>
                  </div>

                  <p class="card-text label-1">
                    Hot coffee that's chilled and then served over ice  
                  </p>

                </div>

              </div>
            </li>

          </ul>

          <p class="menu-text text-center">
            During winter daily from <span class="span">7:00 pm</span> to <span class="span">9:00 pm</span>
          </p>

          <a href="#order" class="btn btn-primary">
            <span class="text text-1">Order</span>

            <span class="text text-2" aria-hidden="true">Order</span>
          </a>

          <img src="./assets/images/shape-5.png" width="921" height="1036" loading="lazy" alt="shape"
            class="shape shape-2 move-anim">
          <img src="./assets/images/shape-6.png" width="343" height="345" loading="lazy" alt="shape"
            class="shape shape-3 move-anim">

        </div>
      </section>



          














<!--LOGIN--->

<?php
if (isset($_SESSION['user_id'])) {
  echo 'Logged in successfully'; // If the user logs innnnnn
}
?>
<section class="Login" id = "login">
  <div class="container">
    <div class="form login-form bg-black-10">
      <?php if (!isset($_SESSION['name'])): ?>
        <div id="loginForm">
        <form action="index.php" method="post" class="form-left">
            <h2 class="headline-1 text-center">Login</h2>
            <div class="input-wrapper">
              <select id="login-usertype" name="usertype" required class="input-field">
                <option value="">Select User Type</option>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
              </select>
              <input type="text" id="login-name" name="name" placeholder="Username" required class="input-field">
              <input type="password" id="login-password" name="password" placeholder="Password" required class="input-field">
            </div>
            <input type="submit" name="login" value="login" class="btn btn-secondary text text-1">
          </form>
        </div>
    
<!-- Registration Section -->
<section id = "registration" class="Registration">
        <div class="container">
          <div class="form reservation-form bg-black-10">
            <button onclick="showRegistrationForm()" class="btn btn-primary centerbutton">
              <span class="text text-1 ">New User?</span>
              <span class="text text-2" aria-hidden="true">Register</span>
            </button> 
            <form action="index.php" method="post" class="form-left" id="registrationForm";>
              <h2 class="headline-1 text-center">Registration</h2>
              <div class="input-wrapper">
                <select id="register-usertype" name="usertype" required class="input-field" onchange="showUserTypeFields()">
                  <option value="">Select User Type</option>
                  <option value="customer">Customer</option>
                  <option value="admin">Admin</option>
                  <option value="employee">Employee</option>
                </select>
                <div id="commonFields">
                  <input type="text" id="register-name" name="name" placeholder="Username" required class="input-field">
                  <input type="email" id="register-email" name="email" placeholder="Email" required class="input-field">
                  <input type="password" id="register-password" name="password" placeholder="Password" required class="input-field">
                </div>
                <div id="customerFields" style="display: none;">
                  <input type="text" id="register-address" name="address" placeholder="Address" required class="input-field">
                  <input type="text" id="register-phone" name="phone" placeholder="Phone" required class="input-field">
                </div>
                <div id="employeeFields" style="display: none;">
                  <input type="text" id="register-designation" name="designation" placeholder="Designation" required class="input-field">
                  <input type="text" id="register-payroll" name="payroll" placeholder="Payroll" required class="input-field">
                </div>
              </div>
              <input type="submit" name="register" value="register" class="btn btn-secondary text text-1">
            </form>
          </div>
        </div>
      </section> 






        <?php else: ?>
          <div id="welcome">
            <h2 class="headline-1 text-center" id="welcomeMessage">Welcome, <?php echo $_SESSION['name']; ?></h2>
            <form action="user_logout.php" method="post" id="logoutForm">
              <input type="submit" name="logout" value="Logout" class="btn btn-secondary text text-1 centerbutton">
            </form>
          </div>
    </div>
  </div>
</section>
<?php ?>






    <!-- Order Section -->
<!-- Order Section -->
<!-- Order Section -->
<section class="order">
        <div class="container">
            <div class="form order-form bg-black-10">
                <!-- Specify the method as POST and the action as the PHP file that will handle the form data -->
                <form action="index.php" method="POST" class="form-left">
                    <h2 class="headline-1 text-center">Order</h2>
                    <p class="form-text text-center">
                        Order request <a href="tel:+88123123456" class="link">+88-123-123456</a>
                        or fill out the order form
                    </p>
                    <div class="input-wrapper">
                        <input type="text" name="name" placeholder="Your Name" autocomplete="off" class="input-field">
                        <input type="tel" name="phone" placeholder="Phone Number" autocomplete="off" class="input-field">
                    </div>
                    <div class="input-wrapper">
                        <div class="icon-wrapper">
                            <ion-icon name="fast-food-outline" aria-hidden="true"></ion-icon>
                            <select name="Item" class="input-field">
                                <option>Select your coffee</option>
                                <option value="Mocha">Mocha - $7.50</option>
                                <option value="Cappuccino">Cappuccino - $5.50</option>
                                <option value="Latte">Latte $5.50</option>
                                <option value="Frappe">Frappe $8.50</option>
                                <option value="Hot Chocolate">Hot Chocolate $7.00</option>
                                <option value="Iced Coffee">Iced Coffee  $6.50</option>
                            </select>
                            <ion-icon name="chevron-down" aria-hidden="true"></ion-icon>
                        </div>
                        <div class="icon-wrapper">
                            <ion-icon name="construct-outline" aria-hidden="true"></ion-icon>
                            <input type="text" name="Customization" placeholder="Customization" autocomplete="off" class="input-field">
                        </div>
                    </div>
                    <div class="input-wrapper">
                        <div class="icon-wrapper">
                            <ion-icon name="restaurant-outline" aria-hidden="true"></ion-icon>
                            <select name="OrderType" class="input-field">
                                <option>Select Order Type</option>
                                <option value="Online">Online</option>
                                <option value="Dine In">Dine In</option>
                                <option value="Take Home">Take Home</option>
                            </select>
                            <ion-icon name="chevron-down" aria-hidden="true"></ion-icon>
                        </div>
                    </div>
                    <textarea name="message" placeholder="Message" autocomplete="off" class="input-field"></textarea>
                    <button type="submit" name="order" class="btn btn-secondary">
                        <span class="text text-1">Place Order</span>
                        <span class="text text-2" aria-hidden="true">Place Order</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Order History Section -->
    <section class="order-history py-5">
        <div class="container">
            <h2 class="headline-1 text-center mb-5">Order History</h2>
            <div class="row justify-content-center">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 d-flex justify-content-center">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Order ID: <?php echo $order['OrderID']; ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">Order Date: <?php echo $order['OrderDate']; ?></h6>
                                <p class="card-text">Item: <?php echo $order['Item']; ?></p>
                                <p class="card-text">Customization: <?php echo $order['Customization']; ?></p>
                                <p class="card-text">Order Type: <?php echo $order['OrderType']; ?></p>
                                <!-- Update order form -->
                                <form action="index.php" method="POST" class="form-inline order-update-form">
                                    <input type="hidden" name="OrderID" value="<?php echo $order['OrderID']; ?>">
                                    <select name="Item" class="form-control">
                                        <option value="Mocha" <?php echo $order['Item'] == 'Mocha' ? 'selected' : ''; ?>>Mocha</option>
                                        <option value="Cappuccino" <?php echo $order['Item'] == 'Cappuccino' ? 'selected' : ''; ?>>Cappuccino</option>
                                        <option value="Latte" <?php echo $order['Item'] == 'Latte' ? 'selected' : ''; ?>>Latte</option>
                                        <option value="Frappe" <?php echo $order['Item'] == 'Frappe' ? 'selected' : ''; ?>>Frappe</option>
                                        <option value="Hot Chocolate" <?php echo $order['Item'] == 'Hot Chocolate' ? 'selected' : ''; ?>>Hot Chocolate</option>
                                        <option value="Iced Coffee" <?php echo $order['Item'] == 'Iced Coffee' ? 'selected' : ''; ?>>Iced Coffee</option>
                                    </select>
                                    <input type="text" name="Customization" value="<?php echo $order['Customization']; ?>" class="form-control">
                                    <select name="OrderType" class="form-control">
                                        <option value="Dine In" <?php echo $order['OrderType'] == 'Dine In' ? 'selected' : ''; ?>>Dine In</option>
                                        <option value="Take Home" <?php echo $order['OrderType'] == 'Take Home' ? 'selected' : ''; ?>>Take Home</option>
                                        <option value="Online" <?php echo $order['OrderType'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                                    </select>
                                    <button type="submit" name="update_order" class="btn btn-secondary ml-2">Update</button>
                                </form>
                                <!-- Delete order button -->
                                <form action="index.php" method="POST" class="form-inline order-delete-form ml-2">
                                    <input type="hidden" name="OrderID" value="<?php echo $order['OrderID']; ?>">
                                    <button type="submit" name="delete_order" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>



































<?php
//Reservation
if(isset($_POST['reservation'])){
  session_start();
  // Get the form data
  $reservationDate = $_POST['ReservationDate'];
  $seats = $_POST['Seats'];

  // Generate the ReservationID
  $result = mysqli_query($conn, "SELECT ReservationID FROM reservation ORDER BY ReservationID DESC LIMIT 1");
  $row = mysqli_fetch_assoc($result);
  $lastId = substr($row['ReservationID'], 1);
  $nextId = 'R' . sprintf('%03d', $lastId + 1);

  // Get the CustomerID from the customer table using the username stored in the session
  $name = $_SESSION['name'];
  $result = mysqli_query($conn, "SELECT CustomerID FROM customer WHERE Name = '$name'");
  $row = mysqli_fetch_assoc($result);
  $customerId = $row['CustomerID'];

  // Insert the data into the reservation table
  $sql = "INSERT INTO reservation (ReservationID, ReservationDate, Seats, CustomerID) VALUES ('$nextId', '$reservationDate', '$seats', '$customerId')";
  if (mysqli_query($conn, $sql)) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }
} 
?>
    <!-- Reservation Section -->
    <section class="reservation">
        <div class="container">
            <div class="form reservation-form bg-black-10">
                <!-- Specify the method as POST and the action as the PHP file that will handle the form data -->
                <form action="index.php" method="POST" class="form-left">
                    <h2 class="headline-1 text-center">Online Reservation</h2>
                    <p class="form-text text-center">
                        Booking request <a href="tel:+88123123456" class="link">+88-123-123456</a>
                        or fill out the order form
                    </p>
                    <div class="input-wrapper">
                        <input type="text" name="name" placeholder="Your Name" autocomplete="off" class="input-field">
                        <input type="tel" name="phone" placeholder="Phone Number" autocomplete="off" class="input-field">
                    </div>
                    <div class="input-wrapper">
                        <div class="icon-wrapper">
                            <ion-icon name="person-outline" aria-hidden="true"></ion-icon>
                            <select name="Seats" class="input-field">
                                <option value="1">1 Person</option>
                                <option value="2">2 Person</option>
                                <option value="3">3 Person</option>
                                <option value="4">4 Person</option>
                                <option value="5">5 Person</option>
                                <option value="6">6 Person</option>
                                <option value="7">7 Person</option>
                            </select>
                            <ion-icon name="chevron-down" aria-hidden="true"></ion-icon>
                        </div>
                        <div class="icon-wrapper">
                            <ion-icon name="calendar-clear-outline" aria-hidden="true"></ion-icon>
                            <input type="date" name="ReservationDate" class="input-field">
                            <ion-icon name="chevron-down" aria-hidden="true"></ion-icon>
                        </div>
                    </div>
                    <textarea name="message" placeholder="Message" autocomplete="off" class="input-field"></textarea>
                    <button type="submit" name="reservation" class="btn btn-secondary">
                        <span class="text text-1">Book A Table</span>
                        <span class="text text-2" aria-hidden="true">Book A Table</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
      <?php endif; ?>





























































      <!-- 
        - #FEATURES
      -->

      <section class="section features text-center" aria-label="features">
        <div class="container">

          <p class="section-subtitle label-2">Why Choose Us</p>

          <h2 class="headline-1 section-title">Our Strength</h2>

          <ul class="grid-list">

            <li class="feature-item">
              <div class="feature-card">

                <div class="card-icon">
                  <img src="./assets/images/features-icon-1.png" width="100" height="80" loading="lazy" alt="icon">
                </div>

                <h3 class="title-2 card-title">Hygienic beverage</h3>

                <p class="label-1 card-text">Lorem Ipsum is simply dummy printing and typesetting.</p>

              </div>
            </li>

            <li class="feature-item">
              <div class="feature-card">

                <div class="card-icon">
                  <img src="./assets/images/features-icon-2.png" width="100" height="80" loading="lazy" alt="icon">
                </div>

                <h3 class="title-2 card-title">Fresh Environment</h3>

                <p class="label-1 card-text">Lorem Ipsum is simply dummy printing and typesetting.</p>

              </div>
            </li>

            <li class="feature-item">
              <div class="feature-card">

                <div class="card-icon">
                  <img src="./assets/images/features-icon-3.png" width="100" height="80" loading="lazy" alt="icon">
                </div>

                <h3 class="title-2 card-title">Skilled Chefs</h3>

                <p class="label-1 card-text">Lorem Ipsum is simply dummy printing and typesetting.</p>

              </div>
            </li>

            <li class="feature-item">
              <div class="feature-card">

                <div class="card-icon">
                  <img src="./assets/images/features-icon-4.png" width="100" height="80" loading="lazy" alt="icon">
                </div>

                <h3 class="title-2 card-title">Event & Party</h3>

                <p class="label-1 card-text">Lorem Ipsum is simply dummy printing and typesetting.</p>

              </div>
            </li>

          </ul>

          <img src="./assets/images/shape-7.png" width="208" height="178" loading="lazy" alt="shape"
            class="shape shape-1">

          <img src="./assets/images/shape-8.png" width="120" height="115" loading="lazy" alt="shape"
            class="shape shape-2">

        </div>
      </section>





      <!-- 
        - #EVENT
      -->

      <section class="section event bg-black-10" aria-label="event">
        <div class="container">

          <p class="section-subtitle label-2 text-center">Recent Updates</p>

          <h2 class="section-title headline-1 text-center">Upcoming Event</h2>

          <ul class="grid-list">

            <li>
              <div class="event-card has-before hover:shine">

                <div class="card-banner img-holder" style="--width: 350; --height: 450;">
                  <img src="./assets/images/event-1.jpg" width="350" height="450" loading="lazy"
                    alt="Flavour so good you’ll try to eat with your eyes." class="img-cover">

                  <time class="publish-date label-2" datetime="2022-09-15">15/09/2022</time>
                </div>

                <div class="card-content">
                  <p class="card-subtitle label-2 text-center">Drinks, Flavour</p>

                  <h3 class="card-title title-2 text-center">
                    Flavour so good you’ll try to eat with your eyes.
                  </h3>
                </div>

              </div>
            </li>

            <li>
              <div class="event-card has-before hover:shine">

                <div class="card-banner img-holder" style="--width: 350; --height: 450;">
                  <img src="./assets/images/event-2.jpg" width="350" height="450" loading="lazy"
                    alt="Flavour so good you’ll try to eat with your eyes." class="img-cover">

                  <time class="publish-date label-2" datetime="2022-09-08">08/09/2022</time>
                </div>

                <div class="card-content">
                  <p class="card-subtitle label-2 text-center">Healthy beverage</p>

                  <h3 class="card-title title-2 text-center">
                    Flavour so good you’ll try to eat with your eyes.
                  </h3>
                </div>

              </div>
            </li>

            <li>
              <div class="event-card has-before hover:shine">

                <div class="card-banner img-holder" style="--width: 350; --height: 450;">
                  <img src="./assets/images/event-3.jpg" width="350" height="450" loading="lazy"
                    alt="Flavour so good you’ll try to eat with your eyes." class="img-cover">

                  <time class="publish-date label-2" datetime="2022-09-03">03/09/2022</time>
                </div>

                <div class="card-content">
                  <p class="card-subtitle label-2 text-center">Recipie</p>

                  <h3 class="card-title title-2 text-center">
                    Flavour so good you’ll try to eat with your eyes.
                  </h3>
                </div>

              </div>
            </li>

          </ul>

          <a href="#" class="btn btn-primary">
            <span class="text text-1">View Our Blog</span>

            <span class="text text-2" aria-hidden="true">View Our Blog</span>
          </a>

        </div>
      </section>

    </article>
  </main>





  <!-- 
    - #FOOTER
  -->

  <footer class="footer section has-bg-image text-center"
    style="background-image: url('./assets/images/footer-bg.jpg')">
    <div class="container">

      <div class="footer-top grid-list">

        <div class="footer-brand has-before has-after">

          <a href="#" class="logo">
            <img src="./assets/images/logo.png" width="160" height="50" loading="lazy" alt="SnapCafe home">
          </a>

          <address class="body-4">
            Restaurant St, Delicious City, London 9578, UK
          </address>

          <a href="mailto:booking@SnapCafe.com" class="body-4 contact-link">booking@SnapCafe.com</a>

          <a href="tel:+88123123456" class="body-4 contact-link">Booking Request : +88-123-123456</a>

          <p class="body-4">
            Open : 09:00 am - 01:00 pm
          </p>

          <div class="wrapper">
            <div class="separator"></div>
            <div class="separator"></div>
            <div class="separator"></div>
          </div>

          <p class="title-1">Get News & Offers</p>

          <p class="label-1">
            Subscribe us & Get <span class="span">25% Off.</span>
          </p>

          <form action="index.php" method="post" class="input-wrapper">
            <div class="icon-wrapper">
              <ion-icon name="mail-outline" aria-hidden="true"></ion-icon>
              <input type="email" name="email_address" placeholder="Your email" autocomplete="off" class="input-field">
            </div>
          
            <button type="submit" class="btn btn-secondary">
              <span class="text text-1">Subscribe</span>
              <span class="text text-2" aria-hidden="true">Subscribe</span>
            </button>
          </form>

        </div>
      
        <ul class="footer-list">

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Home</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Menus</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">About Us</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Our Chefs</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Contact</a>
          </li>

        </ul>

        <ul class="footer-list">

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Facebook</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Instagram</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Twitter</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Youtube</a>
          </li>

          <li>
            <a href="#" class="label-2 footer-link hover-underline">Google Map</a>
          </li>

        </ul>

      </div>

      <div class="footer-bottom">

        <p class="copyright">
          &copy; 2022 SnapCafe. All Rights Reserved | Crafted by <a href="https://github.com/codewithsadee"
            target="_blank" class="link">codewithsadee</a>
        </p>

      </div>

    </div>
  </footer>





  <!-- 
    - #BACK TO TOP
  -->

  <a href="#top" class="back-top-btn active" aria-label="back to top" data-back-top-btn>
    <ion-icon name="chevron-up" aria-hidden="true"></ion-icon>
  </a>





  <!-- 
    - custom js link
  -->
  <script src="./assets/js/script.js"></script>

  <!-- 
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>

</html>