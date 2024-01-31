<?php
    ob_start();
	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php

	if(!isset($_SERVER['HTTP_REFERER'])){
		header("location:".APPURl."");
		exit;
	}
	if (isset($_POST['submit'])) {
		$reviews = $_POST['review'];
		$user_name = $_SESSION['user_name'];

		if (empty($reviews)) {
			echo "<script> alert('one or more field are empty !!');</script>";
		} else {
			$place_order_query = $conn->prepare("INSERT INTO reviews(review,user_name)
				VALUES(:review,:user_name)");

			$place_order_query->execute([
				":review" => $reviews,
				":user_name" => $user_name,
			]);

			header("location:".APPURl."");
            ob_end_flush();
		}
	}
?>


    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Reviews</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ?>">Home </a></span> <span>Review</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12 ftco-animate">
				<form  action="write-reviews.php" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
					<h3 class="mb-4 billing-heading">Write a Review</h3>
					<div class="row align-items-end">
						<div class="col-md-6">
						<div class="form-group">
							<label for="review">Review *</label>
						<input type="text" name="review" id="review" class="form-control" placeholder="Write Review">
						</div>
					</div>
                	<div class="w-100"></div>
					<div class="col-md-12">
						<div class="form-group mt-4">
							<div class="radio">
								<p><button name="submit" type="submit" class="btn btn-primary py-3 px-4">Submit</button></p>
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

