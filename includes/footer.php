<footer class="ftco-footer ftco-section img">
  <div class="overlay"></div>
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-3 col-md-6 mb-5 mb-md-5">
        <div class="ftco-footer-widget mb-4">
          <h2 class="ftco-heading-2">Về chúng tôi</h2>
          <p>Artisan Coffee - Nơi hội tụ những hương vị cà phê đặc sắc, được phục vụ bởi đội ngũ nhân viên chuyên nghiệp trong không gian ấm cúng và sang trọng.</p>
          <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
            <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
            <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
            <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
          </ul>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 mb-5 mb-md-5">
        <div class="ftco-footer-widget mb-4">
          <h2 class="ftco-heading-2">Bài viết gần đây</h2>
          <div class="block-21 mb-4 d-flex">
            <a class="blog-img mr-4" style="background-image: url(<?php echo APPURL ?>/images/image_1.jpg);"></a>
            <div class="text">
              <h3 class="heading"><a href="#">Nghệ thuật thưởng thức cà phê theo phong cách Ý</a></h3>
              <div class="meta">
                <div><a href="#"><span class="icon-calendar"></span> 15/09/2023</a></div>
                <div><a href="#"><span class="icon-person"></span> Quản trị viên</a></div>
                <div><a href="#"><span class="icon-chat"></span> 19</a></div>
              </div>
            </div>
          </div>
          <div class="block-21 mb-4 d-flex">
            <a class="blog-img mr-4" style="background-image: url(<?php echo APPURL ?>/images/image_2.jpg);"></a>
            <div class="text">
              <h3 class="heading"><a href="#">10 loại cà phê đặc sản bạn nên thử một lần trong đời</a></h3>
              <div class="meta">
                <div><a href="#"><span class="icon-calendar"></span> 20/08/2023</a></div>
                <div><a href="#"><span class="icon-person"></span> Quản trị viên</a></div>
                <div><a href="#"><span class="icon-chat"></span> 24</a></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-6 mb-5 mb-md-5">
        <div class="ftco-footer-widget mb-4 ml-md-4">
          <h2 class="ftco-heading-2">Dịch vụ</h2>
          <ul class="list-unstyled">
            <li><a href="#" class="py-2 d-block">Phục vụ tại chỗ</a></li>
            <li><a href="#" class="py-2 d-block">Giao hàng tận nơi</a></li>
            <li><a href="#" class="py-2 d-block">Đặt tiệc & Sự kiện</a></li>
            <li><a href="#" class="py-2 d-block">Khóa học pha chế</a></li>
          </ul>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-5 mb-md-5">
        <div class="ftco-footer-widget mb-4">
          <h2 class="ftco-heading-2">Liên hệ với chúng tôi</h2>
          <div class="block-23 mb-3">
            <ul>
              <li><span class="icon icon-map-marker"></span><span class="text">123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</span></li>
              <li><a href="#"><span class="icon icon-phone"></span><span class="text">+84 28 1234 5678</span></a></li>
              <li><a href="#"><span class="icon icon-envelope"></span><span class="text">info@artisancoffee.vn</span></a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 text-center">
        <p>
          Bản quyền &copy;<script>
            document.write(new Date().getFullYear());
          </script> Artisan Coffee
        </p>
      </div>
    </div>
  </div>
</footer>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
    <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
    <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
  </svg></div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo APPURL ?>/js/jquery-migrate-3.0.1.min.js"></script>
<script src="<?php echo APPURL ?>/js/popper.min.js"></script>
<script src="<?php echo APPURL ?>/js/bootstrap.min.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.easing.1.3.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.waypoints.min.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.stellar.min.js"></script>
<script src="<?php echo APPURL ?>/js/owl.carousel.min.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.magnific-popup.min.js"></script>
<script src="<?php echo APPURL ?>/js/aos.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.animateNumber.min.js"></script>
<script src="<?php echo APPURL ?>/js/bootstrap-datepicker.js"></script>
<script src="<?php echo APPURL ?>/js/jquery.timepicker.min.js"></script>
<script src="<?php echo APPURL ?>/js/scrollax.min.js"></script>
<script src="<?php echo APPURL ?>/js/leaflet-map.js"></script>
<script src="<?php echo APPURL ?>/js/main.js"></script>

<!-- Script xử lý tùy chỉnh -->
<script>
  $(document).ready(function() {
    // Xử lý scroll cho navbar
    $(window).scroll(function() {
      var st = $(this).scrollTop();
      var navbar = $('.ftco-navbar-custom');

      if (st > 150) {
        if (!navbar.hasClass('scrolled')) {
          navbar.addClass('scrolled');
        }
      } else {
        if (navbar.hasClass('scrolled')) {
          navbar.removeClass('scrolled');
        }
      }
    });

    // Xử lý dropdown menu người dùng
    var userDropdown = document.getElementById('userDropdown');
    var dropdownMenu = document.querySelector('.user-dropdown-menu');

    if (userDropdown && dropdownMenu) {
      // Xử lý khi click vào dropdown toggle
      userDropdown.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
      });

      // Đảm bảo các dropdown item hoạt động đúng
      var dropdownItems = document.querySelectorAll('.dropdown-item');
      dropdownItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
          // Chỉ ngăn chặn sự kiện nổi bọt, không ngăn chặn hành vi mặc định
          e.stopPropagation();
        });
      });

      // Đóng dropdown khi click bên ngoài
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-dropdown')) {
          dropdownMenu.classList.remove('show');
        }
      });
    }

    // Tăng cường click cho mobile
    if (window.innerWidth < 992) {
      $('.dropdown-item').on('touchstart', function() {
        window.location.href = $(this).attr('href');
      });
    }
  });
</script>

</body>

</html>