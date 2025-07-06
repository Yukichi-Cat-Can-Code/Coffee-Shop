<?php
require "../../config/config.php";
requireAdminLogin();

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Lấy thông tin đơn hàng trước khi xóa
        $select = $conn->prepare("SELECT * FROM orders WHERE ID = ?");
        $select->execute([$order_id]);
        $order = $select->fetch(PDO::FETCH_OBJ);

        if ($order) {
            // Xóa chi tiết đơn hàng (order_details)
            $check_table = $conn->query("SHOW TABLES LIKE 'order_details'");
            if ($check_table->rowCount() > 0) {
                $delete_details = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
                $delete_details->execute([$order_id]);
            }

            // Nếu có bảng order_items cũ, cũng xóa (tùy hệ thống)
            $check_items = $conn->query("SHOW TABLES LIKE 'order_items'");
            if ($check_items->rowCount() > 0) {
                $delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
                $delete_items->execute([$order_id]);
            }

            // Thực hiện xóa đơn hàng
            $delete_query = $conn->prepare("DELETE FROM orders WHERE ID = ?");
            $delete_query->execute([$order_id]);

            // Commit transaction nếu mọi thứ OK
            $conn->commit();

            session()->setFlash('success_message', "Đã xóa đơn hàng #" . $order_id . " thành công");
        } else {
            session()->setFlash('error_message', "Không tìm thấy đơn hàng #" . $order_id);
        }
    } catch (PDOException $e) {
        // Rollback khi có lỗi
        $conn->rollBack();
        session()->setFlash('error_message', "Lỗi khi xóa đơn hàng: " . $e->getMessage());
    }

    redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
} else {
    session()->setFlash('error_message', "Không tìm thấy ID đơn hàng cần xóa");
    redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
}
