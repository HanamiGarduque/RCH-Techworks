-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 19, 2025 at 09:57 AM
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
-- Database: `water_delivery_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(10, 4, 4, 3, 40.00, 120.00, '2025-11-18 09:48:15', '2025-11-18 10:36:50'),
(11, 4, 7, 4, 10.00, 40.00, '2025-11-18 09:48:20', '2025-11-18 09:48:20'),
(12, 4, 9, 6, 25.00, 150.00, '2025-11-18 09:49:26', '2025-11-18 09:49:26'),
(13, 4, 2, 2, 170.00, 340.00, '2025-11-19 08:31:04', '2025-11-19 08:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pickup_time` datetime DEFAULT NULL,
  `delivery_time` datetime DEFAULT NULL,
  `delivery_status` enum('pending','enroute','delivered','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallon_ownership`
--

CREATE TABLE `gallon_ownership` (
  `ownership_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `gallon_type` varchar(100) DEFAULT NULL,
  `current_owner_id` int(11) NOT NULL,
  `code_value` varchar(255) DEFAULT NULL,
  `qr_image` varchar(255) DEFAULT NULL,
  `status` enum('Available','In-Use','Lost/Damaged') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallon_ownership`
--

INSERT INTO `gallon_ownership` (`ownership_id`, `item_id`, `gallon_type`, `current_owner_id`, `code_value`, `qr_image`, `status`, `created_at`) VALUES
(1, 1, '18.9L Round Gallon', 1, 'GAL-20251030001', 'qr_codes/QR-GAL-20251030001.png', 'Lost/Damaged', '2025-10-22 15:05:00'),
(2, 1, '18.9L Round Gallon', 1, 'GAL-20251105002', 'qr_codes/QR-GAL-20251105002.png', 'In-Use', '2025-10-22 15:05:00'),
(3, 1, '18.9L Round Gallon', 1, 'GAL-20251105003', 'qr_codes/QR-GAL-20251105003.png', 'Available', '2025-10-22 15:05:00'),
(4, 1, '18.9L Round Gallon', 1, 'GAL-20251105004', 'qr_codes/QR-GAL-20251105004.png', 'Lost/Damaged', '2025-10-22 15:05:00'),
(5, 1, '18.9L Round Gallon', 1, 'GAL-20251105005', 'qr_codes/QR-GAL-20251105005.png', 'In-Use', '2025-10-22 15:05:00'),
(6, 2, '20L Slim Gallon', 1, 'GAL-20251105006', 'qr_codes/QR-GAL-20251105006.png', 'Available', '2025-10-22 15:05:00'),
(7, 2, '20L Slim Gallon', 1, 'GAL-20251105007', 'qr_codes/QR-GAL-20251105007.png', 'Available', '2025-10-22 15:05:00'),
(8, 2, '20L Slim Gallon', 1, 'GAL-20251105008', 'qr_codes/QR-GAL-20251105008.png', 'In-Use', '2025-10-22 15:05:00'),
(9, 2, '20L Slim Gallon', 1, 'GAL-20251105009', 'qr_codes/QR-GAL-20251105009.png', 'Lost/Damaged', '2025-10-22 15:05:00'),
(10, 2, '20L Slim Gallon', 2, 'GAL-20251105010', 'qr_codes/QR-GAL-20251105010.png', 'In-Use', '2025-10-22 15:05:00'),
(23, 11, '10L Slim Gallon', 1, NULL, NULL, 'Available', '2025-11-06 12:13:49'),
(26, 2, '20L Slim Gallon', 1, NULL, NULL, 'Available', '2025-11-17 01:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `category` enum('gallons','accessories','others','services') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(20,2) NOT NULL,
  `total_quantity` int(11) DEFAULT 0,
  `item_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `for_sale` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = For Sale, 0 = Not For Sale'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `category`, `item_name`, `description`, `price`, `total_quantity`, `item_image`, `created_at`, `for_sale`) VALUES
(1, 'gallons', '18.9L Round Gallon', 'Main refill containers for customers', 150.00, 0, '../Assets/18.9L round gallon.jpg', '2025-10-22 14:39:37', 1),
(2, 'gallons', '20L Slim Gallon', 'Main refill containers for customers', 170.00, 0, '../Assets/20L box type gallon.jpg', '2025-10-22 14:39:37', 1),
(3, 'others', '500L Bottled   Water', 'Sealed bottled water', 20.00, 200, '../Assets/500ml water bottle.jpg', '2025-10-22 14:39:37', 1),
(4, 'others', '1L Bottled Water', 'Sealed bottled water', 40.00, 200, '../Assets/1L water bottle.jpg', '2025-10-22 14:39:37', 1),
(5, 'accessories', 'Faucet (Press Type)', 'For water flow control in gallons', 25.00, 50, '../Assets/faucet press.jpg\n', '2025-10-22 14:39:37', 1),
(6, 'accessories', 'Inner Cap', 'For slim gallons', 5.00, 100, '../Assets/inner cap.jpg\n', '2025-10-22 14:39:37', 1),
(7, 'accessories', 'Big Cap', 'For slim gallons', 10.00, 100, '../Assets/big cap.jpg', '2025-10-22 14:39:37', 1),
(8, 'accessories', 'Small Cap', 'For slim gallons', 5.00, 100, '../Assets/small cap.jpg\n', '2025-10-22 14:39:37', 1),
(9, 'accessories', 'Faucet (Rotate Type)', 'For water flow control in gallons', 25.00, 150, '../Assets/faucet rotate.jpg\n', '2025-10-22 14:39:37', 1),
(10, 'accessories', 'Shrink Wrap Film', 'Cap seal for gallon lid and faucet', 0.00, 150, '../Assets/shrink wrap.jpg\n', '2025-10-22 14:39:37', 0),
(11, 'gallons', '10L Slim Gallon', 'Main refill containers for customers', 120.00, 0, '../Assets/10L box type gallon.jpg', '2025-11-05 15:34:10', 1),
(12, 'services', 'Refill (18.9L RG)', 'Refilling service for 18.9L round gallon', 35.00, 0, '../Assets/refill.jpg', '2025-11-05 15:39:34', 1),
(13, 'services', 'Refill (20L SG)', 'Refilling service for 20L slim gallon', 35.00, 0, '../Assets/refill.jpg', '2025-11-05 15:39:34', 1),
(14, 'services', 'Refill (10L SG)', 'Refilling service for 10L slim gallon', 20.00, 0, '../Assets/refill.jpg', '2025-11-05 15:39:34', 1),
(15, 'others', 'Banana', 'sample', 20.00, 4, '../Assets/42e9as7nataai4a6jcufwg.jpeg', '2025-11-08 07:11:08', 0);

-- --------------------------------------------------------

--
-- Table structure for table `map_location`
--

CREATE TABLE `map_location` (
  `loc_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `message`, `created_at`, `image_path`, `is_read`) VALUES
(1, 1, 2, 'hi', '2025-11-08 11:59:44', NULL, 0),
(2, 1, 2, 'gusto mo saging?', '2025-11-08 12:00:12', NULL, 0),
(3, 1, 3, 'hey', '2025-11-08 12:31:47', NULL, 1),
(4, 1, 2, '', '2025-11-09 13:05:58', 'uploads/691091b608ed0_42e9as7nataai4a6jcufwg.jpeg', 0),
(5, 1, 3, 'd', '2025-11-09 13:06:41', NULL, 1),
(6, 3, 2, '', '2025-11-09 13:50:08', 'uploads/69109c1008480_42e9as7nataai4a6jcufwg.jpeg', 0),
(7, 3, 2, 'ito saging', '2025-11-09 14:18:40', NULL, 0),
(8, 2, 3, 'hi rider', '2025-11-09 15:16:31', NULL, 1),
(9, 2, 3, 'im the admin', '2025-11-09 15:16:39', NULL, 1),
(10, 2, 3, 'jk, im the customer', '2025-11-09 15:17:31', NULL, 1),
(11, 3, 1, 'hii admin', '2025-11-09 16:07:17', NULL, 1),
(12, 3, 3, 's', '2025-11-09 16:09:05', NULL, 1),
(13, 3, 2, 'hi', '2025-11-09 16:09:13', NULL, 0),
(14, 1, 3, 'im the admin', '2025-11-09 16:11:15', NULL, 1),
(15, 1, 2, 'im the admin', '2025-11-09 16:11:26', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `status` enum('read','unread') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `link`, `status`, `created_at`) VALUES
(62, 1, 'New Customer Item Order', 'A new customer placed an item orders. Order #ORD17635410047744 — Review details.', 'order_details.php?order_id=18', 'unread', '2025-11-19 08:30:04'),
(63, 3, 'Order Ready for Delivery', 'A new order is ready for delivery. Order #ORD17635410047744 — Delivery at 2025-11-19 08:30:04.', 'order_details.php?order_id=18', 'unread', '2025-11-19 08:30:04'),
(64, 1, 'Order Placed Successfully', 'Your order has been placed successfully! Order #ORD17635410047744 — Total: PHP 202.', 'order_details.php?order_id=18', 'unread', '2025-11-19 08:30:04'),
(65, 1, 'New Customer Item Order', 'A new customer placed an item orders. Order #ORD17635410102014 — Review details.', 'order_details.php?order_id=19', 'unread', '2025-11-19 08:30:10'),
(66, 3, 'Order Ready for Delivery', 'A new order is ready for delivery. Order #ORD17635410102014 — Delivery at 2025-11-19 08:30:10.', 'order_details.php?order_id=19', 'unread', '2025-11-19 08:30:10'),
(67, 1, 'Order Placed Successfully', 'Your order has been placed successfully! Order #ORD17635410102014 — Total: PHP 223.6.', 'order_details.php?order_id=19', 'unread', '2025-11-19 08:30:10'),
(68, 1, 'New Customer Item Order', 'A new customer placed an item orders. Order #ORD17635410184624 — Review details.', 'order_details.php?order_id=20', 'unread', '2025-11-19 08:30:18'),
(69, 3, 'Order Ready for Delivery', 'A new order is ready for delivery. Order #ORD17635410184624 — Delivery at 2025-11-19 08:30:18.', 'order_details.php?order_id=20', 'unread', '2025-11-19 08:30:18'),
(70, 1, 'Order Placed Successfully', 'Your order has been placed successfully! Order #ORD17635410184624 — Total: PHP 72.4.', 'order_details.php?order_id=20', 'unread', '2025-11-19 08:30:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_datetime` datetime DEFAULT NULL,
  `order_status` enum('pending','preparing','on_delivery','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_number`, `total_amount`, `delivery_datetime`, `order_status`, `created_at`) VALUES
(18, 1, 'ORD17635410047744', 202.00, '2025-11-19 08:30:04', 'pending', '2025-11-19 08:30:04'),
(19, 1, 'ORD17635410102014', 223.60, '2025-11-19 08:30:10', 'pending', '2025-11-19 08:30:10'),
(20, 1, 'ORD17635410184624', 72.40, '2025-11-19 08:30:18', 'pending', '2025-11-19 08:30:18');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`) VALUES
(1, 18, 1, 1, 150.00, 150.00, '2025-11-19 08:30:04'),
(2, 19, 1, 1, 150.00, 150.00, '2025-11-19 08:30:10'),
(3, 20, 1, 1, 150.00, 150.00, '2025-11-19 08:30:18');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `receipt_number` varchar(20) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `receipt_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` enum('admin','rider','customer') DEFAULT 'customer',
  `status` enum('active','banned') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `phone_number`, `address`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RCH Water', 'rchwater@gmail.com', '$2y$10$JrPU/jSZagylud2oyaBnQe8USH2bjbzfRaBkTLXWeyoakpxaZ4I5m', '09123456789', 'Lipa City, Batangas', 'admin', 'active', '2025-10-22 15:03:24', '2025-10-22 15:03:24'),
(2, 'Juan Dela Cruz', 'jdelacruz@gmail.com', '$2y$10$3xq8O8Q1a3zvCZ2J2UVw7OlmQYQe4DPEi3RrZl8b3Gg7YhT6FeWLC\r\n', '09123456788', 'Brgy. San Carlos, Rosario, Batangas', 'customer', 'active', '2025-11-06 03:35:23', '2025-11-06 03:35:23'),
(3, 'Ramon Fontelera', 'rmfontelera@gmail.com', '$2b$12$myLXDR05AMHba8nPIZ3liOZ4bpGYfg9f.khZbt/..l/OL3dGqQNGi', '09842515247', 'Lipa City, Batangas', 'rider', 'banned', '2025-11-08 08:28:23', '2025-11-17 14:05:53'),
(4, 'Nami Matsuzaki', 'namigarduque@gmail.com', 'd25b8187630473543380f0d8fbc49fbdda86ab3647767842b5db8114937d8a01', '09300295265', 'House No.5, Sitio Sampalucan, Brgy. San Carlos, Rosario, Batangas 4225', 'customer', 'active', '2025-11-14 09:13:55', '2025-11-18 12:26:35');

-- --------------------------------------------------------

--
-- Table structure for table `water_refills`
--

CREATE TABLE `water_refills` (
  `refill_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `pickup_datetime` datetime DEFAULT NULL,
  `delivery_datetime` datetime DEFAULT NULL,
  `order_status` enum('pending','preparing','on_delivery','completed','cancelled') DEFAULT 'pending',
  `gallon_owner` enum('customer','station') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `water_refills`
--

INSERT INTO `water_refills` (`refill_id`, `user_id`, `order_number`, `total_amount`, `pickup_datetime`, `delivery_datetime`, `order_status`, `gallon_owner`, `created_at`) VALUES
(8, 4, 'ORD1763367443019', 80.00, '2025-11-19 13:00:00', '2025-11-22 16:00:00', 'pending', 'station', '2025-11-17 08:17:23'),
(9, 4, 'ORD1763368567293', 20.00, '2025-11-19 13:00:00', '2025-11-19 14:00:00', 'pending', 'station', '2025-11-17 08:36:07'),
(10, 4, 'ORD1763368646307', 90.00, '2025-11-19 07:30:00', '2025-11-26 09:00:00', 'pending', 'station', '2025-11-17 08:37:26'),
(11, 4, 'ORD1763369021309', 20.00, '2025-11-18 13:00:00', '2025-11-18 16:00:00', 'pending', 'station', '2025-11-17 08:43:41'),
(12, 4, 'ORD1763369292514', 30.00, '2025-11-19 13:00:00', '2025-11-19 14:00:00', 'pending', 'station', '2025-11-17 08:48:12'),
(13, 4, 'ORD1763369390494', 20.00, '2025-11-25 13:00:00', '2025-11-25 16:00:00', 'pending', 'customer', '2025-11-17 08:49:50'),
(14, 4, 'ORD1763369529574', 20.00, '2025-11-18 07:30:00', '2025-11-18 09:00:00', 'pending', 'station', '2025-11-17 08:52:09'),
(15, 4, 'ORD1763369792762', 160.00, '2025-11-18 13:00:00', '2025-11-18 16:00:00', 'pending', 'station', '2025-11-17 08:56:32'),
(16, 4, 'ORD1763369913076', 20.00, '2025-11-22 13:00:00', '2025-11-22 16:00:00', 'pending', 'station', '2025-11-17 08:58:33'),
(17, 4, 'ORD1763370551965', 30.00, '2025-11-19 13:00:00', '2025-11-19 16:00:00', 'pending', 'station', '2025-11-17 09:09:12'),
(18, 4, 'ORD1763370647136', 20.00, '2025-11-21 13:00:00', '2025-11-21 16:00:00', 'pending', 'station', '2025-11-17 09:10:47'),
(19, 4, 'ORD1763371031089', 20.00, '2025-11-21 13:00:00', '2025-11-21 16:00:00', 'pending', 'station', '2025-11-17 09:17:11'),
(20, 4, 'ORD1763371247276', 30.00, '2025-11-19 13:00:00', '2025-11-19 14:00:00', 'pending', 'station', '2025-11-17 09:20:47'),
(21, 4, 'ORD1763371580950', 90.00, '2025-11-18 13:00:00', '2025-11-18 14:00:00', 'pending', 'station', '2025-11-17 09:26:20'),
(22, 4, 'ORD1763371935779', 20.00, '2025-11-25 13:00:00', '2025-11-25 16:00:00', 'pending', 'station', '2025-11-17 09:32:15'),
(23, 4, 'ORD1763378510905', 20.00, '2025-11-18 13:00:00', '2025-11-18 14:00:00', 'pending', 'station', '2025-11-17 11:21:50'),
(24, 4, 'ORD1763380943688', 30.00, '2025-11-21 13:00:00', '2025-11-21 16:00:00', 'pending', 'station', '2025-11-17 12:02:24'),
(25, 4, 'ORD1763388547632', 20.00, '2025-11-19 13:00:00', '2025-11-19 16:00:00', 'pending', 'station', '2025-11-17 14:09:07'),
(26, 4, 'ORD17634547107894', 20.00, '2025-11-19 13:00:00', '2025-11-19 16:00:00', 'pending', 'station', '2025-11-18 08:31:50'),
(27, 4, 'ORD17634584241234', 20.00, '2025-11-19 13:00:00', '2025-11-19 16:00:00', 'pending', 'station', '2025-11-18 09:33:44'),
(28, 4, 'ORD17634691277484', 30.00, '2025-11-20 13:00:00', '2025-11-20 16:00:00', 'pending', 'station', '2025-11-18 12:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `water_refill_items`
--

CREATE TABLE `water_refill_items` (
  `item_id` int(11) NOT NULL,
  `refill_id` int(11) NOT NULL,
  `gallon_type` varchar(20) DEFAULT NULL,
  `gallon_size` varchar(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `water_refill_items`
--

INSERT INTO `water_refill_items` (`item_id`, `refill_id`, `gallon_type`, `gallon_size`, `quantity`, `unit_price`, `subtotal`) VALUES
(2, 8, 'Slim', '10L', 1, 20.00, 20.00),
(3, 8, 'Round', '18.9L', 2, 30.00, 60.00),
(4, 9, 'Slim', '10L', 1, 20.00, 20.00),
(5, 10, 'Round', '18.9L', 3, 30.00, 90.00),
(6, 11, 'Slim', '10L', 1, 20.00, 20.00),
(7, 12, 'Round', '18.9L', 1, 30.00, 30.00),
(8, 13, 'Slim', '10L', 1, 20.00, 20.00),
(9, 14, 'Slim', '10L', 1, 20.00, 20.00),
(10, 15, 'Slim', '10L', 1, 20.00, 20.00),
(11, 15, 'Slim', '10L', 1, 20.00, 20.00),
(12, 15, 'Slim', '20L', 1, 30.00, 30.00),
(13, 15, 'Round', '18.9L', 3, 30.00, 90.00),
(14, 16, 'Slim', '10L', 1, 20.00, 20.00),
(15, 17, 'Round', '18.9L', 1, 30.00, 30.00),
(16, 18, 'Slim', '10L', 1, 20.00, 20.00),
(17, 19, 'Slim', '10L', 1, 20.00, 20.00),
(18, 20, 'Round', '18.9L', 1, 30.00, 30.00),
(19, 21, 'Slim', '20L', 3, 30.00, 90.00),
(20, 22, 'Slim', '10L', 1, 20.00, 20.00),
(21, 23, 'Slim', '10L', 1, 20.00, 20.00),
(22, 24, 'Slim', '20L', 1, 30.00, 30.00),
(23, 25, 'Slim', '10L', 1, 20.00, 20.00),
(24, 26, 'Slim', '10L', 1, 20.00, 20.00),
(25, 27, 'Slim', '10L', 1, 20.00, 20.00),
(26, 28, 'Round', '18.9L', 1, 30.00, 30.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `gallon_ownership`
--
ALTER TABLE `gallon_ownership`
  ADD PRIMARY KEY (`ownership_id`),
  ADD UNIQUE KEY `code_value` (`code_value`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `owner_id` (`current_owner_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `map_location`
--
ALTER TABLE `map_location`
  ADD PRIMARY KEY (`loc_id`),
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `fk_receipt_order` (`order_id`),
  ADD KEY `fk_receipt_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `water_refills`
--
ALTER TABLE `water_refills`
  ADD PRIMARY KEY (`refill_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `water_refill_items`
--
ALTER TABLE `water_refill_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `refill_id` (`refill_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallon_ownership`
--
ALTER TABLE `gallon_ownership`
  MODIFY `ownership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `map_location`
--
ALTER TABLE `map_location`
  MODIFY `loc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `water_refills`
--
ALTER TABLE `water_refills`
  MODIFY `refill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `water_refill_items`
--
ALTER TABLE `water_refill_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`item_id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `water_refills` (`refill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `gallon_ownership`
--
ALTER TABLE `gallon_ownership`
  ADD CONSTRAINT `gallon_ownership_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallon_ownership_ibfk_2` FOREIGN KEY (`current_owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`item_id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `fk_receipt_order` FOREIGN KEY (`order_id`) REFERENCES `water_refills` (`refill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_receipt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `water_refills`
--
ALTER TABLE `water_refills`
  ADD CONSTRAINT `water_refills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `water_refill_items`
--
ALTER TABLE `water_refill_items`
  ADD CONSTRAINT `water_refill_items_ibfk_1` FOREIGN KEY (`refill_id`) REFERENCES `water_refills` (`refill_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
