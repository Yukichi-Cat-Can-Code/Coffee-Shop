<?php
require "../../config/config.php";
requireAdminLogin();

// Xử lý xóa đặt bàn
if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Lấy thông tin đặt bàn trước khi xóa (nếu cần log)
        $select = $conn->prepare("SELECT * FROM bookings WHERE ID = ?");
        $select->execute([$booking_id]);
        $booking = $select->fetch(PDO::FETCH_OBJ);

        if ($booking) {
            // Thực hiện xóa đặt bàn
            $delete_query = $conn->prepare("DELETE FROM bookings WHERE ID = ?");
            $delete_query->execute([$booking_id]);

            // Commit transaction nếu mọi thứ OK
            $conn->commit();

            session()->setFlash('success_message', "Đã xóa đặt bàn #" . $booking_id . " thành công");
        } else {
            session()->setFlash('error_message', "Không tìm thấy đặt bàn #" . $booking_id);
        }
    } catch (PDOException $e) {
        // Rollback khi có lỗi
        $conn->rollBack();
        session()->setFlash('error_message', "Lỗi khi xóa đặt bàn: " . $e->getMessage());
    }

    redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
}

// Nếu không có ID đặt bàn, chuyển hướng về trang danh sách
session()->setFlash('error_message', "Không tìm thấy ID đặt bàn cần xóa");
redirect(ADMINAPPURL . "/bookings-admins/show-bookings.php");
