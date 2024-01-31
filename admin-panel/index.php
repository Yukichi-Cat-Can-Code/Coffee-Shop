<?php 
  require "layouts/header.php" ;
  require "../config/config.php" ;

  if(!isset($_SESSION['admin_name'])){
    header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
  }

  //query for Product
  $products = $conn->query("SELECT * from product");
  $products->execute();

  //query for orders;
  $orders = $conn->query("SELECT * FROM orders");
  $orders->execute();

  //query for Bookings
  $bookings = $conn->query("SELECT * FROM bookings");
  $bookings->execute();

  //query for Admins
  $admin = $conn->query("SELECT * FROM admins");
  $admin->execute();
?>
    <div class="container-fluid">      
      <div class="row">
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Products</h5>
              <p class="card-text">number of products: <?php echo $products->rowcount(); ?></p>
             
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Orders</h5>
              
              <p class="card-text">number of orders: <?php echo $orders->rowcount(); ?></p>
              
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Bookings</h5>
              
              <p class="card-text">number of bookings: <?php echo $bookings->rowcount(); ?></p>
              
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Admins</h5>
              
              <p class="card-text">number of admins: <?php echo $admin->rowcount(); ?></p>
              
            </div>
          </div>
        </div>
      </div>

<?php 
  require "layouts/footer.php" 
?>