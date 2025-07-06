<?php
require "../config/config.php";
require "../auth/not-access.php";
require "../includes/header.php";
?>

<section class="ftco-section">
	<div class="container">
		<div class="row">
			<div class="col-md-8 offset-md-2 ftco-animate">
				<h3 class="mb-4 billing-heading">Thông tin đặt hàng</h3>
				<div id="order-message"></div>
				<form id="checkout-form" class="billing-form p-3 p-md-4 bg-light rounded" autocomplete="off">
					<div class="form-group">
						<label for="firstname">Họ *</label>
						<input type="text" name="firstname" id="firstname" class="form-control">
					</div>
					<div class="form-group">
						<label for="lastname">Tên *</label>
						<input type="text" name="lastname" id="lastname" class="form-control">
					</div>
					<div class="form-group">
						<label for="streetaddress">Địa chỉ *</label>
						<input type="text" name="streetaddress" id="streetaddress" class="form-control">
					</div>
					<div class="form-group">
						<label for="apartment">Căn hộ, tầng, v.v. (không bắt buộc)</label>
						<input type="text" name="apartment" id="apartment" class="form-control">
					</div>
					<div class="form-group">
						<label for="towncity">Thành phố *</label>
						<input type="text" name="towncity" id="towncity" class="form-control">
					</div>
					<div class="form-group">
						<label for="postcodezip">Mã bưu điện *</label>
						<input type="text" name="postcodezip" id="postcodezip" class="form-control">
					</div>
					<div class="form-group">
						<label for="phone">Số điện thoại *</label>
						<input type="text" name="phone" id="phone" class="form-control">
					</div>
					<div class="form-group">
						<label for="emailaddress">Email *</label>
						<input type="email" name="emailaddress" id="emailaddress" class="form-control">
					</div>
					<button type="submit" class="btn btn-primary px-4 py-2">Đặt hàng</button>
				</form>
			</div>
		</div>
	</div>
</section>

<script>
	document.getElementById('checkout-form').addEventListener('submit', async function(e) {
		e.preventDefault();
		const form = this;
		const formData = new FormData(form);
		const msgDiv = document.getElementById('order-message');
		msgDiv.innerHTML = '';

		try {
			const response = await fetch('ajax-checkout.php', {
				method: 'POST',
				body: formData
			});
			const res = await response.json();
			console.log(res); // Log response ra console

			if (res.success) {
				msgDiv.innerHTML = '<div class="alert alert-success">Đặt hàng thành công! Đang chuyển hướng...</div>';
				setTimeout(() => window.location = 'pay.php', 1200);
			} else {
				msgDiv.innerHTML = '<div class="alert alert-danger">' + (res.message || 'Có lỗi xảy ra!') + '</div>';
			}
		} catch (err) {
			console.error('Lỗi fetch:', err);
			msgDiv.innerHTML = '<div class="alert alert-danger">Không thể kết nối máy chủ hoặc lỗi không xác định!</div>';
		}
	});
</script>

<?php require "../includes/footer.php"; ?>