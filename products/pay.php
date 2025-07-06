<?php

require "../config/config.php";
require "../auth/not-access.php";

// Kiểm tra đăng nhập đơn giản
if (!function_exists('session') || !session()->isLoggedIn()) {
    header("location:" . APPURL . "/auth/login.php");
    exit;
}

// Kiểm tra có tổng tiền thanh toán trong session chưa
if (!session()->get('payable_total_cost')) {
    header("location:" . APPURL . "");
    exit;
}

// Lấy tổng tiền VNĐ từ session
$payable_total_cost_vnd = session()->get('payable_total_cost');

$usd_rate = 25000; // 1 USD = 25,000 VNĐ

// Chuyển đổi sang USD, làm tròn 2 số thập phân
$payable_total_cost_usd = round($payable_total_cost_vnd / $usd_rate, 2);

$user_id = session()->getCurrentUser()['id'];

require "../includes/header.php";
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3 mt-5">
            <h2 class='text-center mt-5'>Thanh toán PayPal</h2>
            <p class="text-center mb-3" style="font-size:1.1em;">
                Số tiền thanh toán: <b><?php echo number_format($payable_total_cost_vnd, 0, ',', '.'); ?> VNĐ</b>
                (~<?php echo $payable_total_cost_usd; ?> USD)
            </p>
            <script src="https://www.paypal.com/sdk/js?client-id=AXtp5Q665NCz0HSUdXYwTANzh7W-NxpLpdMNKAFBve854Drj-VmpQDGkbDxEvJpNf8KRn58n1MiMOGmA&currency=USD"></script>
            <div id="paypal-button-container"></div>

            <script>
                paypal.Buttons({
                    createOrder: (data, actions) => {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: '<?php echo $payable_total_cost_usd; ?>'
                                }
                            }]
                        });
                    },
                    onApprove: (data, actions) => {
                        return actions.order.capture().then(function(orderData) {
                            window.location.href = '../index.php';
                        });
                    }
                }).render('#paypal-button-container');
            </script>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>