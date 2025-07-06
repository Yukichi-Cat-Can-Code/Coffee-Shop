-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for coffeeshop
DROP DATABASE IF EXISTS `coffeeshop`;
CREATE DATABASE IF NOT EXISTS `coffeeshop` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `coffeeshop`;

-- Dumping structure for table coffeeshop.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(200) NOT NULL,
  `admin_email` varchar(200) NOT NULL,
  `admin_password` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.admins: ~2 rows (approximately)
INSERT INTO `admins` (`ID`, `admin_name`, `admin_email`, `admin_password`, `created_at`) VALUES
	(6, 'Mai Minh', 'maiminh123@gmail.com', '$2y$10$mxL3UJkPvK45LXIDz0qXHOpA9yPYTeW8RIJjUciiry8l/gZdJ/w0e', '2025-06-21 16:21:29'),
	(10, 'Super Admin', 'super_admin@gmail.com', '$2y$10$qXzxwsXCh3DhM3UEale.NOtG/B6V0ZqLFRRhtt6bQOKPfqEcVGX5i', '2025-06-30 10:34:32');

-- Dumping structure for table coffeeshop.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `date` varchar(200) DEFAULT NULL,
  `time` varchar(200) NOT NULL,
  `phone_number` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.bookings: ~11 rows (approximately)
INSERT INTO `bookings` (`ID`, `user_id`, `first_name`, `last_name`, `date`, `time`, `phone_number`, `message`, `status`, `created_at`) VALUES
	(41, 2, 'Mai', 'Minh', '03/06/2025', '08:00', '0912345678', 'Đặt bàn cho 2 người', 'Confirmed', '2025-06-02 13:15:00'),
	(42, 3, 'Ngọc', 'Anh', '06/06/2025', '10:30', '0987654321', 'Tiệc sinh nhật cho 5 người', 'Pending', '2025-06-05 01:00:00'),
	(43, 4, 'Quang', 'Hải', '09/06/2025', '14:00', '0901234567', 'Họp nhóm bạn, cần bàn yên tĩnh', 'Confirmed', '2025-06-08 07:30:00'),
	(44, 5, 'Thu', 'Trang', '12/06/2025', '19:45', '0934567890', 'Hẹn hò, muốn bàn góc cửa sổ', 'Pending', '2025-06-11 11:00:00'),
	(45, 6, 'Văn', 'Nam', '15/06/2025', '11:15', '0945678901', 'Đặt bàn cho gia đình 6 người, có 2 trẻ em', 'Cancelled', '2025-06-14 02:00:00'),
	(46, 7, 'Minh', 'Châu', '18/06/2025', '18:00', '0956789012', 'Họp lớp, khoảng 10 người', 'Confirmed', '2025-06-17 09:30:00'),
	(47, 8, 'Hà', 'Phương', '20/06/2025', '09:30', '0967890123', 'Đặt bàn ngoài trời, nếu trời đẹp', 'Pending', '2025-06-19 00:45:00'),
	(48, 9, 'Tuấn', 'Kiệt', '23/06/2025', '13:00', '0978901234', 'Tiệc nhỏ mừng kỷ niệm', 'Confirmed', '2025-06-22 04:00:00'),
	(49, 10, 'Lan', 'Hương', '26/06/2025', '17:45', '0989012345', 'Đặt bàn cho 4 người, gần khu vực âm nhạc', 'Cancelled', '2025-06-25 08:30:00'),
	(50, 11, 'Bảo', 'Long', '29/06/2025', '20:15', '0990123456', 'Gặp mặt bạn bè, cần bàn rộng', 'Confirmed', '2025-06-28 11:00:00'),
	(53, 0, 'Mai', 'Minh', '2025-07-08', '18:45', '1234567890', 'Đặt bàn test', 'Pending', '2025-07-01 16:43:31');

