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
        SELECT 
            COALESCE(membership_tier, 'none') as membership_tier, 
            COUNT(*) as count
        FROM users
        WHERE user_email IS NOT NULL
        GROUP BY membership_tier
        ORDER BY 
            CASE membership_tier 
                WHEN 'gold' THEN 1
                WHEN 'silver' THEN 2
                WHEN 'bronze' THEN 3
                ELSE 4
            END
    ");
    $tierData = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý dữ liệu để hiển thị
    $stats['tiers'] = [];
    foreach ($tierData as $tier) {
        $tierKey = $tier['membership_tier'] ?? 'none';
        $stats['tiers'][$tierKey] = $tier['count'];
    }

    // Lấy thông tin quy tắc hiện tại
    $rulesStmt = $conn->query("SELECT * FROM membership_rules WHERE is_active = 1 LIMIT 1");
    $rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);

    // Nếu không có quy tắc nào, tạo giá trị mặc định
    if (!$rules) {
        $rules = [
            'points_per_order' => 0.1,
            'reset_period_months' => 12,
            'retention_threshold_percent' => 30
        ];
    }

    // Lấy thông tin các mức hạng với kiểm tra đầy đủ
    $tiersStmt = $conn->query("SELECT * FROM membership_tiers WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thống kê chi tiết hơn
    $advancedStatsStmt = $conn->query("
        SELECT 
            COALESCE(membership_tier, 'none') as tier,
            COUNT(*) as member_count,
            SUM(membership_points) as total_points,
            AVG(membership_points) as avg_points,
            MAX(membership_points) as max_points,
            MIN(membership_points) as min_points
        FROM users 
        WHERE user_email IS NOT NULL
        GROUP BY membership_tier
    ");
    $advancedStats = $advancedStatsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log lỗi
    error_log('Membership Dashboard Error: ' . $e->getMessage());
    $stats['total'] = 0;
    $stats['tiers'] = [];
    $rules = [
        'points_per_order' => 0.1,
        'reset_period_months' => 12,
        'retention_threshold_percent' => 30
    ];
    $tiers = [];
    $advancedStats = [];
}

// Helper function để lấy tên hạng
function getTierDisplayName($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return 'Hạng Đồng';
        case 'silver':
            return 'Hạng Bạc';
        case 'gold':
            return 'Hạng Vàng';
        case 'none':
        case null:
        case '':
            return 'Chưa có hạng';
        default:
            return ucfirst($tierKey);
    }
}

// Helper function để lấy màu sắc
function getTierColor($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return '#CD7F32';
        case 'silver':
            return '#C0C0C0';
        case 'gold':
            return '#FFD700';
        default:
            return '#6c757d';
    }
}

// Helper function để lấy icon
function getTierIcon($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return 'fa-medal';
        case 'silver':
            return 'fa-medal';
        case 'gold':
            return 'fa-crown';
        default:
            return 'fa-user';
    }
}

require "../layouts/header.php";
?>

<style>
    .tier-card {
        transition: transform 0.2s;
    }

    .tier-card:hover {
        transform: translateY(-5px);
    }

    .stats-number {
        font-size: 2rem;
        font-weight: bold;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-crown mr-2"></i>Quản lý Membership
        </h1>
        <div>
            <span class="badge badge-info">Tổng: <?= number_format($stats['total']) ?> thành viên</span>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <!-- Tổng số thành viên -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 tier-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng số thành viên</div>
                            <div class="stats-number text-gray-800"><?= number_format($stats['total']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Vàng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 tier-card" style="border-left: 4px solid #FFD700 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #B8860B;">
                                Hạng Vàng</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['gold'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x" style="color: #FFD700;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Bạc -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 tier-card" style="border-left: 4px solid #C0C0C0 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #708090;">
                                Hạng Bạc</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['silver'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x" style="color: #C0C0C0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thành viên hạng Đồng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 tier-card" style="border-left: 4px solid #CD7F32 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #8B4513;">
                                Hạng Đồng</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['bronze'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x" style="color: #CD7F32;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách tính năng -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-users mr-2"></i>Quản lý thành viên
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Quản lý danh sách thành viên và xem chi tiết thông tin của từng thành viên.</p>
                    <a href="members.php" class="btn btn-primary btn-block">
                        <i class="fas fa-users mr-1"></i> Danh sách thành viên
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-cog mr-2"></i>Cài đặt mức hạng
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Cấu hình các mức hạng thành viên và phần trăm giảm giá tương ứng.</p>
                    <a href="tier-settings.php" class="btn btn-success btn-block">
                        <i class="fas fa-medal mr-1"></i> Cài đặt mức hạng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng 2 -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list-ol mr-2"></i>Cài đặt quy tắc
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Thiết lập quy tắc tích điểm, thời gian reset và điều kiện giữ hạng.</p>
                    <a href="rules-settings.php" class="btn btn-info btn-block">
                        <i class="fas fa-ruler mr-1"></i> Cài đặt quy tắc
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar mr-2"></i>Báo cáo membership
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Xem báo cáo và thống kê về chương trình membership.</p>
                    <a href="reports.php" class="btn btn-secondary btn-block">
                        <i class="fas fa-chart-line mr-1"></i> Xem báo cáo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thông tin quy tắc hiện tại -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-rules mr-2"></i>Quy tắc membership hiện tại
                    </h6>
                    <a href="rules-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <tr>
                                    <th style="width: 40%;">
                                        <i class="fas fa-coins mr-2 text-warning"></i>Điểm tích lũy mỗi 10.000đ
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= number_format(($rules['points_per_order'] ?? 0.1) * 10000, 0) ?> điểm
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-calendar-alt mr-2 text-info"></i>Thời gian reset điểm
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= $rules['reset_period_months'] ?? 12 ?> tháng
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-percentage mr-2 text-success"></i>Ngưỡng duy trì hạng
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= $rules['retention_threshold_percent'] ?? 30 ?>% điểm tối đa của mức hạng
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thông tin các mức hạng -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>Mức hạng thành viên
                    </h6>
                    <a href="tier-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($tiers)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Chưa có mức hạng nào được cấu hình.
                            <a href="tier-settings.php" class="alert-link">Nhấn vào đây để cấu hình</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-tag mr-1"></i>Tên mức hạng</th>
                                        <th><i class="fas fa-star mr-1"></i>Điểm tích lũy</th>
                                        <th><i class="fas fa-percentage mr-1"></i>Giảm giá</th>
                                        <th><i class="fas fa-toggle-on mr-1"></i>Trạng thái</th>
                                        <th><i class="fas fa-users mr-1"></i>Số thành viên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tiers as $tier): ?>
                                        <tr>
                                            <td>
                                                <span style="color: <?= htmlspecialchars($tier['tier_color'] ?? getTierColor($tier['tier_key'])) ?>">
                                                    <i class="fas <?= htmlspecialchars($tier['tier_icon'] ?? getTierIcon($tier['tier_key'])) ?> mr-2"></i>
                                                    <strong><?= htmlspecialchars($tier['tier_name']) ?></strong>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?= number_format($tier['min_points']) ?></span>
                                                <?php if (isset($tier['max_points']) && $tier['max_points']): ?>
                                                    - <?= number_format($tier['max_points']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">trở lên</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= number_format($tier['discount_percent'], 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($tier['status'] == 'active'): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>Đang hoạt động
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-pause mr-1"></i>Không hoạt động
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">
                                                    <?= number_format($stats['tiers'][$tier['tier_key']] ?? 0) ?>
                                                </span>
                                                <small class="text-muted">thành viên</small>
                                            </td>
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