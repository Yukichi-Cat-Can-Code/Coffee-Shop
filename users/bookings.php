<?php

	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php
    $bookings = $conn->query("SELECT ID,first_name, last_name,date,time,phone_number, SUBSTRING(message,1,10) as message, status FROM bookings WHERE user_id='$_SESSION[user_id]'");
    $bookings->execute();
    $bookings_values = $bookings->fetchAll(PDO::FETCH_OBJ);


?>
<section class="home-slider owl-carousel">

<div class="slider-item" style="background-image: url(<?php echo APPURl ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
  <div class="container">
    <div class="row slider-text justify-content-center align-items-center">

      <div class="col-md-7 col-sm-12 text-center ftco-animate">
          <h1 class="mb-3 mt-5 bread">Bookings</h1>
          <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURl ?>">Home</a></span> <span>bookings</span></p>
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
								<th>First Name</th>
								<th>Last Name</th>
								<th>Booking Date</th>
								<th>Phone Number</th>
								<th>Message</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php if($bookings->rowcount() == 0 ): ?>
							<tr class="text-center" rowspan>
								<td>You have no bookings yet.</td>
							</tr>
						<?php else: ?>
							<?php foreach($bookings_values as $bookings_values ): ?>
								<tr class="text-center">
									<td class="product-remove">
										<?php if($bookings_values->status == "approved"): ?>
											<a><span class="icon-close"></span></a>
										<?php else: ?>
											<a href="delete-bookings.php?booking_id=<?php echo $bookings_values->ID ?>" style="curser: pointer;"><span class="icon-close"></span></a>
										<?php endif; ?>
									</td>
									<td>
										<p><?php echo $bookings_values ->first_name ?></p>

									</td>
									<td >
										<p><?php echo $bookings_values->last_name ?></p>

									</td>
									<td>
										<p><?php echo $bookings_values ->date ?></p>
										<p style="margin-top:-20px;"><?php echo $bookings_values->time ?></p>

									</td>
									
									<td>
										<p><?php echo $bookings_values ->phone_number ?></p>
									</td>
									
									<td >
										<p><?php echo $bookings_values->message ?>. . . . . .</p>
									</td>
									<td >
										<p><?php echo $bookings_values->status ?></p>
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