-- Dumping structure for table coffeeshop.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `product_title` varchar(200) NOT NULL,
  `product_image` varchar(200) NOT NULL,
  `product_price` int(10) NOT NULL,
  `product_description` text NOT NULL,
  `product_size` varchar(50) NOT NULL,
  `product_quantity` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.cart: ~2 rows (approximately)
INSERT INTO `cart` (`ID`, `product_title`, `product_image`, `product_price`, `product_description`, `product_size`, `product_quantity`, `user_id`, `product_id`, `created_at`) VALUES
	(63, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 'menu-1.jpg', 65000, 'Thức uống đặc biệt kết hợp giữa espresso đậm đà, sô-cô-la đen và bạc hà mát lạnh. Espresso mang đến hương vị mạnh mẽ, sô-cô-la thêm vào vị ngọt sang trọng, và bạc hà để lại cảm giác mát lạnh trên đầu lưỡi. Như một món tráng miệng trong tách, hoàn hảo để thưởng thức vào những ngày se lạnh.', 'Vừa', 5, 1, 2, '2024-01-28 06:32:39'),
	(64, 'Mocha Ả Rập Đặc Biệt', 'menu-2.jpg', 55000, 'Đắm chìm trong hương vị phong phú và độc đáo của cà phê Mocha Ả Rập Đặc Biệt. Được chế biến từ hạt Arabica thượng hạng trồng tại vùng cao nguyên Yemen, hỗn hợp được chế tác tỉ mỉ này mang đến một bản hòa tấu của những hương vị đậm đà và tinh tế. Bạn sẽ trải nghiệm một kết cấu mượt mà như nhung với nốt hương sô-cô-la đen, gợi ý của bạch đậu khấu và một kết thúc đất lâu dài.', 'Lớn', 4, 1, 1, '2024-01-30 04:29:23'),
	(65, 'Mocha Ả Rập Đặc Biệt', 'menu-2.jpg', 55000, 'Đắm chìm trong hương vị phong phú và độc đáo của cà phê Mocha Ả Rập Đặc Biệt. Được chế biến từ hạt Arabica thượng hạng trồng tại vùng cao nguyên Yemen, hỗn hợp được chế tác tỉ mỉ này mang đến một bản hòa tấu của những hương vị đậm đà và tinh tế. Bạn sẽ trải nghiệm một kết cấu mượt mà như nhung với nốt hương sô-cô-la đen, gợi ý của bạch đậu khấu và một kết thúc đất lâu dài.', 'Vừa', 1, 12, 1, '2025-07-04 05:03:30');

-- Dumping structure for table coffeeshop.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `streetaddress` varchar(200) NOT NULL,
  `apartment` varchar(200) NOT NULL,
  `towncity` varchar(200) NOT NULL,
  `postcode` int(10) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `payable_total_cost` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.orders: ~11 rows (approximately)
