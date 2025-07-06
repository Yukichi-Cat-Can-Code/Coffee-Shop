<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../config/config.php";
require "../auth/not-access.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['firstname', 'lastname', 'streetaddress', 'towncity', 'postcodezip', 'phone', 'emailaddress'];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }
    $data['apartment'] = trim($_POST['apartment'] ?? '');
    $payable_total_cost = function_exists('session') ? session()->get('payable_total_cost') : 0;
    $user_id = function_exists('session') ? session()->getCurrentUser()['id'] : 0;

    foreach ($fields as $f) {
        if (empty($data[$f])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO orders(firstname, lastname, streetaddress, apartment, towncity, postcode, phone, email, payable_total_cost, user_id)
            VALUES(:firstname, :lastname, :streetaddress, :apartment, :towncity, :postcode, :phone, :email, :payable_total_cost, :user_id)");
        $stmt->execute([
            ":firstname" => $data['firstname'],
            ":lastname" => $data['lastname'],
            ":streetaddress" => $data['streetaddress'],
            ":apartment" => $data['apartment'],
            ":towncity" => $data['towncity'],
            ":postcode" => $data['postcodezip'],
            ":phone" => $data['phone'],
            ":email" => $data['emailaddress'],
            ":payable_total_cost" => $payable_total_cost,
            ":user_id" => $user_id,
        ]);

        // Xóa giỏ hàng trong database
        $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $delete_cart->execute([':user_id' => $user_id]);

        // Xóa session giỏ hàng nếu có
        if (function_exists('session')) {
            session()->set('cart_count', 0);
            session()->remove('payable_total_cost');
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu đơn hàng: ' . $e->getMessage()]);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
exit;
