<?php 
  require "../layouts/header.php";
  require "../../config/config.php" ;
?>
<?php

if(isset($_SESSION['admin_name'])){
  header("location: ".ADMINAPPURL."");
}

  if(isset($_POST['submit'])){
    if(empty($_POST['admin_email']) || empty($_POST['admin_password']) ){
      echo "<script> alert('one or more infield are empty') </script>";
    }else{
      $admin_email = $_POST['admin_email'];
      $admin_password = $_POST['admin_password'];

      $login = $conn->query("SELECT * FROM admins WHERE admin_email = '$admin_email'"); 
      $login->execute();

      $fetch = $login->fetch(PDO::FETCH_ASSOC);

      if($login->rowcount() > 0 ){

        if(password_verify($admin_password, $fetch['admin_password'])){
          // start session
          $_SESSION['admin_name'] = $fetch['admin_name'];
          $_SESSION['admin_email'] = $fetch['admin_email'];
          $_SESSION['admin_id'] = $fetch['ID'];


          header("location: ".ADMINAPPURL."");
        }else{
          echo "<script> alert('Email or pass is Wrong !!');</script>";
        }
      }else{
        echo "<script> alert('Email or pass is Wrong !!');</script>";
      }
    }
  }

?>

<div class="container-fluid"> 
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mt-5">Login</h5>
              <form method="POST" action="login-admins.php" class="p-auto">
                  <!-- Email input -->
                  <div class="form-outline mb-4">
                    <input type="email" name="admin_email" id="form2Example1" class="form-control" placeholder="Email" />
                   
                  </div>

                  
                  <!-- Password input -->
                  <div class="form-outline mb-4">
                    <input type="password" name="admin_password" id="form2Example2" placeholder="Password" class="form-control" />
                    
                  </div>



                  <!-- Submit button -->
                  <button type="submit" name="submit" class="btn btn-primary  mb-4 text-center">Login</button>

                 
                </form>

            </div>
       </div>
     </div>
    </div>
</div>
<?php 
  require "../layouts/footer.php" 
?>