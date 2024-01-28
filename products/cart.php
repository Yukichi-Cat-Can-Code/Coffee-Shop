<?php

	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>

<?php

	$user_id = $_SESSION['user_id'];
	$cart = $conn->query("SELECT ID, product_id, MAX(product_title) AS product_title, MAX(SUBSTRING_INDEX(product_description, ' ', 10)) AS product_description, MAX(product_image) AS product_image, MAX(product_price) AS product_price, SUM(product_quantity) AS total_quantity,user_id FROM cart WHERE user_id = '$user_id' GROUP BY product_id, user_id;");
	$cart->execute();
	$cart_product = $cart->fetchAll(PDO::FETCH_OBJ);

	//code for cart total
	$cart_total = $conn->prepare("SELECT SUM(product_quantity*product_price) AS total FROM `cart` WHERE user_id='$user_id';");
	$cart_total->execute();
	$total_cart_product = $cart_total->fetch(PDO::FETCH_OBJ);

	//proceed to checkout
	if(isset($_GET['total_cost'])){
		$_SESSION['payable_total_cost'] = $_GET['total_cost'];
		header("location: checkout.php");
	}


?>

    <section class="home-slider owl-carousel">

      <div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center">

            <div class="col-md-7 col-sm-12 text-center ftco-animate">
            	<h1 class="mb-3 mt-5 bread">Cart</h1>
	            <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ?>">Home</a></span> <span>Cart</span></p>
            </div>

          </div>
        </div>
      </div>
    </section>
	
	<section class="ftco-section ftco-cart">
		<div class="container">
			<div class="row">
			<div class="col-md-12 ftco-animate">
				<div class="cart-list">
					<table class="table">
						<thead class="thead-primary">
							<tr class="text-center">
							<th>&nbsp;</th>
							<th>&nbsp;</th>
							<th>Product</th>
							<th>Price</th>
							<th>Quantity</th>
							<th>Total</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($cart_product as $cart_product ): ?>
							<tr class="text-center">
							<td class="product-remove"><a href="delete-product.php?product_id=<?php echo $cart_product->product_id ?>"><span class="icon-close"></span></a></td>
							
							<td class="image-prod"><div class="img" style="background-image:url(<?php echo APPURl ?>/images/<?php echo $cart_product ->product_image ?>);"></div></td>
							
							<td class="product-name">
								<h3><?php echo $cart_product ->product_title ?></h3>
								<p><?php echo $cart_product->product_description ?>. . . . .</p>
							</td>
							
							<td class="price"><?php echo $cart_product->product_price ?></td>
							
							<td>
								<div class="input-group mb-3">
									<input disabled type="text" name="quantity" class="quantity form-control input-number" value="<?php echo $cart_product->total_quantity ?>" min="1" max="100">
									</div>
							</td>
							
							<td class="total"><?php echo $cart_product->product_price*$cart_product->total_quantity ?> </td>
							</tr><!-- END TR-->
						<?php endforeach; ?>
						</tbody>
						</table>
					</div>
			</div>
		</div>
		<div class="row justify-content-end">
			<div class="col col-lg-3 col-md-6 mt-5 cart-wrap ftco-animate">
				<div class="cart-total mb-3">
					<h3>Cart Totals</h3>
					<p class="d-flex">
						<span>Subtotal</span>
						<span>$<?php echo $total_cart_product->total; ?>.00</span>
					</p>
					<p class="d-flex">
						<span>Delivery</span>
						<?php if($total_cart_product->total > 0): ?>
							<span>$3.00</span>
						<?php else: ?>
							<span>$0.00</span>
						<?php endif; ?>
					</p>
					<p class="d-flex">
						<span>Discount</span>
						<?php if($total_cart_product->total > 0): ?>
							<span>$5.00</span>
						<?php else: ?>
							<span>$0.00</span>
						<?php endif; ?>
					</p>
					<hr>
					<p class="d-flex total-price">
						<span>Total</span>
						<?php if($total_cart_product->total > 0): ?>
							<?php $total_cost = ($total_cart_product->total + 3) - 5; ?>
							<span>$<?php echo $total_cost; ?>.00</span>
						<?php else: ?>
							<?php $total_cost = 0; ?>
							<span>$<?php echo $total_cost; ?>.00</span>
						<?php endif; ?>
					</p>
				</div>
				<p class="text-center"><a href="cart.php?total_cost=<?php echo $total_cost; ?>" class="btn btn-primary py-3 px-4">Proceed to Checkout</a></p>
			</div>
		</div>
		</div>
	</section>



	<?php require "../includes/footer.php"; ?>