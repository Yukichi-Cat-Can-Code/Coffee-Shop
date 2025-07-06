<?php

require_once "config/config.php";
require "includes/header.php";

/**
 * Truy vấn danh sách sản phẩm từ database
 * Sử dụng prepared statements để tránh SQL injection
 */
try {
	// Lấy món tráng miệng (dessert)
	$dessert_query = $conn->prepare("SELECT ID, SUBSTRING(product_title,1,70) as product_title, image, SUBSTRING(description,1,100) as description, price FROM product WHERE type = :type");
	$dessert_query->execute(['type' => 'dessert']);
	$dessert_products = $dessert_query->fetchAll(PDO::FETCH_OBJ);

	// Lấy đồ uống (drink)
	$drinks_query = $conn->prepare("SELECT ID, SUBSTRING(product_title,1,70) as product_title, image, SUBSTRING(description,1,100) as description, price FROM product WHERE type = :type");
	$drinks_query->execute(['type' => 'drink']);
	$drinks_products = $drinks_query->fetchAll(PDO::FETCH_OBJ);

	// Lấy món ăn (food)
	$food_query = $conn->prepare("SELECT ID, SUBSTRING(product_title,1,70) as product_title, image, SUBSTRING(description,1,100) as description, price FROM product WHERE type = :type");
	$food_query->execute(['type' => 'food']);
	$food_products = $food_query->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	// Xử lý lỗi database một cách an toàn
	error_log("Database Error in menu.php: " . $e->getMessage());
	$dessert_products = [];
	$drinks_products = [];
	$food_products = [];
}


if (!function_exists('formatPrice')) {
	function formatPrice($price)
	{
		if (function_exists('formatCurrency')) {
			return formatCurrency($price);
		}
		return number_format($price, 0, ',', '.') . ' ₫';
	}
}
?>

<!-- Banner trang menu -->
<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Thực Đơn</h1>
					<p class="breadcrumbs">
						<span class="mr-2"><a href="<?php echo APPURL; ?>/index.php">Trang Chủ</a></span>
						<span>Thực Đơn</span>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Thông tin liên hệ và đặt bàn -->
<section class="ftco-intro">
	<div class="container-wrap">
		<div class="wrap d-md-flex align-items-xl-end">
			<!-- Thông tin liên hệ -->
			<div class="info">
				<div class="row no-gutters">
					<div class="col-md-4 d-flex ftco-animate">
						<div class="icon"><span class="icon-phone"></span></div>
						<div class="text">
							<h3>+84 28 1234 5678</h3>
							<p>Liên hệ ngay với chúng tôi để được phục vụ tốt nhất.</p>
						</div>
					</div>
					<div class="col-md-4 d-flex ftco-animate">
						<div class="icon"><span class="icon-my_location"></span></div>
						<div class="text">
							<h3>123 Nguyễn Huệ, Quận 1</h3>
							<p>TP. Hồ Chí Minh, Việt Nam</p>
						</div>
					</div>
					<div class="col-md-4 d-flex ftco-animate">
						<div class="icon"><span class="icon-clock-o"></span></div>
						<div class="text">
							<h3>Mở cửa từ Thứ 2 - Chủ Nhật</h3>
							<p>7:00 - 22:00</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Form đặt bàn -->
			<div class="book p-4">
				<h3>Đặt Bàn</h3>
				<form action="<?php echo APPURL; ?>/booking/book.php" method="POST" class="appointment-form" id="bookingForm">
					<!-- Thêm CSRF token để bảo vệ form, sử dụng SessionManager -->
					<input type="hidden" name="csrf_token" value="<?php echo function_exists('session') ? session()->get('csrf_token', bin2hex(random_bytes(32))) : bin2hex(random_bytes(32)); ?>">

					<div class="d-md-flex">
						<div class="form-group">
							<input type="text" class="form-control" name="first_name" placeholder="Tên" required>
						</div>
						<div class="form-group ml-md-4">
							<input type="text" class="form-control" name="last_name" placeholder="Họ" required>
						</div>
					</div>
					<div class="d-md-flex">
						<div class="form-group">
							<div class="input-wrap">
								<div class="icon"><span class="ion-md-calendar"></span></div>
								<input type="text" name="date" class="form-control appointment_date" placeholder="Ngày" required>
							</div>
						</div>
						<div class="form-group ml-md-4">
							<div class="input-wrap">
								<div class="icon"><span class="ion-ios-clock"></span></div>
								<input type="text" name="time" class="form-control appointment_time" placeholder="Giờ" required>
							</div>
						</div>
						<div class="form-group ml-md-4">
							<input type="tel" name="phone_number" class="form-control" placeholder="Số Điện Thoại" pattern="[0-9]{10,11}" required>
						</div>
					</div>
					<div class="d-md-flex">
						<div class="form-group">
							<textarea name="message" cols="30" rows="2" class="form-control" placeholder="Lời Nhắn"></textarea>
						</div>
						<div class="form-group ml-md-4">
							<button type="submit" name="submit" class="btn btn-white py-3 px-4">Đặt Bàn</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Phần hiển thị menu tráng miệng và đồ uống dạng danh sách -->
