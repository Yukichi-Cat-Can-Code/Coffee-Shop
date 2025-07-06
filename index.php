<?php

ob_start();

require_once "config/config.php";
require "includes/header.php";



// Lấy sản phẩm nổi bật từ database
try {
	$product = $conn->prepare("SELECT ID, product_title, image, SUBSTRING(description, 1, 100) AS description, price, type FROM product WHERE status = 'Publish' ORDER BY created_at DESC LIMIT 4");
	$product->execute();
	$all_product = $product->fetchAll(PDO::FETCH_OBJ);

	// Lấy đánh giá từ khách hàng
	$reviews = $conn->prepare("SELECT * FROM reviews WHERE status = 'Approved' ORDER BY created_at DESC LIMIT 4");
	$reviews->execute();
	$all_reviews = $reviews->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	// Xử lý lỗi database một cách an toàn
	error_log("Database Error: " . $e->getMessage());
	$all_product = [];
	$all_reviews = [];
}
?>

<!-- Slider Section -->
<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_1.jpg);">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">
				<div class="col-md-8 col-sm-12 text-center ftco-animate">
					<span class="subheading">Chào Mừng</span>
					<h1 class="mb-4">Trải Nghiệm Cà Phê Tuyệt Hảo</h1>
					<p class="mb-4 mb-md-5">Khám phá hương vị đậm đà tinh túy từ những hạt cà phê thượng hạng được chọn lọc kỹ lưỡng.</p>
					<p>
						<a href="<?php echo APPURL; ?>/menu.php" class="btn btn-primary p-3 px-xl-4 py-xl-3">Đặt Bàn</a>
						<a href="<?php echo APPURL; ?>/menu.php" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem Thực Đơn</a>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_2.jpg);">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">
				<div class="col-md-8 col-sm-12 text-center ftco-animate">
					<span class="subheading">Chào Mừng</span>
					<h1 class="mb-4">Hương Vị Tuyệt Hảo &amp; Không Gian Tinh Tế</h1>
					<p class="mb-4 mb-md-5">Tận hưởng những khoảnh khắc thư giãn trong không gian ấm cúng, tinh tế với hương vị cà phê đặc trưng.</p>
					<p>
						<a href="<?php echo APPURL; ?>/products/cart.php" class="btn btn-primary p-3 px-xl-4 py-xl-3">Đặt Hàng</a>
						<a href="<?php echo APPURL; ?>/menu.php" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem Thực Đơn</a>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">
				<div class="col-md-8 col-sm-12 text-center ftco-animate">
					<span class="subheading">Chào Mừng</span>
					<h1 class="mb-4">Thơm Ngon, Nóng Hổi & Sẵn Sàng Phục Vụ</h1>
					<p class="mb-4 mb-md-5">Đội ngũ nhân viên chuyên nghiệp sẵn sàng mang đến cho bạn trải nghiệm cà phê hoàn hảo mỗi ngày.</p>
					<p>
						<a href="<?php echo APPURL; ?>/products/cart.php" class="btn btn-primary p-3 px-xl-4 py-xl-3">Đặt Hàng</a>
						<a href="<?php echo APPURL; ?>/menu.php" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem Thực Đơn</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Best Sellers Section -->
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-5 pb-3">
			<div class="col-md-7 heading-section ftco-animate text-center">
				<span class="subheading">Khám Phá</span>
				<h2 class="mb-4">Sản Phẩm Bán Chạy</h2>
				<p>Những món đồ uống và thực phẩm được yêu thích nhất tại Artisan Coffee, được lựa chọn từ những nguyên liệu chất lượng cao.</p>
			</div>
		</div>
		<div class="row">
			<?php foreach ($all_product as $product): ?>
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID ?>" class="img" style="background-image: url('<?php echo APPURL; ?>/images/<?php echo escape($product->image); ?>')"></a>
						<div class="text text-center pt-4">
							<h3><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID ?>"><?php echo escape($product->product_title); ?></a></h3>
							<p><?php echo escape($product->description); ?>...</p>
							<p class="price"><span><?php echo formatCurrency($product->price); ?></span></p>
							<p><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product->ID; ?>" class="btn btn-primary btn-outline-primary">Xem Chi Tiết</a></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Testimonials Section -->
