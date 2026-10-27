-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 18, 2026 at 03:42 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kasir_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Dessert');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(50) DEFAULT 'Makan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `image_url`, `created_at`, `category`) VALUES
(1, 2, 'Nasi Goreng Spesial', 25000.00, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=300', '2026-09-17 01:54:02', 'Makan'),
(2, 1, 'Ayam Bakar', 30000.00, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=300', '2026-09-17 01:54:02', 'Makan'),
(3, 2, 'Es Teh Manis', 5000.00, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=300', '2026-09-17 01:54:02', 'Minuman'),
(4, 2, 'Kopi Hitam', 10000.00, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=300', '2026-09-17 01:54:02', 'Minuman'),
(5, 3, 'Es Cream', 5000.00, 'uploads/es_krim.jpg', '2026-09-17 08:16:34', 'Makan'),
(6, 3, 'Bubur Ketan + Roti', 12000.00, 'uploads/bubur_roti.jpeg', '2026-09-17 08:22:27', 'Makan');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `service_charge` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'SUCCESS',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `subtotal`, `tax`, `service_charge`, `total`, `payment_method`, `payment_status`, `created_at`) VALUES
(1, 1, 125000.00, 12500.00, 6250.00, 143750.00, 'CASH', 'SUCCESS', '2026-09-17 03:36:05'),
(2, 1, 10000.00, 1000.00, 500.00, 11500.00, 'QRIS', 'SUCCESS', '2026-09-17 03:45:23'),
(3, 1, 55000.00, 5500.00, 2750.00, 63250.00, 'QRIS', 'SUCCESS', '2026-09-17 03:45:44'),
(4, 1, 40000.00, 4000.00, 2000.00, 46000.00, 'QRIS', 'SUCCESS', '2026-09-17 04:05:52'),
(5, 1, 35000.00, 3500.00, 1750.00, 40250.00, 'QRIS', 'SUCCESS', '2026-09-17 04:34:40'),
(6, 1, 220000.00, 22000.00, 11000.00, 253000.00, 'QRIS', 'SUCCESS', '2026-09-17 07:59:53'),
(7, 1, 359000.00, 35900.00, 17950.00, 412850.00, 'TRANSFER', 'SUCCESS', '2026-09-17 08:29:36');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int NOT NULL,
  `transaction_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_sale` decimal(10,2) NOT NULL,
  `subtotal_item` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `quantity`, `price_at_sale`, `subtotal_item`) VALUES
(1, 1, 4, 3, 10000.00, 30000.00),
(2, 1, 3, 2, 5000.00, 10000.00),
(3, 1, 2, 2, 30000.00, 60000.00),
(4, 1, 1, 1, 25000.00, 25000.00),
(5, 2, 4, 1, 10000.00, 10000.00),
(6, 3, 4, 5, 10000.00, 50000.00),
(7, 3, 3, 1, 5000.00, 5000.00),
(8, 4, 1, 1, 25000.00, 25000.00),
(9, 4, 4, 1, 10000.00, 10000.00),
(10, 4, 3, 1, 5000.00, 5000.00),
(11, 5, 1, 1, 25000.00, 25000.00),
(12, 5, 4, 1, 10000.00, 10000.00),
(13, 6, 1, 5, 25000.00, 125000.00),
(14, 6, 3, 7, 5000.00, 35000.00),
(15, 6, 2, 2, 30000.00, 60000.00),
(16, 7, 2, 5, 30000.00, 150000.00),
(17, 7, 1, 5, 25000.00, 125000.00),
(18, 7, 3, 7, 5000.00, 35000.00),
(19, 7, 4, 1, 10000.00, 10000.00),
(20, 7, 6, 2, 12000.00, 24000.00),
(21, 7, 5, 3, 5000.00, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('kasir','manajer','it_backend') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'kasir', '123456', 'Operator', 'kasir', '2026-09-17 01:54:02'),
(2, 'mjr', '123456', 'Manajer', 'manajer', '2026-09-17 01:54:02'),
(3, 'its', '123456', 'IT Administrator', 'it_backend', '2026-09-17 01:54:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