INSERT INTO `orders` (`ID`, `firstname`, `lastname`, `streetaddress`, `apartment`, `towncity`, `postcode`, `phone`, `email`, `payable_total_cost`, `user_id`, `status`, `created_at`, `order_date`) VALUES
	(11, 'Mai', 'Minh', '123 Lê Lợi', 'Tầng 2', 'Hà Nội', 100000, '0912345678', 'maiminh@gmail.com', 150000, 2, 'Delivered', '2025-06-03 01:00:00', '2025-07-03 16:28:19'),
	(12, 'Ngọc', 'Anh', '45 Trần Phú', 'A12', 'Hải Phòng', 180000, '0987654321', 'ngocanh@gmail.com', 220000, 3, 'Delivered', '2025-06-06 02:30:00', '2025-07-03 16:28:19'),
	(13, 'Quang', 'Hải', '78 Nguyễn Huệ', 'B5', 'Đà Nẵng', 550000, '0901234567', 'quanghai@gmail.com', 320000, 4, 'Cancelled', '2025-06-09 06:00:00', '2025-07-03 16:28:19'),
	(14, 'Thu', 'Trang', '12 Lý Thường Kiệt', 'C3', 'Hà Nội', 120000, '0934567890', 'thutrang@gmail.com', 180000, 5, 'Delivered', '2025-06-12 11:45:00', '2025-07-03 16:28:19'),
	(15, 'Văn', 'Nam', '89 Lê Duẩn', '', 'Huế', 530000, '0945678901', 'vannam@gmail.com', 210000, 6, 'Pending', '2025-06-15 03:15:00', '2025-07-03 16:28:19'),
	(16, 'Minh', 'Châu', '56 Phan Đình Phùng', 'Tầng 1', 'Hà Nội', 100000, '0956789012', 'minhchau@gmail.com', 260000, 7, 'Delivered', '2025-06-18 10:00:00', '2025-07-03 16:28:19'),
	(17, 'Hà', 'Phương', '34 Nguyễn Trãi', 'P.202', 'TP.HCM', 700000, '0967890123', 'haphuong@gmail.com', 190000, 8, 'Pending', '2025-06-20 01:30:00', '2025-07-03 16:28:19'),
	(18, 'Tuấn', 'Kiệt', '67 Lý Nam Đế', '', 'Đà Nẵng', 550000, '0978901234', 'tuankiet@gmail.com', 400000, 9, 'Delivered', '2025-06-23 05:00:00', '2025-07-03 16:28:19'),
	(19, 'Lan', 'Hương', '23 Điện Biên Phủ', 'A8', 'Hải Phòng', 180000, '0989012345', 'lanhuong@gmail.com', 170000, 10, 'Cancelled', '2025-06-26 09:45:00', '2025-07-03 16:28:19'),
	(20, 'Bảo', 'Long', '11 Nguyễn Văn Cừ', '', 'Hà Nội', 100000, '0990123456', 'baolong@gmail.com', 250000, 11, 'Delivered', '2025-06-29 12:15:00', '2025-07-03 16:28:19'),
	(21, 'Mai', 'Minh', 'đường a phường b quận c tỉnh d', 'A12', 'Hà Nội', 100000, '0123456789', 'maiminh@gmail.com', 125000, 1, 'Pending', '2025-07-03 09:28:39', '2025-07-03 16:28:39');

-- Dumping structure for table coffeeshop.order_details
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.order_details: ~27 rows (approximately)
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
	(1, 21, 4, 'Bánh Âu việt quốc', 1, 35000.00, 35000.00),
	(2, 21, 5, 'Bò bít tết 5 sao', 1, 90000.00, 90000.00),
	(3, 22, 4, 'Bánh Âu việt quốc', 1, 35000.00, 35000.00),
	(4, 22, 5, 'Bò bít tết 5 sao', 1, 90000.00, 90000.00),
	(5, 22, 6, 'Cà phê sữa đá truyền thống', 1, 30000.00, 30000.00),
	(6, 11, 1, 'Mocha Ả Rập Đặc Biệt', 2, 55000.00, 110000.00),
	(7, 11, 4, 'Bánh Âu việt quốc', 1, 35000.00, 35000.00),
	(8, 12, 2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 3, 65000.00, 195000.00),
	(9, 12, 4, 'Bánh Âu việt quốc', 1, 35000.00, 35000.00),
	(10, 13, 5, 'Bò bít tết 5 sao', 3, 90000.00, 270000.00),
	(11, 13, 1, 'Mocha Ả Rập Đặc Biệt', 1, 50000.00, 50000.00),
	(12, 14, 3, 'Burger bò một trứng', 2, 50000.00, 100000.00),
	(13, 14, 4, 'Bánh Âu việt quốc', 2, 35000.00, 70000.00),
	(14, 15, 5, 'Bò bít tết 5 sao', 2, 90000.00, 180000.00),
	(15, 15, 7, 'Nước ép cam', 1, 30000.00, 30000.00),
	(16, 16, 1, 'Mocha Ả Rập Đặc Biệt', 2, 55000.00, 110000.00),
	(17, 16, 5, 'Bò bít tết 5 sao', 1, 90000.00, 90000.00),
	(18, 16, 6, 'Cà phê sữa đá truyền thống', 2, 30000.00, 60000.00),
	(19, 17, 2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 2, 65000.00, 130000.00),
	(20, 17, 6, 'Cà phê sữa đá truyền thống', 2, 30000.00, 60000.00),
	(21, 18, 5, 'Bò bít tết 5 sao', 4, 90000.00, 360000.00),
	(22, 18, 7, 'Nước ép cam', 1, 30000.00, 30000.00),
	(23, 19, 1, 'Mocha Ả Rập Đặc Biệt', 2, 55000.00, 110000.00),
	(24, 19, 6, 'Cà phê sữa đá truyền thống', 2, 30000.00, 60000.00),
	(25, 20, 3, 'Burger bò một trứng', 3, 50000.00, 150000.00),
	(26, 20, 2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 1, 65000.00, 65000.00),
	(27, 20, 4, 'Bánh Âu việt quốc', 1, 35000.00, 35000.00);

