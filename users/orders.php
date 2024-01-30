<?php

	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php
    $orders = $conn->query("SELECT ID, firstname, lastname, streetaddress, appartment, towncity, postcode, phone, email, payable_total_cost, status FROM orders WHERE user_id='$_SESSION[user_id]'");
    $orders->execute();
    $orders_values = $orders->fetchAll(PDO::FETCH_OBJ);


?>
	<section class="home-slider owl-carousel">

	<div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">

			<div class="col-md-7 col-sm-12 text-center ftco-animate">
				<h1 class="mb-3 mt-5 bread">ORDERS</h1>
				<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ?>">Home</a></span> <span>orders</span></p>
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
								<th>First Name</th>
								<th>Last Name</th>
								<th>Paid Amount</th>
								<th>Phone Number</th>
								<th>Location</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if($orders->rowcount() == 0 ): ?>
							<tr class="text-center" rowspan>
								<td>You have no orders yet.</td>
							</tr>
						<?php else: ?>
							<?php foreach($orders_values as $orders_values ): ?>
								<tr class="text-center">

									<td>
										<p><?php echo $orders_values ->firstname ?></p>

									</td>
									<td >
										<p><?php echo $orders_values->lastname ?></p>

									</td>
									<td>
										<p><?php echo $orders_values ->payable_total_cost ?></p>
									</td>
									
									<td>
										<p><?php echo $orders_values ->phone ?></p>
									</td>
									
									<td >
										<p><?php echo $orders_values->streetaddress ?></p>
										<p style="margin-top:-20px;"><?php echo $orders_values->appartment ?></p>
										<p style="margin-top:-20px;"><?php echo $orders_values->towncity ?></p>
										<p style="margin-top:-20px;"><?php echo $orders_values->postcode ?></p>
									</td>
									<td >
										<p><?php echo $orders_values->status ?></p>
									</td>
								</tr><!-- END TR-->
							<?php endforeach; ?>
						<?php endif; ?>
						</tbody>
						</table>
					</div>
			</div>
		</div>

		</div>
	</section>



<?php require "../includes/footer.php"; ?>