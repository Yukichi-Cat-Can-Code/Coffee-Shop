<?php
ob_start();

require_once "../config/config.php";

// Kiểm tra người dùng đã đăng nhập chưa
if (function_exists('session') && !session()->isLoggedIn()) {
	session()->setFlash('error_message', "Vui lòng đăng nhập để xem giỏ hàng");
	redirect(APPURL . "/auth/login.php");
}

// Xử lý cập nhật số lượng sản phẩm trong giỏ hàng (theo từng dòng cart ID)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'], $_POST['cart_id'])) {
	$new_qty = max(1, intval($_POST['update_qty']));
	$cart_id = intval($_POST['cart_id']);
	$user_id = function_exists('session') ? session()->getCurrentUser()['id'] : 0;

	// Cập nhật số lượng cho đúng dòng sản phẩm trong giỏ hàng
	$update = $conn->prepare("UPDATE cart SET product_quantity = :qty WHERE ID = :cart_id AND user_id = :user_id");
	$update->execute([
		':qty' => $new_qty,
		':cart_id' => $cart_id,
		':user_id' => $user_id
	]);
	// Sau khi cập nhật, reload lại trang để tránh submit lại form khi refresh
	header("Location: cart.php");
	exit;
}

// Load header sau khi đã xử lý redirect nếu cần
require "../includes/header.php";

// Lấy thông tin người dùng từ session
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : 0;

// Lấy từng dòng sản phẩm trong giỏ hàng (không gộp)
$cart = $conn->prepare("SELECT * FROM cart WHERE user_id = :user_id");
$cart->bindParam(":user_id", $user_id);
$cart->execute();
$cart_row_count = $cart->rowCount();
$cart_products = $cart->fetchAll(PDO::FETCH_OBJ);

// Tính tổng giá trị giỏ hàng
$cart_total = $conn->prepare("SELECT SUM(product_quantity*product_price) AS total FROM `cart` WHERE user_id = :user_id");
$cart_total->bindParam(":user_id", $user_id);
$cart_total->execute();
$total_cart_product = $cart_total->fetch(PDO::FETCH_OBJ);

// Cập nhật số lượng trong giỏ hàng vào session
if (function_exists('session') && $cart_row_count > 0) {
	session()->set('cart_count', $cart_row_count);
} elseif (function_exists('session')) {
	session()->set('cart_count', 0);
}

