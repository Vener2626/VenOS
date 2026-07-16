-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 05:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `venos`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT 0,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','gcash') NOT NULL,
  `amount_tendered` decimal(10,2) DEFAULT NULL,
  `change_due` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `subtotal`, `discount_percent`, `discount_amount`, `total`, `payment_method`, `amount_tendered`, `change_due`, `created_at`) VALUES
(1, 11.00, 0, 0.00, 11.00, 'cash', 20.00, 9.00, '2026-07-15 15:37:57'),
(2, 10.75, 0, 0.00, 10.75, 'gcash', NULL, NULL, '2026-07-15 15:38:29'),
(3, 10.75, 0, 0.00, 10.75, 'cash', 20.00, 9.25, '2026-07-15 16:37:38'),
(4, 3.50, 0, 0.00, 3.50, 'cash', 100.00, 96.50, '2026-07-15 16:41:43'),
(5, 80.00, 0, 0.00, 80.00, 'cash', 100.00, 20.00, '2026-07-15 16:45:45'),
(6, 20.00, 0, 0.00, 20.00, 'cash', 100.00, 80.00, '2026-07-15 16:51:39'),
(7, 335.00, 0, 0.00, 335.00, 'cash', 500.00, 165.00, '2026-07-15 16:57:32'),
(8, 88.50, 0, 0.00, 88.50, 'cash', 90.00, 1.50, '2026-07-15 16:58:29'),
(9, 515.00, 0, 0.00, 515.00, 'cash', 550.00, 35.00, '2026-07-15 17:00:12'),
(10, 455.00, 10, 45.50, 409.50, 'cash', 500.00, 90.50, '2026-07-15 17:02:57'),
(11, 45.00, 0, 0.00, 45.00, 'cash', 50.00, 5.00, '2026-07-15 17:05:43'),
(12, 100.00, 0, 0.00, 100.00, 'cash', 150.00, 50.00, '2026-07-16 08:29:40'),
(13, 127.00, 5, 6.35, 120.65, 'cash', 125.00, 4.35, '2026-07-16 08:36:35'),
(14, 160.00, 0, 0.00, 160.00, 'cash', 200.00, 40.00, '2026-07-16 08:45:39'),
(15, 45.00, 0, 0.00, 45.00, 'cash', 50.00, 5.00, '2026-07-16 10:17:23'),
(16, 55.00, 0, 0.00, 55.00, 'cash', 55.00, 0.00, '2026-07-16 10:18:28'),
(17, 55.00, 0, 0.00, 55.00, 'cash', 55.00, 0.00, '2026-07-16 10:18:37'),
(18, 20.00, 0, 0.00, 20.00, 'cash', 50.00, 30.00, '2026-07-16 10:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `line_total`) VALUES
(1, 1, 8, 'Berry Muffin', 3.50, 1, 3.50),
(2, 1, 9, 'Cheesecake', 7.50, 1, 7.50),
(3, 2, 7, 'Croissant', 3.25, 1, 3.25),
(4, 2, 9, 'Cheesecake', 7.50, 1, 7.50),
(5, 3, 7, 'Croissant', 3.25, 1, 3.25),
(6, 3, 9, 'Cheesecake', 7.50, 1, 7.50),
(7, 4, 8, 'Berry Muffin', 3.50, 1, 3.50),
(8, 5, 3, 'Cappuccino', 55.00, 1, 55.00),
(9, 5, 9, 'Cheesecake', 25.00, 1, 25.00),
(10, 6, 8, 'Berry Muffin', 20.00, 1, 20.00),
(11, 7, 1, 'Espresso Large', 70.00, 1, 70.00),
(12, 7, 3, 'Cappuccino', 55.00, 1, 55.00),
(13, 7, 4, 'Matcha Latte', 60.00, 1, 60.00),
(14, 7, 9, 'Cheesecake', 25.00, 2, 50.00),
(15, 7, 23, 'French Fries Large', 100.00, 1, 100.00),
(16, 8, 10, 'Banana Bread', 15.00, 1, 15.00),
(17, 8, 15, 'Potato Chips', 2.50, 1, 2.50),
(18, 8, 21, 'Fish Tacos', 11.00, 1, 11.00),
(19, 8, 22, 'Dalandan Large', 60.00, 1, 60.00),
(20, 9, 5, 'Fresh Orange', 30.00, 1, 30.00),
(21, 9, 9, 'Cheesecake', 25.00, 1, 25.00),
(22, 9, 22, 'Dalandan Large', 60.00, 1, 60.00),
(23, 9, 23, 'French Fries Large', 100.00, 1, 100.00),
(24, 9, 24, 'Steak Ala Carte', 150.00, 2, 300.00),
(25, 10, 3, 'Cappuccino', 55.00, 1, 55.00),
(26, 10, 20, 'Shrimp Buttered Garlic', 150.00, 2, 300.00),
(27, 10, 23, 'French Fries Large', 100.00, 1, 100.00),
(28, 11, 8, 'Berry Muffin', 20.00, 1, 20.00),
(29, 11, 9, 'Cheesecake', 25.00, 1, 25.00),
(30, 12, 1, 'Espresso Large', 70.00, 1, 70.00),
(31, 12, 5, 'Fresh Orange', 30.00, 1, 30.00),
(32, 13, 3, 'Cappuccino', 55.00, 1, 55.00),
(33, 13, 4, 'Matcha Latte', 60.00, 1, 60.00),
(34, 13, 14, 'Granola Bowl', 9.00, 1, 9.00),
(35, 13, 17, 'Trail Mix', 3.00, 1, 3.00),
(36, 14, 1, 'Espresso Large', 70.00, 1, 70.00),
(37, 14, 5, 'Fresh Orange', 30.00, 1, 30.00),
(38, 14, 7, 'Croissant', 15.00, 1, 15.00),
(39, 14, 8, 'Berry Muffin', 20.00, 1, 20.00),
(40, 14, 9, 'Cheesecake', 25.00, 1, 25.00),
(41, 15, 8, 'Berry Muffin', 20.00, 1, 20.00),
(42, 15, 9, 'Cheesecake', 25.00, 1, 25.00),
(43, 16, 3, 'Cappuccino', 55.00, 1, 55.00),
(44, 17, 3, 'Cappuccino', 55.00, 1, 55.00),
(45, 18, 8, 'Berry Muffin', 20.00, 1, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `icon`, `stock`, `created_at`) VALUES
(1, 'Espresso Large', 'Drinks', 70.00, '☕', NULL, '2026-07-15 06:54:26'),
(2, 'Still Water', 'Drinks', 20.00, '🥛', NULL, '2026-07-15 06:54:26'),
(3, 'Cappuccino', 'Drinks', 55.00, '☕', NULL, '2026-07-15 06:54:26'),
(4, 'Matcha Latte', 'Drinks', 60.00, '🍵', NULL, '2026-07-15 06:54:26'),
(5, 'Fresh Orange', 'Drinks', 30.00, '🍊', NULL, '2026-07-15 06:54:26'),
(6, 'Bottle Water 15ml', 'Drinks', 15.00, '💧', NULL, '2026-07-15 06:54:26'),
(7, 'Croissant', 'Bakery', 15.00, '🥐', NULL, '2026-07-15 06:54:26'),
(8, 'Berry Muffin', 'Bakery', 20.00, '🧁', NULL, '2026-07-15 06:54:26'),
(9, 'Cheesecake', 'Bakery', 25.00, '🍰', NULL, '2026-07-15 06:54:26'),
(10, 'Banana Bread', 'Bakery', 15.00, '🍞', NULL, '2026-07-15 06:54:26'),
(11, 'Club Sandwich', 'Food', 10.50, '🥪', NULL, '2026-07-15 06:54:26'),
(12, 'Caesar Salad', 'Food', 12.00, '🥗', NULL, '2026-07-15 06:54:26'),
(13, 'Avocado Toast', 'Food', 11.50, '🥑', NULL, '2026-07-15 06:54:26'),
(14, 'Granola Bowl', 'Food', 9.00, '🥣', NULL, '2026-07-15 06:54:26'),
(15, 'Potato Chips', 'Snacks', 2.50, '🍟', NULL, '2026-07-15 06:54:26'),
(17, 'Trail Mix', 'Snacks', 3.00, '🥜', NULL, '2026-07-15 06:54:26'),
(18, 'Chocolate Bar', 'Snacks', 2.25, '🍫', NULL, '2026-07-15 06:54:26'),
(19, 'Grilled Salmon', 'Seafood', 16.50, '🐟', NULL, '2026-07-15 06:54:26'),
(20, 'Shrimp Buttered Garlic', 'Seafood', 150.00, '🍤', NULL, '2026-07-15 06:54:26'),
(21, 'Fish Tacos', 'Seafood', 11.00, '🌮', NULL, '2026-07-15 06:54:26'),
(22, 'Dalandan Large', 'Fruit Tea', 60.00, '🧋', NULL, '2026-07-15 08:40:06'),
(23, 'French Fries Large', 'Snacks', 100.00, '🍿', NULL, '2026-07-15 08:56:54'),
(24, 'Steak Ala Carte', 'Food', 150.00, '🥩', NULL, '2026-07-15 08:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `cashier_pin_hash` varchar(255) DEFAULT NULL,
  `owner_pin_hash` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `pin_hash`, `cashier_pin_hash`, `owner_pin_hash`, `updated_at`) VALUES
(1, '$2y$10$IEYEYfXG.HyjF94.qxTg/umTlpbQ83tLmuGAy8TdRAWwx4YqRGLzu', '$2y$10$ZH7k.rkzx2Unx.ZvFUrD6.3m//kJDhrQqzOduCKJ8KX6pu66iOuzu', '$2y$10$QmOyhw64kTxGnG4fVniyjODtsSQQXAXoetGl3Hck0qvrkVy1nR9sC', '2026-07-16 03:23:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
