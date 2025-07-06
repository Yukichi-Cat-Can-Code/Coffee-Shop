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
    // Lấy thông tin đơn hàng POS
    $order_query = $conn->prepare("SELECT po.*, a.admin_name 
                                   FROM pos_orders po
                                   LEFT JOIN admins a ON po.admin_id = a.ID
                                   WHERE po.order_id = ?");
    $order_query->execute([$order_id]);
    $order = $order_query->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo '<div class="alert alert-danger">Đơn hàng không tồn tại</div>';
        exit;
    }

    // Lấy chi tiết đơn hàng
    $items_query = $conn->prepare("SELECT pod.*, p.product_title, p.image 
                              FROM pos_order_items pod
                              LEFT JOIN product p ON pod.product_id = p.ID
                              WHERE pod.order_id = ?");
    $items_query->execute([$order_id]);
    $items = $items_query->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin thanh toán
    $payment_query = $conn->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
    $payment_query->execute([$order_id]);
    $payment = $payment_query->fetch(PDO::FETCH_ASSOC);

    // Xác định trạng thái và màu sắc
    $status_map = [
        'Mới' => ['color' => 'primary', 'icon' => 'file-alt'],
        'Đang chế biến' => ['color' => 'info', 'icon' => 'blender'],
        'Sẵn sàng' => ['color' => 'warning', 'icon' => 'clipboard-check'],
        'Đã phục vụ' => ['color' => 'success', 'icon' => 'utensils'],
        'Hoàn thành' => ['color' => 'success', 'icon' => 'check-circle'],
        'Đã hủy' => ['color' => 'danger', 'icon' => 'times-circle']
    ];

    $status_info = $status_map[$order['order_status']] ?? ['color' => 'secondary', 'icon' => 'circle'];

    // Format thời gian tạo đơn
    $created_date = !empty($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'N/A';
?>

    <div class="pos-order-details">
        <!-- Thông tin đơn hàng -->
        <div class="row border-bottom pb-3 mb-3">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">Thông tin đơn hàng</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">Mã đơn hàng:</td>
                        <td class="fw-bold">#<?= $order['order_id'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Thời gian tạo:</td>
                        <td><?= $created_date ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Loại đơn hàng:</td>
                        <td><span class="badge bg-primary rounded-pill"><?= $order['order_type'] ?></span></td>
                    </tr>
                    <?php if (!empty($order['table_number'])): ?>
                        <tr>
                            <td class="text-muted">Bàn số:</td>
                            <td><?= $order['table_number'] ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Trạng thái:</td>
                        <td>
                            <span class="badge bg-<?= $status_info['color'] ?>">
                                <i class="fas fa-<?= $status_info['icon'] ?> me-1"></i>
                                <?= $order['order_status'] ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">Thanh toán</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">Thu ngân:</td>
                        <td><?= htmlspecialchars($order['admin_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phương thức:</td>
                        <td>
                            <?php if (!empty($payment)): ?>
                                <?php
                                $payment_icon = 'money-bill-wave';
                                $payment_class = 'success';

                                if (stripos($payment['payment_method'], 'thẻ') !== false || stripos($payment['payment_method'], 'card') !== false) {
                                    $payment_icon = 'credit-card';
                                    $payment_class = 'info';
                                } elseif (
                                    stripos($payment['payment_method'], 'momo') !== false ||
                                    stripos($payment['payment_method'], 'zalopay') !== false ||
                                    stripos($payment['payment_method'], 'ví') !== false
                                ) {
                                    $payment_icon = 'mobile-alt';
                                    $payment_class = 'primary';
                                }
                                ?>
                                <span class="badge bg-<?= $payment_class ?> rounded-pill">
                                    <i class="fas fa-<?= $payment_icon ?> me-1"></i>
                                    <?= htmlspecialchars($payment['payment_method']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">
                                    <i class="fas fa-question-circle me-1"></i>
                                    Không có thông tin
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Trạng thái:</td>
                        <td>
                            <span class="badge bg-<?= $order['payment_status'] == 'Đã thanh toán' ? 'success' : ($order['payment_status'] == 'Đã hủy' ? 'danger' : 'warning') ?>">
                                <?= $order['payment_status'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Thời gian thanh toán:</td>
                        <td><?= !empty($payment) ? date('d/m/Y H:i', strtotime($payment['payment_date'])) : 'N/A' ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($order['customer_name']) || !empty($order['customer_phone']) || !empty($order['customer_address'])): ?>
            <!-- Thông tin khách hàng -->
            <div class="border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-2">Thông tin khách hàng</h6>
                <table class="table table-sm table-borderless mb-0">
                    <?php if (!empty($order['customer_name'])): ?>
                        <tr>
                            <td class="text-muted" width="20%">Họ tên:</td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($order['customer_phone'])): ?>
                        <tr>
                            <td class="text-muted">Số điện thoại:</td>
                            <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($order['customer_address'])): ?>
                        <tr>
                            <td class="text-muted">Địa chỉ:</td>
                            <td><?= htmlspecialchars($order['customer_address']) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- Chi tiết sản phẩm -->
        <div>
            <h6 class="fw-bold mb-2">Sản phẩm đã đặt</h6>

            <?php if (count($items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">SL</th>
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
                                                <img src="../../images/<?= htmlspecialchars($item['image']) ?>"
                                                    alt="<?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?>"
                                                    class="me-2" width="40">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($item['product_title'] ?? $item['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?> ₫</td>
                                    <td class="text-end"><?= number_format($item['subtotal'], 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Tạm tính:</td>
                                <td class="text-end"><?= number_format($order['total_amount'], 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end">Giảm giá:</td>
                                    <td class="text-end text-danger">-<?= number_format($order['discount_amount'], 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end">Thuế (VAT):</td>
                                    <td class="text-end"><?= number_format($order['tax_amount'], 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Tổng cộng:</td>
                                <td class="text-end"><?= number_format($order['final_amount'], 0, ',', '.') ?> ₫</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Không tìm thấy chi tiết sản phẩm</div>
            <?php endif; ?>
        </div>

        <?php if (!empty($order['order_notes'])): ?>
            <!-- Ghi chú đơn hàng -->
            <div class="mt-3 pt-3 border-top">
                <h6 class="fw-bold mb-2">Ghi chú</h6>
                <div class="alert alert-light">
                    <?= nl2br(htmlspecialchars($order['order_notes'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Có lỗi xảy ra: ' . $e->getMessage() . '</div>';
}
?>