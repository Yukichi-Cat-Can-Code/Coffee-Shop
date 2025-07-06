<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy tham số
$orderID = $_GET['order_id'] ?? null;
$format = in_array($_GET['format'] ?? '', ['thermal', 'a5']) ? $_GET['format'] : 'a5'; // Validate format
$preview = isset($_GET['preview']) && $_GET['preview'] == 1;

if (!$orderID) {
    echo "Không tìm thấy đơn hàng!";
    exit;
}

// Định dạng tiền tệ
function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Định dạng ngày giờ
function formatDateTime($dateTime)
{
    $date = new DateTime($dateTime);
    return $date->format('H:i:s - d/m/Y');
}

// Đảm bảo thư mục templates tồn tại
$templatesDir = __DIR__ . '/templates';
if (!is_dir($templatesDir)) {
    mkdir($templatesDir, 0755, true);
}

// Output HTML header
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?= htmlspecialchars($orderID) ?></title>
    <style>
        @media print {
            @page {
                margin: 0;
            }

            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        .receipt-container {
            padding: 10px;
        }

        .thermal-receipt {
            max-width: 80mm;
            margin: 0 auto;
            font-size: 12px;
        }

        .a5-receipt {
            max-width: 148mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-print {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .no-print button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
        }
    </style>
</head>

<body>
    <?php
    try {
        // Lấy thông tin đơn hàng chính
        $orderStmt = $conn->prepare("
            SELECT o.*, a.admin_name 
            FROM pos_orders o 
            LEFT JOIN admins a ON o.admin_id = a.ID 
            WHERE o.order_id = :order_id
        ");
        $orderStmt->bindParam(':order_id', $orderID);
        $orderStmt->execute();

        if ($orderStmt->rowCount() == 0) {
            echo "<div style='text-align:center; margin-top:50px;'>
              <h2>Không tìm thấy đơn hàng!</h2>
              <p>Vui lòng kiểm tra lại mã đơn hàng</p>
              <button onclick='window.close()'>Đóng</button>
              </div>";
            echo "</body></html>";
            exit;
        }

        $orderMain = $orderStmt->fetch(PDO::FETCH_ASSOC);

        // Lấy chi tiết đơn hàng
        $itemsStmt = $conn->prepare("
            SELECT * FROM pos_order_items 
            WHERE order_id = :order_id 
            ORDER BY item_id ASC
        ");
        $itemsStmt->bindParam(':order_id', $orderID);
        $itemsStmt->execute();
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy thông tin khách hàng
        $customer = null;
        if (!empty($orderMain['customer_id'])) {
            $custStmt = $conn->prepare("SELECT * FROM users WHERE ID = :id");
            $custStmt->bindParam(':id', $orderMain['customer_id']);
            $custStmt->execute();
            if ($custStmt->rowCount() > 0) {
                $customer = $custStmt->fetch(PDO::FETCH_ASSOC);
                $customer['isGuest'] = false;
            }
        }
        // Nếu không có thông tin khách hàng từ bảng users nhưng có tên khách hàng
        if (!$customer && !empty($orderMain['customer_name'])) {
            $customer = [
                'isGuest' => true,
                'user_name' => $orderMain['customer_name'],
                'user_phone' => $orderMain['customer_phone'] ?? '',
                'points' => 0
            ];
        }

        // Lấy thông tin thanh toán
        $payment = [
            'method' => 'Tiền mặt',
            'amount' => $orderMain['final_amount'],
            'date' => $orderMain['created_at'],
            'transaction_id' => null
        ];

        $paymentStmt = $conn->prepare("
            SELECT * FROM payments 
            WHERE order_id = :order_id 
            LIMIT 1
        ");
        $paymentStmt->bindParam(':order_id', $orderID);
        $paymentStmt->execute();

        if ($paymentStmt->rowCount() > 0) {
            $paymentData = $paymentStmt->fetch(PDO::FETCH_ASSOC);
            $payment['method'] = $paymentData['payment_method'];
            $payment['date'] = $paymentData['payment_date'];
            $payment['transaction_id'] = $paymentData['transaction_id'];
        }

        // Chuẩn bị các biến bổ sung
        $storeName = "Coffee Shop";
        $storeAddress = "123 Đường Cà Phê, Quận 1, TP.HCM";
        $storePhone = "0123456789";
        $storeEmail = "info@coffeeshop.vn";
        $vatNumber = "0123456789";
        $footerNote = "Cảm ơn quý khách đã ủng hộ!";

        // Hiển thị nút điều hướng khi ở chế độ xem trước
        if ($preview) {
            echo '<div class="no-print">
                <button onclick="window.print()">In hóa đơn</button>
                <button onclick="window.close()">Đóng</button>
            </div>';
        }

        // Kiểm tra và render template dựa theo định dạng
        if ($format === 'thermal') {
            // TEMPLATE HÓA ĐƠN THERMAL 80MM
            include 'templates/thermal-receipt.php';
        } else {
            // TEMPLATE HÓA ĐƠN A5
            include 'templates/a5-receipt.php';
        }

        // Nếu không phải chế độ xem trước, tự động kích hoạt in
        if (!$preview) {
            echo "<script>
                window.onload = function() { 
                    try {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    } catch (e) {
                        console.error('Lỗi khi in:', e);
                        alert('Không thể tự động in. Vui lòng sử dụng nút Print trên trình duyệt.');
                    }
                };
            </script>";
        }
    } catch (PDOException $e) {
        echo "<div style='text-align:center; margin-top:50px;'>
          <h2>Lỗi khi xử lý đơn hàng</h2>
          <p>" . htmlspecialchars($e->getMessage()) . "</p>
          <button onclick='window.close()'>Đóng</button>
          </div>";
    }
    ?>
</body>

</html>