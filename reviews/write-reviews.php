<?php

ob_start();

// Đúng thứ tự: đầu tiên load config.php
require_once "../config/config.php";

// Kiểm tra đăng nhập
if (function_exists('session') && !session()->isLoggedIn()) {
	session()->setFlash('error_message', "Vui lòng đăng nhập để viết đánh giá");
	redirect(APPURL . "/auth/login.php");
}

// Lấy thông tin người dùng
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : 0;
$user_name = $currentUser ? $currentUser['username'] : '';

// Kiểm tra order_id có được truyền không
if (isset($_GET['order_id']) && !empty($_GET['order_id']) && is_numeric($_GET['order_id'])) {
	$order_id = (int)$_GET['order_id'];

	// Kiểm tra đơn hàng có tồn tại và thuộc về user hiện tại không
	try {
		$order_check = $conn->prepare("SELECT * FROM orders WHERE ID = :order_id AND user_id = :user_id AND status = 'delivered'");
		$order_check->execute([
			":order_id" => $order_id,
			":user_id" => $user_id
		]);

		if ($order_check->rowCount() == 0) {
			session()->setFlash('error_message', "Bạn chỉ có thể đánh giá đơn hàng đã giao thành công");
			redirect(APPURL . "/users/orders.php");
		}
	} catch (PDOException $e) {
		error_log("Database error checking order: " . $e->getMessage());
	}
} else {
	$order_id = 0;
}

// Khởi tạo biến errors
$errors = [];

// Xử lý form đánh giá
if (isset($_POST['submit'])) {
	// Lấy và làm sạch dữ liệu
	$review = isset($_POST['review']) ? trim($_POST['review']) : '';
	$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

	// Validate dữ liệu
	if (empty($review)) {
		$errors[] = "Vui lòng nhập nội dung đánh giá!";
	}

	if ($rating < 1 || $rating > 5) {
		$errors[] = "Vui lòng chọn số sao đánh giá (1-5)!";
	}

	// Nếu không có lỗi, lưu đánh giá vào database
	if (empty($errors)) {
		try {
			// Kiểm tra xem bảng reviews đã có cột mở rộng chưa
			$table_check = $conn->query("SHOW COLUMNS FROM reviews LIKE 'user_id'");
			$has_user_id = $table_check->rowCount() > 0;

			if ($has_user_id) {
				// Sử dụng cấu trúc bảng mở rộng
				$reviews_query = $conn->prepare(
					"INSERT INTO reviews (review, user_name, user_id, order_id, rating, status, created_at) 
                     VALUES (:review, :user_name, :user_id, :order_id, :rating, :status, NOW())"
				);

				$result = $reviews_query->execute([
					":review" => $review,
					":user_name" => $user_name,
					":user_id" => $user_id,
					":order_id" => $order_id,
					":rating" => $rating,
					":status" => "Pending" // Đánh giá sẽ được admin phê duyệt
				]);
			} else {
				// Tương thích với cấu trúc cơ sở dữ liệu hiện tại
				$reviews_query = $conn->prepare("INSERT INTO reviews (review, user_name, created_at) 
                                              VALUES (:review, :user_name, NOW())");

				$result = $reviews_query->execute([
					":review" => $review,
					":user_name" => $user_name
				]);
			}

			if ($result) {
				session()->setFlash('success_message', "Cảm ơn bạn đã đánh giá! Đánh giá của bạn đã được gửi thành công.");
				redirect(APPURL . "/users/orders.php");
			}
		} catch (PDOException $e) {
			$errors[] = "Đã xảy ra lỗi, vui lòng thử lại!";
			error_log("Database error in write-reviews.php: " . $e->getMessage());
		}
	}
}

// Load header sau khi xử lý form
require "../includes/header.php";
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Đánh Giá</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Đánh Giá</span></p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="ftco-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12 ftco-animate">
				<?php if (!empty($errors)): ?>
					<div class="alert alert-danger">
						<ul>
							<?php foreach ($errors as $error): ?>
								<li><?php echo $error; ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<form action="write-reviews.php<?php echo $order_id ? '?order_id=' . $order_id : ''; ?>" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
					<h3 class="mb-4 billing-heading">Viết Đánh Giá</h3>
					<div class="row align-items-end">
						<div class="col-md-6">
							<div class="form-group">
								<label for="rating">Đánh Giá Sao *</label>
								<div class="rating-stars d-flex">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="rating" id="rating1" value="1" required>
										<label class="form-check-label" for="rating1">1 <i class="icon-star"></i></label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="rating" id="rating2" value="2">
										<label class="form-check-label" for="rating2">2 <i class="icon-star"></i></label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="rating" id="rating3" value="3">
										<label class="form-check-label" for="rating3">3 <i class="icon-star"></i></label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="rating" id="rating4" value="4">
										<label class="form-check-label" for="rating4">4 <i class="icon-star"></i></label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="rating" id="rating5" value="5" checked>
										<label class="form-check-label" for="rating5">5 <i class="icon-star"></i></label>
									</div>
								</div>
							</div>
						</div>
						<div class="w-100"></div>
						<div class="col-md-12">
							<div class="form-group">
								<label for="review">Nội Dung Đánh Giá *</label>
								<textarea name="review" id="review" rows="5" class="form-control" placeholder="Nhập nội dung đánh giá của bạn" required><?php echo isset($_POST['review']) ? htmlspecialchars($_POST['review']) : ''; ?></textarea>
							</div>
						</div>
						<div class="w-100"></div>
						<div class="col-md-12">
							<div class="form-group mt-4">
								<div class="radio">
									<p><button name="submit" type="submit" class="btn btn-primary py-3 px-4">Gửi Đánh Giá</button></p>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<style>
	.rating-stars .form-check-inline {
		margin-right: 15px;
	}

	.rating-stars .icon-star {
		color: #fac564;
	}
</style>

<?php
require "../includes/footer.php";
ob_end_flush();
?>