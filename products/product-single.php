<?php

require "../config/config.php";

// Hàm định dạng tiền tệ VND
function formatPrice($price)
{
	return number_format($price, 0, ',', '.') . ' ₫';
}

if (isset($_GET['id'])) {
	// Lấy thông tin sản phẩm
	$product_id = $_GET['id'];

	// Sử dụng prepared statement để tránh SQL injection
	$product = $conn->prepare("SELECT * FROM product WHERE ID = :id");
	$product->execute(['id' => $product_id]);
	$single_product = $product->fetch(PDO::FETCH_OBJ);

	// Kiểm tra xem sản phẩm có tồn tại hay không
	if ($product->rowCount() == 0) {
		header("location: " . APPURL . "/404.php");
		exit;
	}

	// Lấy sản phẩm liên quan
	$related_product = $conn->prepare("SELECT ID, product_title, image, SUBSTRING(description, 1, 100) AS description, price, type 
                                          FROM product 
                                          WHERE type = :type AND ID != :id 
                                          LIMIT 4");
	$related_product->execute([
		'type' => $single_product->type,
		'id' => $product_id
	]);
	$related_product_details = $related_product->fetchAll(PDO::FETCH_OBJ);

	// Xử lý thêm vào giỏ hàng
	if (isset($_POST['submit']) && function_exists('session') && session()->isLoggedIn()) {
		$product_title = $_POST['product_title'];
		$product_image = $_POST['product_image'];
		$product_price = $_POST['product_price'];
		$product_description = $_POST['product_description'];
		$product_size = $_POST['product_size'];
		$product_quantity = max(1, intval($_POST['product_quantity'])); // Đảm bảo số lượng ít nhất là 1
		$user_id = session()->getCurrentUser()['id'];

		$insert_cart = $conn->prepare("INSERT INTO cart(product_title, product_image, product_price, product_description, product_size, product_quantity, user_id, product_id)
                                          VALUES(:product_title, :product_image, :product_price, :product_description, :product_size, :product_quantity, :user_id, :product_id)");
		$insert_cart->execute([
			":product_title" => $product_title,
			":product_image" => $product_image,
			":product_price" => $product_price,
			":product_description" => $product_description,
			":product_size" => $product_size,
			":product_quantity" => $product_quantity,
			":user_id" => $user_id,
			":product_id" => $product_id,
		]);

		// Chuyển hướng để tránh gửi lại form khi refresh trang
		header("Location: " . APPURL . "/products/product-single.php?id=" . $product_id . "&added=1");
		exit;
	}

	// Kiểm tra sản phẩm đã có trong giỏ hàng chưa
	$cart_validation_rowcount = 0;
	$number_of_product = 0;

	if (function_exists('session') && session()->isLoggedIn()) {
		$user_id = session()->getCurrentUser()['id'];
		$cart_validation = $conn->prepare("SELECT * FROM cart WHERE product_id = :product_id AND user_id = :user_id");
		$cart_validation->execute([
			'product_id' => $product_id,
			'user_id' => $user_id
		]);

		$cart_validation_rowcount = $cart_validation->rowCount();
		$number_of_product_in_cart = $cart_validation->fetchAll(PDO::FETCH_OBJ);

		foreach ($number_of_product_in_cart as $product_count) {
			$number_of_product += $product_count->product_quantity;
		}
	}
} else {
	header("location: " . APPURL . "/404.php");
	exit;
}

require "../includes/header.php";
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Chi tiết sản phẩm</h1>
					<p class="breadcrumbs">
						<span class="mr-2"><a href="<?php echo APPURL; ?>">Trang chủ</a></span>
						<span class="mr-2"><a href="<?php echo APPURL; ?>/menu.php">Thực đơn</a></span>
						<span><?php echo htmlspecialchars($single_product->product_title); ?></span>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="ftco-section">
	<div class="container">
		<!-- Thông báo khi thêm vào giỏ hàng thành công -->
		<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Thành công!</strong> Sản phẩm đã được thêm vào giỏ hàng.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-lg-6 mb-5 ftco-animate">
				<a href="<?php echo APPURL; ?>/images/<?php echo htmlspecialchars($single_product->image); ?>" class="image-popup">
					<img src="<?php echo APPURL; ?>/images/<?php echo htmlspecialchars($single_product->image); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($single_product->product_title); ?>">
				</a>
			</div>
			<div class="col-lg-6 product-details pl-md-5 ftco-animate">
				<h3><?php echo htmlspecialchars($single_product->product_title); ?></h3>
				<p class="price"><span><?php echo formatPrice($single_product->price); ?></span></p>
				<p><?php echo htmlspecialchars($single_product->description); ?></p>

				<form action="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $product_id; ?>" method="POST">
					<div class="row mt-4">
						<div class="col-md-6">
							<div class="form-group d-flex">
								<div class="select-wrap">
									<div class="icon"><span class="ion-ios-arrow-down"></span></div>
									<select name="product_size" id="product_size" class="form-control">
										<option value="Nhỏ">Nhỏ</option>
										<option value="Vừa" selected>Vừa</option>
										<option value="Lớn">Lớn</option>
										<option value="Siêu lớn">Siêu lớn</option>
									</select>
								</div>
							</div>
						</div>

						<div class="w-100"></div>
						<div class="input-group col-md-6 d-flex mb-3">
							<span class="input-group-btn mr-2">
								<button type="button" class="quantity-left-minus btn" data-type="minus" data-field="">
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

					<input name="product_title" type="hidden" value="<?php echo htmlspecialchars($single_product->product_title); ?>">
					<input name="product_image" type="hidden" value="<?php echo htmlspecialchars($single_product->image); ?>">
					<input name="product_price" type="hidden" value="<?php echo $single_product->price; ?>">
					<input name="product_description" type="hidden" value="<?php echo htmlspecialchars($single_product->description); ?>">

					<?php if (function_exists('session') && session()->isLoggedIn()): ?>
						<?php if ($cart_validation_rowcount > 0): ?>
							<p><button type="submit" name="submit" class="btn btn-primary py-3 px-5 cart-btn"><?php echo $number_of_product . " sản phẩm trong giỏ hàng"; ?></button></p>
						<?php else: ?>
							<p><button type="submit" name="submit" class="btn btn-primary py-3 px-5 cart-btn">Thêm vào giỏ hàng</button></p>
						<?php endif; ?>
					<?php else: ?>
						<p><a href="<?php echo APPURL; ?>/auth/login.php" class="btn btn-primary py-3 px-5 cart-btn">Đăng nhập để mua hàng</a></p>
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
				<span class="subheading">Khám phá</span>
				<h2 class="mb-4">Sản phẩm liên quan</h2>
				<p>Những sản phẩm khác có thể bạn sẽ thích</p>
			</div>
		</div>
		<div class="row">
			<?php foreach ($related_product_details as $all_related_product): ?>
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>" class="img" style="background-image: url('<?php echo APPURL; ?>/images/<?php echo htmlspecialchars($all_related_product->image); ?>')"></a>
						<div class="text text-center pt-4">
							<h3><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>"><?php echo htmlspecialchars($all_related_product->product_title); ?></a></h3>
							<p><?php echo htmlspecialchars($all_related_product->description); ?>...</p>
							<p class="price"><span><?php echo formatPrice($all_related_product->price); ?></span></p>
							<p><a href="<?php echo APPURL; ?>/products/product-single.php?id=<?php echo $all_related_product->ID; ?>" class="btn btn-primary btn-outline-primary">Xem chi tiết</a></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php require "../includes/footer.php"; ?>

<script>
	$(document).ready(function() {
		var quantitiy = 0;

		$('.quantity-right-plus').click(function(e) {
			// Ngăn chặn hành vi mặc định của nút
			e.preventDefault();

			// Lấy giá trị số lượng hiện tại
			var quantity = parseInt($('#quantity').val());

			// Tăng giá trị
			$('#quantity').val(quantity + 1);
		});

		$('.quantity-left-minus').click(function(e) {
			// Ngăn chặn hành vi mặc định của nút
			e.preventDefault();

			// Lấy giá trị số lượng hiện tại
			var quantity = parseInt($('#quantity').val());

			// Giảm giá trị nhưng không nhỏ hơn 1
			if (quantity > 1) {
				$('#quantity').val(quantity - 1);
			}
		});
	});
</script>