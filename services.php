<?php
ob_start();
require "includes/header.php";

// Biến lưu thông báo lỗi
$errors = [];

// Xử lý form đăng ký tư vấn nếu được gửi
if (isset($_POST['submit_inquiry'])) {
  // Validate dữ liệu nhập vào - sử dụng cách an toàn hơn thay vì FILTER_SANITIZE_STRING (deprecated)
  $customer_name = trim(htmlspecialchars($_POST['customer_name'] ?? ''));
  $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_VALIDATE_EMAIL);
  $customer_phone = trim(htmlspecialchars($_POST['customer_phone'] ?? ''));
  $service_type = trim(htmlspecialchars($_POST['service_type'] ?? ''));
  $customer_message = trim(htmlspecialchars($_POST['customer_message'] ?? ''));

  // Kiểm tra lỗi
  if (empty($customer_name)) {
    $errors['customer_name'] = "Vui lòng nhập tên của bạn";
  }

  if (!$customer_email) {
    $errors['customer_email'] = "Email không hợp lệ";
  }

  if (!preg_match('/^[0-9]{10,11}$/', $customer_phone)) {
    $errors['customer_phone'] = "Số điện thoại phải có 10-11 chữ số";
  }

  if (empty($service_type)) {
    $errors['service_type'] = "Vui lòng chọn dịch vụ";
  }

  if (empty($customer_message)) {
    $errors['customer_message'] = "Vui lòng nhập yêu cầu của bạn";
  }

  // Nếu không có lỗi, xử lý form
  if (empty($errors)) {
    // Có thể thêm code lưu vào database ở đây

    // Giả lập thành công
    $inquiry_success = true;

    // Redirect để tránh gửi lại form khi refresh trang
    if ($inquiry_success) {
      header("Location: " . APPURL . "/services.php?inquiry_sent=1");
      exit;
    }
  }
}

// Đảm bảo bất kỳ lỗi header nào được hiển thị trước khi bắt đầu xuất HTML
ob_end_flush();
?>

<!-- Banner Section - Cải thiện banner -->
<section class="home-slider owl-carousel">
  <div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg); background-position: center center;" data-stellar-background-ratio="0.5">
    <div class="overlay" style="opacity: 0.7;"></div>
    <div class="container">
      <div class="row slider-text justify-content-center align-items-center">
        <div class="col-md-7 col-sm-12 text-center ftco-animate">
          <h1 class="mb-3 mt-5 bread text-shadow">Dịch Vụ</h1>
          <p class="breadcrumbs text-shadow"><span class="mr-2"><a href="<?php echo APPURL; ?>/index.php">Trang Chủ</a></span> <span>Dịch Vụ</span></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Thêm style cho text-shadow -->
<style>
  .text-shadow {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
  }

  /* Cải thiện dropdown select với nền tối */
  .form-control {
    background-color: rgba(255, 255, 255, 0.9) !important;
    color: #000 !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
  }

  .select-wrap .form-control option {
    color: #000;
    background-color: #fff;
  }

  /* Hiển thị thông báo lỗi */
  .error-message {
    color: #ff6b6b;
    font-size: 0.875rem;
    margin-top: 5px;
  }

  /* Đánh dấu trường có lỗi */
  .is-invalid {
    border-color: #ff6b6b !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
  }

  /* PHỐI MÀU TỐI CHỦ ĐỀ CÀ PHÊ */
  .ftco-services {
    background-color: #1a0d00;
    padding-top: 7em;
    padding-bottom: 7em;
    position: relative;
  }

  .ftco-services .heading-section .subheading {
    color: #c49b63;
  }

  .ftco-services .heading-section h2 {
    color: #fff;
  }

  .ftco-services .heading-section p {
    color: rgba(255, 255, 255, 0.7);
  }

  .services .media-body h3.heading {
    color: #c49b63;
    font-weight: 600;
    font-size: 20px;
  }

  .services .media-body p {
    color: rgba(255, 255, 255, 0.7);
  }

  .services .icon {
    background: #3f2305;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 2px solid #c49b63;
  }

  .services .icon span {
    color: #c49b63;
    font-size: 30px;
  }

  /* Tông màu chung cho các phần khác */
  .subheading {
    font-weight: 600;
    letter-spacing: 1px;
    color: #c49b63;
    margin-bottom: 15px;
  }
</style>

