<?php

// Load configuration
require "../../config/config.php";

$message = '';
$message_type = '';

// Kiểm tra nếu đã đăng nhập thì chuyển hướng
if (isset($_SESSION['login_message'])) {
  $message = $_SESSION['login_message'];
  $message_type = isset($_SESSION['login_message_type']) ? $_SESSION['login_message_type'] : 'warning';
  // Clear message after using it
  unset($_SESSION['login_message']);
  unset($_SESSION['login_message_type']);
}

if (isset($_SESSION['admin_name'])) {
  header("location: " . ADMINAPPURL . "");
  exit();
}

$error_message = '';

if (isset($_POST['submit'])) {
  if (empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
    $error_message = 'Vui lòng điền đầy đủ thông tin đăng nhập!';
  } else {
    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];

    // Sử dụng prepared statement để bảo mật
    $login = $conn->prepare("SELECT * FROM admins WHERE admin_email = ?");
    $login->execute([$admin_email]);

    $fetch = $login->fetch(PDO::FETCH_ASSOC);

    if ($login->rowCount() > 0) {
      if (password_verify($admin_password, $fetch['admin_password'])) {
        // Bắt đầu phiên
        $_SESSION['admin_name'] = $fetch['admin_name'];
        $_SESSION['admin_email'] = $fetch['admin_email'];
        $_SESSION['admin_id'] = $fetch['ID'];

        header("location: " . ADMINAPPURL . "");
        exit();
      } else {
        $error_message = 'Đăng nhập sai do sai username hoặc password';
      }
    } else {
      $error_message = 'Đăng nhập sai do sai username hoặc password';
    }
  }
}

require "../layouts/header.php";
?>

<!-- Steam effect outside wrapper -->
<div class="steam">
  <div class="steam-particle"></div>
  <div class="steam-particle"></div>
  <div class="steam-particle"></div>
  <div class="steam-particle"></div>
  <div class="steam-particle"></div>
</div>

<!-- Login container inside wrapper -->
<div class="login-container">
  <div class="login-header">
    <div class="coffee-logo">
      <i class="fas fa-coffee"></i>
    </div>
    <h1 class="login-title">Artisan Coffee Admin</h1>
    <p class="login-subtitle">Đăng nhập hệ thống quản lý</p>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
      <i class="fas fa-info-circle mr-2"></i>
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login-admins.php" id="loginForm">
    <div class="form-group">
      <input
        type="email"
        name="admin_email"
        class="form-control"
        placeholder="Tên tài khoản của bạn"
        required
        autocomplete="email"
        value="<?php echo isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : ''; ?>" />
      <i class="fas fa-envelope input-icon"></i>
    </div>

    <div class="form-group password-group">
      <input
        type="password"
        name="admin_password"
        id="passwordField"
        class="form-control"
        placeholder="Nhập mật khẩu của bạn"
        required
        autocomplete="current-password" />
      <i class="fas fa-lock input-icon"></i>
      <div class="toggle-password" id="togglePassword">
        <i class="fas fa-eye-slash"></i>
      </div>
    </div>

    <button type="submit" name="submit" class="login-btn" id="submitBtn">
      <span class="btn-text">Đăng Nhập Hệ Thống</span>
      <div class="loading">
        <div class="spinner"></div>
      </div>
    </button>
  </form>
</div>

<!-- Toast container for notifications -->
<div id="toastContainer" class="toast-container"></div>

