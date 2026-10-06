-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 10:06 AM
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
-- Database: `clothing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `size` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `pid`, `name`, `price`, `quantity`, `size`, `image`) VALUES
(4, 6, 14, 'Corset Bralette Top', 1700, 1, 'S', 'brallet.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL,
  `reply` text DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`, `reply`, `replied_at`) VALUES
(1, 5, 'rajesh', 'rajesh123@gmail.com', '9876543210', 'i am satisfiedd', NULL, NULL),
(2, 7, 'Nicole Carroll', 'wumyvoj@mailinator.com', '9876543210', 'hello', NULL, NULL),
(3, 7, 'Nicole Carroll', 'wumyvoj@mailinator.com', '9876543210', 'how are u', 'okay', '2026-06-04 22:12:14'),
(5, 5, 'rajesh', 'rajesh123@gmail.com', '9876543210', 'hiiiiiiiiii', 'ok', '2026-06-03 09:52:39'),
(6, 5, 'rajesh', 'rajesh123@gmail.com', '9876543210', 'i like your product', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`) VALUES
(1, 5, 'rajesh', '9876543210', 'rejinakarki743@gmail.com', 'cash on delivery', 'flat no. Champadevi, tgwsfvx, Okhaldhunga, gahgegdvx, Nepal - 2345', 'lily ( 1 ), Cute Knitted Short Skirt ( 1 )', 3500, '03-Jun-2026', 'completed'),
(2, 5, 'rajesh', '9876543210', 'rajesh123@gmail.com', 'cash on delivery', 'flat no. Champadevi, tgwsfvx, Okhaldhunga, gahgegdvx, Nepal - 45200', 'White Backless Top & See through Long Skirt Set ( 1 ), Long Slit Dress ( 1 )', 5500, '06-Jun-2026', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(20) NOT NULL,
  `sizes` varchar(100) NOT NULL DEFAULT 'S,M,L',
  `details` varchar(500) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `sizes`, `details`, `price`, `image`) VALUES
(1, 'roshni', 'tops', 'S,M,L', 'white tops', 500, 'tops1.jpg'),
(2, 'lily', 'dresses', 'S,M,L', 'brown long dress', 1200, 'dresses1.jpg'),
(3, 'Straight Pants', 'bottoms', 'S,M,L', 'Brown straight pants', 1200, 'bottoms1.jpg'),
(4, 'Formal Pants', 'bottoms', 'S,M,L', 'Grey formal pants', 2000, 'bottoms2.jpg'),
(5, 'Wide Leg Pants', 'bottoms', 'S,M,L', 'white wide leg pants', 1500, 'bottoms3.jpg'),
(6, 'Leather Pants', 'bottoms', 'S,M,L', 'black leather pants', 1700, 'bottoms4.jpg'),
(7, 'Pintex Pants', 'bottoms', 'S,M,L', 'Pintex pants', 2500, 'bottoms5.jpg'),
(8, 'Full Sleeve Long Set', 'dresses', 'S,M,L', 'blue two piece set', 3000, 'dresses2.jpg'),
(9, 'Long Slit Dress', 'dresses', 'S,M,L', 'long black dress', 2500, 'dresses3.jpg'),
(10, 'Full Sleeve Flare Top', 'tops', 'S,M,L', 'red top', 1300, 'top2.jpg'),
(11, 'Pink Sleeveless Top', 'tops', 'S,M,L', 'pink top', 1200, 'top3.jpg'),
(12, 'Summer Flowy Tops', 'tops', 'S,M,L', 'Black flowy top', 1800, 'flowytop.webp'),
(13, 'Summer Corset Top', 'tops', 'S,M,L', 'corset Top', 1500, 'corset.jpg'),
(14, 'Corset Bralette Top', 'tops', 'S,M,L', 'Only Pink available', 1700, 'brallet.jpg'),
(15, 'Off Shoulder Top', 'tops', 'S,M,L', 'Grey off shoulder top', 1200, 'off_shoulder_top.jpg'),
(16, 'Grey Cape Coat', 'jackets', 'S,M,L', 'grey jacket', 2800, 'grey_jacket.jpg'),
(17, 'Leather Jacket', 'jackets', 'S,M,L', 'short length', 2400, 'jacket.jpg'),
(18, 'Leather Jacket with Belt', 'jackets', 'S,M,L,XL', 'SHORT LENGTH', 3600, 'j1.jpg'),
(19, 'Warm leather jacket', 'jackets', 'S,M,L', 'with fur collar and fur inside', 4800, 'j2.jpg'),
(20, 'Lulu Jacket', 'jackets', 'S,M,L', 'Bodycon tight Jacket', 1200, 'j5.jpg'),
(21, 'Fur Jacket', 'jackets', 'S,M,L', 'fur jacket', 4000, 'j6.jpg'),
(22, 'Hello Kitty puffer', 'jackets', 'S,M,L,XL', 'puffer jacket', 3800, 'j7.jpg'),
(23, 'Tank Top & Folded Trouser Set', 'sets', 'S,M,L,XL', 'Tank Top & Folded Trouser Set', 2800, 'set1.jpg'),
(24, 'V Neck Top & Skirt Set - Chiffon Summer Set', 'sets', 'S,M,L', 'V Neck Top & Skirt Set - Chiffon Summer Set', 3800, 's1.jpg'),
(25, 'Off Shoulder Crop Top & Shorts Set', 'sets', 'S,M,L,XL', 'Off Shoulder Crop Top & Shorts Set', 1700, 's2.jpg'),
(26, 'Cute Knitted skirt & cardigan set', 'sets', 'S,M,L', 'Cute Knitted skirt & cardigan set', 5500, 's3.jpg'),
(27, 'Fluffy checked Skirt & Full Sleeve Top Set', 'sets', 'S,M', 'Fluffy checked Skirt & Full Sleeve Top Set', 3000, 's4.jpg'),
(28, 'Long Skirt and Blazer Set', 'sets', 'S,M,L,XL', 'Long Skirt and Blazer Set', 2900, 's5.jpg'),
(29, 'Tweed Set - Long skirt & blazer', 'sets', 'S,M,L', 'Tweed Set - Long skirt & blazer', 4000, 's5.jpg'),
(30, 'White Backless Top & See through Long Skirt Set', 'sets', 'S,M,L', 'White Backless Top & See through Long Skirt Set', 3000, 's7.jpg'),
(31, 'Sweater & Shorts Set - Knitted set', 'sets', 'S,M', 'Sweater & Shorts Set - Knitted set', 3800, 's8.jpg'),
(32, 'White Fuzzy Warm Blazer & skirt set', 'sets', 'S,M,L', 'White Fuzzy Warm Blazer & skirt set', 6000, 's9.jpg'),
(33, 'Sweater Set - Knitted Flare Pant & Jacket set', 'sets', 'S,M,L', 'Sweater Set - Knitted Flare Pant & Jacket set', 4600, 's10.jpg'),
(34, '3 Piece Bikini & Skirt Swimwear Set - Swimsuit', 'swimwear', 'S,M,L,XL', '3 Piece Bikini & Skirt Swimwear Set - Swimsuit', 2600, 'w.jpg'),
(35, '3 Piece Swimwear - Bra, Underwear & Shorts Swimsuit Set', 'swimwear', 'S,M', '3 Piece Swimwear - Bra, Underwear & Shorts Swimsuit Set', 2500, 'w1.jpg'),
(36, 'Polka Dot 2 piece Swimwear - Swimsuit with shorts', 'swimwear', 'S,M,L,XL', 'Polka Dot 2 piece Swimwear - Swimsuit with shorts', 2400, 'w2.jpg'),
(37, 'Red Bikini Set with Long Skirt & Flower Clip', 'swimwear', 'S,M,L', 'Red Bikini Set with Long Skirt & Flower Clip', 2300, 'w3.jpg'),
(38, 'Star print Bikini set - Top, Head scarf & Skirt with attached inner', 'swimwear', 'S,M,L', 'Star print Bikini set - Top, Head scarf & Skirt with attached inner', 2600, 'w4.jpg'),
(39, '3 piece swimsuit set - black & red', 'swimwear', 'S,M,L,XL', '3 piece swimsuit set - black & red', 2100, 'w5.jpg'),
(40, 'Lining Summer Shorts', 'shorts', 'S,M', 'Lining Summer Shorts', 900, 'd.jpg'),
(41, 'Hello Kitty Summer Shorts', 'shorts', 'S,M,L,XL', 'Hello Kitty Summer Shorts', 600, 'd1.jpg'),
(42, 'Comfy Shorts', 'shorts', 'S,M,L', 'Comfy Shorts', 500, 'd2.jpg'),
(43, 'Low waist short Shorts', 'shorts', 'S,M,L,XL', 'Low waist short Shorts', 2200, 'd3.jpg'),
(44, 'Short Knitted Shorts - Baggy Fit', 'shorts', 'S,M,L', 'Short Knitted Shorts - Baggy Fit', 1800, 'd4.jpg'),
(45, 'Denim Short Skirt - Mini Skirt', 'skirts', 'S,M,L', 'Denim Short Skirt - Mini Skirt', 800, 'e.jpg'),
(46, 'Cute Denim Skirt with shorts attached', 'skirts', 'S,M,L', 'Cute Denim Skirt with shorts attached', 1800, 'e1.jpg'),
(47, 'Woolen Short Skirt - Tennis Skirt with built in shorts', 'skirts', 'S,M,L,XL', 'Woolen Short Skirt - Tennis Skirt with built in shorts', 2500, 'e2.jpg'),
(48, 'Short Skirt with shorts attached', 'skirts', 'S,M,L', 'Short Skirt with shorts attached', 1800, 'e3.jpg'),
(49, 'Cute Knitted Short Skirt', 'skirts', 'S,M', 'Cute Knitted Short Skirt', 2300, 'e4.jpg'),
(50, 'Warm Long Skirt', 'skirts', 'S,M,L,XL', 'Warm Long Skirt', 1500, 'e5.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'NOT NULL',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` varchar(100) NOT NULL DEFAULT 'user',
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `image`) VALUES
(2, 'roshni', 'roskarki@gmail.com', '$2y$10$1wogfPuo0MxFCP8brTtE6.rUbwbIgL2SwuNp7oA/zXeTt2rZYwiPW', 'user', 'a.jpg'),
(4, 'roshni', 'roskarki123@gmail.com', '$2y$10$bspP414To97r8sNdJVz5tuLT3KQzd40WNtx4Flqj1UxGcSR4R7Ln6', 'admin', 'a.jpg'),
(5, 'rajesh', 'rajesh123@gmail.com', '$2y$10$yy9b1oSDYg0xNJKjnxJTwO5I5BwKf9FzBlHhKWJQAeaHR64Wezhxi', 'user', 'a.jpg'),
(6, 'roshni', 'rejinakarki743@gmail.com', '$2y$10$OVGBdga1zgWUYuS0i.w1jukMAf8IqR2Ot3biTyg.wH/krx19yZEr6', 'user', 'a.jpg'),
(7, 'Nicole Carroll', 'wumyvoj@mailinator.com', '$2y$10$wE/azNNYr2xNEAhGqov5AO.OvzEVtrTMloPFCrDkuwR0aSYCK8pM2', 'user', 'a.jpg'),
(8, 'Kirestin Bauer', 'doxi@mailinator.com', '$2y$10$2tdhLGEqoigo0QtTC94cf.zAIKECE.d4Rpx.bYUmSNGjEb.IRN3Ha', 'user', 'a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
