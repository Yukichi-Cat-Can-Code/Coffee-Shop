<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy quy tắc hiện tại
try {
    $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
    $rules = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu chưa có quy tắc, tạo mặc định
    if (!$rules) {
        $conn->exec("INSERT INTO membership_rules (points_per_order, reset_period_months, retention_threshold_percent) 
                    VALUES (0.1, 12, 30)");
        $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
        $rules = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Rules Settings Error: ' . $e->getMessage());
    $rules = [
        'points_per_order' => 0.1,
        'reset_period_months' => 12,
        'retention_threshold_percent' => 30
    ];
}

// Xử lý cập nhật quy tắc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rules'])) {
    try {
        $pointsPerOrder = floatval($_POST['points_per_order']);
        $resetPeriod = intval($_POST['reset_period_months']);
        $retentionThreshold = intval($_POST['retention_threshold']);

        // Validate giá trị
        if ($pointsPerOrder <= 0) {
            throw new Exception("Điểm tích lũy phải lớn hơn 0");
        }

        if ($resetPeriod <= 0) {
            throw new Exception("Thời gian reset phải lớn hơn 0 tháng");
        }

        if ($retentionThreshold < 0 || $retentionThreshold > 100) {
            throw new Exception("Ngưỡng duy trì hạng phải từ 0-100%");
        }

        // Cập nhật vào database
        $stmt = $conn->prepare("
            UPDATE membership_rules 
            SET points_per_order = :points_per_order, 
                reset_period_months = :reset_period, 
                retention_threshold_percent = :retention_threshold
            WHERE id = :id
        ");

        $stmt->bindParam(':points_per_order', $pointsPerOrder);
        $stmt->bindParam(':reset_period', $resetPeriod);
        $stmt->bindParam(':retention_threshold', $retentionThreshold);
        $stmt->bindParam(':id', $rules['id']);
        $stmt->execute();

        $successMessage = "Cập nhật quy tắc thành công!";

        // Refresh rules data
        $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
        $rules = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cài đặt quy tắc Membership</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại Dashboard
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cài đặt quy tắc chung</h6>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Điểm tích lũy theo đơn hàng</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" class="form-control"
                                    name="points_per_order" value="<?= htmlspecialchars($rules['points_per_order']) ?>" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">điểm / 1000đ</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Số điểm tích lũy cho mỗi 1000đ chi tiêu</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Thời gian reset điểm</label>
                            <div class="input-group">
                                <input type="number" min="1" class="form-control"
                                    name="reset_period_months" value="<?= htmlspecialchars($rules['reset_period_months']) ?>" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">tháng</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Số tháng trước khi reset điểm nếu không đạt ngưỡng duy trì</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ngưỡng duy trì hạng</label>
                            <div class="input-group">
                                <input type="number" min="0" max="100" class="form-control"
                                    name="retention_threshold" value="<?= htmlspecialchars($rules['retention_threshold_percent']) ?>" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Phần trăm của điểm tối đa cần đạt được để duy trì hạng</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="save_rules" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Lưu cài đặt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>