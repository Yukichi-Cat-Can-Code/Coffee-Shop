<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\contact.php

require_once "config/config.php";
require "includes/header.php";

// Khởi tạo biến để lưu thông báo
$success_message = '';
$error_message = '';

// Xử lý form khi được submit
if (isset($_POST['submit'])) {
	// Lấy và làm sạch dữ liệu
	$name = isset($_POST['name']) ? trim(escape($_POST['name'])) : '';
	$email = isset($_POST['email']) ? trim($_POST['email']) : '';
	$subject = isset($_POST['subject']) ? trim(escape($_POST['subject'])) : '';
	$message = isset($_POST['message']) ? trim(escape($_POST['message'])) : '';

	// Validate dữ liệu
	$errors = [];

	if (empty($name)) {
		$errors[] = "Vui lòng nhập họ tên";
	}

	if (empty($email)) {
		$errors[] = "Vui lòng nhập email";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Email không hợp lệ";
	}

	if (empty($subject)) {
		$errors[] = "Vui lòng nhập tiêu đề";
	}

	if (empty($message)) {
		$errors[] = "Vui lòng nhập nội dung tin nhắn";
	}

	// Nếu không có lỗi, xử lý gửi liên hệ
	if (empty($errors)) {
		try {
			// Bắt đầu transaction
			$conn->beginTransaction();

			// Lưu liên hệ vào database
			$insert = $conn->prepare("
                INSERT INTO contacts (name, email, subject, message, created_at)
                VALUES (:name, :email, :subject, :message, NOW())
            ");

			$result = $insert->execute([
				":name" => $name,
				":email" => $email,
				":subject" => $subject,
				":message" => $message
			]);

			// Commit transaction
			$conn->commit();

			// Hiển thị thông báo thành công
			if (function_exists('session')) {
				session()->setFlash('success_message', "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.");
				redirect(APPURL . "/contact.php?sent=success");
			} else {
				$success_message = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.";
			}

			// Reset form
			$name = $email = $subject = $message = '';
		} catch (PDOException $e) {
			// Rollback transaction nếu có lỗi
			$conn->rollBack();

			// Log lỗi
			error_log("Contact form error: " . $e->getMessage());

			// Hiển thị thông báo lỗi
			$error_message = "Đã xảy ra lỗi khi gửi liên hệ. Vui lòng thử lại sau.";
		}
	} else {
		// Hiển thị các lỗi validation
		$error_message = implode("<br>", $errors);
	}
}
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Liên Hệ</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Liên Hệ</span></p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="ftco-section contact-section">
	<div class="container mt-5">
		<div class="row block-9">
			<div class="col-md-4 contact-info ftco-animate">
				<div class="row">
					<div class="col-md-12 mb-4">
						<h2 class="h4">Thông Tin Liên Hệ</h2>
					</div>
					<div class="col-md-12 mb-3">
						<p><span>Địa chỉ:</span> 123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</p>
					</div>
					<div class="col-md-12 mb-3">
						<p><span>Điện thoại:</span> <a href="tel://84281234567">+84 28 1234 5678</a></p>
					</div>
					<div class="col-md-12 mb-3">
						<p><span>Email:</span> <a href="mailto:info@artisancoffee.vn">info@artisancoffee.vn</a></p>
					</div>
					<div class="col-md-12 mb-3">
						<p><span>Website:</span> <a href="<?php echo APPURL; ?>"><?php echo str_replace(["http://", "https://"], "", APPURL); ?></a></p>
					</div>
				</div>
			</div>
			<div class="col-md-1"></div>
			<div class="col-md-6 ftco-animate">
				<?php if (!empty($success_message)): ?>
					<div class="alert alert-success">
						<?php echo $success_message; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($error_message)): ?>
					<div class="alert alert-danger">
						<?php echo $error_message; ?>
					</div>
				<?php endif; ?>

				<form method="POST" action="<?php echo APPURL; ?>/contact.php" class="contact-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<input type="text" name="name" class="form-control" placeholder="Họ và Tên" value="<?php echo isset($name) ? escape($name) : ''; ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<input type="email" name="email" class="form-control" placeholder="Email của Bạn" value="<?php echo isset($email) ? escape($email) : ''; ?>">
							</div>
						</div>
					</div>
					<div class="form-group">
						<input type="text" name="subject" class="form-control" placeholder="Tiêu Đề" value="<?php echo isset($subject) ? escape($subject) : ''; ?>">
					</div>
					<div class="form-group">
						<textarea name="message" cols="30" rows="7" class="form-control" placeholder="Nội Dung"><?php echo isset($message) ? escape($message) : ''; ?></textarea>
					</div>
					<div class="form-group">
						<input type="submit" name="submit" value="Gửi Tin Nhắn" class="btn btn-primary py-3 px-5">
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Google Map Section -->
<div id="map"></div>



<?php require "includes/footer.php"; ?>