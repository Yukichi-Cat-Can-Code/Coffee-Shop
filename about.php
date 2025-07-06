<?php
require "includes/header.php";

// Lấy dữ liệu đánh giá từ database
$reviews = $conn->query("SELECT * FROM reviews WHERE status = 'Approved' ORDER BY created_at DESC LIMIT 6");
$reviews->execute();
$all_reviews = $reviews->fetchAll(PDO::FETCH_OBJ);
?>

<section class="home-slider owl-carousel">
  <div
    class="slider-item"
    style="background-image: url(images/bg_3.jpg)"
    data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
      <div
        class="row slider-text justify-content-center align-items-center">
        <div class="col-md-7 col-sm-12 text-center ftco-animate">
          <h1 class="mb-3 mt-5 bread">Giới Thiệu</h1>
          <p class="breadcrumbs">
            <span class="mr-2"><a href="<?php echo APPURL; ?>/index.php">Trang Chủ</a></span>
            <span>Giới Thiệu</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ftco-about d-md-flex">
  <div
    class="one-half img"
    style="background-image: url(images/about.jpg)"></div>
  <div class="one-half ftco-animate">
    <div class="overlap">
      <div class="heading-section ftco-animate">
        <span class="subheading">Khám Phá</span>
        <h2 class="mb-4">Câu Chuyện Của Chúng Tôi</h2>
      </div>
      <div>
        <p>
          Artisan Coffee được khởi nguồn từ niềm đam mê cà phê của những người sáng lập.
          Chúng tôi bắt đầu hành trình này với một mục tiêu đơn giản: mang đến những tách
          cà phê thủ công chất lượng cao nhất từ nguồn hạt cà phê tốt nhất Việt Nam.
          Mỗi hạt cà phê được chọn lọc kỹ lưỡng từ các vùng nguyên liệu nổi tiếng như
          Buôn Ma Thuột, Lâm Đồng và Sơn La, được rang xay theo công thức độc quyền để
          tạo ra hương vị đặc trưng mà chỉ có tại Artisan Coffee bạn mới thưởng thức được.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="ftco-section img" id="ftco-testimony" style="background-image: url(images/bg_1.jpg)" data-stellar-background-ratio="0.5">
  <div class="overlay"></div>
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-md-7 heading-section text-center ftco-animate">
        <span class="subheading">Đánh Giá</span>
        <h2 class="mb-4">Khách Hàng Nói Gì</h2>
        <p>
          Những trải nghiệm và cảm nhận thực tế từ khách hàng là nguồn động lực
          giúp chúng tôi không ngừng hoàn thiện và phát triển.
        </p>
      </div>
    </div>
  </div>
  <div class="container-wrap">
    <div class="testimony-slider owl-carousel">
      <?php foreach ($all_reviews as $review): ?>
        <div class="item">
          <div class="testimony golden-bg">
            <blockquote>
              <p>&ldquo;<?php echo escape($review->review); ?>&rdquo;</p>
            </blockquote>
            <div class="author d-flex mt-4">
              <div class="name align-self-center"><?php echo escape($review->user_name); ?></div>
            </div>
            <!-- Hiển thị rating sao -->
            <div class="rating mt-2">
              <?php
              // Lấy rating từ database hoặc mặc định là 5 sao nếu không có
              $rating = isset($review->rating) ? (int)$review->rating : 5;
              // Hiển thị số sao đánh giá
              for ($i = 1; $i <= 5; $i++): ?>
                <i class="icon-star<?php echo ($i <= $rating) ? '' : '-o'; ?>" style="color: #fac564;"></i>
              <?php endfor; ?>
            </div>
            <div class="date text-muted mt-1">
              <small><?php echo date('d/m/Y', strtotime($review->created_at)); ?></small>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="ftco-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 pr-md-5">
        <div class="heading-section text-md-right ftco-animate">
          <span class="subheading">Khám Phá</span>
          <h2 class="mb-4">Thực Đơn Của Chúng Tôi</h2>
          <p class="mb-4">
            Thực đơn của Artisan Coffee không chỉ dừng lại ở những tách cà phê thơm ngon.
            Chúng tôi còn mang đến nhiều lựa chọn đa dạng từ trà, nước ép trái cây tươi
            đến các món bánh ngọt được làm thủ công mỗi ngày, đảm bảo sẽ làm hài lòng mọi
            khẩu vị khó tính nhất.
          </p>
          <p>
            <a
              href="<?php echo APPURL; ?>/menu.php"
              class="btn btn-primary btn-outline-primary px-4 py-3">Xem Toàn Bộ Thực Đơn</a>
          </p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="row">
          <div class="col-md-6">
            <div class="menu-entry">
              <a
                href="<?php echo APPURL; ?>/menu.php"
                class="img"
                style="background-image: url(images/menu-1.jpg)"></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="menu-entry mt-lg-4">
              <a
                href="<?php echo APPURL; ?>/menu.php"
                class="img"
                style="background-image: url(images/menu-2.jpg)"></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="menu-entry">
              <a
                href="<?php echo APPURL; ?>/menu.php"
                class="img"
                style="background-image: url(images/menu-3.jpg)"></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="menu-entry mt-lg-4">
              <a
                href="<?php echo APPURL; ?>/menu.php"
                class="img"
                style="background-image: url(images/menu-4.jpg)"></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section
  class="ftco-counter ftco-bg-dark img"
  id="section-counter"
  style="background-image: url(images/bg_2.jpg)"
  data-stellar-background-ratio="0.5">
  <div class="overlay"></div>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="row">
          <div
            class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18 text-center">
              <div class="text">
                <div class="icon">
                  <span class="flaticon-coffee-cup"></span>
                </div>
                <strong class="number" data-number="100">0</strong>
                <span>Chi Nhánh</span>
              </div>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18 text-center">
              <div class="text">
                <div class="icon">
                  <span class="flaticon-coffee-cup"></span>
                </div>
                <strong class="number" data-number="85">0</strong>
                <span>Giải Thưởng</span>
              </div>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18 text-center">
              <div class="text">
                <div class="icon">
                  <span class="flaticon-coffee-cup"></span>
                </div>
                <strong class="number" data-number="10567">0</strong>
                <span>Khách Hàng Hài Lòng</span>
              </div>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
            <div class="block-18 text-center">
              <div class="text">
                <div class="icon">
                  <span class="flaticon-coffee-cup"></span>
                </div>
                <strong class="number" data-number="900">0</strong>
                <span>Nhân Viên</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  .testimony.golden-bg {
    background-color: #c49b63;
    padding: 30px;
    color: #fff;
    border-radius: 5px;
    height: 100%;
  }

  .testimony-slider .owl-stage {
    display: flex;
  }

  .testimony-slider .item {
    height: 100%;
    padding: 15px;
  }

  .testimony.golden-bg blockquote {
    border-left: none;
    padding: 0;
  }

  .testimony.golden-bg .name {
    color: #fff;
    font-weight: 600;
  }

  .testimony.golden-bg .date {
    color: rgba(255, 255, 255, 0.7) !important;
  }

  .testimony.golden-bg .icon-star,
  .testimony.golden-bg .icon-star-o {
    color: #fff !important;
  }

  /* Điều chỉnh kích thước điểm chỉ báo */
  .testimony-slider .owl-dots .owl-dot span {
    width: 10px;
    height: 10px;
    margin: 5px 7px;
    background: #D6D6D6;
    opacity: 0.5;
  }

  .testimony-slider .owl-dots .owl-dot.active span {
    background: #c49b63;
    opacity: 1;
  }
</style>

<script>
  $(document).ready(function() {
    $('.testimony-slider').owlCarousel({
      autoplay: true,
      autoplayTimeout: 3000,
      autoplayHoverPause: false,
      loop: true,
      margin: 30,
      nav: false,
      dots: true,
      smartSpeed: 1000,
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 2
        },
        1000: {
          items: 4
        }
      }
    });
  });
</script>

<?php
require "includes/footer.php";
?>