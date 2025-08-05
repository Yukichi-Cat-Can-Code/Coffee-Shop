<?php
require "../../config/config.php";
requireAdminLogin();

// Xử lý cập nhật mức hạng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tiers'])) {
    try {
        $conn->beginTransaction();

        // Xử lý các mức hạng hiện tại
        foreach ($_POST['tier'] as $id => $tier) {
            $stmt = $conn->prepare("
                UPDATE membership_tiers
                SET tier_name = :name,
                    min_points = :min_points,
                    max_points = :max_points,
                    discount_percent = :discount,
                    status = :status
                WHERE id = :id
            ");

            $maxPoints = !empty($tier['max_points']) ? $tier['max_points'] : null;
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $tier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $tier['min_points'], PDO::PARAM_INT);
            $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            $stmt->bindParam(':discount', $tier['discount'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $tier['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        // Thêm mới mức hạng nếu có
        if (!empty($_POST['new_tier']['name'])) {
            $stmt = $conn->prepare("
                INSERT INTO membership_tiers 
                (tier_code, tier_name, min_points, max_points, discount_percent, tier_icon, tier_color, status)
                VALUES (:code, :name, :min_points, :max_points, :discount, :icon, :color, :status)
            ");

            $newTier = $_POST['new_tier'];
            $tierCode = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $newTier['name']));
            $maxPoints = !empty($newTier['max_points']) ? $newTier['max_points'] : null;
            $icon = !empty($newTier['icon']) ? $newTier['icon'] : 'fas fa-medal';
            $color = !empty($newTier['color']) ? $newTier['color'] : '#6c757d';

            $stmt->bindParam(':code', $tierCode, PDO::PARAM_STR);
            $stmt->bindParam(':name', $newTier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $newTier['min_points'], PDO::PARAM_INT);
            $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            $stmt->bindParam(':discount', $newTier['discount'], PDO::PARAM_STR);
            $stmt->bindParam(':icon', $icon, PDO::PARAM_STR);
            $stmt->bindParam(':color', $color, PDO::PARAM_STR);
            $stmt->bindParam(':status', $newTier['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        $conn->commit();
        $successMessage = "Cập nhật mức hạng thành công!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $errorMessage = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý xóa mức hạng
if (isset($_POST['delete_tier'])) {
    try {
        $tierId = (int)$_POST['delete_tier'];

        // Kiểm tra xem có thành viên đang sử dụng mức hạng này không
        $checkStmt = $conn->prepare("
            SELECT tier_code FROM membership_tiers WHERE id = :id
        ");
        $checkStmt->bindParam(':id', $tierId, PDO::PARAM_INT);
        $checkStmt->execute();
        $tierCode = $checkStmt->fetch(PDO::FETCH_ASSOC)['tier_code'];

        $countStmt = $conn->prepare("
            SELECT COUNT(*) as count FROM users WHERE membership_tier = :tier_code
        ");
        $countStmt->bindParam(':tier_code', $tierCode, PDO::PARAM_STR);
        $countStmt->execute();
        $memberCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($memberCount > 0) {
            $errorMessage = "Không thể xóa mức hạng này vì có {$memberCount} thành viên đang sử dụng!";
        } else {
            $deleteStmt = $conn->prepare("DELETE FROM membership_tiers WHERE id = :id");
            $deleteStmt->bindParam(':id', $tierId, PDO::PARAM_INT);
            $deleteStmt->execute();
            $successMessage = "Đã xóa mức hạng thành công!";
        }
    } catch (PDOException $e) {
        $errorMessage = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách các mức hạng
try {
    $stmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
    $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiers = [];
    $errorMessage = "Lỗi khi tải dữ liệu: " . $e->getMessage();
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cài đặt mức hạng thành viên</h1>
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
            <h6 class="m-0 font-weight-bold text-primary">Cấu hình mức hạng</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tierTable">
                        <thead>
                            <tr>
                                <th width="20%">Tên mức hạng</th>
                                <th width="15%">Điểm tối thiểu</th>
                                <th width="15%">Điểm tối đa</th>
                                <th width="15%">Giảm giá (%)</th>
                                <th width="15%">Trạng thái</th>
                                <th width="20%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiers as $tier): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="<?= $tier['tier_icon'] ?>" style="color: <?= $tier['tier_color'] ?>; margin-right: 8px;"></i>
                                            <input type="text" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][name]"
                                                value="<?= htmlspecialchars($tier['tier_name']) ?>"
                                                required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="tier[<?= $tier['id'] ?>][min_points]"
                                            value="<?= $tier['min_points'] ?>"
                                            min="0" step="1" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="tier[<?= $tier['id'] ?>][max_points]"
                                            value="<?= $tier['max_points'] ?>"
                                            min="0" step="1"
                                            placeholder="Không giới hạn">
                                        <small class="form-text text-muted">Để trống = không giới hạn</small>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="tier[<?= $tier['id'] ?>][discount]"
                                            value="<?= $tier['discount_percent'] ?>"
                                            min="0" max="100" step="0.1" required>
                                    </td>
                                    <td>
                                        <select class="form-control" name="tier[<?= $tier['id'] ?>][status]">
                                            <option value="active" <?= $tier['status'] === 'active' ? 'selected' : '' ?>>
                                                Hoạt động
                                            </option>
                                            <option value="inactive" <?= $tier['status'] === 'inactive' ? 'selected' : '' ?>>
                                                Không hoạt động
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-tier-btn"
                                            data-toggle="modal" data-target="#deleteTierModal"
                                            data-tier-id="<?= $tier['id'] ?>"
                                            data-tier-name="<?= htmlspecialchars($tier['tier_name']) ?>">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Thêm mức hạng mới -->
                            <tr class="table-light">
                                <td>
                                    <input type="text" class="form-control"
                                        name="new_tier[name]" placeholder="Tên mức hạng mới">
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        name="new_tier[min_points]" min="0" step="1" placeholder="Điểm tối thiểu">
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        name="new_tier[max_points]" min="0" step="1" placeholder="Điểm tối đa">
                                    <small class="form-text text-muted">Để trống = không giới hạn</small>
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        name="new_tier[discount]" min="0" max="100" step="0.1" placeholder="% giảm giá">
                                </td>
                                <td>
                                    <select class="form-control" name="new_tier[status]">
                                        <option value="active">Hoạt động</option>
                                        <option value="inactive">Không hoạt động</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge badge-info">Mức hạng mới</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Lưu ý: Mỗi mức hạng cần có điểm tối thiểu khác nhau. Khi có sự chồng chéo điểm, hệ thống sẽ ưu tiên mức hạng có điểm tối thiểu cao nhất.
                </div>

                <button type="submit" name="save_tiers" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xác nhận xóa mức hạng -->
<div class="modal fade" id="deleteTierModal" tabindex="-1" role="dialog" aria-labelledby="deleteTierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTierModalLabel">Xác nhận xóa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa mức hạng <span id="tierNameToDelete" class="font-weight-bold"></span>?
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="delete_tier" id="tierIdToDelete">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Thiết lập dữ liệu cho modal xóa
    document.addEventListener('DOMContentLoaded', function() {
        const deleteTierBtns = document.querySelectorAll('.delete-tier-btn');
        deleteTierBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tierId = this.getAttribute('data-tier-id');
                const tierName = this.getAttribute('data-tier-name');

                document.getElementById('tierIdToDelete').value = tierId;
                document.getElementById('tierNameToDelete').textContent = tierName;
            });
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>