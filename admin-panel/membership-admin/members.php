<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy các tham số tìm kiếm và phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(5, min(50, (int)$_GET['limit'])) : 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tierFilter = isset($_GET['tier']) ? trim($_GET['tier']) : '';
$sortField = isset($_GET['sort']) ? trim($_GET['sort']) : 'membership_points';
$sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc' ? 'ASC' : 'DESC';

// Danh sách các trường hợp lệ để sắp xếp
$validSortFields = ['ID', 'user_name', 'membership_points', 'membership_tier', 'created_at', 'last_order_date'];
if (!in_array($sortField, $validSortFields)) {
    $sortField = 'membership_points';
}

try {
    // Xây dựng truy vấn cơ sở
    $query = "FROM users WHERE 1=1";
    $params = [];

    // Thêm điều kiện tìm kiếm
    if (!empty($search)) {
        $query .= " AND (user_name LIKE :search OR user_email LIKE :search OR user_phone LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Lọc theo tier
    if ($tierFilter === 'NULL' || $tierFilter === 'none') {
        $query .= " AND (membership_tier IS NULL OR membership_tier = 'none')";
    } elseif (!empty($tierFilter)) {
        $query .= " AND membership_tier = :tier";
        $params[':tier'] = $tierFilter;
    }

    // Đếm tổng số bản ghi
    $countStmt = $conn->prepare("SELECT COUNT(*) as total " . $query);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Truy vấn dữ liệu thành viên với sắp xếp và phân trang
    $stmt = $conn->prepare("SELECT ID, user_name, user_email, user_phone, 
                           membership_tier, membership_points, points_reset_date,
                           tier_updated_at, last_order_date, created_at " . $query .
        " ORDER BY " . $sortField . " " . $sortDirection .
        " LIMIT :offset, :limit");

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách các tier để hiển thị trong bộ lọc
    $tiersStmt = $conn->query("SELECT tier_key, tier_name, tier_color FROM membership_tiers 
                              WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thống kê về thành viên theo từng hạng
    $statsStmt = $conn->query("SELECT 
                                COALESCE(membership_tier, 'none') AS tier, 
                                COUNT(*) AS member_count,
                                SUM(membership_points) AS total_points
                              FROM users 
                              GROUP BY membership_tier
                              ORDER BY 
                                CASE membership_tier 
                                  WHEN 'gold' THEN 1
                                  WHEN 'silver' THEN 2
                                  WHEN 'bronze' THEN 3
                                  ELSE 4
                                END");
    $tierStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Membership Error: ' . $e->getMessage());
    $errorMessage = "Đã xảy ra lỗi khi truy vấn dữ liệu. Vui lòng thử lại sau.";
    $members = [];
    $totalPages = 1;
    $tiers = [];
    $tierStats = [];
}

// // Helper function để định dạng tiền tệ
// function formatCurrency($amount)
// {
//     return number_format($amount, 0, ',', '.') . ' đ';
// }

// Helper function để lấy tên hạng từ code
function getMembershipTierName($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_name'];
        }
    }

    if ($tierCode === 'none' || empty($tierCode)) {
        return 'Chưa có hạng';
    }

    return ucfirst($tierCode);
}

// Helper function để lấy màu badge cho từng hạng
function getMembershipBadgeClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'bronze';
        case 'silver':
            return 'silver';
        case 'gold':
            return 'gold';
        default:
            return 'light';
    }
}

// Helper function để lấy màu cho từng hạng
function getTierColor($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_color'];
        }
    }

    return '#6c757d';
}

require "../layouts/header.php";

echo '<style>
    .badge-gold {
        background-color: #FFD700;
        color: #000;
    }
    .badge-silver {
        background-color: #C0C0C0;
        color: #000;
    }
    .badge-bronze {
        background-color: #CD7F32;
        color: #fff;
    }
    .tier-icon {
        color: inherit !important;
    }
</style>';
?>

