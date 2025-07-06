<?php

require_once __DIR__ . '/../init.php'; // Load session manager

/**
 * ĐỊNH NGHĨA HẰNG SỐ HỆ THỐNG
 * ============================
 */

// Thiết lập hằng số cho ứng dụng chính
if (!defined('APPURL')) define("APPURL", "http://localhost/Coffee-Shop");
if (!defined('APPNAME')) define("APPNAME", "Artisan Coffee");
if (!defined('APP_VERSION')) define("APP_VERSION", "1.0.0");

// Thiết lập hằng số cho Admin Panel
if (!defined('ADMIN_URL')) define("ADMIN_URL", APPURL . "/admin-panel");
if (!defined('ADMINAPPURL')) define("ADMINAPPURL", APPURL . "/admin-panel");
if (!defined('ADMIN_APPNAME')) define("ADMIN_APPNAME", "Artisan Coffee Admin");

/**
 * CẤU HÌNH CƠ SỞ DỮ LIỆU
 * =======================
 */
try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "coffeeShop"; // Đảm bảo tên này trùng khớp với tên database thực tế

    // Thêm charset=utf8mb4 để hỗ trợ tiếng Việt và các ký tự đặc biệt
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Thiết lập chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Thiết lập để PDO trả về kết quả dưới dạng object
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    // Thiết lập timezone cho PHP
    date_default_timezone_set('Asia/Ho_Chi_Minh');
} catch (PDOException $Exception) {
    // Ghi log lỗi thay vì hiển thị trực tiếp
    error_log("Database Error: " . $Exception->getMessage());

    // Thêm thông báo lỗi vào flash session để hiển thị sau này
    if (function_exists('session')) {
        session()->setFlash('error_message', "Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
    }

    // Hiển thị thông báo thân thiện với người dùng
    die("Có lỗi xảy ra trong quá trình kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

/**
 * CÁC HÀM TIỆN ÍCH
 * ================
 */

/**
 * Chuyển hướng người dùng đến URL khác
 * @param string $url URL đích
 */
function redirect($url)
{
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
    }
    exit;
}

/**
 * Escape HTML để tránh XSS
 * @param string $html Chuỗi HTML cần escape
 * @return string Chuỗi đã được escape
 */
function escape($html)
{
    return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Định dạng số tiền thành chuỗi tiền tệ VNĐ
 * @param float $amount Số tiền cần định dạng
 * @return string Chuỗi tiền tệ đã định dạng
 */
function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' đ';
}

/**
 * Kiểm tra người dùng đã đăng nhập hay chưa
 * @return bool True nếu đã đăng nhập, ngược lại là false
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra người dùng có quyền admin không
 * @return bool True nếu là admin, ngược lại là false
 */
function isAdmin()
{
    return isset($_SESSION['admin_id']) || isset($_SESSION['admin_name']);
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa và chuyển hướng nếu chưa đăng nhập
 */
function requireLogin()
{
    if (!session()->isLoggedIn()) {
        session()->setFlash('error_message', 'Vui lòng đăng nhập để tiếp tục.');
        redirect(APPURL . '/auth/login.php');
    }
}

/**
 * Kiểm tra quyền admin và chuyển hướng về trang chính nếu không phải admin
 */
function requireAdmin()
{
    if (!isAdmin()) {
        session()->setFlash('error_message', 'Bạn không có quyền truy cập trang này.');
        redirect(APPURL);
    }
}

/**
 * Kiểm tra quyền admin và chuyển hướng đến trang đăng nhập admin nếu chưa đăng nhập
 */
function requireAdminLogin()
{
    if (!isAdmin()) {
        session()->setFlash('error_message', 'Vui lòng đăng nhập với tài khoản quản trị.');
        redirect(ADMINAPPURL . '/login-admins.php');
    }
}

/**
 * Tạo chuỗi ngẫu nhiên cho các mã tham chiếu
 * @param int $length Độ dài chuỗi
 * @return string Chuỗi ngẫu nhiên
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Cắt chuỗi và thêm dấu ... nếu chuỗi dài
 * @param string $string Chuỗi cần cắt
 * @param int $length Độ dài tối đa
 * @param string $append Ký tự thêm vào cuối nếu chuỗi bị cắt
 * @return string Chuỗi đã được cắt
 */
function truncateString($string, $length = 100, $append = "...")
{
    $string = trim($string);

    if (strlen($string) > $length) {
        $string = wordwrap($string, $length);
        $string = explode("\n", $string, 2);
        $string = $string[0] . $append;
    }

    return $string;
}
