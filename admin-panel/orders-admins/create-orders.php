<?php
require "../../config/config.php";
requireAdminLogin();

// Lấy danh sách users
$users_query = $conn->query("SELECT ID, user_name, user_email FROM users ORDER BY user_name");
$users = $users_query->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sản phẩm
$products_query = $conn->query("SELECT * FROM product ORDER BY product_title");
$products = $products_query->fetchAll(PDO::FETCH_ASSOC);

$error_message = "";
$success = false;

// Xử lý submit form
if (isset($_POST['submit'])) {
    $customer_type = $_POST['customer_type'] ?? 'existing';
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $streetaddress = trim($_POST['streetaddress'] ?? '');
    $apartment = trim($_POST['apartment'] ?? '');
    $towncity = trim($_POST['towncity'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $payable_total_cost = trim($_POST['payable_total_cost'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];

    $errors = [];

    if ($customer_type === 'existing') {
        $user_id = trim($_POST['user_id'] ?? '');
        if (empty($user_id)) {
            $errors[] = "Vui lòng chọn người dùng";
        }
    } else {
        $user_id = -1;
    }

    if (empty($firstname)) $errors[] = "Họ không được để trống";
    if (empty($lastname)) $errors[] = "Tên không được để trống";
    if (empty($streetaddress)) $errors[] = "Địa chỉ không được để trống";
    if (empty($towncity)) $errors[] = "Thành phố/Tỉnh không được để trống";
    if (empty($phone)) $errors[] = "Số điện thoại không được để trống";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ";
    if (empty($payable_total_cost)) $errors[] = "Tổng số tiền không được để trống";
    elseif (!is_numeric($payable_total_cost)) $errors[] = "Tổng số tiền phải là số";
    if (empty($status)) $errors[] = "Vui lòng chọn trạng thái đơn hàng";
    if (empty($cart_items)) $errors[] = "Giỏ hàng trống! Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $current_date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO orders (firstname, lastname, streetaddress, apartment, towncity, phone, email, payable_total_cost, user_id, status, order_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $firstname,
                $lastname,
                $streetaddress,
                $apartment,
                $towncity,
                $phone,
                $email,
                $payable_total_cost,
                $user_id,
                $status,
                $current_date
            ]);

            $order_id = $conn->lastInsertId();

            // Đảm bảo bảng order_details tồn tại
            try {
                $conn->query("SELECT 1 FROM order_details LIMIT 1");
            } catch (PDOException $e) {
                $conn->exec("
                    CREATE TABLE IF NOT EXISTS `order_details` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `order_id` int(11) NOT NULL,
                      `product_id` int(11) NOT NULL,
                      `product_name` varchar(255) NOT NULL,
                      `quantity` int(11) NOT NULL DEFAULT 1,
                      `price` decimal(10,2) NOT NULL,
                      `subtotal` decimal(10,2) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `order_id` (`order_id`),
                      KEY `product_id` (`product_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            }

            foreach ($cart_items as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;

                $detail_stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, product_name, quantity, price, subtotal) 
                                              VALUES (?, ?, ?, ?, ?, ?)");

                $detail_stmt->execute([
                    $order_id,
                    $product_id,
                    $item['name'],
                    $quantity,
                    $price,
                    $subtotal
                ]);
            }

            $conn->commit();

            session()->setFlash('success_message', "Đơn hàng #$order_id đã được tạo thành công");
            redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $error_message = "Lỗi tạo đơn hàng: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="page-icon-box me-3">
                            <i class="fas fa-shopping-cart text-primary"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-gray-800">Tạo đơn hàng mới</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 bg-transparent p-0">
                                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="show-orders.php" class="text-decoration-none">Đơn hàng</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Tạo đơn hàng</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <form action="create-orders.php" method="POST">
                        <div class="row">
                            <!-- Cột thông tin khách hàng và đơn hàng (bên trái) -->
                            <div class="col-lg-7">
                                <!-- Phần chọn khách hàng -->
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary bg-opacity-10 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-primary me-2 fs-4"></i>
                                            <h5 class="mb-0 fw-bold">Lựa chọn khách hàng</h5>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="customer_type" id="existing_customer" value="existing" checked>
                                                    <label class="form-check-label" for="existing_customer">
                                                        <strong>Khách hàng đã có tài khoản</strong>
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="customer_type" id="new_customer" value="new">
                                                    <label class="form-check-label" for="new_customer">
                                                        <strong>Khách hàng mới (không có tài khoản)</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="existing_customer_section">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="user_id" class="form-label">Chọn từ danh sách khách hàng</label>
                                                    <select name="user_id" id="user_id" class="form-select form-select-lg">
                                                        <option value="">-- Chọn khách hàng --</option>
                                                        <?php foreach ($users as $user): ?>
                                                            <option value="<?= $user['ID'] ?>"><?= htmlspecialchars($user['user_name']) ?> (<?= htmlspecialchars($user['user_email']) ?>)</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="form-text text-muted mt-2">
                                                        <i class="fas fa-info-circle me-1"></i> Thông tin sẽ được tự động điền dựa trên tài khoản đã chọn
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thông tin khách hàng -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <h5 class="mb-0">Thông tin khách hàng</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="firstname" class="form-label">Họ <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="firstname" name="firstname" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lastname" class="form-label">Tên <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email">
                                                <small class="form-text text-muted">Không bắt buộc</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="phone" name="phone" required>
                                            </div>
                                        </div>

                                        <!-- Địa chỉ giao hàng -->
                                        <h6 class="mt-4 mb-3 text-muted">Địa chỉ giao hàng</h6>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="streetaddress" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="streetaddress" name="streetaddress" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="apartment" class="form-label">Căn hộ/Tòa nhà</label>
                                                <input type="text" class="form-control" id="apartment" name="apartment">
                                                <small class="form-text text-muted">Không bắt buộc</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="towncity" class="form-label">Thành phố/Tỉnh <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="towncity" name="towncity" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thông tin đơn hàng -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <h5 class="mb-0">Thông tin đơn hàng</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="payable_total_cost" class="form-label">Tổng số tiền <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="payable_total_cost" name="payable_total_cost" readonly>
                                                    <span class="input-group-text">₫</span>
                                                </div>
                                                <small class="form-text text-muted">Tự động tính dựa trên giỏ hàng</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Trạng thái đơn hàng <span class="text-danger">*</span></label>
                                                <div class="status-selector">
                                                    <div class="form-check form-check-inline status-option">
                                                        <input class="form-check-input" type="radio" name="status" id="status_pending" value="Pending" checked>
                                                        <label class="form-check-label status-label status-pending" for="status_pending">
                                                            <i class="fas fa-clock me-1"></i> Đang xử lý
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline status-option">
                                                        <input class="form-check-input" type="radio" name="status" id="status_processing" value="Processing">
                                                        <label class="form-check-label status-label status-processing" for="status_processing">
                                                            <i class="fas fa-cog me-1"></i> Đang chuẩn bị
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline status-option">
                                                        <input class="form-check-input" type="radio" name="status" id="status_shipped" value="Shipped">
                                                        <label class="form-check-label status-label status-shipped" for="status_shipped">
                                                            <i class="fas fa-shipping-fast me-1"></i> Đang giao hàng
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline status-option">
                                                        <input class="form-check-input" type="radio" name="status" id="status_delivered" value="Delivered">
                                                        <label class="form-check-label status-label status-delivered" for="status_delivered">
                                                            <i class="fas fa-check-circle me-1"></i> Đã giao
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline status-option">
                                                        <input class="form-check-input" type="radio" name="status" id="status_cancelled" value="Cancelled">
                                                        <label class="form-check-label status-label status-cancelled" for="status_cancelled">
                                                            <i class="fas fa-times-circle me-1"></i> Đã hủy
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <a href="show-orders.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                                            </a>
                                            <button type="submit" name="submit" class="btn btn-success">
                                                <i class="fas fa-plus-circle me-1"></i> Tạo đơn hàng
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cột giỏ hàng (bên phải) -->
                            <div class="col-lg-5">
                                <!-- Thêm sản phẩm vào đơn hàng -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-success bg-opacity-10 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-shopping-basket text-success me-2 fs-4"></i>
                                            <h5 class="mb-0 fw-bold">Thêm sản phẩm vào đơn hàng</h5>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label for="product-search" class="form-label">Tìm sản phẩm</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="product-search" placeholder="Nhập tên sản phẩm...">
                                                <button type="button" class="btn btn-outline-secondary" id="clear-search">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="product-list-container border rounded overflow-auto mb-3" style="max-height: 300px;">
                                            <ul class="list-group list-group-flush" id="product-list">
                                                <?php foreach ($products as $product): ?>
                                                    <li class="list-group-item product-item" data-id="<?= $product['ID'] ?>" data-name="<?= htmlspecialchars($product['product_title']) ?>" data-price="<?= $product['price'] ?>">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($product['product_title']) ?></h6>
                                                                <span class="text-muted small"><?= htmlspecialchars($product['description'] ?? '') ?></span>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="fw-bold"><?= number_format($product['price'], 0, ',', '.') ?>₫</div>
                                                                <button type="button" class="btn btn-sm btn-outline-success add-to-cart">
                                                                    <i class="fas fa-plus"></i> Thêm
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <div class="alert alert-info" id="empty-product-alert" style="display: none;">
                                            <i class="fas fa-info-circle me-1"></i> Không tìm thấy sản phẩm phù hợp
                                        </div>
                                    </div>
                                </div>

                                <!-- Giỏ hàng -->
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary bg-opacity-10 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-cart-plus text-primary me-2 fs-4"></i>
                                            <h5 class="mb-0 fw-bold">Giỏ hàng</h5>
                                            <span class="badge bg-primary ms-2" id="cart-count">0</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div id="cart-container">
                                            <!-- Bảng giỏ hàng -->
                                            <div class="table-responsive">
                                                <table class="table" id="cart-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Sản phẩm</th>
                                                            <th class="text-center">SL</th>
                                                            <th class="text-end">Giá</th>
                                                            <th class="text-end">Thành tiền</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="cart-items">
                                                        <!-- Sản phẩm sẽ được thêm vào đây -->
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                                            <td class="text-end fw-bold" id="cart-total">0 ₫</td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Thông báo giỏ hàng trống -->
                                        <div id="empty-cart-message" class="text-center py-4">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h5>Giỏ hàng trống</h5>
                                            <p class="text-muted">Vui lòng thêm sản phẩm vào đơn hàng</p>
                                        </div>

                                        <!-- Hidden input to store cart data -->
                                        <input type="hidden" name="cart_items" id="cart-items-input" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Status selector styling */
    .status-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .status-option {
        margin-right: 0;
    }

    .status-label {
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 5px;
    }

    input[type="radio"]:checked+.status-label {
        color: #fff;
    }

    .status-pending {
        border: 2px solid #ffc107;
        color: #856404;
    }

    input[type="radio"]:checked+.status-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .status-processing {
        border: 2px solid #17a2b8;
        color: #0c5460;
    }

    input[type="radio"]:checked+.status-processing {
        background-color: #17a2b8;
    }

    .status-shipped {
        border: 2px solid #007bff;
        color: #004085;
    }

    input[type="radio"]:checked+.status-shipped {
        background-color: #007bff;
    }

    .status-delivered {
        border: 2px solid #28a745;
        color: #155724;
    }

    input[type="radio"]:checked+.status-delivered {
        background-color: #28a745;
    }

    .status-cancelled {
        border: 2px solid #dc3545;
        color: #721c24;
    }

    input[type="radio"]:checked+.status-cancelled {
        background-color: #dc3545;
    }

    /* Hide actual radio buttons */
    .status-selector .form-check-input {
        position: absolute;
        opacity: 0;
    }

    /* Product List Styling */
    .product-item {
        transition: all 0.2s;
    }

    .product-item:hover {
        background-color: #f8f9fa;
    }

    /* Cart Item styling */
    .quantity-control {
        width: 80px;
    }

    .cart-item-row {
        vertical-align: middle;
    }

    /* Improve display on mobile */
    @media (max-width: 768px) {
        .status-selector {
            flex-direction: column;
        }

        .status-label {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ====== QUẢN LÝ KHÁCH HÀNG ======
        const existingCustomerRadio = document.getElementById('existing_customer');
        const newCustomerRadio = document.getElementById('new_customer');
        const existingCustomerSection = document.getElementById('existing_customer_section');
        const userSelect = document.getElementById('user_id');

        const firstnameInput = document.getElementById('firstname');
        const lastnameInput = document.getElementById('lastname');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const addressInput = document.getElementById('streetaddress');
        const apartmentInput = document.getElementById('apartment');
        const towncityInput = document.getElementById('towncity');

        // Đánh dấu các trường sẽ được tự động điền khi chọn user
        const autoFillFields = [firstnameInput, lastnameInput, emailInput];

        // Hàm để xử lý việc chuyển đổi giữa khách hàng mới và khách hàng có sẵn
        function toggleCustomerType() {
            if (existingCustomerRadio.checked) {
                // Hiển thị phần chọn khách hàng có sẵn
                existingCustomerSection.style.display = 'block';
                userSelect.disabled = false; // Đảm bảo không bị disabled

                // Nếu đã chọn khách hàng, điền thông tin và vô hiệu hóa các trường
                if (userSelect.value) {
                    fillCustomerInfo();
                    setReadOnly(true);
                }
            } else {
                // Đang ở chế độ khách hàng mới
                existingCustomerSection.style.display = 'none';
                userSelect.value = ''; // Xóa giá trị đã chọn

                // Xóa giá trị và cho phép chỉnh sửa các trường
                clearFormFields();
                setReadOnly(false);
            }
        }

        // Hàm điền thông tin khách hàng từ dropdown đã chọn
        function fillCustomerInfo() {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userName = selectedOption.text.split(' (')[0];
            const nameParts = userName.split(' ');

            // Xử lý họ tên
            if (nameParts.length > 1) {
                lastnameInput.value = nameParts.pop();
                firstnameInput.value = nameParts.join(' ');
            } else {
                firstnameInput.value = userName;
                lastnameInput.value = '';
            }

            // Điền email từ thông tin trong dropdown
            const emailMatch = selectedOption.text.match(/\((.*?)\)/);
            if (emailMatch && emailMatch[1]) {
                emailInput.value = emailMatch[1];
            }

            // Nếu có API thì có thể lấy thêm thông tin khác
            if (userSelect.value) {
                // Chức năng AJAX có thể được thêm vào đây
                // fetch('get-user-details.php?user_id=' + userSelect.value)
                //    .then(response => response.json())
                //    .then(data => {
                //        // Điền thêm thông tin nếu có
                //        phoneInput.value = data.phone || '';
                //        addressInput.value = data.address || '';
                //        towncityInput.value = data.city || '';
                //    })
                //    .catch(error => console.error('Error:', error));
            }
        }

        // Hàm đặt chế độ chỉ đọc cho các trường được tự động điền
        function setReadOnly(isReadOnly) {
            autoFillFields.forEach(field => {
                if (isReadOnly) {
                    field.setAttribute('readonly', 'readonly');
                    field.classList.add('bg-light');
                } else {
                    field.removeAttribute('readonly');
                    field.classList.remove('bg-light');
                }
            });
        }

        // Hàm xóa giá trị các trường form
        function clearFormFields() {
            firstnameInput.value = '';
            lastnameInput.value = '';
            emailInput.value = '';
            phoneInput.value = '';
            addressInput.value = '';
            apartmentInput.value = '';
            towncityInput.value = '';
        }

        // Xử lý sự kiện khi thay đổi loại khách hàng
        existingCustomerRadio.addEventListener('change', toggleCustomerType);
        newCustomerRadio.addEventListener('change', toggleCustomerType);

        // Xử lý sự kiện khi chọn khách hàng từ dropdown
        userSelect.addEventListener('change', function() {
            if (this.value && existingCustomerRadio.checked) {
                fillCustomerInfo();
                setReadOnly(true);
            } else {
                clearFormFields();
                setReadOnly(false);
            }
        });

        // Hiển thị ban đầu dựa trên trạng thái radio buttons khi trang tải
        toggleCustomerType();

        // ====== QUẢN LÝ GIỎ HÀNG ======
        const productSearch = document.getElementById('product-search');
        const clearSearchBtn = document.getElementById('clear-search');
        const productList = document.getElementById('product-list');
        const productItems = document.querySelectorAll('.product-item');
        const emptyProductAlert = document.getElementById('empty-product-alert');
        const cartItemsContainer = document.getElementById('cart-items');
        const cartItemsInput = document.getElementById('cart-items-input');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const cartTable = document.getElementById('cart-table');
        const cartTotalElement = document.getElementById('cart-total');
        const cartCountElement = document.getElementById('cart-count');
        const payableTotalInput = document.getElementById('payable_total_cost');

        // Khởi tạo giỏ hàng
        let cart = [];

        // Hiển thị/ẩn giỏ hàng trống
        function updateCartDisplay() {
            if (cart.length === 0) {
                emptyCartMessage.style.display = 'block';
                cartTable.style.display = 'none';
            } else {
                emptyCartMessage.style.display = 'none';
                cartTable.style.display = 'table';
            }

            // Cập nhật số lượng sản phẩm
            cartCountElement.textContent = cart.length;
        }

        // Cập nhật tổng tiền
        function updateCartTotal() {
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
            });

            cartTotalElement.textContent = formatCurrency(total);
            payableTotalInput.value = total; // Cập nhật input hidden

            // Lưu dữ liệu giỏ hàng vào input hidden
            cartItemsInput.value = JSON.stringify(cart);
        }

        // Format tiền tệ
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Render giỏ hàng
        function renderCart() {
            cartItemsContainer.innerHTML = '';

            cart.forEach((item, index) => {
                const row = document.createElement('tr');
                row.classList.add('cart-item-row');

                row.innerHTML = `
                    <td>
                        <div class="fw-semibold">${item.name}</div>
                        <input type="hidden" name="product_id[]" value="${item.id}">
                        <input type="hidden" name="product_name[]" value="${item.name}">
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm quantity-control">
                            <button type="button" class="btn btn-outline-secondary decrement-qty" data-index="${index}">-</button>
                            <input type="text" class="form-control text-center item-quantity" value="${item.quantity}" readonly>
                            <input type="hidden" name="quantity[]" value="${item.quantity}">
                            <button type="button" class="btn btn-outline-secondary increment-qty" data-index="${index}">+</button>
                        </div>
                    </td>
                    <td class="text-end">
                        ${formatCurrency(item.price)}
                        <input type="hidden" name="price[]" value="${item.price}">
                    </td>
                    <td class="text-end">${formatCurrency(item.price * item.quantity)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;

                cartItemsContainer.appendChild(row);
            });

            // Gắn event listeners cho các nút trong giỏ hàng
            document.querySelectorAll('.increment-qty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    cart[index].quantity += 1;
                    renderCart();
                    updateCartTotal();
                });
            });

            document.querySelectorAll('.decrement-qty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    if (cart[index].quantity > 1) {
                        cart[index].quantity -= 1;
                        renderCart();
                        updateCartTotal();
                    }
                });
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    cart.splice(index, 1);
                    renderCart();
                    updateCartTotal();
                    updateCartDisplay();
                });
            });

            updateCartTotal();
        }

        // Thêm sản phẩm vào giỏ hàng
        function addToCart(product) {
            // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
            const existingItemIndex = cart.findIndex(item => item.id === product.id);

            if (existingItemIndex >= 0) {
                // Nếu đã có, tăng số lượng
                cart[existingItemIndex].quantity += 1;
            } else {
                // Nếu chưa có, thêm mới
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1
                });
            }

            renderCart();
            updateCartDisplay();
        }

        // Xử lý tìm kiếm sản phẩm
        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let hasResults = false;

            productItems.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(searchTerm) || searchTerm === '') {
                    item.style.display = '';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            emptyProductAlert.style.display = hasResults ? 'none' : 'block';
        });

        // Xóa tìm kiếm
        clearSearchBtn.addEventListener('click', function() {
            productSearch.value = '';
            productItems.forEach(item => {
                item.style.display = '';
            });
            emptyProductAlert.style.display = 'none';
        });

        // Thêm sản phẩm vào giỏ hàng khi nhấn nút Thêm
        document.querySelectorAll('.add-to-cart').forEach((btn, index) => {
            btn.addEventListener('click', function() {
                const productItem = this.closest('.product-item');
                const product = {
                    id: productItem.getAttribute('data-id'),
                    name: productItem.getAttribute('data-name'),
                    price: parseFloat(productItem.getAttribute('data-price'))
                };

                addToCart(product);
            });
        });

        // Khởi tạo hiển thị giỏ hàng
        updateCartDisplay();

        // Xử lý form validation trước khi submit
        const orderForm = document.querySelector('form');
        orderForm.addEventListener('submit', function(e) {
            // Kiểm tra giỏ hàng
            if (cart.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một sản phẩm vào đơn hàng!');
                return;
            }

            // Tạo input ẩn để lưu loại khách hàng
            const customerTypeInput = document.createElement('input');
            customerTypeInput.type = 'hidden';
            customerTypeInput.name = 'customer_type';

            if (existingCustomerRadio.checked) {
                customerTypeInput.value = 'existing';
                // Kiểm tra đã chọn khách hàng chưa
                if (!userSelect.value) {
                    e.preventDefault();
                    alert('Vui lòng chọn khách hàng từ danh sách');
                    return;
                }
                // Đảm bảo userSelect không bị disabled
                userSelect.disabled = false;
            } else {
                customerTypeInput.value = 'new';
                // Vô hiệu hóa select để không gửi lên server
                userSelect.disabled = true;
            }

            // Thêm input ẩn vào form
            this.appendChild(customerTypeInput);
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>