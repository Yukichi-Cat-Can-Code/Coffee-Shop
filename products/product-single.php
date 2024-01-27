<?php
	require "../includes/header.php";
	require "../config/config.php";
?>
<?php
	if(isset($_GET['id'])){

		//code for single product
		$product_id = $_GET['id'];
		$product = $conn->query("SELECT * FROM product WHERE ID = '$product_id'");
		$product->execute();
		$single_product = $product->fetch(PDO::FETCH_OBJ);

		//code for related product
		$related_product = $conn->query("SELECT * FROM product WHERE type = '$single_product->type' AND ID != '$product_id' LIMIT 4");
		$related_product->execute();
		$related_product_details = $related_product->fetchAll(PDO::FETCH_OBJ);

		//code for ADD to Cart
		if(isset($_POST['submit'])){
			$product_title = $_POST['product_title'];
			$product_image = $_POST['product_image'];
			$product_price = $_POST['product_price'];
			$product_description = $_POST['product_description'];
			$product_size = $_POST['product_size'];
			$product_quantity = $_POST['product_quantity'];
			$user_id = $_SESSION['user_id'];

			$insert_cart = $conn->prepare("INSERT INTO cart(product_title, product_image, product_price, product_description, product_size,product_quantity, user_id,product_id)
			VALUES(:product_title, :product_image, :product_price, :product_description, :product_size, :product_quantity, :user_id, :product_id)");
			$insert_cart->execute([
				":product_title" => $product_title,
				":product_image" => $product_image,
				":product_price" => $product_price,
				":product_description" => $product_description,
				":product_size" => $product_size,
				":product_quantity" => $product_quantity,
				":user_id" => $user_id,
				":product_id" =>$product_id,
			]);

		}

		//validation code for cart button
		if(isset($_SESSION['user_id'])){
			$cart_validation = $conn->query("SELECT * FROM cart WHERE product_id='$product_id' AND user_id='{$_SESSION["user_id"]}'");

			$cart_validation->execute();

			$cart_validation_rowcount = $cart_validation->rowcount();
			$number_of_product = 0;
			$number_of_product_in_cart = $cart_validation->fetchAll(PDO::FETCH_OBJ);
			foreach($number_of_product_in_cart as $product_count){
				$number_of_product += $product_count->product_quantity;
				
			}
		}

	}
?>

    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Product Detail</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ; ?>">Home</a></span> <span>Product Detail</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
    	<div class="container">
    		<div class="row">
    			<div class="col-lg-6 mb-5 ftco-animate">
    				<a href="<?php echo APPURl ; ?>/images/<?php echo $single_product->image; ?>" class="image-popup"><img src="<?php echo APPURl ; ?>/images/<?php echo $single_product->image; ?>" class="img-fluid" alt="Colorlib Template"></a>
    			</div>
    			<div class="col-lg-6 product-details pl-md-5 ftco-animate">
    				<h3><?php echo $single_product->product_title; ?></h3>
    				<p class="price"><span><?php echo "$".$single_product->price; ?></span></p>
    				<p><?php echo $single_product->description; ?></p>
					<form action="product-single.php?id=<?php echo $product_id; ?>" method="POST">
					<div class="row mt-4">
						<div class="col-md-6">
							<div class="form-group d-flex">
								<div class="select-wrap">
									<div class="icon"><span class="ion-ios-arrow-down"></span></div>
										<select name="product_size" id="" class="form-control">
											<option value="Small">Small</option>
											<option value="Medium">Medium</option>
											<option value="Large">Large</option>
											<option value="Extra Large">Extra Large</option>
										</select>
									</div>
								</div>
							</div>

							<div class="w-100"></div>
							<div class="input-group col-md-6 d-flex mb-3">
							<span class="input-group-btn mr-2">
								<button type="button" class="quantity-left-minus btn"  data-type="minus" data-field="">
									<i class="icon-minus"></i>
								</button>
							</span>
							<input type="text" id="quantity" name="product_quantity" class="form-control input-number" value="1" min="1" max="100">
							<span class="input-group-btn ml-2">
								<button type="button" class="quantity-right-plus btn" data-type="plus" data-field="">
									<i class="icon-plus"></i>
								</button>
							</span>
						</div>
          			</div>
					
						<input name="product_title" type="hidden" value="<?php echo $single_product->product_title; ?>" >
						<input name="product_image" type="hidden" value="<?php echo $single_product->image; ?>" >
						<input name="product_price" type="hidden" value="<?php echo $single_product->price; ?>" >
						<input name="product_description" type="hidden" value="<?php echo $single_product->description; ?>">
						<?php if($cart_validation_rowcount > 0): ?>
							<p><button type="submit" name="submit" class="btn btn-primary py-3 px-5 cart-btn" ><?php echo $number_of_product." in Cart" ?></button></p>
						
						<?php else: ?>
							<p><button type="submit" name="submit" class="btn btn-primary py-3 px-5 cart-btn" >Add to Cart</button></p>
						<?php endif; ?>
					</form>
    			</div>
    		</div>
    	</div>
    </section>

    <section class="ftco-section">
    	<div class="container">
    		<div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section ftco-animate text-center">
          	<span class="subheading">Discover</span>
            <h2 class="mb-4">Related products</h2>
            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.</p>
          </div>
        </div>
        <div class="row">
			<?php foreach($related_product_details as $all_related_product): ?>
        	<div class="col-md-3">
        		<div class="menu-entry">
    					<a href="<?php echo APPURl ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>" class="img" style="background-image: url('<?php echo APPURl ; ?>/images/<?php echo $all_related_product->image; ?>')"></a>
    					<div class="text text-center pt-4">
    						<h3><a href="<?php echo APPURl ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>"><?php echo $all_related_product->product_title; ?></a></h3>
    						<p><?php echo $all_related_product->description; ?></p>
    						<p class="price"><span><?php echo $all_related_product->price; ?></span></p>
    						<p><a href="<?php echo APPURl ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>" class="btn btn-primary btn-outline-primary">Show</a></p>
    					</div>
    				</div>
        	</div>
			<?php endforeach; ?>
        </div>
    	</div>
    </section>
	
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

