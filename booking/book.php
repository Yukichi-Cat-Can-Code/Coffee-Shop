<?php
ob_start();

// Load config (sẽ tự động load SessionManager)
require "../config/config.php";

// Kiểm tra người dùng đã đăng nhập chưa
if (function_exists('session') && !session()->isLoggedIn()) {
    session()->setFlash('error_message', "Vui lòng đăng nhập để đặt bàn");
    redirect(APPURL . "/auth/login.php");
}

// Lấy thông tin người dùng từ session
$currentUser = function_exists('session') ? session()->getCurrentUser() : null;
$user_id = $currentUser ? $currentUser['id'] : null;

// Khởi tạo mảng lưu lỗi
$errors = [];

if (isset($_POST['submit'])) {
    // Lấy và làm sạch dữ liệu
    $first_name = isset($_POST['first_name']) ? trim(escape($_POST['first_name'])) : '';
    $last_name = isset($_POST['last_name']) ? trim(escape($_POST['last_name'])) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $phone_number = isset($_POST['phone_number']) ? trim(escape($_POST['phone_number'])) : '';
    $message = isset($_POST['message']) ? trim(escape($_POST['message'])) : '';

    // Kiểm tra dữ liệu đầu vào
    if (empty($first_name)) {
        $errors[] = "Vui lòng nhập họ";
    }

    if (empty($last_name)) {
        $errors[] = "Vui lòng nhập tên";
    }

    if (empty($date)) {
        $errors[] = "Vui lòng chọn ngày";
    }

    if (empty($time)) {
        $errors[] = "Vui lòng chọn giờ";
    }

    if (empty($phone_number)) {
        $errors[] = "Vui lòng nhập số điện thoại";
    } elseif (!preg_match("/^[0-9]{10,11}$/", $phone_number)) {
        $errors[] = "Số điện thoại không hợp lệ";
    }

    // Nếu không có lỗi, thêm đặt bàn vào cơ sở dữ liệu
    if (empty($errors)) {
        try {
            // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
            $conn->beginTransaction();

            $insert = $conn->prepare("
                INSERT INTO bookings (user_id, first_name, last_name, date, time, phone_number, message, status)
                VALUES (:user_id, :first_name, :last_name, :date, :time, :phone_number, :message, :status)
            ");

            $result = $insert->execute([
                ":user_id" => $user_id,
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":date" => $date,
                ":time" => $time,
                ":phone_number" => $phone_number,
                ":message" => $message,
                ":status" => "Pending",
            ]);

            if ($result === false) {
                // Log lỗi và throw exception
                $errorInfo = $insert->errorInfo();
                throw new PDOException("Database error: " . $errorInfo[2]);
            }

            // Commit transaction
            $conn->commit();

            // Sử dụng SessionManager để lưu thông báo thành công
            if (function_exists('session')) {
                session()->setFlash('success_message', "Đặt bàn thành công! Chúng tôi sẽ liên hệ với bạn sớm.");
            }

            // Chuyển hướng với tham số để tránh redirect loop
            redirect(APPURL . "/index.php?booking=success");
        } catch (PDOException $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollBack();

            // Log lỗi và thêm vào mảng errors
            error_log("Database Error in booking: " . $e->getMessage());
            $errors[] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();

            // Debug để kiểm tra lỗi
            if (isset($_GET['debug'])) {
                echo "<pre>";
                print_r($e->getMessage());
                echo "</pre>";
                exit;
            }
        }
    }

    // Nếu có lỗi, lưu vào session để hiển thị ở trang đặt bàn
    if (!empty($errors)) {
        if (function_exists('session')) {
            foreach ($errors as $error) {
                session()->setFlash('error_message', $error);
            }

            session()->set('booking_data', [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date' => $date,
                'time' => $time,
                'phone_number' => $phone_number,
                'message' => $message,
            ]);
        }

        redirect(APPURL . "/booking.php?error=true");
    }
} else {
    // Nếu không phải POST request, chuyển hướng về trang booking
    redirect(APPURL . "/booking.php");
}

ob_end_flush();
