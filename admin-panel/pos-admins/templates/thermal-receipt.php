<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Hóa đơn nhỏ #<?= $orderMain['order_id'] ?></title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            margin: 5mm;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
        }

        .center {
            text-align: center;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        h3,
        h4,
        p {
            margin: 3px 0;
        }
    </style>
</head>

<body>
    <div class="center">
        <h3>COFFEE SHOP</h3>
        <p>123 Nguyễn Huệ, Quận 1, TP.HCM</p>
        <p>Tel: 028-1234-5678</p>
        <h4>HÓA ĐƠN BÁN HÀNG</h4>
        <p>Số: <?= $orderMain['order_id'] ?></p>
        <p>Ngày: <?= date('d/m/Y', strtotime($orderMain['created_at'])) ?></p>
        <p>Giờ: <?= date('H:i:s', strtotime($orderMain['created_at'])) ?></p>
        <p>Thu ngân: <?= htmlspecialchars($orderMain['admin_name'] ?? $_SESSION['admin_name']) ?></p>

        <?php if ($customer): ?>
            <p>Khách hàng: <?= htmlspecialchars($customer['user_name'] ?? '') ?></p>
            <?php if (!empty($customer['user_phone'])): ?>
                <p>SĐT: <?= htmlspecialchars($customer['user_phone']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <th style="text-align: left">Sản phẩm</th>
            <th style="text-align: right">SL</th>
            <th style="text-align: right">Giá</th>
            <th style="text-align: right">T.Tiền</th>
        </tr>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td style="text-align: left"><?= htmlspecialchars($item['product_name']) ?></td>
                <td style="text-align: right"><?= $item['quantity'] ?></td>
                <td style="text-align: right"><?= formatMoney($item['unit_price']) ?></td>
                <td style="text-align: right"><?= formatMoney($item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="divider"></div>

    <div>
        <div class="row">
            <span>Tạm tính:</span>
            <span><?= formatMoney($orderMain['total_amount']) ?></span>
        </div>
        <div class="row">
            <span>Giảm giá:</span>
            <span><?= formatMoney($orderMain['discount_amount']) ?></span>
        </div>
        <div class="row">
            <span>Thuế (10%):</span>
            <span><?= formatMoney($orderMain['tax_amount']) ?></span>
        </div>
        <div class="row" style="font-weight: bold">
            <span>Tổng cộng:</span>
            <span><?= formatMoney($orderMain['final_amount']) ?></span>
        </div>
        <p>Thanh toán: <?= htmlspecialchars($payment['method']) ?></p>
    </div>

    <div class="divider"></div>

    <div class="center" style="margin-top: 10px">
        <p>Cảm ơn quý khách!</p>
        <p>www.coffeeshop.vn</p>
        <?php if ($preview): ?>
            <button onclick="window.print()" style="margin-top:10px; padding:5px 10px">In Ngay</button>
        <?php endif; ?>
    </div>
</body>

</html>