-- Dumping structure for table coffeeshop.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `payment_method` enum('Tiền mặt','Thẻ tín dụng','Thẻ ghi nợ','Ví điện tử','Thẻ quà tặng') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `card_last_digits` varchar(4) DEFAULT NULL,
  `status` enum('Hoàn thành','Thất bại','Hoàn tiền') NOT NULL DEFAULT 'Hoàn thành',
  `admin_id` int(10) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.payments: ~5 rows (approximately)
INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount`, `transaction_id`, `card_last_digits`, `status`, `admin_id`, `payment_date`) VALUES
	(1, 1, 'Tiền mặt', 118800.00, NULL, NULL, 'Hoàn thành', 6, '2025-07-01 11:15:00'),
	(2, 2, 'Thẻ tín dụng', 88825.00, 'CARD-20250702-001', '4321', 'Hoàn thành', 6, '2025-07-02 14:40:00'),
	(3, 3, 'Tiền mặt', 210375.00, NULL, NULL, 'Hoàn thành', 10, '2025-07-02 19:30:00'),
	(4, 4, 'Ví điện tử', 182875.00, 'MOMO-20250703-002', NULL, 'Hoàn thành', 10, '2025-07-03 10:00:00'),
	(5, 5, 'Thẻ tín dụng', 316800.00, 'CARD-20250703-003', '5678', 'Hoàn thành', 6, '2025-07-03 22:00:00');

-- Dumping structure for table coffeeshop.pos_orders
CREATE TABLE IF NOT EXISTS `pos_orders` (
  `order_id` int(10) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(10) DEFAULT NULL,
  `order_type` enum('Tại quán','Mang đi','Giao hàng') NOT NULL DEFAULT 'Tại quán',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('Chưa thanh toán','Đã thanh toán','Đã hủy') NOT NULL DEFAULT 'Chưa thanh toán',
  `order_status` enum('Mới','Đang chế biến','Sẵn sàng','Đã phục vụ','Hoàn thành','Đã hủy') NOT NULL DEFAULT 'Mới',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `admin_id` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `customer_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `pos_orders_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.pos_orders: ~5 rows (approximately)
INSERT INTO `pos_orders` (`order_id`, `table_number`, `order_type`, `total_amount`, `discount_amount`, `tax_amount`, `final_amount`, `payment_status`, `order_status`, `customer_name`, `customer_phone`, `admin_id`, `created_at`, `completed_at`, `customer_id`) VALUES
	(1, 'A1', 'Tại quán', 120000.00, 12000.00, 10800.00, 118800.00, 'Đã thanh toán', 'Hoàn thành', 'Mai Minh', '0912345678', 6, '2025-07-01 10:30:00', '2025-07-01 11:15:00', 1),
	(2, NULL, 'Mang đi', 85000.00, 4250.00, 8075.00, 88825.00, 'Đã thanh toán', 'Hoàn thành', 'Ngọc Anh', '0987654321', 6, '2025-07-02 14:20:00', '2025-07-02 14:40:00', 2),
	(3, 'B3', 'Tại quán', 225000.00, 33750.00, 19125.00, 210375.00, 'Đã thanh toán', 'Hoàn thành', 'Quang Hải', '0901234567', 10, '2025-07-02 18:00:00', '2025-07-02 19:30:00', 3),
	(4, NULL, 'Giao hàng', 175000.00, 8750.00, 16625.00, 182875.00, 'Đã thanh toán', 'Hoàn thành', 'Thu Trang', '0934567890', 10, '2025-07-03 09:15:00', '2025-07-03 10:00:00', 4),
	(5, 'C2', 'Tại quán', 320000.00, 32000.00, 28800.00, 316800.00, 'Đã thanh toán', 'Hoàn thành', 'Văn Nam', '0945678901', 6, '2025-07-03 20:45:00', '2025-07-03 22:00:00', 5);

-- Dumping structure for table coffeeshop.pos_order_items
CREATE TABLE IF NOT EXISTS `pos_order_items` (
  `item_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(5) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `customizations` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `pos_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `pos_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.pos_order_items: ~10 rows (approximately)
