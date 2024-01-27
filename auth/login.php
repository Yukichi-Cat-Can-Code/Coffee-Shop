<?php
	require "../includes/header.php";
  require "../config/config.php";
?>
<?php

	if(isset($_SESSION['user_name'])){
		header("location: ".APPURl."");
	}

    if(isset($_POST['submit'])){
      if(empty($_POST['user_email']) || empty($_POST['user_pass']) ){
        echo "<script> alert('one or more infield are empty') </script>";
      }else{
        $user_email = $_POST['user_email'];
        $user_pass = $_POST['user_pass'];

        $login = $conn->query("SELECT * FROM users WHERE user_email = '$user_email'"); 
        $login->execute();

        $fetch = $login->fetch(PDO::FETCH_ASSOC);

        if($login->rowcount() > 0 ){

          if(password_verify($user_pass, $fetch['user_pass'])){
            //start session
			$_SESSION['user_name'] = $fetch['user_name'];
			$_SESSION['user_email'] = $fetch['user_email'];
			$_SESSION['user_id'] = $fetch['ID'];


            header("location: ".APPURl."");
          }else{
            echo "<script> alert('Email or pass is Wrong !!');</script>";
          }
        }else{
          echo "<script> alert('Email or pass is Wrong !!');</script>";
        }
      }
    }

?>

    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_1.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Login</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl; ?>">Home</a></span> <span>Login</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12 ftco-animate">
			<form action="login.php" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
				<h3 class="mb-4 billing-heading">Login</h3>
	          	<div class="row align-items-end">
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
                  <button name="submit" type="submit" class="btn btn-primary py-3 px-4">Login</button>
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

  <script>
		$(document).ready(function(){

		var quantitiy=0;
		   $('.quantity-right-plus').click(function(e){
		        
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		            
		            $('#quantity').val(quantity + 1);

		          
		            // Increment
		        
		    });

		     $('.quantity-left-minus').click(function(e){
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		      
		            // Increment
		            if(quantity>0){
		            $('#quantity').val(quantity - 1);
		            }
		    });
		    
		})
	</script>