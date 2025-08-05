<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy tổng số thành viên theo từng hạng mức
$stats = [];
try {
    // Tổng số thành viên
    $totalStmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_email IS NOT NULL");
    $stats['total'] = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Thống kê theo mức hạng
    $tierStmt = $conn->query("
    SELECT membership_tier, COUNT(*) as count
    FROM users
    WHERE user_email IS NOT NULL
    GROUP BY membership_tier
  ");
    $tierData = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý dữ liệu để hiển thị
    $stats['tiers'] = [];
    foreach ($tierData as $tier) {
        $stats['tiers'][$tier['membership_tier']] = $tier['count'];
    }

    // Lấy thông tin quy tắc hiện tại
    $rulesStmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
    $rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);

    // Lấy thông tin các mức hạng
    $tiersStmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log lỗi
    error_log('Membership Dashboard Error: ' . $e->getMessage());
    $stats['total'] = 0;
    $stats['tiers'] = [];
    $rules = [];
    $tiers = [];
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800">Quản lý Membership</h1>

    <!-- Thông tin tổng quan -->
    <div class="row">
        <!-- Tổng số thành viên -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng số thành viên</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Đồng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Hạng Đồng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['tiers']['bronze'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-warning-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Bạc -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Hạng Bạc</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['tiers']['silver'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Vàng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Hạng Vàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['tiers']['gold'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-yellow-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách tính năng -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quản lý thành viên</h6>
                </div>
                <div class="card-body">
                    <p>Quản lý danh sách thành viên và xem chi tiết thông tin của từng thành viên.</p>
                    <a href="members.php" class="btn btn-primary btn-block">
                        <i class="fas fa-users mr-1"></i> Danh sách thành viên
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cài đặt mức hạng</h6>
                </div>
                <div class="card-body">
                    <p>Cấu hình các mức hạng thành viên và phần trăm giảm giá tương ứng.</p>
                    <a href="tier-settings.php" class="btn btn-success btn-block">
                        <i class="fas fa-cog mr-1"></i> Cài đặt mức hạng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng 2 -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cài đặt quy tắc</h6>
                </div>
                <div class="card-body">
                    <p>Thiết lập quy tắc tích điểm, thời gian reset và điều kiện giữ hạng.</p>
                    <a href="rules-settings.php" class="btn btn-info btn-block">
                        <i class="fas fa-list-ol mr-1"></i> Cài đặt quy tắc
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Báo cáo membership</h6>
                </div>
                <div class="card-body">
                    <p>Xem báo cáo và thống kê về chương trình membership.</p>
                    <a href="reports.php" class="btn btn-secondary btn-block">
                        <i class="fas fa-chart-bar mr-1"></i> Xem báo cáo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thông tin quy tắc hiện tại -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Quy tắc membership hiện tại</h6>
                    <a href="rules-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th>Điểm tích lũy mỗi 10.000đ</th>
                                <td><?= number_format(($rules['points_per_order'] ?? 0.1) * 10000, 0) ?> điểm</td>
                            </tr>
                            <tr>
                                <th>Thời gian reset điểm</th>
                                <td><?= $rules['reset_period_months'] ?? 12 ?> tháng</td>
                            </tr>
                            <tr>
                                <th>Ngưỡng duy trì hạng</th>
                                <td><?= $rules['retention_threshold_percent'] ?? 30 ?>% điểm tối đa của mức hạng</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thông tin các mức hạng -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Mức hạng thành viên</h6>
                    <a href="tier-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Tên mức hạng</th>
                                    <th>Điểm tích lũy</th>
                                    <th>Giảm giá</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tiers as $tier): ?>
                                    <tr>
                                        <td>
                                            <span style="color: <?= htmlspecialchars($tier['tier_color']) ?>">
                                                <i class="fas <?= htmlspecialchars($tier['tier_icon']) ?>"></i>
                                                <?= htmlspecialchars($tier['tier_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= number_format($tier['min_points']) ?>
                                            <?= $tier['max_points'] ? ' - ' . number_format($tier['max_points']) : ' trở lên' ?>
                                        </td>
                                        <td><?= number_format($tier['discount_percent'], 1) ?>%</td>
                                        <td>
                                            <?php if ($tier['status'] == 'active'): ?>
                                                <span class="badge badge-success">Đang hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Không hoạt động</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>