// Xử lý đặt hàng trực tiếp với đầy đủ thông tin khách hàng
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_submit'])) {
	// Chỉ các trường này là bắt buộc
	$fields_required = ['firstname', 'lastname', 'streetaddress', 'towncity', 'phone'];
	$fields_all = ['firstname', 'lastname', 'streetaddress', 'apartment', 'towncity', 'postcodezip', 'phone', 'emailaddress'];
	$data = [];
	foreach ($fields_all as $f) {
		$data[$f] = trim($_POST[$f] ?? '');
	}
	$total_cost = floatval($_POST['total_cost'] ?? 0);

	// Kiểm tra dữ liệu bắt buộc
	foreach ($fields_required as $f) {
		if (empty($data[$f])) {
			$message = "Vui lòng điền đầy đủ thông tin bắt buộc!";
			break;
		}
	}

	if (!$message) {
		$stmt = $conn->prepare("INSERT INTO orders (firstname, lastname, streetaddress, apartment, towncity, postcode, phone, email, payable_total_cost, user_id, created_at)
            VALUES (:firstname, :lastname, :streetaddress, :apartment, :towncity, :postcode, :phone, :email, :payable_total_cost, :user_id, NOW())");
		$stmt->execute([
			":firstname" => $data['firstname'],
			":lastname" => $data['lastname'],
			":streetaddress" => $data['streetaddress'],
			":apartment" => $data['apartment'],
			":towncity" => $data['towncity'],
			":postcode" => $data['postcodezip'],
			":phone" => $data['phone'],
			":email" => $data['emailaddress'],
			":payable_total_cost" => $total_cost,
			":user_id" => $user_id
		]);
		// Xóa giỏ hàng sau khi đặt hàng
		$conn->prepare("DELETE FROM cart WHERE user_id = :user_id")->execute([':user_id' => $user_id]);
		redirect(APPURL . "/products/pay.php");
		exit;
	}
}
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Giỏ hàng</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang chủ</a></span> <span>Giỏ hàng</span></p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="ftco-section ftco-cart" style="background: #2d221b;">
	<div class="container">
		<div class="row justify-content-center">
			<?php if ($cart_row_count > 0): ?>
				<!-- Form đặt hàng nhỏ gọn -->
				<div class="col-lg-4 col-md-6 mb-4">
					<form method="post" action="cart.php" class="p-3 rounded shadow-sm" style="background: #f7f1ee; border:1px solid #e0d3c2;">
						<h5 class="mb-3 text-center font-weight-bold" style="color:#a9744f">
							<i class="icon-user"></i> Thông tin đặt hàng
						</h5>
						<?php if ($message): ?>
							<div class="alert alert-danger py-2 px-2"><?php echo htmlspecialchars($message); ?></div>
						<?php endif; ?>
						<input type="hidden" name="total_cost" value="<?php echo $total_cost; ?>">
						<div class="form-group mb-2">
							<input type="text" name="firstname" id="firstname" class="form-control form-control-sm" required placeholder="Họ *" style="background:#f3e9e2;"
								value="<?php echo htmlspecialchars($currentUser['firstname'] ?? ''); ?>">
						</div>
						<div class="form-group mb-2">
							<input type="text" name="lastname" id="lastname" class="form-control form-control-sm" required placeholder="Tên *" style="background:#f3e9e2;"
								value="<?php echo htmlspecialchars($currentUser['lastname'] ?? ''); ?>">
						</div>
						<div class="form-group mb-2">
							<input type="text" name="streetaddress" id="streetaddress" class="form-control form-control-sm" required placeholder="Địa chỉ *" style="background:#f3e9e2;"
								value="<?php echo htmlspecialchars($currentUser['streetaddress'] ?? ''); ?>">
						</div>
						<div class="form-group mb-2">
							<input type="text" name="apartment" id="apartment" class="form-control form-control-sm" placeholder="Căn hộ, tầng (không bắt buộc)" style="background:#f3e9e2;">
						</div>
						<div class="form-row">
							<div class="col mb-2">
								<input type="text" name="towncity" id="towncity" class="form-control form-control-sm" required placeholder="Thành phố *" style="background:#f3e9e2;">
							</div>
							<div class="col mb-2">
								<input type="text" name="postcodezip" id="postcodezip" class="form-control form-control-sm" placeholder="Mã bưu điện" style="background:#f3e9e2;">
							</div>
						</div>
						<div class="form-row">
							<div class="col mb-2">
								<input type="text" name="phone" id="phone" class="form-control form-control-sm" required placeholder="Số điện thoại *" style="background:#f3e9e2;">
							</div>
							<div class="col mb-2">
								<input type="email" name="emailaddress" id="emailaddress" class="form-control form-control-sm" placeholder="Email" style="background:#f3e9e2;"
									value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>">
							</div>
						</div>
						<button type="submit" name="order_submit" class="btn btn-block py-2 font-weight-bold mt-2" style="background:#a9744f; color:#fff;">
							<i class="icon-shopping-cart"></i> Đặt hàng
						</button>
					</form>
				</div>
			<?php endif; ?>

			<!-- Bảng tổng giỏ hàng rõ ràng, dễ chỉnh sửa -->
			<div class="col-lg-8 col-md-12">
				<div class="p-4 rounded shadow-sm cart-wrap ftco-animate mb-4" style="background: #fff; border:1px solid #e0d3c2;">
					<h3 class="mb-4" style="color:#a9744f">Tổng giỏ hàng</h3>
					<div class="cart-list mb-4">
						<table class="table table-bordered table-hover bg-light" style="font-size: 0.97em; margin-bottom:0;">
							<thead class="thead-primary" style="background:#a9744f; color:#fff;">
								<tr class="text-center align-middle" style="height:48px;">
									<th style="width:32px;">&nbsp;</th>
									<th style="width:52px;">&nbsp;</th>
									<th style="min-width:120px;">Sản phẩm</th>
									<th style="width:80px;">Giá</th>
									<th style="width:90px;">Số lượng</th>
									<th style="width:80px;">Tổng</th>
								</tr>
							</thead>
							<tbody>
								<?php if ($cart_row_count == 0): ?>
									<tr class="text-center">
										<td colspan="6">Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm vào giỏ hàng.</td>
									</tr>
								<?php else: ?>
									<?php foreach ($cart_products as $product): ?>
										<tr class="text-center align-middle" style="height:56px;">
											<td class="product-remove align-middle" style="vertical-align:middle;">
												<a href="delete-product.php?cart_id=<?php echo $product->ID ?>">
													<span class="icon-close" style="font-size:1.1em;"></span>
												</a>
											</td>
											<td class="image-prod align-middle" style="vertical-align:middle;">
												<div class="img" style="background-image:url(<?php echo APPURL ?>/images/<?php echo escape($product->product_image) ?>); width:38px; height:38px; background-size:cover; border-radius:5px; margin:auto;"></div>
											</td>
											<td class="product-name align-middle" style="color:#6d4c2e; vertical-align:middle;">
												<div style="font-size:1em;"><?php echo escape($product->product_title) ?></div>
												<?php if (!empty($product->product_size)): ?>
													<div>
														<span style="font-size:0.98em; font-weight: bold; color: #a9744f;">
															Size: <?php echo strtoupper(htmlspecialchars($product->product_size)); ?>
														</span>
													</div>
												<?php endif; ?>
											</td>
											<td class="price align-middle" style="color:#a9744f; vertical-align:middle;"><?php echo formatCurrency($product->product_price) ?></td>
											<td class="align-middle" style="vertical-align:middle;">
												<form method="post" action="cart.php" class="d-flex justify-content-center align-items-center" style="gap:4px;">
													<input type="hidden" name="cart_id" value="<?php echo $product->ID ?>">
													<input type="number" name="update_qty" class="quantity form-control form-control-sm input-number text-center" value="<?php echo $product->product_quantity ?>" min="1" max="100" style="width:48px; background:#f3e9e2; font-size:0.97em; padding:2px 4px;">
													<button type="submit" class="btn btn-sm" style="background:#a9744f; color:#fff; padding:2px 7px;" title="Cập nhật số lượng"><i class="icon-refresh" style="font-size:1em;"></i></button>
												</form>
											</td>
											<td class="total align-middle" style="color:#a9744f; vertical-align:middle;"><?php echo formatCurrency($product->product_price * $product->product_quantity) ?></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
					<div class="cart-total" style="background:#f7f1ee; border-radius:8px; padding:18px;">
						<p class="d-flex mb-2">
							<span style="color:#6d4c2e;">Tạm tính</span>
							<span style="color:#a9744f; margin-left:auto;"><?php echo formatCurrency($total_cart_product->total ?? 0); ?></span>
						</p>
						<p class="d-flex mb-2">
							<span style="color:#6d4c2e;">Phí vận chuyển</span>
							<?php if (($total_cart_product->total ?? 0) > 0): ?>
								<span style="color:#a9744f; margin-left:auto;"><?php echo formatCurrency(30000); ?></span>
							<?php else: ?>
								<span style="color:#a9744f; margin-left:auto;"><?php echo formatCurrency(0); ?></span>
							<?php endif; ?>
						</p>
						<p class="d-flex mb-2">
							<span style="color:#6d4c2e;">Giảm giá</span>
							<?php if (($total_cart_product->total ?? 0) > 0): ?>
								<span style="color:#a9744f; margin-left:auto;"><?php echo formatCurrency(10000); ?></span>
							<?php else: ?>
								<span style="color:#a9744f; margin-left:auto;"><?php echo formatCurrency(0); ?></span>
							<?php endif; ?>
						</p>
						<hr>
						<p class="d-flex total-price mb-0" style="font-size:1.15em;">
							<span style="color:#6d4c2e;">Tổng cộng</span>
							<?php if (($total_cart_product->total ?? 0) > 0): ?>
								<?php $total_cost = ($total_cart_product->total + 30000) - 10000; ?>
								<span style="color:#a9744f; font-weight:bold; margin-left:auto;"><?php echo formatCurrency($total_cost); ?></span>
							<?php else: ?>
								<?php $total_cost = 0; ?>
								<span style="color:#a9744f; font-weight:bold; margin-left:auto;"><?php echo formatCurrency($total_cost); ?></span>
							<?php endif; ?>
						</p>
					</div>
					<?php if ($cart_row_count == 0): ?>
						<p class="text-center mt-3">
							<button disabled class="btn py-3 px-4" style="background:#a9744f; color:#fff;">Thanh toán</button>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
require "../includes/footer.php";
ob_end_flush();
?>