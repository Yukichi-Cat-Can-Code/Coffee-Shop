<?php
require "../../config/config.php";
requireAdminLogin();

header('Content-Type: application/json');

// Nhận dữ liệu đơn hàng từ POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}

// Lấy dữ liệu JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Không nhận được dữ liệu đơn hàng']);
    exit;
}

// Xác thực dữ liệu cơ bản
if (empty($data['items']) || !is_array($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không có sản phẩm']);
    exit;
}

// Kiểm tra tiền thanh toán (nếu là tiền mặt)
if (($data['paymentMethod'] ?? '') === 'cash') {
    $receivedAmount = floatval($data['receivedAmount'] ?? 0);
    $subtotal = floatval($data['subtotal'] ?? 0);
    $discountPercent = floatval($data['discount'] ?? 0) / 100;
    $afterDiscount = $subtotal * (1 - $discountPercent);
    $finalAmount = $afterDiscount * 1.1; // Cộng 10% VAT

    if ($receivedAmount < $finalAmount) {
        echo json_encode(['status' => 'error', 'message' => 'Số tiền thanh toán không đủ']);
        exit;
    }
}

try {
    $conn->beginTransaction();

    // 1. Lấy thông tin admin
    $adminId = getAdminId($conn, $_SESSION['admin_name']);

    // 2. Xử lý thông tin khách hàng
    $customerInfo = processCustomerInfo($data);

    // 3. Tính toán giá trị đơn hàng
    $orderValues = calculateOrderValues($data);

    // 4. Lưu thông tin đơn hàng
    $orderId = saveOrder($conn, $data, $customerInfo, $orderValues, $adminId);

    // 5. Lưu chi tiết đơn hàng
    saveOrderItems($conn, $orderId, $data['items']);

    // 6. Lưu thông tin thanh toán
    savePayment($conn, $orderId, $data, $orderValues['finalAmount'], $adminId);

    // 7. Xử lý khách hàng (tích điểm hoặc tạo mới)
    processCustomerPoints($conn, $data, $customerInfo, $orderValues['finalAmount'], $orderId);

    // Hoàn tất giao dịch
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Đơn hàng đã được xử lý thành công',
        'order_id' => $orderId
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}

// --- CÁC HÀM PHỤ TRỢ ---

// Lấy ID admin từ tên
function getAdminId($conn, $adminName)
{
    $stmt = $conn->prepare("SELECT ID FROM admins WHERE admin_name = :admin_name");
    $stmt->bindParam(':admin_name', $adminName);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    return $admin ? $admin['ID'] : 0;
}

// Xử lý thông tin khách hàng
function processCustomerInfo($data)
{
    $info = [
        'name' => '',
        'phone' => '',
        'id' => null,
        'isNew' => false
    ];

    if (isset($data['customer'])) {
        // Xác định ID khách hàng
        if (!empty($data['customer']['id'])) {
            if (
                strpos($data['customer']['id'], 'new_') !== 0 &&
                strpos($data['customer']['id'], 'guest_') !== 0
            ) {
                $info['id'] = $data['customer']['id'];
            }
        }

        // Xác định tên khách hàng
        if (!empty($data['customer']['name'])) {
            $info['name'] = $data['customer']['name'];
        } elseif (!empty($data['customer']['firstname']) && !empty($data['customer']['lastname'])) {
            $info['name'] = $data['customer']['firstname'] . ' ' . $data['customer']['lastname'];
        }

        // Xác định số điện thoại
        $info['phone'] = $data['customer']['phone'] ?? '';

        // Xác định nếu là khách hàng mới
        $info['isNew'] = isset($data['customer']['isNew']) && $data['customer']['isNew'];
    }

    return $info;
}

// Tính toán giá trị đơn hàng
function calculateOrderValues($data)
{
    $subtotal = floatval($data['subtotal']);
    $discountPercent = floatval($data['discount'] ?? 0) / 100;
    $discountAmount = $subtotal * $discountPercent;
    $afterDiscount = $subtotal - $discountAmount;
    $taxAmount = $afterDiscount * 0.1; // 10% VAT
    $finalAmount = $afterDiscount + $taxAmount;

    return [
        'subtotal' => $subtotal,
        'discountAmount' => $discountAmount,
        'taxAmount' => $taxAmount,
        'finalAmount' => $finalAmount
    ];
}

// Lưu thông tin đơn hàng chính
function saveOrder($conn, $data, $customer, $values, $adminId)
{
    // Kiểm tra bảng có cột customer_id không
    $addCustomerId = checkTableHasColumn($conn, 'pos_orders', 'customer_id') && isset($customer['id']) && $customer['id'] !== null;

    $sql = "INSERT INTO pos_orders (
        table_number, order_type, total_amount, discount_amount, 
        tax_amount, final_amount, payment_status, order_status, 
        customer_name, customer_phone, admin_id" .
        ($addCustomerId ? ", customer_id" : "") .
        ") VALUES (
        :table_number, :order_type, :total_amount, :discount_amount, 
        :tax_amount, :final_amount, 'Đã thanh toán', 'Hoàn thành', 
        :customer_name, :customer_phone, :admin_id" .
        ($addCustomerId ? ", :customer_id" : "") .
        ")";

    $stmt = $conn->prepare($sql);

    $orderType = $data['orderType'] ?? 'Tại quán';
    $tableNumber = $data['tableNumber'] ?? null;

    $stmt->bindParam(':table_number', $tableNumber);
    $stmt->bindParam(':order_type', $orderType);
    $stmt->bindParam(':total_amount', $values['subtotal'], PDO::PARAM_STR);
    $stmt->bindParam(':discount_amount', $values['discountAmount'], PDO::PARAM_STR);
    $stmt->bindParam(':tax_amount', $values['taxAmount'], PDO::PARAM_STR);
    $stmt->bindParam(':final_amount', $values['finalAmount'], PDO::PARAM_STR);
    $stmt->bindParam(':customer_name', $customer['name']);
    $stmt->bindParam(':customer_phone', $customer['phone']);
    $stmt->bindParam(':admin_id', $adminId);

    if ($addCustomerId && $customer['id'] !== null) {
        $stmt->bindParam(':customer_id', $customer['id']);
    }

    $stmt->execute();
    return $conn->lastInsertId();
}

// Lưu chi tiết đơn hàng
function saveOrderItems($conn, $orderId, $items)
{
    foreach ($items as $item) {
        // Kiểm tra dữ liệu hợp lệ
        if ($item['quantity'] <= 0 || $item['price'] < 0) {
            continue;
        }

        // Kiểm tra từng item có size/customizations không
        $hasSize = checkTableHasColumn($conn, 'pos_order_items', 'size') && isset($item['size']) && $item['size'] !== null;
        $hasCustomizations = checkTableHasColumn($conn, 'pos_order_items', 'customizations') && isset($item['customizations']) && $item['customizations'] !== null;

        // Tạo câu truy vấn động cho TỪNG ITEM
        $fields = "order_id, product_id, product_name, quantity, unit_price, subtotal";
        $values = ":order_id, :product_id, :product_name, :quantity, :unit_price, :subtotal";

        if ($hasSize) {
            $fields .= ", size";
            $values .= ", :size";
        }

        if ($hasCustomizations) {
            $fields .= ", customizations";
            $values .= ", :customizations";
        }

        $sql = "INSERT INTO pos_order_items ($fields) VALUES ($values)";
        $stmt = $conn->prepare($sql);

        // Bind các tham số cơ bản
        $itemSubtotal = $item['price'] * $item['quantity'];
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':product_id', $item['id']);
        $stmt->bindParam(':product_name', $item['name']);
        $stmt->bindParam(':quantity', $item['quantity']);
        $stmt->bindParam(':unit_price', $item['price'], PDO::PARAM_STR);
        $stmt->bindParam(':subtotal', $itemSubtotal, PDO::PARAM_STR);

        // Bind các tham số động
        if ($hasSize) {
            $stmt->bindParam(':size', $item['size']);
        }

        if ($hasCustomizations) {
            $customizations = is_array($item['customizations'])
                ? implode(', ', $item['customizations'])
                : $item['customizations'];
            $stmt->bindParam(':customizations', $customizations);
        }

        $stmt->execute();
    }
}

