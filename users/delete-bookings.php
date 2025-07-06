<?php

require_once "../config/config.php";

// Kiểm tra người dùng đã đăng nhập chưa
if (function_exists('session') && !session()->isLoggedIn()) {
    session()->setFlash('error_message', "Bạn không có quyền truy cập trang này");
    redirect(APPURL . "/auth/login.php");
}

// Lấy user_id từ session
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : 0;

// Kiểm tra có booking_id không
if (!isset($_GET['booking_id']) || empty($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    session()->setFlash('error_message', "Yêu cầu không hợp lệ");
    redirect(APPURL . "/users/bookings.php");
}

$booking_id = (int)$_GET['booking_id'];

try {
    // Kiểm tra booking tồn tại và thuộc về user hiện tại
    $check_booking = $conn->prepare("SELECT * FROM bookings WHERE ID = :booking_id AND user_id = :user_id");
    $check_booking->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
    $check_booking->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $check_booking->execute();

    if ($check_booking->rowCount() === 0) {
        // Booking không tồn tại hoặc không thuộc về user
        session()->setFlash('error_message', "Lịch đặt bàn không tồn tại hoặc bạn không có quyền xóa");
        redirect(APPURL . "/users/bookings.php");
    }

    // Kiểm tra trạng thái booking
    $booking = $check_booking->fetch(PDO::FETCH_OBJ);
    if (strtolower($booking->status) === "approved" || strtolower($booking->status) === "đã duyệt") {
        session()->setFlash('error_message', "Không thể xóa lịch đặt bàn đã được duyệt");
        redirect(APPURL . "/users/bookings.php");
    }

    // Tiến hành xóa booking
    $delete_booking = $conn->prepare("DELETE FROM bookings WHERE ID = :booking_id AND user_id = :user_id");
    $delete_booking->bindParam(":booking_id", $booking_id, PDO::PARAM_INT);
    $delete_booking->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $result = $delete_booking->execute();

    if ($result) {
        // Xóa thành công
        session()->setFlash('success_message', "Đã hủy lịch đặt bàn thành công");
    } else {
        // Xóa thất bại
        session()->setFlash('error_message', "Không thể hủy lịch đặt bàn, vui lòng thử lại");
    }
} catch (PDOException $e) {
    // Log lỗi database
    error_log("Database error in delete-bookings.php: " . $e->getMessage());
    session()->setFlash('error_message', "Đã xảy ra lỗi, vui lòng thử lại sau");
}

// Chuyển hướng về trang danh sách đặt bàn
redirect(APPURL . "/users/bookings.php");
