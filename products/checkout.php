<?php

	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php
if (isset($_POST['submit'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $streetaddress = $_POST['streetaddress'];
    $Appartment = $_POST['Appartment'];
    $towncity = $_POST['towncity'];
    $postcodezip = $_POST['postcodezip'];
    $phone = $_POST['phone'];
    $emailaddress = $_POST['emailaddress'];
    $payable_total_cost = $_SESSION['payable_total_cost'];
    $user_id = $_SESSION['user_id'];

    if (empty($firstname) || empty($lastname) || empty($streetaddress) || empty($towncity) || empty($postcodezip) || empty($phone) || empty($emailaddress)) {
        echo "<script> alert('one or more field are empty !!');</script>";
    } else {
        $place_order_query = $conn->prepare("INSERT INTO orders(firstname, lastname, streetaddress, appartment, towncity, postcode, phone, email, payable_total_cost, user_id)
            VALUES(:firstname, :lastname, :streetaddress, :appartment, :towncity, :postcode, :phone, :email, :payable_total_cost, :user_id)");

        $place_order_query->execute([
            ":firstname" => $firstname,
            ":lastname" => $lastname,
            ":streetaddress" => $streetaddress,
            ":appartment" => $Appartment,
            ":towncity" => $towncity,
            ":postcode" => $postcodezip,
            ":phone" => $phone,
            ":email" => $emailaddress,
            ":payable_total_cost" => $payable_total_cost,
            ":user_id" => $user_id,
        ]);

        header("location: pay.php");
    }
}
?>


    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Checkout</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ?>">Home </a></span> <span>Checout</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12 ftco-animate">
				<form  action="checkout.php" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
					<h3 class="mb-4 billing-heading">Billing Details</h3>
					<div class="row align-items-end">
						<div class="col-md-6">
						<div class="form-group">
							<label for="firstname">Firt Name *</label>
						<input type="text" name="firstname" id="firstname" class="form-control" placeholder="">
						</div>
					</div>
	              	<div class="col-md-6">
						<div class="form-group">
							<label for="lastname">Last Name *</label>
						<input type="text" name="lastname" class="form-control" placeholder="">
						</div>
                	</div>

					<div class="w-100"></div>
						<div class="col-md-12">
							<div class="form-group">
							<label for="streetaddress">Street Address *</label>
						<input type="text" name="streetaddress" class="form-control" placeholder="House number and street name">
						</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
						<input type="text" class="form-control" name="Appartment" placeholder="Appartment, suite, unit etc: (optional)">
						</div>
						</div>
						<div class="w-100"></div>
						<div class="col-md-6">
							<div class="form-group">
							<label for="towncity">Town / City *</label>
						<input type="text" name="towncity" class="form-control" placeholder="">
						</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="postcodezip">Postcode / ZIP *</label>
						<input type="text" name="postcodezip" class="form-control" placeholder="">
						</div>
						</div>
						<div class="w-100"></div>
						<div class="col-md-6">
						<div class="form-group">
							<label for="phone">Phone *</label>
						<input type="text" name="phone" class="form-control" placeholder="">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="emailaddress">Email Address *</label>
						<input type="text" name="emailaddress" class="form-control" placeholder="">
						</div>
					</div>
                	<div class="w-100"></div>
					<div class="col-md-12">
						<div class="form-group mt-4">
							<div class="radio">
								<p><button name="submit" type="submit" class="btn btn-primary py-3 px-4">Place an order</button></p>
							</div>
						</div>
					</div>
	            </div>
	          </form><!-- END -->


          </div> <!-- .col-md-8 -->

           
          </div>

        </div>
      </div>
    </section> <!-- .section -->

	<?php require "../includes/footer.php"; ?>

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
		    
		});
	</script>