// Lưu thông tin thanh toán
function savePayment($conn, $orderId, $data, $finalAmount, $adminId)
{
    $stmt = $conn->prepare("INSERT INTO payments (
        order_id, payment_method, amount, admin_id, status, payment_date
    ) VALUES (
        :order_id, :payment_method, :amount, :admin_id, 'Hoàn thành', NOW()
    )");

    // Chuyển đổi mã phương thức thành tên hiển thị
    $paymentMethod = 'Tiền mặt'; // Mặc định
    if (isset($data['paymentMethod'])) {
        switch ($data['paymentMethod']) {
            case 'card':
                $paymentMethod = 'Thẻ tín dụng';
                break;
            case 'momo':
                $paymentMethod = 'Ví điện tử';
                break;
        }
    }

    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':payment_method', $paymentMethod);
    $stmt->bindParam(':amount', $finalAmount, PDO::PARAM_STR);
    $stmt->bindParam(':admin_id', $adminId);
    $stmt->execute();
}

// Xử lý điểm tích lũy hoặc tạo khách hàng mới
function processCustomerPoints($conn, $data, $customer, $finalAmount, $orderId)
{
    // Chỉ xử lý nếu có thông tin khách hàng
    if (empty($data['customer'])) return;

    // Nếu là khách hàng mới
    if ($customer['isNew']) {
        // Kiểm tra email có tồn tại trong data không
        if (!empty($data['customer']['email'])) {
            // Kiểm tra email đã tồn tại chưa
            $checkEmail = $conn->prepare("SELECT ID FROM users WHERE user_email = :email");
            $checkEmail->bindParam(':email', $data['customer']['email']);
            $checkEmail->execute();

            if ($checkEmail->rowCount() == 0) {
                // Email chưa tồn tại, tạo người dùng mới
                createNewUser($conn, $data, $finalAmount, $orderId);
            }
        }
    }
    // Nếu là khách hàng cũ, cập nhật điểm
    elseif ($customer['id'] !== null) {
        $pointsEarned = intval($finalAmount / 10000);

        if ($pointsEarned > 0) {
            $updatePoints = $conn->prepare("UPDATE users SET points = points + :points WHERE ID = :id");
            $updatePoints->bindParam(':points', $pointsEarned);
            $updatePoints->bindParam(':id', $customer['id']);
            $updatePoints->execute();
        }
    }
}

