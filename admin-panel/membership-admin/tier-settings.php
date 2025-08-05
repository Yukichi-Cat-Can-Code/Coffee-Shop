<?php
require "../../config/config.php";
requireAdminLogin();

// Xử lý cập nhật mức hạng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tiers'])) {
    try {
        $conn->beginTransaction();

        // Xử lý các mức hạng hiện tại
        foreach ($_POST['tier'] as $id => $tier) {
            // Kiểm tra cấu trúc bảng trước khi update
            $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('max_points', $checkColumns)) {
                // Nếu có cột max_points
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
                $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            } else {
                // Nếu không có cột max_points
                $stmt = $conn->prepare("
                    UPDATE membership_tiers
                    SET tier_name = :name,
                        min_points = :min_points,
                        discount_percent = :discount,
                        status = :status
                    WHERE id = :id
                ");
            }

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $tier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $tier['min_points'], PDO::PARAM_INT);
            $stmt->bindParam(':discount', $tier['discount'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $tier['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        // Thêm mới mức hạng nếu có
        if (!empty($_POST['new_tier']['name'])) {
            $newTier = $_POST['new_tier'];
            $tierKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $newTier['name']));
            $icon = !empty($newTier['icon']) ? $newTier['icon'] : 'fas fa-medal';
            $color = !empty($newTier['color']) ? $newTier['color'] : '#6c757d';

            // Kiểm tra xem có cột tier_code hay tier_key
            $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('max_points', $checkColumns)) {
                // Có cột max_points
                if (in_array('tier_code', $checkColumns)) {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_code, tier_name, min_points, max_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :max_points, :discount, :icon, :color, :status)
                    ");
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_key, tier_name, min_points, max_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :max_points, :discount, :icon, :color, :status)
                    ");
                }

                $maxPoints = !empty($newTier['max_points']) ? $newTier['max_points'] : null;
                $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            } else {
                // Không có cột max_points
                if (in_array('tier_code', $checkColumns)) {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_code, tier_name, min_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :discount, :icon, :color, :status)
                    ");
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_key, tier_name, min_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :discount, :icon, :color, :status)
                    ");
                }
            }

            $stmt->bindParam(':code', $tierKey, PDO::PARAM_STR);
            $stmt->bindParam(':name', $newTier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $newTier['min_points'], PDO::PARAM_INT);
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

        // Kiểm tra cấu trúc bảng để xác định tên cột
        $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);
        $tierCodeColumn = in_array('tier_code', $checkColumns) ? 'tier_code' : 'tier_key';

        // Kiểm tra xem có thành viên đang sử dụng mức hạng này không
        $checkStmt = $conn->prepare("
            SELECT {$tierCodeColumn} FROM membership_tiers WHERE id = :id
        ");
        $checkStmt->bindParam(':id', $tierId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $tierCode = $checkStmt->fetch(PDO::FETCH_ASSOC)[$tierCodeColumn];

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
        } else {
            $errorMessage = "Không tìm thấy mức hạng để xóa!";
        }
    } catch (PDOException $e) {
        $errorMessage = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách các mức hạng
try {
    $stmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
    $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kiểm tra cấu trúc bảng để biết có cột max_points không
    $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);
    $hasMaxPoints = in_array('max_points', $checkColumns);
} catch (PDOException $e) {
    $tiers = [];
    $hasMaxPoints = false;
    $errorMessage = "Lỗi khi tải dữ liệu: " . $e->getMessage();
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-medal mr-2"></i>Cài đặt mức hạng thành viên
        </h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Quay lại Dashboard
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i><?= $successMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i><?= $errorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cog mr-2"></i>Cấu hình mức hạng
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tierTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%">Tên mức hạng</th>
                                <th width="15%">Điểm tối thiểu</th>
                                <?php if ($hasMaxPoints): ?>
                                    <th width="15%">Điểm tối đa</th>
                                <?php endif; ?>
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
                                            <?php if (isset($tier['tier_icon'])): ?>
                                                <i class="<?= htmlspecialchars($tier['tier_icon']) ?>"
                                                    style="color: <?= htmlspecialchars($tier['tier_color'] ?? '#6c757d') ?>; margin-right: 8px;"></i>
                                            <?php endif; ?>
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
                                    <?php if ($hasMaxPoints): ?>
                                        <td>
                                            <input type="number" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][max_points]"
                                                value="<?= $tier['max_points'] ?? '' ?>"
                                                min="0" step="1"
                                                placeholder="Không giới hạn">
                                            <small class="form-text text-muted">Để trống = không giới hạn</small>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][discount]"
                                                value="<?= $tier['discount_percent'] ?>"
                                                min="0" max="100" step="0.1" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
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
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plus-circle text-success mr-2"></i>
                                        <input type="text" class="form-control"
                                            name="new_tier[name]" placeholder="Tên mức hạng mới">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        name="new_tier[min_points]" min="0" step="1" placeholder="Điểm tối thiểu">
                                </td>
                                <?php if ($hasMaxPoints): ?>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="new_tier[max_points]" min="0" step="1" placeholder="Điểm tối đa">
                                        <small class="form-text text-muted">Để trống = không giới hạn</small>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            name="new_tier[discount]" min="0" max="100" step="0.1" placeholder="% giảm giá">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-control" name="new_tier[status]">
                                        <option value="active">Hoạt động</option>
                                        <option value="inactive">Không hoạt động</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-plus mr-1"></i>Mức hạng mới
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Lưu ý:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Mỗi mức hạng cần có điểm tối thiểu khác nhau</li>
                        <li>Khi có sự chồng chéo điểm, hệ thống sẽ ưu tiên mức hạng có điểm tối thiểu cao nhất</li>
                        <li>Điểm tối đa có thể để trống để biểu thị "không giới hạn"</li>
                        <li>Phần trăm giảm giá từ 0% đến 100%</li>
                    </ul>
                </div>

                <div class="text-center">
                    <button type="submit" name="save_tiers" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                    <a href="index.php" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-2"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Thêm ghi chú hướng dẫn -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-question-circle mr-2"></i>Hướng dẫn sử dụng
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Cách thiết lập mức hạng:</h6>
                    <ol>
                        <li>Điền tên mức hạng (VD: Đồng, Bạc, Vàng)</li>
                        <li>Đặt điểm tối thiểu để đạt mức hạng đó</li>
                        <li>Thiết lập phần trăm giảm giá cho mức hạng</li>
                        <li>Chọn trạng thái hoạt động/không hoạt động</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Ví dụ mức hạng:</h6>
                    <ul>
                        <li><strong>Đồng:</strong> 0 - 199 điểm (Giảm 5%)</li>
                        <li><strong>Bạc:</strong> 200 - 499 điểm (Giảm 10%)</li>
                        <li><strong>Vàng:</strong> 500+ điểm (Giảm 15%)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận xóa mức hạng -->
