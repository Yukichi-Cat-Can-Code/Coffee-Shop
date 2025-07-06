<?php
require "../../config/config.php";
requireAdminLogin();

require "../layouts/header.php";

// Xử lý lưu cài đặt (giả lập, chỉ lưu vào session)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['theme'] = $_POST['theme'] ?? 'light';
    $_SESSION['notify'] = isset($_POST['notify']) ? 1 : 0;
    $success = "Đã lưu cài đặt thành công!";
}

// Lấy giá trị hiện tại
$theme = $_SESSION['theme'] ?? 'light';
$notify = $_SESSION['notify'] ?? 0;
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-coffee text-white">
                    <h4 class="mb-0"><i class="fas fa-cog me-2"></i> Cài đặt cá nhân</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Giao diện</label>
                            <select name="theme" class="form-select">
                                <option value="light" <?= $theme == 'light' ? 'selected' : '' ?>>Sáng</option>
                                <option value="dark" <?= $theme == 'dark' ? 'selected' : '' ?>>Tối</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="notify" id="notify" value="1" <?= $notify ? 'checked' : '' ?>>
                            <label class="form-check-label" for="notify">
                                Nhận thông báo hệ thống
                            </label>
                        </div>
                        <button type="submit" class="btn btn-coffee"><i class="fas fa-save me-1"></i> Lưu cài đặt</button>
                    </form>
                </div>
            </div>