<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\users\orders.php

// Load config (sẽ tự động load SessionManager)
require_once "../config/config.php";

// Kiểm tra người dùng đã đăng nhập chưa
if (function_exists('session') && !session()->isLoggedIn()) {
	session()->setFlash('error_message', "Vui lòng đăng nhập để xem đơn hàng");
	redirect(APPURL . "/auth/login.php");
}

// Lấy thông tin người dùng từ session
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : 0;

// Load header sau khi kiểm tra quyền truy cập
require "../includes/header.php";

// Lấy danh sách đơn hàng của người dùng
try {
	// Sử dụng prepared statement để tránh SQL injection
	$orders = $conn->prepare("
        SELECT ID, firstname, lastname, streetaddress, appartment, towncity, 
               postcode, phone, email, payable_total_cost, status 
        FROM orders 
        WHERE user_id = :user_id
        ORDER BY ID DESC
    ");
	$orders->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	$orders->execute();
	$orders_values = $orders->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	// Log lỗi database
	error_log("Database Error in orders.php: " . $e->getMessage());
	$orders_values = [];
}
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Đơn Hàng Của Tôi</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Đơn Hàng</span></p>
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
								<th>Họ</th>
								<th>Tên</th>
								<th>Tổng Tiền</th>
								<th>Số Điện Thoại</th>
								<th>Địa Chỉ</th>
								<th>Trạng Thái</th>
								<th>Đánh Giá</th>
							</tr>
						</thead>
						<tbody>
							<?php if (count($orders_values) == 0): ?>
								<tr class="text-center">
									<td colspan="7" class="py-4">Bạn chưa có đơn hàng nào.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($orders_values as $order): ?>
									<tr class="text-center">
										<td>
											<p><?php echo escape($order->firstname); ?></p>
										</td>
										<td>
											<p><?php echo escape($order->lastname); ?></p>
										</td>
										<td>
											<p><?php echo formatCurrency($order->payable_total_cost); ?></p>
										</td>
										<td>
											<p><?php echo escape($order->phone); ?></p>
										</td>
										<td>
											<p><?php echo escape($order->streetaddress); ?></p>
											<?php if (!empty($order->appartment)): ?>
												<p style="margin-top:-20px;"><?php echo escape($order->appartment); ?></p>
											<?php endif; ?>
											<p style="margin-top:-20px;"><?php echo escape($order->towncity); ?></p>
											<p style="margin-top:-20px;"><?php echo escape($order->postcode); ?></p>
										</td>
										<td>
											<?php
											// Dịch trạng thái sang tiếng Việt
											$statusClass = "";
											$statusText = $order->status;

											if (strtolower($order->status) == "pending") {
												$statusText = "Đang xử lý";
												$statusClass = "text-warning";
											} elseif (strtolower($order->status) == "delivered") {
												$statusText = "Đã giao hàng";
												$statusClass = "text-success";
											} elseif (strtolower($order->status) == "cancelled") {
												$statusText = "Đã hủy";
												$statusClass = "text-danger";
											}
											?>
											<p class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></p>
										</td>
										<td>
											<?php if (strtolower($order->status) == "delivered"): ?>
												<a href="<?php echo APPURL; ?>/reviews/write-reviews.php?order_id=<?php echo $order->ID; ?>" class="btn btn-primary">Viết Đánh Giá</a>
											<?php else: ?>
												<button class="btn btn-secondary" disabled>Chưa giao hàng</button>
											<?php endif; ?>
										</td>
									</tr>
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