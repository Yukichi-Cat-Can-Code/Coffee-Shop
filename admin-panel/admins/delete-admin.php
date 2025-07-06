<?php
require "../../config/config.php";
requireAdminLogin();

// Kiểm tra nếu gửi đúng phương thức POST và có admin_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    $admin_id = (int)$_POST['admin_id'];

    // Không cho phép xóa chính mình
    if ($admin_id == $_SESSION['admin_id']) {
        session()->setFlash('admin_message', "Bạn không thể xóa tài khoản của chính mình!");
        session()->setFlash('admin_message_type', "danger");
        redirect(ADMINAPPURL . "/admins/admins.php");
        exit;
    }

    try {
        $conn->beginTransaction();

        // Kiểm tra admin tồn tại
        $check = $conn->prepare("SELECT admin_name FROM admins WHERE ID = ?");
        $check->execute([$admin_id]);
        $admin = $check->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            throw new Exception("Không tìm thấy admin với ID này");
        }

        // Xóa admin
        $delete = $conn->prepare("DELETE FROM admins WHERE ID = ?");
        $result = $delete->execute([$admin_id]);

        if (!$result) {
            throw new Exception("Có lỗi xảy ra khi xóa admin");
        }

        $conn->commit();

        session()->setFlash('admin_message', "Đã xóa quản trị viên \"" . htmlspecialchars($admin['admin_name']) . "\" thành công");
        session()->setFlash('admin_message_type', "success");
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        session()->setFlash('admin_message', "Lỗi: " . $e->getMessage());
        session()->setFlash('admin_message_type', "danger");
    }
} else {
    session()->setFlash('admin_message', "Truy cập không hợp lệ");
    session()->setFlash('admin_message_type', "danger");
}

// Chuyển hướng về danh sách admin
redirect(ADMINAPPURL . "/admins/admins.php");
exit;
