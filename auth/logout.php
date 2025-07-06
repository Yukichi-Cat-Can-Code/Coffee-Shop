<?php

require_once "../config/config.php";

// Kiểm tra xem session đã được khởi tạo chưa
if (function_exists('session')) {
    // Sử dụng SessionManager để logout
    session()->logout();

    // Đặt flash message thông báo đăng xuất thành công
    session()->setFlash('success_message', 'Đăng xuất thành công!');
} else {
    // Fallback nếu SessionManager không hoạt động
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
}

redirect(APPURL);