<div class="modal fade" id="deleteTierModal" tabindex="-1" role="dialog" aria-labelledby="deleteTierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteTierModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Xác nhận xóa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa mức hạng <span id="tierNameToDelete" class="font-weight-bold text-danger"></span>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning mr-1"></i>
                    <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                </div>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="delete_tier" id="tierIdToDelete">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thiết lập dữ liệu cho modal xóa
        const deleteTierBtns = document.querySelectorAll('.delete-tier-btn');
        deleteTierBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tierId = this.getAttribute('data-tier-id');
                const tierName = this.getAttribute('data-tier-name');

                document.getElementById('tierIdToDelete').value = tierId;
                document.getElementById('tierNameToDelete').textContent = tierName;
            });
        });

        // Validation cho form
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const minPointInputs = document.querySelectorAll('input[name*="[min_points]"]');
            const minPoints = [];

            minPointInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    minPoints.push(parseInt(input.value));
                }
            });

            // Kiểm tra trùng lặp điểm tối thiểu
            const duplicates = minPoints.filter((item, index) => minPoints.indexOf(item) !== index);
            if (duplicates.length > 0) {
                e.preventDefault();
                alert('Có điểm tối thiểu bị trùng lặp. Vui lòng kiểm tra lại!');
                return false;
            }
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>