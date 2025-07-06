<?php
require "../../config/config.php";
requireAdminLogin();

// Kiểm tra ID đơn hàng
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo '<div class="alert alert-danger">Không tìm thấy mã đơn hàng</div>';
    exit;
}

$order_id = (int)$_GET['order_id'];

try {
    // Lấy thông tin đơn hàng
    $order_query = $conn->prepare("SELECT o.*, u.user_name 
                                   FROM orders o
                                   LEFT JOIN users u ON o.user_id = u.ID
                                   WHERE o.ID = ?");
    $order_query->execute([$order_id]);
    $order = $order_query->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo '<div class="alert alert-danger">Đơn hàng không tồn tại</div>';
        exit;
    }

    // Lấy chi tiết đơn hàng
    $items_query = $conn->prepare("SELECT od.*, p.product_title, p.image 
                                   FROM order_details od
                                   LEFT JOIN product p ON od.product_id = p.ID
                                   WHERE od.order_id = ?");
    $items_query->execute([$order_id]);
    $items = $items_query->fetchAll(PDO::FETCH_ASSOC);

    // Xác định trạng thái và màu sắc
    $status_map = [
        'Pending' => ['text' => 'Đang xử lý', 'color' => 'warning', 'icon' => 'clock'],
        'Processing' => ['text' => 'Đang chuẩn bị', 'color' => 'info', 'icon' => 'cog'],
        'Shipped' => ['text' => 'Đang giao hàng', 'color' => 'primary', 'icon' => 'shipping-fast'],
        'Delivered' => ['text' => 'Đã giao', 'color' => 'success', 'icon' => 'check-circle'],
        'Cancelled' => ['text' => 'Đã hủy', 'color' => 'danger', 'icon' => 'times-circle']
    ];

    $status_info = $status_map[$order['status']] ?? ['text' => $order['status'], 'color' => 'secondary', 'icon' => 'circle'];

    // Format ngày đặt hàng
    $order_date = !empty($order['order_date']) ? date('d/m/Y H:i', strtotime($order['order_date'])) : 'N/A';
?>

    <div class="order-details">
        <!-- Thông tin đơn hàng -->
        <div class="row border-bottom pb-3 mb-3">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">Thông tin đơn hàng</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Mã đơn hàng:</td>
                        <td class="fw-bold">#<?= $order['ID'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ngày đặt:</td>
                        <td><?= $order_date ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Trạng thái:</td>
                        <td>
                            <span class="badge bg-<?= $status_info['color'] ?>">
                                <i class="fas fa-<?= $status_info['icon'] ?> me-1"></i>
                                <?= $status_info['text'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tổng tiền:</td>
                        <td class="fw-bold text-success"><?= number_format($order['payable_total_cost'], 0, ',', '.') ?> ₫</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">Thông tin khách hàng</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Họ tên:</td>
                        <td><?= htmlspecialchars($order['firstname'] . ' ' . $order['lastname']) ?></td>
                    </tr>
                    <?php if (!empty($order['user_name'])): ?>
                        <tr>
                            <td class="text-muted">Tài khoản:</td>
                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Điện thoại:</td>
                        <td><?= htmlspecialchars($order['phone']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td><?= htmlspecialchars($order['email'] ?? 'Không có') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Địa chỉ giao hàng -->
        <div class="border-bottom pb-3 mb-3">
            <h6 class="fw-bold mb-2">Địa chỉ giao hàng</h6>
            <p class="mb-0">
                <?= htmlspecialchars($order['streetaddress']) ?>
                <?= !empty($order['apartment']) ? ', ' . htmlspecialchars($order['apartment']) : '' ?>
                <br>
                <?= htmlspecialchars($order['towncity']) ?>
            </p>
        </div>

        <!-- Chi tiết sản phẩm -->
        <div>
            <h6 class="fw-bold mb-2">Sản phẩm đã đặt</h6>

            <?php if (count($items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../../assets/images/products/<?= htmlspecialchars($item['image']) ?>"
                                                    alt="<?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?>"
                                                    class="me-2" width="40">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'], 0, ',', '.') ?> ₫</td>
                                    <td class="text-end"><?= number_format($item['subtotal'], 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Tổng cộng:</td>
                                <td class="text-end"><?= number_format($order['payable_total_cost'], 0, ',', '.') ?> ₫</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Không tìm thấy chi tiết sản phẩm</div>
            <?php endif; ?>
        </div>
    </div>

<?php
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Có lỗi xảy ra: ' . $e->getMessage() . '</div>';
}
?>