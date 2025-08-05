<?php

require "../../config/config.php";
requireAdminLogin();

// Helper functions - đưa lên đầu file để có thể sử dụng ở mọi nơi
function getMembershipTierName($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_name'];
        }
    }

    switch ($tierCode) {
        case 'bronze':
            return 'Hạng Đồng';
        case 'silver':
            return 'Hạng Bạc';
        case 'gold':
            return 'Hạng Vàng';
        default:
            return 'Chưa có hạng';
    }
}

function getMembershipBadgeClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'bronze';
        case 'silver':
            return 'secondary';
        case 'gold':
            return 'gold';
        default:
            return 'light';
    }
}

function getMembershipCardClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'bg-gradient-warning';
        case 'silver':
            return 'bg-gradient-secondary';
        case 'gold':
            return 'bg-gradient-primary';
        default:
            return 'bg-gradient-dark';
    }
}

function getMembershipCardIcon($tierCode)
{
    switch ($tierCode) {
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

function getActionBadgeClass($action)
{
    switch ($action) {
        case 'earned':
            return 'success';
        case 'used':
            return 'info';
        case 'expired':
            return 'danger';
        case 'adjusted':
            return 'warning';
        default:
            return 'secondary';
    }
}

function getActionName($action)
{
    switch ($action) {
        case 'earned':
            return 'Tích lũy';
        case 'used':
            return 'Sử dụng';
        case 'expired':
            return 'Hết hạn';
        case 'adjusted':
            return 'Điều chỉnh';
        default:
            return ucfirst($action);
    }
}

function getStatusBadgeClass($status)
{
    switch (strtolower($status)) {
        case 'completed':
        case 'đã hoàn thành':
        case 'đã thanh toán':
            return 'success';
        case 'pending':
        case 'đang chờ':
            return 'warning';
        case 'cancelled':
        case 'đã hủy':
            return 'danger';
        default:
            return 'info';
    }
}

// Lấy ID thành viên từ URL
$memberId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($memberId <= 0) {
    header("Location: members.php?error=invalid_id");
    exit;
}

// Xử lý cập nhật điểm và hạng thủ công
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'adjust_points') {
            $points = (int)$_POST['points'];
            $notes = trim($_POST['notes']);

            // Lấy điểm hiện tại
            $currentPointsStmt = $conn->prepare("SELECT membership_points FROM users WHERE ID = :id");
            $currentPointsStmt->bindParam(':id', $memberId, PDO::PARAM_INT);
            $currentPointsStmt->execute();

            if ($currentPointsStmt->rowCount() === 0) {
                throw new Exception("Không tìm thấy thành viên");
            }

            $currentPoints = $currentPointsStmt->fetch(PDO::FETCH_ASSOC)['membership_points'];

            // Cập nhật điểm
            $newPoints = $currentPoints + $points;
            if ($newPoints < 0) $newPoints = 0;

            $conn->beginTransaction();

            // Cập nhật điểm cho user
            $updateStmt = $conn->prepare("UPDATE users SET membership_points = :points WHERE ID = :id");
            $updateStmt->bindParam(':points', $newPoints, PDO::PARAM_INT);
            $updateStmt->bindParam(':id', $memberId, PDO::PARAM_INT);
            $updateStmt->execute();

            // Thêm vào lịch sử điểm
            $historyStmt = $conn->prepare("
                INSERT INTO membership_point_history (user_id, points, action, notes) 
                VALUES (:user_id, :points, :action, :notes)
            ");
            $actionType = $points >= 0 ? 'earned' : 'used';
            $historyStmt->bindParam(':user_id', $memberId, PDO::PARAM_INT);
            $historyStmt->bindParam(':points', $points, PDO::PARAM_INT);
            $historyStmt->bindParam(':action', $actionType, PDO::PARAM_STR);
            $historyStmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $historyStmt->execute();

            // Tự động cập nhật hạng dựa trên điểm mới
            updateMembershipTier($conn, $memberId, $newPoints);

            $conn->commit();
            $successMessage = "Đã cập nhật điểm thành công.";
        } elseif ($action === 'change_tier') {
            $newTier = $_POST['tier'];
            $notes = "Thay đổi hạng thành viên thủ công sang: " . $newTier;

            $updateStmt = $conn->prepare("
                UPDATE users 
                SET membership_tier = :tier, last_tier_update = CURRENT_DATE 
                WHERE ID = :id
            ");
            $updateStmt->bindParam(':tier', $newTier, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $memberId, PDO::PARAM_INT);
            $updateStmt->execute();

            $successMessage = "Đã cập nhật hạng thành viên thành công.";
        }
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        $errorMessage = "Lỗi: " . $e->getMessage();
    }
}

// Cập nhật hạng thành viên tự động dựa trên điểm
function updateMembershipTier($conn, $userId, $points)
{
    // Lấy mức hạng dựa trên điểm
    $stmt = $conn->prepare("
        SELECT tier_key
        FROM membership_tiers 
        WHERE status = 'active' AND min_points <= :points
        ORDER BY min_points DESC
        LIMIT 1
    ");
    $stmt->bindParam(':points', $points, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $tier = $stmt->fetch(PDO::FETCH_ASSOC)['tier_key']; // Sửa lỗi tier_code thành tier_key

        // Cập nhật hạng mới
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET membership_tier = :tier, last_tier_update = CURRENT_DATE
            WHERE ID = :id
        ");
        $updateStmt->bindParam(':tier', $tier, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $updateStmt->execute();
    }
}

// Lấy thông tin thành viên
try {
    $stmt = $conn->prepare("
        SELECT u.*, 
               COUNT(DISTINCT o.ID) as total_online_orders,
               COUNT(DISTINCT p.order_id) as total_pos_orders, 
               COALESCE(SUM(
                   CASE
                       WHEN o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR) THEN o.payable_total_cost
                       ELSE 0 
                   END
               ), 0) +
               COALESCE(SUM(
                   CASE 
                       WHEN p.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR) THEN p.final_amount 
                       ELSE 0
                   END
               ), 0) as annual_spend
        FROM users u
        LEFT JOIN orders o ON u.ID = o.user_id
        LEFT JOIN pos_orders p ON u.ID = p.customer_id
        WHERE u.ID = :id
        GROUP BY u.ID
    ");
    $stmt->bindParam(':id', $memberId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        header("Location: members.php?error=not_found");
        exit;
    }

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy lịch sử điểm
    $historyStmt = $conn->prepare("
        SELECT * FROM membership_point_history
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT 30
    ");
    $historyStmt->bindParam(':user_id', $memberId, PDO::PARAM_INT);
    $historyStmt->execute();
    $pointHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách tiers
    $tiersStmt = $conn->query("SELECT * FROM membership_tiers WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin đơn hàng gần đây
    $ordersStmt = $conn->prepare("
        (SELECT 'online' as type, ID as order_id, payable_total_cost as amount, created_at, status
         FROM orders
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT 5)
        UNION ALL
        (SELECT 'pos' as type, order_id, final_amount as amount, created_at, order_status as status
        FROM pos_orders
        WHERE customer_id = :customer_id
        ORDER BY created_at DESC
        LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $ordersStmt->bindParam(':user_id', $memberId, PDO::PARAM_INT);
    $ordersStmt->bindParam(':customer_id', $memberId, PDO::PARAM_INT);
    $ordersStmt->execute();
    $recentOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Membership Error: ' . $e->getMessage());
    header("Location: members.php?error=db_error");
    exit;
}

require "../layouts/header.php";
?>
<style>
    .badge-bronze {
        background-color: #CD7F32;
        color: #fff;
    }

    .badge-gold {
        background-color: #FFD700;
        color: #000;
    }

    .membership-card .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .bg-gradient-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(87deg, #fb6340 0, #fbb140 100%);
    }

    .bg-gradient-secondary {
        background: linear-gradient(87deg, #8898aa 0, #888aaa 100%);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết thành viên</h1>
        <a href="members.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> <?= $successMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Thông tin thành viên -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin thành viên</h6>
                    <span class="badge badge-pill badge-<?= getMembershipBadgeClass($member['membership_tier'] ?? '') ?>"
                        style="padding: 8px 12px; font-size: 90%;">
                        <?= getMembershipTierName($member['membership_tier'] ?? '', $tiers) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-5x text-gray-300 mb-3"></i>
                        <h5 class="font-weight-bold"><?= htmlspecialchars($member['user_name']) ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($member['user_email']) ?></p>
                        <?php if (!empty($member['user_phone'])): ?>
                            <p class="mb-0"><?= htmlspecialchars($member['user_phone']) ?></p>
                        <?php endif; ?>
                        <p class="text-muted small">
                            Tham gia: <?= date('d/m/Y', strtotime($member['created_at'])) ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Tình trạng thành viên:</h6>
                        <div class="d-flex justify-content-between">
                            <span>Điểm tích lũy:</span>
                            <span class="font-weight-bold"><?= number_format($member['membership_points']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Chi tiêu trong năm:</span>
                            <span class="font-weight-bold">
                                <?= isset($member['annual_spend']) ? number_format($member['annual_spend'], 0, ',', '.') : '0' ?> đ
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ngày hết hạn điểm:</span>
                            <span class="font-weight-bold">
                                <?= !empty($member['points_reset_date']) ? date('d/m/Y', strtotime($member['points_reset_date'])) : 'N/A' ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cập nhật hạng gần nhất:</span>
                            <span class="font-weight-bold">
                                <?= !empty($member['last_tier_update']) ? date('d/m/Y', strtotime($member['last_tier_update'])) : 'N/A' ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Đơn hàng:</h6>
                        <div class="d-flex justify-content-between">
                            <span>Đơn hàng online:</span>
                            <span class="font-weight-bold"><?= $member['total_online_orders'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Đơn hàng POS:</span>
                            <span class="font-weight-bold"><?= $member['total_pos_orders'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tổng đơn hàng:</span>
                            <span class="font-weight-bold"><?= $member['total_online_orders'] + $member['total_pos_orders'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thẻ thành viên -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thẻ thành viên</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="membership-card mb-3 flex-grow-1">
                        <div class="card text-white 
                            <?= getMembershipCardClass($member['membership_tier'] ?? '') ?> shadow">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Coffee Shop</h5>
                                    <i class="fas <?= getMembershipCardIcon($member['membership_tier'] ?? '') ?> fa-2x"></i>
                                </div>
                                <p class="text-uppercase mt-4 mb-0">THÀNH VIÊN</p>
                                <h4 class="font-weight-bold"><?= htmlspecialchars($member['user_name']) ?></h4>
                                <div class="d-flex justify-content-between mt-3">
                                    <div>
                                        <small class="d-block">MEMBERSHIP ID</small>
                                        <span>#<?= str_pad($member['ID'], 6, '0', STR_PAD_LEFT) ?></span>
                                    </div>
                                    <div class="text-right">
                                        <small class="d-block">HẠNG THÀNH VIÊN</small>
                                        <span class="text-uppercase">
                                            <?= getMembershipTierName($member['membership_tier'] ?? '', $tiers) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thay đổi hạng thành viên -->
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="m-0 font-weight-bold text-primary">Thay đổi hạng thành viên</h6>
                        </div>
                        <div class="card-body py-3">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="change_tier">
                                <div class="form-group">
                                    <label for="tier">Chọn hạng mới:</label>
                                    <select name="tier" id="tier" class="form-control">
                                        <option value="">-- Không có hạng --</option>
                                        <?php foreach ($tiers as $tier): ?>
                                            <option value="<?= $tier['tier_key'] ?>"
                                                <?= ($member['membership_tier'] == $tier['tier_key']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tier['tier_name']) ?>
                                                (<?= number_format($tier['discount_percent'], 1) ?>% giảm)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Cập nhật hạng
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Điều chỉnh điểm -->
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Điều chỉnh điểm thành viên</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="adjust_points">

                        <div class="form-group">
                            <label for="points">Số điểm điều chỉnh:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <select class="form-control" id="point-action">
                                        <option value="add">Thêm (+)</option>
                                        <option value="subtract">Trừ (-)</option>
                                        <option value="set">Đặt lại</option>
                                    </select>
                                </div>
                                <input type="number" class="form-control" name="points" id="points" required min="0">
                            </div>
                            <small class="form-text text-muted" id="points-help">
                                Thêm điểm vào tài khoản thành viên.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Ghi chú:</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-1"></i> Cập nhật điểm
                        </button>
                    </form>

                    <hr>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Điều chỉnh điểm thành viên sẽ tự động cập nhật hạng thành viên tương ứng.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lịch sử điểm -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lịch sử điểm</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($pointHistory)): ?>
                        <div class="alert alert-info">Chưa có lịch sử điểm nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Điểm</th>
                                        <th>Hành động</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pointHistory as $history): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></td>
                                            <td class="font-weight-bold <?= $history['points'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $history['points'] >= 0 ? '+' : '' ?><?= number_format($history['points']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= getActionBadgeClass($history['action']) ?>">
                                                    <?= getActionName($history['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($history['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Đơn hàng gần đây -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="alert alert-info">Chưa có đơn hàng nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Loại</th>
                                        <th>Tổng tiền</th>
                                        <th>Ngày</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['order_id'] ?></td>
                                            <td>
                                                <?php if ($order['type'] == 'online'): ?>
                                                    <span class="badge badge-info">Online</span>
                                                <?php else: ?>
                                                    <span class="badge badge-primary">POS</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($order['amount'], 0, ',', '.') ?> đ</td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= getStatusBadgeClass($order['status']) ?>">
                                                    <?= htmlspecialchars($order['status']) ?>
                                                </span>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý thay đổi loại điều chỉnh điểm
        const pointActionSelect = document.getElementById('point-action');
        const pointsInput = document.getElementById('points');
        const pointsHelp = document.getElementById('points-help');

        pointActionSelect.addEventListener('change', function() {
            switch (this.value) {
                case 'add':
                    pointsHelp.textContent = 'Thêm điểm vào tài khoản thành viên.';
                    pointsInput.name = 'points';
                    break;
                case 'subtract':
                    pointsHelp.textContent = 'Trừ điểm khỏi tài khoản thành viên.';
                    pointsInput.name = 'points';
                    break;
                case 'set':
                    pointsHelp.textContent = 'Đặt lại tổng điểm của thành viên.';
                    pointsInput.name = 'set_points';
                    break;
            }
        });

        // Submit form handler
        const form = document.querySelector('form[action=""][name="action"][value="adjust_points"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const pointAction = pointActionSelect.value;
                const pointsValue = parseInt(pointsInput.value);

                if (pointAction === 'subtract') {
                    // Chuyển giá trị thành số âm khi chọn trừ điểm
                    pointsInput.value = -Math.abs(pointsValue);
                }

                // Form sẽ submit bình thường
            });
        }
    });
</script>

<?php require "../layouts/footer.php"; ?>