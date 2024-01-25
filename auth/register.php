<?php
	require "../includes/header.php";
  require "../config/config.php";
?>
<?php
  if(isset($_POST['submit'])){
    if(empty($_POST['user_name']) || empty($_POST['user_email']) || empty($_POST['user_pass']) ){
      echo "<script> alert('one or more infield are empty') </script>";
    }else{
      $user_name = $_POST['user_name'];
      $user_email = $_POST['user_email'];
      $user_pass = password_hash($_POST['user_pass'], PASSWORD_DEFAULT);
      
      $insert = $conn->prepare("INSERT INTO users(user_name, user_email,user_pass) VALUES(:user_name, :user_email, :user_pass)");
      $insert->execute([
        ":user_name" => $user_name,
        ":user_email" => $user_email,
        ":user_pass" => $user_pass,
      ]);

      header("location: login.php");
    }
  }

?>

    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_2.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Register</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home</a></span> <span>Register</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12 ftco-animate">
			<form action="register.php" method = "POST" class="billing-form ftco-bg-dark p-3 p-md-5">
				<h3 class="mb-4 billing-heading">Register</h3>
	          	<div class="row align-items-end">
                 <div class="col-md-12">
                        <div class="form-group">
                            <label for="Username">Username</label>
                          <input type="text" name="user_name" class="form-control" placeholder="Username">
                        </div>
                 </div>
	          	  <div class="col-md-12">
	                <div class="form-group">
	                	<label for="Email">Email</label>
	                  <input type="text" name="user_email" class="form-control" placeholder="Email">
	                </div>
	              </div>
                 
	              <div class="col-md-12">
	                <div class="form-group">
	                	<label for="Password">Password</label>
	                    <input type="password" name="user_pass" class="form-control" placeholder="Password">
	                </div>

                </div>
                <div class="col-md-12">
                	<div class="form-group mt-4">
							<div class="radio">
                <button class="btn btn-primary py-3 px-4" name="submit" type="submit">Register</button>
						  </div>
					</div>
                </div>

               
	          </form><!-- END -->
          </div> <!-- .col-md-8 -->
          </div>
        </div>
      </div>
    </section> <!-- .section -->

    <?php
	require "../includes/footer.php";
	?>
