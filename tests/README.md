# Coffee Shop Selenium Testing Guide

## 📋 Mục lục
1. [Cài đặt và Cấu hình](#cài-đặt-và-cấu-hình)
2. [Chạy Tests](#chạy-tests)
3. [Cấu trúc Test](#cấu-trúc-test)
4. [Troubleshooting](#troubleshooting)

## 🛠️ Cài đặt và Cấu hình

### 1. Cài đặt Dependencies
```bash
composer install
```

### 2. Cài đặt Selenium Server
Có 2 cách để chạy Selenium:

#### Cách 1: Sử dụng Selenium Standalone Server
1. Tải Selenium Server JAR file từ: https://selenium-release.storage.googleapis.com/index.html
2. Tải ChromeDriver từ: https://chromedriver.chromium.org/
3. Chạy Selenium Server:
```bash
java -Dwebdriver.chrome.driver=/path/to/chromedriver -jar selenium-server-standalone-x.xx.x.jar
```

#### Cách 2: Sử dụng Docker (Khuyến nghị)
```bash
# Chạy Selenium Grid với Chrome
docker run -d -p 4444:4444 -p 7900:7900 --shm-size=2g selenium/standalone-chrome:latest

# Hoặc với VNC để xem browser (truy cập http://localhost:7900, password: secret)
docker run -d -p 4444:4444 -p 7900:7900 --shm-size=2g selenium/standalone-chrome:latest
```

### 3. Cấu hình Local Server
Đảm bảo XAMPP đang chạy và website accessible tại: `http://localhost/Coffee-Shop`

## 🚀 Chạy Tests

### Chạy tất cả tests
```bash
cd tests
php TestRunner.php
```

### Chạy test riêng lẻ
```bash
# Test admin login
php AdminLoginTest.php

# Test booking system
php BookingTest.php
```

## 🏗️ Cấu trúc Test

### BaseTest.php
- Class cơ sở cho tất cả tests
- Chứa các utility methods:
  - `waitForElement()` - Chờ element xuất hiện
  - `fillInput()` - Điền form an toàn
  - `clickElement()` - Click element an toàn
  - `takeScreenshot()` - Chụp màn hình khi lỗi
  - `assertCurrentUrl()` - Kiểm tra URL
  - `assertElementExists()` - Kiểm tra element tồn tại

### AdminLoginTest.php
Tests cho admin login:
- ✅ Đăng nhập hợp lệ
- ❌ Đăng nhập sai thông tin
- 📝 Validation fields trống
- 👁️ Toggle hiện/ẩn password

### BookingTest.php
Tests cho hệ thống đặt bàn:
- 🔐 Kiểm tra yêu cầu đăng nhập
- 📅 Đặt bàn hợp lệ
- 📝 Validation form
- ⏰ Chọn ngày giờ

### TestRunner.php
- Chạy tất cả test suites
- Hiển thị báo cáo tổng hợp
- Tính toán thời gian và tỉ lệ thành công

## 🔧 Cấu hình Test

### Thay đổi cấu hình trong BaseTest.php:
```php
// URL của ứng dụng
protected $baseUrl = 'http://localhost/Coffee-Shop';

// Selenium server
protected $seleniumHost = 'http://localhost:4444';

// Timeout settings
protected $implicitWait = 10;  // Chờ element tự động
protected $explicitWait = 15;  // Chờ điều kiện cụ thể
```

### Thay đổi thông tin test trong các test classes:
```php
// AdminLoginTest.php
private $adminEmail = 'admin@coffeeshop.com';
private $adminPassword = 'admin123';

// BookingTest.php
private $userEmail = 'testuser@example.com';
private $userPassword = 'testpass123';
```

## 📸 Screenshots
Khi test thất bại, screenshots sẽ được lưu trong thư mục `tests/screenshots/`

## 🐛 Troubleshooting

### Lỗi "Could not connect to Selenium server"
```
❌ Lỗi khởi tạo WebDriver: Could not connect to host
```
**Giải pháp:**
1. Kiểm tra Selenium server đang chạy tại port 4444
2. Thử truy cập http://localhost:4444/wd/hub/status
3. Nếu dùng Docker, kiểm tra container đang running

### Lỗi "Element not found"
```
❌ Element not found: //input[@name='email']
```
**Giải pháp:**
1. Kiểm tra selector đúng chưa
2. Tăng timeout trong `waitForElement()`
3. Kiểm tra page đã load đầy đủ chưa
4. Xem screenshot để debug

### Lỗi "Session not created"
```
❌ session not created: This version of ChromeDriver only supports Chrome version XX
```
**Giải pháp:**
1. Cập nhật ChromeDriver phù hợp với Chrome version
2. Hoặc dùng Docker image mới nhất

### Website không accessible
```
❌ Test FAILED: Expected URL to contain '/admin-panel/', but got 'http://localhost/Coffee-Shop/404.php'
```
**Giải pháp:**
1. Kiểm tra XAMPP đang chạy
2. Kiểm tra website accessible tại http://localhost/Coffee-Shop
3. Kiểm tra database đã import chưa
4. Kiểm tra file config.php

### Test chạy chậm
**Cách tối ưu:**
1. Giảm implicit wait time
2. Sử dụng explicit waits cho specific elements
3. Chạy Chrome headless mode:
```php
$chromeOptions->addArguments(['--headless']);
```

### Memory issues
**Giải pháp:**
1. Tăng memory limit trong PHP:
```bash
php -d memory_limit=512M TestRunner.php
```
2. Giảm số lượng browser instances chạy đồng thời

## 📝 Viết Test Mới

### Template cho test class mới:
```php
<?php
require_once __DIR__ . '/BaseTest.php';
use Facebook\WebDriver\WebDriverBy;

class MyNewTest extends BaseTest
{
    public function runAllTests()
    {
        echo "\n🧪 Bắt đầu My New Test...\n";
        $this->setUp();
        
        try {
            $this->testSomething();
            echo "\n✅ Tất cả tests đã PASS!\n";
        } catch (Exception $e) {
            echo "\n❌ Test FAILED: " . $e->getMessage() . "\n";
            $this->takeScreenshot('my_test_failed');
        } finally {
            $this->tearDown();
        }
    }
    
    public function testSomething()
    {
        $this->driver->get($this->baseUrl . '/some-page.php');
        $this->assertElementExists(WebDriverBy::id('some-element'));
        // Add your test logic here
    }
}
```

## 📞 Support
Nếu gặp vấn đề, hãy:
1. Kiểm tra log của Selenium server
2. Xem screenshot trong thư mục `tests/screenshots/`
3. Chạy test từng cái một để isolate vấn đề
4. Kiểm tra browser developer tools khi chạy manual test
