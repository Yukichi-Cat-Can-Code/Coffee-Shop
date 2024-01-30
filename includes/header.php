<?php

    session_start();
    define("APPURl", "http://localhost/coffee-Shop");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Coffee - Free Bootstrap 4 Template by Colorlib</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">

    <link rel="stylesheet" href=" <?php echo APPURl ?>/css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/animate.css">
    
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/magnific-popup.css">

    <link rel="stylesheet" href="<?php echo APPURl ?>/css/aos.css">

    <link rel="stylesheet" href="<?php echo APPURl ?>/css/ionicons.min.css">

    <link rel="stylesheet" href="<?php echo APPURl ?>/css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/jquery.timepicker.css">

    
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/flaticon.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/icomoon.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo APPURl ?>/css/custom.css">
  </head>
  <body>
  	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="<?php echo APPURl?>">Coffee<small>Blend</small></a>
	      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>
	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item active"><a href="<?php echo APPURl; ?>" class="nav-link">Home</a></li>
	          <li class="nav-item"><a href="<?php echo APPURl ?>/menu.php" class="nav-link">Menu</a></li>
	          <li class="nav-item"><a href="<?php echo APPURl ?>/services.php" class="nav-link">Services</a></li>
	          <li class="nav-item"><a href="<?php echo APPURl ?>/about.php" class="nav-link">About</a></li>
	         
	          <li class="nav-item"><a href="<?php echo APPURl; ?>/contact.php" class="nav-link">Contact</a></li>
           
            <?php if(isset($_SESSION['user_name'])): ?>

                <li class="nav-item cart dropdown">
                  <a href="<?php echo APPURl ?>/products/cart.php" class="nav-link dropdown-toggle" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="icon icon-shopping_cart"></span>
                  </a>
                </li>
                
                <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                      <?php echo $_SESSION['user_name']; ?>
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="<?php echo APPURl; ?>/users/bookings.php">Bookings</a></li>
                        <li><a class="dropdown-item" href="<?php echo APPURl; ?>/users/orders.php">Orders</a></li>
                        <li><a class="dropdown-item" href="<?php echo APPURl; ?>/auth/logout.php">Logout</a></li>
                      </ul>
                </li>
            <?php else: ?>
                <li class="nav-item"><a href="<?php echo APPURl ?>/auth/login.php" class="nav-link">login</a></li>
                <li class="nav-item"><a href="<?php echo APPURl ?>/auth/register.php" class="nav-link">register</a></li>
            <?php endif; ?>
	        </ul>
	      </div>
		</div>
	  </nav>
    <!-- END nav -->