-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2024 at 06:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coffee-shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `ID` int(10) NOT NULL,
  `admin_name` varchar(200) NOT NULL,
  `admin_email` varchar(200) NOT NULL,
  `admin_password` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`ID`, `admin_name`, `admin_email`, `admin_password`, `created_at`) VALUES
(1, 'Rafiqul_Jakir', 'user.first@gmail.com', '$2y$10$ZUFNmCvSBaIk20ZUjVrb4.wWDv5FpsNlRnbmonL4cvDi8C/Xd/ZqW', '2024-01-31 16:53:34'),
(7, 'abc', 'abc@gmail.com', '$2y$10$Pf20/N9sdBD.hut2aZidHevXchqHPi3DBZsiQrKmBwPHqyP7wvmfi', '2024-01-31 18:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `ID` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `date` varchar(200) DEFAULT NULL,
  `time` varchar(200) NOT NULL,
  `phone_number` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`ID`, `user_id`, `first_name`, `last_name`, `date`, `time`, `phone_number`, `message`, `status`, `created_at`) VALUES
(21, 1, 'Rafiqul', 'JAkir', '2/25/2024', '12:30am', '01879074212', 'adsaa d', 'Confirm', '2024-02-01 17:56:17');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `ID` int(10) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_image` varchar(200) NOT NULL,
  `product_price` int(10) NOT NULL,
  `product_description` text NOT NULL,
  `product_size` varchar(50) NOT NULL,
  `product_quantity` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`ID`, `product_title`, `product_image`, `product_price`, `product_description`, `product_size`, `product_quantity`, `user_id`, `product_id`, `created_at`) VALUES
(63, 'Midnight Mocha (Espresso, Dark Chocolate, and Mint)', 'menu-1.jpg', 9, 'This decadent drink is a marriage of rich espresso, dark chocolate syrup, and refreshing mint. The espresso provides a robust kick, the chocolate adds a luxurious sweetness, and the mint leaves a cool, tingling sensation on your palate. It\'s like a dessert in a cup, perfect for indulging on a chilly day.', 'Medium', 5, 1, 2, '2024-01-28 14:32:39'),
(64, 'Arabian Mocha Delight', 'menu-2.jpg', 5, 'Indulge in the rich and exotic flavors of our Arabian Mocha Delight coffee. Sourced from the finest Arabica beans grown in the high-altitude regions of Yemen, this meticulously crafted blend offers a symphony of bold and nuanced tastes. You\'ll experience a velvety smooth texture with notes of dark chocolate, hints of cardamom, and a lingering, earthy finish.', 'Large', 4, 1, 1, '2024-01-30 12:29:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ID` int(10) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `streetaddress` varchar(200) NOT NULL,
  `appartment` varchar(200) NOT NULL,
  `towncity` varchar(200) NOT NULL,
  `postcode` int(10) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `payable_total_cost` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ID`, `firstname`, `lastname`, `streetaddress`, `appartment`, `towncity`, `postcode`, `phone`, `email`, `payable_total_cost`, `user_id`, `status`, `created_at`) VALUES
(1, 'asdasd', 'Rafiqul', 'asdasd', 'adasd', 'asdasd', 1218, '01879.74512', 'abc@gmail.com', 34, 1, 'Delivered', '2024-01-28 08:00:53');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ID` int(10) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ID`, `product_title`, `image`, `description`, `price`, `created_at`, `type`) VALUES
(1, 'Arabian Mocha Delight', 'menu-2.jpg', 'Indulge in the rich and exotic flavors of our Arabian Mocha Delight coffee. Sourced from the finest Arabica beans grown in the high-altitude regions of Yemen, this meticulously crafted blend offers a symphony of bold and nuanced tastes. You\'ll experience a velvety smooth texture with notes of dark chocolate, hints of cardamom, and a lingering, earthy finish.', 5, '2024-01-26 06:00:31', 'drink'),
(2, 'Midnight Mocha (Espresso, Dark Chocolate, and Mint)', 'menu-1.jpg', 'This decadent drink is a marriage of rich espresso, dark chocolate syrup, and refreshing mint. The espresso provides a robust kick, the chocolate adds a luxurious sweetness, and the mint leaves a cool, tingling sensation on your palate. It\'s like a dessert in a cup, perfect for indulging on a chilly day.', 9, '2024-01-26 15:17:03', 'drink');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `ID` int(10) NOT NULL,
  `review` varchar(1000) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`ID`, `review`, `user_name`, `created_at`) VALUES
(1, 'Experience the bold and intense flavors of Espresso Supreme! This rich and robust coffee blend offers a powerful kick that coffee enthusiasts crave. With its velvety crema and concentrated essence, Espresso Supreme is perfect for those who savor a strong and satisfying coffee experience.', 'user.first', '2024-01-31 05:11:12'),
(2, 'Indulge in the luscious Smooth Vanilla Latte – a delightful blend of smooth espresso and creamy vanilla goodness. This coffee creation strikes a perfect balance between bold coffee flavor and sweet vanilla notes, making it an ideal choice for those who prefer a milder yet flavorful coffee experience.', 'user.first', '2024-01-31 14:24:35'),
(3, 'Embark on a journey of exquisite taste with Ethiopian Yirgacheffe coffee. Known for its bright acidity, floral aroma, and citrusy undertones, this single-origin coffee is a true connoisseur\'s delight. The distinct flavors of Ethiopian Yirgacheffe capture the essence of the region\'s unique coffee culture.', 'user.first', '2024-01-31 14:24:49'),
(4, 'For those seeking a caffeine-free option without compromising on taste, our Decaf Delight is the perfect choice. This well-balanced decaffeinated coffee boasts a smooth profile with hints of nutty sweetness. Enjoy the comforting warmth of a great cup of coffee any time of the day.', 'user.first', '2024-01-31 14:25:00'),
(5, 'this is Review 5', 'user.first', '2024-01-31 14:25:10'),
(6, 'this is Review 6', 'user.first', '2024-01-31 14:25:30'),
(7, 'this is Review 7', 'user.first', '2024-01-31 14:25:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(10) NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `user_pass` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `user_name`, `user_email`, `user_pass`, `created_at`) VALUES
(1, 'user.first', 'user.first@gmail.com', '$2y$10$ZUFNmCvSBaIk20ZUjVrb4.wWDv5FpsNlRnbmonL4cvDi8C/Xd/ZqW', '2024-01-25 05:09:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