<style>
  :root {
    --coffee-main: #3A2618;
    --coffee-accent: #6B4226;
    --coffee-dark: #2C1A10;
    --coffee-choco: #4E2C15;
    --coffee-cream: #E6D7C3;
    --coffee-blond: #C8B6A6;
    --coffee-neutral: #7D6B5D;
    --accent-gold: #9C7A3C;
    --accent-warm: #B38867;
    --glass-bg: rgba(155, 109, 66, 0.88);
    --glass-border: rgba(156, 122, 60, 0.35);
    --shadow-color: rgba(44, 26, 16, 0.4);
    --error-bg: rgba(220, 53, 69, 0.1);
    --error-border: rgba(220, 53, 69, 0.3);
    --darkbackground: rgba(34, 19, 5, 0.95);
  }

  /* Override body styles for login page */
  body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #1a0d02 !important;
    position: relative;
  }

  /* Coffee beans background */
  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?q=80&w=2000&auto=format');
    background-size: cover;
    background-position: center;
    filter: brightness(0.4) saturate(1.2);
    z-index: -2;
  }

  /* Overlay gradient */
  body::after {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 50% 50%, rgba(26, 13, 2, 0.6) 0%, rgba(20, 10, 2, 0.9) 100%);
    z-index: -1;
  }

  /* Remove wrapper and page-container margin for login page */
  #wrapper {
    margin-left: 0 !important;
    width: 100% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background-color: transparent !important;
  }

  .page-container {
    padding-top: 70pt !important;
  }

  /* Hide sidebar for login page */
  .side-nav-container {
    display: none !important;
  }

  /* Hide footer for login page */
  .admin-footer {
    display: none;
  }

  /* Steam animation elements */
  .steam {
    position: fixed;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
  }

  .steam-particle {
    position: absolute;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    width: 15px;
    height: 15px;
    animation: rise 10s infinite ease-in-out;
    opacity: 0;
  }

  .steam-particle:nth-child(1) {
    left: 10%;
    width: 20px;
    height: 20px;
    animation-duration: 8s;
    animation-delay: 1s;
  }

  .steam-particle:nth-child(2) {
    left: 30%;
    width: 25px;
    height: 25px;
    animation-duration: 10s;
    animation-delay: 3s;
  }

  .steam-particle:nth-child(3) {
    left: 50%;
    width: 15px;
    height: 15px;
    animation-duration: 7s;
  }

  .steam-particle:nth-child(4) {
    left: 70%;
    width: 18px;
    height: 18px;
    animation-duration: 9s;
    animation-delay: 2s;
  }

  .steam-particle:nth-child(5) {
    left: 90%;
    width: 22px;
    height: 22px;
    animation-duration: 11s;
    animation-delay: 4s;
  }

  @keyframes rise {
    0% {
      bottom: -50px;
      opacity: 0;
      transform: translateX(0) scale(0.2);
    }

    20% {
      opacity: 0.15;
      transform: translateX(-20px) scale(0.5);
    }

    40% {
      opacity: 0.1;
      transform: translateX(20px) scale(0.7);
    }

    60% {
      opacity: 0.05;
      transform: translateX(-10px) scale(1);
    }

    80% {
      opacity: 0.02;
      transform: translateX(10px) scale(1.2);
    }

    100% {
      bottom: 100%;
      opacity: 0;
      transform: translateX(0) scale(1.5);
    }
  }

  /* Alert auto-hide animation */
  .alert {
    transition: opacity 0.5s ease, transform 0.5s ease;
  }

  .fade-out {
    opacity: 0;
    transform: translateY(-10px);
  }

  .login-container {
    background: rgba(62, 39, 19, 0.5);
    backdrop-filter: blur(15px);
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    border: 1px solid rgba(179, 136, 103, 0.2);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3),
      0 5px 15px rgba(0, 0, 0, 0.2),
      inset 0 1px 0 rgba(255, 255, 255, 0.1);
    position: relative;
    z-index: 10;
    overflow: hidden;
    transition: transform 0.5s ease, box-shadow 0.5s ease;
    margin: 2rem auto;
  }

  /* Coffee cup design element */
  .login-container::before {
    content: "";
    position: absolute;
    top: -80px;
    right: -80px;
    width: 160px;
    height: 160px;
    background: radial-gradient(circle at 30% 30%,
        rgba(227, 212, 195, 0.15),
        rgba(179, 136, 103, 0.05));
    border-radius: 50%;
    z-index: -1;
  }

  .login-container::after {
    content: "";
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle at 70% 70%,
        rgba(156, 122, 60, 0.1),
        rgba(78, 44, 21, 0.05));
    border-radius: 50%;
    z-index: -1;
  }

  .login-header {
    text-align: center;
    margin-bottom: 2.8rem;
  }

  .coffee-logo {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #9C7A3C, #6B4226);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2),
      inset 0 2px 10px rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
  }

  /* Coffee logo steam effect */
  .coffee-logo::before {
    content: "";
    position: absolute;
    width: 30px;
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    filter: blur(3px);
    animation: steamPulse 3s infinite ease-in-out;
  }

  .coffee-logo::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 5px;
    background: rgba(255, 255, 255, 0.1);
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    filter: blur(2px);
    animation: steamPulse 3s infinite ease-in-out 1s;
  }

  @keyframes steamPulse {

    0%,
    100% {
      opacity: 0.5;
      transform: translateX(-50%) scale(1);
    }

    50% {
      opacity: 0.8;
      transform: translateX(-50%) scale(1.2);
    }
  }

  .coffee-logo i {
    font-size: 2.6rem;
    color: var(--coffee-cream);
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    animation: glow 3s infinite alternate;
  }

  @keyframes glow {
    from {
      text-shadow: 0 0 5px rgba(230, 215, 195, 0.3);
    }

    to {
      text-shadow: 0 0 15px rgba(230, 215, 195, 0.7);
    }
  }

  .login-title {
    color: var(--coffee-cream);
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    letter-spacing: 1px;
  }

  .login-subtitle {
    color: #b38867;
    font-size: 1rem;
    opacity: 0.9;
    font-weight: 400;
    letter-spacing: 0.5px;
  }

  .form-group {
    margin-bottom: 1.8rem;
    position: relative;
  }

  .form-control {
    background: rgba(200, 182, 166, 0.15) !important;
    border: 1px solid rgba(179, 136, 103, 0.3) !important;
    border-radius: 16px !important;
    padding: 1.2rem 1.2rem 1.2rem 3.5rem !important;
    color: var(--coffee-cream) !important;
    font-size: 1rem !important;
    font-weight: 400 !important;
    transition: all 0.3s !important;
    width: 100% !important;
    backdrop-filter: blur(5px) !important;
  }

  .form-control:focus {
    background: rgba(200, 182, 166, 0.25) !important;
    border-color: rgba(179, 136, 103, 0.6) !important;
    color: #fff !important;
    box-shadow: 0 0 0 0.25rem rgba(156, 122, 60, 0.2) !important;
    outline: none !important;
  }

  .form-control::placeholder {
    color: rgba(200, 182, 166, 0.6) !important;
    opacity: 0.8 !important;
  }

  .input-icon {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--accent-gold);
    font-size: 1.3rem;
    z-index: 5;
    transition: all 0.3s ease;
  }

  .error-message {
    background: rgba(220, 53, 69, 0.15);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 12px;
    padding: 0.8rem 1rem;
    margin-top: 0.8rem;
    color: #ffb3b8;
    font-size: 0.92rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    animation: errorSlideDown 0.4s;
  }

  .error-message i {
    color: #ff8a8f;
    font-size: 1.1rem;
  }

  .login-btn {
    background: linear-gradient(90deg, #6B4226, #9C7A3C);
    color: var(--coffee-cream);
    border: none;
    border-radius: 16px;
    padding: 1.2rem 2rem;
    font-size: 1.05rem;
    font-weight: 600;
    width: 100%;
    transition: all 0.4s;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    position: relative;
    overflow: hidden;
    margin-top: 1rem;
    cursor: pointer;
  }

  .login-btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent);
    transition: all 0.6s;
  }

  .login-btn:hover::before {
    left: 100%;
  }

  .login-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    background: linear-gradient(90deg, #7B5236, #AC8A4C);
  }

  .login-btn:active {
    transform: translateY(0);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  }

  .toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
  }

  .custom-toast {
    background: rgba(44, 26, 16, 0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 12px;
    color: #ffb3b8;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    animation: toastSlideIn 0.4s;
    min-width: 300px;
    padding: 1rem;
  }

  .toast-header {
    border-bottom: 1px solid rgba(220, 53, 69, 0.2);
    padding-bottom: 0.5rem;
  }

  .btn-close {
    filter: invert(1) brightness(0.8);
  }

  .loading {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .spinner {
    width: 24px;
    height: 24px;
    border: 3px solid rgba(200, 182, 166, 0.3);
    border-top: 3px solid var(--coffee-cream);
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  /* Password toggle styling */
  .password-group {
    position: relative;
  }

  .toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--accent-gold);
    font-size: 1.1rem;
    cursor: pointer;
    z-index: 10;
    opacity: 0.7;
    transition: all 0.3s ease;
  }

  .toggle-password:hover {
    opacity: 1;
    color: var(--coffee-cream);
  }

  /* Fix input padding to accommodate the toggle icon */
  .password-group .form-control {
    padding-right: 45px !important;
  }

  /* Animation for password visibility toggle */
  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }

  .toggle-password.active {
    color: var(--coffee-cream);
    animation: pulse 0.3s;
  }

  /* Enhanced animations */
  @keyframes errorSlideDown {
    from {
      opacity: 0;
      transform: translateY(-15px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes toastSlideIn {
    from {
      opacity: 0;
      transform: translateX(100%);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  /* Responsive styles */
  @media (max-width: 768px) {
    .login-container {
      margin: 1rem;
      padding: 2.2rem 1.8rem;
      max-width: 95%;
    }

    .login-title {
      font-size: 1.8rem;
    }

    .coffee-logo {
      width: 80px;
      height: 80px;
    }

    .coffee-logo i {
      font-size: 2.2rem;
    }
  }

  @media (max-width: 480px) {
    .login-container {
      padding: 1.8rem 1.5rem;
      border-radius: 20px;
    }

    .login-title {
      font-size: 1.6rem;
    }

    .coffee-logo {
      width: 70px;
      height: 70px;
      margin-bottom: 1.2rem;
    }

    .coffee-logo i {
      font-size: 2rem;
    }

    .form-control {
      padding: 1rem 1rem 1rem 3.2rem !important;
      font-size: 0.95rem !important;
    }

    .login-btn {
      padding: 1rem 1.5rem;
      font-size: 1rem;
    }
  }
</style>

<script>
  // Auto-hide alerts after 3 seconds
  document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.custom-toast)');
    if (alerts.length) {
      setTimeout(() => {
        alerts.forEach(alert => {
          // Add fade-out class
          alert.classList.add('fade-out');

          // Remove after animation completes
          setTimeout(() => {
            alert.remove();
          }, 500);
        });
      }, 3000); // 3 seconds before starting to hide
    }
  });

  // Password visibility toggle
  document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('passwordField');

    if (togglePassword && passwordField) {
      togglePassword.addEventListener('click', function() {
        // Toggle type attribute
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Toggle icon
        const icon = togglePassword.querySelector('i');
        if (type === 'password') {
          icon.className = 'fas fa-eye-slash';
          togglePassword.classList.remove('active');
        } else {
          icon.className = 'fas fa-eye';
          togglePassword.classList.add('active');
        }

        // Add focus to password field
        passwordField.focus();
      });
    }
  });

  function showToast(message, type = 'error') {
    const toastContainer = document.getElementById('toastContainer');
    const toastHtml = `
      <div class="toast custom-toast show" role="alert">
        <div class="toast-header">
          <i class="fas fa-exclamation-triangle text-danger me-2"></i>
          <strong class="me-auto">Thông báo</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${message}</div>
      </div>
    `;
    toastContainer.innerHTML = toastHtml;
    setTimeout(() => {
      const toast = toastContainer.querySelector('.toast');
      if (toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }
    }, 5000);
  }

  // Form submission effects
  document.getElementById('loginForm').addEventListener('submit', function() {
    const btnText = document.querySelector('.btn-text');
    const loading = document.querySelector('.loading');
    const submitBtn = document.getElementById('submitBtn');
    btnText.style.opacity = '0';
    loading.style.display = 'block';
  });

  // Show toast notification if there's an error
  <?php if (!empty($error_message)): ?>
    document.addEventListener('DOMContentLoaded', function() {
      showToast('<?php echo addslashes($error_message); ?>', 'error');
    });
  <?php endif; ?>

  // Page load animation
  document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.login-container');
    container.style.opacity = '0';
    container.style.transform = 'translateY(30px) scale(0.95)';
    setTimeout(() => {
      container.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
      container.style.opacity = '1';
      container.style.transform = 'translateY(0) scale(1)';
    }, 100);
  });
</script>

<?php
require "../layouts/footer.php";
?>