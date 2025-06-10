-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 12:18 PM
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
-- Database: `today_s_meal_10`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `cat_id`, `last_login`) VALUES
(1, NULL, '2025-05-22 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `action_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` text DEFAULT NULL,
  `action_target` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_actions`
--

INSERT INTO `admin_actions` (`action_id`, `admin_id`, `action_type`, `action_target`, `created_at`) VALUES
(1, 1, 'Status Changed: active → suspended', 'Kitchen: Cairo Delights (ID: 80)', '2025-06-04 13:19:06'),
(2, 1, 'Status Changed: suspended → blocked', 'Kitchen: Cairo Delights (ID: 80)', '2025-06-04 13:19:09'),
(3, 1, 'Rejected Kitchen', 'b2k', '2025-06-04 16:50:04'),
(4, 1, 'Approved Kitchen', 'Nile Bites', '2025-06-04 16:50:33'),
(5, 1, 'Document Notes Added', 'Document ID: 14', '2025-06-04 19:31:32'),
(6, 1, 'Document Notes Added', 'Document ID: 15', '2025-06-04 19:31:59'),
(7, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:36:26'),
(8, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:36:53'),
(9, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:39:26'),
(10, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:42:39'),
(11, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:45:15'),
(12, 1, 'Kitchen Suspension', 'Suspended Cairo Cuisine for 0.5 hours', '2025-06-04 22:45:24'),
(13, 1, 'Unblocked kitchen', 'Kitchen ID: 3', '2025-06-04 23:51:19'),
(14, 1, 'Suspended kitchen', 'Kitchen ID: 3, Reason: ovan maintainance', '2025-06-04 23:51:43'),
(15, 1, 'Blocked kitchen', 'Kitchen ID: 3', '2025-06-04 23:51:50'),
(16, 1, 'Unblocked kitchen', 'Kitchen ID: 3', '2025-06-04 23:51:53'),
(17, 1, 'Suspended kitchen', 'Kitchen ID: 3, Reason: ovan maitanance', '2025-06-04 23:52:03'),
(18, 1, 'Unsuspended kitchen', 'Kitchen ID: 3', '2025-06-04 23:52:41'),
(19, 1, 'Deleted kitchen', 'Kitchen: Cairo Kitchen (ID: 3)', '2025-06-04 23:57:21'),
(20, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-04 23:59:17'),
(21, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-04 23:59:21'),
(22, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-04 23:59:23'),
(23, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-04 23:59:32'),
(24, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:03:06'),
(25, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:03:09'),
(26, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:39:56'),
(27, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:40:20'),
(28, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:40:45'),
(29, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-05 00:40:51'),
(30, 1, 'Suspended kitchen', 'Kitchen ID: 5, Reason: ovan maintinance', '2025-06-05 00:44:16'),
(31, 1, 'Unblocked kitchen', 'Kitchen ID: 80', '2025-06-05 01:11:58'),
(32, 1, 'Created category', 'category1', '2025-06-05 01:56:13'),
(33, 1, 'Document Notes Added', 'Document ID: 14', '2025-06-05 03:09:32'),
(34, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-05 03:10:54'),
(35, 1, 'Suspended kitchen', 'Kitchen ID: 18, Reason: ovan maintainace', '2025-06-05 03:11:48'),
(36, 1, 'Unsuspended kitchen', 'Kitchen ID: 18', '2025-06-05 03:11:57'),
(37, 1, 'Deleted kitchen', 'Kitchen: Giza Gourmet (ID: 18)', '2025-06-05 03:12:20'),
(38, 1, 'Suspended kitchen', 'Kitchen ID: 20, Reason: ovan maintainance', '2025-06-05 03:21:24'),
(39, 1, 'Blocked kitchen', 'Kitchen ID: 22', '2025-06-05 03:22:04'),
(40, 1, 'Document Notes Added', 'Document ID: 14', '2025-06-05 03:38:37'),
(41, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-05 03:39:17'),
(42, 1, 'Suspended kitchen', 'Kitchen ID: 26, Reason: ovan maintainance', '2025-06-05 03:40:22'),
(43, 1, 'Blocked kitchen', 'Kitchen ID: 80', '2025-06-05 03:40:45'),
(44, 1, 'Deleted kitchen', 'Kitchen: Nile Bites (ID: 81)', '2025-06-05 03:41:11'),
(45, 1, 'Created subcategory', 'egyptian', '2025-06-05 03:46:55'),
(46, 1, 'Blocked kitchen', 'Kitchen ID: 5', '2025-06-05 03:50:40'),
(47, 1, 'Blocked kitchen', 'Kitchen ID: 28', '2025-06-05 16:47:05'),
(48, 1, 'Document Notes Added', 'Document ID: 81', '2025-06-05 20:31:43'),
(49, 1, 'Document Notes Cleared', 'Document ID: 81', '2025-06-05 20:31:53'),
(50, 1, 'Unblocked kitchen', 'Kitchen ID: 5', '2025-06-05 20:33:31'),
(51, 1, 'Suspended kitchen', 'Kitchen ID: 5, Reason: ovan maintainance', '2025-06-05 20:34:05'),
(52, 1, 'Created category', 'category22', '2025-06-06 21:51:57'),
(53, 1, 'Created subcategory', 'sub22', '2025-06-06 21:56:35'),
(54, 1, 'Created subcategory', 'sub44', '2025-06-06 21:58:35'),
(55, 1, 'Created subcategory', 'sub55', '2025-06-06 21:58:59'),
(56, 1, 'Created subcategory', 'sub80', '2025-06-06 21:59:27'),
(57, 1, 'Blocked customer', 'customer22', '2025-06-07 11:49:38'),
(58, 1, 'Activated customer', 'customer22', '2025-06-07 11:49:43'),
(59, 1, 'Approved Kitchen', 'Marashly Meals', '2025-06-07 11:52:16'),
(60, 1, 'Blocked customer', 'customer22', '2025-06-07 11:55:38'),
(61, 1, 'Activated customer', 'customer22', '2025-06-07 11:55:40'),
(62, 1, 'Blocked customer', 'Youssef Ali', '2025-06-07 12:32:42'),
(63, 1, 'Activated customer', 'Youssef Ali', '2025-06-07 12:32:49'),
(64, 1, 'Blocked customer', 'customer1', '2025-06-07 14:18:58'),
(65, 1, 'Activated customer', 'customer1', '2025-06-07 14:19:01'),
(66, 1, 'Blocked customer', 'Omar Hassan', '2025-06-07 15:19:55'),
(67, 1, 'Blocked customer', 'Youssef Ibrahim', '2025-06-08 11:58:43'),
(68, 1, 'Activated customer', 'Youssef Ibrahim', '2025-06-08 11:58:53'),
(69, 1, 'Deleted customer', 'customer22', '2025-06-08 11:59:07'),
(70, 1, 'Created category', 'cat55', '2025-06-08 13:56:19'),
(71, 1, 'Document Notes Added', 'Document ID: 83', '2025-06-10 08:31:20');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `cloud_kitchen_id`, `created_at`) VALUES
(1, 83, 20, '2025-06-07 13:48:44'),
(2, 86, 9, '2025-06-08 04:54:07'),
(3, 90, 9, '2025-06-08 13:37:29');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `meal_id`, `quantity`, `price`) VALUES
(1, 1, 32, 2, 250.00),
(2, 2, 7, 3, 40.00),
(3, 2, 8, 1, 45.00),
(4, 3, 7, 3, 40.00),
(5, 3, 8, 1, 45.00);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_photo` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `cloud_kitchens_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `c_name`, `description`, `category_photo`, `created_by`, `cloud_kitchens_count`) VALUES
(1, 'Egyptian Cuisine', 'Traditional and Classic Egyptian dishes ', 'egyptian.jpg', 1, 15),
(2, 'Italian Cuisine', 'Pasta, pizza and other Italian favorites', 'italian.jpg', 1, 10),
(3, 'Asian Cuisine', 'Chinese, Japanese, Thai and other Asian dishes', 'asian.jpg', 1, 8),
(4, 'Healthy Food', 'Low-calorie, high-protein and balanced meals', 'healthy.jpg', 1, 7),
(5, 'Desserts', 'Sweet treats and baked goods', 'desserts.jpg', 1, 5),
(6, 'Seafood', 'Fresh fish and seafood dishes', 'seafood.jpg', 1, 3),
(7, 'Grill', 'Grilled meats and vegetables', 'grilled.jpg', 1, 6),
(8, 'Fast Food', 'fast food kinds', 'ff.jpg', 1, 4),
(9, 'Bakeries', 'All Types of Bakries', 'bake.jpg', 1, 3),
(10, 'Mediterranean', 'Dishes from the Mediterranean region', 'mediterranean.jpg', 1, 5),
(11, 'Breakfast', 'Morning meals and brunch options', 'breakfast.jpg', 1, 4),
(12, 'Sandwiches', 'Various sandwich options', 'sandwiches.jpg', 1, 3),
(13, 'Salads', 'Fresh salad options', 'salads.jpg', 1, 2),
(14, 'Juices', 'Fresh juices and smoothies', 'juices.jpg', 1, 2),
(15, 'International', 'Dishes from around the world', 'international.jpg', 1, 4),
(16, 'category1', 'category for categoyr', NULL, 1, 0),
(18, 'cat55', 'tt', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `caterer_tags`
--

CREATE TABLE `caterer_tags` (
  `user_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caterer_tags`
--

INSERT INTO `caterer_tags` (`user_id`, `tag_id`) VALUES
(5, 1),
(5, 6),
(7, 4),
(7, 15),
(9, 2),
(9, 7),
(9, 9),
(20, 4),
(20, 13),
(22, 4),
(22, 7),
(24, 2),
(24, 8),
(24, 9),
(26, 2),
(26, 9),
(26, 10),
(28, 4),
(28, 14),
(30, 10),
(30, 11),
(32, 1),
(32, 6),
(34, 7),
(34, 9),
(34, 13),
(36, 10),
(36, 11),
(38, 4),
(38, 15),
(40, 4),
(40, 14),
(42, 1),
(42, 6),
(44, 4),
(44, 15),
(46, 2),
(46, 7),
(46, 9),
(48, 8),
(48, 10),
(50, 4),
(50, 13);

-- --------------------------------------------------------

--
-- Table structure for table `cloud_kitchen_owner`
--

CREATE TABLE `cloud_kitchen_owner` (
  `user_id` int(11) NOT NULL,
  `start_year` year(4) NOT NULL,
  `c_n_id` varchar(50) NOT NULL,
  `status` enum('active','suspended','blocked') DEFAULT 'active',
  `orders_count` int(11) DEFAULT 0,
  `business_name` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `years_of_experience` enum('Beginner (0-1 years)','Intermediate (2-3 years)','Advanced (4-5 years)','Expert (6+ years)') NOT NULL,
  `customized_orders` tinyint(1) DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `is_approved` tinyint(1) DEFAULT 0,
  `speciality_id` int(11) NOT NULL,
  `suspension_reason` text DEFAULT NULL,
  `suspension_date` timestamp NULL DEFAULT NULL,
  `suspended_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cloud_kitchen_owner`
--

INSERT INTO `cloud_kitchen_owner` (`user_id`, `start_year`, `c_n_id`, `status`, `orders_count`, `business_name`, `registration_date`, `years_of_experience`, `customized_orders`, `average_rating`, `is_approved`, `speciality_id`, `suspension_reason`, `suspension_date`, `suspended_by`) VALUES
(5, '2019', '30005234567890', 'suspended', 85, 'Port Said Delights', '2024-02-20 09:30:00', 'Beginner (0-1 years)', 0, 4.20, 1, 2, 'ovan maintainance', '2025-06-05 20:34:04', 1),
(7, '2017', '30005345678901', 'blocked', 150, 'Ismailia Meals', '2024-03-10 07:15:00', 'Advanced (4-5 years)', 1, 4.75, 1, 3, NULL, NULL, NULL),
(9, '2020', '30005456789012', 'blocked', 60, 'Tanta Treats', '2024-04-05 12:45:00', 'Beginner (0-1 years)', 0, 3.90, 1, 4, NULL, NULL, NULL),
(20, '2019', '30005678901234', 'suspended', 95, 'Cairo Cuisine', '2024-06-18 05:30:00', 'Intermediate (2-3 years)', 0, 4.30, 1, 6, 'ovan maintainance', '2025-06-05 03:21:24', 1),
(22, '2018', '30005789012345', 'blocked', 110, 'Kasr El Aini Kitchen', '2024-07-22 09:15:00', 'Intermediate (2-3 years)', 1, 4.40, 1, 7, NULL, NULL, NULL),
(24, '2020', '30005890123456', 'blocked', 70, 'Ahram Meals', '2024-08-30 07:45:00', 'Beginner (0-1 years)', 0, 3.80, 1, 8, NULL, NULL, NULL),
(26, '2017', '30005901234567', 'suspended', 130, 'Remaya Restaurant', '2024-09-15 11:00:00', 'Advanced (4-5 years)', 1, 4.60, 1, 9, 'ovan maintainance', '2025-06-05 03:40:22', 1),
(28, '2019', '30006012345678', 'blocked', 90, 'Sudan Street Food', '2024-10-20 06:30:00', 'Intermediate (2-3 years)', 0, 4.10, 1, 10, NULL, NULL, NULL),
(30, '2018', '30006123456789', 'active', 105, 'Zamalek Zest', '2024-11-05 09:20:00', 'Intermediate (2-3 years)', 1, 4.45, 1, 11, NULL, NULL, NULL),
(32, '2020', '30006234567890', 'active', 65, 'Marashly Meals', '2024-12-10 13:10:00', 'Beginner (0-1 years)', 0, 3.95, 1, 12, NULL, NULL, NULL),
(34, '2017', '30006345678901', 'active', 140, 'Malek El Afdal Kitchen', '2025-01-15 08:25:00', 'Advanced (4-5 years)', 1, 4.70, 1, 13, NULL, NULL, NULL),
(36, '2019', '30006456789012', 'active', 80, 'Nahda Nutrition', '2025-02-20 11:40:00', 'Intermediate (2-3 years)', 0, 4.15, 1, 14, NULL, NULL, NULL),
(38, '2018', '30006567890123', 'active', 115, 'Falaki Foods', '2025-03-25 14:55:00', 'Intermediate (2-3 years)', 1, 4.35, 1, 15, NULL, NULL, NULL),
(40, '2020', '30006678901234', 'active', 75, 'Sherifein Specialties', '2025-04-05 07:05:00', 'Beginner (0-1 years)', 0, 3.85, 1, 1, NULL, NULL, NULL),
(42, '2017', '30006789012345', 'active', 125, 'Sabtiya Savories', '2025-05-10 09:30:00', 'Advanced (4-5 years)', 1, 4.55, 1, 2, NULL, NULL, NULL),
(44, '2019', '30006890123456', 'active', 85, 'Mansour Meals', '2025-05-15 11:45:00', 'Intermediate (2-3 years)', 0, 4.25, 1, 3, NULL, NULL, NULL),
(46, '2018', '30006901234567', 'active', 100, 'Merghany Munchies', '2025-05-18 14:00:00', 'Intermediate (2-3 years)', 1, 4.30, 1, 4, NULL, NULL, NULL),
(48, '2020', '30007012345678', 'active', 55, 'Hegaz Healthy', '2025-05-20 07:15:00', 'Beginner (0-1 years)', 0, 3.75, 1, 5, NULL, NULL, NULL),
(50, '2017', '30007123456789', 'active', 160, 'Maamoun Meals', '2025-05-22 10:30:00', 'Expert (6+ years)', 1, 4.85, 1, 6, NULL, NULL, NULL),
(80, '2020', '30007234567890', 'blocked', 0, 'Cairo Delights', '2025-06-02 19:01:20', 'Intermediate (2-3 years)', 1, 0.00, 1, 1, NULL, NULL, NULL),
(81, '2025', '8525212', 'active', 0, 'bck', '2025-06-05 16:18:08', 'Beginner (0-1 years)', 0, 0.00, 0, 11, NULL, NULL, NULL),
(95, '2025', '20210170501963', 'active', 0, 'ck77', '2025-06-10 07:58:12', 'Beginner (0-1 years)', 1, 0.00, 0, 11, NULL, NULL, NULL),
(96, '2025', '20210170501741', 'active', 0, 'ck88', '2025-06-10 08:01:45', 'Beginner (0-1 years)', 1, 0.00, 0, 11, NULL, NULL, NULL),
(98, '2025', '202101741852', 'active', 0, 'b77', '2025-06-10 08:13:25', 'Intermediate (2-3 years)', 1, 0.00, 0, 9, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cloud_kitchen_specialist_category`
--

CREATE TABLE `cloud_kitchen_specialist_category` (
  `cloud_kitchen_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cloud_kitchen_specialist_category`
--

INSERT INTO `cloud_kitchen_specialist_category` (`cloud_kitchen_id`, `cat_id`) VALUES
(5, 2),
(5, 15),
(7, 3),
(7, 15),
(9, 4),
(9, 8),
(20, 6),
(20, 7),
(22, 1),
(22, 7),
(24, 8),
(24, 9),
(26, 4),
(26, 9),
(28, 1),
(28, 10),
(30, 10),
(30, 11),
(32, 12),
(32, 13),
(34, 4),
(34, 13),
(36, 11),
(36, 14),
(38, 3),
(38, 15),
(40, 1),
(40, 10),
(42, 2),
(42, 15),
(44, 3),
(44, 7),
(46, 4),
(46, 8),
(48, 5),
(48, 14),
(50, 6),
(50, 7),
(80, 1),
(80, 10),
(95, 11),
(95, 18),
(96, 11),
(96, 18),
(98, 9),
(98, 11);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `customer_id` int(11) DEFAULT NULL,
  `kitchen_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `subject`, `message`, `status`, `customer_id`, `kitchen_id`, `created_at`, `resolved_at`) VALUES
(2, 'Missing Item', 'The dessert was missing from my order', 'resolved', 4, 5, '2025-05-02 11:30:00', '2025-05-02 13:00:00'),
(3, 'Wrong Order', 'Received someone else\'s order', 'pending', 6, 7, '2025-05-03 14:15:00', NULL),
(4, 'Food Quality', 'The salad was not fresh', 'resolved', 8, 9, '2025-05-04 09:15:00', '2025-05-04 11:00:00'),
(6, 'Incorrect Temperature', 'Food arrived cold', 'resolved', 16, 20, '2025-05-06 17:30:00', '2025-05-06 19:00:00'),
(7, 'Rude Delivery', 'The delivery person was rude', 'pending', 17, 22, '2025-05-07 10:00:00', NULL),
(8, 'Allergy Concern', 'Had allergic reaction to undisclosed ingredient', 'resolved', 19, 24, '2025-05-08 14:00:00', '2025-05-08 16:00:00'),
(9, 'Portion Size', 'Portion was much smaller than expected', 'pending', 21, 26, '2025-05-09 15:30:00', NULL),
(10, 'Wrong Address', 'Order delivered to wrong address', 'resolved', 23, 28, '2025-05-10 11:00:00', '2025-05-10 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `BOD` date NOT NULL,
  `status` enum('active','suspended','blocked') DEFAULT 'active',
  `is_subscribed` tinyint(1) DEFAULT 0,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`user_id`, `gender`, `BOD`, `status`, `is_subscribed`, `last_login`, `registration_date`) VALUES
(2, 'Male', '1990-05-15', 'active', 1, '2025-05-22 06:30:00', '2024-01-10 06:00:00'),
(4, 'Female', '1985-08-22', 'active', 0, '2025-05-21 11:15:00', '2024-02-15 08:30:00'),
(5, 'Female', '2025-06-18', 'active', 0, '2025-06-08 11:58:53', '2025-06-08 11:54:45'),
(6, 'Female', '1995-03-10', 'active', 1, '2025-05-22 08:45:00', '2024-03-20 10:00:00'),
(8, 'Female', '1988-11-05', 'active', 0, '2025-05-20 13:20:00', '2024-04-05 07:15:00'),
(10, 'Female', '1992-07-18', 'active', 1, '2025-05-22 05:10:00', '2024-05-12 11:45:00'),
(16, 'Male', '1993-09-25', 'active', 0, '2025-05-21 16:30:00', '2024-06-18 08:20:00'),
(17, 'Female', '1987-12-30', 'active', 1, '2025-05-22 07:15:00', '2024-07-22 13:40:00'),
(19, 'Female', '1991-04-12', 'active', 0, '2025-05-20 10:25:00', '2024-08-30 07:10:00'),
(21, 'Female', '1989-06-08', 'active', 1, '2025-05-22 09:05:00', '2024-09-15 11:30:00'),
(23, 'Female', '1994-02-14', 'active', 0, '2025-05-21 14:50:00', '2024-10-20 05:45:00'),
(24, 'Female', '2025-06-18', 'active', 0, '2025-06-08 11:54:05', '2025-06-08 11:54:05'),
(25, 'Female', '1996-10-03', 'active', 1, '2025-05-22 04:30:00', '2024-11-05 10:15:00'),
(27, 'Female', '1986-01-20', 'active', 0, '2025-05-21 17:10:00', '2024-12-10 13:50:00'),
(29, 'Female', '1990-07-07', 'active', 1, '2025-05-22 06:45:00', '2025-01-15 07:25:00'),
(31, 'Female', '1993-11-28', 'active', 0, '2025-05-20 11:35:00', '2025-02-20 09:40:00'),
(33, 'Female', '1988-05-17', 'active', 1, '2025-05-22 08:20:00', '2025-03-25 11:15:00'),
(35, 'Female', '1995-08-09', 'active', 0, '2025-05-21 15:05:00', '2025-04-05 14:30:00'),
(37, 'Female', '1991-12-24', 'active', 1, '2025-05-22 05:50:00', '2025-05-10 07:20:00'),
(39, 'Female', '1987-03-19', 'active', 0, '2025-05-20 12:40:00', '2025-05-15 11:10:00'),
(41, 'Female', '1994-06-22', 'active', 1, '2025-05-22 07:55:00', '2025-05-18 06:45:00'),
(43, 'Female', '1989-09-11', 'active', 0, '2025-05-21 16:15:00', '2025-05-20 09:30:00'),
(45, 'Male', '1992-04-05', 'active', 1, '2025-05-22 04:10:00', '2025-05-21 05:15:00'),
(47, 'Male', '1996-01-30', 'active', 0, '2025-05-21 13:25:00', '2025-05-22 07:40:00'),
(49, 'Female', '1985-10-15', 'active', 1, '2025-05-20 09:50:00', '2025-05-22 08:55:00'),
(82, 'Male', '1990-01-15', 'blocked', 1, '2025-06-07 15:19:55', '2025-06-02 19:02:45'),
(83, 'Female', '1988-05-22', 'active', 0, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(84, 'Male', '1995-09-10', 'active', 1, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(85, 'Female', '1992-11-05', 'active', 1, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(86, 'Male', '1987-07-18', 'active', 0, '2025-06-07 12:32:49', '2025-06-02 19:02:45'),
(90, 'Male', '2025-06-04', 'active', 0, '2025-06-07 14:19:01', '2025-06-04 11:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `customized_order`
--

CREATE TABLE `customized_order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `kitchen_id` int(11) NOT NULL,
  `budget_min` decimal(10,2) NOT NULL,
  `budget_max` decimal(10,2) NOT NULL,
  `chosen_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `ord_description` text NOT NULL,
  `img_reference` varchar(255) DEFAULT NULL,
  `people_servings` int(11) NOT NULL,
  `preferred_completion_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_approval` enum('approved','rejected','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customized_order`
--

INSERT INTO `customized_order` (`order_id`, `customer_id`, `kitchen_id`, `budget_min`, `budget_max`, `chosen_amount`, `status`, `ord_description`, `img_reference`, `people_servings`, `preferred_completion_date`, `created_at`, `customer_approval`) VALUES
(25, 4, 9, 100.00, 150.00, 130.00, 'accepted', 'Need a healthy meal plan for 5 people with high protein and low carbs', 'mealplan.jpg', 5, '2025-05-22 00:00:00', '2025-05-21 11:00:00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `delivery_person_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `distance` decimal(8,2) NOT NULL,
  `estimated_duration` int(11) NOT NULL,
  `scheduled_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pickup_time` timestamp NULL DEFAULT NULL,
  `completion_time` timestamp NULL DEFAULT NULL,
  `cancelled_time` timestamp NULL DEFAULT NULL,
  `delayed_time` timestamp NULL DEFAULT NULL,
  `last_status_update` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `p_method` enum('cash','visa') NOT NULL,
  `route_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_data`)),
  `estimated_distance` decimal(10,2) DEFAULT NULL COMMENT 'Estimated trip distance in meters',
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `delivery_person_id`, `customer_id`, `provider_id`, `distance`, `estimated_duration`, `scheduled_time`, `pickup_time`, `completion_time`, `cancelled_time`, `delayed_time`, `last_status_update`, `notes`, `p_method`, `route_data`, `estimated_distance`, `status`) VALUES
(2, '22', 69, 47, 20, 4698.82, 23494, '2025-06-02 15:04:22', '2025-06-02 14:03:56', '2025-06-02 14:04:22', NULL, NULL, '2025-06-02 14:04:22', NULL, 'visa', NULL, NULL, 'pending'),
(3, '22', 11, 47, 20, 4698.82, 23494, '2025-06-02 17:43:23', '2025-06-02 16:43:19', '2025-06-02 16:43:23', NULL, NULL, '2025-06-02 16:43:23', NULL, 'visa', NULL, NULL, 'pending'),
(5, '28', 87, 84, 80, 0.03, 0, '2025-06-02 20:49:28', NULL, NULL, NULL, NULL, NULL, NULL, 'visa', NULL, NULL, 'pending'),
(8, '26', 88, 82, 80, 0.08, 0, '2025-06-03 10:47:34', NULL, '2025-06-03 09:47:34', NULL, NULL, '2025-06-03 09:47:34', NULL, 'visa', NULL, NULL, 'pending'),
(9, '30', 87, 86, 80, 0.03, 0, '2025-06-03 17:27:04', NULL, NULL, NULL, NULL, NULL, NULL, 'visa', NULL, NULL, 'pending'),
(10, '7', 89, 17, 22, 0.00, 0, '2025-06-06 17:36:14', NULL, '2025-06-06 16:36:14', NULL, NULL, '2025-06-06 16:36:14', NULL, 'visa', NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_details`
--

CREATE TABLE `delivery_details` (
  `delivery_id` int(11) NOT NULL,
  `d_location` text NOT NULL,
  `p_method` enum('cash','visa') NOT NULL,
  `delivery_date_and_time` datetime NOT NULL,
  `d_status` enum('delivered','not_delivered') NOT NULL,
  `ord_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_man`
--

CREATE TABLE `delivery_man` (
  `user_id` int(11) NOT NULL,
  `d_n_id` varchar(20) DEFAULT NULL,
  `d_license` varchar(50) NOT NULL,
  `d_zone` varchar(255) NOT NULL,
  `status` enum('online','offline') DEFAULT 'offline',
  `current_status` enum('free','busy') DEFAULT 'free',
  `is_approved` tinyint(1) DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `del_id` varchar(255) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_man`
--

INSERT INTO `delivery_man` (`user_id`, `d_n_id`, `d_license`, `d_zone`, `status`, `current_status`, `is_approved`, `registration_date`, `del_id`, `zone_id`, `latitude`, `longitude`) VALUES
(11, '29905012345678', 'DL12345678', 'Cairo', 'online', 'busy', 1, '2024-01-05 07:00:00', '', NULL, 0.0000000, 0.0000000),
(12, '29905123456789', 'DL23456789', 'Giza', 'online', 'busy', 1, '2024-01-10 08:30:00', '', NULL, 0.0000000, 0.0000000),
(13, '29905234567890', 'DL34567890', 'Alexandria', 'offline', 'free', 1, '2024-02-15 09:45:00', '', NULL, 0.0000000, 0.0000000),
(14, '29905345678901', 'DL45678901', 'Heliopolis', 'online', 'free', 1, '2024-03-20 10:15:00', '', NULL, 0.0000000, 0.0000000),
(15, '29905456789012', 'DL56789012', 'Zamalek', 'offline', 'free', 1, '2024-04-25 12:30:00', '', NULL, 0.0000000, 0.0000000),
(54, NULL, 'DL20258075', 'Cairo', 'offline', 'free', 0, '2025-06-01 08:32:23', '', NULL, 0.0000000, 0.0000000),
(55, '299258790550', 'DL99185821', 'Cairo', 'offline', 'free', 1, '2025-06-01 12:30:12', '', NULL, 0.0000000, 0.0000000),
(58, '299257972178', 'DL11151502', 'Cairo', 'offline', 'free', 1, '2025-06-01 12:32:07', '', NULL, 0.0000000, 0.0000000),
(60, '299250851374', 'attached_assets/licenses/license_1748781429_2688.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 12:37:09', '', NULL, 0.0000000, 0.0000000),
(63, 'DEL250759006', 'attached_assets/licenses/license_1748781548_7813.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 12:39:08', '', NULL, 0.0000000, 0.0000000),
(65, '29948569752315', 'attached_assets/licenses/license_1748782937_2009.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 13:02:17', '', NULL, 0.0000000, 0.0000000),
(66, '29912345678910', 'attached_assets/licenses/license_1748784391_9770.p', 'Alexandria', 'offline', 'free', 0, '2025-06-01 13:26:32', '', NULL, 0.0000000, 0.0000000),
(68, '29912345678911', 'attached_assets/licenses/license_1748784971_5927.p', 'Zamalek', 'online', 'busy', 0, '2025-06-01 13:36:11', '', NULL, 0.0000000, 0.0000000),
(69, '299485697523191', 'attached_assets/licenses/license_1748786161_3787.p', 'Cairo', 'online', 'busy', 1, '2025-06-01 13:56:01', 'DEL256745555', NULL, 0.0000000, 0.0000000),
(72, '299123456788560', 'attached_assets/licenses/license_1748876176_4830.p', '', 'offline', 'free', 0, '2025-06-02 14:56:16', 'DEL259099722', NULL, 0.0000000, 0.0000000),
(73, '29912345678741', 'uploads/licenses/license_1748877702_3935.png', '', 'offline', 'free', 0, '2025-06-02 15:21:42', 'DEL259446791', NULL, 0.0000000, 0.0000000),
(75, '29912345678743', 'uploads/licenses/license_1748882439_6281.png', '', 'offline', 'free', 0, '2025-06-02 16:40:39', 'DEL251658908', NULL, 0.0000000, 0.0000000),
(76, '29912345690743', 'attached_assets/licenses/license_1748883525_9591.j', '', 'offline', 'free', 0, '2025-06-02 16:58:46', 'DEL259376321', NULL, 0.0000000, 0.0000000),
(87, '29948888852315', 'attached_assets/licenses/license_1748891025_2111.j', '5th settlement', 'online', 'busy', 1, '2025-06-02 19:03:45', 'DEL257045031', NULL, 30.0850000, 31.5382000),
(88, '29948569752417', 'attached_assets/licenses/license_1748947499_9227.j', '5th settlement', 'online', 'free', 1, '2025-06-03 10:44:59', 'DEL254779878', NULL, 30.0004560, 31.4626260),
(89, '2999952152155', 'attached_assets/licenses/license_1748971706_7841.p', '5th settlement', 'online', 'free', 1, '2025-06-03 17:28:26', 'DEL253124078', NULL, 30.0850000, 31.5382000);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_status_history`
--

CREATE TABLE `delivery_status_history` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_status_history`
--

INSERT INTO `delivery_status_history` (`id`, `delivery_id`, `status`, `timestamp`) VALUES
(4, 2, 'pending', '2025-06-02 15:01:06'),
(5, 2, 'in-progress', '2025-06-02 14:03:56'),
(6, 2, 'completed', '2025-06-02 14:04:22'),
(7, 3, 'pending', '2025-06-02 17:42:27'),
(8, 3, 'in-progress', '2025-06-02 16:43:19'),
(9, 3, 'completed', '2025-06-02 16:43:23'),
(13, 5, 'pending', '2025-06-02 20:49:28'),
(16, 8, 'pending', '2025-06-03 10:47:07'),
(17, 8, 'delivered', '2025-06-03 09:47:34'),
(19, 9, 'pending', '2025-06-03 17:27:04'),
(20, 10, 'pending', '2025-06-06 17:35:45'),
(21, 10, 'delivered', '2025-06-06 16:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_subscriptions`
--

CREATE TABLE `delivery_subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `plan_duration` enum('1_month','6_months','12_months') NOT NULL,
  `start_date` date NOT NULL DEFAULT curdate(),
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_subscriptions`
--

INSERT INTO `delivery_subscriptions` (`subscription_id`, `customer_id`, `plan_duration`, `start_date`, `end_date`, `is_active`, `payment_status`) VALUES
(1, 2, '6_months', '2025-01-10', '2025-07-10', 1, 'paid'),
(2, 6, '1_month', '2025-05-01', '2025-06-01', 1, 'paid'),
(3, 10, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(4, 17, '6_months', '2025-03-01', '2025-09-01', 1, 'paid'),
(5, 21, '1_month', '2025-05-15', '2025-06-15', 1, 'paid'),
(6, 25, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(7, 29, '6_months', '2025-04-01', '2025-10-01', 1, 'paid'),
(8, 33, '1_month', '2025-05-10', '2025-06-10', 1, 'paid'),
(9, 37, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(10, 41, '6_months', '2025-03-15', '2025-09-15', 1, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tokens`
--

CREATE TABLE `delivery_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `type` varchar(20) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dietary_tags`
--

CREATE TABLE `dietary_tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dietary_tags`
--

INSERT INTO `dietary_tags` (`tag_id`, `tag_name`) VALUES
(2, 'Dairy-Free'),
(12, 'Diabetic-Friendly'),
(1, 'Gluten-Free'),
(13, 'Heart-Healthy'),
(9, 'High-Fiber'),
(11, 'High-Protein'),
(5, 'Keto'),
(14, 'Kid-Friendly'),
(6, 'Low-Carb'),
(7, 'Low-Fat'),
(3, 'Nut-Free'),
(10, 'Organic'),
(15, 'Spicy'),
(8, 'Sugar-Free'),
(16, 'tag22'),
(17, 'tag55'),
(4, 'Vegan');

-- --------------------------------------------------------

--
-- Table structure for table `external_user`
--

CREATE TABLE `external_user` (
  `user_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `ext_role` enum('customer','cloud_kitchen_owner') NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `zone_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `external_user`
--

INSERT INTO `external_user` (`user_id`, `address`, `ext_role`, `latitude`, `longitude`, `zone_id`) VALUES
(2, '15 El Nozha St., Cairo', 'customer', 0.0000000, 0.0000000, 1),
(4, '37, Ismail Mohamed Street, Zamalek, Cairo, Egypt', 'customer', 30.0626300, 31.2496700, 1),
(5, '45 El Geish St., Port Said', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(6, '45 abo elfeda  , zamalek, egypt', 'customer', 30.0641800, 31.2200900, 1),
(7, '33 El Nasr St., Ismailia', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(8, '7 El Thawra St., Suez', 'customer', 0.0000000, 0.0000000, 1),
(9, '19 El Salam St., Tanta', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(10, '24 El Borg St., Aswan', 'customer', 0.0000000, 0.0000000, 1),
(16, '5 El Khalifa El Maamoun St., Cairo', 'customer', 0.0000000, 0.0000000, 1),
(17, '30 El Hegaz St., Heliopolis', 'customer', 30.0626300, 31.0626300, 1),
(19, '9 El Tahrir St., Dokki', 'customer', 0.0000000, 0.0000000, 1),
(20, '14 El Manyal St., Cairo', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(21, '3 El Azhar St., Cairo', 'customer', 0.0000000, 0.0000000, 1),
(22, '17 El Kasr El Aini St., Cairo', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(23, '2 El Nile St., Giza', 'customer', 0.0000000, 0.0000000, 1),
(24, '6 El Ahram St., Giza', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(25, '10 El Haram St., Giza', 'customer', 0.0000000, 0.0000000, 1),
(26, '13 El Remaya Sq., Giza', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(27, '20 El Batal Ahmed Abdel Aziz St., Mohandessin', 'customer', 0.0000000, 0.0000000, 1),
(28, '25 El Sudan St., Mohandessin', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(29, '18 El Mesaha Sq., Dokki', 'customer', 0.0000000, 0.0000000, 1),
(30, '16 El Brazil St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 1),
(31, '4 El Gabalaya St., Zamalek', 'customer', 0.0000000, 0.0000000, 1),
(32, '23 El Marashly St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(33, '1 El Gezira St., Zamalek', 'customer', 0.0000000, 0.0000000, 0),
(34, '27 El Malek El Afdal St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(35, '29 El Nil St., Agouza', 'customer', 0.0000000, 0.0000000, 0),
(36, '31 El Nahda St., Dokki', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(37, '32 El Tahrir St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(38, '34 El Falaki St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(39, '35 El Bustan St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(40, '36 El Sherifein St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(41, '37 El Gomhoreya St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(42, '38 El Sabtiya St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(43, '15 El Nozha St., Cairo', 'customer', 30.1406586, 31.3740845, 0),
(44, '40 El Mansour Mohamed St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(45, '41 El Sheikh El Sharbatly St., Heliopolis', 'customer', 0.0000000, 0.0000000, 0),
(46, '42 El Merghany St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(47, '45 mohamed mazhar zamalek egypt', 'customer', 30.0641800, 31.2200900, 0),
(48, '44 El Hegaz St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(49, '45 El Nozha St., Heliopolis', 'customer', 0.0000000, 0.0000000, 0),
(50, '46 El Khalifa El Maamoun St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(80, 'Building 12, Road 90, 5th Settlement', 'cloud_kitchen_owner', 30.0130560, 31.4200000, 2),
(81, 'cairo egypt', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2),
(82, 'Villa 25, Street 200, 5th Settlement', 'customer', 30.0125000, 31.4195000, 2),
(83, 'Apartment 301, Building 45, 5th Settlement', 'customer', 30.0140000, 31.4210000, 2),
(84, 'Compound 7, Road 150, 5th Settlement', 'customer', 30.0132000, 31.4203000, 2),
(85, 'Building 33, Road 180, 5th Settlement', 'customer', 30.0145000, 31.4220000, 2),
(86, 'Villa 12, Street 210, 5th Settlement', 'customer', 30.0128000, 31.4198000, 2),
(90, 'New Cairo, Cairo, Egypt', 'customer', 0.0000000, 0.0000000, 0),
(91, 'cairo egypt', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2),
(95, 'new cairo cairo egypt', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2),
(96, 'new cairo cairo egypt', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2),
(98, 'new cairo cairo egypt', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 2);

-- --------------------------------------------------------

--
-- Table structure for table `financial_settlements`
--

CREATE TABLE `financial_settlements` (
  `settlement_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `settlement_type` enum('delivery_receivable','delivery_payable','kitchen_payable') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `settlement_status` enum('pending','settled','partially_settled') DEFAULT 'pending',
  `settlement_date` timestamp NULL DEFAULT NULL,
  `settlement_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_settlements`
--

INSERT INTO `financial_settlements` (`settlement_id`, `order_id`, `settlement_type`, `amount`, `settlement_status`, `settlement_date`, `settlement_reference`, `notes`, `created_at`, `updated_at`) VALUES
(31, 2, 'delivery_receivable', 122.00, 'pending', NULL, NULL, NULL, '2025-05-02 08:30:00', '2025-06-08 15:23:58'),
(32, 3, 'delivery_payable', 25.00, 'pending', NULL, NULL, NULL, '2025-05-03 09:45:00', '2025-06-08 14:49:35'),
(33, 4, 'delivery_receivable', 86.50, 'pending', NULL, NULL, NULL, '2025-05-04 06:15:00', '2025-06-08 15:23:58'),
(34, 6, 'delivery_receivable', 233.00, 'pending', NULL, NULL, NULL, '2025-05-06 13:20:00', '2025-06-08 15:23:58'),
(35, 7, 'delivery_payable', 15.00, 'pending', NULL, NULL, NULL, '2025-05-07 05:30:00', '2025-06-08 14:49:35'),
(36, 8, 'delivery_receivable', 86.50, 'settled', '2025-06-09 22:30:28', '', '', '2025-05-08 09:15:00', '2025-06-09 22:30:28'),
(37, 9, 'delivery_payable', 15.00, 'settled', '2025-06-08 22:05:16', '', '', '2025-05-09 11:00:00', '2025-06-08 22:05:16'),
(38, 10, 'delivery_receivable', 81.50, 'settled', '2025-06-08 16:14:38', '', '', '2025-05-10 06:30:00', '2025-06-08 16:14:38'),
(46, 2, 'kitchen_payable', 102.00, 'pending', NULL, NULL, NULL, '2025-05-02 08:30:00', '2025-06-08 14:49:35'),
(47, 3, 'kitchen_payable', 153.00, 'pending', NULL, NULL, NULL, '2025-05-03 09:45:00', '2025-06-08 14:49:35'),
(48, 4, 'kitchen_payable', 72.25, 'pending', NULL, NULL, NULL, '2025-05-04 06:15:00', '2025-06-08 14:49:35'),
(49, 6, 'kitchen_payable', 195.50, 'pending', NULL, NULL, NULL, '2025-05-06 13:20:00', '2025-06-08 14:49:35'),
(50, 7, 'kitchen_payable', 89.25, 'pending', NULL, NULL, NULL, '2025-05-07 05:30:00', '2025-06-08 14:49:35'),
(51, 8, 'kitchen_payable', 72.25, 'pending', NULL, NULL, NULL, '2025-05-08 09:15:00', '2025-06-08 14:49:35'),
(52, 9, 'kitchen_payable', 72.25, 'settled', '2025-06-08 22:07:59', '', '', '2025-05-09 11:00:00', '2025-06-08 22:07:59'),
(53, 10, 'kitchen_payable', 68.00, 'settled', '2025-06-08 19:47:56', '', '', '2025-05-10 06:30:00', '2025-06-08 19:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_documents`
--

CREATE TABLE `kitchen_documents` (
  `doc_id` int(11) NOT NULL,
  `kitchen_id` int(11) NOT NULL,
  `document_type` enum('national_id','business_license','health_certificate','tax_certificate','kitchen_photos','other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchen_documents`
--

INSERT INTO `kitchen_documents` (`doc_id`, `kitchen_id`, `document_type`, `document_name`, `file_path`, `file_size`, `file_type`, `upload_date`, `admin_notes`, `reviewed_by`, `reviewed_at`) VALUES
(5, 5, 'national_id', 'National ID - Youssef Ibrahim', 'uploads/documents/5/national_id_youssef.pdf', 298000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(6, 5, 'business_license', 'Business License - Port Said Delights', 'uploads/documents/5/business_license.pdf', 445000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(7, 7, 'national_id', 'National ID - Omar Khaled', 'uploads/documents/7/national_id_omar.pdf', 275000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(8, 7, 'health_certificate', 'Health Certificate', 'uploads/documents/7/health_cert.pdf', 367000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(9, 9, 'national_id', 'National ID - Karim Adel', 'uploads/documents/9/national_id_karim.pdf', 312000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(10, 9, 'business_license', 'Business License - Tanta Treats', 'uploads/documents/9/business_license.pdf', 523000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(12, 80, 'business_license', 'Business License - Cairo Delights', 'uploads/documents/80/business_license.pdf', 445000, 'application/pdf', '2025-06-04 17:04:04', NULL, NULL, NULL),
(13, 80, 'kitchen_photos', 'Kitchen Interior', 'uploads/documents/80/kitchen_interior.jpg', 1856000, 'image/jpeg', '2025-06-04 17:04:04', NULL, NULL, NULL),
(81, 81, 'national_id', 'National ID  ', 'uploads/documents/81/national_id.pdf', 287000, 'application/pdf', '2025-06-04 17:04:04', '', 1, '2025-06-05 20:31:53'),
(83, 95, 'national_id', 'National ID', 'uploads/documents/95/national_id_1749542292.jpg', 133202, 'image/jpeg', '2025-06-10 07:58:12', 'this nid is expired', 1, '2025-06-10 08:31:20'),
(84, 95, 'business_license', 'Business License', 'uploads/documents/95/business_license_1749542292.PNG', 1731693, 'image/png', '2025-06-10 07:58:12', NULL, NULL, NULL),
(85, 95, 'health_certificate', 'Health Certificate', 'uploads/documents/95/health_certificate_1749542292.png', 152265, 'image/png', '2025-06-10 07:58:12', NULL, NULL, NULL),
(86, 95, 'tax_certificate', 'Tax Certificate', 'uploads/documents/95/tax_certificate_1749542292.jpg', 133202, 'image/jpeg', '2025-06-10 07:58:12', NULL, NULL, NULL),
(87, 96, 'national_id', 'National ID', 'uploads/documents/96/national_id_1749542505.pdf', 15263, 'application/pdf', '2025-06-10 08:01:45', NULL, NULL, NULL),
(88, 96, 'business_license', 'Business License', 'uploads/documents/96/business_license_1749542505.pdf', 15869, 'application/pdf', '2025-06-10 08:01:45', NULL, NULL, NULL),
(89, 98, 'national_id', 'National ID', 'uploads/documents/98/national_id_1749543205.pdf', 15263, 'application/pdf', '2025-06-10 08:13:25', NULL, NULL, NULL),
(90, 98, 'business_license', 'Business License', 'uploads/documents/98/business_license_1749543205.pdf', 15869, 'application/pdf', '2025-06-10 08:13:25', NULL, NULL, NULL),
(91, 95, 'national_id', 'National ID', 'uploads/documents/95/national_id_1749545535.pdf', 15263, 'application/pdf', '2025-06-10 08:52:15', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `meal_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('out of stock','low stock','available') DEFAULT 'out of stock',
  `price` decimal(10,2) NOT NULL,
  `visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`meal_id`, `cloud_kitchen_id`, `name`, `description`, `photo`, `stock_quantity`, `status`, `price`, `visible`) VALUES
(3, 5, 'Spaghetti Carbonara', 'Classic Italian pasta with creamy sauce and bacon', 'carbonara.jpg', 40, 'available', 55.00, 1),
(4, 5, 'Margherita Pizza', 'Traditional pizza with tomato sauce and mozzarella', 'pizza.jpg', 25, 'available', 65.00, 1),
(5, 7, 'Chicken Pad Thai', 'Thai stir-fried noodles with chicken and vegetables', 'padthai.jpg', 35, 'available', 60.00, 1),
(6, 7, 'Sushi Platter', 'Assorted sushi rolls with soy sauce and wasabi', 'sushi.jpg', 20, 'low stock', 120.00, 1),
(7, 9, 'Grilled Chicken Salad', 'Healthy salad with grilled chicken and vegetables', 'chickensalad.jpg', 45, 'available', 40.00, 1),
(8, 9, 'Quinoa Bowl', 'Nutritious quinoa with vegetables and tahini dressing', 'quinoa.jpg', 30, 'available', 45.00, 1),
(11, 20, 'Grilled Sea Bass', 'Fresh sea bass with lemon and herbs', 'seabass.jpg', 10, 'low stock', 150.00, 1),
(12, 20, 'Fried Calamari', 'Crispy fried squid with tartar sauce', 'calamari.jpg', 15, 'available', 80.00, 1),
(13, 22, 'Grilled Kofta', 'Egyptian grilled meat skewers with rice', 'kofta.jpg', 30, 'available', 60.00, 1),
(14, 22, 'Chicken Shawarma', 'Marinated chicken with garlic sauce and bread', 'shawarma.jpg', 25, 'available', 45.00, 1),
(15, 24, 'Vegetable Tagine', 'Moroccan vegetable stew with couscous', 'tagine.jpg', 20, 'available', 50.00, 1),
(16, 24, 'Falafel Plate', 'Fried falafel with tahini and salad', 'falafel.jpg', 35, 'available', 35.00, 1),
(17, 26, 'Vegan Burger', 'Plant-based burger with vegan cheese', 'veganburger.jpg', 25, 'available', 55.00, 1),
(18, 26, 'Vegan Chocolate Mousse', 'Dairy-free chocolate dessert', 'veganmousse.jpg', 15, 'available', 30.00, 1),
(19, 28, 'Greek Salad', 'Fresh salad with feta cheese and olives', 'greeksalad.jpg', 40, 'available', 45.00, 1),
(20, 28, 'Hummus Plate', 'Creamy hummus with pita bread', 'hummus.jpg', 30, 'available', 35.00, 1),
(21, 30, 'Foul Medames', 'Traditional Egyptian breakfast beans', 'foul.jpg', 50, 'available', 25.00, 1),
(22, 30, 'Taameya', 'Egyptian falafel with tahini sauce', 'taameya.jpg', 40, 'available', 30.00, 1),
(23, 32, 'Cheese Burger', 'Classic beef burger with cheese', 'cheeseburger.jpg', 30, 'available', 65.00, 1),
(24, 32, 'Chicken Sub', 'Grilled chicken sub with vegetables', 'chickensub.jpg', 25, 'available', 45.00, 1),
(25, 34, 'Caesar Salad', 'Romaine lettuce with Caesar dressing and croutons', 'caesar.jpg', 35, 'available', 40.00, 1),
(26, 34, 'Tabbouleh', 'Lebanese parsley and bulgur salad', 'tabbouleh.jpg', 30, 'available', 35.00, 1),
(27, 36, 'Orange Juice', 'Freshly squeezed orange juice', 'orangejuice.jpg', 50, 'available', 20.00, 1),
(28, 36, 'Strawberry Smoothie', 'Fresh strawberry smoothie with yogurt', 'smoothie.jpg', 40, 'available', 30.00, 1),
(29, 38, 'Chicken Tikka Masala', 'Indian chicken curry with rice', 'tikkamasala.jpg', 25, 'available', 70.00, 1),
(30, 38, 'Vegetable Biryani', 'Fragrant rice with mixed vegetables', 'biryani.jpg', 30, 'available', 55.00, 1),
(31, 40, 'Koshari', 'Traditional Egyptian dish with rice, pasta, lentils, and crispy onions', 'koshari2.jpg', 40, 'available', 30.00, 1),
(32, 40, 'Fatta', 'Egyptian dish with rice, bread, meat and garlic vinegar sauce', 'fatta.jpg', 25, 'available', 50.00, 1),
(33, 42, 'Penne Arrabiata', 'Spicy tomato pasta with chili', 'arrabiata.jpg', 35, 'available', 50.00, 1),
(34, 42, 'Lasagna', 'Layered pasta with meat and cheese', 'lasagna.jpg', 20, 'available', 65.00, 1),
(35, 44, 'Beef Teriyaki', 'Japanese beef with teriyaki sauce and rice', 'teriyaki.jpg', 25, 'available', 80.00, 1),
(36, 44, 'Vegetable Tempura', 'Assorted fried vegetables with dipping sauce', 'tempura.jpg', 30, 'available', 45.00, 1),
(37, 46, 'Protein Bowl', 'High-protein meal with chicken and quinoa', 'proteinbowl.jpg', 35, 'available', 55.00, 1),
(38, 46, 'Avocado Toast', 'Whole grain toast with avocado and eggs', 'avocadotoast.jpg', 40, 'available', 40.00, 1),
(39, 48, 'Red Velvet Cake', 'Classic red velvet cake with cream cheese frosting', 'redvelvet.jpg', 15, 'available', 60.00, 1),
(40, 48, 'Cheesecake', 'New York style cheesecake with berry sauce', 'cheesecake.jpg', 10, 'low stock', 65.00, 1),
(41, 50, 'Grilled Prawns', 'Jumbo prawns with garlic butter', 'prawns.jpg', 15, 'available', 180.00, 1),
(42, 50, 'Seafood Paella', 'Spanish rice dish with mixed seafood', 'paella.jpg', 10, 'low stock', 200.00, 1),
(43, 80, 'Stuffed Vine Leaves', 'Traditional Egyptian dish with rice and herbs', 'vineleaves.jpg', 30, 'available', 45.00, 1),
(44, 80, 'Grilled Kofta', 'Egyptian grilled meat with spices', 'kofta.jpg', 40, 'available', 65.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `meals_in_each_package`
--

CREATE TABLE `meals_in_each_package` (
  `package_meal_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals_in_each_package`
--

INSERT INTO `meals_in_each_package` (`package_meal_id`, `package_id`, `meal_id`, `quantity`, `price`) VALUES
(1, 1, 5, 2, 120.00),
(2, 2, 6, 1, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `meal_category`
--

CREATE TABLE `meal_category` (
  `meal_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_category`
--

INSERT INTO `meal_category` (`meal_id`, `cat_id`) VALUES
(3, 2),
(4, 2),
(5, 3),
(6, 3),
(7, 4),
(8, 4),
(11, 6),
(12, 6),
(13, 7),
(14, 7),
(15, 8),
(16, 8),
(17, 9),
(18, 5),
(18, 9),
(19, 10),
(20, 10),
(21, 1),
(21, 11),
(22, 1),
(22, 11),
(23, 12),
(24, 12),
(25, 13),
(26, 13),
(27, 14),
(28, 14),
(29, 15),
(30, 15),
(31, 1),
(32, 1),
(33, 2),
(34, 2),
(35, 3),
(36, 3),
(37, 4),
(38, 4),
(38, 11),
(39, 5),
(40, 5),
(41, 6),
(42, 6);

-- --------------------------------------------------------

--
-- Table structure for table `meal_dietary_tag`
--

CREATE TABLE `meal_dietary_tag` (
  `meal_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_dietary_tag`
--

INSERT INTO `meal_dietary_tag` (`meal_id`, `tag_id`) VALUES
(4, 10),
(4, 14),
(41, 3),
(41, 4);

-- --------------------------------------------------------

--
-- Table structure for table `meal_subcategory`
--

CREATE TABLE `meal_subcategory` (
  `meal_id` int(11) NOT NULL,
  `subcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_subcategory`
--

INSERT INTO `meal_subcategory` (`meal_id`, `subcat_id`) VALUES
(3, 5),
(4, 6),
(5, 9),
(6, 8),
(41, 5);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ord_type` enum('customized','normal','scheduled') NOT NULL,
  `delivery_type` enum('all_at_once','daily_delivery') DEFAULT NULL,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `customer_selected_date` date DEFAULT NULL,
  `order_status` enum('pending','preparing','ready_for_pickup','in_transit','delivered','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `cloud_kitchen_id`, `total_price`, `order_date`, `ord_type`, `delivery_type`, `delivery_date`, `customer_selected_date`, `order_status`) VALUES
(2, 4, 5, 120.00, '2025-05-02 08:30:00', 'normal', 'all_at_once', '2025-05-02 11:00:00', NULL, 'preparing'),
(3, 6, 7, 180.00, '2025-05-03 09:45:00', 'normal', 'all_at_once', '2025-05-03 12:15:00', NULL, 'ready_for_pickup'),
(4, 8, 9, 85.00, '2025-05-04 06:15:00', 'normal', 'all_at_once', '2025-05-04 08:45:00', NULL, 'in_transit'),
(6, 16, 20, 230.00, '2025-05-06 13:20:00', 'normal', 'all_at_once', '2025-05-06 15:50:00', NULL, 'delivered'),
(7, 17, 22, 105.00, '2025-05-07 05:30:00', 'normal', 'all_at_once', '2025-05-07 08:00:00', NULL, 'pending'),
(8, 19, 24, 85.00, '2025-05-08 09:15:00', 'normal', 'all_at_once', '2025-05-08 11:45:00', NULL, 'pending'),
(9, 21, 26, 85.00, '2025-05-09 11:00:00', 'normal', 'all_at_once', '2025-05-09 13:30:00', NULL, 'preparing'),
(10, 23, 28, 80.00, '2025-05-10 06:30:00', 'normal', 'all_at_once', '2025-05-10 09:00:00', NULL, 'ready_for_pickup'),
(11, 25, 30, 55.00, '2025-05-11 08:20:00', 'normal', 'all_at_once', '2025-05-11 10:50:00', NULL, 'in_transit'),
(12, 27, 32, 110.00, '2025-05-12 12:10:00', 'normal', 'all_at_once', '2025-05-12 14:40:00', NULL, 'delivered'),
(13, 29, 34, 75.00, '2025-05-13 07:25:00', 'normal', 'all_at_once', '2025-05-13 09:55:00', NULL, 'ready_for_pickup'),
(14, 31, 36, 50.00, '2025-05-14 10:40:00', 'normal', 'all_at_once', '2025-05-14 13:10:00', NULL, 'delivered'),
(15, 33, 38, 125.00, '2025-05-15 13:55:00', 'normal', 'all_at_once', '2025-05-15 16:25:00', NULL, 'delivered'),
(16, 35, 40, 80.00, '2025-05-16 06:05:00', 'normal', 'all_at_once', '2025-05-16 08:35:00', NULL, 'preparing'),
(17, 37, 42, 115.00, '2025-05-17 09:30:00', 'normal', 'all_at_once', '2025-05-17 12:00:00', NULL, 'ready_for_pickup'),
(18, 39, 44, 125.00, '0000-00-00 00:00:00', 'normal', 'all_at_once', '2025-05-18 14:15:00', NULL, 'pending'),
(19, 41, 46, 95.00, '2025-05-19 14:00:00', 'normal', 'all_at_once', '2025-05-19 16:30:00', NULL, 'pending'),
(20, 43, 48, 125.00, '2025-05-20 07:15:00', 'normal', 'all_at_once', '2025-05-20 09:45:00', NULL, 'pending'),
(21, 45, 50, 380.00, '2025-05-21 10:30:00', 'normal', 'all_at_once', '2025-05-21 13:00:00', NULL, 'pending'),
(22, 47, 20, 80.00, '2025-05-22 05:45:00', 'normal', 'all_at_once', '2025-05-22 08:15:00', NULL, 'pending'),
(23, 49, 5, 175.00, '2025-05-22 07:20:00', 'normal', 'all_at_once', '2025-05-22 09:50:00', NULL, 'pending'),
(24, 2, 7, 240.00, '2025-05-22 08:55:00', 'scheduled', 'daily_delivery', '2025-05-23 10:00:00', NULL, 'pending'),
(25, 4, 9, 130.00, '2025-05-22 10:30:00', 'customized', NULL, '2025-05-22 13:00:00', NULL, 'pending'),
(26, 82, 80, 110.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, NULL, 'pending'),
(30, 86, 80, 130.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, NULL, 'pending'),
(31, 86, 20, 50.00, '2025-06-08 05:02:08', 'normal', 'daily_delivery', '0000-00-00 00:00:00', '0000-00-00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_content`
--

CREATE TABLE `order_content` (
  `order_content_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_content`
--

INSERT INTO `order_content` (`order_content_id`, `order_id`, `meal_id`, `quantity`, `price`) VALUES
(3, 2, 3, 1, 55.00),
(4, 2, 4, 1, 65.00),
(5, 3, 5, 2, 120.00),
(6, 3, 6, 1, 120.00),
(7, 4, 7, 1, 40.00),
(8, 4, 8, 1, 45.00),
(11, 6, 11, 1, 150.00),
(12, 6, 12, 1, 80.00),
(13, 7, 13, 1, 60.00),
(14, 7, 14, 1, 45.00),
(15, 8, 15, 1, 50.00),
(16, 8, 16, 1, 35.00),
(17, 9, 17, 1, 55.00),
(18, 9, 18, 1, 30.00),
(19, 10, 19, 1, 45.00),
(20, 10, 20, 1, 35.00),
(21, 11, 21, 2, 50.00),
(22, 11, 22, 1, 30.00),
(23, 12, 23, 1, 65.00),
(24, 12, 24, 1, 45.00),
(25, 13, 25, 1, 40.00),
(26, 13, 26, 1, 35.00),
(27, 14, 27, 2, 40.00),
(28, 14, 28, 1, 30.00),
(29, 15, 29, 1, 70.00),
(30, 15, 30, 1, 55.00),
(31, 16, 31, 2, 60.00),
(32, 16, 32, 1, 50.00),
(33, 17, 33, 1, 50.00),
(34, 17, 34, 1, 65.00),
(35, 18, 35, 1, 80.00),
(36, 18, 36, 1, 45.00),
(37, 19, 37, 1, 55.00),
(38, 19, 38, 1, 40.00),
(39, 20, 39, 1, 60.00),
(40, 20, 40, 1, 65.00),
(41, 21, 41, 1, 180.00),
(42, 21, 42, 1, 200.00),
(45, 23, 3, 1, 55.00),
(46, 23, 4, 2, 130.00),
(47, 24, 5, 2, 120.00),
(48, 24, 6, 1, 120.00),
(49, 25, 7, 2, 80.00),
(50, 25, 8, 1, 45.00),
(51, 26, 43, 2, 90.00),
(52, 26, 44, 1, 65.00),
(57, 30, 44, 2, 130.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_packages`
--

CREATE TABLE `order_packages` (
  `package_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `delivery_date` date NOT NULL,
  `package_price` decimal(10,2) NOT NULL,
  `package_status` enum('pending','preparing','ready_for_pickup','in_transit','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_packages`
--

INSERT INTO `order_packages` (`package_id`, `order_id`, `package_name`, `delivery_date`, `package_price`, `package_status`, `payment_status`) VALUES
(1, 24, 'Day 1', '2025-05-23', 120.00, 'pending', 'pending'),
(2, 24, 'Day 2', '2025-05-24', 120.00, 'pending', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `total_ord_price` decimal(10,2) NOT NULL,
  `delivery_fees` decimal(10,2) NOT NULL,
  `website_revenue` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `p_date_time` datetime NOT NULL,
  `p_method` enum('cash','visa') NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`payment_id`, `order_id`, `total_ord_price`, `delivery_fees`, `website_revenue`, `total_payment`, `p_date_time`, `p_method`, `payment_status`) VALUES
(1, 2, 120.00, 50.00, 18.00, 170.00, '2025-05-02 08:30:00', 'cash', 'paid'),
(2, 3, 180.00, 10.00, 27.00, 190.00, '2025-05-03 09:45:00', 'visa', 'paid'),
(3, 4, 85.00, 13.00, 12.75, 98.00, '2025-05-04 06:15:00', 'cash', 'paid'),
(4, 6, 230.00, 15.00, 34.50, 245.00, '2025-05-06 13:20:00', 'cash', 'paid'),
(5, 7, 105.00, 35.00, 15.75, 140.00, '2025-05-07 05:30:00', 'cash', 'paid'),
(6, 8, 85.00, 25.00, 12.75, 110.00, '2025-05-08 09:15:00', 'visa', 'paid'),
(7, 9, 85.00, 35.00, 12.75, 120.00, '2025-05-09 11:00:00', 'cash', 'paid'),
(8, 10, 80.00, 25.00, 12.00, 105.00, '2025-05-10 06:30:00', 'visa', 'paid'),
(9, 11, 55.00, 25.00, 8.25, 80.00, '2025-05-11 08:20:00', 'cash', 'paid'),
(10, 12, 110.00, 15.00, 16.50, 125.00, '2025-05-12 12:10:00', 'visa', 'paid'),
(11, 13, 75.00, 25.00, 11.25, 100.00, '2025-05-13 07:25:00', 'cash', 'paid'),
(12, 14, 50.00, 15.00, 7.50, 65.00, '2025-05-14 10:40:00', 'visa', 'paid'),
(13, 15, 125.00, 15.00, 18.75, 140.00, '2025-05-15 13:55:00', 'cash', 'paid'),
(14, 16, 80.00, 15.00, 12.00, 95.00, '2025-05-16 06:05:00', 'visa', 'paid'),
(15, 17, 115.00, 15.00, 17.25, 130.00, '2025-05-17 09:30:00', 'cash', 'paid'),
(16, 18, 125.00, 15.00, 18.75, 140.00, '2025-05-18 11:45:00', 'visa', 'paid'),
(17, 19, 95.00, 15.00, 14.25, 110.00, '2025-05-19 14:00:00', 'cash', 'paid'),
(18, 20, 125.00, 15.00, 18.75, 140.00, '2025-05-20 07:15:00', 'visa', 'paid'),
(19, 23, 175.00, 25.00, 26.25, 200.00, '2025-05-22 07:20:00', 'visa', 'paid'),
(20, 24, 240.00, 25.00, 36.00, 265.00, '2025-05-22 08:55:00', 'cash', 'paid'),
(21, 25, 130.00, 35.00, 19.50, 165.00, '2025-05-22 10:30:00', 'visa', 'paid'),
(22, 26, 110.00, 45.00, 16.50, 155.00, '2025-06-02 19:02:45', 'visa', 'paid'),
(23, 30, 130.00, 45.00, 19.50, 175.00, '2025-06-02 19:02:45', 'visa', 'paid'),
(24, 21, 380.00, 15.00, 57.00, 395.00, '2025-05-21 10:30:00', 'visa', 'pending'),
(25, 22, 80.00, 5.00, 12.00, 85.00, '2025-05-22 05:45:00', 'visa', 'pending'),
(26, 31, 50.00, 15.00, 7.50, 65.00, '2025-06-08 05:02:08', 'visa', 'pending');

--
-- Triggers `payment_details`
--
DELIMITER $$
CREATE TRIGGER `before_payment_details_insert` BEFORE INSERT ON `payment_details` FOR EACH ROW BEGIN
    SET NEW.website_revenue = NEW.total_ord_price * 0.15;
    SET NEW.total_payment = NEW.total_ord_price + NEW.delivery_fees;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_payment_details_update` BEFORE UPDATE ON `payment_details` FOR EACH ROW BEGIN
    SET NEW.website_revenue = NEW.total_ord_price * 0.15;
    SET NEW.total_payment = NEW.total_ord_price + NEW.delivery_fees;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_no` int(11) NOT NULL,
  `stars` tinyint(3) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_no`, `stars`, `order_id`, `cloud_kitchen_id`, `customer_id`, `review_date`) VALUES
(2, 4, 2, 5, 4, '2025-05-02 12:30:00'),
(3, 5, 3, 7, 6, '2025-05-03 13:45:00'),
(4, 3, 4, 9, 8, '2025-05-04 10:15:00'),
(6, 5, 6, 20, 16, '2025-05-06 17:00:00'),
(7, 4, 7, 22, 17, '2025-05-07 09:30:00'),
(8, 3, 8, 24, 19, '2025-05-08 13:15:00'),
(9, 5, 9, 26, 21, '2025-05-09 15:00:00'),
(10, 4, 10, 28, 23, '2025-05-10 10:30:00'),
(11, 5, 11, 30, 25, '2025-05-11 12:20:00'),
(12, 3, 12, 32, 27, '2025-05-12 16:10:00'),
(13, 4, 13, 34, 29, '2025-05-13 11:25:00'),
(14, 5, 14, 36, 31, '2025-05-14 14:40:00'),
(15, 4, 15, 38, 33, '2025-05-15 17:45:00'),
(16, 3, 16, 40, 35, '2025-05-16 10:05:00'),
(17, 5, 17, 42, 37, '2025-05-17 13:30:00'),
(18, 4, 18, 44, 39, '2025-05-18 15:45:00'),
(19, 5, 19, 46, 41, '2025-05-19 18:00:00'),
(20, 3, 20, 48, 43, '2025-05-20 11:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `route_cache`
--

CREATE TABLE `route_cache` (
  `id` int(11) NOT NULL,
  `start_lat` decimal(10,8) NOT NULL,
  `start_lng` decimal(11,8) NOT NULL,
  `end_lat` decimal(10,8) NOT NULL,
  `end_lng` decimal(11,8) NOT NULL,
  `vehicle_type` varchar(20) DEFAULT 'car',
  `route_data` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_category`
--

CREATE TABLE `sub_category` (
  `subcat_id` int(11) NOT NULL,
  `subcat_name` varchar(100) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `parent_cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_category`
--

INSERT INTO `sub_category` (`subcat_id`, `subcat_name`, `admin_id`, `parent_cat_id`) VALUES
(1, 'Siani Bedouin', 1, 1),
(2, 'Red Sea Seafood', 1, 1),
(3, 'Festive Food', 1, 1),
(4, 'Upper Egypt', 1, 1),
(5, 'Pasta', 1, 2),
(6, 'Pizza', 1, 2),
(7, 'Risotto', 1, 2),
(8, 'Sushi', 1, 3),
(9, 'Pad Thai', 1, 3),
(14, 'Cakes', 1, 5),
(17, 'Grilled Fish', 1, 6),
(18, 'Fried Fish', 1, 6),
(19, 'Seafood Platter', 1, 6),
(20, 'Grilled Chicken', 1, 7),
(21, 'Grilled Meat', 1, 7),
(22, 'Grilled Vegetables', 1, 7),
(27, 'Greek', 1, 10),
(28, 'Turkish', 1, 10),
(29, 'Lebanese', 1, 10),
(32, 'Burgers', 1, 12),
(33, 'Subs', 1, 12),
(34, 'Wraps', 1, 12),
(35, 'Greek Salad', 1, 13),
(36, 'Caesar Salad', 1, 13),
(37, 'Fresh Juices', 1, 14),
(38, 'Smoothies', 1, 14),
(39, 'Mexican', 1, 15),
(40, 'Indian', 1, 15),
(42, 'sub22', 1, 16),
(43, 'sub44', 1, 16);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `u_name` varchar(100) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `u_role` enum('admin','external_user','delivery_man') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `u_name`, `mail`, `phone`, `password`, `reset_token`, `reset_token_expiry`, `u_role`, `created_at`, `last_login`) VALUES
(1, 'Admin User', 'admin@todaymeal.com', '01012345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', '2025-05-31 15:04:27', NULL),
(2, 'Mohamed Ali', 'mohamed.ali@example.com', '01011223344', '$2y$10$ozvHnjykXeZQZljuI1dICeGAuJKHU2Evrhxb/AfBa2/ltQSqcgBXi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(4, 'Fatma Mahmoud', 'fatma.mahmoud@example.com', '01033445566', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(5, 'Youssef Ibrahim', 'youssef.ibrahim@example.com', '01044556677', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(6, 'Aya Mohamed', 'aya.mohamed@example.com', '01055667788', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(7, 'Omar Khaled', 'omar.khaled@example.com', '01066778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(8, 'Hana Samir', 'hana.samir@example.com', '01077889900', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(9, 'Karim Adel', 'karim.adel@example.com', '01088990011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(10, 'Nourhan Wael', 'nourhan.wael@example.com', '01099001122', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(11, 'Delivery Man 1', 'delivery1@todaymeal.com', '01112345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', '2025-06-02 17:38:53'),
(12, 'Delivery Man 2', 'delivery2@todaymeal.com', '01123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(13, 'Delivery Man 3', 'delivery3@todaymeal.com', '01134567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(14, 'Delivery Man 4', 'delivery4@todaymeal.com', '01145678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(15, 'Delivery Man 5', 'delivery5@todaymeal.com', '01156789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(16, 'Ali Mohamed', 'ali.mohamed@example.com', '01212345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(17, 'Mona Ahmed', 'mona.ahmed@example.com', '01223456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(19, 'Dalia Magdy', 'dalia.magdy@example.com', '01245678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(20, 'Wael Khedr', 'wael.khedr@example.com', '01256789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(21, 'Rania Said', 'rania.said@example.com', '01267890123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(22, 'Sherif Gamal', 'sherif.gamal@example.com', '01278901234', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(23, 'Heba Ali', 'heba.ali@example.com', '01289012345', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(24, 'Amr Diab', 'amr.diab@example.com', '01290123456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(25, 'Nada Mostafa', 'nada.mostafa@example.com', '01011223399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(26, 'Khaled ElNabawy', 'khaled.elnabawy@example.com', '01022334499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(27, 'Samira Ahmed', 'samira.ahmed@example.com', '01033445599', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(28, 'Mahmoud Yassin', 'mahmoud.yassin@example.com', '01044556699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(29, 'Laila Elwi', 'laila.elwi@example.com', '01055667799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(30, 'Hassan Hosny', 'hassan.hosny@example.com', '01066778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(31, 'Yasmin Abdelaziz', 'yasmin.abdelaziz@example.com', '01077889999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(32, 'Tarek Lotfy', 'tarek.lotfy@example.com', '01088990099', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(33, 'Mai Kassab', 'mai.kassab@example.com', '01099001199', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(34, 'Adel Emam', 'adel.emam@example.com', '01112345699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(35, 'Mervat Amin', 'mervat.amin@example.com', '01123456799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(36, 'Hany Ramzy', 'hany.ramzy@example.com', '01134567899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(37, 'Nelly Karim', 'nelly.karim@example.com', '01145678999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(38, 'Ahmed Helmy', 'ahmed.helmy@example.com', '01156789099', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(39, 'Donia Samir', 'donia.samir@example.com', '01167890199', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(40, 'Mohamed Henedy', 'mohamed.henedy@example.com', '01178901299', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(41, 'Mona Zaki', 'mona.zaki@example.com', '01189012399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(42, 'Karim Abdel Aziz', 'karim.abdelaziz@example.com', '01190123499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(43, 'Yousra', 'yousra@example.com', '01211223399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(44, 'Sherihan', 'sherihan@example.com', '01222334499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(45, 'Hani Salama', 'hani.salama@example.com', '01233445599', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(46, 'Sawsan Badr', 'sawsan.badr@example.com', '01244556699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(47, 'Ahmed Rizk', 'ahmed.rizk@example.com', '01255667799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(48, 'Lebleba', 'lebleba@example.com', '01266778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(49, 'Ezzat El Alaily', 'ezzat.alaily@example.com', '01277889999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(50, 'Fifi Abdou', 'fifi.abdou@example.com', '01288990099', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(54, 'DEL20251002', 'ahmed@delivery.com', '+201272377148', '$2y$10$oSEyaiaThOIY6/Vo8o0dX.thRxQz2XhV6nmiG0nr4s65GVAqcxa3O', NULL, NULL, 'delivery_man', '2025-06-01 08:32:23', '2025-06-01 11:37:49'),
(55, 'hussein', 'hu@delivery.com', '+201172377189', '$2y$10$RFkY3tpGui81OFDsRvU.x.Tn5ymtQ32Zlwb4TTKWCNnHe8A1HmPmO', NULL, NULL, 'delivery_man', '2025-06-01 12:30:12', NULL),
(58, 'hussein 2', 'hue@delivery.com', '+201172377189', '$2y$10$sofxCH998258KJMfFZ724OfHeym0z/nOBOdwVvEvbM1MWruHjONtK', NULL, NULL, 'delivery_man', '2025-06-01 12:32:07', NULL),
(60, 'ali', 'ali@gmail.com', '+2012333678', '$2y$10$ycbh4XMXtlXupnxanrLpuu9XYmc2bdceXBds6m94hBs/4QYCkJNiS', NULL, NULL, 'delivery_man', '2025-06-01 12:37:09', NULL),
(63, 'aliali', 'aliali@gmail.com', '+2012777678', '$2y$10$vU0GGIj9OKj5LC/GTOLrC.XYagnX5Hf8.d2yJHT7BHi0llLjwSxdW', NULL, NULL, 'delivery_man', '2025-06-01 12:39:08', NULL),
(65, 'aliali ali', 'alialiali@gmail.com', '+2012777778', '$2y$10$VnHOAXb5I7wXbu6pWUXVSOHptlSsYPOyEQCZ/iGsWkV7HEOZf8ZNi', NULL, NULL, 'delivery_man', '2025-06-01 13:02:17', NULL),
(66, 'fff', 'fff@gmail', '+201277776', '$2y$10$TvNwvVaxor1akZUrwKtBSugoHaFMFSza6/JNla2NCmouRUhVQaL32', NULL, NULL, 'delivery_man', '2025-06-01 13:26:31', NULL),
(68, 'fffnnn', 'fffnn@gmail', '+201277777', '$2y$10$sWeOMyLWKQNLEmMKrlcgqOUIZeZb/yOnZMh/yJlUfriBmMDpHsoDq', NULL, NULL, 'delivery_man', '2025-06-01 13:36:11', '2025-06-01 13:37:38'),
(69, 'name', 'name@gmail.com', '01230123012', '$2y$10$gSoFwIVCvvU5v0i7dWua.u9AUvyBTLKgv1gn.5OeYIbG0/A3s90X.', NULL, NULL, 'delivery_man', '2025-06-01 13:56:01', '2025-06-02 15:00:51'),
(70, 'na', 'na@gmail.com', '012012012', '$2y$10$.BsPwzHZED0FNNB6JsMhtudeHAS.08b0x5ZCkmEvXIN/Z4fK54LoS', NULL, NULL, 'delivery_man', '2025-06-02 14:54:21', NULL),
(72, 'nana', 'naa@gmail.com', '012012013', '$2y$10$VoAr7RaNAwjWGjQ5LoUyhuPJKQwpkzodTK1bPjM2GpIQbs7tr4nVK', NULL, NULL, 'delivery_man', '2025-06-02 14:56:16', NULL),
(73, 'Hussein Ali', 'ha@gmail.com', '+201277779', '$2y$10$GoD1dgkYatKTMPXpix6wIOWUMy/ocfbVEYxBUMCkQAFHvwG0VCT9q', NULL, NULL, 'delivery_man', '2025-06-02 15:21:42', NULL),
(75, 'Hussein Ali', 'haaa@gmail.com', '+201277773', '$2y$10$klcKx9TjXnNeL4X6K.AR/eC4mi8JP5fPotCDYezechsjpNiHZ2.zq', NULL, NULL, 'delivery_man', '2025-06-02 16:40:39', NULL),
(76, 'Hussein Ali', 'haaaa@gmail.com', '+201277779', '$2y$10$H5kaKrJUJlZPSNQtFoA0eOJQ./5eP8A3DFY8.ByQMSyF2JuaGmyly', NULL, NULL, 'delivery_man', '2025-06-02 16:58:46', NULL),
(80, 'Cairo Delights', 'cairo.delights@example.com', '01055550001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:01:20', NULL),
(81, 'Dalia Elbehery', 'dalia@example.com', '01203555178', '$2y$10$ozvHnjykXeZQZljuI1dICeGAuJKHU2Evrhxb/AfBa2/ltQSqcgBXi', NULL, NULL, 'external_user', '2025-06-05 14:58:45', NULL),
(82, 'Omar Hassan', 'omar.hassan@example.com', '01066660001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(83, 'Laila Ahmed', 'laila.ahmed@example.com', '01066660002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(84, 'Karim Mohamed', 'karim.mohamed@example.com', '01066660003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(85, 'Nadia Samir', 'nadia.samir@example.com', '01066660004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(86, 'Youssef Ali', 'youssef.ali@example.com', '01066660005', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(87, 'hany', 'hany@gmail.com', '+20127557988', '$2y$10$ozvHnjykXeZQZljuI1dICeGAuJKHU2Evrhxb/AfBa2/ltQSqcgBXi', NULL, NULL, 'delivery_man', '2025-06-02 19:03:45', '2025-06-03 12:06:27'),
(88, 'name 2', 'name2@gmail.com', '+201272477147', '$2y$10$UDX1IDaLPLelpczUGgPC6u6NrJNWbpFXBgcP7xVjnKNDgGeKh5CkG', NULL, NULL, 'delivery_man', '2025-06-03 10:44:59', '2025-06-03 10:46:25'),
(89, 'name', 'gg@gmail.com', '01225255588', '$2y$10$7fDVzQyBKHmZG8NVk3ajze2xIWJi8i9LDro2.NhKM3IEmhuJP3CpS', NULL, NULL, 'delivery_man', '2025-06-03 17:28:26', '2025-06-06 17:30:48'),
(90, 'customer1', 'customer1@gmail.com', '127777988', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-06-04 11:02:10', NULL),
(91, 'c2', 'c2@example.com', '0123456789', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-06-04 16:16:31', NULL),
(95, 'ck55', 'ck55@exampl.com', '01202000199', '$2y$10$ozvHnjykXeZQZljuI1dICeGAuJKHU2Evrhxb/AfBa2/ltQSqcgBXi', NULL, NULL, 'external_user', '2025-06-10 07:58:12', NULL),
(96, 'ck66', 'ck66@exampl.com', '01202000179', '$2y$10$8O8DNjpk8ZKiZQ9sE2OnT.YvKWWCCXiV3gU//ssxzu..GXXUts/Te', NULL, NULL, 'external_user', '2025-06-10 08:01:45', NULL),
(98, 'ck99', 'ck99@gmail.com', '01203000185', '$2y$10$pqaVEatl6MQ0kMvMotZviuV51bAvzeyuAsseCTfT4N0na57n5PGVO', NULL, NULL, 'external_user', '2025-06-10 08:13:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`zone_id`, `name`) VALUES
(2, '6th of October'),
(1, 'New Cairo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`action_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `c_name` (`c_name`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `caterer_tags`
--
ALTER TABLE `caterer_tags`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `cloud_kitchen_owner`
--
ALTER TABLE `cloud_kitchen_owner`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `speciality_id` (`speciality_id`),
  ADD KEY `fk_suspended_by` (`suspended_by`);

--
-- Indexes for table `cloud_kitchen_specialist_category`
--
ALTER TABLE `cloud_kitchen_specialist_category`
  ADD PRIMARY KEY (`cloud_kitchen_id`,`cat_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `kitchen_id` (`kitchen_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `customized_order`
--
ALTER TABLE `customized_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `kitchen_id` (`kitchen_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_person_id` (`delivery_person_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `delivery_man`
--
ALTER TABLE `delivery_man`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `d_license` (`d_license`),
  ADD UNIQUE KEY `d_n_id` (`d_n_id`),
  ADD KEY `fk_zone` (`zone_id`);

--
-- Indexes for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indexes for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`,`type`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dietary_tags`
--
ALTER TABLE `dietary_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Indexes for table `external_user`
--
ALTER TABLE `external_user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_external_user_zone` (`zone_id`);

--
-- Indexes for table `financial_settlements`
--
ALTER TABLE `financial_settlements`
  ADD PRIMARY KEY (`settlement_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `settlement_type` (`settlement_type`),
  ADD KEY `settlement_status` (`settlement_status`),
  ADD KEY `idx_settlement_type_status` (`settlement_type`,`settlement_status`),
  ADD KEY `idx_settlement_date` (`settlement_date`);

--
-- Indexes for table `kitchen_documents`
--
ALTER TABLE `kitchen_documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `kitchen_id` (`kitchen_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  ADD PRIMARY KEY (`package_meal_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD PRIMARY KEY (`meal_id`,`cat_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `meal_dietary_tag`
--
ALTER TABLE `meal_dietary_tag`
  ADD PRIMARY KEY (`meal_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `meal_subcategory`
--
ALTER TABLE `meal_subcategory`
  ADD PRIMARY KEY (`meal_id`,`subcat_id`),
  ADD KEY `subcat_id` (`subcat_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `order_content`
--
ALTER TABLE `order_content`
  ADD PRIMARY KEY (`order_content_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `order_packages`
--
ALTER TABLE `order_packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_no`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `route_cache`
--
ALTER TABLE `route_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coordinates` (`start_lat`,`start_lng`,`end_lat`,`end_lng`,`vehicle_type`);

--
-- Indexes for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD PRIMARY KEY (`subcat_id`),
  ADD KEY `parent_cat_id` (`parent_cat_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `action_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dietary_tags`
--
ALTER TABLE `dietary_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `financial_settlements`
--
ALTER TABLE `financial_settlements`
  MODIFY `settlement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `kitchen_documents`
--
ALTER TABLE `kitchen_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  MODIFY `package_meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `order_content`
--
ALTER TABLE `order_content`
  MODIFY `order_content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_packages`
--
ALTER TABLE `order_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `route_cache`
--
ALTER TABLE `route_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_category`
--
ALTER TABLE `sub_category`
  MODIFY `subcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`user_id`);

--
-- Constraints for table `caterer_tags`
--
ALTER TABLE `caterer_tags`
  ADD CONSTRAINT `caterer_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `caterer_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `dietary_tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `cloud_kitchen_owner`
--
ALTER TABLE `cloud_kitchen_owner`
  ADD CONSTRAINT `cloud_kitchen_owner_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `external_user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cloud_kitchen_owner_ibfk_3` FOREIGN KEY (`speciality_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_suspended_by` FOREIGN KEY (`suspended_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cloud_kitchen_specialist_category`
--
ALTER TABLE `cloud_kitchen_specialist_category`
  ADD CONSTRAINT `cloud_kitchen_specialist_category_ibfk_1` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `cloud_kitchen_specialist_category_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `external_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `customized_order`
--
ALTER TABLE `customized_order`
  ADD CONSTRAINT `customized_order_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customized_order_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customized_order_ibfk_3` FOREIGN KEY (`kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`delivery_person_id`) REFERENCES `delivery_man` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `delivery_man`
--
ALTER TABLE `delivery_man`
  ADD CONSTRAINT `delivery_man_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  ADD CONSTRAINT `delivery_status_history_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  ADD CONSTRAINT `delivery_subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  ADD CONSTRAINT `delivery_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `external_user`
--
ALTER TABLE `external_user`
  ADD CONSTRAINT `external_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_external_user_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `financial_settlements`
--
ALTER TABLE `financial_settlements`
  ADD CONSTRAINT `financial_settlements_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `kitchen_documents`
--
ALTER TABLE `kitchen_documents`
  ADD CONSTRAINT `kitchen_documents_ibfk_1` FOREIGN KEY (`kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kitchen_documents_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `meals_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meals_ibfk_3` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meals_ibfk_4` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  ADD CONSTRAINT `meals_in_each_package_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `order_packages` (`package_id`),
  ADD CONSTRAINT `meals_in_each_package_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD CONSTRAINT `meal_category_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`),
  ADD CONSTRAINT `meal_category_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `meal_dietary_tag`
--
ALTER TABLE `meal_dietary_tag`
  ADD CONSTRAINT `meal_dietary_tag_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_dietary_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `dietary_tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_subcategory`
--
ALTER TABLE `meal_subcategory`
  ADD CONSTRAINT `meal_subcategory_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`),
  ADD CONSTRAINT `meal_subcategory_ibfk_2` FOREIGN KEY (`subcat_id`) REFERENCES `sub_category` (`subcat_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `order_content`
--
ALTER TABLE `order_content`
  ADD CONSTRAINT `order_content_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_content_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `order_packages`
--
ALTER TABLE `order_packages`
  ADD CONSTRAINT `order_packages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`);

--
-- Constraints for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD CONSTRAINT `sub_category_ibfk_1` FOREIGN KEY (`parent_cat_id`) REFERENCES `category` (`cat_id`),
  ADD CONSTRAINT `sub_category_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
