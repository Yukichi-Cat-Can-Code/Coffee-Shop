# Coffee Shop E-Commerce Website

Chào mừng bạn đến với dự án Coffee Shop E-Commerce Website!  
Đây là website bán hàng cà phê động, sử dụng PHP & MySQL, giao diện hiện đại, dễ sử dụng và dễ tùy biến.

## Tính năng nổi bật

- Quản lý sản phẩm, danh mục, đơn hàng động qua database
- Giao diện responsive, đẹp mắt (Bootstrap)
- Đăng ký, đăng nhập, quản lý tài khoản người dùng
- Giỏ hàng, đặt hàng, thanh toán qua PayPal (USD)
- Quản trị viên quản lý sản phẩm, đơn hàng, người dùng

## Yêu cầu hệ thống

- Xampp (Apache)
- PHP >= 7.2
- MySQL/MariaDB

## Hướng dẫn cài đặt

1. **Clone source về máy:**

   ```
   git clone https://github.com/your-username/coffee-shop-ecommerce.git
   ```

2. **Tạo database và import dữ liệu:**

   - Tạo database mới, ví dụ: `coffee-shop`
   - Import file `database.sql` có sẵn trong thư mục dự án

3. **Cấu hình kết nối database:**  
   Mở file `config/config.php` và chỉnh lại thông tin:

   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "coffee-shop";
   ```

4. **Khởi động web server (XAMPP/WAMP...)**  
   Truy cập trình duyệt với đường dẫn:
   ```
   http://localhost/Coffee-Shop/
   ```

## Đăng nhập thử nghiệm

- **Tài khoản quản trị (Admin):**

  - Đường dẫn: [http://localhost/Coffee-Shop/admin/](http://localhost/Coffee-Shop/admin-panel/)
  - Username: `maiminh123@gmail.com`
  - Password: `maiminh123`

- **Tài khoản khách hàng (Store):**
  - Đường dẫn: [http://localhost/Coffee-Shop/](http://localhost/Coffee-Shop/)
  - Username: `abcdef@gmail.com`
  - Password: `abcdefgh`

> _Bạn có thể tạo thêm tài khoản khách hàng mới trực tiếp trên website._

## Một số đường dẫn chính

- Trang chủ: [http://localhost/Coffee-Shop/](http://localhost/Coffee-Shop/)
- Trang quản trị: [http://localhost/Coffee-Shop/admin/](http://localhost/Coffee-Shop/admin/)
