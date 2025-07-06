<?php
require "../../config/config.php";
requireAdminLogin();

header('Content-Type: application/json');

// Kiểm tra dữ liệu POST
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin cần thiết'
    ]);
    exit;
}

$order_id = (int)$_POST['order_id'];
$status = trim($_POST['status']);

// Kiểm tra trạng thái hợp lệ
$valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Trạng thái không hợp lệ'
    ]);
    exit;
}

try {
    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE ID = ?");
    $result = $stmt->execute([$status, $order_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể cập nhật trạng thái đơn hàng'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
