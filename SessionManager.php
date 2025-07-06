<?php

/**
 * Session Manager - Quản lý session tập trung
 */
class SessionManager
{
    private static $instance = null;
    private $isStarted = false;

    // Singleton pattern - đảm bảo chỉ có một instance duy nhất
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Private constructor để ngăn việc tạo instance trực tiếp
    private function __construct()
    {
        $this->start();
    }

    // Bắt đầu session nếu chưa bắt đầu
    public function start()
    {
        if ($this->isStarted === false) {
            // Cấu hình session
            ini_set('session.cookie_lifetime', 86400); // 24 giờ
            ini_set('session.gc_maxlifetime', 86400); // 24 giờ

            // Bắt đầu session chỉ khi cần
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $this->isStarted = true;
        }
        return $this->isStarted;
    }

    // Lấy giá trị từ session
    public function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    // Đặt giá trị vào session
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    // Xóa giá trị khỏi session
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    // Kiểm tra key tồn tại
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    // Lưu thông báo flash (hiển thị một lần rồi mất)
    public function setFlash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }

    // Lấy và xóa thông báo flash
    public function getFlash($key, $default = null)
    {
        $value = $default;
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
        }
        return $value;
    }

    // Kiểm tra user đã đăng nhập chưa
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Lấy thông tin user hiện tại
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null
        ];
    }

    // Lưu thông tin đăng nhập
    public function login($userId, $userName, $userEmail)
    {
        $this->set('user_id', $userId);
        $this->set('user_name', $userName);
        $this->set('user_email', $userEmail);
    }

    // Đăng xuất
    public function logout()
    {
        $this->remove('user_id');
        $this->remove('user_name');
        $this->remove('user_email');
    }
}
