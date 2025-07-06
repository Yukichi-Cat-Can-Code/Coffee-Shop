<?php
require "../../config/config.php";
requireAdminLogin();

// Khởi tạo biến
$error_message = "";
$success_message = "";

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Validate dữ liệu
    if (
        empty($first_name) || empty($last_name) || empty($date) ||
        empty($time) || empty($phone_number) || empty($status)
    ) {
        $error_message = "Vui lòng điền đầy đủ các trường bắt buộc";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO bookings (first_name, last_name, date, time, phone_number, message, status) 
                              VALUES (:first_name, :last_name, :date, :time, :phone_number, :message, :status)");

            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':time', $time);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                session()->setFlash('success_message', "Đã tạo đặt bàn mới thành công");
                redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
            } else {
                $error_message = "Có lỗi xảy ra khi tạo đặt bàn";
            }
        } catch (PDOException $e) {
            $error_message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tạo Đặt Bàn Mới</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>/bookings-admins/show-bookings.php">Đặt bàn</a></li>
                    <li class="breadcrumb-item active">Tạo mới</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Messages -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Thông tin đặt bàn</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Họ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Ngày đặt <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="time" class="form-label">Giờ đặt <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="time" name="time" value="<?= htmlspecialchars($time ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($phone_number ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Lời nhắn</label>
                    <textarea class="form-control" id="message" name="message" rows="3"><?= htmlspecialchars($message ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="">Chọn trạng thái</option>
                        <option value="Pending" <?= (isset($status) && $status == 'Pending') ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="Confirmed" <?= (isset($status) && $status == 'Confirmed') ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="Cancelled" <?= (isset($status) && $status == 'Cancelled') ? 'selected' : '' ?>>Đã hủy</option>
                        <option value="Completed" <?= (isset($status) && $status == 'Completed') ? 'selected' : '' ?>>Đã hoàn thành</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="<?= ADMINAPPURL ?>/bookings-admins/show-bookings.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Tạo đặt bàn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-fill today's date if not already set
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('date').value) {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
        }
    });
</script>

<?php require "../layouts/footer.php"; ?>