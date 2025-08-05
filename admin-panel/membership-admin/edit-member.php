<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\edit-member.php
require "../../config/config.php";
requireAdminLogin();

// Kiểm tra ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: members.php");
    exit;
}

$memberId = intval($_GET['id']);

try {
    // Lấy thông tin thành viên
    $stmt = $conn->prepare("
        SELECT * FROM users WHERE id = :id LIMIT 1
    ");
    $stmt->bindParam(':id', $memberId);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        throw new Exception("Can not find member with ID: $memberId");
    }

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy lịch sử điểm
    $pointsStmt = $conn->prepare("
        SELECT * FROM membership_points 
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
    $pointsStmt->bindParam(':user_id', $memberId);
    $pointsStmt->execute();
    $pointsHistory = $pointsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý form cập nhật
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
        $tier = $_POST['tier'];
        $points = floatval($_POST['points']);
        $notes = trim($_POST['notes']);

        // Validate
        if ($points < 0) {
            throw new Exception("Points cannot be negative");
        }

        // Cập nhật thông tin thành viên
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET membership_points = :points,
                membership_tier = :tier
            WHERE id = :id
        ");

        $updateStmt->bindParam(':points', $points);
        $updateStmt->bindParam(':tier', $tier);
        $updateStmt->bindParam(':id', $memberId);
        $updateStmt->execute();

        // Thêm vào lịch sử nếu có ghi chú
        if (!empty($notes)) {
            $pointChange = $points - $member['membership_points'];
            $historyStmt = $conn->prepare("
                INSERT INTO membership_points (user_id, points_change, reason, admin_id, created_at)
                VALUES (:user_id, :points_change, :reason, :admin_id, NOW())
            ");

            $historyStmt->bindParam(':user_id', $memberId);
            $historyStmt->bindParam(':points_change', $pointChange);
            $historyStmt->bindParam(':reason', $notes);
            $historyStmt->bindParam(':admin_id', $_SESSION['admin_id']);
            $historyStmt->execute();
        }

        $successMessage = "Update member information successfully!";

        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $memberId);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        // Refresh points history
        $pointsStmt->execute();
        $pointsHistory = $pointsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit member</h1>
        <a href="members.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to list
        </a>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if (isset($member)): ?>
        <div class="row">
            <div class="col-xl-4">
                <!-- Thông tin thành viên -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Member Information</h6>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <div class="mb-3">
                                <label>Full Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($member['username']) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($member['user_email']) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Accumulated Points</label>
                                <input type="number" class="form-control" name="points" value="<?= htmlspecialchars($member['membership_points']) ?>" step="1" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label>Membership Tier</label>
                                <select class="form-control" name="tier">
                                    <option value="none" <?= $member['membership_tier'] == 'none' ? 'selected' : '' ?>>No Tier</option>
                                    <option value="bronze" <?= $member['membership_tier'] == 'bronze' ? 'selected' : '' ?>>Bronze Tier</option>
                                    <option value="silver" <?= $member['membership_tier'] == 'silver' ? 'selected' : '' ?>>Silver Tier</option>
                                    <option value="gold" <?= $member['membership_tier'] == 'gold' ? 'selected' : '' ?>>Gold Tier</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Change Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Enter reason for point/tier change (if any)"></textarea>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="update_member" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <!-- Lịch sử điểm -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Accumulated Points History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Points Change</th>
                                        <th>Reason</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pointsHistory)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">There is no points history available.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pointsHistory as $record): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($record['created_at'])) ?></td>
                                                <td class="<?= $record['points_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= $record['points_change'] > 0 ? '+' : '' ?><?= $record['points_change'] ?>
                                                </td>
                                                <td><?= htmlspecialchars($record['reason']) ?></td>
                                                <td><?= $record['admin_id'] ? 'Admin #' . $record['admin_id'] : 'System' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require "../layouts/footer.php"; ?>