<section class="ftco-section img" id="ftco-testimony" style="background-image: url(<?php echo APPURL; ?>/images/bg_1.jpg);" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-7 heading-section text-center ftco-animate">
				<span class="subheading">Đánh Giá</span>
				<h2 class="mb-4">Khách Hàng Nói Gì</h2>
				<p>Những trải nghiệm và cảm nhận chân thực từ khách hàng về sản phẩm và dịch vụ tại Artisan Coffee.</p>
			</div>
		</div>
	</div>
	<div class="container-wrap">
		<div class="row d-flex no-gutters">
			<?php $counter = 0; ?>
			<?php foreach ($all_reviews as $review): ?>
				<?php if ($counter % 2 == 0): ?>
					<div class="col-lg align-self-sm-end ftco-animate">
						<div class="testimony">
							<blockquote>
								<p>&ldquo;<?php echo escape($review->review); ?>&rdquo;</p>
							</blockquote>
							<div class="author d-flex mt-4">
								<div class="name align-self-center"><?php echo escape($review->user_name); ?></div>
							</div>
							<!-- Hiển thị rating sao -->
							<div class="rating mt-2">
								<?php
								// Lấy rating từ database hoặc mặc định là 5 sao nếu không có
								$rating = isset($review->rating) ? (int)$review->rating : 5;
								// Hiển thị số sao đánh giá
								for ($i = 1; $i <= 5; $i++): ?>
									<i class="icon-star<?php echo ($i <= $rating) ? '' : '-o'; ?>" style="color: #fac564;"></i>
								<?php endfor; ?>
							</div>
							<div class="date text-muted mt-1">
								<small><?php echo date('d/m/Y', strtotime($review->created_at)); ?></small>
							</div>
						</div>
					</div>
				<?php else: ?>
					<div class="col-lg align-self-sm-end">
						<div class="testimony overlay">
							<blockquote>
								<p>&ldquo;<?php echo escape($review->review); ?>&rdquo;</p>
							</blockquote>
							<div class="author d-flex mt-4">
								<div class="name align-self-center"><?php echo escape($review->user_name); ?></div>
							</div>
							<!-- Hiển thị rating sao -->
							<div class="rating mt-2">
								<?php
								// Lấy rating từ database hoặc mặc định là 5 sao nếu không có
								$rating = isset($review->rating) ? (int)$review->rating : 5;
								// Hiển thị số sao đánh giá
								for ($i = 1; $i <= 5; $i++): ?>
									<i class="icon-star<?php echo ($i <= $rating) ? '' : '-o'; ?>" style="color: #fac564;"></i>
								<?php endfor; ?>
							</div>
							<div class="date text-muted mt-1">
								<small><?php echo date('d/m/Y', strtotime($review->created_at)); ?></small>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php $counter++; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- About Us Section -->
<section class="ftco-about d-md-flex">
	<div class="one-half img" style="background-image: url(<?php echo APPURL; ?>/images/about.jpg);"></div>
	<div class="one-half ftco-animate">
		<div class="overlap">
			<div class="heading-section ftco-animate ">
				<span class="subheading">Khám Phá</span>
				<h2 class="mb-4">Câu Chuyện Của Chúng Tôi</h2>
			</div>
			<div>
				<p>Từ một quán cà phê nhỏ năm 2010, Artisan Coffee đã trở thành điểm đến yêu thích của những người yêu cà phê trên khắp cả nước. Chúng tôi tự hào mang đến những trải nghiệm cà phê đẳng cấp thế giới nhưng vẫn giữ được bản sắc Việt Nam.</p>
				<p>Mỗi hạt cà phê được chọn lọc kỹ lưỡng từ những vùng trồng nổi tiếng như Buôn Ma Thuột, Lâm Đồng. Chúng tôi hợp tác trực tiếp với nông dân địa phương để đảm bảo chất lượng tốt nhất và tạo sinh kế bền vững cho cộng đồng.</p>
				<p><a href="<?php echo APPURL; ?>/about.php" class="btn btn-primary btn-outline-primary px-4 py-3">Tìm Hiểu Thêm</a></p>
			</div>
		</div>
	</div>
</section>

<!-- Services Section -->
<section class="ftco-section ftco-services">
	<div class="container">
		<div class="row">
			<div class="col-md-4 ftco-animate">
				<div class="media d-block text-center block-6 services">
					<div class="icon d-flex justify-content-center align-items-center mb-5">
						<span class="flaticon-choices"></span>
					</div>
					<div class="media-body">
						<h3 class="heading">Dễ Dàng Đặt Hàng</h3>
						<p>Chỉ với vài thao tác đơn giản, bạn có thể đặt món yêu thích và nhận giao hàng tận nơi.</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 ftco-animate">
				<div class="media d-block text-center block-6 services">
					<div class="icon d-flex justify-content-center align-items-center mb-5">
						<span class="flaticon-delivery-truck"></span>
					</div>
					<div class="media-body">
						<h3 class="heading">Giao Hàng Nhanh Chóng</h3>
						<p>Đội ngũ giao hàng chuyên nghiệp đảm bảo đồ uống đến tay bạn trong tình trạng nóng hổi, thơm ngon.</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 ftco-animate">
				<div class="media d-block text-center block-6 services">
					<div class="icon d-flex justify-content-center align-items-center mb-5">
						<span class="flaticon-coffee-bean"></span>
					</div>
					<div class="media-body">
						<h3 class="heading">Cà Phê Chất Lượng Cao</h3>
						<p>Chúng tôi sử dụng những hạt cà phê ngon nhất, rang xay theo công thức độc quyền để tạo nên hương vị đặc trưng.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Counter Section -->
<section class="ftco-counter ftco-bg-dark img" id="section-counter" style="background-image: url(<?php echo APPURL; ?>/images/bg_2.jpg);" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-10">
				<div class="row">
					<div class="col-md-3 col-sm-6 col-xs-6 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="15">0</strong>
								<span>Năm Kinh Nghiệm</span>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-6 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="20000">0</strong>
								<span>Khách Hàng Hài Lòng</span>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-6 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="50">0</strong>
								<span>Cửa Hàng</span>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-6 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="500">0</strong>
								<span>Nhân Viên</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
require "includes/footer.php";
ob_end_flush();
?>