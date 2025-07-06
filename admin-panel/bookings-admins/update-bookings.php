<?php
require "../../config/config.php";
requireAdminLogin();

// Khởi tạo biến
$error_message = '';
$booking = null;

// Kiểm tra và xử lý booking_id
if (isset($_GET['booking_id']) && is_numeric($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    // Lấy thông tin đặt bàn hiện tại
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE ID = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$booking) {
        session()->setFlash('error_message', "Không tìm thấy thông tin đặt bàn #$booking_id");
        redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
    }

    // Xử lý form khi submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $status = trim($_POST['status']);

        // Validate trạng thái
        if (empty($status) || $status === "Choose Type") {
            $error_message = "Vui lòng chọn trạng thái";
        } else {
            try {
                // Cập nhật trạng thái
                $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE ID = ?");
                $update_result = $update_stmt->execute([$status, $booking_id]);

                if ($update_result) {
                    session()->setFlash('success_message', "Đã cập nhật trạng thái đặt bàn #$booking_id thành công");
                    redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
                } else {
                    $error_message = "Không thể cập nhật trạng thái đặt bàn";
                }
            } catch (PDOException $e) {
                $error_message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
            }
        }
    }
} else {
    session()->setFlash('error_message', "ID đặt bàn không hợp lệ");
    redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Cập Nhật Đặt Bàn</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>/bookings-admins/show-bookings.php">Đặt bàn</a></li>
                    <li class="breadcrumb-item active">Cập nhật đặt bàn #<?= $booking_id ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <!-- Thông báo lỗi -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Thông tin đặt bàn #<?= $booking_id ?></h5>
                </div>
                <div class="card-body">
                    <!-- Thông tin khách hàng -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Thông tin khách hàng:</h6>
                            <p class="mb-1"><strong><?= htmlspecialchars($booking->first_name) ?> <?= htmlspecialchars($booking->last_name) ?></strong></p>
                            <p class="mb-1"><?= htmlspecialchars($booking->phone_number) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Thời gian đặt bàn:</h6>
                            <p class="mb-1"><strong>Ngày:</strong> <?= htmlspecialchars($booking->date) ?></p>
                            <p class="mb-1"><strong>Giờ:</strong> <?= htmlspecialchars($booking->time) ?></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Lời nhắn:</h6>
                        <p class="mb-0"><?= htmlspecialchars($booking->message) ?></p>
                    </div>

                    <hr class="my-4">

                    <!-- Form cập nhật trạng thái -->
                    <h6 class="mb-3">Cập nhật trạng thái</h6>
                    <form action="update-bookings.php?booking_id=<?= $booking_id ?>" method="POST">
                        <div class="mb-4">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Choose Type" <?= empty($booking->status) ? 'selected' : '' ?>>Chọn trạng thái</option>
                                <option value="Pending" <?= $booking->status === 'Pending' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="Confirmed" <?= ($booking->status === 'Confirmed' || $booking->status === 'Confirm') ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="Cancelled" <?= $booking->status === 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                <option value="Completed" <?= $booking->status === 'Completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= ADMINAPPURL ?>/bookings-admins/show-bookings.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Cập nhật trạng thái
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>