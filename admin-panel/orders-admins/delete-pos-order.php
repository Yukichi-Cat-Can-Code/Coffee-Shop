<?php
require "../../config/config.php";
requireAdminLogin();

// Kiểm tra order_id
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    session()->setFlash('error_message', "Không tìm thấy mã đơn hàng");
    redirect(ADMINAPPURL . "/orders-admins/show-orders.php?type=pos");
}

$order_id = (int)$_GET['order_id'];

try {
    // Kiểm tra đơn hàng tồn tại
    $checkStmt = $conn->prepare("SELECT order_id FROM pos_orders WHERE order_id = :order_id");
    $checkStmt->bindParam(':order_id', $order_id);
    $checkStmt->execute();

    if ($checkStmt->rowCount() == 0) {
        session()->setFlash('error_message', "Đơn hàng #$order_id không tồn tại");
        redirect(ADMINAPPURL . "/orders-admins/show-orders.php?type=pos");
    }

    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->beginTransaction();

    // 1. Xóa chi tiết đơn hàng (pos_order_items)
    $deleteItemsStmt = $conn->prepare("DELETE FROM pos_order_items WHERE order_id = :order_id");
    $deleteItemsStmt->bindParam(':order_id', $order_id);
    $deleteItemsStmt->execute();

    // 2. Xóa thông tin thanh toán (nếu có bảng payments)
    $deletePaymentStmt = $conn->prepare("DELETE FROM payments WHERE order_id = :order_id");
    $deletePaymentStmt->bindParam(':order_id', $order_id);
    $deletePaymentStmt->execute();

    // 3. Xóa đơn hàng chính
    $deleteOrderStmt = $conn->prepare("DELETE FROM pos_orders WHERE order_id = :order_id");
    $deleteOrderStmt->bindParam(':order_id', $order_id);
    $deleteOrderStmt->execute();

    // Commit transaction
    $conn->commit();

    session()->setFlash('success_message', "Đã xóa thành công đơn hàng POS #$order_id");
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Lỗi xóa đơn hàng POS #$order_id: " . $e->getMessage());
    session()->setFlash('error_message', "Lỗi khi xóa đơn hàng: " . $e->getMessage());
}

// Redirect về trang danh sách đơn hàng POS
redirect(ADMINAPPURL . "/orders-admins/show-orders.php?type=pos");
