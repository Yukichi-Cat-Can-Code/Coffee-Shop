<?php
require "../../config/config.php";
requireAdminLogin();

define("ADMINAPPURL", "http://localhost/coffee-Shop/admin-panel");

// Xử lý AJAX request tìm kiếm khách hàng
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search-customers') {
    if (!isset($_SESSION['admin_name'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

    if (empty($searchTerm)) {
        echo json_encode(['error' => 'Vui lòng nhập từ khóa tìm kiếm']);
        exit;
    }

    try {
        // Kiểm tra cấu trúc bảng để xác định cột nào tồn tại
        $checkColumns = $conn->query("SHOW COLUMNS FROM users");
        $columns = [];
        while ($column = $checkColumns->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }

        // Xây dựng câu truy vấn dựa trên cột tồn tại
        $query = "SELECT ID, user_name, user_email";

        $columnMappings = [
            'user_phone' => 'user_phone',
            'street_address' => 'street_address',
            'apartment' => 'apartment',
            'town_city' => 'town_city',
            'postcode' => 'postcode',
            'points' => 'points'
        ];

        foreach ($columnMappings as $column => $alias) {
            $query .= in_array($column, $columns) ?
                ", $column" :
                ", '' AS $alias";
        }

        $query .= " FROM users WHERE user_name LIKE :term 
                   OR user_email LIKE :term 
                   OR user_phone LIKE :term
                   ORDER BY user_name LIMIT 10";

        // Tìm kiếm trong bảng users
        $stmt = $conn->prepare($query);
        $searchParam = "%{$searchTerm}%";
        $stmt->bindParam(':term', $searchParam);
        $stmt->execute();

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về kết quả dạng JSON
        header('Content-Type: application/json');
        echo json_encode($customers);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Lấy tất cả sản phẩm từ cơ sở dữ liệu và nhóm theo loại
try {
    $stmt = $conn->prepare("SELECT * FROM product ORDER BY type, product_title");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groupedProducts = [];
    foreach ($products as $product) {
        $type = $product['type'];
        if (!isset($groupedProducts[$type])) {
            $groupedProducts[$type] = [];
        }
        $groupedProducts[$type][] = $product;
    }
} catch (PDOException $e) {
    session()->setFlash('error_message', "Lỗi khi lấy dữ liệu sản phẩm: " . $e->getMessage());
}

// Khởi tạo session nếu cần
if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
    $_SESSION['pos_order_id'] = 'ORD' . time();
}

require "../layouts/header.php";
?>

<style>
    .customer-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .customer-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .customer-card.active {
        border-color: #4e73df;
        background-color: #f8f9fc;
    }

    .skeleton-loader {
        height: 120px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .product-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
    }

    #cart-items-container {
        scroll-behavior: smooth;
    }

    .add-to-cart {
        transition: all 0.2s;
    }

    .add-to-cart:hover {
        transform: scale(1.05);
    }

    .alert {
        transition: opacity 0.5s ease;
    }

    .card {
        border-radius: 0.5rem;
    }
</style>

<div class="container-fluid py-4">
    <!-- Alert container cho thông báo lỗi/thành công -->
    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

    <!-- Tiêu đề trang -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">POS Terminal</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">POS Terminal</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= ADMINAPPURL ?>/orders-admins/show-orders.php" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-history me-1"></i> Lịch sử đơn hàng
            </a>
            <button id="clear-pos" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash me-1"></i> Xóa đơn hàng
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Phần sản phẩm -->
        <div class="col-lg-8">
            <!-- Tìm kiếm sản phẩm -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="search-product" class="form-control bg-light border-0"
                            placeholder="Tìm kiếm sản phẩm theo tên, mô tả...">
                    </div>
                </div>
            </div>

            <!-- Tab danh mục -->
            <ul class="nav nav-tabs mb-4" id="product-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab"
                        data-bs-target="#all" type="button" role="tab" aria-selected="true">
                        Tất cả
                    </button>
                </li>
                <?php foreach (array_keys($groupedProducts) as $type): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="<?= htmlspecialchars($type) ?>-tab"
                            data-bs-toggle="tab" data-bs-target="#<?= htmlspecialchars($type) ?>"
                            type="button" role="tab">
                            <?php
                            $typeName = match ($type) {
                                'đồ uống', 'drink' => 'Đồ uống',
                                'food' => 'Đồ ăn',
                                'dessert' => 'Tráng miệng',
                                default => ucfirst($type)
                            };
                            echo $typeName;
                            ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Nội dung tab -->
            <div class="tab-content" id="product-content">
                <!-- Tab tất cả sản phẩm -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="all-products">
                        <?php foreach ($products as $product): ?>
                            <div class="col product-item" data-name="<?= htmlspecialchars(strtolower($product['product_title'])) ?>"
                                data-description="<?= htmlspecialchars(strtolower($product['description'])) ?>">
                                <div class="card h-100 border-0 shadow-sm product-card" data-id="<?= $product['ID'] ?>">
                                    <div class="position-relative">
                                        <img src="<?= ADMINAPPURL ?>/../images/<?= htmlspecialchars($product['image']) ?>"
                                            class="card-img-top" alt="<?= htmlspecialchars($product['product_title']) ?>"
                                            style="height: 160px; object-fit: cover;"
                                            onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <span class="badge bg-primary"><?= number_format($product['price'], 0, ',', '.') ?> đ</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate"><?= htmlspecialchars($product['product_title']) ?></h5>
                                        <p class="card-text small text-muted text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0">
                                        <button class="btn btn-primary btn-sm w-100 add-to-cart"
                                            data-id="<?= $product['ID'] ?>"
                                            data-name="<?= htmlspecialchars($product['product_title']) ?>"
                                            data-price="<?= $product['price'] ?>"
                                            data-image="<?= htmlspecialchars($product['image']) ?>">
                                            <i class="fas fa-plus me-2"></i> Thêm vào đơn
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab theo từng loại -->
                <?php foreach ($groupedProducts as $type => $typeProducts): ?>
                    <div class="tab-pane fade" id="<?= htmlspecialchars($type) ?>" role="tabpanel">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                            <?php foreach ($typeProducts as $product): ?>
                                <div class="col product-item" data-name="<?= htmlspecialchars(strtolower($product['product_title'])) ?>"
                                    data-description="<?= htmlspecialchars(strtolower($product['description'])) ?>">
                                    <div class="card h-100 border-0 shadow-sm product-card" data-id="<?= $product['ID'] ?>">
                                        <div class="position-relative">
                                            <img src="<?= ADMINAPPURL ?>/../images/<?= htmlspecialchars($product['image']) ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($product['product_title']) ?>"
                                                style="height: 160px; object-fit: cover;"
                                                onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <span class="badge bg-primary"><?= number_format($product['price'], 0, ',', '.') ?> đ</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title text-truncate"><?= htmlspecialchars($product['product_title']) ?></h5>
                                            <p class="card-text small text-muted text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                                        </div>
                                        <div class="card-footer bg-white border-0 pt-0">
                                            <button class="btn btn-primary btn-sm w-100 add-to-cart"
                                                data-id="<?= $product['ID'] ?>"
                                                data-name="<?= htmlspecialchars($product['product_title']) ?>"
                                                data-price="<?= $product['price'] ?>"
                                                data-image="<?= htmlspecialchars($product['image']) ?>">
                                                <i class="fas fa-plus me-2"></i> Thêm vào đơn
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Phần đơn hàng -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Đơn Hàng #<span id="order-id"><?= $_SESSION['pos_order_id'] ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Thông tin khách hàng -->
                    <div id="selected-customer-display" class="p-3 border-bottom bg-light" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    <span id="customer-name-display">-</span>
                                </h6>
                                <p class="small mb-0 text-muted">
                                    <i class="fas fa-phone-alt me-2"></i>
                                    <span id="customer-phone-display">-</span>
                                </p>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="change-customer">
                                <i class="fas fa-user-edit"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Nút chọn khách hàng khi chưa có -->
                    <div id="select-customer-prompt" class="p-3 border-bottom">
                        <button id="select-customer-btn" class="btn btn-outline-info btn-sm w-100">
                            <i class="fas fa-user-plus me-2"></i> Chọn khách hàng
                        </button>
                    </div>

                    <!-- Thông tin đơn hàng -->
                    <div class="d-flex border-bottom">
                        <div class="p-2 flex-fill text-center border-end">
                            <label for="order-type" class="form-label small mb-1">Loại đơn</label>
                            <select id="order-type" class="form-select form-select-sm">
                                <option value="Tại quán">Tại quán</option>
                                <option value="Mang đi">Mang đi</option>
                                <option value="Giao hàng">Giao hàng</option>
                            </select>
                        </div>
                        <div class="p-2 flex-fill text-center">
                            <label for="table-number" class="form-label small mb-1">Bàn số</label>
                            <input type="text" id="table-number" class="form-control form-control-sm" placeholder="Nhập số bàn">
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm trong đơn -->
                    <div id="cart-items-container" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="cart-items">
                            <!-- Sản phẩm sẽ được thêm vào đây bằng JavaScript -->
                            <li class="list-group-item text-center py-5" id="empty-cart">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                                    <p>Chưa có sản phẩm nào trong đơn hàng</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Thông tin tổng tiền -->
                    <div class="border-top p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="subtotal">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Giảm giá:</span>
                            <div class="input-group input-group-sm w-50">
                                <input type="number" class="form-control" id="discount" min="0" max="100" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Thuế VAT (10%):</span>
                            <span id="tax">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Tổng cộng:</span>
                            <span id="total">0 đ</span>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="mb-3">
                            <label class="form-label mb-2">Phương thức thanh toán:</label>
                            <div class="payment-methods">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment-method" id="payment-cash" value="cash" checked>
                                    <label class="form-check-label" for="payment-cash">
                                        <i class="fas fa-money-bill-wave me-2"></i> Tiền mặt
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment-method" id="payment-card" value="card">
                                    <label class="form-check-label" for="payment-card">
                                        <i class="fas fa-credit-card me-2"></i> Thẻ tín dụng/ghi nợ
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment-method" id="payment-momo" value="momo">
                                    <label class="form-check-label" for="payment-momo">
                                        <i class="fas fa-wallet me-2"></i> Ví điện tử (MoMo)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Các nút thao tác -->
                        <button id="checkout-btn" class="btn btn-success w-100 mb-2" disabled>
                            <i class="fas fa-check-circle me-2"></i> Thanh toán
                        </button>
                        <button id="summary-btn" class="btn btn-outline-info w-100" disabled>
                            <i class="fas fa-list-alt me-2"></i> Xem tóm tắt đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận thanh toán -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Xác nhận thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4 text-center">
                    <h4 class="mb-0">Tổng tiền: <span id="modal-total" class="text-primary">0 đ</span></h4>
                </div>

                <div id="cash-payment-section">
                    <div class="mb-3">
                        <label for="received-amount" class="form-label">Số tiền nhận:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="received-amount" min="0" autocomplete="off">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="change-amount" class="form-label">Tiền thối:</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="change-amount" readonly>
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                </div>

                <div id="card-payment-section" style="display: none;">
                    <div class="text-center p-4">
                        <i class="fas fa-credit-card fa-3x mb-3 text-primary"></i>
                        <p>Vui lòng quẹt thẻ trên thiết bị POS</p>
                    </div>
                </div>

                <div id="momo-payment-section" style="display: none;">
                    <div class="text-center p-4">
                        <p>Quét mã QR dưới đây để thanh toán qua MoMo</p>
                        <img src="<?= ADMINAPPURL ?>/assets/img/momo-qr.png" alt="MoMo QR Code" class="img-fluid" style="max-width: 200px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="confirm-payment">
                    <i class="fas fa-check me-2"></i> Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem tóm tắt đơn hàng -->
<div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="summaryModalLabel">Tóm tắt đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sản phẩm</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="summary-items">
                            <!-- Items will be filled here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end">Tạm tính:</td>
                                <td class="text-end" id="summary-subtotal">0 đ</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Giảm giá:</td>
                                <td class="text-end" id="summary-discount">0 đ</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Thuế VAT (10%):</td>
                                <td class="text-end" id="summary-tax">0 đ</td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Tổng cộng:</td>
                                <td class="text-end" id="summary-total">0 đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="proceed-checkout">
                    <i class="fas fa-arrow-right me-2"></i> Tiến hành thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal thông tin khách hàng -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Thông Tin Khách Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tab điều hướng -->
                <ul class="nav nav-tabs mb-3" id="customerTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="existing-customer-tab" data-bs-toggle="tab" data-bs-target="#existing-customer" type="button" role="tab">
                            <i class="fas fa-users me-2"></i> Khách Hàng Đã Có
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="new-customer-tab" data-bs-toggle="tab" data-bs-target="#new-customer" type="button" role="tab">
                            <i class="fas fa-user-plus me-2"></i> Khách Hàng Mới
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="guest-customer-tab" data-bs-toggle="tab" data-bs-target="#guest-customer" type="button" role="tab">
                            <i class="fas fa-user-tag me-2"></i> Khách Vãng Lai
                        </button>
                    </li>
                </ul>

                <!-- Nội dung tab -->
                <div class="tab-content" id="customerTabContent">
                    <!-- Tab khách hàng đã có -->
                    <div class="tab-pane fade show active" id="existing-customer" role="tabpanel">
                        <div class="mb-3">
                            <label for="search-customer" class="form-label">Tìm khách hàng:</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="search-customer"
                                    placeholder="Nhập tên, số điện thoại hoặc email...">
                                <button class="btn btn-primary" type="button" id="btn-search-customer">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div id="customer-search-results" class="mb-3">
                            <!-- Kết quả tìm kiếm sẽ hiển thị ở đây -->
                            <p class="text-muted text-center">Tìm kiếm khách hàng để xem thông tin</p>
                        </div>

                        <div id="selected-customer-info" style="display: none;" class="border rounded p-3 mb-3 bg-light">
                            <h6 class="mb-3 border-bottom pb-2">Thông tin chi tiết</h6>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">ID:</div>
                                <div class="col-8" id="customer-id"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Họ tên:</div>
                                <div class="col-8" id="customer-name"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Số điện thoại:</div>
                                <div class="col-8" id="customer-phone"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Email:</div>
                                <div class="col-8" id="customer-email"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Địa chỉ:</div>
                                <div class="col-8" id="customer-address"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Điểm tích lũy:</div>
                                <div class="col-8" id="customer-points"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab khách hàng mới -->
                    <div class="tab-pane fade" id="new-customer" role="tabpanel">
                        <form id="new-customer-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new-customer-firstname" class="form-label">Họ: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new-customer-firstname" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new-customer-lastname" class="form-label">Tên: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new-customer-lastname" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="new-customer-phone" class="form-label">Số điện thoại: <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="new-customer-phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="new-customer-email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="new-customer-email">
                                <div class="form-text">Tùy chọn, có thể bỏ qua</div>
                            </div>
                            <div class="mb-3">
                                <label for="new-customer-street" class="form-label">Địa chỉ đường:</label>
                                <input type="text" class="form-control" id="new-customer-street">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new-customer-apartment" class="form-label">Căn hộ/Phòng:</label>
                                    <input type="text" class="form-control" id="new-customer-apartment">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new-customer-city" class="form-label">Thành phố:</label>
                                    <input type="text" class="form-control" id="new-customer-city">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="new-customer-postcode" class="form-label">Mã bưu chính:</label>
                                <input type="text" class="form-control" id="new-customer-postcode">
                            </div>
                        </form>
                    </div>

                    <!-- Tab khách vãng lai -->
                    <div class="tab-pane fade" id="guest-customer" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Khách vãng lai:</strong> Tiếp tục mà không cần thông tin chi tiết khách hàng.
                            <p class="small mb-0 mt-1">Lưu ý: Không thể tích lũy điểm và theo dõi lịch sử mua hàng.</p>
                        </div>

                        <form id="guest-customer-form" class="mt-3">
                            <div class="mb-3">
                                <label for="guest-customer-name" class="form-label">Tên gọi (tùy chọn):</label>
                                <input type="text" class="form-control" id="guest-customer-name" placeholder="Ví dụ: Bàn 5, Khách mang đi...">
                            </div>
                            <div class="mb-3">
                                <label for="guest-customer-phone" class="form-label">Số điện thoại (tùy chọn):</label>
                                <input type="tel" class="form-control" id="guest-customer-phone" placeholder="Số điện thoại liên hệ...">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btn-select-customer">
                    <i class="fas fa-check me-2"></i> Tiếp tục
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script xử lý POS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Biến toàn cục
        let cart = [];
        const subtotalElement = document.getElementById('subtotal');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        const discountInput = document.getElementById('discount');
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCartMessage = document.getElementById('empty-cart');
        const checkoutBtn = document.getElementById('checkout-btn');
        const summaryBtn = document.getElementById('summary-btn');
        const searchInput = document.getElementById('search-product');
        const productItems = document.querySelectorAll('.product-item');
        const alertContainer = document.getElementById('alert-container');
        let selectedCustomer = null; // Biến lưu thông tin khách hàng đã chọn

        // Khôi phục giỏ hàng từ localStorage nếu có
        initializeCart();

        // Hàm khởi tạo giỏ hàng
        function initializeCart() {
            const savedCart = localStorage.getItem('posCart');
            if (savedCart) {
                try {
                    cart = JSON.parse(savedCart);
                    if (cart.length > 0) {
                        // Hiển thị các sản phẩm đã lưu
                        emptyCartMessage.style.display = 'none';
                        cart.forEach(item => addCartItemUI(item));
                        updateTotals();
                        checkoutBtn.disabled = false;
                        summaryBtn.disabled = false;
                    }
                } catch (e) {
                    console.error('Lỗi khi khôi phục giỏ hàng:', e);
                    cart = [];
                }
            }

            // Khôi phục thông tin khách hàng nếu có
            const savedCustomer = localStorage.getItem('posCustomer');
            if (savedCustomer) {
                try {
                    selectedCustomer = JSON.parse(savedCustomer);
                    if (selectedCustomer) {
                        document.getElementById('customer-name-display').textContent = selectedCustomer.name;
                        document.getElementById('customer-phone-display').textContent = selectedCustomer.phone || 'Không có';
                        document.getElementById('selected-customer-display').style.display = 'block';
                        document.getElementById('select-customer-prompt').style.display = 'none';
                    }
                } catch (e) {
                    console.error('Lỗi khi khôi phục thông tin khách hàng:', e);
                    selectedCustomer = null;
                }
            }
        }

        // Hàm hiển thị thông báo
        function showAlert(message, type = 'danger') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
            <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            alertContainer.appendChild(alert);

            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }

        // Hiển thị số lượng sản phẩm trên header giỏ hàng
        function updateCartCount() {
            const itemCount = cart.reduce((total, item) => total + item.quantity, 0);
            const cartHeader = document.querySelector('.card-header h5.mb-0');

            if (cartHeader) {
                cartHeader.innerHTML = `
                <i class="fas fa-shopping-cart me-2"></i>
                Đơn Hàng #<span id="order-id">${document.getElementById('order-id').textContent}</span>
                ${itemCount > 0 ? `<span class="badge bg-light text-dark ms-2">${itemCount} món</span>` : ''}
            `;
            }
        }

        // Xử lý tìm kiếm sản phẩm
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            productItems.forEach(item => {
                const productName = item.dataset.name || '';
                const productDesc = item.dataset.description || '';

                if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });

            // Nếu không có kết quả, hiển thị thông báo
            const visibleItems = document.querySelectorAll('.product-item[style=""]').length;
            const noResultsMsg = document.getElementById('no-results-message');

            if (visibleItems === 0 && searchTerm !== '') {
                if (!noResultsMsg) {
                    const message = document.createElement('div');
                    message.id = 'no-results-message';
                    message.className = 'col-12 text-center py-5';
                    message.innerHTML = `
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>Không tìm thấy sản phẩm phù hợp với từ khóa "${searchTerm}"</p>
                    </div>
                `;
                    document.getElementById('all-products').appendChild(message);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        });

        // Thêm sản phẩm vào giỏ hàng
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = parseFloat(this.dataset.price);
                const productImage = this.dataset.image;

                // Kiểm tra sản phẩm đã có trong giỏ chưa
                const existingItemIndex = cart.findIndex(item => item.id === productId);

                if (existingItemIndex !== -1) {
                    // Nếu đã có, tăng số lượng
                    cart[existingItemIndex].quantity += 1;
                    updateCartItemUI(cart[existingItemIndex]);
                } else {
                    // Nếu chưa có, thêm mới
                    const newItem = {
                        id: productId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        quantity: 1
                    };

                    cart.push(newItem);
                    addCartItemUI(newItem);
                }

                // Cập nhật tổng tiền
                updateTotals();

                // Ẩn thông báo giỏ trống
                if (cart.length > 0) {
                    emptyCartMessage.style.display = 'none';
                    checkoutBtn.disabled = false;
                    summaryBtn.disabled = false;
                }

                // Lưu giỏ hàng vào localStorage
                localStorage.setItem('posCart', JSON.stringify(cart));

                // Hiệu ứng thông báo đã thêm
                showAlert(`Đã thêm <strong>${productName}</strong> vào đơn hàng`, 'success');

                // Tạo hiệu ứng animation khi thêm vào giỏ
                const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
                if (productCard) {
                    productCard.classList.add('animate__animated', 'animate__pulse');
                    setTimeout(() => {
                        productCard.classList.remove('animate__animated', 'animate__pulse');
                    }, 500);
                }
            });
        });

        // Xử lý giảm giá
        discountInput.addEventListener('input', function() {
            // Đảm bảo giá trị nằm trong khoảng 0-100
            let value = parseInt(this.value) || 0;
            if (value < 0) value = 0;
            if (value > 100) value = 100;
            this.value = value;

            updateTotals();
        });

        // Xử lý phương thức thanh toán
        document.querySelectorAll('input[name="payment-method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const method = this.value;
                const cashSection = document.getElementById('cash-payment-section');
                const cardSection = document.getElementById('card-payment-section');
                const momoSection = document.getElementById('momo-payment-section');

                // Ẩn tất cả trước
                cashSection.style.display = 'none';
                cardSection.style.display = 'none';
                momoSection.style.display = 'none';

                // Hiển thị section phù hợp
                if (method === 'cash') cashSection.style.display = 'block';
                else if (method === 'card') cardSection.style.display = 'block';
                else if (method === 'momo') momoSection.style.display = 'block';
            });
        });

        // Xử lý nút chọn khách hàng
        document.getElementById('select-customer-btn').addEventListener('click', function() {
            const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
            customerModal.show();
        });

        // Xử lý nút thay đổi khách hàng
        document.getElementById('change-customer').addEventListener('click', function() {
            const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
            customerModal.show();
        });

        // Xử lý tìm kiếm khách hàng
        document.getElementById('btn-search-customer').addEventListener('click', function() {
            const searchTerm = document.getElementById('search-customer').value.trim();
            if (!searchTerm) {
                showAlert('Vui lòng nhập thông tin tìm kiếm!', 'warning');
                return;
            }

            // Hiển thị trạng thái đang tìm kiếm
            document.getElementById('customer-search-results').innerHTML = `
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tìm kiếm...</span>
                </div>
                <p class="mt-2">Đang tìm kiếm khách hàng...</p>
            </div>
        `;

            // Gọi AJAX để tìm kiếm từ database
            fetch(`<?= ADMINAPPURL ?>/pos-admins/index.php?ajax=search-customers&term=${encodeURIComponent(searchTerm)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Lỗi HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const resultsContainer = document.getElementById('customer-search-results');

                    if (data.error) {
                        resultsContainer.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Lỗi: ${data.error}
                        </div>
                    `;
                        return;
                    }

                    if (!data || data.length === 0) {
                        resultsContainer.innerHTML = `
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Không tìm thấy khách hàng phù hợp với từ khóa "${searchTerm}"
                        </div>
                        <div class="mt-3 text-center">
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="tab" data-bs-target="#new-customer" type="button">
                                <i class="fas fa-plus-circle me-1"></i> Tạo khách hàng mới
                            </button>
                        </div>
                    `;
                        return;
                    }

                    // Hiển thị danh sách khách hàng tìm thấy
                    let resultHTML = `<div class="list-group">`;
                    data.forEach(customer => {
                        // Tạo địa chỉ đầy đủ
                        const address = [
                            customer.street_address || '',
                            customer.apartment || '',
                            customer.town_city || '',
                            customer.postcode || ''
                        ].filter(Boolean).join(', ');

                        resultHTML += `
                        <button type="button" class="list-group-item list-group-item-action select-customer customer-card" 
                            data-id="${customer.ID}" 
                            data-name="${customer.user_name || ''}"
                            data-email="${customer.user_email || ''}"
                            data-phone="${customer.user_phone || ''}"
                            data-address="${address}"
                            data-points="${customer.points || '0'}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${customer.user_name || 'Chưa có tên'}</h6>
                                <small class="badge bg-primary">ID: ${customer.ID}</small>
                            </div>
                            <p class="mb-1 small">${customer.user_email || '<em>Chưa có email</em>'}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">${customer.user_phone || 'Chưa có SĐT'}</small>
                                <small class="badge bg-light text-dark">${address || 'Chưa có địa chỉ'}</small>
                            </div>
                            ${customer.points > 0 ? `<div class="mt-1"><span class="badge bg-success">Điểm: ${customer.points}</span></div>` : ''}
                        </button>
                    `;
                    });
                    resultHTML += `</div>`;

                    resultsContainer.innerHTML = resultHTML;

                    // Thêm sự kiện chọn khách hàng
                    document.querySelectorAll('.select-customer').forEach(button => {
                        button.addEventListener('click', function() {
                            // Lấy thông tin khách hàng từ data attributes
                            selectedCustomer = {
                                id: this.dataset.id,
                                name: this.dataset.name,
                                email: this.dataset.email,
                                phone: this.dataset.phone || '',
                                address: this.dataset.address || '',
                                points: this.dataset.points || '0'
                            };

                            // Đánh dấu khách hàng đã chọn
                            document.querySelectorAll('.select-customer').forEach(btn => {
                                btn.classList.remove('active');
                            });
                            this.classList.add('active');

                            // Hiển thị thông tin chi tiết khách hàng
                            document.getElementById('customer-id').textContent = selectedCustomer.id;
                            document.getElementById('customer-name').textContent = selectedCustomer.name || 'Không có';
                            document.getElementById('customer-phone').textContent = selectedCustomer.phone || 'Không có';
                            document.getElementById('customer-email').textContent = selectedCustomer.email || 'Không có';
                            document.getElementById('customer-address').textContent = selectedCustomer.address || 'Không có';
                            document.getElementById('customer-points').textContent = selectedCustomer.points || '0';

                            // Hiển thị phần thông tin chi tiết
                            document.getElementById('selected-customer-info').style.display = 'block';
                        });
                    });
                })
                .catch(error => {
                    console.error('Lỗi khi tìm kiếm khách hàng:', error);
                    document.getElementById('customer-search-results').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Đã xảy ra lỗi khi tìm kiếm khách hàng. Vui lòng thử lại.
                    </div>
                `;
                });
        });

        // Cập nhật sự kiện tìm kiếm khi nhấn Enter
        document.getElementById('search-customer').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('btn-search-customer').click();
            }
        });

        // Xử lý nút chọn khách hàng từ modal
        document.getElementById('btn-select-customer').addEventListener('click', function() {
            // Kiểm tra tab nào đang active
            const activeTab = document.querySelector('#customerTab .nav-link.active').id;

            if (activeTab === 'existing-customer-tab') {
                // Khách hàng đã có
                if (!selectedCustomer) {
                    showAlert('Vui lòng tìm và chọn khách hàng trước!', 'warning');
                    return;
                }
            } else if (activeTab === 'new-customer-tab') {
                // Khách hàng mới
                const firstName = document.getElementById('new-customer-firstname').value.trim();
                const lastName = document.getElementById('new-customer-lastname').value.trim();
                const customerPhone = document.getElementById('new-customer-phone').value.trim();

                if (!firstName || !lastName || !customerPhone) {
                    showAlert('Vui lòng nhập đủ họ, tên và số điện thoại của khách hàng!', 'warning');
                    return;
                }

                // Tạo thông tin khách hàng mới
                selectedCustomer = {
                    id: 'new_' + Date.now(),
                    name: `${firstName} ${lastName}`,
                    phone: customerPhone,
                    email: document.getElementById('new-customer-email').value.trim() || '',
                    street: document.getElementById('new-customer-street').value.trim() || '',
                    apartment: document.getElementById('new-customer-apartment').value.trim() || '',
                    city: document.getElementById('new-customer-city').value.trim() || '',
                    postcode: document.getElementById('new-customer-postcode').value.trim() || '',
                    points: 0,
                    isNew: true
                };
            } else if (activeTab === 'guest-customer-tab') {
                // Khách vãng lai
                const guestName = document.getElementById('guest-customer-name').value.trim() || 'Khách vãng lai';
                const guestPhone = document.getElementById('guest-customer-phone').value.trim() || '';

                selectedCustomer = {
                    id: 'guest_' + Date.now(),
                    name: guestName,
                    phone: guestPhone,
                    email: '',
                    points: 0,
                    isGuest: true
                };
            }

            // Đóng modal khách hàng
            bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();

            // Lưu thông tin khách hàng vào localStorage
            localStorage.setItem('posCustomer', JSON.stringify(selectedCustomer));

            // Cập nhật UI hiển thị khách hàng đã chọn
            document.getElementById('customer-name-display').textContent = selectedCustomer.name;
            document.getElementById('customer-phone-display').textContent = selectedCustomer.phone || 'Không có SĐT';
            document.getElementById('selected-customer-display').style.display = 'block';
            document.getElementById('select-customer-prompt').style.display = 'none';

            // Hiển thị thông báo
            showAlert(`Đã chọn khách hàng: <strong>${selectedCustomer.name}</strong>`, 'success');
        });

        // Nút thanh toán
        checkoutBtn.addEventListener('click', function() {
            if (cart.length === 0) {
                showAlert('Giỏ hàng đang trống!', 'warning');
                return;
            }

            // Kiểm tra xem đã chọn khách hàng chưa
            if (!selectedCustomer) {
                // Hiển thị modal chọn khách hàng trước
                const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
                customerModal.show();
                showAlert('Vui lòng chọn khách hàng trước khi thanh toán!', 'warning');
                return;
            }

            const totalAmount = calculateTotal();
            document.getElementById('modal-total').textContent = formatCurrency(totalAmount);

            // Reset tiền thối
            document.getElementById('received-amount').value = '';
            document.getElementById('change-amount').value = '';

            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();

            // Focus vào trường nhập tiền
            setTimeout(() => {
                document.getElementById('received-amount').focus();
            }, 500);
        });

        // Nút xem tóm tắt
        summaryBtn.addEventListener('click', function() {
            showSummary();
        });

        // Hàm hiển thị tóm tắt đơn hàng trong modal
        function showSummary() {
            if (cart.length === 0) {
                showAlert('Giỏ hàng đang trống!', 'warning');
                return;
            }

            const summaryItems = document.getElementById('summary-items');
            summaryItems.innerHTML = '';

            // Tính các giá trị tổng
            const subtotal = calculateSubtotal();
            const discountPercent = parseFloat(discountInput.value) || 0;
            const discountAmount = subtotal * (discountPercent / 100);
            const afterDiscount = subtotal - discountAmount;
            const tax = afterDiscount * 0.1;
            const total = afterDiscount + tax;

            // Hiển thị từng sản phẩm
            cart.forEach((item, index) => {
                const row = document.createElement('tr');

                // Thêm thông tin size và tùy chỉnh nếu có
                let itemDetails = item.name;
                if (item.size) {
                    itemDetails += `<br><small class="text-muted">Size: ${item.size}</small>`;
                }
                if (item.customizations) {
                    itemDetails += `<br><small class="text-muted">${item.customizations}</small>`;
                }

                row.innerHTML = `
            <td>${index + 1}</td>
            <td>${itemDetails}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-end">${formatCurrency(item.price)}</td>
            <td class="text-end">${formatCurrency(item.price * item.quantity)}</td>
        `;

                summaryItems.appendChild(row);
            });

            // Cập nhật các giá trị tổng
            document.getElementById('summary-subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('summary-discount').textContent = formatCurrency(discountAmount);
            document.getElementById('summary-tax').textContent = formatCurrency(tax);
            document.getElementById('summary-total').textContent = formatCurrency(total);

            // Hiển thị modal
            const summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));
            summaryModal.show();
        }

        // Nút tiến hành thanh toán từ modal summary
        document.getElementById('proceed-checkout').addEventListener('click', function() {
            const summaryModal = bootstrap.Modal.getInstance(document.getElementById('summaryModal'));
            summaryModal.hide();
            document.getElementById('checkout-btn').click();
        });

        // Xử lý số tiền nhận được và tiền thối
        document.getElementById('received-amount').addEventListener('input', function() {
            const receivedAmount = parseFloat(this.value) || 0;
            const totalAmount = calculateTotal();
            const changeAmount = receivedAmount - totalAmount;

            document.getElementById('change-amount').value = changeAmount >= 0 ? formatCurrency(changeAmount) : '0 đ';

            // Đổi màu nếu số tiền không đủ
            if (receivedAmount < totalAmount) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Nút xác nhận thanh toán
        document.getElementById('confirm-payment').addEventListener('click', function() {
            // Kiểm tra phương thức thanh toán được chọn
            const paymentMethodInput = document.querySelector('input[name="payment-method"]:checked');
            if (!paymentMethodInput) {
                showAlert('Vui lòng chọn phương thức thanh toán!', 'warning');
                return;
            }

            const paymentMethod = paymentMethodInput.value;

            // Kiểm tra số tiền nếu là tiền mặt
            if (paymentMethod === 'cash') {
                const receivedAmount = parseFloat(document.getElementById('received-amount').value) || 0;
                const totalAmount = calculateTotal();

                if (receivedAmount < totalAmount) {
                    showAlert('Số tiền nhận không đủ! Vui lòng kiểm tra lại.', 'warning');
                    return;
                }

                if (receivedAmount === 0) {
                    showAlert('Vui lòng nhập số tiền khách thanh toán!', 'warning');
                    return;
                }
            }

            // Đóng modal
            const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            paymentModal.hide();

            // Hiển thị đang xử lý
            showAlert('Đang xử lý đơn hàng...', 'info');

            // Chuẩn bị dữ liệu để gửi đến server
            const orderData = {
                items: cart.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity,
                    size: item.size || null,
                    customizations: item.notes || null
                })),
                subtotal: calculateSubtotal(),
                discount: parseFloat(discountInput.value) || 0,
                paymentMethod: paymentMethod,
                receivedAmount: paymentMethod === 'cash' ? parseFloat(document.getElementById('received-amount').value) || 0 : 0,
                orderType: document.getElementById('order-type').value || 'Tại quán',
                tableNumber: document.getElementById('table-number').value || null,
                customer: selectedCustomer || null
            };

            // Lưu dữ liệu vào localStorage (để dự phòng)
            localStorage.setItem('posOrderData', JSON.stringify(orderData));

            // Gửi dữ liệu đến server để xử lý
            fetch('<?= ADMINAPPURL ?>/pos-admins/process-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Xóa giỏ hàng
                        cart = [];
                        updateCartDisplay();
                        localStorage.removeItem('posCart');

                        // Chuyển hướng đến trang hóa đơn
                        window.location.href = '<?= ADMINAPPURL ?>/pos-admins/receipt.php?order_id=' + data.order_id;
                    } else {
                        // Hiển thị thông báo lỗi
                        console.error('Lỗi từ server:', data);
                        showAlert('Lỗi: ' + (data.message || 'Không xác định'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    showAlert('Lỗi khi xử lý đơn hàng! Vui lòng thử lại. ' + error.message, 'danger');
                });
        });

        // Nút xóa đơn hàng
        document.getElementById('clear-pos').addEventListener('click', function() {
            if (cart.length === 0) {
                showAlert('Giỏ hàng đang trống!', 'warning');
                return;
            }

            if (confirm('Bạn có chắc muốn xóa toàn bộ đơn hàng?')) {
                cart = [];
                localStorage.removeItem('posCart');
                updateCartDisplay();
                emptyCartMessage.style.display = '';
                checkoutBtn.disabled = true;
                summaryBtn.disabled = true;
                showAlert('Đã xóa toàn bộ đơn hàng', 'info');
            }
        });

        // Hàm cập nhật hiển thị giỏ hàng
        function updateCartDisplay() {
            // Xóa tất cả sản phẩm hiện tại trong UI
            const cartItems = document.querySelectorAll('#cart-items .list-group-item:not(#empty-cart)');
            cartItems.forEach(item => item.remove());

            // Hiển thị lại từ mảng cart
            if (cart.length > 0) {
                emptyCartMessage.style.display = 'none';
                cart.forEach(item => addCartItemUI(item));
                checkoutBtn.disabled = false;
                summaryBtn.disabled = false;
            } else {
                emptyCartMessage.style.display = '';
                checkoutBtn.disabled = true;
                summaryBtn.disabled = true;
            }

            // Cập nhật tổng tiền
            updateTotals();

            // Cập nhật số lượng sản phẩm trên header
            updateCartCount();
        }

        // Hàm thêm sản phẩm vào UI giỏ hàng
        function addCartItemUI(item) {
            // Kiểm tra dữ liệu đầu vào
            if (!item || !item.id) {
                console.error('Dữ liệu sản phẩm không hợp lệ:', item);
                showAlert('Lỗi khi thêm sản phẩm vào giỏ hàng', 'danger');
                return;
            }

            const listItem = document.createElement('li');
            listItem.className = 'list-group-item py-3';
            listItem.dataset.id = item.id;

            try {
                // Đảm bảo các giá trị hợp lệ
                const imageUrl = item.image ? `<?= ADMINAPPURL ?>/../images/${encodeURIComponent(item.image)}` : '<?= ADMINAPPURL ?>/../images/default-product.jpg';
                const itemName = item.name || 'Sản phẩm không xác định';
                const itemPrice = parseFloat(item.price) || 0;
                const itemQuantity = parseInt(item.quantity) || 1;

                // HTML cho mỗi mục sản phẩm
                listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <img src="${imageUrl}" alt="${itemName}" 
                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"
                        onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">${itemName}</h6>
                    ${item.size ? `<small class="text-muted d-block mb-1">Size: ${item.size}</small>` : ''}
                    ${item.notes ? `<small class="text-muted d-block mb-1">Ghi chú: ${item.notes}</small>` : ''}
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="input-group input-group-sm" style="width: 110px;">
                            <button type="button" class="btn btn-outline-secondary decrease-qty">-</button>
                            <input type="number" class="form-control text-center item-qty" value="${itemQuantity}" min="1" data-id="${item.id}">
                            <button type="button" class="btn btn-outline-secondary increase-qty">+</button>
                        </div>
                        <div class="text-end ms-3">
                            <div class="item-price">${formatCurrency(itemPrice * itemQuantity)}</div>
                            <small class="text-muted">${formatCurrency(itemPrice)} x ${itemQuantity}</small>
                        </div>
                    </div>
                </div>
                <div class="ms-2">
                    <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

                // Thêm vào giỏ hàng
                cartItemsContainer.insertBefore(listItem, emptyCartMessage);

                // Xử lý sự kiện tăng số lượng
                const increaseBtn = listItem.querySelector('.increase-qty');
                increaseBtn.addEventListener('click', function() {
                    const itemId = item.id;
                    const itemIndex = cart.findIndex(i => i.id === itemId);
                    if (itemIndex !== -1) {
                        cart[itemIndex].quantity += 1;
                        updateCartItemUI(cart[itemIndex]);
                        updateTotals();

                        // Lưu giỏ hàng vào localStorage
                        localStorage.setItem('posCart', JSON.stringify(cart));
                    }
                });

                // Xử lý sự kiện giảm số lượng
                const decreaseBtn = listItem.querySelector('.decrease-qty');
                decreaseBtn.addEventListener('click', function() {
                    const itemId = item.id;
                    const itemIndex = cart.findIndex(i => i.id === itemId);
                    if (itemIndex !== -1 && cart[itemIndex].quantity > 1) {
                        cart[itemIndex].quantity -= 1;
                        updateCartItemUI(cart[itemIndex]);
                        updateTotals();

                        // Lưu giỏ hàng vào localStorage
                        localStorage.setItem('posCart', JSON.stringify(cart));
                    }
                });

                // Xử lý sự kiện thay đổi số lượng thủ công
                const qtyInput = listItem.querySelector('.item-qty');
                qtyInput.addEventListener('change', function() {
                    const newQty = Math.max(1, parseInt(this.value) || 1);
                    this.value = newQty; // Đảm bảo hiển thị giá trị hợp lệ

                    const itemId = item.id;
                    const itemIndex = cart.findIndex(i => i.id === itemId);
                    if (itemIndex !== -1) {
                        cart[itemIndex].quantity = newQty;
                        updateCartItemUI(cart[itemIndex]);
                        updateTotals();

                        // Lưu giỏ hàng vào localStorage
                        localStorage.setItem('posCart', JSON.stringify(cart));
                    }
                });

                // Xử lý sự kiện xóa sản phẩm
                const removeBtn = listItem.querySelector('.remove-item');
                removeBtn.addEventListener('click', function() {
                    const itemId = item.id;
                    const itemName = cart.find(i => i.id === itemId)?.name || '';

                    cart = cart.filter(i => i.id !== itemId);
                    listItem.remove();
                    updateTotals();

                    // Lưu giỏ hàng vào localStorage
                    localStorage.setItem('posCart', JSON.stringify(cart));

                    if (cart.length === 0) {
                        emptyCartMessage.style.display = '';
                        checkoutBtn.disabled = true;
                        summaryBtn.disabled = true;
                    }

                    showAlert(`Đã xóa <strong>${itemName}</strong> khỏi đơn hàng`, 'warning');
                    updateCartCount();
                });

            } catch (error) {
                console.error('Lỗi khi thêm sản phẩm vào giỏ hàng UI:', error);
                showAlert('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng', 'danger');
            }
        }

        // Hàm cập nhật UI của một sản phẩm trong giỏ hàng
        function updateCartItemUI(item) {
            const listItem = document.querySelector(`.list-group-item[data-id="${item.id}"]`);
            if (!listItem) return;

            const quantityInput = listItem.querySelector('.item-qty');
            const priceElement = listItem.querySelector('.item-price');
            const smallPriceElement = listItem.querySelector('.text-muted');

            quantityInput.value = item.quantity;
            priceElement.textContent = formatCurrency(item.price * item.quantity);
            smallPriceElement.textContent = `${formatCurrency(item.price)} x ${item.quantity}`;

            updateCartCount();
        }

        // Hàm tính tổng tiền và cập nhật hiển thị
        function updateTotals() {
            const subtotal = calculateSubtotal();
            const discountPercent = parseFloat(discountInput.value) || 0;
            const discountAmount = subtotal * (discountPercent / 100);
            const afterDiscount = subtotal - discountAmount;
            const tax = afterDiscount * 0.1;
            const total = afterDiscount + tax;

            subtotalElement.textContent = formatCurrency(subtotal);
            taxElement.textContent = formatCurrency(tax);
            totalElement.textContent = formatCurrency(total);
        }

        // Hàm tính tổng tiền sản phẩm chưa tính thuế và giảm giá
        function calculateSubtotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        // Hàm tính tổng tiền sau thuế và giảm giá
        function calculateTotal() {
            const subtotal = calculateSubtotal();
            const discountPercent = parseFloat(discountInput.value) || 0;
            const discountAmount = subtotal * (discountPercent / 100);
            const afterDiscount = subtotal - discountAmount;
            const tax = afterDiscount * 0.1;
            return afterDiscount + tax;
        }

        // Định dạng hiển thị tiền tệ
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                })
                .format(amount)
                .replace('₫', 'đ');
        }
    });
</script>

<?php require "../layouts/footer.php"; ?>