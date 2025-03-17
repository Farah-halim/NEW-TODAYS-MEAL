-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2025 at 05:06 PM
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
-- Database: `today's meal`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(4, 'Desserts'),
(1, 'Drinks'),
(2, 'Fast Food'),
(6, 'Italian'),
(5, 'Seafood'),
(3, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `food`
--

CREATE TABLE `food` (
  `food_id` int(11) NOT NULL,
  `caterer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food`
--

INSERT INTO `food` (`food_id`, `caterer_id`, `title`, `tags`, `price`, `description`, `created_at`, `image`) VALUES
(4, 27, 'pizza', '', 222.00, 'piizza margreta', '2025-03-08 08:39:06', 'uploads/1741437546_pexels-photo-2403850 (2).jpeg'),
(5, 27, 'tea', '', 10.00, 'el tea gamil', '2025-03-08 09:43:46', 'uploads/1741441426_tea.jpg'),
(6, 27, 'koshari', '', 77.00, 'koshari 7lw', '2025-03-08 09:50:32', 'uploads/1741441832_pexels-madison-inouye-1382393.jpg'),
(7, 27, 'tea ', '', 99.00, 'el tea gamil', '2025-03-08 09:51:22', 'uploads/1741441882_tea.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `food_categories`
--

CREATE TABLE `food_categories` (
  `food_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_categories`
--

INSERT INTO `food_categories` (`food_id`, `category_id`) VALUES
(4, 2),
(4, 6),
(5, 1),
(6, 3),
(7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `phone` int(20) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `role` enum('customer','admin','caterer','delivery') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) NOT NULL,
  `address2` varchar(65) DEFAULT NULL,
  `national_id` varchar(14) DEFAULT NULL,
  `driver_license` varchar(14) DEFAULT NULL,
  `years_of_experience` enum('No Experience','Beginner (0-1 years)','Intermediate (2-3 years)','Advanced (4-5 years)','Expert (6+ years)') DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `reset_token`, `reset_token_expiry`, `phone`, `address1`, `role`, `created_at`, `is_approved`, `address2`, `national_id`, `driver_license`, `years_of_experience`, `category_id`, `gender`) VALUES
(13, 'admin1', 'admin1@gmail.com', '$2y$10$vO6Yg9u6k394LVW1lQnJkOI1YrxTMCtU82EYpbdJU3c7AvRMMC4Vy', NULL, NULL, 1234, 'mohamed st', 'admin', '2025-02-27 07:39:59', 1, '', NULL, NULL, NULL, NULL, 'Male'),
(26, 'customer', 'customer@gmail.com', '$2y$10$g21XcpP9hTJdZxUOGohCUOK6RXkA5OLXgj1OoBUFlhRJ6ZvZO/J1G', NULL, NULL, 822222221, 'w', 'customer', '2025-02-27 10:16:03', 1, '', NULL, NULL, NULL, NULL, 'Male'),
(27, 'caterer1', 'caterer1@gmail.com', '$2y$10$j7rl9BgjxWt9KP2/Rvg60eRypWRJSbg4wHnJWrePWBXD6Yl2nyiue', NULL, NULL, 333333333, 'abo gafer st', 'caterer', '2025-02-27 10:17:29', 1, '', NULL, NULL, 'Intermediate (2-3 years)', NULL, 'Male'),
(29, 'Farah halim', 'level2bis2@gmail.com', '$2y$10$3i3mwdSGs/eOFqbwFXh2ceABkof.f6sgSynPNCteEkXsZ7LsIjdkK', '6f5413a7700719686958e829c1e0d0d4bc7df0caf48e78ee09f7ac241734a003', '2025-03-14 23:56:18', 2147483647, 'nabil khattab st', 'customer', '2025-03-11 13:03:16', 1, '', NULL, NULL, NULL, NULL, 'Male'),
(41, 'Esraa Yasser', 'esraayasser809@gmail.com', '$2y$10$UwPKfzvt1ePRQkSs3CJS3OS5GJbEiTJQ9YDzQvUKY7CnpiVl/Nq7G', NULL, NULL, 1006457530, 'الهرم - الجيزة', 'customer', '2025-03-17 01:28:07', 1, NULL, NULL, NULL, NULL, NULL, 'Male'),
(42, 'hend', 'mauricefanning1@gmail.com', '$2y$10$KsVHDkXawdzHfC53NEDc4ugwjIXF5ekt6tVPAOYsT08p70IiJQjmS', NULL, NULL, 919579318, 'الهرم - الجيزة', 'caterer', '2025-03-17 01:29:02', 0, NULL, '300000', NULL, 'No Experience', NULL, 'Male'),
(43, 'test', 'test2@gmail.com', '$2y$10$Lsov56KCoJV8rk/Cstpeqe.ge2MgWUfINEzEkkvCaU5G1ORxcx0xu', NULL, NULL, 2147483647, 'egypy', 'caterer', '2025-03-17 15:11:34', 0, NULL, '22222222222222', NULL, 'Advanced (4-5 years)', 5, 'Male'),
(44, 'test2', 'test22@gmail.com', '$2y$10$zd4rETmpV77U39ReLmuGduhvuMbJfNSgjICF3pqK4D4C3k9.uj6PG', NULL, NULL, 2147483647, 'egypy', 'customer', '2025-03-17 15:12:28', 1, NULL, NULL, NULL, NULL, NULL, 'Male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `food`
--
ALTER TABLE `food`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `fk_caterer` (`caterer_id`);

--
-- Indexes for table `food_categories`
--
ALTER TABLE `food_categories`
  ADD PRIMARY KEY (`food_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_category` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `food_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `food`
--
ALTER TABLE `food`
  ADD CONSTRAINT `fk_caterer` FOREIGN KEY (`caterer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_categories`
--
ALTER TABLE `food_categories`
  ADD CONSTRAINT `food_categories_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `food` (`food_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
