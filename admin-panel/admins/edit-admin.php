<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy admin_id từ URL hoặc session
$admin_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['admin_id'];

// Kiểm tra quyền: chỉ cho phép sửa chính mình hoặc superadmin
if ($_SESSION['admin_id'] != $admin_id && (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != 'superadmin')) {
    session()->setFlash('admin_message', "Bạn không có quyền chỉnh sửa thông tin này");
    session()->setFlash('admin_message_type', "danger");
    redirect(ADMINAPPURL . "/admins/admins.php");
    exit;
}

// Thông báo lỗi/thành công
$error_message = "";
$success_message = "";

// Lấy thông tin admin
try {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE ID = :admin_id");
    $stmt->bindParam(':admin_id', $admin_id);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$admin) {
        session()->setFlash('error_message', "Không tìm thấy quản trị viên này");
        session()->setFlash('error_message_type', "danger");
        redirect(ADMINAPPURL . "/admins/admins.php");
        exit;
    }
} catch (PDOException $e) {
    session()->setFlash('error_message', "Lỗi cơ sở dữ liệu: " . $e->getMessage());
    session()->setFlash('error_message_type', "danger");
    redirect(ADMINAPPURL . "/admins/admins.php");
    exit;
}

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($admin_name) || empty($admin_email) || empty($current_password)) {
        $error_message = "Vui lòng điền đầy đủ thông tin bắt buộc";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Định dạng email không hợp lệ";
    } else {
        try {
            // Kiểm tra email đã tồn tại cho admin khác chưa
            $check_email = $conn->prepare("SELECT * FROM admins WHERE admin_email = :admin_email AND ID != :admin_id");
            $check_email->bindParam(':admin_email', $admin_email);
            $check_email->bindParam(':admin_id', $admin_id);
            $check_email->execute();

            if ($check_email->rowCount() > 0) {
                $error_message = "Email này đã được sử dụng bởi tài khoản khác";
            } else {
                // Kiểm tra mật khẩu hiện tại
                $verify_password = $conn->prepare("SELECT admin_password FROM admins WHERE ID = :admin_id");
                $verify_password->bindParam(':admin_id', $admin_id);
                $verify_password->execute();
                $stored_hash = $verify_password->fetch(PDO::FETCH_OBJ)->admin_password;

                if (!password_verify($current_password, $stored_hash)) {
                    $error_message = "Mật khẩu hiện tại không chính xác";
                } else {
                    $update_password = false;
                    if (!empty($new_password)) {
                        if (strlen($new_password) < 8) {
                            $error_message = "Mật khẩu mới phải có ít nhất 8 ký tự";
                        } elseif ($new_password !== $confirm_password) {
                            $error_message = "Mật khẩu mới và xác nhận mật khẩu không khớp";
                        } else {
                            $update_password = true;
                        }
                    }

                    if (empty($error_message)) {
                        if ($update_password) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_stmt = $conn->prepare("UPDATE admins SET admin_name = :admin_name, admin_email = :admin_email, admin_password = :admin_password WHERE ID = :admin_id");
                            $update_stmt->bindParam(':admin_password', $hashed_password);
                        } else {
                            $update_stmt = $conn->prepare("UPDATE admins SET admin_name = :admin_name, admin_email = :admin_email WHERE ID = :admin_id");
                        }

                        $update_stmt->bindParam(':admin_name', $admin_name);
                        $update_stmt->bindParam(':admin_email', $admin_email);
                        $update_stmt->bindParam(':admin_id', $admin_id);

                        if ($update_stmt->execute()) {
                            // Cập nhật session nếu là chính mình
                            if ($_SESSION['admin_id'] == $admin_id) {
                                $_SESSION['admin_name'] = $admin_name;
                                $_SESSION['admin_email'] = $admin_email;
                            }
                            $success_message = "Cập nhật thông tin thành công!";

                            // Refresh admin data
                            $stmt = $conn->prepare("SELECT * FROM admins WHERE ID = :admin_id");
                            $stmt->bindParam(':admin_id', $admin_id);
                            $stmt->execute();
                            $admin = $stmt->fetch(PDO::FETCH_OBJ);
                        } else {
                            $error_message = "Có lỗi xảy ra khi cập nhật thông tin";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}

require "../layouts/header.php";
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa quản trị viên</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?php echo ADMINAPPURL; ?>">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo ADMINAPPURL; ?>/admins/admins.php">Quản trị viên</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card card-dark shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin quản trị viên</h3>
                    </div>

                    <!-- Form start -->
                    <form method="POST" action="">
                        <div class="card-body">
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="admin_name" class="form-label">Tên quản trị viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin->admin_name); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin->admin_email); ?>" required>
                            </div>

                            <hr>
                            <h5 class="text-muted mb-3">Xác nhận & Đổi mật khẩu</h5>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Nhập mật khẩu hiện tại để xác nhận thay đổi</small>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent d-flex justify-content-between">
                            <a href="<?php echo ADMINAPPURL; ?>/admins/admins.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>