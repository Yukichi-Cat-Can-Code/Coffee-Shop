# 🚀 ChromeDriver Local Setup - Selenium Automation

## 📋 Tổng quan
Dự án Coffee Shop hiện đã được cấu hình để sử dụng **ChromeDriver local** thay vì Docker Selenium server. Điều này có nghĩa là:

✅ **Browser thật chạy trực tiếp trên máy của bạn**  
✅ **Không cần Docker container**  
✅ **Tốc độ nhanh hơn và ổn định hơn**  
✅ **Có thể quan sát trực tiếp các thao tác automation**  

## 🛠️ Cấu hình hiện tại

### ChromeDriver Path
```
E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64chromedriver.exe
```

### Website URL  
```
http://localhost/Coffee-Shop
```

### Các thông số test
- **UI Mode**: Enabled (browser hiển thị)
- **Local ChromeDriver**: Enabled  
- **Docker Selenium**: Disabled

## 🎯 Cách sử dụng

### 1. Kiểm tra setup nhanh
```bash
# Kiểm tra ChromeDriver có hoạt động không
check_chromedriver.bat

# Test nhanh 10 giây
quick_chrome_test.bat
```

### 2. Xem demo automation LIVE
```bash
# Xem browser tự động thao tác (3-4 phút)
live_show.bat

# Hoặc chạy trực tiếp
php tests/WatchOnlyTest.php
```

### 3. Chạy các test khác
```bash
# Test đơn giản
php tests/QuickChromeTest.php

# Test chi tiết với BaseTest
php tests/SimpleChromeDemo.php
```

## 📁 Cấu trúc file quan trọng

### Cấu hình chính
- `tests/TestConfig.php` - Cấu hình ChromeDriver local
- `tests/BaseTest.php` - Base class hỗ trợ ChromeDriver

### Scripts demo
- `live_show.bat` - Demo automation LIVE  
- `quick_chrome_test.bat` - Test nhanh
- `check_chromedriver.bat` - Kiểm tra setup

### Test files
- `tests/WatchOnlyTest.php` - Demo xem browser thao tác
- `tests/QuickChromeTest.php` - Test nhanh 10 giây
- `tests/SimpleChromeDemo.php` - Demo đơn giản

## 🔧 Cấu hình trong TestConfig.php

```php
// ChromeDriver Local Config
const USE_LOCAL_CHROMEDRIVER = true;
const CHROMEDRIVER_PATH = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
const BASE_URL = 'http://localhost/Coffee-Shop';

// UI Settings
const SHOW_UI = true;    // Browser hiển thị
const SLOW_MODE = true;  // Chậm để quan sát
```

## ⚙️ Chrome Options được sử dụng

```php
--no-sandbox
--disable-dev-shm-usage  
--start-maximized
--enable-automation
--disable-background-timer-throttling
--disable-renderer-backgrounding
--disable-web-security
```

## 🎬 Các chế độ demo

### 1. Live Show (live_show.bat)
- **Mục đích**: Xem automation trực tiếp
- **Thời gian**: 3-4 phút  
- **Tính năng**: Scroll, navigate, resize window
- **Không screenshot**: Chỉ quan sát

### 2. Quick Test (quick_chrome_test.bat)  
- **Mục đích**: Kiểm tra nhanh setup
- **Thời gian**: 10 giây
- **Tính năng**: Mở trang chủ, hiển thị title

### 3. Watch Only (WatchOnlyTest.php)
- **Mục đích**: Demo chi tiết với interaction
- **Tính năng**: Đầy đủ navigation, scrolling, responsive

## 🚨 Troubleshooting

### ChromeDriver không tìm thấy
```bash
# Kiểm tra file tồn tại
dir "E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64\chromedriver.exe"

# Chạy kiểm tra
check_chromedriver.bat
```

### Chrome không mở
1. Kiểm tra Chrome browser đã cài đặt
2. Kiểm tra quyền truy cập ChromeDriver
3. Tắt process ChromeDriver cũ nếu có

### Website không accessible  
1. Đảm bảo XAMPP đang chạy
2. Kiểm tra http://localhost/Coffee-Shop trong browser
3. Kiểm tra Apache service status

### Test bị lỗi
```bash
# Chạy quick test trước
php tests/QuickChromeTest.php

# Kiểm tra log để debug
```

## 🔄 So sánh Docker vs Local

| Tiêu chí | Docker Selenium | ChromeDriver Local |
|----------|-----------------|-------------------|
| Setup | Phức tạp | Đơn giản ✅ |
| Performance | Chậm hơn | Nhanh hơn ✅ |
| Resource | Tốn RAM nhiều | Ít tốn RAM ✅ |
| Observation | Khó quan sát | Dễ quan sát ✅ |
| Debugging | Khó debug | Dễ debug ✅ |
| Stability | Ít ổn định | Ổn định hơn ✅ |

## 📝 Lưu ý quan trọng

1. **XAMPP phải chạy** để website accessible
2. **ChromeDriver path** phải chính xác  
3. **Chrome browser** phải được cài đặt
4. **Process ChromeDriver** sẽ tự động cleanup sau test
5. **UI Mode** cho phép quan sát browser thao tác

## 🎉 Kết luận

Việc chuyển sang ChromeDriver local mang lại:
- **Trải nghiệm tốt hơn**: Quan sát trực tiếp automation
- **Performance cao hơn**: Không qua Docker layer  
- **Debugging dễ hơn**: Direct access to browser
- **Setup đơn giản hơn**: Không cần Docker container

> 💡 **Tip**: Chạy `live_show.bat` để có trải nghiệm tốt nhất!