<!-- Dịch vụ cơ bản với giao diện cải tiến -->
<section class="ftco-section ftco-services" style="background-image: url(<?php echo APPURL; ?>/images/bg_4.jpg); background-attachment: fixed; background-size: cover;">
  <div class="overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 8, 2, 0.92);"></div>
  <div class="container position-relative">
    <div class="row justify-content-center mb-5 pb-3">
      <div class="col-md-7 heading-section ftco-animate text-center">
        <span class="subheading">Dịch Vụ Của Chúng Tôi</span>
        <h2 class="mb-4">Trải Nghiệm Đẳng Cấp</h2>
        <p>Chúng tôi cung cấp những dịch vụ tốt nhất, đảm bảo sự hài lòng cho mọi khách hàng.</p>
      </div>
    </div>
    <div class="row">
      <!-- Dịch vụ 1: Đặt hàng dễ dàng -->
      <div class="col-md-4 ftco-animate">
        <div class="media d-block text-center block-6 services">
          <div class="icon d-flex justify-content-center align-items-center mb-5">
            <span class="flaticon-choices"></span>
          </div>
          <div class="media-body">
            <h3 class="heading">Đặt Hàng Dễ Dàng</h3>
            <p>Đặt hàng nhanh chóng qua website, ứng dụng di động hoặc gọi điện trực tiếp. Giao diện thân thiện, dễ sử dụng giúp bạn dễ dàng lựa chọn món yêu thích.</p>
          </div>
        </div>
      </div>

      <!-- Dịch vụ 2: Giao hàng nhanh chóng -->
      <div class="col-md-4 ftco-animate">
        <div class="media d-block text-center block-6 services">
          <div class="icon d-flex justify-content-center align-items-center mb-5">
            <span class="flaticon-delivery-truck"></span>
          </div>
          <div class="media-body">
            <h3 class="heading">Giao Hàng Nhanh Chóng</h3>
            <p>Dịch vụ giao hàng trong vòng 30 phút giúp bạn thưởng thức đồ uống và món ăn nóng hổi, thơm ngon tại nhà hoặc văn phòng. Miễn phí giao hàng trong bán kính 5km.</p>
          </div>
        </div>
      </div>

      <!-- Dịch vụ 3: Sản phẩm chất lượng cao -->
      <div class="col-md-4 ftco-animate">
        <div class="media d-block text-center block-6 services">
          <div class="icon d-flex justify-content-center align-items-center mb-5">
            <span class="flaticon-coffee-bean"></span>
          </div>
          <div class="media-body">
            <h3 class="heading">Sản Phẩm Chất Lượng Cao</h3>
            <p>Chúng tôi cam kết sử dụng những nguyên liệu tươi ngon nhất, được chọn lọc kỹ càng từ các nhà cung cấp uy tín để đảm bảo chất lượng vượt trội.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Phần code cho dịch vụ nổi bật - giữ nguyên phần này -->
<section class="ftco-section">
  <div class="container">
    <!-- Tiêu đề section -->
    <div class="row justify-content-center mb-5 pb-3">
      <div class="col-md-7 heading-section ftco-animate text-center">
        <span class="subheading">Khám Phá</span>
        <h2 class="mb-4">Dịch Vụ Nổi Bật</h2>
        <p>Ngoài việc phục vụ những tách cà phê thơm ngon, Artisan Coffee còn mang đến nhiều dịch vụ đặc biệt để đáp ứng nhu cầu đa dạng của khách hàng.</p>
      </div>
    </div>

    <div class="row">
      <!-- Dịch vụ nổi bật - giữ nguyên phần này -->
      <!-- ... -->
    </div>
  </div>
</section>

