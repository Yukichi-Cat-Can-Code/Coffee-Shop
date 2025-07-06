<?php

ob_start();

require "../config/config.php";

// Kiểm tra nếu đã đăng nhập rồi thì chuyển hướng về trang chủ
if (function_exists('session') && session()->isLoggedIn()) {
  redirect(APPURL);
}

$errors = [];

if (isset($_POST['submit'])) {
  if (empty($_POST['user_name'])) {
    $errors[] = "Vui lòng nhập tên người dùng";
  }

  if (empty($_POST['user_email'])) {
    $errors[] = "Vui lòng nhập email";
  } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email không hợp lệ";
  }

  if (empty($_POST['user_pass'])) {
    $errors[] = "Vui lòng nhập mật khẩu";
  } elseif (strlen($_POST['user_pass']) < 6) {
    $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
  }

  if (empty($_POST['user_pass_confirm'])) {
    $errors[] = "Vui lòng xác nhận mật khẩu";
  } elseif ($_POST['user_pass'] !== $_POST['user_pass_confirm']) {
    $errors[] = "Xác nhận mật khẩu không khớp";
  }

  // Kiểm tra email đã tồn tại chưa
  if (!empty($_POST['user_email']) && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
    try {
      $check_email = $conn->prepare("SELECT * FROM users WHERE user_email = :email");
      $check_email->bindParam(":email", $_POST['user_email']);
      $check_email->execute();

      if ($check_email->rowCount() > 0) {
        $errors[] = "Email này đã được sử dụng";
      }
    } catch (PDOException $e) {
      $errors[] = "Lỗi hệ thống: Không thể kiểm tra email";
      error_log("Register - Email check error: " . $e->getMessage());
    }
  }

  if (empty($errors)) {
    try {
      $user_name = trim($_POST['user_name']);
      $user_email = trim($_POST['user_email']);
      $user_pass = password_hash($_POST['user_pass'], PASSWORD_DEFAULT);

      // Bắt đầu transaction
      $conn->beginTransaction();

      $insert = $conn->prepare("INSERT INTO users(user_name, user_email, user_pass) VALUES(:user_name, :user_email, :user_pass)");
      $insert->execute([
        ":user_name" => $user_name,
        ":user_email" => $user_email,
        ":user_pass" => $user_pass,
      ]);

      // Lấy ID của user vừa tạo
      $user_id = $conn->lastInsertId();

      // Commit transaction
      $conn->commit();

      if (function_exists('session')) {
        // Đăng nhập tự động sau khi đăng ký
        session()->login($user_id, $user_name, $user_email);

        // Thêm thông báo thành công
        session()->setFlash('success_message', "Đăng ký thành công! Chào mừng $user_name đến với Artisan Coffee.");

        // Chuyển hướng về trang chủ
        redirect(APPURL);
      } else {
        // Fallback nếu SessionManager không hoạt động
        // Thêm thông báo để hiển thị ở trang login
        redirect(APPURL . "/auth/login.php?registered=success");
      }
    } catch (PDOException $e) {
      // Rollback transaction nếu có lỗi
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }

      $errors[] = "Lỗi hệ thống: Không thể tạo tài khoản";
      error_log("Register error: " . $e->getMessage());
    }
  }
}

// Include header sau khi xử lý logic nhưng trước khi hiển thị HTML
require "../includes/header.php";
?>

<section class="home-slider owl-carousel">
  <div class="slider-item" style="background-image: url(<?php echo APPURL ?>/images/bg_2.jpg);" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
      <div class="row slider-text justify-content-center align-items-center">
        <div class="col-md-7 col-sm-12 text-center ftco-animate">
          <h1 class="mb-3 mt-5 bread">Đăng Ký</h1>
          <p class="breadcrumbs"><span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span> <span>Đăng Ký</span></p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12 ftco-animate">
        <form action="register.php" method="POST" class="billing-form ftco-bg-dark p-3 p-md-5">
          <h3 class="mb-4 billing-heading">Đăng Ký Tài Khoản</h3>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo escape($error); ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="row align-items-end">
            <div class="col-md-12">
              <div class="form-group">
                <label for="Username">Tên người dùng</label>
                <input type="text" name="user_name" class="form-control" placeholder="Nhập tên người dùng"
                  value="<?php echo isset($_POST['user_name']) ? escape($_POST['user_name']) : ''; ?>">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="Email">Email</label>
                <input type="email" name="user_email" class="form-control" placeholder="Nhập email của bạn"
                  value="<?php echo isset($_POST['user_email']) ? escape($_POST['user_email']) : ''; ?>">
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="Password">Mật khẩu</label>
                <input type="password" name="user_pass" class="form-control" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)">
                <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="Password">Xác nhận mật khẩu</label>
                <input type="password" name="user_pass_confirm" class="form-control" placeholder="Nhập lại mật khẩu">
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group mt-4">
                <button class="btn btn-primary py-3 px-4" name="submit" type="submit">Đăng Ký</button>
              </div>
            </div>

            <div class="col-md-12 mt-3">
              <p>Đã có tài khoản? <a href="<?php echo APPURL; ?>/auth/login.php" class="text-primary">Đăng nhập ngay</a></p>
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