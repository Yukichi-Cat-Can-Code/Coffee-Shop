<?php
require "../../config/config.php";
requireAdminLogin();

$orderID = $_GET['order_id'] ?? null;

// Nếu không có order_id, chuyển về trang POS
if (!$orderID) {
    session()->setFlash('error_message', "Không tìm thấy đơn hàng!");
    redirect(ADMINAPPURL . "/pos-admins/index.php");
    exit;
}

// Lấy thông tin đơn hàng cơ bản để hiển thị
try {
    $orderStmt = $conn->prepare("SELECT o.order_id, o.created_at, a.admin_name, o.total_amount, o.discount_amount, o.tax_amount, o.final_amount  
                              FROM pos_orders o 
                              LEFT JOIN admins a ON o.admin_id = a.ID 
                              WHERE o.order_id = :order_id");
    $orderStmt->bindParam(':order_id', $orderID);
    $orderStmt->execute();

    if ($orderStmt->rowCount() == 0) {
        session()->setFlash('error_message', "Không tìm thấy đơn hàng!");
        redirect(ADMINAPPURL . "/pos-admins/index.php");
        exit;
    }

    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    session()->setFlash('error_message', "Lỗi khi truy vấn: " . $e->getMessage());
    redirect(ADMINAPPURL . "/pos-admins/index.php");
    exit;
}

require "../layouts/header.php";

// Format tiền tệ
function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Thông báo thành công -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="mb-3 text-success">Thanh Toán Thành Công!</h2>

                    <!-- Thông tin đơn hàng -->
                    <div class="card bg-light mb-4 mx-auto" style="max-width: 400px;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Mã đơn hàng:</span>
                                <span class="fw-bold">#<?= $order['order_id'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ngày:</span>
                                <span><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Nhân viên:</span>
                                <span><?= $order['admin_name'] ?? $_SESSION['admin_name'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tổng cộng:</span>
                                <span class="fw-bold"><?= formatMoney($order['final_amount']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Nút in hóa đơn -->
                    <div class="mb-4">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-print me-2"></i> In hóa đơn
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="printReceipt('a5')">Hóa đơn khổ A5</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="printReceipt('thermal')">Hóa đơn nhỏ (80mm)</a></li>
                            </ul>
                        </div>
                        <a href="<?= ADMINAPPURL ?>/pos-admins/index.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại
                        </a>
                    </div>

                    <!-- Lựa chọn định dạng xem trước -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-eye me-2"></i> Xem trước hóa đơn</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" data-receipt-format="a5">Khổ A5</button>
                                <button type="button" class="btn btn-outline-secondary" data-receipt-format="thermal">Khổ 80mm</button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="height: 500px;">
                            <iframe id="previewFrame" src="print-receipt.php?order_id=<?= $orderID ?>&format=a5&preview=1"
                                style="width:100%; height:100%; border:none;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // In hóa đơn theo định dạng
    function printReceipt(format) {
        const printWindow = window.open('print-receipt.php?order_id=<?= $orderID ?>&format=' + format, '_blank');
        if (!printWindow) {
            alert('Vui lòng cho phép trình duyệt mở popup để in hóa đơn!');
        }
    }

    // Thay đổi định dạng xem trước
    function changePreview(format) {
        // Cập nhật iframe
        document.getElementById('previewFrame').src = 'print-receipt.php?order_id=<?= $orderID ?>&format=' + format + '&preview=1';

        // Cập nhật trạng thái nút
        document.querySelectorAll('[data-receipt-format]').forEach(btn => {
            if (btn.getAttribute('data-receipt-format') === format) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    // Thêm sự kiện cho các nút thay đổi khổ giấy
    document.addEventListener('DOMContentLoaded', function() {
        const formatButtons = document.querySelectorAll('[data-receipt-format]');
        formatButtons.forEach(button => {
            button.addEventListener('click', function() {
                const format = this.getAttribute('data-receipt-format');
                changePreview(format);
            });
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>