<!-- Form đăng ký tư vấn với giao diện cải tiến -->
<section class="ftco-section img" style="background-image: url(<?php echo APPURL; ?>/images/bg_2.jpg);" data-stellar-background-ratio="0.5">
  <div class="overlay" style="opacity: 0.85; background: rgba(0, 0, 0, 0.75);"></div>

  <div class="container position-relative">
    <!-- Hiển thị thông báo thành công nếu form đã được gửi -->
    <?php if (isset($_GET['inquiry_sent']) && $_GET['inquiry_sent'] == 1): ?>
      <div class="row justify-content-center mb-4">
        <div class="col-md-10">
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Gửi yêu cầu thành công!</strong> Chúng tôi sẽ liên hệ lại với bạn trong thời gian sớm nhất.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Tiêu đề form -->
    <div class="row justify-content-center mb-5">
      <div class="col-md-10 text-center ftco-animate">
        <span class="subheading" style="color: #c49b63;">Liên Hệ</span>
        <h2 class="mb-4" style="color: #fff;">ĐĂNG KÝ TƯ VẤN</h2>
      </div>
    </div>

    <!-- Form và thông tin liên hệ -->
    <div class="row d-flex">
      <!-- Form đăng ký -->
      <div class="col-md-7 ftco-animate makereservation">
        <!-- Background mờ cho form -->
        <div class="p-4 p-md-5 rounded" style="background-color: rgba(0, 0, 0, 0.5);">
          <form action="<?php echo APPURL; ?>/services.php" method="POST" id="inquiryForm">
            <div class="row">
              <!-- Tên -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="customer_name" class="text-white font-weight-bold">Tên</label>
                  <input type="text" id="customer_name" name="customer_name" class="form-control <?php echo isset($errors['customer_name']) ? 'is-invalid' : ''; ?>" placeholder="Tên của bạn" value="<?php echo isset($customer_name) ? $customer_name : ''; ?>">
                  <?php if (isset($errors['customer_name'])): ?>
                    <div class="error-message"><?php echo $errors['customer_name']; ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <!-- Email -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="customer_email" class="text-white font-weight-bold">Email</label>
                  <input type="email" id="customer_email" name="customer_email" class="form-control <?php echo isset($errors['customer_email']) ? 'is-invalid' : ''; ?>" placeholder="Email của bạn" value="<?php echo isset($customer_email) ? $customer_email : ''; ?>">
                  <?php if (isset($errors['customer_email'])): ?>
                    <div class="error-message"><?php echo $errors['customer_email']; ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <!-- Số điện thoại -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="customer_phone" class="text-white font-weight-bold">Số điện thoại</label>
                  <input type="tel" id="customer_phone" name="customer_phone" class="form-control <?php echo isset($errors['customer_phone']) ? 'is-invalid' : ''; ?>" placeholder="Số điện thoại" value="<?php echo isset($customer_phone) ? $customer_phone : ''; ?>">
                  <?php if (isset($errors['customer_phone'])): ?>
                    <div class="error-message"><?php echo $errors['customer_phone']; ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <!-- Dịch vụ quan tâm -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="service_type" class="text-white font-weight-bold">Dịch vụ quan tâm</label>
                  <div class="select-wrap one-third">
                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                    <select id="service_type" name="service_type" class="form-control <?php echo isset($errors['service_type']) ? 'is-invalid' : ''; ?>">
                      <option value="">-- Chọn dịch vụ --</option>
                      <option value="event" <?php echo (isset($service_type) && $service_type == 'event') ? 'selected' : ''; ?>>Tổ chức sự kiện</option>
                      <option value="workspace" <?php echo (isset($service_type) && $service_type == 'workspace') ? 'selected' : ''; ?>>Không gian làm việc</option>
                      <option value="barista" <?php echo (isset($service_type) && $service_type == 'barista') ? 'selected' : ''; ?>>Lớp học Barista</option>
                      <option value="booking" <?php echo (isset($service_type) && $service_type == 'booking') ? 'selected' : ''; ?>>Đặt bàn</option>
                      <option value="delivery" <?php echo (isset($service_type) && $service_type == 'delivery') ? 'selected' : ''; ?>>Giao hàng</option>
                    </select>
                    <?php if (isset($errors['service_type'])): ?>
                      <div class="error-message"><?php echo $errors['service_type']; ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <!-- Yêu cầu cụ thể -->
              <div class="col-md-12 mt-3">
                <div class="form-group">
                  <label for="customer_message" class="text-white font-weight-bold">Yêu cầu cụ thể</label>
                  <textarea id="customer_message" name="customer_message" cols="30" rows="7" class="form-control <?php echo isset($errors['customer_message']) ? 'is-invalid' : ''; ?>" placeholder="Vui lòng cho chúng tôi biết yêu cầu chi tiết của bạn"><?php echo isset($customer_message) ? $customer_message : ''; ?></textarea>
                  <?php if (isset($errors['customer_message'])): ?>
                    <div class="error-message"><?php echo $errors['customer_message']; ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <!-- Nút gửi -->
              <div class="col-md-12 mt-3">
                <div class="form-group">
                  <button type="submit" name="submit_inquiry" class="btn btn-primary py-3 px-5">Gửi Yêu Cầu</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Thông tin liên hệ - giữ nguyên phần này -->
      <div class="col-md-5 ftco-animate">
        <!-- Background mờ cho phần thông tin -->
        <div class="p-4 p-md-5 h-100 rounded" style="background-color: rgba(0, 0, 0, 0.5);">
          <!-- Nội dung thông tin liên hệ - giữ nguyên phần này -->
          <!-- ... -->
        </div>
      </div>
    </div>
  </div>
</section>

<?php require "includes/footer.php"; ?>