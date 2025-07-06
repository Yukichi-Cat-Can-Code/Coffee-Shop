<?php
require "includes/header.php";
?>

<section class="home-slider owl-carousel">
  <div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_3.jpg);" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
      <div class="row slider-text justify-content-center align-items-center">
        <div class="col-md-7 col-sm-12 text-center ftco-animate">
          <h1 class="mb-3 mt-5 bread">Không Tìm Thấy Trang</h1>
          <p class="breadcrumbs">
            <span class="mr-2"><a href="<?php echo APPURL; ?>/index.php">Trang Chủ</a></span>
            <span>404</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section ftco-cart">
  <div class="container">
    <div class="row justify-content-center mb-5 pb-3">
      <div class="col-md-7 heading-section ftco-animate text-center">
        <span class="subheading">Lỗi</span>
        <h2 class="mb-4">Trang bạn đang tìm kiếm không tồn tại</h2>
        <p>Có thể đường dẫn đã bị thay đổi hoặc trang đã được di chuyển.</p>
        <div class="mt-5">
          <a href="<?php echo APPURL; ?>/index.php" class="btn btn-primary btn-lg">Quay về trang chủ</a>
          <a href="<?php echo APPURL; ?>/menu.php" class="btn btn-outline-primary btn-lg ml-2">Xem thực đơn</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require "includes/footer.php"; ?>