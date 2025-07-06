<?php
require "../../config/config.php";
requireAdminLogin();

$error_message = "";
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
    exit;
}

// Lấy thông tin đơn hàng hiện tại để hiển thị
$stmt = $conn->prepare("SELECT o.*, u.user_name 
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.ID
                        WHERE o.ID = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
    exit;
}

// Lấy chi tiết đơn hàng
$details_stmt = $conn->prepare("SELECT od.*, p.product_title, p.image 
                              FROM order_details od
                              LEFT JOIN product p ON od.product_id = p.ID
                              WHERE od.order_id = ?");
$details_stmt->execute([$order_id]);
$order_details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý form khi submit
if (isset($_POST['submit'])) {
    $status = $_POST['status'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $towncity = trim($_POST['towncity']);
    $streetaddress = trim($_POST['streetaddress']);
    $phone = trim($_POST['phone']);
    $apartment = trim($_POST['apartment'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = [];

    // Kiểm tra trạng thái
    if (empty($status)) {
        $errors[] = "Vui lòng chọn trạng thái đơn hàng";
    }

    // Kiểm tra các trường bắt buộc
    if (empty($firstname)) {
        $errors[] = "Họ không được để trống";
    }

    if (empty($lastname)) {
        $errors[] = "Tên không được để trống";
    }

    if (empty($phone)) {
        $errors[] = "Số điện thoại không được để trống";
    }

    if (empty($streetaddress)) {
        $errors[] = "Địa chỉ không được để trống";
    }

    if (empty($towncity)) {
        $errors[] = "Thành phố/Tỉnh không được để trống";
    }

    // Nếu không có lỗi, cập nhật dữ liệu
    if (empty($errors)) {
        try {
            $update = $conn->prepare("UPDATE orders SET 
                status = ?,
                firstname = ?,
                lastname = ?,
                streetaddress = ?,
                towncity = ?,
                apartment = ?,
                phone = ?,
                email = ?
                WHERE ID = ?");

            $update->execute([
                $status,
                $firstname,
                $lastname,
                $streetaddress,
                $towncity,
                $apartment,
                $phone,
                $email,
                $order_id
            ]);

            session()->setFlash('success_message', "Đơn hàng #$order_id đã được cập nhật thành công");
            redirect(ADMINAPPURL . "/orders-admins/show-orders.php");
            exit;
        } catch (PDOException $e) {
            $error_message = "Lỗi cập nhật: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

require "../layouts/header.php";

// Định nghĩa mapping trạng thái
$status_map = [
    'Pending' => ['text' => 'Đang xử lý', 'color' => 'warning', 'icon' => 'clock'],
    'Processing' => ['text' => 'Đang chuẩn bị', 'color' => 'info', 'icon' => 'cog'],
    'Shipped' => ['text' => 'Đang giao hàng', 'color' => 'primary', 'icon' => 'shipping-fast'],
    'Delivered' => ['text' => 'Đã giao', 'color' => 'success', 'icon' => 'check-circle'],
    'Cancelled' => ['text' => 'Đã hủy', 'color' => 'danger', 'icon' => 'times-circle']
];

// Format ngày đặt hàng
$order_date = !empty($order['order_date']) ? date('d/m/Y H:i', strtotime($order['order_date'])) : 'N/A';
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
                            <h1 class="h3 mb-1 fw-bold text-gray-800">Cập nhật đơn hàng #<?= $order_id ?></h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 bg-transparent p-0">
                                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="show-orders.php" class="text-decoration-none">Đơn hàng</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Cập nhật đơn hàng</li>
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%" class="text-muted">Mã đơn hàng:</td>
                                            <td><strong>#<?= $order_id ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Ngày đặt hàng:</td>
                                            <td><?= $order_date ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tổng tiền:</td>
                                            <td><strong class="text-success"><?= number_format($order['payable_total_cost'], 0, ',', '.') ?> ₫</strong></td>
                                        </tr>
                                        <?php if (!empty($order['user_name'])): ?>
                                            <tr>
                                                <td class="text-muted">Tài khoản:</td>
                                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h5 class="card-title mb-0">Chi tiết sản phẩm</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($order_details) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Sản phẩm</th>
                                                        <th class="text-center">SL</th>
                                                        <th class="text-end">Thành tiền</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_details as $item): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <?php if (!empty($item['image'])): ?>
                                                                        <img src="../../images/<?= htmlspecialchars($item['image']) ?>"
                                                                            alt="<?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?>"
                                                                            class="me-2" width="30" height="30">
                                                                    <?php endif; ?>
                                                                    <span><?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="text-center"><?= $item['quantity'] ?></td>
                                                            <td class="text-end"><?= number_format($item['subtotal'], 0, ',', '.') ?> ₫</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 text-center">
                                            <span class="text-muted">Không có thông tin chi tiết sản phẩm</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="update-orders.php?order_id=<?= $order_id ?>" method="POST">
                        <div class="card border mb-4">
                            <div class="card-header bg-light py-2">
                                <h5 class="card-title mb-0">Cập nhật trạng thái đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold mb-3">Chọn trạng thái mới</label>
                                    <div class="status-selector">
                                        <div class="form-check form-check-inline status-option">
                                            <input class="form-check-input" type="radio" name="status" id="status_pending" value="Pending"
                                                <?= ($order['status'] == 'Pending') ? 'checked' : '' ?>>
                                            <label class="form-check-label status-label status-pending" for="status_pending">
                                                <i class="fas fa-clock me-1"></i> Đang xử lý
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline status-option">
                                            <input class="form-check-input" type="radio" name="status" id="status_processing" value="Processing"
                                                <?= ($order['status'] == 'Processing') ? 'checked' : '' ?>>
                                            <label class="form-check-label status-label status-processing" for="status_processing">
                                                <i class="fas fa-cog me-1"></i> Đang chuẩn bị
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline status-option">
                                            <input class="form-check-input" type="radio" name="status" id="status_shipped" value="Shipped"
                                                <?= ($order['status'] == 'Shipped') ? 'checked' : '' ?>>
                                            <label class="form-check-label status-label status-shipped" for="status_shipped">
                                                <i class="fas fa-shipping-fast me-1"></i> Đang giao hàng
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline status-option">
                                            <input class="form-check-input" type="radio" name="status" id="status_delivered" value="Delivered"
                                                <?= ($order['status'] == 'Delivered') ? 'checked' : '' ?>>
                                            <label class="form-check-label status-label status-delivered" for="status_delivered">
                                                <i class="fas fa-check-circle me-1"></i> Đã giao
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline status-option">
                                            <input class="form-check-input" type="radio" name="status" id="status_cancelled" value="Cancelled"
                                                <?= ($order['status'] == 'Cancelled') ? 'checked' : '' ?>>
                                            <label class="form-check-label status-label status-cancelled" for="status_cancelled">
                                                <i class="fas fa-times-circle me-1"></i> Đã hủy
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($order['status'] !== 'Delivered' && $order['status'] !== 'Cancelled'): ?>
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Mẹo:</strong> Thay đổi trạng thái đơn hàng sẽ tự động gửi email thông báo đến khách hàng.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card border mb-4">
                            <div class="card-header bg-light py-2">
                                <h5 class="card-title mb-0">Thông tin khách hàng và địa chỉ giao hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="firstname" class="form-label">Họ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="firstname" name="firstname"
                                            value="<?= htmlspecialchars($order['firstname'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lastname" class="form-label">Tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lastname" name="lastname"
                                            value="<?= htmlspecialchars($order['lastname'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="<?= htmlspecialchars($order['phone'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= htmlspecialchars($order['email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="towncity" class="form-label">Thành phố/Tỉnh <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="towncity" name="towncity"
                                            value="<?= htmlspecialchars($order['towncity'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="streetaddress" class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="streetaddress" name="streetaddress" rows="3"><?= trim(htmlspecialchars($order['streetaddress'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apartment" class="form-label">Căn hộ/Tòa nhà</label>
                                        <input type="text" class="form-control" id="apartment" name="apartment"
                                            value="<?= htmlspecialchars($order['apartment'] ?? '') ?>">
                                        <small class="form-text text-muted">Không bắt buộc</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="show-orders.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" name="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Cập nhật đơn hàng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

    .status-selector .form-check-input {
        position: absolute;
        opacity: 0;
    }

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

<?php require "../layouts/footer.php"; ?>