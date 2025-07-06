<?php
// Tự động load các class cần thiết
require_once __DIR__ . '/SessionManager.php';

// Khởi tạo session manager
$session = SessionManager::getInstance();

// Hàm helper để dễ dàng sử dụng session
function session()
{
    return SessionManager::getInstance();
}