INSERT INTO `pos_order_items` (`item_id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `size`, `customizations`, `subtotal`, `created_at`) VALUES
	(1, 1, 1, 'Mocha Ả Rập Đặc Biệt', 1, 55000.00, 'Vừa', NULL, 55000.00, '2025-07-01 10:30:00'),
	(2, 1, 2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 1, 65000.00, 'Lớn', 'Thêm đá', 65000.00, '2025-07-01 10:30:00'),
	(3, 2, 4, 'Bánh Âu việt quốc', 1, 35000.00, NULL, NULL, 35000.00, '2025-07-02 14:20:00'),
	(4, 2, 1, 'Mocha Ả Rập Đặc Biệt', 1, 50000.00, 'Nhỏ', 'Ít đường', 50000.00, '2025-07-02 14:20:00'),
	(5, 3, 5, 'Bò bít tết 5 sao', 2, 90000.00, NULL, 'Chín vừa', 180000.00, '2025-07-02 18:00:00'),
	(6, 3, 2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 1, 45000.00, 'Nhỏ', NULL, 45000.00, '2025-07-02 18:00:00'),
	(7, 4, 3, 'Burger bò một trứng', 2, 50000.00, NULL, 'Không hành', 100000.00, '2025-07-03 09:15:00'),
	(8, 4, 1, 'Mocha Ả Rập Đặc Biệt', 1, 75000.00, 'Siêu lớn', 'Thêm whipped cream', 75000.00, '2025-07-03 09:15:00'),
	(9, 5, 5, 'Bò bít tết 5 sao', 2, 90000.00, NULL, 'Tái', 180000.00, '2025-07-03 20:45:00'),
	(10, 5, 4, 'Bánh Âu việt quốc', 4, 35000.00, NULL, NULL, 140000.00, '2025-07-03 20:45:00');

-- Dumping structure for table coffeeshop.product
CREATE TABLE IF NOT EXISTS `product` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `product_title` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(200) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.product: ~7 rows (approximately)
INSERT INTO `product` (`ID`, `product_title`, `image`, `description`, `price`, `created_at`, `type`) VALUES
	(1, 'Mocha Ả Rập Đặc Biệt', 'menu-2.jpg', 'Đắm chìm trong hương vị phong phú và độc đáo của cà phê Mocha Ả Rập Đặc Biệt. Được chế biến từ hạt Arabica thượng hạng trồng tại vùng cao nguyên Yemen, hỗn hợp được chế tác tỉ mỉ này mang đến một bản hòa tấu của những hương vị đậm đà và tinh tế. Bạn sẽ trải nghiệm một kết cấu mượt mà như nhung với nốt hương sô-cô-la đen, gợi ý của bạch đậu khấu và một kết thúc đất lâu dài.', 55000, '2024-01-25 22:00:31', 'drink'),
	(2, 'Mocha Đêm Khuya (Espresso, Sô-cô-la Đen và Bạc Hà)', 'menu-1.jpg', 'Thức uống đặc biệt kết hợp giữa espresso đậm đà, sô-cô-la đen và bạc hà mát lạnh. Espresso mang đến hương vị mạnh mẽ, sô-cô-la thêm vào vị ngọt sang trọng, và bạc hà để lại cảm giác mát lạnh trên đầu lưỡi. Như một món tráng miệng trong tách, hoàn hảo để thưởng thức vào những ngày se lạnh.', 65000, '2024-01-26 07:17:03', 'drink'),
	(3, 'Burger bò một trứng', 'product_1751418909_6864881d58950.jpg', 'Burger bò hảo hạng được làm từ thịt bò 100% tươi ngon, kèm theo một quả trứng chiên vừa chảy lòng đào, rau xà lách, cà chua và sốt đặc biệt của quán. Phục vụ kèm khoai tây chiên giòn.', 50000, '2025-07-02 01:15:09', 'food'),
	(4, 'Bánh Âu việt quốc', 'product_1751418939_6864883bef9d5.jpg', 'Bánh ngọt kiểu Âu kết hợp với hương vị Việt Nam, được làm từ bột mì cao cấp, trứng tươi, sữa béo và phủ lớp kem tươi mịn màng cùng trái cây tươi theo mùa.', 35000, '2025-07-02 01:15:39', 'dessert'),
	(5, 'Bò bít tết 5 sao', 'product_1751418982_686488662f299.jpg', 'Thịt bò được nhập khẩu từ Úc, thái miếng dày vừa phải, được ướp với các loại gia vị đặc biệt và nướng theo yêu cầu của khách hàng. Phục vụ kèm với khoai tây nghiền, rau củ xào và sốt đen đậm đà.', 90000, '2025-07-02 01:16:22', 'food'),
	(6, 'Cà phê sữa đá truyền thống', 'product_1751512282_6865f4da62f0c.jpg', 'Hương vị đậm đà của cà phê nguyên chất kết hợp với sữa đặc, được phục vụ cùng đá viên trong ly cao. Thức uống truyền thống mang đậm bản sắc Việt Nam.', 30000, '2025-07-03 10:00:00', 'drink'),
	(7, 'Nước ép cam', 'product_1751512357_6865f525cd5bc.jpg', 'Nước ép được làm từ cam tươi, kết hợp với mật ong nguyên chất. Thức uống thanh mát, giải nhiệt tuyệt vời cho ngày hè.', 30000, '2025-07-03 10:05:00', 'drink'),
	(8, 'Bánh kép dâu', 'product_1751560676_6866b1e4431ca.jpg', 'Bánh kếp kiểu Âu', 55000, '2025-07-03 16:37:56', 'food');

-- Dumping structure for table coffeeshop.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `review` varchar(1000) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `order_id` int(10) DEFAULT NULL,
  `rating` int(1) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.reviews: ~10 rows (approximately)
INSERT INTO `reviews` (`ID`, `review`, `user_name`, `user_id`, `order_id`, `rating`, `status`, `created_at`) VALUES
    (1, 'Cà phê thơm ngon, đậm đà, phục vụ nhiệt tình. Mình rất hài lòng!', 'MaiMinh', 1, 103, 5, 'Approved', '2025-06-03 00:15:00'),
    (2, 'Không gian quán yên tĩnh, thích hợp để làm việc và học tập. Cảm giác rất thư giãn.', 'NgocAnh', 2, 107, 4, 'Approved', '2025-06-05 01:30:00'),
    (3, 'Món Latte ở đây rất ngon, vị béo vừa phải, lớp bọt sữa mịn màng. Sẽ quay lại lần sau!', 'QuangHai', 3, 112, 5, 'Approved', '2025-06-07 02:45:00'),
    (4, 'Nhân viên thân thiện, phục vụ nhanh chóng. Đồ uống được làm rất cẩn thận và đẹp mắt. Rất đáng thử!', 'ThuTrang', 4, 118, 5, 'Approved', '2025-06-09 03:20:00'),
    (5, 'Mình thích món bánh ngọt ở đây, đặc biệt là bánh tiramisu, rất hợp với cà phê.', 'VanNam', 5, 121, 4, 'Approved', '2025-06-12 06:10:00'),
    (6, 'Giá cả hợp lý, chất lượng tuyệt vời. Đồ uống tươi ngon và có nhiều lựa chọn. Sẽ giới thiệu cho bạn bè.', 'MinhChau', 6, 125, 5, 'Approved', '2025-06-14 08:05:00'),
    (7, 'Không gian ngoài trời mát mẻ, view đẹp, phù hợp tụ tập bạn bè vào những ngày đẹp trời.', 'HaPhuong', 7, 129, 4, 'Approved', '2025-06-17 10:30:00'),
    (8, 'Cà phê sữa đá đúng chuẩn vị Việt Nam, đậm đà và thơm lừng. Mình rất thích!', 'TuanKiet', 8, 134, 5, 'Approved', '2025-06-20 00:55:00'),
    (9, 'Menu đa dạng, nhiều lựa chọn cho cả đồ uống nóng, lạnh và bánh ngọt. Mỗi lần đến đều có thể thử món mới.', 'LanHuong', 9, 137, 4, 'Pending', '2025-06-23 05:40:00'),
    (10, 'Quán sạch sẽ, wifi mạnh, không gian thoáng đãng. Rất thích hợp để làm việc hoặc đọc sách.', 'BaoLong', 10, 142, 5, 'Pending', '2025-06-27 11:25:00');

-- Dumping structure for table coffeeshop.users
CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(200) NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `user_pass` varchar(200) NOT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `street_address` varchar(200) DEFAULT NULL,
  `apartment` varchar(100) DEFAULT NULL,
  `town_city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `points` int(10) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table coffeeshop.users: ~12 rows (approximately)
INSERT INTO `users` (`ID`, `user_name`, `user_email`, `user_pass`, `user_phone`, `street_address`, `apartment`, `town_city`, `postcode`, `points`, `created_at`) VALUES
	(1, 'Mai Minh', 'maiminh@gmail.com', '$2y$10$QBDWZTQUiCohLY95woJK3ew/Aez7HSpQsgmfGLz9AXivCTA02czXy', '0912345678', '123 Lê Lợi', 'Tầng 2', 'Hà Nội', '100000', 28, '2024-01-24 21:09:51'),
	(2, 'Ngọc Anh', 'ngocanh@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0987654321', '45 Trần Phú', 'A12', 'Hải Phòng', '180000', 15, '2025-06-01 00:00:00'),
	(3, 'Quang Hải', 'quanghai@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0901234567', '78 Nguyễn Huệ', 'B5', 'Đà Nẵng', '550000', 32, '2025-06-02 01:15:00'),
	(4, 'Thu Trang', 'thutrang@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0934567890', '12 Lý Thường Kiệt', 'C3', 'Hà Nội', '120000', 18, '2025-06-03 02:30:00'),
	(5, 'Văn Nam', 'vannam@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0945678901', '89 Lê Duẩn', '', 'Huế', '530000', 21, '2025-06-04 03:45:00'),
	(6, 'Minh Châu', 'minhchau@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0956789012', '56 Phan Đình Phùng', 'Tầng 1', 'Hà Nội', '100000', 29, '2025-06-05 05:00:00'),
	(7, 'Hà Phương', 'haphuong@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0967890123', '34 Nguyễn Trãi', 'P.202', 'TP.HCM', '700000', 14, '2025-06-06 06:15:00'),
	(8, 'Tuấn Kiệt', 'tuankiet@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0978901234', '67 Lý Nam Đế', '', 'Đà Nẵng', '550000', 40, '2025-06-07 07:30:00'),
	(9, 'Lan Hương', 'lanhuong@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0989012345', '23 Điện Biên Phủ', 'A8', 'Hải Phòng', '180000', 17, '2025-06-08 08:45:00'),
	(10, 'Bảo Long', 'baolong@gmail.com', '$2y$10$abCdEfGhIjKlMnOpQrStUv1234567890AbCdEfGhIjK', '0990123456', '11 Nguyễn Văn Cừ', '', 'Hà Nội', '100000', 24, '2025-06-09 10:00:00'),
	(11, 'ABCD', 'abcd@gmail.com', '$2y$10$iYG19XY5w4OG8ytVg5tO6OpsoBt5nFNNPDTtDwh0JTbdNW91jvF.a', NULL, NULL, NULL, NULL, NULL, 0, '2025-07-04 01:28:26'),
	(12, 'ABCDEF', 'abcdef@gmail.com', '$2y$10$20Tz7f3an6/xCmTyrea3Cuvj0Pdaju4yEz/x5vIERtBdIAhR8NrQO', NULL, NULL, NULL, NULL, NULL, 0, '2025-07-04 01:29:59');

ALTER TABLE `orders`
ADD CONSTRAINT `fk_orders_user_id`
FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `bookings`
ADD CONSTRAINT `fk_bookings_user_id`
FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `reviews`
ADD CONSTRAINT `fk_reviews_user_id`
FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `cart`
ADD CONSTRAINT `fk_cart_user_id`
FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `order_details`
ADD CONSTRAINT `fk_order_details_order_id`
FOREIGN KEY (`order_id`) REFERENCES `orders` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `order_details`
ADD CONSTRAINT `fk_order_details_product_id`
FOREIGN KEY (`product_id`) REFERENCES `product` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`ID`)

