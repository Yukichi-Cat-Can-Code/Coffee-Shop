<?php
require "../../config/config.php";
requireAdminLogin();

$errors = [];
$success = false;

// Xử lý form khi submit
if (isset($_POST['submit'])) {
  // Validate dữ liệu
  if (empty($_POST['admin_email'])) {
    $errors[] = "Email không được để trống";
  } elseif (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email không hợp lệ";
  }

  if (empty($_POST['admin_name'])) {
    $errors[] = "Tên không được để trống";
  }

  if (empty($_POST['admin_password'])) {
    $errors[] = "Mật khẩu không được để trống";
  } elseif (strlen($_POST['admin_password']) < 6) {
    $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
  }

  if (empty($_POST['confirm_password'])) {
    $errors[] = "Vui lòng xác nhận mật khẩu";
  } elseif ($_POST['admin_password'] !== $_POST['confirm_password']) {
    $errors[] = "Mật khẩu xác nhận không khớp";
  }

  // Nếu không có lỗi
  if (empty($errors)) {
    $admin_email = $_POST['admin_email'];
    $admin_name = $_POST['admin_name'];
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

    try {
      // Thêm admin mới vào database
      $admin = $conn->prepare("INSERT INTO admins(admin_name, admin_email, admin_password) VALUES(:admin_name, :admin_email, :admin_password)");
      $admin->execute([
        ":admin_name" => $admin_name,
        ":admin_email" => $admin_email,
        ":admin_password" => $admin_password,
      ]);

      // Lưu thông báo thành công vào session
      session()->setFlash('admin_message', "Tài khoản quản trị viên \"{$admin_name}\" đã được tạo thành công!");
      session()->setFlash('admin_message_type', "success");

      // Chuyển hướng về trang danh sách admin
      redirect(ADMINAPPURL . "/admins/admins.php");
      exit;
    } catch (PDOException $e) {
      // Xử lý lỗi database
      if ($e->errorInfo[1] == 1062) { // Lỗi trùng email
        $errors[] = "Email này đã tồn tại trong hệ thống";
      } else {
        $errors[] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
      }
    }
  }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <!-- Phần header card -->
        <div class="card-header bg-white py-3">
          <div class="d-flex align-items-center">
            <div class="page-icon-box me-3">
              <i class="fas fa-user-plus"></i>
            </div>
            <div>
              <h1 class="h3 mb-1 fw-bold text-gray-800">Tạo mới quản trị viên</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                  <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>" class="text-decoration-none">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="admins.php" class="text-decoration-none">Quản trị viên</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>

        <div class="card-body p-4">
          <!-- Hiển thị lỗi -->
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <!-- Form tạo admin -->
          <form method="POST" action="" id="createAdminForm">
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="admin_name" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="admin_name" id="admin_name" class="form-control"
                      placeholder="Nhập tên người dùng" value="<?= isset($_POST['admin_name']) ? htmlspecialchars($_POST['admin_name']) : '' ?>" required>
                  </div>
                  <small class="form-text text-muted">Tên hiển thị của quản trị viên</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="admin_email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="admin_email" id="admin_email" class="form-control"
                      placeholder="Nhập địa chỉ email" value="<?= isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : '' ?>" required>
                  </div>
                  <small class="form-text text-muted">Email dùng để đăng nhập và nhận thông báo</small>
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="admin_password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="admin_password" id="admin_password" class="form-control"
                      placeholder="Tạo mật khẩu" minlength="6" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="fas fa-eye-slash"></i>
                    </button>
                  </div>
                  <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="confirm_password" class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light">
                      <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                      placeholder="Nhập lại mật khẩu" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                      <i class="fas fa-eye-slash"></i>
                    </button>
                  </div>
                  <small id="passwordHelpBlock" class="form-text"></small>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between pt-3">
              <a href="admins.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
              </a>
              <button type="submit" name="submit" class="btn btn-primary px-4">
                <i class="fas fa-user-plus me-1"></i> Tạo quản trị viên
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Form styling improvements */
  .form-label {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  .input-group {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border-radius: 0.25rem;
  }

  .input-group-text {
    width: 42px;
    justify-content: center;
    border-color: #dee2e6;
  }

  .form-control {
    height: 42px;
    border-color: #dee2e6;
    z-index: 0 !important;
    /* Fix z-index issues */
  }

  .btn-outline-secondary {
    border-color: #dee2e6;
    color: #6c757d;
  }

  .btn-outline-secondary:hover {
    background-color: #f8f9fa;
    color: #495057;
  }

  /* Fix for the unexpected error message */
  .alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
  }

  /* Toast position fix */
  .toast-container {
    z-index: 1050;
  }

  /* Fix button spacing */
  .btn {
    display: inline-flex;
    align-items: center;
    height: 42px;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('admin_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordHelpBlock = document.getElementById('passwordHelpBlock');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Kiểm tra mật khẩu khớp không
    function checkPasswordsMatch() {
      if (confirmPasswordInput.value === '') {
        passwordHelpBlock.textContent = '';
        passwordHelpBlock.className = 'form-text';
        return;
      }

      if (passwordInput.value === confirmPasswordInput.value) {
        passwordHelpBlock.textContent = 'Mật khẩu khớp';
        passwordHelpBlock.className = 'form-text text-success';
      } else {
        passwordHelpBlock.textContent = 'Mật khẩu không khớp';
        passwordHelpBlock.className = 'form-text text-danger';
      }
    }

    // Hiển thị/ẩn mật khẩu
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.querySelector('i').className = `fas fa-${type === 'password' ? 'eye-slash' : 'eye'}`;
    });

    toggleConfirmPassword.addEventListener('click', function() {
      const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPasswordInput.setAttribute('type', type);
      this.querySelector('i').className = `fas fa-${type === 'password' ? 'eye-slash' : 'eye'}`;
    });

    // Kiểm tra mật khẩu khi nhập
    passwordInput.addEventListener('input', checkPasswordsMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordsMatch);

    // Kiểm tra form trước khi submit
    document.getElementById('createAdminForm').addEventListener('submit', function(event) {
      if (passwordInput.value !== confirmPasswordInput.value) {
        event.preventDefault();
        alert('Mật khẩu không khớp. Vui lòng kiểm tra lại.');
      }
    });
  });
</script>
<?php
require "../layouts/footer.php";
?>