<div class="container-fluid py-4">
    <!-- Tiêu đề và nút quay lại -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Thành viên</h1>
        <div>
            <a href="export-members.php" class="btn btn-sm btn-success mr-2">
                <i class="fas fa-file-excel mr-1"></i> Xuất Excel
            </a>
            <a href="index.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </a>
        </div>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
        </div>
    <?php endif; ?>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <?php foreach ($tierStats as $stat): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-<?= getMembershipBadgeClass($stat['tier']) ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1"
                                    style="color: <?= getTierColor($stat['tier'], $tiers) ?>">
                                    <?= getMembershipTierName($stat['tier'], $tiers) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stat['member_count'] ?> thành viên</div>
                                <!-- <div class="text-xs text-gray-600"><?= number_format($stat['total_points']) ?> điểm tích lũy</div> -->
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-medal fa-2x text-gray-300"
                                    style="color: <?= getTierColor($stat['tier'], $tiers) ?> !important;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="mb-0">
                <div class="form-row align-items-center">
                    <div class="col-md-4 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" name="search"
                                placeholder="Tìm theo tên, email, SĐT" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <select name="tier" class="form-control">
                            <option value="">- Tất cả mức hạng -</option>
                            <option value="none" <?= $tierFilter === 'none' ? 'selected' : '' ?>>Chưa có hạng</option>
                            <?php foreach ($tiers as $tier): ?>
                                <option value="<?= $tier['tier_key'] ?>"
                                    <?= $tierFilter === $tier['tier_key'] ? 'selected' : '' ?>
                                    style="color: <?= $tier['tier_color'] ?>; font-weight: bold;">
                                    <?= htmlspecialchars($tier['tier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <select name="limit" class="form-control">
                            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15 mục</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 mục</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 mục</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <a href="members.php" class="btn btn-secondary">
                            <i class="fas fa-redo-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách thành viên -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách thành viên (<?= $totalRecords ?> kết quả)</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                    id="dropdownSortMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-sort mr-1"></i> Sắp xếp
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownSortMenu">
                    <a class="dropdown-item <?= $sortField == 'membership_points' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=membership_points&dir=desc">
                        <i class="fas fa-sort-amount-down mr-1"></i> Điểm cao nhất
                    </a>
                    <a class="dropdown-item <?= $sortField == 'membership_points' && $sortDirection == 'ASC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=membership_points&dir=asc">
                        <i class="fas fa-sort-amount-up mr-1"></i> Điểm thấp nhất
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item <?= $sortField == 'last_order_date' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=last_order_date&dir=desc">
                        <i class="fas fa-shopping-cart mr-1"></i> Đơn hàng gần đây
                    </a>
                    <a class="dropdown-item <?= $sortField == 'created_at' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=created_at&dir=desc">
                        <i class="fas fa-user-plus mr-1"></i> Mới đăng ký
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($members)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Không tìm thấy thành viên nào phù hợp với tiêu chí tìm kiếm.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px">ID</th>
                                <th>Tên thành viên</th>
                                <th>Liên hệ</th>
                                <th>Hạng thành viên</th>
                                <th>Điểm</th>
                                <th>Ngày hết hạn</th>
                                <th style="width: 100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $member['ID'] ?></td>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($member['user_name']) ?></div>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            Tham gia: <?= date('d/m/Y', strtotime($member['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div><i class="far fa-envelope mr-1"></i> <?= htmlspecialchars($member['user_email']) ?></div>
                                        <?php if (!empty($member['user_phone'])): ?>
                                            <div><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($member['user_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($member['membership_tier']) && $member['membership_tier'] !== 'none'): ?>
                                            <?php
                                            $badgeClass = getMembershipBadgeClass($member['membership_tier']);
                                            $tierColor = getTierColor($member['membership_tier'], $tiers);
                                            ?>
                                            <span class="badge badge-pill badge-<?= $badgeClass ?>"
                                                style="font-size: 90%; padding: 5px 10px; <?= $badgeClass == 'light' ? "background-color: $tierColor;" : "" ?>">
                                                <i class="fas fa-medal mr-1 tier-icon"></i>
                                                <?= getMembershipTierName($member['membership_tier'], $tiers) ?>
                                            </span>
                                            <?php if ($member['tier_updated_at']): ?>
                                                <div class="text-muted small mt-1">
                                                    Cập nhật: <?= date('d/m/Y', strtotime($member['tier_updated_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-light text-muted">
                                                Chưa có hạng
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold"><?= number_format($member['membership_points']) ?></span>
                                        <?php if ($member['last_order_date']): ?>
                                            <div class="text-muted small">
                                                <i class="fas fa-shopping-cart mr-1"></i>
                                                <?= date('d/m/Y', strtotime($member['last_order_date'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($member['points_reset_date']): ?>
                                            <?php
                                            $resetDate = new DateTime($member['points_reset_date']);
                                            $today = new DateTime();
                                            $diff = $today->diff($resetDate);
                                            $daysLeft = $diff->days;
                                            $isPast = $today > $resetDate;
                                            ?>

                                            <?php if ($isPast): ?>
                                                <span class="badge badge-danger">Đã hết hạn</span>
                                            <?php elseif ($daysLeft <= 30): ?>
                                                <span class="badge badge-warning">
                                                    <?= $daysLeft ?> ngày
                                                </span>
                                            <?php else: ?>
                                                <?= date('d/m/Y', strtotime($member['points_reset_date'])) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="member-detail.php?id=<?= $member['ID'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary edit-points-btn"
                                            data-toggle="modal" data-target="#editPointsModal"
                                            data-id="<?= $member['ID'] ?>"
                                            data-name="<?= htmlspecialchars($member['user_name']) ?>"
                                            data-points="<?= $member['membership_points'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>

                            <?php
                            $startPage = max(1, min($page - 2, $totalPages - 4));
                            $endPage = min($totalPages, $startPage + 4);
                            ?>

                            <?php if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                        <?= $totalPages ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa điểm -->
<div class="modal fade" id="editPointsModal" tabindex="-1" role="dialog" aria-labelledby="editPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="adjust-points.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPointsModalLabel">Điều chỉnh điểm thành viên</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserID">
                    <div class="form-group">
                        <label>Thành viên</label>
                        <input type="text" class="form-control" id="editUserName" readonly>
                    </div>
                    <div class="form-group">
                        <label>Điểm hiện tại</label>
                        <input type="text" class="form-control" id="currentPoints" readonly>
                    </div>
                    <div class="form-group">
                        <label for="pointAdjustment">Điều chỉnh điểm</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <select class="form-control" name="adjustment_type" id="adjustmentType">
                                    <option value="add">+</option>
                                    <option value="subtract">-</option>
                                    <option value="set">Đặt lại</option>
                                </select>
                            </div>
                            <input type="number" class="form-control" id="pointAdjustment" name="points" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adjustmentReason">Lý do</label>
                        <select class="form-control" id="adjustmentReason" name="reason">
                            <option value="manual_adjust">Điều chỉnh thủ công</option>
                            <option value="customer_service">Dịch vụ khách hàng</option>
                            <option value="promotion">Khuyến mãi</option>
                            <option value="correction">Sửa lỗi</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adjustmentNote">Ghi chú</label>
                        <textarea class="form-control" id="adjustmentNote" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Xử lý modal chỉnh sửa điểm
        $('.edit-points-btn').click(function() {
            var userId = $(this).data('id');
            var userName = $(this).data('name');
            var points = $(this).data('points');

            $('#editUserID').val(userId);
            $('#editUserName').val(userName);
            $('#currentPoints').val(points.toLocaleString('vi-VN'));
            $('#pointAdjustment').val('');
        });

        // Làm nổi bật các hàng khi hover
        $('tbody tr').hover(
            function() {
                $(this).addClass('bg-light');
            },
            function() {
                $(this).removeClass('bg-light');
            }
        );
    });
</script>

<?php require "../layouts/footer.php"; ?>