ALTER TABLE `payments`
DROP FOREIGN KEY `payments_ibfk_1`;
ALTER TABLE `payments`
ADD CONSTRAINT `fk_payments_order_id`
FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

CONSTRAINT `pos_orders_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`ID`)

ALTER TABLE `pos_order_items`
DROP FOREIGN KEY `pos_order_items_ibfk_2`;
ALTER TABLE `pos_order_items`
ADD CONSTRAINT `fk_pos_order_items_product_id`
FOREIGN KEY (`product_id`) REFERENCES `product` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

CONSTRAINT `pos_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`order_id`) ON DELETE CASCADE

CREATE INDEX `idx_bookings_user_id` ON `bookings` (`user_id`);
CREATE INDEX `idx_bookings_status` ON `bookings` (`status`);

CREATE INDEX `idx_cart_user_id` ON `cart` (`user_id`);
CREATE INDEX `idx_cart_product_id` ON `cart` (`product_id`);

CREATE INDEX `idx_orders_created_at` ON `orders` (`created_at`);
CREATE INDEX `idx_orders_order_date` ON `orders` (`order_date`);

DROP INDEX `idx_product_id` ON `order_details`;

CREATE INDEX `idx_payments_payment_date` ON `payments` (`payment_date`);

CREATE INDEX `idx_pos_orders_created_at` ON `pos_orders` (`created_at`);
CREATE INDEX `idx_pos_orders_order_status` ON `pos_orders` (`order_status`);
CREATE INDEX `idx_pos_orders_payment_status` ON `pos_orders` (`payment_status`);

CREATE INDEX `idx_pos_order_items_created_at` ON `pos_order_items` (`created_at`);

CREATE INDEX `idx_product_type` ON `product` (`type`);

CREATE INDEX `idx_reviews_status` ON `reviews` (`status`);
CREATE INDEX `idx_reviews_created_at` ON `reviews` (`created_at`);

CREATE INDEX `idx_users_user_email` ON `users` (`user_email`);
CREATE INDEX `idx_users_user_phone` ON `users` (`user_phone`);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
