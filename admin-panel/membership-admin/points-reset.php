<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\points-reset.php
require "../../config/config.php";
requireAdminLogin();

// Chỉ admin có quyền cao nhất mới có thể chạy công cụ này
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != 'super_admin') {
    header("Location: index.php");
    exit;
}

// Lấy quy tắc membership
$rulesStmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
$rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);

// Lấy tất cả các tier để tính toán
$tiersStmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
$tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
$resetCount = 0;
$maintainCount = 0;
$upgradeCount = 0;

// Xử lý khi gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_reset'])) {
    try {
        // Lấy ngày cụ thể để reset (tính từ ngày này trở về trước)
        $resetDate = $_POST['reset_date'] ?? date('Y-m-d');
        $retentionThreshold = $rules['retention_threshold_percent'] / 100;
        $resetMonths = $rules['reset_period_months'];

        // Tìm những user cần đánh giá lại (không có đơn hàng trong khoảng thời gian)
        $stmt = $conn->prepare("
            SELECT id, username, user_email, membership_points, membership_tier 
            FROM users 
            WHERE user_email IS NOT NULL 
            AND (
                last_order_date IS NULL 
                OR last_order_date < DATE_SUB(:reset_date, INTERVAL :reset_months MONTH)
            )
        ");
        $stmt->bindParam(':reset_date', $resetDate);
        $stmt->bindParam(':reset_months', $resetMonths, PDO::PARAM_INT);
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Xử lý từng user
        foreach ($users as $user) {
            $currentPoints = $user['membership_points'];
            $currentTier = $user['membership_tier'];

            // Tìm tier hiện tại và mức điểm tối thiểu
            $currentTierKey = $user['membership_tier'];
            $currentTierMinPoints = 0;

            foreach ($tiers as $tier) {
                if ($tier['tier_key'] == $currentTierKey) {
                    $currentTierMinPoints = $tier['min_points'];
                    break;
                }
            }

            // Tính ngưỡng duy trì hạng
            $retentionThresholdPoints = $currentTierMinPoints * $retentionThreshold;

            // Nếu điểm không đủ để duy trì hạng
            if ($currentPoints < $retentionThresholdPoints) {
                // Xác định tier mới dựa trên điểm
                $newTier = 'none';
                foreach ($tiers as $tier) {
                    if ($currentPoints >= $tier['min_points']) {
                        $newTier = $tier['tier_key'];
                    } else {
                        break;
                    }
                }

                // Cập nhật tier mới nếu khác tier hiện tại
                if ($newTier != $currentTier) {
                    $updateStmt = $conn->prepare("
                        UPDATE users 
                        SET membership_tier = :new_tier,
                            points_reset_date = :reset_date
                        WHERE id = :user_id
                    ");
                    $updateStmt->bindParam(':new_tier', $newTier);
                    $updateStmt->bindParam(':reset_date', $resetDate);
                    $updateStmt->bindParam(':user_id', $user['id']);
                    $updateStmt->execute();

                    // Thêm vào lịch sử
                    $reason = "Đánh giá lại hạng thành viên do không đủ điểm duy trì";
                    $pointsStmt = $conn->prepare("
                        INSERT INTO membership_points (user_id, points_change, reason, admin_id, created_at)
                        VALUES (:user_id, 0, :reason, :admin_id, NOW())
                    ");
                    $pointsStmt->bindParam(':user_id', $user['id']);
                    $pointsStmt->bindParam(':reason', $reason);
                    $pointsStmt->bindParam(':admin_id', $_SESSION['admin_id']);
                    $pointsStmt->execute();

                    if ($newTier < $currentTier) {
                        $resetCount++;
                    } else {
                        $upgradeCount++;
                    }

                    $results[] = [
                        'user' => $user['username'] . ' (' . $user['user_email'] . ')',
                        'old_tier' => $currentTier,
                        'new_tier' => $newTier,
                        'points' => $currentPoints,
                        'threshold' => $retentionThresholdPoints
                    ];
                } else {
                    $maintainCount++;
                }
            } else {
                $maintainCount++;
            }
        }

        $successMessage = "Đã hoàn tất đánh giá lại hạng thành viên cho " . count($users) . " thành viên.";
    } catch (Exception $e) {
        $errorMessage = "Lỗi: " . $e->getMessage();
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Công cụ Reset Điểm & Đánh Giá Hạng</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại Dashboard
        </a>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <!-- Card Thông tin -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Công cụ Reset & Đánh giá lại hạng thành viên</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Lưu ý!</strong> Công cụ này sẽ kiểm tra và hạ cấp các thành viên không hoạt động trong
                        <strong><?= $rules['reset_period_months'] ?> tháng</strong> gần đây, nếu điểm không đạt
                        <strong><?= $rules['retention_threshold_percent'] ?>%</strong> của ngưỡng duy trì hạng.
                    </div>

                    <form method="post" action="" class="mb-4">
                        <div class="form-group">
                            <label>Đánh giá lại tính đến ngày:</label>
                            <input type="date" class="form-control" name="reset_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <button type="submit" name="run_reset" class="btn btn-primary" onclick="return confirm('Bạn có chắc chắn muốn thực hiện đánh giá lại hạng thành viên?')">
                            <i class="fas fa-sync"></i> Chạy công cụ đánh giá
                        </button>
                    </form>

                    <?php if (!empty($results)): ?>
                        <h5>Kết quả đánh giá</h5>
                        <div class="mb-3">
                            <div class="card-deck text-center">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Hạ cấp</h5>
                                        <p class="card-text display-4"><?= $resetCount ?></p>
                                    </div>
                                </div>
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Giữ nguyên</h5>
                                        <p class="card-text display-4"><?= $maintainCount ?></p>
                                    </div>
                                </div>
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Thăng cấp</h5>
                                        <p class="card-text display-4"><?= $upgradeCount ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Thành viên</th>
                                        <th>Điểm</th>
                                        <th>Ngưỡng duy trì</th>
                                        <th>Hạng cũ</th>
                                        <th>Hạng mới</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($result['user']) ?></td>
                                            <td><?= number_format($result['points'], 1) ?></td>
                                            <td><?= number_format($result['threshold'], 1) ?></td>
                                            <td><?= htmlspecialchars($result['old_tier']) ?></td>
                                            <td><?= htmlspecialchars($result['new_tier']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>