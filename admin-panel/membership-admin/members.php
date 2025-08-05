<?php
require "../../config/config.php";
requireAdminLogin();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tierFilter = isset($_GET['tier']) ? trim($_GET['tier']) : '';

try {
    // Tạo câu truy vấn cơ bản
    $query = "FROM users WHERE user_email IS NOT NULL";
    $params = [];

    // Thêm điều kiện tìm kiếm nếu có
    if (!empty($search)) {
        $query .= " AND (user_name LIKE :search OR user_email LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Lọc theo tier nếu có
    if (!empty($tierFilter)) {
        $query .= " AND membership_tier = :tier";
        $params[':tier'] = $tierFilter;
    }

    // Đếm tổng số records
    $countStmt = $conn->prepare("SELECT COUNT(*) as total " . $query);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Truy vấn dữ liệu với phân trang
    $stmt = $conn->prepare("SELECT ID, user_name, user_email, membership_tier, membership_points, 
                          points_expiry_date, last_tier_update, annual_spend, created_at " . $query .
        " ORDER BY membership_points DESC LIMIT :offset, :limit");
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách tiers để filter
    $tiersStmt = $conn->query("SELECT DISTINCT tier_code, tier_name FROM membership_tiers ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Membership Error: ' . $e->getMessage());
    $members = [];
    $totalPages = 1;
    $tiers = [];
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Thành viên</h1>
        <a href="index.php" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại
        </a>
    </div>

    <!-- Filter và tìm kiếm -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo tên, email"
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="form-group mb-2 mr-2">
                    <select name="tier" class="form-control">
                        <option value="">- Tất cả mức hạng -</option>
                        <option value="NULL" <?= $tierFilter === 'NULL' ? 'selected' : '' ?>>Chưa có hạng</option>
                        <?php foreach ($tiers as $tier): ?>
                            <option value="<?= $tier['tier_code'] ?>"
                                <?= $tierFilter === $tier['tier_code'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tier['tier_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search mr-1"></i> Tìm kiếm
                </button>
                <a href="members.php" class="btn btn-secondary mb-2 ml-2">
                    <i class="fas fa-redo-alt mr-1"></i> Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Danh sách thành viên -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách thành viên</h6>
        </div>
        <div class="card-body">
            <?php if (empty($members)): ?>
                <div class="alert alert-info">
                    Không tìm thấy thành viên nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Hạng thành viên</th>
                                <th>Điểm</th>
                                <th>Chi tiêu năm</th>
                                <th>Ngày hết hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?= $member['ID'] ?></td>
                                    <td><?= htmlspecialchars($member['user_name']) ?></td>
                                    <td><?= htmlspecialchars($member['user_email']) ?></td>
                                    <td>
                                        <?php if ($member['membership_tier']): ?>
                                            <span class="badge badge-pill badge-<?= getMembershipBadgeClass($member['membership_tier']) ?>">
                                                <?= getMembershipTierName($member['membership_tier']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-secondary">Chưa có hạng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($member['membership_points']) ?></td>
                                    <td><?= number_format($member['annual_spend'], 3, ',', '.') ?> đ</td>
                                    <td>
                                        <?= $member['points_expiry_date'] ? date('d/m/Y', strtotime($member['points_expiry_date'])) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <a href="member-detail.php?id=<?= $member['ID'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>">&laquo;</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function để lấy tên hạng từ code
function getMembershipTierName($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'Hạng Đồng';
        case 'silver':
            return 'Hạng Bạc';
        case 'gold':
            return 'Hạng Vàng';
        default:
            return ucfirst($tierCode);
    }
}

// Helper function để lấy class bootstrap cho badge
function getMembershipBadgeClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'warning';
        case 'silver':
            return 'secondary';
        case 'gold':
            return 'warning';
        default:
            return 'info';
    }
}

require "../layouts/footer.php";
?>