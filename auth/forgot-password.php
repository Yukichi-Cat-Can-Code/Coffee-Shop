<?php
require "../includes/header.php";

// Kiểm tra nếu đã đăng nhập rồi thì chuyển hướng về trang chủ
if (function_exists('session') && session()->isLoggedIn()) {
    redirect(APPURL);
}

$message = '';
$messageType = '';
$showEmailForm = true;
$showPasswordForm = false;
$email = '';

// Xử lý form email
if (isset($_POST['check_email'])) {
    if (empty($_POST['email'])) {
        $message = "Vui lòng nhập địa chỉ email";
        $messageType = "danger";
    } else {
        $email = trim($_POST['email']);

        // Kiểm tra email có tồn tại trong hệ thống không
        $check_email = $conn->prepare("SELECT * FROM users WHERE user_email = :email");
        $check_email->bindParam(":email", $email);
        $check_email->execute();

        if ($check_email->rowCount() > 0) {
            // Email tồn tại, hiển thị form đổi mật khẩu
            $showEmailForm = false;
            $showPasswordForm = true;
        } else {
            $message = "Email này không tồn tại trong hệ thống";
            $messageType = "danger";
        }
    }
}

// Xử lý form đổi mật khẩu
if (isset($_POST['change_password'])) {
    $errors = [];
    $email = trim($_POST['email']);

    // Kiểm tra mật khẩu
    if (empty($_POST['new_password'])) {
        $errors[] = "Vui lòng nhập mật khẩu mới";
    } elseif (strlen($_POST['new_password']) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }

    // Kiểm tra xác nhận mật khẩu
    if (empty($_POST['confirm_password'])) {
        $errors[] = "Vui lòng xác nhận mật khẩu";
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $errors[] = "Xác nhận mật khẩu không khớp";
    }

    if (empty($errors)) {
        try {
            // Cập nhật mật khẩu mới
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET user_pass = :password WHERE user_email = :email");
            $update->bindParam(":password", $hashed_password);
            $update->bindParam(":email", $email);

            if ($update->execute()) {
                $message = "Mật khẩu đã được cập nhật thành công. Bạn có thể đăng nhập ngay bây giờ.";
                $messageType = "success";
                $showPasswordForm = false;
                $showEmailForm = false;

                // Thêm flash message để hiển thị sau khi redirect
                if (function_exists('session')) {
                    session()->setFlash('success_message', 'Mật khẩu đã được đặt lại thành công!');
                }
            } else {
                $message = "Có lỗi xảy ra. Vui lòng thử lại sau.";
                $messageType = "danger";
                $showPasswordForm = true;
                $showEmailForm = false;
            }
        } catch (PDOException $e) {
            $message = "Lỗi hệ thống: " . $e->getMessage();
            $messageType = "danger";
            $showPasswordForm = true;
            $showEmailForm = false;
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "danger";
        $showPasswordForm = true;
        $showEmailForm = false;
    }
}
?>

<section class="home-slider owl-carousel">
    <div class="slider-item" style="background-image: url(<?php echo APPURL ?>/images/bg_1.jpg);" data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row slider-text justify-content-center align-items-center">
                <div class="col-md-7 col-sm-12 text-center ftco-animate">
                    <h1 class="mb-3 mt-5 bread">Quên Mật Khẩu</h1>
                    <p class="breadcrumbs">
                        <span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span>
                        <span>Quên Mật Khẩu</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 ftco-animate">
                <div class="billing-form ftco-bg-dark p-3 p-md-5">
                    <h3 class="mb-4 billing-heading">Khôi Phục Mật Khẩu</h3>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo $message; ?>

                            <?php if ($messageType == "success" && !$showEmailForm && !$showPasswordForm): ?>
                                <p class="mt-3">
                                    <a href="<?php echo APPURL; ?>/auth/login.php" class="btn btn-primary">Đăng nhập ngay</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($showEmailForm): ?>
                        <!-- Form nhập email -->
                        <form action="" method="POST">
                            <p class="text-muted mb-4">Vui lòng nhập địa chỉ email đã đăng ký để khôi phục mật khẩu.</p>

                            <div class="row align-items-end">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            placeholder="Nhập địa chỉ email"
                                            value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mt-4">
                                        <button name="check_email" type="submit" class="btn btn-primary py-3 px-4">Tiếp tục</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if ($showPasswordForm): ?>
                        <!-- Form đổi mật khẩu -->
                        <form action="" method="POST">
                            <input type="hidden" name="email" value="<?php echo escape($email); ?>">

                            <p class="text-muted mb-4">Vui lòng nhập mật khẩu mới cho tài khoản của bạn.</p>

                            <div class="row align-items-end">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="new_password">Mật khẩu mới</label>
                                        <input type="password" name="new_password" id="new_password" class="form-control"
                                            placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="confirm_password">Xác nhận mật khẩu</label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                            placeholder="Nhập lại mật khẩu mới" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mt-4">
                                        <button name="change_password" type="submit" class="btn btn-primary py-3 px-4">Đổi mật khẩu</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="col-md-12 mt-3">
                        <p>Đã nhớ mật khẩu? <a href="<?php echo APPURL; ?>/auth/login.php" class="text-primary">Đăng nhập</a></p>
                        <p>Chưa có tài khoản? <a href="<?php echo APPURL; ?>/auth/register.php" class="text-primary">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "../includes/footer.php"; ?>