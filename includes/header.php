<?php
require_once __DIR__ . '/../config/config.php';

// Hiển thị flash messages nếu có
if (function_exists('session') && session()->has('_flash')) {
  if (session()->getFlash('success_message')): ?>
    <div class="container mt-3">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> <?php echo session()->getFlash('success_message'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
  <?php endif;

  if (session()->getFlash('error_message')): ?>
    <div class="container mt-3">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong> <?php echo session()->getFlash('error_message'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
<?php endif;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <title>Artisan Coffee - Tinh hoa cà phê Việt Nam</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Artisan Coffee - Nơi thưởng thức những tách cà phê thủ công ngon nhất">

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>">

  <!-- jQuery (cần load trước Bootstrap) -->
  <script src="<?php echo APPURL ?>/js/jquery.min.js"></script>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Bootstrap & CSS Files -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin="" />
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/open-iconic-bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/animate.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/owl.carousel.min.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/owl.theme.default.min.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/magnific-popup.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/aos.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/bootstrap-datepicker.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/jquery.timepicker.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/flaticon.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/icomoon.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/style.css">
  <link rel="stylesheet" href="<?php echo APPURL ?>/css/custom.css">

  <!-- Các style CSS giữ nguyên như cũ -->
  <<style>
    /* Fix cho navbar */
    .ftco-navbar-light {
    background: transparent !important;
    }

    .ftco-navbar-light.scrolled {
    background: #000 !important;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    /* Style cho nav items */
    .ftco-navbar-light .navbar-nav > .nav-item > .nav-link {
    font-size: 14px;
    padding: 1.2rem 15px;
    font-weight: 500;
    color: #fff;
    position: relative;
    text-transform: uppercase;
    letter-spacing: 1px;
    }

    .ftco-navbar-light .navbar-nav > .nav-item.active > .nav-link {
    color: #c49b63;
    }

    .ftco-navbar-light .navbar-nav > .nav-item > .nav-link .menu-icon {
    margin-right: 5px;
    color: #c49b63;
    }

    /* Style cho auth buttons */
    .auth-links {
    display: flex;
    align-items: center;
    margin-left: 10px;
    }

    .btn-auth {
    padding: 8px 15px;
    margin-left: 5px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    }

    .btn-login {
    color: #fff;
    background-color: transparent;
    border: 1px solid #fff;
    }

    .btn-register {
    color: #000;
    background-color: #c49b63;
    border: 1px solid #c49b63;
    }

    .btn-register:hover {
    background-color: #b38b57;
    color:rgb(241, 239, 236);
    }

    /* Fix dropdown menu */
    .user-dropdown-menu {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 200px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 4px;
    top: 100%;
    right: 0;
    }

    .user-dropdown-menu.show {
    display: block;
    }

    /* Cart icon */
    .cart-link {
    position: relative;
    }

    .cart-count {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: #c49b63;
    color: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    }

    /* Mobile fixes */
    @media (max-width: 991.98px) {
    .ftco-navbar-light .navbar-nav > .nav-item > .nav-link {
    padding: 10px 0;
    color: #000;
    }

    .auth-links {
    display: block;
    margin-top: 10px;
    margin-left: 0;
    }

    .btn-auth {
    display: inline-block;
    margin-bottom: 5px;
    }

    .user-dropdown-menu {
    position: static;
    box-shadow: none;
    width: 100%;
    }
    }
    </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light ftco-navbar-custom" id="ftco-navbar">
    <div class="container">
      <!-- Logo -->
      <a class="navbar-brand brand-logo" href="<?php echo APPURL; ?>">
        <i class="flaticon-coffee-cup coffee-icon"></i>
        <span class="brand-name">
          Artisan<small>Coffee</small>
        </span>
      </a>

      <!-- Nút menu mobile -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
        aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="oi oi-menu"></span>
      </button>

      <div class="collapse navbar-collapse" id="ftco-nav">
        <ul class="navbar-nav ml-auto">
          <!-- Menu chính -->
          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <a href="<?php echo APPURL; ?>" class="nav-link nav-link-custom">
              <i class="menu-icon fas fa-home"></i>
              <span>Trang chủ</span>
            </a>
          </li>

          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">
            <a href="<?php echo APPURL ?>/menu.php" class="nav-link nav-link-custom">
              <i class="menu-icon fas fa-coffee"></i>
              <span>Thực đơn</span>
            </a>
          </li>

          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
            <a href="<?php echo APPURL ?>/services.php" class="nav-link nav-link-custom">
              <i class="menu-icon fas fa-concierge-bell"></i>
              <span>Dịch vụ</span>
            </a>
          </li>

          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
            <a href="<?php echo APPURL ?>/about.php" class="nav-link nav-link-custom">
              <i class="menu-icon fas fa-info-circle"></i>
              <span>Giới thiệu</span>
            </a>
          </li>

          <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
            <a href="<?php echo APPURL; ?>/contact.php" class="nav-link nav-link-custom">
              <i class="menu-icon fas fa-envelope"></i>
              <span>Liên hệ</span>
            </a>
          </li>

          <!-- Đăng nhập/Đăng ký hoặc Menu người dùng -->
          <?php if (function_exists('session') && session()->isLoggedIn()): ?>
            <!-- Giỏ hàng -->
            <li class="nav-item">
              <a href="<?php echo APPURL ?>/products/cart.php" class="nav-link nav-link-custom cart-link">
                <i class="menu-icon fas fa-shopping-cart"></i>
                <?php if (session()->has('cart_count') && session()->get('cart_count') > 0): ?>
                  <span class="cart-count"><?= session()->get('cart_count') ?></span>
                <?php endif; ?>
              </a>
            </li>
            <!-- Menu người dùng -->
            <li class="nav-item dropdown user-dropdown">
              <a class="nav-link nav-link-custom dropdown-toggle" href="javascript:void(0);" id="userDropdown">
                <i class="menu-icon fas fa-user-circle"></i>
                <span><?php echo session()->get('user_name'); ?></span>
              </a>
              <div class="dropdown-menu user-dropdown-menu">
                <a class="dropdown-item" href="<?php echo APPURL ?>/users/profile.php">
                  <i class="fas fa-user-cog"></i> Tài khoản của tôi
                </a>
                <a class="dropdown-item" href="<?php echo APPURL ?>/users/bookings.php">
                  <i class="fas fa-calendar-check"></i> Đặt bàn
                </a>
                <a class="dropdown-item" href="<?php echo APPURL ?>/users/orders.php">
                  <i class="fas fa-clipboard-list"></i> Đơn hàng
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo APPURL ?>/auth/logout.php">
                  <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
              </div>
            </li>
          <?php else: ?>
            <li class="auth-links">
              <a href="<?php echo APPURL; ?>/auth/login.php" class="btn-auth btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
              </a>
              <a href="<?php echo APPURL; ?>/auth/register.php" class="btn-auth btn-register">
                <i class="fas fa-user-plus"></i> Đăng ký
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <!-- KẾT THÚC nav -->

  <!-- Script cho dropdown user -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Xử lý dropdown user
      const userDropdown = document.getElementById('userDropdown');
      if (userDropdown) {
        userDropdown.addEventListener('click', function() {
          const dropdownMenu = this.nextElementSibling;
          dropdownMenu.classList.toggle('show');

          // Đóng dropdown khi click ngoài
          document.addEventListener('click', function closeDropdown(e) {
            if (!e.target.closest('.user-dropdown')) {
              const openDropdown = document.querySelector('.user-dropdown-menu.show');
              if (openDropdown) {
                openDropdown.classList.remove('show');
              }
              document.removeEventListener('click', closeDropdown);
            }
          });
        });
      }
    });
  </script>