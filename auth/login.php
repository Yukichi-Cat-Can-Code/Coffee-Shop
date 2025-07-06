<?php
// Sử dụng output buffering để tránh lỗi headers
ob_start();

require_once "../config/config.php";

// Kiểm tra nếu đã đăng nhập rồi thì chuyển hướng về trang chủ
if (function_exists('session') && session()->isLoggedIn()) {
	redirect(APPURL);
}

$errors = []; // Mảng lưu lỗi

if (isset($_POST['submit'])) {
	// Kiểm tra các trường nhập liệu
	if (empty($_POST['user_email'])) {
		$errors[] = "Vui lòng nhập email";
	} elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Địa chỉ email không hợp lệ";
	}

	if (empty($_POST['user_pass'])) {
		$errors[] = "Vui lòng nhập mật khẩu";
	}

	// Nếu không có lỗi, tiến hành đăng nhập
	if (empty($errors)) {
		try {
			$user_email = trim($_POST['user_email']);
			$user_pass = $_POST['user_pass'];

			// Dùng prepared statement để tránh SQL injection
			$login = $conn->prepare("SELECT * FROM users WHERE user_email = :email");
			$login->bindParam(":email", $user_email);
			$login->execute();

			if ($login->rowCount() > 0) {
				$user = $login->fetch();

				if (password_verify($user_pass, $user->user_pass)) {
					// Đăng nhập thành công với SessionManager
					if (function_exists('session')) {
						session()->login($user->ID, $user->user_name, $user->user_email);

						// Lưu thêm thông tin người dùng nếu cần
						if (isset($user->is_admin) && $user->is_admin == 1) {
							session()->set('is_admin', true);
						}

						// Thông báo thành công
						session()->setFlash('success_message', "Đăng nhập thành công! Chào mừng trở lại, {$user->user_name}");
					} else {
						// Fallback nếu SessionManager không hoạt động
						$_SESSION['user_name'] = $user->user_name;
						$_SESSION['user_email'] = $user->user_email;
						$_SESSION['user_id'] = $user->ID;
					}

					// Chuyển hướng về trang chủ
					redirect(APPURL);
				} else {
					$errors[] = "Email hoặc mật khẩu không chính xác!";
				}
			} else {
				$errors[] = "Email hoặc mật khẩu không chính xác!";
			}
		} catch (PDOException $e) {
			error_log("Login error: " . $e->getMessage());
			$errors[] = "Đã xảy ra lỗi hệ thống, vui lòng thử lại sau.";
		}
	}

	// Lưu lỗi vào flash để hiển thị sau khi redirect
	if (!empty($errors) && function_exists('session')) {
		foreach ($errors as $error) {
			session()->setFlash('error_message', $error);
		}
	}
}

// Include header sau khi xử lý logic để tránh output trước redirect
require "../includes/header.php";
?>

<section class="home-slider owl-carousel">
	<div class="slider-item" style="background-image: url(<?php echo APPURL ?>/images/bg_1.jpg);" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row slider-text justify-content-center align-items-center">
				<div class="col-md-7 col-sm-12 text-center ftco-animate">
					<h1 class="mb-3 mt-5 bread">Đăng Nhập</h1>
					<p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Đăng Nhập</span></p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="ftco-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12 ftco-animate">
				<form action="login.php" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
					<h3 class="mb-4 billing-heading">Đăng Nhập</h3>

					<?php if (isset($errors) && !empty($errors)): ?>
						<div class="alert alert-danger">
							<?php foreach ($errors as $error): ?>
								<p class="mb-0"><?php echo escape($error); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="row align-items-end">
						<div class="col-md-12">
							<div class="form-group">
								<label for="user_email">Email</label>
								<input type="email" name="user_email" id="user_email" class="form-control"
									placeholder="Nhập địa chỉ email" value="<?php echo isset($_POST['user_email']) ? escape($_POST['user_email']) : ''; ?>" required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label for="user_pass">Mật Khẩu</label>
								<input type="password" name="user_pass" id="user_pass" class="form-control"
									placeholder="Nhập mật khẩu" required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group mt-4">
								<button name="submit" type="submit" class="btn btn-primary py-3 px-4">Đăng Nhập</button>
							</div>
						</div>

						<div class="col-md-12 mt-3">
							<p>Chưa có tài khoản? <a href="<?php echo APPURL; ?>/auth/register.php" class="text-primary">Đăng Ký Ngay</a></p>
							<p><a href="<?php echo APPURL; ?>/auth/forgot-password.php" class="text-muted">Quên mật khẩu?</a></p>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<?php
require "../includes/footer.php";
ob_end_flush();
?>