<section class="ftco-section">
	<div class="container">
		<div class="row">
			<!-- Danh sách tráng miệng -->
			<div class="col-md-6">
				<h3 class="mb-5 heading-pricing ftco-animate">Tráng Miệng</h3>
				<?php foreach ($dessert_products as $product): ?>
					<div class="pricing-entry d-flex ftco-animate">
						<div class="img" style="background-image: url('<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>');"></div>
						<div class="desc pl-3">
							<div class="d-flex text align-items-center">
								<h3><span><?php echo escape($product->product_title); ?></span></h3>
								<span class="price"><?php echo formatPrice($product->price); ?></span>
							</div>
							<div class="d-block">
								<p><?php echo escape($product->description); ?> . . .</p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Danh sách đồ uống -->
			<div class="col-md-6">
				<h3 class="mb-5 heading-pricing ftco-animate">Đồ Uống</h3>
				<?php foreach ($drinks_products as $product): ?>
					<div class="pricing-entry d-flex ftco-animate">
						<div class="img" style="background-image: url(<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>);"></div>
						<div class="desc pl-3">
							<div class="d-flex text align-items-center">
								<h3><span><?php echo escape($product->product_title); ?></span></h3>
								<span class="price"><?php echo formatPrice($product->price); ?></span>
							</div>
							<div class="d-block">
								<p><?php echo escape($product->description); ?>. . .</p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- Menu tab hiển thị sản phẩm dạng grid -->
<section class="ftco-menu mb-5 pb-5">
	<div class="container">
		<!-- Heading -->
		<div class="row justify-content-center mb-5">
			<div class="col-md-7 heading-section text-center ftco-animate">
				<span class="subheading">Khám Phá</span>
				<h2 class="mb-4">Sản Phẩm Của Chúng Tôi</h2>
				<p>Tự hào mang đến cho bạn những sản phẩm chất lượng cao với hương vị đặc trưng, được chế biến từ nguyên liệu tươi ngon nhất.</p>
			</div>
		</div>

		<!-- Tab content -->
		<div class="row d-md-flex">
			<div class="col-lg-12 ftco-animate p-md-5">
				<div class="row">
					<!-- Tab navigation -->
					<div class="col-md-12 nav-link-wrap mb-5">
						<div class="nav ftco-animate nav-pills justify-content-center" id="v-pills-tab" role="tablist" aria-orientation="vertical">
							<a class="nav-link active" id="v-pills-2-tab" data-toggle="pill" href="#v-pills-2" role="tab" aria-controls="v-pills-2" aria-selected="false">Đồ Uống</a>
							<a class="nav-link" id="v-pills-3-tab" data-toggle="pill" href="#v-pills-3" role="tab" aria-controls="v-pills-3" aria-selected="false">Tráng Miệng</a>
							<a class="nav-link" id="v-pills-1-tab" data-toggle="pill" href="#v-pills-1" role="tab" aria-controls="v-pills-1" aria-selected="false">Món Ăn</a>
						</div>
					</div>

					<!-- Tab content -->
					<div class="col-md-12 d-flex align-items-center">
						<div class="tab-content ftco-animate" id="v-pills-tabContent">
							<!-- Tab đồ uống -->
							<div class="tab-pane fade show active" id="v-pills-2" role="tabpanel" aria-labelledby="v-pills-2-tab">
								<div class="row">
									<?php foreach ($drinks_products as $product): ?>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="menu-img img mb-4" style="background-image: url(<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>);"></a>
												<div class="text">
													<h3><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>"><?php echo escape($product->product_title); ?></a></h3>
													<p><?php echo escape($product->description); ?></p>
													<p class="price"><span><?php echo formatPrice($product->price); ?></span></p>
													<p><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="btn btn-primary btn-outline-primary">Xem Chi Tiết</a></p>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<!-- Tab tráng miệng -->
							<div class="tab-pane fade" id="v-pills-3" role="tabpanel" aria-labelledby="v-pills-3-tab">
								<div class="row">
									<?php foreach ($dessert_products as $product): ?>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="menu-img img mb-4" style="background-image: url(<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>);"></a>
												<div class="text">
													<h3><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>"><?php echo escape($product->product_title); ?></a></h3>
													<p><?php echo escape($product->description); ?> . . .</p>
													<p class="price"><span><?php echo formatPrice($product->price); ?></span></p>
													<p><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="btn btn-primary btn-outline-primary">Xem Chi Tiết</a></p>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<!-- Tab món ăn -->
							<div class="tab-pane fade" id="v-pills-1" role="tabpanel" aria-labelledby="v-pills-1-tab">
								<div class="row">
									<?php foreach ($food_products as $product): ?>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="menu-img img mb-4" style="background-image: url(<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>);"></a>
												<div class="text">
													<h3><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>"><?php echo escape($product->product_title); ?></a></h3>
													<p><?php echo escape($product->description); ?> . . .</p>
													<p class="price"><span><?php echo formatPrice($product->price); ?></span></p>
													<p><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="btn btn-primary btn-outline-primary">Xem Chi Tiết</a></p>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require "includes/footer.php"; ?>