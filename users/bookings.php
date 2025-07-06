<?php

require_once "../config/config.php";

// Kiểm tra người dùng đã đăng nhập chưa
if (function_exists('session') && !session()->isLoggedIn()) {
	session()->setFlash('error_message', "Vui lòng đăng nhập để xem lịch đặt bàn");
	redirect(APPURL . "/auth/login.php");
}

// Lấy thông tin người dùng từ session
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : 0;

// Load header sau khi kiểm tra quyền truy cập
require "../includes/header.php";

try {
	// Sử dụng prepared statement để truy vấn an toàn
	$bookings = $conn->prepare("SELECT ID, first_name, last_name, date, time, phone_number, 
                              SUBSTRING(message, 1, 10) as message, status 
                              FROM bookings 
                              WHERE user_id = :user_id
                              ORDER BY date DESC");
	$bookings->bindParam(":user_id", $user_id);
	$bookings->execute();
	$bookings_values = $bookings->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	// Log lỗi và khởi tạo mảng trống
	error_log("Database Error in bookings.php: " . $e->getMessage());
	$bookings_values = [];
}
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Lịch Đặt Bàn</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Đặt Bàn</span></p>
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
								<th>Họ</th>
								<th>Tên</th>
								<th>Ngày Đặt</th>
								<th>Số Điện Thoại</th>
								<th>Lời Nhắn</th>
								<th>Trạng Thái</th>
							</tr>
						</thead>
						<tbody>
							<?php if (count($bookings_values) == 0): ?>
								<tr class="text-center">
									<td colspan="7" class="py-4">Bạn chưa có lịch đặt bàn nào.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($bookings_values as $booking): ?>
									<tr class="text-center">
										<td class="product-remove">
											<?php if (strtolower($booking->status) == "approved" || strtolower($booking->status) == "đã duyệt"): ?>
												<a href="#" class="disabled"><span class="icon-lock"></span></a>
											<?php else: ?>
												<a href="delete-bookings.php?booking_id=<?php echo $booking->ID; ?>" class="delete-booking" style="cursor: pointer;"
													onclick="return confirm('Bạn có chắc chắn muốn hủy đặt bàn này?');">
													<span class="icon-close"></span>
												</a>
											<?php endif; ?>
										</td>
										<td>
											<p><?php echo escape($booking->first_name); ?></p>
										</td>
										<td>
											<p><?php echo escape($booking->last_name); ?></p>
										</td>
										<td>
											<p><?php echo escape($booking->date); ?></p>
											<p style="margin-top:-10px;"><?php echo escape($booking->time); ?></p>
										</td>
										<td>
											<p><?php echo escape($booking->phone_number); ?></p>
										</td>
										<td>
											<p><?php echo escape($booking->message); ?><?php echo strlen($booking->message) >= 10 ? '...' : ''; ?></p>
										</td>
										<td>
											<?php
											// Dịch trạng thái sang tiếng Việt
											$statusClass = "";
											$statusText = $booking->status;

											if (strtolower($booking->status) == "pending") {
												$statusText = "Đang chờ";
												$statusClass = "text-warning";
											} elseif (strtolower($booking->status) == "approved") {
												$statusText = "Đã duyệt";
												$statusClass = "text-success";
											} elseif (strtolower($booking->status) == "cancelled") {
												$statusText = "Đã hủy";
												$statusClass = "text-danger";
											}
											?>
											<p class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></p>
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