// Tạo người dùng mới
function createNewUser($conn, $data, $finalAmount, $orderId)
{
    // Tạo mảng các trường cần thiết
    $userFields = [
        'user_name' => $data['customer']['name'] ?? '',
        'user_email' => $data['customer']['email'] ?? '',
        'user_pass' => password_hash(uniqid(), PASSWORD_DEFAULT),
        'points' => intval($finalAmount / 10000)
    ];

    // Thêm các trường tùy chọn nếu có dữ liệu
    if (!empty($data['customer']['phone'])) {
        $userFields['user_phone'] = $data['customer']['phone'];
    }

    if (!empty($data['customer']['streetaddress'])) {
        $userFields['street_address'] = $data['customer']['streetaddress'];
    }

    if (!empty($data['customer']['apartment'])) {
        $userFields['apartment'] = $data['customer']['apartment'];
    }

    if (!empty($data['customer']['towncity'])) {
        $userFields['town_city'] = $data['customer']['towncity'];
    }

    if (!empty($data['customer']['postcode'])) {
        $userFields['postcode'] = $data['customer']['postcode'];
    }

    // Xây dựng câu SQL động
    $fields = implode(', ', array_keys($userFields));
    $values = ':' . implode(', :', array_keys($userFields));

    $stmt = $conn->prepare("INSERT INTO users ($fields) VALUES ($values)");

    foreach ($userFields as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }

    $stmt->execute();
    $newUserId = $conn->lastInsertId();

    // Cập nhật customer_id trong đơn hàng
    if (checkTableHasColumn($conn, 'pos_orders', 'customer_id')) {
        $updateOrder = $conn->prepare("UPDATE pos_orders SET customer_id = :customer_id WHERE order_id = :order_id");
        $updateOrder->bindParam(':customer_id', $newUserId);
        $updateOrder->bindParam(':order_id', $orderId);
        $updateOrder->execute();
    }
}

// Kiểm tra cột có tồn tại trong bảng
function checkTableHasColumn($conn, $table, $column)
{
    $stmt = $conn->prepare("SHOW COLUMNS FROM $table LIKE :column");
    $stmt->bindParam(':column', $column);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}
