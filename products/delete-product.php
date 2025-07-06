<?php
require "../config/config.php";

// Chỉ kiểm tra đăng nhập, không cần kiểm tra cart_id ở đây
if (!function_exists('session') || !session()->isLoggedIn()) {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
    header("Location: " . APPURL . "/auth/login.php");
    exit;
}

if (isset($_GET['cart_id']) && is_numeric($_GET['cart_id'])) {
    $cart_id = intval($_GET['cart_id']);
    $user_id = session()->getCurrentUser()['id'];

    // Xóa sản phẩm khỏi giỏ hàng của đúng user
    $delete_product = $conn->prepare("DELETE FROM cart WHERE ID = :cart_id AND user_id = :user_id");
    $delete_product->execute([
        ':cart_id' => $cart_id,
        ':user_id' => $user_id
    ]);
}

// Chuyển hướng về trang giỏ hàng
header("Location: cart.php");
exit;
