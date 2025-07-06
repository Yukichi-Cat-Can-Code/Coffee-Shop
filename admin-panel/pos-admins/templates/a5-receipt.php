<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?= $orderMain['order_id'] ?></title>
    <style>
        @page {
            size: A5;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            max-width: 148mm;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th {
            background: #f2f2f2;
            text-align: left;
            padding: 8px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
        }

        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature div {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="header">
            <h2>COFFEE SHOP</h2>
            <p>123 Nguyễn Huệ, Quận 1, TP.HCM</p>
            <p>Tel: 028-1234-5678 | www.coffeeshop.vn</p>
            <h2>HÓA ĐƠN BÁN HÀNG</h2>
        </div>

        <div style="display: flex; justify-content: space-between;">
            <p><strong>Số hóa đơn:</strong> <?= $orderMain['order_id'] ?></p>
            <p><strong>Ngày:</strong> <?= date('d/m/Y', strtotime($orderMain['created_at'])) ?></p>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <p><strong>Thu ngân:</strong> <?= htmlspecialchars($orderMain['admin_name'] ?? $_SESSION['admin_name']) ?></p>
            <p><strong>Giờ:</strong> <?= date('H:i:s', strtotime($orderMain['created_at'])) ?></p>
        </div>

        <?php if ($customer): ?>
            <div style="background: #f9f9f9; padding: 10px; margin-bottom: 15px;">
                <p style="margin: 5px 0"><strong>Khách hàng:</strong> <?= htmlspecialchars($customer['user_name'] ?? '') ?></p>
                <?php if (!empty($customer['user_phone'])): ?>
                    <p style="margin: 5px 0"><strong>SĐT:</strong> <?= htmlspecialchars($customer['user_phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($customer['user_email'])): ?>
                    <p style="margin: 5px 0"><strong>Email:</strong> <?= htmlspecialchars($customer['user_email']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 45%;">Sản phẩm</th>
                    <th style="width: 10%; text-align: center;">SL</th>
                    <th style="width: 20%; text-align: right;">Đơn giá</th>
                    <th style="width: 20%; text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td style="text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="text-align: right;"><?= formatMoney($item['unit_price']) ?></td>
                        <td style="text-align: right;"><?= formatMoney($item['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table style="width: 60%; margin-left: auto;">
            <tr>
                <td style="text-align: right;">Tạm tính:</td>
                <td style="text-align: right; width: 100px;"><?= formatMoney($orderMain['total_amount']) ?></td>
            </tr>
            <tr>
                <td style="text-align: right;">Giảm giá:</td>
                <td style="text-align: right;"><?= formatMoney($orderMain['discount_amount']) ?></td>
            </tr>
            <tr>
                <td style="text-align: right;">Thuế VAT (10%):</td>
                <td style="text-align: right;"><?= formatMoney($orderMain['tax_amount']) ?></td>
            </tr>
            <tr>
                <td style="text-align: right;"><strong>Tổng cộng:</strong></td>
                <td style="text-align: right; font-weight: bold"><?= formatMoney($orderMain['final_amount']) ?></td>
            </tr>
        </table>

        <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($payment['method']) ?></p>

        <div class="signature">
            <div>
                <div class="signature-line"></div>
                <p>Người bán hàng</p>
            </div>
            <div>
                <div class="signature-line"></div>
                <p>Khách hàng</p>
            </div>
        </div>

        <div class="footer">
            <p>Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!</p>
        </div>
    </div>
</body>

</html>